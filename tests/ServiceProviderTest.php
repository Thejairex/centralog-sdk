<?php

use Centralog\ErrorMonitoring\CentralogClient;
use Centralog\ErrorMonitoring\Facades\Centralog;
use Illuminate\Support\Facades\Http;
use RuntimeException;

test('config is merged with defaults', function () {
    expect(config('centralog.enabled'))->toBeTrue();
    expect(config('centralog.timeout'))->toBe(2);
});

test('facade resolves to the client and captures', function () {
    Http::fake(['*' => Http::response([], 201)]);

    config()->set('centralog.endpoint', 'https://centralog.test/api/v1/ingest/events');
    config()->set('centralog.api_key', 'clk_test_key');

    expect(Centralog::capture(new RuntimeException('Boom')))->toBeTrue();

    Http::assertSent(fn ($request) => $request->header('Authorization') === ['Bearer clk_test_key']);
});

test('client is bound as singleton', function () {
    expect(app(CentralogClient::class))->toBe(app(CentralogClient::class));
});
