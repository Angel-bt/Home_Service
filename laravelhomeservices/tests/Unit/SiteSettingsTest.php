<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Los ajustes del panel reemplazan a config/*.php sin romper nada si faltan o vienen mal.
 */
class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_field_points_to_an_existing_config_key_and_group(): void
    {
        foreach (SiteSettings::FIELDS as $key => [$group, $configKey, $label, , $type, $rules, $cast]) {
            $this->assertArrayHasKey($group, SiteSettings::GROUPS, $key);
            $this->assertTrue(\Illuminate\Support\Arr::has(config()->all(), $configKey), "{$key} → {$configKey}");
            $this->assertNotSame('', $label);
            $this->assertContains($type, ['text', 'tel', 'email', 'number']);
            $this->assertContains($cast, ['string', 'int', 'float']);
            $this->assertNotEmpty($rules);
        }
    }

    public function test_saved_values_are_typed_and_survive_a_new_request(): void
    {
        SiteSettings::save(['free_service_limit' => '7', 'pro_price' => '12.5', 'contact_email' => 'nuevo@example.test', 'bank_name' => '']);

        config(['marketplace.free_service_limit' => 3, 'billing.pro_price' => 8.0]);
        Cache::flush();
        SiteSettings::apply();

        $this->assertSame(7, config('marketplace.free_service_limit'));
        $this->assertSame(12.5, config('billing.pro_price'));
        $this->assertSame('nuevo@example.test', config('marketplace.contact_email'));
        $this->assertNull(config('billing.bank_transfer.bank'), 'vacío se guarda como nulo');
    }

    public function test_untouched_settings_keep_the_value_from_config(): void
    {
        config(['marketplace.contact_phone' => '0995528556']);

        SiteSettings::save(['free_service_limit' => '5']);

        $this->assertSame('0995528556', config('marketplace.contact_phone'));
    }

    public function test_zones_and_main_zone(): void
    {
        SiteSettings::save([SiteSettings::ZONES_KEY => ['malacatos' => 'Malacatos', 'san-pedro' => 'San Pedro'], SiteSettings::MAIN_ZONE_KEY => 'san-pedro']);

        $this->assertSame(['malacatos' => 'Malacatos', 'san-pedro' => 'San Pedro'], config('marketplace.zones'));
        $this->assertSame('san-pedro', config('marketplace.main_zone'));
    }

    public function test_broken_zone_data_is_ignored(): void
    {
        $zones = config('marketplace.zones');
        DB::table('settings')->insert([['key' => SiteSettings::ZONES_KEY, 'value' => 'no es json'], ['key' => SiteSettings::MAIN_ZONE_KEY, 'value' => 'no-existe']]);

        Cache::flush();
        SiteSettings::apply();

        $this->assertSame($zones, config('marketplace.zones'));
        $this->assertNotSame('no-existe', config('marketplace.main_zone'));
    }

    public function test_a_missing_table_does_not_break_the_site(): void
    {
        $before = config('marketplace.free_service_limit');
        Schema::drop('settings');
        Cache::flush();

        SiteSettings::apply();

        $this->assertSame($before, config('marketplace.free_service_limit'));
        $this->assertFalse(Cache::has(SiteSettings::CACHE_KEY), 'un fallo no se guarda en caché');
    }
}
