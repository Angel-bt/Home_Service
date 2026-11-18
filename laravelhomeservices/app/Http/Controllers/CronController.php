<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Scheduled tasks for hosts without a process running `schedule:run` (Vercel Cron calls this
 * URL sending CRON_SECRET as a bearer token). Without a configured secret the route does not exist.
 * Answers 500 when the backup, storage, mail or security checks fail, so it stands out in the host's logs.
 */
class CronController extends Controller
{
    public function daily(Request $request): JsonResponse
    {
        $secret = (string) config('services.cron.secret');

        abort_if($secret === '' || ! hash_equals($secret, (string) $request->bearerToken()), 404);

        // La copia va primero: es lo más importante y no depende de lo demás.
        // Los lunes, además, se prueba restaurarla en un esquema temporal.
        $backup = Artisan::call('backup:run', now()->isMonday() ? ['--test-restore' => true] : []);
        $storage = Artisan::call('storage:check');
        $mail = Artisan::call('mail:check');
        $security = Artisan::call('security:check');

        $results = [
            'backup:run' => $backup,
            'billing:expire-plans' => Artisan::call('billing:expire-plans'),
            'activity:prune' => Artisan::call('activity:prune'),
            'services:sync-visit-counters' => config('marketplace.visit_counter') === 'redis' ? Artisan::call('services:sync-visit-counters') : 'not needed',
            'storage:check' => $storage,
            'mail:check' => $mail,
            'security:check' => $security,
        ];
        $ok = $backup === 0 && $storage === 0 && $mail === 0 && $security === 0;

        // Para «Estado del sistema»: cuándo corrió y cómo salió.
        try {
            \Illuminate\Support\Facades\Cache::forever('system:last_cron', ['at' => now()->toIso8601String(), 'ok' => $ok, 'results' => $results]);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json($results, $ok ? 200 : 500);
    }
}
