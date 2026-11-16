<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Historial de lo que hace la administración (/admin/history): quién, qué, sobre qué y cuándo.
 * Sirve para saber quién cambió algo y para detectar un acceso indebido. Nunca falla la acción.
 */
final class AdminLog
{
    public static function record(string $action, ?string $subject = null, ?string $details = null): void
    {
        try {
            DB::table('admin_logs')->insert([
                'user_id' => Auth::id(),
                'action' => Str::limit($action, 117),
                'subject' => $subject === null ? null : Str::limit($subject, 250),
                'details' => $details === null ? null : Str::limit($details, 2000),
                'ip' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
