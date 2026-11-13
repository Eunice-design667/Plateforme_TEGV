<?php

namespace App\Console\Commands;

use App\Enums\ThanksStatus;
use App\Models\Visitor;
use App\Services\MessageWriter;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppManager;
use App\Support\PhoneNumber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tegv:remercier-visiteurs {--dry-run : Rédige les messages sans les envoyer}')]
#[Description('Envoie le message de remerciement WhatsApp aux visiteurs pas encore remerciés')]
class ThankSundayVisitors extends Command
{
    public function handle(MessageWriter $writer, WhatsAppManager $whatsApp): int
    {
        // Visitors of previous days only: Sunday's visitors are thanked on Monday, and those
        // left over when the task runs out of time are thanked the next day.
        $visitors = Visitor::query()->awaitingThanks()->whereDate('visited_on', '<', today())->orderBy('id')->get();

        if ($visitors->isEmpty()) {
            $this->info('Aucun visiteur à remercier.');

            return self::SUCCESS;
        }

        foreach ($visitors as $index => $visitor) {
            $message = $writer->visitorThanks($visitor->first_name, $visitor->has_no_church ? null : $visitor->origin_church, $visitor->visited_on);

            if ($this->option('dry-run')) {
                $this->line("— {$visitor->fullName()} : {$message}");

                continue;
            }

            if ($index > 0) {
                sleep(config('tegv.thanks_delay_seconds'));
            }

            $this->deliver($visitor, $message, $whatsApp);
        }

        return self::SUCCESS;
    }

    private function deliver(Visitor $visitor, string $message, WhatsAppManager $whatsApp): void
    {
        $visitor->thanks_message = $message;
        $number = PhoneNumber::normalize($visitor->whatsapp);

        try {
            if ($number === null) {
                throw WhatsAppException::invalidNumber($visitor->whatsapp);
            }

            $visitor->thanks_channel = $whatsApp->send($number, $message);
            $visitor->thanks_status = ThanksStatus::Sent;
            $visitor->thanks_sent_at = now();
            $visitor->thanks_error = null;
            $this->info("Envoyé à {$visitor->fullName()} ({$visitor->thanks_channel})");
        } catch (WhatsAppException $exception) {
            // A failed visitor is never retried automatically, so nobody receives the message twice.
            $visitor->thanks_status = ThanksStatus::Failed;
            $visitor->thanks_error = mb_substr($exception->getMessage(), 0, 250);
            $this->warn("Échec pour {$visitor->fullName()} : {$exception->getMessage()}");
        }

        $visitor->save();
    }
}
