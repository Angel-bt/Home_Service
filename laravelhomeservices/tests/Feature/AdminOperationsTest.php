<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Admin\AdminContentComponent;
use App\Http\Livewire\Admin\AdminErrorsComponent;
use App\Http\Livewire\Admin\AdminHistoryComponent;
use App\Http\Livewire\Admin\AdminLegalComponent;
use App\Http\Livewire\Admin\AdminSettingsComponent;
use App\Http\Livewire\Admin\AdminSystemComponent;
use App\Http\Livewire\Admin\AdminUsersComponent;
use App\Http\Livewire\Sprovider\SproviderPlanComponent;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\Content;
use App\Support\ErrorRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['utype' => 'ADM']);
    }

    // --- Textos y aviso ---

    public function test_the_admin_edits_the_home_texts_and_nothing_can_inject_code(): void
    {
        Livewire::actingAs($this->admin())->test(AdminContentComponent::class)
            ->set('values.hero_title', 'Tu vecino que *sabe arreglar* <script>alert(1)</script>')
            ->set('values.seo_title', 'Malakplace · Prueba')
            ->call('save')
            ->assertHasNoErrors();

        auth()->logout();
        $this->get('/')
            ->assertSee('<span class="lp-accent text-oro-300">sabe arreglar</span>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('<title>Malakplace · Prueba</title>', false);
    }

    public function test_the_announcement_shows_everywhere_only_when_turned_on(): void
    {
        $admin = $this->admin();
        $component = Livewire::actingAs($admin)->test(AdminContentComponent::class)
            ->set('values.announcement_enabled', true)
            ->set('values.announcement_text', '')
            ->call('save')
            ->assertHasErrors('values.announcement_text');

        $component->set('values.announcement_text', 'Este feriado respondemos desde el lunes.')->call('save')->assertHasNoErrors();
        auth()->logout();
        $this->get('/')->assertSee('Este feriado respondemos desde el lunes.');
        $this->get('/faq')->assertSee('Este feriado respondemos desde el lunes.');

        $this->actingAs($admin);
        $component->set('values.announcement_enabled', false)->call('save');
        $this->app['auth']->forgetGuards();
        $this->get('/')->assertDontSee('Este feriado respondemos desde el lunes.');
    }

    public function test_payment_methods_follow_the_settings(): void
    {
        Livewire::actingAs($this->admin())->test(AdminSettingsComponent::class)
            ->set('values.pay_transfer', false)
            ->set('values.pay_cash', false)
            ->call('save')
            ->assertHasErrors('values.pay_cash')
            ->set('values.pay_cash', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([SubscriptionPayment::METHOD_CASH], SubscriptionPayment::enabledManualMethods());

        $provider = User::factory()->create(['utype' => 'SVP']);
        Livewire::actingAs($provider)->test(SproviderPlanComponent::class)
            ->assertSet('method', SubscriptionPayment::METHOD_CASH)
            ->assertDontSee('Transferencia')
            ->set('method', SubscriptionPayment::METHOD_TRANSFER)
            ->call('submitReceipt')
            ->assertHasErrors('method');
    }

    // --- Textos legales ---

    public function test_a_correction_keeps_the_version_and_a_new_version_asks_everyone_again(): void
    {
        $customer = User::factory()->create(['utype' => 'CST']);
        $version = config('marketplace.terms_version');
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(AdminLegalComponent::class)
            ->set('texts.legal_terms', "## Qué es Malakplace\n\nUn texto **corregido**.")
            ->call('saveCorrection')
            ->assertHasNoErrors();
        $this->assertSame($version, config('marketplace.terms_version'));
        $this->actingAs($customer)->get('/customer/dashboard')->assertOk();

        $this->actingAs($admin);
        $component->call('publishVersion');
        $this->assertSame(now()->format('Y-m-d'), config('marketplace.terms_version'));
        $component->call('publishVersion');
        $this->assertSame(now()->format('Y-m-d') . '-2', config('marketplace.terms_version'));

        $this->actingAs($customer)->get('/customer/dashboard')->assertRedirect(route('terms.accept'));
        $this->app['auth']->forgetGuards();
        $this->get('/terms')->assertSee('<h2 id="que-es-malakplace" class="scroll-mt-28">Qué es Malakplace</h2>', false)->assertSee('<strong>corregido</strong>', false);
    }

    public function test_legal_texts_cannot_carry_code_or_dangerous_links(): void
    {
        $html = (string) Content::markdown("Hola <script>alert(1)</script> [clic](javascript:alert(1)) escríbenos a {correo}");

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<a href="mailto:' . config('marketplace.contact_email') . '">', $html);
    }

    public function test_registration_shows_the_edited_summary_and_consent(): void
    {
        Livewire::actingAs($this->admin())->test(AdminLegalComponent::class)
            ->set('texts.legal_summary_all', "**Algo nuevo**\nEl resumen cambió.")
            ->set('texts.legal_consent', 'Acepto que mi perfil se publique.')
            ->call('saveCorrection');

        auth()->logout();
        $this->get('/register')->assertSee('El resumen cambió.')->assertSee('Acepto que mi perfil se publique.');
    }

    public function test_the_original_text_can_be_brought_back(): void
    {
        Livewire::actingAs($this->admin())->test(AdminLegalComponent::class)
            ->set('texts.legal_terms', 'otro')
            ->call('restoreOriginal')
            ->assertSet('texts.legal_terms', file_get_contents(resource_path('legal/terminos.md')));
    }

    // --- Errores, historial y estado del sistema ---

    public function test_errors_are_grouped_and_come_back_if_they_happen_again(): void
    {
        $make = fn () => new RuntimeException('El correo no respondió');
        ErrorRecorder::capture($make());
        ErrorRecorder::capture($make());

        $this->assertSame(1, DB::table('error_logs')->count());
        $this->assertSame(2, (int) DB::table('error_logs')->value('count'));

        $component = Livewire::actingAs($this->admin())->test(AdminErrorsComponent::class)
            ->assertSee('El correo no respondió')
            ->call('resolveAll')
            ->assertSee('Sin errores por revisar');

        ErrorRecorder::capture($make());
        $this->assertNull(DB::table('error_logs')->value('resolved_at'));
        $component->call('$refresh')->assertSee('El correo no respondió');
    }

    public function test_a_new_kind_of_error_emails_the_admins_once_and_with_a_limit(): void
    {
        \Illuminate\Support\Facades\Bus::fake([\App\Jobs\SendNoticeEmail::class]);
        $admin = $this->admin();
        User::factory()->create(['utype' => 'ADM', 'suspended_at' => now()]);
        User::factory()->create(['utype' => 'SVP']);

        $first = new RuntimeException('La base no respondió');
        ErrorRecorder::capture($first);
        ErrorRecorder::capture($first);

        // Un aviso, solo al admin activo, aunque el error se repita.
        \Illuminate\Support\Facades\Bus::assertDispatchedTimes(\App\Jobs\SendNoticeEmail::class, 1);
        \Illuminate\Support\Facades\Bus::assertDispatched(\App\Jobs\SendNoticeEmail::class, fn ($job) => $job->to === $admin->email);

        // Si todo se rompe a la vez (diez errores distintos), no más de ALERTS_PER_HOUR correos por hora.
        foreach (range(1, 10) as $line) {
            $exception = new RuntimeException('fallo ' . $line);
            (new \ReflectionProperty(\Exception::class, 'line'))->setValue($exception, $line);
            ErrorRecorder::capture($exception);
        }

        $this->assertSame(11, DB::table('error_logs')->count());
        \Illuminate\Support\Facades\Bus::assertDispatchedTimes(\App\Jobs\SendNoticeEmail::class, ErrorRecorder::ALERTS_PER_HOUR);
    }

    public function test_in_production_the_admin_panel_requires_two_step_verification(): void
    {
        config(['marketplace.admin_two_factor' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.system'))
            ->assertRedirect(route('profile.show') . '#dos-pasos')
            ->assertSessionHas('error');
        $this->actingAs($admin)->get(route('profile.show'))->assertOk()->assertSee('Verificación en dos pasos');

        // Activada a medias (sin confirmar el código) todavía no alcanza.
        $admin->forceFill(['two_factor_secret' => encrypt('secreto'), 'two_factor_recovery_codes' => encrypt('[]')])->save();
        $this->actingAs($admin)->get(route('admin.system'))->assertRedirect();

        $admin->forceFill(['two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin)->get(route('admin.system'))->assertOk();

        // Al ingresar, la contraseña sola ya no alcanza: pide el código.
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/two-factor-challenge');
        $this->get('/two-factor-challenge')->assertOk()->assertSee('Escribe el código de 6 números');
        $this->assertGuest('web');

        // Los demás paneles no la exigen.
        $this->actingAs(User::factory()->create(['utype' => 'SVP']))->get(route('sprovider.dashboard'))->assertOk();
    }

    public function test_reported_errors_reach_the_admin_page(): void
    {
        report(new RuntimeException('Algo falló al guardar'));

        $this->assertSame('Algo falló al guardar', DB::table('error_logs')->value('message'));
    }

    public function test_admin_actions_are_kept_in_the_history(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['utype' => 'CST', 'email' => 'vecino@example.test']);

        Livewire::actingAs($admin)->test(AdminUsersComponent::class)
            ->call('startSuspend', $customer->id)->set('suspendReason', 'Spam')->call('suspend');

        $this->assertDatabaseHas('admin_logs', ['user_id' => $admin->id, 'action' => 'Suspendió una cuenta', 'subject' => 'vecino@example.test', 'details' => 'Spam']);

        Livewire::actingAs($admin)->test(AdminHistoryComponent::class)
            ->assertSee('Suspendió una cuenta')
            ->set('q', 'VECINO')->assertSee('vecino@example.test')
            ->set('q', 'nada que ver')->assertDontSee('vecino@example.test');
    }

    public function test_the_system_page_runs_the_checks_and_shows_the_last_cron(): void
    {
        Storage::fake('local');
        Storage::fake('receipts');
        Storage::fake('backups');
        config(['services.cron.secret' => 'secreto', 'marketplace.visit_counter' => 'database']);
        $this->withToken('secreto')->get('/cron/diario')->assertOk();

        Livewire::actingAs($this->admin())->test(AdminSystemComponent::class)
            ->assertSee('todo bien')
            ->assertSee('Última copia')
            ->call('backupNow')
            ->assertSee('Restauración probada')
            ->call('runChecks')
            ->assertSet('results.security:check.ok', true)
            ->assertSet('results.mail:check.ok', true);

        $this->assertDatabaseHas('admin_logs', ['action' => 'Revisó el estado del sistema']);
    }

    public function test_data_exports_are_admin_only_and_safe_for_excel(): void
    {
        User::factory()->create(['utype' => 'CST', 'name' => '=HYPERLINK("x")', 'email' => 'dato@example.test']);

        $this->actingAs(User::factory()->create(['utype' => 'SVP']))->get(route('admin.export', ['type' => 'users']))->assertRedirect();

        $csv = $this->actingAs($this->admin())->get(route('admin.export', ['type' => 'users']))->assertOk()->streamedContent();
        $this->assertStringContainsString('dato@example.test', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDatabaseHas('admin_logs', ['action' => 'Descargó datos', 'subject' => 'users']);

        foreach (['services', 'requests'] as $type) {
            $this->get(route('admin.export', ['type' => $type]))->assertOk();
        }
        $this->get('/admin/descargas/otra-cosa.csv')->assertNotFound();
    }

    public function test_old_history_and_errors_are_pruned(): void
    {
        DB::table('admin_logs')->insert(['action' => 'viejo', 'created_at' => now()->subYears(2)]);
        DB::table('admin_logs')->insert(['action' => 'nuevo', 'created_at' => now()]);
        DB::table('error_logs')->insert(['fingerprint' => 'a', 'exception' => 'X', 'message' => 'revisado hace tiempo', 'count' => 1, 'last_seen_at' => now()->subMonths(2), 'resolved_at' => now()->subMonths(2)]);
        DB::table('error_logs')->insert(['fingerprint' => 'b', 'exception' => 'X', 'message' => 'reciente', 'count' => 1, 'last_seen_at' => now()]);

        $this->artisan('activity:prune')->assertSuccessful();

        $this->assertSame(['nuevo'], DB::table('admin_logs')->pluck('action')->all());
        $this->assertSame(['reciente'], DB::table('error_logs')->pluck('message')->all());
    }

    public function test_the_guide_links_to_every_screen(): void
    {
        $this->actingAs($this->admin())->get(route('admin.guide'))
            ->assertOk()
            ->assertSee('Aprobar un pago del plan Pro')
            ->assertSee('Lo que sí necesita a alguien técnico');
    }
}
