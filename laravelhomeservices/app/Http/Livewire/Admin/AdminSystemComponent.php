<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\AdminLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

/**
 * Estado del sistema: ¿funciona todo? Sin entrar a Vercel ni a Supabase.
 * Las mismas comprobaciones que corre el cron diario, a pedido, en lenguaje simple.
 */
class AdminSystemComponent extends Component
{
    public const CHECKS = [
        'storage:check' => ['Fotos y documentos', 'Sube, lee y borra un archivo de prueba en los dos almacenes.', 'Si falla, nadie puede subir fotos ni comprobantes. Hay que revisar en Supabase que los almacenes «media» y «privado» existan, y en Vercel que las claves AWS_* sigan vigentes.'],
        'mail:check' => ['Correo', 'Entra al servidor de correo sin enviar nada.', 'Si falla, no salen avisos ni correos para recuperar la contraseña. Casi siempre es la contraseña de aplicación de Gmail: si se cambió o revocó en Google, hay que ponerla de nuevo en Vercel (MAIL_PASSWORD).'],
        'security:check' => ['Seguridad', 'Modo de errores apagado, cookies seguras y base cerrada a la API pública.', 'El detalle dice qué corregir. Envíaselo a quien mantiene el sitio.'],
    ];

    /** @var array<string, array{ok: bool, detail: string}> */
    public array $results = [];

    public function runChecks(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);

        foreach (array_keys(self::CHECKS) as $command) {
            try {
                $code = Artisan::call($command);
                $output = trim(Artisan::output());
            } catch (Throwable $exception) {
                $code = 1;
                $output = $exception->getMessage();
            }

            $this->results[$command] = ['ok' => $code === 0, 'detail' => Str::limit($output, 400)];
        }

        $failed = count(array_filter($this->results, fn (array $result) => ! $result['ok']));
        AdminLog::record('Revisó el estado del sistema', null, $failed === 0 ? 'Todo bien' : $failed . ' con problemas');
    }

    /**
     * Copia de seguridad a pedido, con prueba de restauración. Tarda unos segundos.
     */
    public function backupNow(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);

        try {
            $code = Artisan::call('backup:run', ['--test-restore' => true]);
        } catch (Throwable $exception) {
            report($exception);
            $code = 1;
        }

        AdminLog::record('Hizo una copia de seguridad', null, $code === 0 ? 'Bien' : 'Falló');
        session()->flash($code === 0 ? 'message' : 'error', $code === 0
            ? 'Copia hecha y probada: se restauró completa en un espacio aparte.'
            : 'La copia falló. Abajo dice por qué.');
    }

    public function render()
    {
        $cron = Cache::get('system:last_cron');
        $backup = Cache::get('system:last_backup');

        try {
            $database = DB::connection()->getDriverName();
            DB::select('select 1');
            $databaseOk = true;
        } catch (Throwable) {
            $databaseOk = false;
        }

        return view('livewire.admin.admin-system-component', [
            'checks' => self::CHECKS,
            'cron' => is_array($cron) ? $cron + ['when' => Carbon::parse($cron['at'])] : null,
            'backup' => is_array($backup) ? [
                'ok' => (bool) ($backup['ok'] ?? false),
                'when' => Carbon::parse($backup['at']),
                'rows' => (int) ($backup['rows'] ?? 0),
                'size' => Number::fileSize((int) ($backup['size'] ?? 0)),
                'error' => Str::limit((string) ($backup['error'] ?? ''), 200),
                'lastOk' => ($backup['last_ok'] ?? null) ? Carbon::parse($backup['last_ok']) : null,
                'tested' => ($backup['restore_tested_at'] ?? null) ? Carbon::parse($backup['restore_tested_at']) : null,
            ] : null,
            'database' => $database ?? '—',
            'databaseOk' => $databaseOk,
            'openErrors' => $databaseOk ? (int) DB::table('error_logs')->whereNull('resolved_at')->count() : 0,
            'version' => [
                'Versión publicada' => ($sha = env('VERCEL_GIT_COMMIT_SHA')) ? substr($sha, 0, 7) . ' · ' . Str::limit((string) env('VERCEL_GIT_COMMIT_MESSAGE'), 70) : 'Local (sin Vercel)',
                'Entorno' => app()->environment(),
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Base de datos' => $database ?? '—',
            ],
            'counts' => $databaseOk ? [
                'Cuentas' => User::count(),
                'Servicios' => Service::count(),
                'Solicitudes' => ServiceRequest::count(),
                'Reseñas' => Review::count(),
            ] : [],
            'exports' => [
                'users' => 'Cuentas',
                'services' => 'Servicios',
                'requests' => 'Solicitudes',
            ],
        ])->layout('layouts.base')->title('Estado del sistema');
    }
}
