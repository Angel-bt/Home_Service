<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HomeComponent extends Component
{
    public function render()
    {
        $ttl = now()->addSeconds((int) config('marketplace.cache_ttl_seconds'));
        $scategories = Cache::remember('home:service-categories', $ttl, fn () => ServiceCategory::query()->inRandomOrder()->take(18)->get());
        $fservices = Cache::remember('home:featured-services', $ttl, fn () => Service::query()->with(['category:id,name,slug', 'user:id,name'])->where('featured', true)->inRandomOrder()->take(8)->get());
        $fscategories = Cache::remember('home:featured-categories', $ttl, fn () => ServiceCategory::query()->where('featured', true)->inRandomOrder()->take(8)->get());
        $aservices = Cache::remember('home:appliance-services', $ttl, fn () => Service::query()->with(['category:id,name,slug', 'user:id,name'])->whereHas('category', fn ($query) => $query->whereIn('slug', ['ac', 'tv', 'refrigerator', 'geyser', 'water-purifier']))->inRandomOrder()->take(8)->get());
        $slides = Cache::remember('home:active-sliders', $ttl, fn () => Slider::query()->where('status', true)->get());

        // Datos para los modales de categoría independientes (grid principal).
        // Se cachea como array serializable para poder hidratarlo en la vista.
        $categoryModals = Cache::remember('home:category-modals', $ttl, function () {
            return ServiceCategory::query()
                ->with(['services' => fn ($query) => $query->with('user:id,name')->orderByDesc('featured')->latest()->take(6)])
                ->take(18)
                ->get()
                ->map(fn (ServiceCategory $category) => [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'image' => $category->image,
                    'services' => $category->services->map(fn (Service $service) => [
                        'name' => $service->name,
                        'slug' => $service->slug,
                        'tagline' => $service->tagline,
                        'price' => $service->price,
                        'thumbnail' => $service->thumbnail,
                    ])->values()->all(),
                ])->keyBy('slug')->all();
        });

        return view('livewire.home-component', ['scategories' => $scategories, 'fservices' => $fservices, 'fscategories' => $fscategories, 'aservices' => $aservices, 'slides' => $slides, 'categoryModals' => $categoryModals])->layout('layouts.base');
    }
}
