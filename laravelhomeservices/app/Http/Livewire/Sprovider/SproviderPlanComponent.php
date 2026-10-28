<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Http\Livewire\Concerns\ThrottlesActions;
use App\Models\SubscriptionPayment;
use App\Services\ProPlanBilling;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pro plan page: payment instructions, manual receipt upload and payment history.
 */
class SproviderPlanComponent extends Component
{
    use ThrottlesActions;
    use WithFileUploads;

    public const PERIOD_OPTIONS = [1, 3, 6, 12];

    public string $method = SubscriptionPayment::METHOD_TRANSFER;
    public int $periods = 1;
    public string $reference = '';
    public $receipt;

    protected function rules(): array
    {
        return [
            'method' => ['required', Rule::in([SubscriptionPayment::METHOD_TRANSFER, SubscriptionPayment::METHOD_DEUNA])],
            'periods' => ['required', 'integer', Rule::in(self::PERIOD_OPTIONS)],
            'reference' => ['required', 'string', 'max:100'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    protected $validationAttributes = [
        'reference' => 'el número de comprobante',
        'receipt' => 'el comprobante',
        'periods' => 'el periodo',
        'method' => 'el método de pago',
    ];

    public function submitReceipt(ProPlanBilling $billing): void
    {
        $this->validate();

        $user = Auth::user();

        if (! $this->withinRateLimit('receipt', (string) $user->id, 5, 3600, 'reference')) {
            return;
        }

        if ($user->subscriptionPayments()->where('status', SubscriptionPayment::PENDING)->exists()) {
            $this->addError('reference', 'Ya tienes un pago en revisión. Espera a que lo confirmemos.');

            return;
        }

        $fileName = $user->id . '_' . now()->format('YmdHis') . '_' . Str::random(8) . '.' . $this->receipt->extension();
        $path = $this->receipt->storeAs('', $fileName, 'receipts');

        $user->subscriptionPayments()->create([
            'method' => $this->method,
            'periods' => $this->periods,
            'amount' => $billing->price($this->periods),
            'reference' => $this->reference,
            'receipt_path' => $path,
            'status' => SubscriptionPayment::PENDING,
        ]);

        $this->reset(['reference', 'receipt']);
        session()->flash('message', 'Recibimos tu comprobante. Activaremos tu plan Pro en cuanto verifiquemos el pago (normalmente el mismo día).');
    }

    public function render(ProPlanBilling $billing)
    {
        $user = Auth::user();

        return view('livewire.sprovider.sprovider-plan-component', [
            'user' => $user,
            'isPro' => $user->isPro(),
            'limit' => (int) config('marketplace.free_service_limit'),
            'used' => $user->services()->count(),
            'price' => $billing->price(),
            'total' => $billing->price($this->periods),
            'periodDays' => (int) config('billing.pro_period_days'),
            'periodOptions' => self::PERIOD_OPTIONS,
            'bank' => config('billing.bank_transfer'),
            'deunaPhone' => config('billing.deuna_phone'),
            'payphoneEnabled' => (bool) config('billing.payphone.enabled'),
            'hasPending' => $user->subscriptionPayments()->where('status', SubscriptionPayment::PENDING)->exists(),
            'payments' => $user->subscriptionPayments()->latest()->take(10)->get(),
        ])->layout('layouts.base');
    }
}
