<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Admin\AdminDashboardComponent;
use App\Http\Livewire\Admin\AdminFaqComponent;
use App\Http\Livewire\Admin\AdminPaymentsComponent;
use App\Http\Livewire\Admin\AdminReviewsComponent;
use App\Http\Livewire\Admin\AdminSettingsComponent;
use App\Http\Livewire\Admin\AdminUsersComponent;
use App\Http\Livewire\Admin\AdminVerificationsComponent;
use App\Http\Livewire\Admin\Service\AdminAddServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminAddServiceComponent;
use App\Http\Livewire\Admin\Service\AdminAddSlideComponent;
use App\Http\Livewire\Admin\Service\AdminContactComponent;
use App\Http\Livewire\Admin\Service\AdminEditServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminEditServiceComponent;
use App\Http\Livewire\Admin\Service\AdminServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminServicesByCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminServicesComponent;
use App\Http\Livewire\Admin\Service\AdminSliderComponent;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminCatalogAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = new ServiceCategory();
        $this->category->forceFill(['name' => 'Jardinería', 'slug' => 'jardineria', 'image' => 'jardineria.png'])->save();
    }

    private function admin(): User
    {
        return User::factory()->create(['utype' => 'ADM']);
    }

    private function provider(): User
    {
        $user = User::factory()->create(['utype' => 'SVP', 'phone' => '0991112233']);
        ServiceProvider::create(['user_id' => $user->id]);

        return $user;
    }

    private function service(User $owner, string $name = 'Poda de árboles'): Service
    {
        $service = new Service();
        $service->forceFill([
            'user_id' => $owner->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'tagline' => 'x',
            'service_category_id' => $this->category->id, 'price' => 20, 'image' => 'i.png', 'thumbnail' => 't.png',
        ])->save();

        return $service;
    }

    // --- Catálogo ---

    public function test_the_admin_creates_edits_and_deletes_a_category(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(AdminAddServiceCategoryComponent::class)
            ->call('createNewCategory')->assertHasErrors(['name', 'slug', 'image'])
            ->set('name', 'Cerrajería')->call('generateSlug')
            ->set('image', UploadedFile::fake()->image('llave.png'))
            ->call('createNewCategory')->assertHasNoErrors();

        $category = ServiceCategory::where('slug', 'cerrajeria')->firstOrFail();
        Storage::disk('local')->assertExists('categories/' . $category->image);

        Livewire::actingAs($admin)->test(AdminEditServiceCategoryComponent::class, ['category_id' => $category->id])
            ->set('name', 'Cerrajería y llaves')->call('generateSlug')
            ->call('updateServiceCategory')->assertHasNoErrors();
        $this->assertSame('cerrajeria-y-llaves', $category->fresh()->slug);
        $this->get('/cerrajeria-y-llaves/services')->assertOk()->assertSee('Cerrajería y llaves');

        $service = $this->service($this->provider(), 'Copia de llaves');
        $service->forceFill(['service_category_id' => $category->id])->save();

        Livewire::actingAs($admin)->test(AdminServiceCategoryComponent::class)->call('deleteServiceCategory', $category->id);
        $this->assertModelMissing($category);
        $this->assertModelMissing($service);
    }

    public function test_the_admin_publishes_and_edits_a_service_for_every_zone(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(AdminAddServiceComponent::class)
            ->set('name', 'Revisión de techos')->call('generateSlug')
            ->set('service_category_id', $this->category->id)
            ->set('price', '25')
            ->set('description', 'Revisamos goteras y tejas.')
            ->set('image', UploadedFile::fake()->image('techo.jpg'))
            ->call('createService')
            ->assertHasNoErrors();

        $service = Service::where('slug', 'revision-de-techos')->firstOrFail();
        $this->assertSame($admin->id, $service->user_id);
        $this->assertNotEmpty($service->thumbnail, 'sin miniatura aparte, usa la misma foto');
        Storage::disk('local')->assertExists('services/thumbnails/' . $service->thumbnail);

        // Los servicios de la administración salen en todas las zonas.
        $this->get('/buscar?zona=quinara')->assertSee('Revisión de techos');

        Livewire::actingAs($admin)->test(AdminEditServiceComponent::class, ['service_slug' => $service->slug])
            ->set('price', '30')->set('discount', '10')->set('discount_type', 'percent')
            ->call('updateService')->assertHasNoErrors();
        $this->assertSame(27.0, $service->fresh()->finalPrice());
    }

    public function test_the_category_list_actions_work_on_services(): void
    {
        $service = $this->service($this->provider());

        Livewire::actingAs($this->admin())->test(AdminServicesByCategoryComponent::class, ['category_slug' => 'jardineria'])
            ->assertSee('Poda de árboles')
            ->call('toggleFeatured', $service->id)
            ->call('toggleVisible', $service->id);

        $this->assertTrue($service->fresh()->featured);
        $this->assertFalse($service->fresh()->status);
    }

    // --- Permisos ---

    /**
     * Cada acción del panel de administración, llamada por alguien que no es admin.
     */
    public static function adminActions(): array
    {
        return [
            'crear categoría' => [AdminAddServiceCategoryComponent::class, [], 'createNewCategory', []],
            'editar categoría' => [AdminEditServiceCategoryComponent::class, ['category_id' => 'CATEGORY'], 'updateServiceCategory', []],
            'borrar categoría' => [AdminServiceCategoryComponent::class, [], 'deleteServiceCategory', ['CATEGORY']],
            'crear servicio' => [AdminAddServiceComponent::class, [], 'createService', []],
            'editar servicio' => [AdminEditServiceComponent::class, ['service_slug' => 'SERVICE_SLUG'], 'updateService', []],
            'borrar servicio' => [AdminServicesComponent::class, [], 'deleteService', ['SERVICE']],
            'ocultar servicio' => [AdminServicesComponent::class, [], 'toggleVisible', ['SERVICE']],
            'destacar servicio' => [AdminServicesComponent::class, [], 'toggleFeatured', ['SERVICE']],
            'borrar imagen de portada' => [AdminSliderComponent::class, [], 'deleteSlide', [1]],
            'crear imagen de portada' => [AdminAddSlideComponent::class, [], 'addNewSlide', []],
            'aprobar pago' => [AdminPaymentsComponent::class, [], 'approve', ['PAYMENT']],
            'pago en efectivo' => [AdminPaymentsComponent::class, [], 'recordCashPayment', []],
            'aprobar documento' => [AdminVerificationsComponent::class, [], 'approve', [1]],
            'reactivar cuenta' => [AdminUsersComponent::class, [], 'reactivate', ['OWNER']],
            'cambiar tipo de cuenta' => [AdminUsersComponent::class, [], 'changeRole', ['OWNER', 'ADM']],
            'eliminar cuenta' => [AdminUsersComponent::class, [], 'deleteUser', ['OWNER']],
            'ocultar reseña' => [AdminReviewsComponent::class, [], 'show', [1]],
            'marcar mensaje' => [AdminContactComponent::class, [], 'toggleAnswered', [1]],
            'borrar pregunta' => [AdminFaqComponent::class, [], 'delete', [1]],
            'guardar ajustes' => [AdminSettingsComponent::class, [], 'save', []],
            'agregar zona' => [AdminSettingsComponent::class, [], 'addZone', []],
            'reiniciar visitas' => [AdminDashboardComponent::class, [], 'resetVisits', []],
        ];
    }

    #[DataProvider('adminActions')]
    public function test_no_admin_action_works_for_other_roles(string $component, array $mountParams, string $method, array $params): void
    {
        $owner = $this->provider();
        $service = $this->service($owner);
        $payment = SubscriptionPayment::create(['user_id' => $owner->id, 'method' => 'cash', 'periods' => 1, 'amount' => 8, 'status' => SubscriptionPayment::PENDING]);

        $swap = fn (array $values) => array_map(fn ($value) => match ($value) {
            'CATEGORY' => $this->category->id,
            'SERVICE' => $service->id,
            'SERVICE_SLUG' => $service->slug,
            'PAYMENT' => $payment->id,
            'OWNER' => $owner->id,
            default => $value,
        }, $values);

        foreach ([$owner, User::factory()->create(['utype' => 'CST'])] as $intruder) {
            Livewire::actingAs($intruder)->test($component, $swap($mountParams))
                ->call($method, ...$swap($params))
                ->assertForbidden();
        }

        $this->assertModelExists($service);
        $this->assertModelExists($this->category);
        $this->assertSame('SVP', $owner->fresh()->utype);
        $this->assertSame(SubscriptionPayment::PENDING, $payment->fresh()->status);
    }

    public function test_the_role_check_also_runs_on_real_livewire_requests(): void
    {
        $admin = $this->admin();
        $html = $this->actingAs($admin)->get('/admin/service-categories')->assertOk()->getContent();

        preg_match('/wire:snapshot="([^"]+)"[^>]*wire:id="[^"]+"[^>]*>/', $html, $match);
        $this->assertNotEmpty($match, 'la página debe tener el componente de categorías');
        $snapshot = html_entity_decode($match[1]);
        $this->assertStringContainsString('admin-service-category', $snapshot);

        $deleteRequest = fn () => $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson('/livewire/update', ['components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['path' => '', 'method' => 'deleteServiceCategory', 'params' => [$this->category->id]]],
            ]]]);

        // La misma petición que haría el navegador, pero con la sesión de un proveedor: no borra nada.
        $this->actingAs($this->provider());
        $deleteRequest();
        $this->assertModelExists($this->category);

        // Control: la misma petición hecha por la administración sí borra. Así la prueba no pasa por un error ajeno.
        $this->actingAs($admin);
        $deleteRequest()->assertOk();
        $this->assertModelMissing($this->category);
    }

    // --- Tareas automáticas y contador ---

    public function test_expired_pro_plans_go_back_to_free(): void
    {
        $expired = $this->provider();
        $expired->forceFill(['subscription_plan' => 'pro', 'plan_expires_at' => now()->subDay()])->save();
        $active = $this->provider();
        $active->forceFill(['subscription_plan' => 'pro', 'plan_expires_at' => now()->addWeek()])->save();

        $this->artisan('billing:expire-plans')->assertSuccessful();

        $this->assertSame('free', $expired->fresh()->subscription_plan);
        $this->assertSame('pro', $active->fresh()->subscription_plan);
    }

    public function test_the_visit_counter_counts_once_per_session_and_only_the_admin_resets_it(): void
    {
        $before = (int) Visit::counter()->visits;

        $this->get('/increment-visit')->assertStatus(405);
        $this->post('/increment-visit')->assertOk();
        $this->post('/increment-visit')->assertOk();
        $this->assertSame($before + 1, (int) Visit::counter()->fresh()->visits);

        $this->post('/reset-visits')->assertRedirect(route('login'));

        Livewire::actingAs($this->admin())->test(AdminDashboardComponent::class)->call('resetVisits');
        $this->assertSame(0, (int) Visit::counter()->fresh()->visits);
    }

    public function test_the_counter_keeps_counting_when_its_row_is_not_number_one(): void
    {
        Visit::query()->delete();
        \Illuminate\Support\Facades\DB::table('visit_counter')->insert(['id' => 7, 'visitor_name' => 'Sistema', 'visits' => 40, 'created_at' => now(), 'updated_at' => now()]);

        $this->post('/increment-visit')->assertOk();

        $this->assertSame(1, Visit::count(), 'no debe crear filas nuevas');
        $this->assertSame(41, (int) Visit::counter()->visits);
    }

    public function test_autocomplete_only_suggests_what_people_can_see(): void
    {
        $owner = $this->provider();
        $this->service($owner, 'Poda de árboles');
        $this->service($owner, 'Poda de setos')->forceFill(['paused_at' => now()])->save();

        $this->getJson('/autocomplete?query=poda')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Poda de árboles'])
            ->assertJsonMissing(['name' => 'Poda de setos']);
    }
}
