<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Payphone "Botón de pagos" (redirect) client.
 *
 * Flow: prepare() returns the hosted payment URLs; Payphone sends the customer back to
 * responseUrl with ?id=&clientTransactionId=, and confirm() must run within 5 minutes or
 * Payphone reverses the charge. Amounts travel in cents.
 *
 * @see https://docs.payphone.app/boton-de-pago-por-redireccion
 */
final class PayphoneGateway
{
    public const STATUS_APPROVED = 3;
    public const STATUS_CANCELED = 2;

    public function isEnabled(): bool
    {
        return (bool) config('billing.payphone.enabled') && (string) config('billing.payphone.token') !== '';
    }

    /**
     * @return array{paymentId: mixed, payWithCard: string, payWithPayPhone?: string}
     */
    public function prepare(float $total, string $clientTransactionId, string $responseUrl, string $cancellationUrl, string $reference, ?string $email = null): array
    {
        $payload = $this->amounts($total) + [
            'currency' => 'USD',
            'clientTransactionId' => $clientTransactionId,
            'responseUrl' => $responseUrl,
            'cancellationUrl' => $cancellationUrl,
            'reference' => $reference,
            'lang' => 'es',
            'timeZone' => -5,
        ];

        if ((string) config('billing.payphone.store_id') !== '') {
            $payload['storeId'] = (string) config('billing.payphone.store_id');
        }

        if ($email !== null && $email !== '') {
            $payload['email'] = $email;
        }

        $response = $this->client()->post('/api/button/Prepare', $payload);

        if (! $response->successful() || ! is_string($response->json('payWithCard'))) {
            throw new RuntimeException('Payphone Prepare failed: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed> Payphone transaction (statusCode, transactionStatus, amount, clientTransactionId, transactionId...)
     */
    public function confirm(int $id, string $clientTransactionId): array
    {
        $response = $this->client()->post('/api/button/V2/Confirm', [
            'id' => $id,
            'clientTxId' => $clientTransactionId,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Payphone Confirm failed: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Split a total (USD) into Payphone amount fields in cents, honouring billing.tax_rate
     * (the tax is considered included in the price).
     *
     * @return array{amount: int, amountWithoutTax: int, amountWithTax: int, tax: int}
     */
    public function amounts(float $total): array
    {
        $amount = (int) round($total * 100);
        $rate = (float) config('billing.tax_rate');

        if ($rate <= 0) {
            return ['amount' => $amount, 'amountWithoutTax' => $amount, 'amountWithTax' => 0, 'tax' => 0];
        }

        $base = (int) round($amount / (1 + $rate));

        return ['amount' => $amount, 'amountWithoutTax' => 0, 'amountWithTax' => $base, 'tax' => $amount - $base];
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('billing.payphone.base_url'))
            ->withToken((string) config('billing.payphone.token'))
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
