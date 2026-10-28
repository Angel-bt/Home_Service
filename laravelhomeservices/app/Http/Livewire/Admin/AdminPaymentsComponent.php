<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Models\SubscriptionPayment;
use App\Services\ProPlanBilling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPaymentsComponent extends Component
{
    use WithPagination;

    public string $status = SubscriptionPayment::PENDING;

    public ?int $rejectingId = null;
    public string $rejectReason = '';

    protected $queryString = ['status' => ['except' => SubscriptionPayment::PENDING]];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function approve(int $paymentId, ProPlanBilling $billing): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $payment = $billing->approve(SubscriptionPayment::findOrFail($paymentId), Auth::user());
        session()->flash('message', 'Pago aprobado. ' . $payment->user->name . ' tiene Pro hasta el ' . $payment->paid_until->format('d/m/Y') . '.');
    }

    public function startReject(int $paymentId): void
    {
        $this->resetErrorBag();
        $this->rejectingId = $paymentId;
        $this->rejectReason = '';
    }

    public function reject(ProPlanBilling $billing): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $this->validate(['rejectReason' => ['required', 'string', 'max:255']], [], ['rejectReason' => 'el motivo']);

        $billing->reject(SubscriptionPayment::findOrFail($this->rejectingId), Auth::user(), $this->rejectReason);
        $this->rejectingId = null;
        session()->flash('message', 'Pago rechazado. El proveedor verá el motivo en su historial.');
    }

    public function render()
    {
        return view('livewire.admin.admin-payments-component', [
            'payments' => SubscriptionPayment::query()
                ->with(['user:id,name,email,phone,subscription_plan,plan_expires_at', 'reviewer:id,name'])
                ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
                ->latest()
                ->paginate(20),
            'pendingCount' => SubscriptionPayment::where('status', SubscriptionPayment::PENDING)->count(),
            'periodDays' => (int) config('billing.pro_period_days'),
        ])->layout('layouts.base');
    }
}
