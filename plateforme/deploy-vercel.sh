#!/usr/bin/env bash
#
# Déploie la plateforme TEGV sur Vercel (production).
#
# Le moteur PHP de Vercel (vercel-php 0.9) ne fonctionne plus avec le nouveau
# démarrage des fonctions Vercel (https://github.com/vercel-community/php/issues/650).
# Ce script construit donc le site dans un conteneur Linux, applique le correctif
# connu, puis envoie la version construite sur Vercel.
#
# Prérequis : Docker lancé, `vercel login` fait, et le fichier .env.vercel rempli.
# Utilisation : ./deploy-vercel.sh
#
set -euo pipefail

cd "$(dirname "$0")"
APP_DIR="$(pwd)"
BUILD_DIR="${TMPDIR:-/tmp}"
BUILD_DIR="${BUILD_DIR%/}/tegv-vercel-build"
VERCEL_VERSION="63.1.2"

[ -f .env.vercel ] || { echo "Fichier .env.vercel introuvable."; exit 1; }
[ -f .vercel/project.json ] || { echo "Projet Vercel non relié : lancez 'vercel link'."; exit 1; }
docker version >/dev/null 2>&1 || { echo "Docker n'est pas lancé : ouvrez Docker Desktop puis relancez."; exit 1; }

# The build copy holds production secrets: always remove it at the end.
trap 'rm -rf "$BUILD_DIR"' EXIT

echo "1/5 Préparation d'une copie propre du projet…"
rm -rf "$BUILD_DIR" && mkdir -p "$BUILD_DIR/.vercel"
rsync -a \
    --exclude vendor --exclude node_modules --exclude .git --exclude .vercel \
    --exclude public/build --exclude public/hot --exclude public/storage \
    --exclude 'bootstrap/cache/*.php' --exclude 'storage/app/public/*' --exclude 'storage/logs/*' \
    --exclude 'storage/framework/*/*' --exclude 'database/*.sqlite' \
    --exclude whatsapp-gateway/node_modules --exclude whatsapp-gateway/session \
    --exclude .env --exclude '.env.*' --exclude .vercelignore \
    ./ "$BUILD_DIR/"
cp .vercel/project.json "$BUILD_DIR/.vercel/"

echo "2/5 Réglages du projet Vercel…"
(cd "$BUILD_DIR" && vercel pull --yes --environment=production >/dev/null)
python3 - "$BUILD_DIR/.vercel/project.json" <<'PY'
import json, sys
path = sys.argv[1]
project = json.load(open(path))
# The copy is already the "plateforme" folder.
project.setdefault('settings', {})['rootDirectory'] = None
json.dump(project, open(path, 'w'))
PY
# Sensitive variables cannot be pulled back from Vercel: use the local file.
grep -v '^[[:space:]]*#' .env.vercel | grep '=' > "$BUILD_DIR/.vercel/.env.production.local"

echo "3/5 Construction dans un conteneur Linux (base de données mise à jour au passage)…"
docker run --rm --platform linux/amd64 \
    -v "$BUILD_DIR:$BUILD_DIR" -w "$BUILD_DIR" -e VERCEL_TELEMETRY_DISABLED=1 \
    node:22 bash -c "npm install -g vercel@$VERCEL_VERSION >/dev/null 2>&1 && vercel build --prod --yes"

echo "4/5 Correctif du démarrage PHP…"
mkdir -p "$BUILD_DIR/.vercel/tegv-bridge"
cat > "$BUILD_DIR/.vercel/tegv-bridge/launcher.js" <<'JS'
// Bridge between Vercel's current Node bootstrap and the vercel-php launcher
// (workaround for https://github.com/vercel-community/php/issues/650).
process.env.LAMBDA_TASK_ROOT = process.env.LAMBDA_TASK_ROOT || '/var/task';

const original = require('./launcher-orig.js');

exports.launcher = async (event, context) => {
    const normalized = { ...event };

    // The bootstrap sends the body as an array of bytes (or null on GET).
    if (Array.isArray(normalized.body)) {
        normalized.body = Buffer.from(normalized.body);
    } else if (typeof normalized.body === 'string') {
        normalized.body = Buffer.from(normalized.body, normalized.isBase64Encoded ? 'base64' : 'utf8');
    } else {
        normalized.body = undefined;
    }

    const headers = normalized.headers || {};
    normalized.host = normalized.host || headers['x-forwarded-host'] || headers.host;

    return original.launcher(normalized, context);
};
JS
python3 - "$BUILD_DIR/.vercel/output/functions/api/index.func/.vc-config.json" <<'PY'
import json, sys
path = sys.argv[1]
config = json.load(open(path))
files = config['filePathMap']
if config.get('handler') != 'launcher.js':
    files['launcher-orig.js'] = files['launcher.js']
    files['launcher.js'] = '.vercel/tegv-bridge/launcher.js'
    config.update(handler='launcher.js', launcherType='Nodejs', awsLambdaHandler='launcher.launcher')
json.dump(config, open(path, 'w'))
PY

echo "5/5 Envoi sur Vercel (production)…"
(cd "$BUILD_DIR" && vercel deploy --prebuilt --prod --archive=tgz --yes)

echo "Terminé : https://plateforme-tegv.vercel.app"
