<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\SearchController;
use App\Http\Livewire\Admin\AdminDashboardComponent;
use App\Http\Livewire\Admin\AdminPaymentsComponent;
use App\Http\Livewire\Admin\Service\AdminAddServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminAddServiceComponent;
use App\Http\Livewire\Admin\Service\AdminAddSlideComponent;
use App\Http\Livewire\Admin\Service\AdminContactComponent;
use App\Http\Livewire\Admin\Service\AdminEditServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminEditServiceComponent;
use App\Http\Livewire\Admin\Service\AdminEditSlideComponent;
use App\Http\Livewire\Admin\Service\AdminServiceCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminServiceProvidersComponent;
use App\Http\Livewire\Admin\Service\AdminServicesByCategoryComponent;
use App\Http\Livewire\Admin\Service\AdminServicesComponent;
use App\Http\Livewire\Admin\Service\AdminSliderComponent;
use App\Http\Livewire\Admin\AdminAboutComponent;
use App\Http\Livewire\Admin\AdminFaqComponent;
use App\Http\Livewire\Admin\AdminTermsComponent;
use App\Http\Livewire\Admin\AdminPrivacyComponent;
use App\Http\Livewire\ContactComponent;
use App\Http\Livewire\Customer\CustomerDashboardComponent;
use App\Http\Livewire\HomeComponent;
use App\Http\Livewire\Service\ChangeLocationComponent;
use App\Http\Livewire\Service\SearchComponent;
use App\Http\Livewire\Service\ServiceCategoriesComponent;
use App\Http\Livewire\Service\ServiceDetailsComponent;
use App\Http\Livewire\Service\ServicesByCategoryComponent;
use App\Http\Livewire\Sprovider\EditSproviderProfileComponent;
use App\Http\Livewire\Sprovider\SproviderAddServiceComponent;
use App\Http\Livewire\Sprovider\SproviderPlanComponent;
use App\Http\Livewire\Sprovider\SproviderEditServiceComponent;
use App\Http\Livewire\Sprovider\SproviderRequestsComponent;
use App\Http\Livewire\Sprovider\SproviderServicesComponent;
use App\Http\Livewire\Sprovider\SproviderDashboardComponent;
use App\Http\Livewire\Sprovider\SproviderProfileComponent;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\AboutComponent;
use App\Http\Livewire\FaqComponent;
use App\Http\Livewire\TermsComponent;
use App\Http\Livewire\PrivacyComponent;
use App\Http\Controllers\VisitController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/', HomeComponent::class)->name('home');

Route::get('/service-category', ServiceCategoriesComponent::class)->name('home.service_categories');



Route::get('/change-location', ChangeLocationComponent::class)->name('home.change_location');

Route::get('/contact-us', ContactComponent::class)->name('home.contact');

Route::get('/about-us', AboutComponent::class)->name('home.about');

Route::get('/faq', FaqComponent::class)->name('home.faq');

Route::get('/terms', TermsComponent::class)->name('home.terms');
Route::get('/privacy', PrivacyComponent::class)->name('home.privacy');

// Public catalogue and search routes must remain accessible to visitors.
Route::get('/{category_slug}/services', ServicesByCategoryComponent::class)->name('home.services_by_category');
Route::get('/service/{service_slug}', ServiceDetailsComponent::class)->name('home.service_details');
Route::get('/service/{service_slug}/whatsapp', [\App\Http\Controllers\ServiceContactController::class, 'whatsapp'])->middleware('throttle:60,1')->name('home.service_whatsapp');
Route::get('/buscar', SearchComponent::class)->name('search');

// Retorno de Payphone: sin auth (la sesión puede haber expirado); el pago se valida contra Payphone.
Route::get('/billing/payphone/return', [BillingController::class, 'payphoneReturn'])->middleware('throttle:30,1')->name('billing.payphone.return');
Route::get('/autocomplete', [SearchController::class, 'autocomplete'])->name('autocomplete');
Route::post('/search', [SearchController::class, 'searchService'])->name('searchService');



// Tareas programadas en hosts sin schedule:run (Vercel Cron). Protegida con CRON_SECRET.
Route::get('/cron/diario', [\App\Http\Controllers\CronController::class, 'daily'])->middleware('throttle:10,1')->name('cron.daily');

// Ruta para incrementar el contador de visitas (POST: efecto secundario, no debe ser GET)
Route::post('/increment-visit', [VisitController::class, 'incrementVisit'])->middleware('throttle:60,1')->name('increment.visit');

// For Admin
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'authadmin'])->group(function () {

    Route::get('/admin/dashboard', AdminDashboardComponent::class)->name('admin.dashboard');

    Route::get('/admin/service-categories', AdminServiceCategoryComponent::class)->name('admin.service_categories');
    Route::get('/admin/service-category/add', AdminAddServiceCategoryComponent::class)->name('admin.add_service_category');
    Route::get('/admin/service-category/edit/{category_id}', AdminEditServiceCategoryComponent::class)->name('admin.edit_service_category');

    Route::get('/admin/all-services', AdminServicesComponent::class)->name('admin.all_services');
    Route::get('/admin/{category_slug}/services', AdminServicesByCategoryComponent::class)->name('admin.services_by_category');
    Route::get('/admin/service/add', AdminAddServiceComponent::class)->name('admin.add_service');
    Route::get('/admin/service/edit/{service_slug}', AdminEditServiceComponent::class)->name('admin.edit_service');

    Route::get('/admin/slider', AdminSliderComponent::class)->name('admin.slider');
    Route::get('/admin/slide/add', AdminAddSlideComponent::class)->name('admin.add_slide');
    Route::get('/admin/slide/edit/{slide_id}', AdminEditSlideComponent::class)->name('admin.edit_slide');

    Route::get('/admin/contacts', AdminContactComponent::class)->name('admin.contacts');
    Route::get('/admin/service-requests', SproviderRequestsComponent::class)->name('admin.service_requests');
    Route::get('/admin/payments', AdminPaymentsComponent::class)->name('admin.payments');
    Route::get('/admin/payments/{payment}/receipt', [BillingController::class, 'receipt'])->name('admin.payment_receipt');

    Route::get('/admin/service-providers', AdminServiceProvidersComponent::class)->name('admin.service_providers');
    Route::get('/admin/verifications', \App\Http\Livewire\Admin\AdminVerificationsComponent::class)->name('admin.verifications');
    Route::get('/admin/verifications/{document}/file', [\App\Http\Controllers\AdminTrustController::class, 'document'])->name('admin.provider_document');
    Route::get('/admin/activity', \App\Http\Livewire\Admin\AdminActivityComponent::class)->name('admin.activity');
    Route::get('/admin/activity/export', [\App\Http\Controllers\AdminTrustController::class, 'exportActivity'])->name('admin.activity_export');
    Route::get('/admin/about', AdminAboutComponent::class)->name('admin.about');
    Route::get('/admin/faq', AdminFaqComponent::class)->name('admin.faq');
    Route::get('/admin/terms', AdminTermsComponent::class)->name('admin.terms');
    Route::get('/admin/privacy', AdminPrivacyComponent::class)->name('admin.privacy');

    // Ruta de reset del contador de visitas (solo administradores).
    Route::post('/reset-visits', [VisitController::class, 'resetVisits'])->name('reset.visits');

});

// For Service Provider
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'authsprovider'])->group(function () {
    Route::get('/sprovider/dashboard', SproviderDashboardComponent::class)->name('sprovider.dashboard');
    Route::get('/sprovider/profile', SproviderProfileComponent::class)->name('sprovider.profile');
    Route::get('/sprovider/profile/edit', EditSproviderProfileComponent::class)->name('sprovider.edit_profile');
    Route::get('/sprovider/my-services', SproviderServicesComponent::class)->name('sprovider.services');
    Route::get('/sprovider/service/add', SproviderAddServiceComponent::class)->name('sprovider.add_service');
    Route::get('/sprovider/service/edit/{service_slug}', SproviderEditServiceComponent::class)->name('sprovider.edit_service');
    Route::get('/sprovider/requests', SproviderRequestsComponent::class)->name('sprovider.requests');
    Route::get('/sprovider/plan', SproviderPlanComponent::class)->name('sprovider.plan');
    Route::get('/sprovider/verification', \App\Http\Livewire\Sprovider\SproviderVerificationComponent::class)->name('sprovider.verification');
    Route::post('/sprovider/plan/payphone', [BillingController::class, 'startPayphone'])->middleware('throttle:10,1')->name('billing.payphone.start');
});

// For Customer
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/customer/dashboard', CustomerDashboardComponent::class)->name('customer.dashboard');
    Route::get('/aceptar-terminos', \App\Http\Livewire\AcceptTermsComponent::class)->name('terms.accept');
});
