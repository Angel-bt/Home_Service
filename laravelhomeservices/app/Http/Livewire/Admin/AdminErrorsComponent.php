<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Support\AdminLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Errores del sitio (App\Support\ErrorRecorder), agrupados por lugar. Si uno vuelve a pasar
 * después de marcarlo como revisado, reaparece solo.
 */
class AdminErrorsComponent extends Component
{
    use WithPagination;

    public bool $showResolved = false;

    public function updatingShowResolved(): void
    {
        $this->resetPage();
    }

    public function resolve(int $errorId): void
    {
        $this->authorizeAdmin();

        DB::table('error_logs')->where('id', $errorId)->update(['resolved_at' => now()]);
        AdminLog::record('Marcó un error como revisado', 'error #' . $errorId);
    }

    public function resolveAll(): void
    {
        $this->authorizeAdmin();

        $count = DB::table('error_logs')->whereNull('resolved_at')->update(['resolved_at' => now()]);
        AdminLog::record('Marcó todos los errores como revisados', null, $count . ' errores');
        session()->flash('message', 'Listo. Si alguno vuelve a pasar, aparecerá otra vez aquí.');
    }

    public function render()
    {
        return view('livewire.admin.admin-errors-component', [
            'logs' => DB::table('error_logs')
                ->when(! $this->showResolved, fn ($query) => $query->whereNull('resolved_at'))
                ->orderByDesc('last_seen_at')
                ->paginate(20),
        ])->layout('layouts.base')->title('Errores');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
