<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Activates, extends and expires the Pro plan from subscription payments.
 */
final class ProPlanBilling
{
    public function price(int $periods = 1): float
    {
        return round((float) config('billing.pro_price') * $periods, 2);
    }

    /**
     * Approve a payment and extend the user's Pro plan. Safe to call twice:
     * an already approved payment is returned untouched.
     */
    public function approve(SubscriptionPayment $payment, ?User $reviewer = null): SubscriptionPayment
    {
        return DB::transaction(function () use ($payment, $reviewer): SubscriptionPayment {
            $payment = SubscriptionPayment::whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($payment->status === SubscriptionPayment::APPROVED) {
                return $payment;
            }

            $user = User::whereKey($payment->user_id)->lockForUpdate()->firstOrFail();
            $start = $user->isPro() && $user->plan_expires_at !== null && $user->plan_expires_at->isFuture()
                ? $user->plan_expires_at->copy()
                : Carbon::now();
            $until = $start->addDays((int) config('billing.pro_period_days') * max(1, $payment->periods));

            $user->forceFill(['subscription_plan' => 'pro', 'plan_expires_at' => $until])->save();

            $payment->forceFill([
                'status' => SubscriptionPayment::APPROVED,
                'paid_until' => $until,
                'reviewed_by' => $reviewer?->id,
                'reviewed_at' => Carbon::now(),
            ])->save();

            return $payment;
        });
    }

    public function reject(SubscriptionPayment $payment, User $reviewer, string $notes): SubscriptionPayment
    {
        if ($payment->status !== SubscriptionPayment::PENDING) {
            return $payment;
        }

        $payment->forceFill([
            'status' => SubscriptionPayment::REJECTED,
            'notes' => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => Carbon::now(),
        ])->save();

        return $payment;
    }

    /**
     * Move users whose Pro period ended back to the free plan.
     */
    public function expireDuePlans(): int
    {
        return User::query()
            ->where('subscription_plan', 'pro')
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', Carbon::now())
            ->update(['subscription_plan' => 'free']);
    }
}
