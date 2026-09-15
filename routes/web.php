<?php

use App\Http\Controllers\SearchController;
use App\Http\Livewire\Admin\AdminDashboardComponent;
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
use App\Http\Livewire\Service\ServiceCategoriesComponent;
use App\Http\Livewire\Service\ServiceDetailsComponent;
use App\Http\Livewire\Service\ServicesByCategoryComponent;
use App\Http\Livewire\Sprovider\EditSproviderProfileComponent;
use App\Http\Livewire\Sprovider\SproviderDashboardComponent;
use App\Http\Livewire\Sprovider\SproviderProfileComponent;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\AboutComponent;
use App\Http\Livewire\FaqComponent;
use App\Http\Livewire\TermsComponent;
use App\Http\Livewire\PrivacyComponent;
use App\Http\Controllers\PasswordResetController;
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
Route::get('/autocomplete', [SearchController::class, 'autocomplete'])->name('autocomplete');
Route::post('/search', [SearchController::class, 'searchService'])->name('searchService');

Route::get('password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.request');
Route::post('password/reset', [PasswordResetController::class, 'sendResetLink'])->name('password.email');


// Ruta para incrementar el contador de visitas
Route::get('/increment-visit', [VisitController::class, 'incrementVisit']);

// Ruta para reiniciar el contador de visitas
Route::post('/reset-visits', [VisitController::class, 'resetVisits'])->name('reset.visits');

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

    Route::get('/admin/service-providers', AdminServiceProvidersComponent::class)->name('admin.service_providers');
    Route::get('/admin/about', AdminAboutComponent::class)->name('admin.about');
    Route::get('/admin/faq', AdminFaqComponent::class)->name('admin.faq');
    Route::get('/admin/terms', AdminTermsComponent::class)->name('admin.terms');
    Route::get('/admin/privacy', AdminPrivacyComponent::class)->name('admin.privacy');

});

// For Service Provider
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'authsprovider'])->group(function () {
    Route::get('/sprovider/dashboard', SproviderDashboardComponent::class)->name('sprovider.dashboard');
    Route::get('/sprovider/profile', SproviderProfileComponent::class)->name('sprovider.profile');
    Route::get('/sprovider/profile/edit', EditSproviderProfileComponent::class)->name('sprovider.edit_profile');
});

// For Customer
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/customer/dashboard', CustomerDashboardComponent::class)->name('customer.dashboard');
});
