<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Livewire\Sprovider\SproviderPlanComponent;
use App\Models\SubscriptionPayment;
use App\Services\PayphoneGateway;
use App\Services\ProPlanBilling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BillingController extends Controller
{
    /**
     * Start a Payphone card payment for the Pro plan and send the provider to Payphone.
     */
    public function startPayphone(Request $request, PayphoneGateway $payphone, ProPlanBilling $billing): RedirectResponse
    {
        abort_unless($payphone->isEnabled(), 404);

        $data = $request->validate([
            'periods' => ['required', 'integer', Rule::in(SproviderPlanComponent::PERIOD_OPTIONS)],
        ]);

        $user = $request->user();
        $periods = (int) $data['periods'];

        $payment = $user->subscriptionPayments()->create([
            'method' => SubscriptionPayment::METHOD_PAYPHONE,
            'periods' => $periods,
            'amount' => $billing->price($periods),
            'status' => SubscriptionPayment::INITIATED,
            'client_transaction_id' => 'PRO' . $user->id . '-' . Str::upper(Str::random(12)),
        ]);

        try {
            $prepared = $payphone->prepare(
                (float) $payment->amount,
                $payment->client_transaction_id,
                route('billing.payphone.return'),
                route('sprovider.plan'),
                'Plan Pro Malakplace ' . ($periods * (int) config('billing.pro_period_days')) . ' días',
                $user->email,
            );
        } catch (Throwable $exception) {
            report($exception);
            $payment->forceFill(['status' => SubscriptionPayment::REJECTED, 'notes' => 'No se pudo iniciar el pago con Payphone.'])->save();

            return redirect()->route('sprovider.plan')->with('error', 'No pudimos conectar con Payphone. Intenta de nuevo o paga por transferencia.');
        }

        $payment->forceFill(['gateway_transaction_id' => (string) ($prepared['paymentId'] ?? '')])->save();

        return redirect()->away($prepared['payWithCard']);
    }

    /**
     * Payphone sends the customer back here with ?id=&clientTransactionId=. The payment is
     * confirmed server to server, so this route does not trust anything but Payphone's answer.
     */
    public function payphoneReturn(Request $request, PayphoneGateway $payphone, ProPlanBilling $billing): RedirectResponse
    {
        $id = (int) $request->query('id');
        $clientTransactionId = (string) $request->query('clientTransactionId');

        $payment = SubscriptionPayment::where('method', SubscriptionPayment::METHOD_PAYPHONE)
            ->where('client_transaction_id', $clientTransactionId)
            ->first();

        if ($payment === null || $id <= 0) {
            return redirect()->route('sprovider.plan')->with('error', 'No encontramos ese pago.');
        }

        if ($payment->status === SubscriptionPayment::APPROVED) {
            return redirect()->route('sprovider.plan')->with('message', 'Tu plan Pro ya está activo.');
        }

        if ($payment->status !== SubscriptionPayment::INITIATED) {
            return redirect()->route('sprovider.plan')->with('error', 'Este pago ya fue procesado.');
        }

        try {
            $transaction = $payphone->confirm($id, $clientTransactionId);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('sprovider.plan')->with('error', 'No pudimos confirmar el pago con Payphone. Si se debitó, escríbenos con tu comprobante.');
        }

        $approved = (int) ($transaction['statusCode'] ?? 0) === PayphoneGateway::STATUS_APPROVED
            && (string) ($transaction['clientTransactionId'] ?? '') === $payment->client_transaction_id
            && (int) ($transaction['amount'] ?? -1) === (int) round((float) $payment->amount * 100);

        $payment->forceFill([
            'gateway_transaction_id' => (string) ($transaction['transactionId'] ?? $id),
            'reference' => isset($transaction['authorizationCode']) ? 'Aut. ' . $transaction['authorizationCode'] : null,
        ])->save();

        if (! $approved) {
            $payment->forceFill([
                'status' => SubscriptionPayment::REJECTED,
                'notes' => 'Payphone: ' . ($transaction['message'] ?? $transaction['transactionStatus'] ?? 'pago no aprobado'),
            ])->save();

            return redirect()->route('sprovider.plan')->with('error', 'El pago no fue aprobado. No se realizó ningún cobro.');
        }

        $payment = $billing->approve($payment);

        return redirect()->route('sprovider.plan')->with('message', '¡Pago aprobado! Tu plan Pro está activo hasta el ' . $payment->paid_until->format('d/m/Y') . '.');
    }

    /**
     * Private receipt file, only for administrators.
     */
    public function receipt(SubscriptionPayment $payment): StreamedResponse
    {
        abort_unless($payment->receipt_path !== null && Storage::disk('receipts')->exists($payment->receipt_path), 404);

        return \App\Support\PrivateFile::response(Storage::disk('receipts'), $payment->receipt_path);
    }
}
