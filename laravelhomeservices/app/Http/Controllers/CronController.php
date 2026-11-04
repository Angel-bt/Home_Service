<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Scheduled tasks for hosts without a process running `schedule:run` (Vercel Cron calls this
 * URL sending CRON_SECRET as a bearer token). Without a configured secret the route does not exist.
 */
class CronController extends Controller
{
    public function daily(Request $request): JsonResponse
    {
        $secret = (string) config('services.cron.secret');

        abort_if($secret === '' || ! hash_equals($secret, (string) $request->bearerToken()), 404);

        return response()->json([
            'billing:expire-plans' => Artisan::call('billing:expire-plans'),
            'services:sync-visit-counters' => config('marketplace.visit_counter') === 'redis' ? Artisan::call('services:sync-visit-counters') : 'not needed',
        ]);
    }
}
