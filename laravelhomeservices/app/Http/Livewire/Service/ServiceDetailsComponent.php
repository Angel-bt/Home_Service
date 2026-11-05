<?php

declare(strict_types=1);

namespace App\Http\Livewire\Service;

use App\Http\Livewire\Concerns\ThrottlesActions;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Services\ServiceVisitCounter;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class ServiceDetailsComponent extends Component
{
    use ThrottlesActions;

    public string $service_slug;

    public int $views = 0;

    // Formulario de solicitud de servicio.
    public string $name = '';
    public string $phone = '';
    public string $zone = '';
    public string $address = '';
    public ?string $preferred_date = null;
    public string $message = '';

    public function mount(string $service_slug, ServiceVisitCounter $visitCounter): void
    {
        $this->service_slug = $service_slug;
        $service = Service::query()->select(['id', 'views', 'user_id'])->where('slug', $service_slug)->first();

        if ($service !== null) {
            $this->views = (int) $service->views;

            \App\Support\Activity::forService(\App\Models\ActivityEvent::SERVICE_VIEW, $service);

            // Las visitas no deben tumbar la página si Redis no está disponible.
            try {
                $visitCounter->increment($service);
                $this->views = $visitCounter->total($service);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $user = Auth::user();
        $this->name = (string) ($user?->name ?? '');
        $this->phone = (string) ($user?->phone ?? '');
        $this->zone = (string) session('zone', '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', function ($attribute, $value, $fail): void {
                if (WhatsApp::normalize($value) === null) {
                    $fail('Ingresa un número de celular válido (ej.: 0991234567).');
                }
            }],
            'zone' => ['required', Rule::in(array_keys(config('marketplace.zones')))],
            'address' => ['nullable', 'string', 'max:255'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function submitRequest()
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $service = Service::where('slug', $this->service_slug)->firstOrFail();

        if ($service->user_id === $user->id) {
            $this->addError('message', 'No puedes solicitar tu propio servicio.');

            return null;
        }

        $data = $this->validate();

        if (! $this->withinRateLimit('service-request', (string) $user->id, 5, 600, 'message')) {
            return null;
        }

        $request = ServiceRequest::create($data + [
            'service_id' => $service->id,
            'provider_id' => $service->user_id,
            'customer_id' => $user->id,
            'preferred_date' => $this->preferred_date ?: null,
            'status' => ServiceRequest::PENDING,
        ]);

        \App\Support\Activity::forService(\App\Models\ActivityEvent::REQUEST_CREATED, $service);
        $request->notifyProviderOfNewRequest();

        session(['zone' => $this->zone]);
        $this->reset(['address', 'preferred_date', 'message']);
        session()->flash('request_sent', 'Tu solicitud fue enviada. El proveedor te contactará pronto; también puedes escribirle por WhatsApp.');

        return null;
    }

    public function render()
    {
        $service = Service::query()
            ->with(['category:id,name,slug', 'user.serviceProvider.zones'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $this->service_slug)
            ->first();

        $rService = $service === null ? null : Service::query()
            ->with('category:id,name,slug')
            ->where('service_category_id', $service->service_category_id)
            ->where('slug', '!=', $this->service_slug)
            ->inRandomOrder()
            ->first();

        $reviews = $service === null ? collect() : $service->reviews()->with('user:id,name')->latest()->take(10)->get();

        return view('livewire.service.service-details-component', [
            'service' => $service,
            'r_service' => $rService,
            'reviews' => $reviews,
            'zones' => config('marketplace.zones'),
        ])->layout('layouts.base')->title($service?->name ?? 'Servicio no disponible');
    }
}
