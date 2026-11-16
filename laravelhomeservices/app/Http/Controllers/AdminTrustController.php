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

        return \App\Support\PrivateFile::response($disk, $document->path);
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
     * Copia de los datos principales en CSV (Estado del sistema → Descargar datos). Admin solamente.
     */
    public function export(string $type): StreamedResponse
    {
        $exports = [
            'users' => [
                ['nombre', 'correo', 'celular', 'tipo', 'plan', 'creada', 'suspendida', 'motivo'],
                fn () => \App\Models\User::query()->orderBy('id')->lazyById(500),
                fn ($u) => [$u->name, $u->email, $u->phone, ['ADM' => 'administración', 'SVP' => 'proveedor', 'CST' => 'cliente'][$u->utype] ?? $u->utype, $u->isPro() ? 'Pro' : 'gratis', $u->created_at?->format('Y-m-d'), $u->suspended_at?->format('Y-m-d'), $u->suspension_reason],
            ],
            'services' => [
                ['servicio', 'oficio', 'publicado por', 'precio', 'visible', 'pausado', 'destacado', 'visitas', 'creado'],
                fn () => Service::query()->with(['category:id,name', 'user:id,name'])->orderBy('id')->lazyById(500),
                fn ($s) => [$s->name, $s->category?->name, $s->user?->name ?? 'administración', $s->price, $s->status ? 'sí' : 'no', $s->paused_at ? 'sí' : 'no', $s->featured ? 'sí' : 'no', $s->views, $s->created_at?->format('Y-m-d')],
            ],
            'requests' => [
                ['fecha', 'servicio', 'cliente', 'celular', 'zona', 'estado', 'proveedor'],
                fn () => \App\Models\ServiceRequest::query()->with(['service:id,name', 'provider:id,name'])->orderBy('id')->lazyById(500),
                fn ($r) => [$r->created_at?->format('Y-m-d H:i'), $r->service?->name, $r->name, $r->phone, $r->zoneLabel(), $r->statusLabel(), $r->provider?->name],
            ],
        ];

        abort_unless(isset($exports[$type]), 404);
        [$header, $rows, $map] = $exports[$type];

        \App\Support\AdminLog::record('Descargó datos', $type);

        return response()->streamDownload(function () use ($header, $rows, $map): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $header, ';');
            foreach ($rows() as $row) {
                fputcsv($output, array_map(fn ($cell) => self::safeCell($cell === null ? null : (string) $cell), $map($row)), ';');
            }
            fclose($output);
        }, $type . '-malakplace-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    /**
     * Lo que escribe la gente no debe ejecutarse como fórmula al abrir el archivo en Excel.
     */
    private static function safeCell(?string $value): ?string
    {
        return $value !== null && preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'" . $value : $value;
    }
}
