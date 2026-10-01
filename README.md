# Centralog Error Monitoring — Laravel SDK

Laravel SDK for [Centralog](https://github.com/Thejairex/centralog) error monitoring.
Captures exceptions and sends a copy to your Centralog platform. Never breaks your app.

## Install

```bash
composer require centralog/error-monitoring-laravel
```

Publish the config:

```bash
php artisan vendor:publish --provider="Centralog\ErrorMonitoring\CentralogServiceProvider" --tag="centralog-config"
```

## Configure

`.env`:

```env
CENTRALOG_ENABLED=true
CENTRALOG_ENDPOINT=https://centralog.tudominio.com/api/v1/ingest/events
CENTRALOG_API_KEY=clk_tu_api_key
CENTRALOG_ENVIRONMENT=production
CENTRALOG_RELEASE=2026.09.30.1
CENTRALOG_TIMEOUT=2
```

Generate the API key in Centralog: Projects → API Keys.

## Usage

Report exceptions from your exception handler (`bootstrap/app.php`):

```php
use Centralog\ErrorMonitoring\Facades\Centralog;
use Illuminate\Foundation\Configuration\Exceptions;
use Throwable;

->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->report(fn (Throwable $e) => Centralog::capture($e));
})
```

Add extra context anywhere:

```php
Centralog::context(['tenant' => 'acme', 'order_id' => 9182]);
```

Your local `laravel.log` keeps working as always — the SDK only sends a **copy** to Centralog.

## Custom errors

Any exception (including your domain exceptions) can be captured manually
with a level (`error`, `warning`, `info` or `debug`):

```php
use Centralog\ErrorMonitoring\Facades\Centralog;

class PaymentFailedException extends \RuntimeException {}

try {
    charge($order);
} catch (PaymentFailedException $e) {
    Centralog::capture($e, ['order_id' => $order->id], 'warning');
}
```

Each exception is grouped in Centralog by **class + file + line**, so every
custom exception in your domain gets its own group in the panel.

Valid levels: `error` (default), `warning`, `info`, `debug`.

## Safety

- Short timeout (2s default), never blocks a request for long.
- Any network failure is swallowed and logged with `Log::debug`.
- Disabled automatically when `CENTRALOG_ENABLED=false` or endpoint/key are missing.
- No queue worker required (synchronous by design).

## Testing

```bash
composer test
```
