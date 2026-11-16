<?php

declare(strict_types=1);

use App\Support\SupabaseLockdown;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Para operar el sitio desde el panel sin mirar los registros del servidor:
 * - admin_logs: quién de la administración hizo qué y cuándo (/admin/history).
 * - error_logs: los errores del sitio, agrupados por lugar (/admin/errors).
 * Solo crea tablas nuevas: se puede correr antes de desplegar el código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 120);
            $table->string('subject')->nullable();
            $table->text('details')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });

        Schema::create('error_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->string('exception');
            $table->text('message');
            $table->string('location')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->index(['resolved_at', 'last_seen_at']);
        });

        // Las tablas nuevas también quedan cerradas a la API pública de Supabase.
        SupabaseLockdown::apply();
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
        Schema::dropIfExists('admin_logs');
    }
};
