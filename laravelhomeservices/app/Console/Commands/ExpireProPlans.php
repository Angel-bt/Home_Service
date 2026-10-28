<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SubscriptionPayment;
use App\Services\ProPlanBilling;
use Illuminate\Console\Command;

final class ExpireProPlans extends Command
{
    protected $signature = 'billing:expire-plans';

    protected $description = 'Move providers whose Pro plan expired back to the free plan and close abandoned card payments.';

    public function handle(ProPlanBilling $billing): int
    {
        $expired = $billing->expireDuePlans();

        // Payphone reverses unconfirmed charges after 5 minutes; these attempts can never be approved.
        $abandoned = SubscriptionPayment::where('status', SubscriptionPayment::INITIATED)
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => SubscriptionPayment::REJECTED, 'notes' => 'Pago con tarjeta no completado.']);

        $this->info("Planes vencidos: {$expired}. Pagos con tarjeta abandonados: {$abandoned}.");

        return self::SUCCESS;
    }
}
