# Production optimizations

## 1. Redis service views

`ServiceVisitCounter` increments `service-visits:service:{id}` in Redis and records the service ID in a Redis set. No MySQL write occurs on a page view. `services:sync-visit-counters` atomically claims each counter with `GETSET`, increments the `services.views` column in a transaction, and restores claimed values to Redis if MySQL fails.

Run the database migration and test the synchronizer:

```powershell
php artisan migrate --force
php artisan services:sync-visit-counters
php artisan schedule:work
```

The scheduler invokes it every five minutes. In production, run one scheduler process (or one cron entry) and use a process manager for it:

```cron
* * * * * cd /var/www/home-services && php artisan schedule:run >> /dev/null 2>&1
```

The `withoutOverlapping()` and `onOneServer()` protections require the shared Redis cache driver.

## 2. Eager loading and home cache

`HomeComponent` caches categories, featured services, appliance services, and active sliders. Services eager load their category and owner. The service-detail and administration listing components also eager load `category` and `user`, removing relation queries from Blade loops.

`Service`, `ServiceCategory`, and `Slider` invalidate the affected home keys on save or delete. To clear all cache keys intentionally after a deployment:

```powershell
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Queued mail

Registration dispatches `SendWelcomeEmail`; saving a contact lead dispatches `SendNewLeadNotification`. Both jobs use the `mail` queue and retry three times, so Livewire requests do not wait for SMTP.

For Redis queues, start workers under Supervisor, systemd, or the cloud worker service:

```powershell
php artisan queue:work redis --queue=mail,default --sleep=1 --tries=3 --max-time=3600
php artisan queue:restart
```

For a database queue instead, set `QUEUE_CONNECTION=database` and use the supplied `jobs` migration. Set `LEAD_NOTIFICATION_ADDRESS` to the operations inbox; absent value intentionally skips the lead email.

## 5. Freemium and Pro plan billing

Free providers can publish `FREE_SERVICE_LIMIT` services (default 3); `User::isPro()` (plan `pro` and `plan_expires_at` in the future, or `null` for a manual upgrade) and `ADM` are unlimited. `ServicePublicationGate` enforces the cap on creation.

Stripe does not accept merchants based in Ecuador, so the Pro plan is sold with:

1. **Transfer / DeUna with receipt** (always available). The provider pays, uploads the receipt on `/sprovider/plan` (stored on the private `receipts` disk, `storage/app/receipts`) and an administrator approves or rejects it on `/admin/payments`. Approval extends `plan_expires_at` by `BILLING_PRO_PERIOD_DAYS` x periods, counting from the current expiry when renewing early.
2. **Payphone card payments** (optional, `PAYPHONE_ENABLED=true`). `App\Services\PayphoneGateway` calls `api/button/Prepare`, redirects to Payphone and, on return to `/billing/payphone/return`, calls `api/button/V2/Confirm`. The plan is activated only when Payphone answers `statusCode` 3 for the same `clientTransactionId` and amount. Payphone reverses unconfirmed charges after 5 minutes. Register the production domain in Payphone Developer, which only allows the payment button from that domain.

`php artisan billing:expire-plans` (scheduled hourly) moves expired plans back to `free` and closes card attempts that were never confirmed. Services published while on Pro stay online after expiry; the provider just cannot add more until they renew.

Set the price and payment details through `BILLING_*` and `PAYPHONE_*` (see `.env.production.example` and `config/billing.php`). `BILLING_TAX_RATE` is the IVA share included in the price (0 if the business does not charge IVA); confirm it with an accountant. The unused `stripe_id`/`pm_*` user columns are left from the earlier Cashier plan.

## Required environment

The server needs **PHP 8.3 or newer** (Laravel 13) with the `redis` extension. Deploying the Laravel 13 upgrade switches sessions to JSON serialization, so everyone is logged out once.


Copy the non-secret keys from `.env.production.example` into the production environment. At minimum, set `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, the Redis connection values, SMTP settings, and the lead notification address. After changing environment values, run `php artisan config:cache`.
