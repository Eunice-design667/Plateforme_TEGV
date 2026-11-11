<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;

/**
 * Writes the short personal messages sent by the Monday agent
 * (visitor thanks) and the birthday wishes, with the Claude API.
 * Falls back to a warm template when no API key is set or the call fails,
 * so a visitor or member is never left without a message.
 */
class MessageWriter
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        Tu rédiges des messages courts pour l'Église AD, Temple Évangélique Grâce et Victoire (TEGV), à Ouagadougou (Burkina Faso).
        Le ton est chaleureux, simple et fraternel, en français, comme un message WhatsApp écrit par l'église.
        Le message fait 2 à 4 phrases, sans titre, sans liste, sans guillemets, avec au plus un emoji.
        Ne promets rien que l'église n'a pas annoncé et n'invente aucun détail sur la personne.
        Réponds uniquement par le texte du message.
        PROMPT;

    public function visitorThanks(string $firstName, ?string $originChurch, CarbonInterface $visitedOn): string
    {
        $visit = $visitedOn->isSunday() && $visitedOn->isSameDay(today()->subDay())
            ? "venu(e) au culte dominical d'hier au TEGV"
            : 'venu(e) au TEGV le '.$visitedOn->translatedFormat('l j F');

        $context = $originChurch
            ? "Le visiteur vient de l'église « {$originChurch} »."
            : "Le visiteur n'a pas d'église pour l'instant.";

        $prompt = "Écris le message de remerciement envoyé le lundi à {$firstName}, {$visit}. "
            ."{$context} Remercie-le pour sa présence, dis-lui qu'il est le bienvenu et invite-le à revenir "
            .'(culte tous les dimanches de 8h à 10h30, étude biblique le mardi et prière le vendredi de 19h à 20h30).';

        return $this->write($prompt) ?? $this->templateThanks($firstName);
    }

    /**
     * Standard thank-you messages, used while no Claude key is set.
     * Several wordings so that two visitors do not receive the exact same text.
     */
    public function templateThanks(string $firstName): string
    {
        $templates = [
            "Bonjour {$firstName}, toute la famille du Temple Évangélique Grâce et Victoire vous remercie pour votre visite. Vous êtes ici chez vous : nous serons heureux de vous revoir dimanche prochain à 8h. Que Dieu vous bénisse !",
            "Bonjour {$firstName} ! Merci d’avoir partagé le culte avec nous au TEGV. Ce fut une joie de vous accueillir ; notre porte vous reste grande ouverte, chaque dimanche de 8h à 10h30. Soyez béni(e) !",
            "Cher(e) {$firstName}, le Temple Évangélique Grâce et Victoire vous dit merci pour votre présence. Nous espérons vous retrouver bientôt, au culte du dimanche ou à l’étude biblique du mardi à 19h. Que la grâce de Dieu vous accompagne !",
            "Bonjour {$firstName}, merci pour votre visite au TEGV ! Toute l’église a été heureuse de vous recevoir. Revenez quand vous voulez : culte le dimanche à 8h, prière le vendredi à 19h. Dieu vous bénisse !",
        ];

        return $templates[abs(crc32(mb_strtolower($firstName))) % count($templates)];
    }

    public function birthdayWishes(string $firstName): string
    {
        $prompt = "Écris un message de vœux d'anniversaire pour {$firstName}, membre du TEGV, affiché sur la plateforme de l'église le jour de son anniversaire. "
            .'Souhaite-lui une belle journée et une année bénie. Ne mentionne jamais son âge.';

        return $this->write($prompt) ?? "Joyeux anniversaire {$firstName} ! Toute la famille du TEGV se réjouit avec vous "
            .'et prie pour que cette nouvelle année soit remplie de la grâce et de la paix de Dieu. 🎉';
    }

    private function write(string $prompt): ?string
    {
        $apiKey = config('services.anthropic.key');

        if (blank($apiKey)) {
            return null;
        }

        try {
            $client = new Client(apiKey: $apiKey);

            $message = $client->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: 1024,
                system: self::SYSTEM_PROMPT,
                outputConfig: ['effort' => 'low'],
                messages: [['role' => 'user', 'content' => $prompt]],
            );
        } catch (APIException $exception) {
            Log::warning('Claude API call failed, using the template message.', ['error' => $exception->getMessage()]);

            return null;
        }

        if ($message->stopReason === 'refusal') {
            return null;
        }

        $text = collect($message->content)
            ->filter(fn ($block) => $block->type === 'text')
            ->map(fn ($block) => $block->text)
            ->implode("\n");

        return trim($text) !== '' ? trim($text) : null;
    }
}
