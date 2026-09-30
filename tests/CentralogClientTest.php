<?php

use Centralog\ErrorMonitoring\CentralogClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * @param  array<string, mixed>  $overrides
 */
function makeClient(array $overrides = []): CentralogClient
{
    return new CentralogClient(...array_merge([
        'endpoint' => 'https://centralog.test/api/v1/ingest/events',
        'apiKey' => 'clk_test_key',
        'environment' => 'production',
        'release' => '2026.09.30.1',
        'timeout' => 2,
        'enabled' => true,
    ], $overrides));
}

test('capture posts the payload with bearer auth', function () {
    Http::fake([
        'centralog.test/*' => Http::response(['event_id' => 'event_123'], 201),
    ]);

    $client = makeClient();

    expect($client->capture(new RuntimeException('Payment failed')))->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://centralog.test/api/v1/ingest/events'
            && $request->header('Authorization') === ['Bearer clk_test_key']
            && $request['exception']['class'] === RuntimeException::class
            && $request['exception']['message'] === 'Payment failed'
            && $request['environment'] === 'production'
            && $request['release'] === '2026.09.30.1'
            && $request['event_id'] !== '';
    });
});

test('capture returns false on server error without throwing', function () {
    Http::fake(['centralog.test/*' => Http::response('boom', 500)]);

    expect(makeClient()->capture(new RuntimeException('Payment failed')))->toBeFalse();
});

test('capture returns false on connection failure without throwing', function () {
    Http::fake(function () {
        throw new RuntimeException('Connection refused');
    });

    expect(makeClient()->capture(new RuntimeException('Payment failed')))->toBeFalse();
});

test('capture does nothing when disabled', function () {
    Http::fake();

    expect(makeClient(['enabled' => false])->capture(new RuntimeException('x')))->toBeFalse();

    Http::assertNothingSent();
});

test('capture does nothing without endpoint or key', function () {
    Http::fake();

    expect(makeClient(['endpoint' => ''])->capture(new RuntimeException('x')))->toBeFalse();
    expect(makeClient(['apiKey' => ''])->capture(new RuntimeException('x')))->toBeFalse();

    Http::assertNothingSent();
});

test('extra context is merged into captured events', function () {
    Http::fake(['centralog.test/*' => Http::response([], 201)]);

    $client = makeClient();
    $client->context(['tenant' => 'acme']);

    expect($client->getContext())->toBe(['tenant' => 'acme']);
    expect($client->capture(new RuntimeException('x'), ['order_id' => 1]))->toBeTrue();

    Http::assertSent(fn ($request) => $request['context'] === ['tenant' => 'acme', 'order_id' => 1]);

    $client->flushContext();

    expect($client->getContext())->toBe([]);
});
