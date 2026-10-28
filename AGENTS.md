# AGENTS.md

## Layout

- The application lives in `laravelhomeservices/`; the repo root is just that folder plus `estructura.txt`; tutorial notes/links live in `laravelhomeservices/pasos .txt`. Run every command from `laravelhomeservices/`.
- Laravel 13 + Livewire 3 full-page components + Jetstream 5/Fortify auth + Sanctum 4 + Tailwind (via Mix). PHP `^8.3`. Legacy Bootstrap 3 HTML theme is committed under `public/assets/`.
- The app keeps the pre-Laravel-11 structure (`app/Http/Kernel.php`, `app/Console/Kernel.php`, `RouteServiceProvider`, `Exceptions/Handler`); Laravel 13 still supports it. Register middleware in `Kernel::$middlewareAliases`.

## Local dev

- `.env`: MySQL db `home` (root/root) and Redis for `CACHE/SESSION/QUEUE` (`phpredis`). A local Redis instance is required for `php artisan serve`; tests are not (see below).
- Assets use **Laravel Mix** (`webpack.mix.js`), not Vite: `npm run dev` / `npm run watch` / `npm run prod`. `layouts/base.blade.php` loads `mix('css/app.css')` (Tailwind, used by the header and home) after the legacy Bootstrap 3 theme; Tailwind `preflight` is disabled so it doesn't reset that theme. Rebuild after adding Tailwind classes to views.
- `php artisan db:seed --class=DemoMarketplaceSeeder` (local only) creates Spanish local categories, demo admin/providers/customer (`*@proyetech.test`, see the seeder) and a completed request with a review.
- Locale is `es` (`APP_LOCALE`); Spanish strings live in `lang/es/*.php`, untranslated validation keys fall back to `lang/en`.
- SMTP points at Mailpit-style `127.0.0.1:1025`.
- `/change-location` loads Google Places only when `GOOGLE_MAPS_API_KEY` is set (`config('services.google_maps.key')`); without it the page shows a warning and manual fields.

## Tests

- `php artisan test` (or `vendor/bin/phpunit`, PHPUnit 12: use `#[DataProvider]` attributes with static providers, not docblock annotations). `phpunit.xml` overrides to sqlite `:memory:`, array cache/session, sync queue — no MySQL/Redis needed.
- Besides stock Jetstream tests, `tests/Feature/MarketplaceSmokeTest.php` renders every public/admin/provider/customer page and covers registration roles. `tests/Feature/MarketplaceFlowTest.php` covers provider publishing/freemium, profile zones, the request → review flow, search and zone filters. `ServiceDetailsComponent` swallows (and reports) visit-counter failures, so `/service/{slug}` renders without Redis.

## Domain model and roles

- `users.utype`: `ADM` (admin), `SVP` (service provider), `CST` (customer). Guards are the `authadmin` / `authsprovider` middleware; mismatch flushes the session and redirects to login. Create an `ADM` user directly in DB.
- Register validates `registeras` as `CST|SVP` (missing → `CST`); `SVP` also creates the `service_providers` row. Deleting a category nulls `service_providers.service_category_id` (does not delete the profile).
- The global visit counter is the single `visit_counter` row from `Visit::counter()`; don't call `firstOrCreate(['id' => 1])` without `visitor_name` (NOT NULL).
- Providers (`SVP`) manage their own services under `/sprovider/my-services` (not `/sprovider/services`: it would collide with the public `/{category_slug}/services`). Admin and provider service forms share `App\Http\Livewire\Concerns\HandlesServiceForm` (rules, uploads, `|`-joined inclusion/exclusion) and the `livewire.partials.service-form` view.
- Zones are slugs from `config('marketplace.zones')` stored in `provider_zones`; `Service::scopeInZone()` returns services of providers covering the zone plus services not owned by an `SVP` (admin services count for every zone). Listings remember the chosen zone in `session('zone')` via `Concerns\FiltersByZone`.
- `service_requests` flow: `pending` → `accepted`/`rejected` (provider or ADM) → `completed` (provider); customer may cancel `pending`/`accepted`. Transitions are enforced only in `ServiceRequest::transitionTo()`. Reviews: one per completed request, by its customer (`ServiceRequest::canBeReviewedBy()`).
- WhatsApp links go through `App\Support\WhatsApp` (normalises Ecuadorian numbers to `593…`). Provider number = `service_providers.whatsapp`, else `users.phone`; non-provider services use `marketplace.contact_phone`.
- Freemium cap: `FREE_SERVICE_LIMIT` (default 3) services for `free` plan; `ADM` and `User::isPro()` unlimited. Enforced by `ServicePublicationGate` on create; `User::canPublishService()`. `isPro()` also checks `plan_expires_at`, so an expired plan stops counting before `billing:expire-plans` runs.
- Pro plan billing (no Stripe: it doesn't serve Ecuador): manual transfer/DeUna receipts on the private `receipts` disk, approved in `/admin/payments`, plus optional Payphone card payments (`PayphoneGateway`, `BillingController`). Always change plan state through `App\Services\ProPlanBilling::approve()/reject()` (idempotent, extends from current expiry). Payphone payments start as `initiated` and are approved only after a server-side Confirm matching `clientTransactionId` and amount; `/billing/payphone/return` is intentionally unauthenticated. Tests fake Payphone with `Http::fake()`.

## Gotchas

- Livewire 3: components stay in `App\Http\Livewire` (`config/livewire.php` `class_namespace`), default layout `layouts.base`. `wire:model` is deferred; use `wire:model.live` for inputs that must re-render (zone/status/period selects, search with `.live.debounce`), `.blur` instead of v2 `.lazy`. Events: `$this->dispatch(...)->to(...)`. Every component view needs exactly one root element (put `<script>`/`<style>` inside it or in `@push('scripts')`).
- `AuthAdmin`/`AuthSprovider` are Livewire persistent middleware (`AppServiceProvider`), so role checks also run on `/livewire/update` requests.
- Jetstream 5 views/components are published (`resources/views/components`, `x-button`, no `x-jet-` prefix). `layouts/app.blade.php` wraps `<x-base-layout>` so account pages (`/user/profile`) use the site header; there is no `dashboard` route. Jetstream strings are translated in `lang/es.json`. Tailwind `darkMode: 'class'` keeps Jetstream's `dark:` styles off.
- Password reset uses Fortify only (`/forgot-password`, `password.request`); `NeutralPasswordResetLinkResponse` answers unknown e-mails like sent links. Don't re-add custom routes with Fortify's names: duplicate names break `route:cache`.
- Sessions use JSON serialization and the cache only unserializes `cache.serializable_classes` (the models cached by `HomeComponent`). Caching a new object type requires adding its classes there.
- Livewire actions that send data (contact, service request, Pro receipt) are rate limited with `Concerns\ThrottlesActions`; route `throttle` does not cover `/livewire/update`.

- `AdminServicesByCategoryComponent` is now only served by the `authadmin` route `/admin/{category_slug}/services`; its `deleteService()` also re-checks `Auth::user()->utype === 'ADM'`. The former public `servicesbycategory/{category_slug}` route was removed — public category links use `home.services_by_category` (`/{category_slug}/services`).
- Home page cache keys (`home:*`, Redis, TTL `MARKETPLACE_CACHE_TTL` default 3600s) are only invalidated when `Service`, `ServiceCategory`, or `Slider` save/delete. After external edits, `php artisan cache:clear`.
- Public image files are deleted through `App\Services\ServiceAssetCleaner`, which builds absolute paths with `public_path()` (independent of CWD) and skips missing files. Don't reintroduce relative `unlink('images/...')` calls.
- Newer PHP files use `declare(strict_types=1)` + typed signatures; older tutorial-era files do not. Match the file you are editing.
- Visit counts are Redis counters (no DB write per view); `php artisan services:sync-visit-counters` flushes them to `services.views` and is scheduled every 5 minutes.
- Registration/contact dispatch queued mail jobs (queue `mail`, 3 tries). `LEAD_NOTIFICATION_ADDRESS` unset intentionally skips the lead email.
- See `docs/production-optimizations.md` — authoritative for schedulers, caching, queues, and freemium/Stripe setup.