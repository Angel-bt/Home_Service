<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityEvent;
use App\Models\ProviderDocument;
use App\Models\Service;
use App\Services\ProviderVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Archivos que solo ve la administración (las rutas están bajo authadmin).
 */
class AdminTrustController extends Controller
{
    public function document(ProviderDocument $document): StreamedResponse
    {
        $disk = Storage::disk(ProviderVerification::DISK);

        abort_unless($document->path !== null && $disk->exists($document->path), 404);

        return $disk->response($document->path, null, ['Cache-Control' => 'private, no-store']);
    }

    /**
     * Actividad en CSV para analizarla en Excel. Va sin datos de la cuenta: solo qué pasó, no quién.
     */
    public function exportActivity(Request $request): StreamedResponse
    {
        $days = min(max((int) $request->query('days', 30), 1), 366);

        return response()->streamDownload(function () use ($days): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['fecha', 'evento', 'busqueda', 'resultados', 'servicio', 'zona', 'tipo_de_cuenta'], ';');

            $names = Service::query()->pluck('name', 'id');

            ActivityEvent::query()->where('created_at', '>=', now()->subDays($days))->orderBy('id')->lazyById(500)
                ->each(function (ActivityEvent $event) use ($output, $names): void {
                    fputcsv($output, [
                        $event->created_at?->format('Y-m-d H:i'),
                        $event->event,
                        self::safeCell($event->term),
                        $event->results,
                        self::safeCell($names[$event->service_id] ?? null),
                        $event->zone,
                        $event->user_role,
                    ], ';');
                });

            fclose($output);
        }, 'actividad-malakplace-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Lo que escribe la gente no debe ejecutarse como fórmula al abrir el archivo en Excel.
     */
    private static function safeCell(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'" . $value : $value;
    }
}
