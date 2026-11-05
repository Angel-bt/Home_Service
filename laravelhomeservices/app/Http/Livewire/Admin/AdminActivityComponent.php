<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\ActivityEvent;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Qué pasa en el sitio: qué se busca, qué falta y qué genera contactos.
 */
class AdminActivityComponent extends Component
{
    public const PERIODS = [7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => 'Un año'];

    public int $days = 30;

    protected $queryString = ['days' => ['except' => 30]];

    public function render()
    {
        if (! array_key_exists($this->days, self::PERIODS)) {
            $this->days = 30;
        }

        $counts = $this->events()->selectRaw('event, count(*) as total')->groupBy('event')->pluck('total', 'event');

        return view('livewire.admin.admin-activity-component', [
            'periods' => self::PERIODS,
            'counts' => $counts,
            'signups' => $this->events()->where('event', ActivityEvent::SIGNUP)->selectRaw('user_role, count(*) as total')->groupBy('user_role')->pluck('total', 'user_role'),
            'activeUsers' => $this->events()->whereNotNull('user_id')->distinct()->count('user_id'),
            'topSearches' => $this->searches()->get(),
            'missingSearches' => $this->searches()->where('results', 0)->get(),
            'topServices' => $this->topServices(),
            'zones' => $this->events()->whereNotNull('zone')->selectRaw('zone, count(*) as total')->groupBy('zone')->orderByDesc('total')->pluck('total', 'zone'),
            'zoneLabels' => config('marketplace.zones'),
            'hasData' => $counts->isNotEmpty(),
        ])->layout('layouts.base')->title('Actividad');
    }

    private function events(): Builder
    {
        return ActivityEvent::query()->where('created_at', '>=', now()->subDays($this->days));
    }

    private function searches(): Builder
    {
        return $this->events()
            ->where('event', ActivityEvent::SEARCH)
            ->whereNotNull('term')
            ->selectRaw('term, count(*) as total')
            ->groupBy('term')
            ->orderByDesc('total')
            ->orderBy('term')
            ->limit(15);
    }

    /**
     * @return Collection<int, array{name: string, slug: ?string, views: int, contacts: int}>
     */
    private function topServices(): Collection
    {
        $rows = $this->events()
            ->whereNotNull('service_id')
            ->selectRaw('service_id')
            ->selectRaw('sum(case when event = ? then 1 else 0 end) as views', [ActivityEvent::SERVICE_VIEW])
            ->selectRaw('sum(case when event = ? then 1 else 0 end) as contacts', [ActivityEvent::WHATSAPP_CLICK])
            ->groupBy('service_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $services = Service::whereIn('id', $rows->pluck('service_id'))->get(['id', 'name', 'slug'])->keyBy('id');

        return $rows->map(fn ($row): array => [
            'name' => $services[$row->service_id]->name ?? 'Servicio eliminado',
            'slug' => $services[$row->service_id]->slug ?? null,
            'views' => (int) $row->views,
            'contacts' => (int) $row->contacts,
        ]);
    }
}
