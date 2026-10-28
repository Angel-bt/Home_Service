<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table): void {
            $table->string('whatsapp', 20)->nullable()->after('city');
        });

        Schema::create('provider_zones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->cascadeOnDelete();
            $table->string('zone', 50);
            $table->timestamps();

            $table->unique(['service_provider_id', 'zone']);
            $table->index('zone');
        });

        Schema::create('service_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('zone', 50);
            $table->string('address')->nullable();
            $table->date('preferred_date')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['provider_id', 'status']);
            $table->index(['customer_id', 'status']);
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('provider_zones');

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->dropColumn('whatsapp');
        });
    }
};
