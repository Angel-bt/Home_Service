<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * /salud: lo que consulta el vigilante externo (GitHub Actions, cada 15 minutos).
 * 200 si el sitio responde, la base contesta y la revisión diaria salió bien en las últimas 36 horas;
 * 503 si no, con el nombre de lo que falla y nada más (el detalle está en «Estado del sistema»).
 */
final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $problems = [];

        try {
            DB::select('select 1');
        } catch (Throwable) {
            $problems[] = 'base de datos';
        }

        try {
            $cron = Cache::get('system:last_cron');
            if (is_array($cron) && ! ($cron['ok'] ?? false)) {
                $problems[] = 'revisión diaria';
            }

            $backup = Cache::get('system:last_backup');
            if (is_array($backup) && (! ($backup['last_ok'] ?? null) || now()->parse($backup['last_ok'])->lt(now()->subHours(36)))) {
                $problems[] = 'copias de seguridad';
            }
        } catch (Throwable) {
            $problems[] = 'caché';
        }

        return response()
            ->json($problems === [] ? ['estado' => 'bien'] : ['estado' => 'con problemas', 'falla' => $problems], $problems === [] ? 200 : 503)
            ->header('Cache-Control', 'no-store');
    }
}
