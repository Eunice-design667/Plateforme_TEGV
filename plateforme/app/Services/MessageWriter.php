<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIException;
use Illuminate\Support\Facades\Log;

/**
 * Writes the personal birthday wishes shown on the platform, with the Claude API.
 * Falls back to a warm template when no API key is set or the call fails,
 * so a member is never left without a message.
 */
class MessageWriter
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        Tu rédiges des messages courts pour l'Église AD, Temple Évangélique Grâce et Victoire (TEGV), à Ouagadougou (Burkina Faso).
        Le ton est chaleureux, simple et fraternel, en français, comme un mot écrit par l'église.
        Le message fait 2 à 4 phrases, sans titre, sans liste, sans guillemets, avec au plus un emoji.
        Ne promets rien que l'église n'a pas annoncé et n'invente aucun détail sur la personne.
        Réponds uniquement par le texte du message.
        PROMPT;

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
