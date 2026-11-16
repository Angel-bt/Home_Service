<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Historial de lo que hizo la administración (App\Support\AdminLog). Solo lectura.
 */
class AdminHistoryComponent extends Component
{
    use WithPagination;

    public string $q = '';

    protected $queryString = ['q' => ['except' => '']];

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $term = mb_strtolower(trim($this->q));

        return view('livewire.admin.admin-history-component', [
            'entries' => DB::table('admin_logs')
                ->leftJoin('users', 'users.id', '=', 'admin_logs.user_id')
                ->select('admin_logs.*', 'users.name as user_name')
                ->when($term !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($term): void {
                    $like = '%' . $term . '%';
                    $query->whereRaw('lower(admin_logs.action) like ?', [$like])
                        ->orWhereRaw('lower(admin_logs.subject) like ?', [$like])
                        ->orWhereRaw('lower(admin_logs.details) like ?', [$like]);
                }))
                ->orderByDesc('admin_logs.id')
                ->paginate(30),
        ])->layout('layouts.base')->title('Historial');
    }
}
