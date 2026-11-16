<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Support\FrenchDate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The list of visitors of one Sunday (or of every date), as a PDF to print or a CSV for Excel.
 */
class VisitorExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'format' => ['required', 'in:pdf,csv'],
        ]);

        $date = isset($validated['date']) ? Carbon::parse($validated['date']) : null;
        $visitors = Visitor::query()
            ->when($date, fn ($query) => $query->whereDate('visited_on', $date))
            ->orderByDesc('visited_on')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $title = $date ? 'Visiteurs du '.FrenchDate::long($date) : 'Tous les visiteurs';
        $file = 'visiteurs-tegv-'.($date?->toDateString() ?? 'tous');

        if ($validated['format'] === 'csv') {
            return $this->csv($visitors, "{$file}.csv");
        }

        return Pdf::loadView('pdf.visitors', ['visitors' => $visitors, 'title' => $title, 'date' => $date])
            ->setPaper('a4')
            ->download("{$file}.pdf");
    }

    /**
     * Semicolons and a UTF-8 mark, so Excel in French opens it with the right columns and accents.
     *
     * @param  Collection<int, Visitor>  $visitors
     */
    private function csv($visitors, string $file): StreamedResponse
    {
        return response()->streamDownload(function () use ($visitors): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date de visite', 'Prénom', 'Nom', 'Église d’origine', 'Numéro WhatsApp', 'Enregistré à'], ';', escape: '\\');

            foreach ($visitors as $visitor) {
                fputcsv($out, [
                    $visitor->visited_on->format('d/m/Y'),
                    $visitor->first_name,
                    $visitor->last_name,
                    $visitor->has_no_church ? 'Pas d’église' : $visitor->origin_church,
                    $visitor->whatsapp,
                    $visitor->created_at?->format('H:i'),
                ], ';', escape: '\\');
            }

            fclose($out);
        }, $file, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
