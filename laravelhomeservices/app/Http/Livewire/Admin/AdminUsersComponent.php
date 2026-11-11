<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Actions\Jetstream\DeleteUser;
use App\Http\Livewire\HomeComponent;
use App\Models\ActivityEvent;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Todas las cuentas: buscar, suspender, reactivar, cambiar el tipo y eliminar.
 */
class AdminUsersComponent extends Component
{
    use WithPagination;

    public const ROLES = ['CST' => 'Cliente', 'SVP' => 'Proveedor', 'ADM' => 'Administración'];

    public string $q = '';

    public string $role = '';

    public ?int $suspendingId = null;

    public string $suspendReason = '';

    protected $queryString = ['q' => ['except' => ''], 'role' => ['except' => '']];

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function startSuspend(int $userId): void
    {
        $this->resetErrorBag();
        $this->suspendingId = $userId;
        $this->suspendReason = '';
    }

    public function suspend(): void
    {
        $this->authorizeAdmin();
        $this->validate(['suspendReason' => ['required', 'string', 'max:255']], [], ['suspendReason' => 'el motivo']);

        $user = $this->editable($this->suspendingId);
        if ($user === null) {
            return;
        }

        $user->forceFill(['suspended_at' => now(), 'suspension_reason' => $this->suspendReason])->save();
        Cache::forget(HomeComponent::CACHE_KEY);
        $this->suspendingId = null;
        session()->flash('message', $user->name . ' quedó suspendido: ya no puede entrar y sus servicios no se ven.');
    }

    public function reactivate(int $userId): void
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);
        $user->forceFill(['suspended_at' => null, 'suspension_reason' => null])->save();
        Cache::forget(HomeComponent::CACHE_KEY);
        session()->flash('message', $user->name . ' puede volver a entrar y sus servicios se ven otra vez.');
    }

    public function changeRole(int $userId, string $role): void
    {
        $this->authorizeAdmin();
        abort_unless(array_key_exists($role, self::ROLES), 404);

        $user = $this->editable($userId);
        if ($user === null || $user->utype === $role) {
            return;
        }

        if ($user->utype === 'ADM' && User::where('utype', 'ADM')->count() <= 1) {
            session()->flash('error', 'Es la única cuenta de administración: crea otra antes de cambiar esta.');

            return;
        }

        if ($user->utype === 'SVP' && $role !== 'SVP' && ($services = $user->services()->count()) > 0) {
            session()->flash('error', $user->name . ' tiene ' . $services . ' servicios publicados. Elimínalos o pídele que los elimine antes de cambiar su cuenta.');

            return;
        }

        $user->utype = $role;
        if ($role === 'SVP') {
            ServiceProvider::firstOrCreate(['user_id' => $user->id]);
            // Su perfil pasa a ser público: debe aceptar el permiso de publicación al entrar.
            $user->terms_version = null;
        }
        $user->save();

        session()->flash('message', $user->name . ' ahora es: ' . self::ROLES[$role] . '.');
    }

    public function deleteUser(int $userId, DeleteUser $deleter): void
    {
        $this->authorizeAdmin();

        $user = $this->editable($userId);
        if ($user === null) {
            return;
        }

        if ($user->utype === 'ADM') {
            session()->flash('error', 'Primero cambia esta cuenta a cliente; luego podrás eliminarla.');

            return;
        }

        $name = $user->name;
        $deleter->delete($user);
        Cache::forget(HomeComponent::CACHE_KEY);
        session()->flash('message', 'Se eliminó la cuenta de ' . $name . ' con sus servicios.');
    }

    public function render()
    {
        $term = mb_strtolower(trim($this->q));

        $users = User::query()
            ->withCount('services')
            ->addSelect(['last_seen' => ActivityEvent::query()->selectRaw('max(created_at)')->whereColumn('user_id', 'users.id')])
            ->when($term !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($term): void {
                $like = '%' . $term . '%';
                $query->whereRaw('lower(name) like ?', [$like])
                    ->orWhereRaw('lower(email) like ?', [$like])
                    ->orWhere('phone', 'like', $like);
            }))
            ->when($this->role === 'suspended', fn (Builder $query) => $query->whereNotNull('suspended_at'))
            ->when(array_key_exists($this->role, self::ROLES), fn (Builder $query) => $query->where('utype', $this->role))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.admin-users-component', [
            'users' => $users,
            'roles' => self::ROLES,
            'counts' => User::query()->selectRaw('utype, count(*) as total')->groupBy('utype')->pluck('total', 'utype'),
            'suspendedCount' => User::whereNotNull('suspended_at')->count(),
            'total' => User::count(),
        ])->layout('layouts.base')->title('Usuarios');
    }

    /**
     * La cuenta a cambiar, o null si es la propia (nadie se suspende, degrada ni elimina a sí mismo).
     */
    private function editable(?int $userId): ?User
    {
        $user = User::findOrFail($userId);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'No puedes hacer esto con tu propia cuenta.');

            return null;
        }

        return $user;
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->utype === 'ADM', 403);
    }
}
