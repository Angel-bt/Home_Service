<?php

declare(strict_types=1);

namespace App\Http\Livewire\Service;

use App\Models\Service;
use App\Services\ServiceVisitCounter;
use Livewire\Component;

class ServiceDetailsComponent extends Component
{
    public string $service_slug;

    public int $views = 0;

    public function mount(string $service_slug, ServiceVisitCounter $visitCounter): void
    {
        $this->service_slug = $service_slug;
        $service = Service::query()->select(['id', 'views'])->where('slug', $service_slug)->first();

        if ($service !== null) {
            $visitCounter->increment($service);
            $this->views = $visitCounter->total($service);
        }
    }

    public function render()
    {
        $service = Service::query()
            ->with(['category:id,name,slug', 'user:id,name'])
            ->where('slug', $this->service_slug)
            ->first();

        $rService = $service === null ? null : Service::query()
            ->with('category:id,name,slug')
            ->where('service_category_id', $service->service_category_id)
            ->where('slug', '!=', $this->service_slug)
            ->inRandomOrder()
            ->first();

        if ($service === null) {
            session()->flash('error', 'The requested service is unavailable. Please verify the link.');
        }

        return view('livewire.service.service-details-component', [
            'service' => $service,
            'r_service' => $rService,
        ])->layout('layouts.base');
    }
}
