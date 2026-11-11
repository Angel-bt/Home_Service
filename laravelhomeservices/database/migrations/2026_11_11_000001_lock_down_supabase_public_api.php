<?php

declare(strict_types=1);

use App\Support\SupabaseLockdown;
use Illuminate\Database\Migrations\Migration;

/**
 * Cierra la API pública de Supabase sobre las tablas de Malakplace (ver App\Support\SupabaseLockdown).
 * En MySQL, SQLite o un Postgres que no es Supabase no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        SupabaseLockdown::apply();
    }

    public function down(): void
    {
        // A propósito no se reabre la API pública.
    }
};
