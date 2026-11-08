<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Http\Livewire\Sprovider\SproviderPlanComponent;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\ProPlanBilling;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Pro plan payments: review transfer receipts and cash notices, and record cash
 * payments received in person. Every activation goes through ProPlanBilling.
 */
class AdminPaymentsComponent extends Component
{
    use WithPagination;

    public string $status = SubscriptionPayment::PENDING;

    public ?int $rejectingId = null;
    public string $rejectReason = '';

    // "Registrar pago en efectivo" form.
    public $cashUserId = '';
    public int $cashPeriods = 1;
    public string $cashNote = '';

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

    /**
     * Cash handed to the administration in person: create the payment and approve it at once.
     */
    public function recordCashPayment(ProPlanBilling $billing): void
    {
        abort_unless(Auth::user()->utype === 'ADM', 403);

        $this->validate([
            'cashUserId' => ['required', Rule::exists('users', 'id')->where('utype', 'SVP')],
            'cashPeriods' => ['required', 'integer', Rule::in(SproviderPlanComponent::PERIOD_OPTIONS)],
            'cashNote' => ['nullable', 'string', 'max:100'],
        ], [], ['cashUserId' => 'el proveedor', 'cashPeriods' => 'el periodo', 'cashNote' => 'la nota']);

        $payment = SubscriptionPayment::create([
            'user_id' => (int) $this->cashUserId,
            'method' => SubscriptionPayment::METHOD_CASH,
            'periods' => $this->cashPeriods,
            'amount' => $billing->price($this->cashPeriods),
            'reference' => $this->cashNote === '' ? 'Registrado por la administración' : $this->cashNote,
            'status' => SubscriptionPayment::PENDING,
        ]);
        $payment = $billing->approve($payment, Auth::user());

        $this->reset(['cashUserId', 'cashPeriods', 'cashNote']);
        session()->flash('message', 'Pago en efectivo registrado. ' . $payment->user->name . ' tiene Pro hasta el ' . $payment->paid_until->format('d/m/Y') . '.');
    }

    public function render(ProPlanBilling $billing)
    {
        return view('livewire.admin.admin-payments-component', [
            'payments' => SubscriptionPayment::query()
                ->with(['user:id,name,email,phone,subscription_plan,plan_expires_at', 'reviewer:id,name'])
                ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
                ->latest()
                ->paginate(20),
            'pendingCount' => SubscriptionPayment::where('status', SubscriptionPayment::PENDING)->count(),
            'periodDays' => (int) config('billing.pro_period_days'),
            'periodOptions' => SproviderPlanComponent::PERIOD_OPTIONS,
            'price' => $billing->price(),
            'providers' => User::where('utype', 'SVP')->orderBy('name')->get(['id', 'name', 'email']),
        ])->layout('layouts.base')->title('Pagos Pro');
    }
}
