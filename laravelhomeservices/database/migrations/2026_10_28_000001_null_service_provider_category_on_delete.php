<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a service category must not delete provider profiles: the
 * provider simply loses its category and can pick a new one.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot drop foreign keys; fresh installs get the rule from the create migration.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->dropForeign(['service_category_id']);
            $table->foreign('service_category_id')->references('id')->on('service_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->dropForeign(['service_category_id']);
            $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('cascade');
        });
    }
};
