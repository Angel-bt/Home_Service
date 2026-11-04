<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The marketplace now covers only Malacatos and its neighbouring parishes
 * (Vilcabamba, Yangana, Quinara). Provider zones outside that list are removed;
 * old service requests keep their zone text for history.
 */
return new class extends Migration
{
    private const ZONES = ['malacatos', 'vilcabamba', 'yangana', 'quinara'];

    public function up(): void
    {
        DB::table('provider_zones')->whereNotIn('zone', self::ZONES)->delete();
    }

    public function down(): void
    {
        // Removed zones cannot be restored.
    }
};
