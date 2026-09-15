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

## 5. Freemium and Stripe readiness

The user migration adds `subscription_plan`, Stripe customer/payment-method columns, and `trial_ends_at`. `User::canPublishService()` returns false once a free provider has `FREE_SERVICE_LIMIT` services (default: 3). `ServicePublicationGate` enforces that rule on service creation. `pro` and `ADM` are unlimited.

The current project does not install Cashier yet, so the plan gate is independent and deployable now. When billing is enabled, install the Laravel 9-compatible Cashier release and add its trait to `App\\Models\\User`:

```powershell
composer require laravel/cashier:^13.0
php artisan vendor:publish --tag="cashier-migrations"
php artisan migrate --force
```

Then add `use Laravel\\Cashier\\Billable;` and `use Billable;` to `User`, configure `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET`, and register the Stripe webhook endpoint. Do not expose those secrets in source control.

## Required environment

Copy the non-secret keys from `.env.production.example` into the production environment. At minimum, set `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, the Redis connection values, SMTP settings, and the lead notification address. After changing environment values, run `php artisan config:cache`.
