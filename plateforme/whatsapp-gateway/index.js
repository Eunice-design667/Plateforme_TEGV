// Passerelle WhatsApp du TEGV (option gratuite, non officielle).
//
// - Au premier lancement, un QR code s'affiche dans le terminal : scannez-le avec
//   le téléphone du numéro DÉDIÉ de l'église (WhatsApp > Appareils connectés).
// - La plateforme Laravel appelle ensuite POST /send avec { number, message }.
// - Réglages : GATEWAY_TOKEN (obligatoire, le même que WHATSAPP_GATEWAY_TOKEN côté Laravel)
//   et GATEWAY_PORT (3001 par défaut). N'exposez pas ce service sur Internet.
//
// Attention : ce mode est contraire aux conditions de WhatsApp et le numéro peut être bloqué.
// La plateforme bascule alors sur l'API officielle si WHATSAPP_FALLBACK=cloud.

import http from 'node:http';
import makeWASocket, { DisconnectReason, useMultiFileAuthState } from '@whiskeysockets/baileys';
import qrcode from 'qrcode-terminal';

const PORT = Number(process.env.GATEWAY_PORT ?? 3001);
const TOKEN = process.env.GATEWAY_TOKEN;

if (!TOKEN) {
    console.error('GATEWAY_TOKEN manquant. Exemple : GATEWAY_TOKEN=un-long-secret npm start');
    process.exit(1);
}

let socket = null;
let ready = false;

async function connect() {
    const { state, saveCreds } = await useMultiFileAuthState('./session');
    socket = makeWASocket({ auth: state, browser: ['TEGV', 'Chrome', '1.0'] });
    socket.ev.on('creds.update', saveCreds);

    socket.ev.on('connection.update', ({ connection, lastDisconnect, qr }) => {
        if (qr) {
            console.log('Scannez ce QR code avec le téléphone du numéro de l’église :');
            qrcode.generate(qr, { small: true });
        }

        if (connection === 'open') {
            ready = true;
            console.log('WhatsApp connecté.');
        }

        if (connection === 'close') {
            ready = false;
            const loggedOut = lastDisconnect?.error?.output?.statusCode === DisconnectReason.loggedOut;
            console.log(loggedOut ? 'Session déconnectée : supprimez le dossier session/ et relancez.' : 'Connexion perdue, reconnexion…');

            if (!loggedOut) {
                setTimeout(connect, 5000);
            }
        }
    });
}

function reply(res, status, body) {
    res.writeHead(status, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify(body));
}

function readJson(req) {
    return new Promise((resolve, reject) => {
        let data = '';
        req.on('data', (chunk) => {
            data += chunk;
            if (data.length > 20_000) {
                reject(new Error('Message trop long'));
                req.destroy();
            }
        });
        req.on('end', () => {
            try {
                resolve(JSON.parse(data || '{}'));
            } catch (error) {
                reject(error);
            }
        });
    });
}

const server = http.createServer(async (req, res) => {
    if (req.method === 'GET' && req.url === '/health') {
        return reply(res, ready ? 200 : 503, { ready });
    }

    if (req.method !== 'POST' || req.url !== '/send') {
        return reply(res, 404, { error: 'Introuvable' });
    }

    if (req.headers.authorization !== `Bearer ${TOKEN}`) {
        return reply(res, 401, { error: 'Non autorisé' });
    }

    if (!ready) {
        return reply(res, 503, { error: 'WhatsApp n’est pas connecté' });
    }

    try {
        const { number, message } = await readJson(req);
        const digits = String(number ?? '').replace(/\D/g, '');

        if (digits.length < 10 || !message) {
            return reply(res, 422, { error: 'Numéro ou message invalide' });
        }

        const [account] = await socket.onWhatsApp(`${digits}@s.whatsapp.net`);

        if (!account?.exists) {
            return reply(res, 422, { error: 'Ce numéro n’a pas WhatsApp' });
        }

        await socket.sendMessage(account.jid, { text: String(message) });

        return reply(res, 200, { sent: true });
    } catch (error) {
        return reply(res, 500, { error: error.message });
    }
});

server.listen(PORT, '127.0.0.1', () => console.log(`Passerelle WhatsApp sur http://127.0.0.1:${PORT}`));
connect();
