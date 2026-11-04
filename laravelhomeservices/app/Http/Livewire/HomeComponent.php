<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\ProviderZone;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Landing page. Everything it shows comes from one cached array (no Eloquent
 * objects in the cache), rendered with the lightweight layouts.landing layout
 * instead of the legacy Bootstrap theme.
 */
class HomeComponent extends Component
{
    public const CACHE_KEY = 'home:landing';

    // One short line per zone for the hero strip (keep in sync with config/marketplace.zones).
    private const ZONE_NOTES = [
        'malacatos' => 'Zona principal',
        'vilcabamba' => 'Valle de la longevidad',
        'yangana' => 'Al pie del Podocarpus',
        'quinara' => 'Al sur del valle',
    ];

    public function render()
    {
        // Short TTL: stats and reviews change without touching services or categories.
        $ttl = now()->addSeconds(min((int) config('marketplace.cache_ttl_seconds'), 600));

        return view('livewire.home-component', [
            'landing' => Cache::remember(self::CACHE_KEY, $ttl, fn (): array => $this->landingData()),
        ])->layout('layouts.landing');
    }

    /**
     * @return array<string, mixed>
     */
    private function landingData(): array
    {
        $providersByZone = ProviderZone::query()
            ->select('zone', DB::raw('count(*) as total'))
            ->groupBy('zone')
            ->pluck('total', 'zone');

        $zones = [];
        foreach (config('marketplace.zones') as $slug => $label) {
            $zones[] = [
                'slug' => $slug,
                'label' => $label,
                'note' => self::ZONE_NOTES[$slug] ?? '',
                'providers' => (int) ($providersByZone[$slug] ?? 0),
                'main' => $slug === config('marketplace.main_zone'),
            ];
        }

        $categories = ServiceCategory::query()
            ->withCount(['services' => fn ($query) => $query->where('status', true)])
            ->with(['services' => fn ($query) => $query->where('status', true)->select('id', 'service_category_id', 'thumbnail')->orderByDesc('featured')->latest()])
            ->orderByDesc('services_count')
            ->orderByDesc('featured')
            ->orderBy('name')
            ->take(8)
            ->get()
            ->map(fn (ServiceCategory $category): array => [
                'slug' => $category->slug,
                'name' => $category->name,
                'count' => (int) $category->services_count,
                // Thumbnail of its best service, shown as a hover preview.
                'preview' => $category->services->first()?->thumbnail,
            ])->all();

        $featured = Service::query()
            ->active()
            ->with(['category:id,name,slug', 'user:id,name,utype', 'user.serviceProvider:id,user_id', 'user.serviceProvider.zones:id,service_provider_id,zone'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('featured')
            ->orderByDesc('views')
            ->take(4)
            ->get()
            ->map(function (Service $service): array {
                $zones = $service->user?->utype === 'SVP'
                    ? ($service->user->serviceProvider?->zones->map->label()->implode(' · ') ?: null)
                    : 'Todas las zonas';

                return [
                    'slug' => $service->slug,
                    'name' => $service->name,
                    'tagline' => $service->tagline,
                    'category' => $service->category?->name,
                    'image' => $service->image ?: $service->thumbnail,
                    'provider' => $service->user?->utype === 'SVP' ? $service->user->name : null,
                    'price' => $service->finalPrice(),
                    'rating' => $service->reviews_avg_rating ? round((float) $service->reviews_avg_rating, 1) : null,
                    'reviews' => (int) $service->reviews_count,
                    'zones' => $zones,
                ];
            })->all();

        // Moving strip of services in the hero: enough variety to glance at without leaving the page.
        $ticker = Service::query()
            ->active()
            ->with(['category:id,name', 'user:id,name,utype'])
            ->withAvg('reviews', 'rating')
            ->orderByDesc('featured')
            ->latest()
            ->take(12)
            ->get()
            ->map(fn (Service $service): array => [
                'slug' => $service->slug,
                'name' => $service->name,
                'category' => $service->category?->name,
                'thumbnail' => $service->thumbnail,
                'provider' => $service->user?->utype === 'SVP' ? explode(' ', $service->user->name)[0] : null,
                'price' => $service->finalPrice(),
                'rating' => $service->reviews_avg_rating ? round((float) $service->reviews_avg_rating, 1) : null,
            ])->all();

        $reviews = Review::query()
            ->with(['user:id,name', 'service:id,name,slug'])
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (Review $review): array => [
                'author' => trim(explode(' ', (string) $review->user?->name)[0]) ?: 'Cliente',
                'rating' => $review->rating,
                'comment' => $review->comment,
                'service' => $review->service?->name,
                'slug' => $review->service?->slug,
            ])->all();

        return [
            'stats' => [
                'providers' => User::where('utype', 'SVP')->count(),
                'services' => Service::active()->count(),
                'reviews' => Review::count(),
                'rating' => ($avg = Review::avg('rating')) ? round((float) $avg, 1) : null,
            ],
            'zones' => $zones,
            'categories' => $categories,
            'featured' => $featured,
            'ticker' => $ticker,
            'reviews' => $reviews,
        ];
    }
}
