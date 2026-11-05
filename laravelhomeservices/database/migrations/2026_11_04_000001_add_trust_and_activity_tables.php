<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aceptación de términos, verificación voluntaria de proveedores y registro de actividad.
 * Solo agrega columnas opcionales y tablas nuevas: se puede correr antes de desplegar el código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('terms_version', 20)->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->timestamp('publication_consent_at')->nullable();
        });

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->timestamp('background_checked_at')->nullable();
            $table->timestamp('background_valid_until')->nullable();
            $table->timestamp('cv_reviewed_at')->nullable();
        });

        Schema::create('provider_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            // Ruta en el disco privado. Queda vacía apenas se revisa: el archivo se borra.
            $table->string('path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->timestamp('consent_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('activity_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 30);
            $table->string('term', 100)->nullable();
            $table->unsignedInteger('results')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_role', 3)->nullable();
            $table->string('zone', 30)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['event', 'created_at']);
            $table->index(['provider_id', 'event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_events');
        Schema::dropIfExists('provider_documents');

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->dropColumn(['experience_years', 'identity_verified_at', 'background_checked_at', 'background_valid_until', 'cv_reviewed_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['terms_version', 'terms_accepted_at', 'publication_consent_at']);
        });
    }
};
