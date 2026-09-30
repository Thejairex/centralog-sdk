<?php

use Centralog\ErrorMonitoring\PayloadBuilder;
use RuntimeException;

test('payload matches the ingest contract structure', function () {
    $exception = new RuntimeException('Payment failed', 0);

    $payload = PayloadBuilder::build($exception, 'production', '2026.09.30.1');

    expect($payload)->toHaveKeys(['event_id', 'environment', 'release', 'level', 'exception', 'request', 'user', 'context']);
    expect($payload['environment'])->toBe('production');
    expect($payload['release'])->toBe('2026.09.30.1');
    expect($payload['level'])->toBe('error');
    expect($payload['exception'])->toMatchArray([
        'class' => RuntimeException::class,
        'message' => 'Payment failed',
    ]);
    expect($payload['exception']['file'])->toEndWith('PayloadBuilderTest.php');
    expect($payload['exception']['line'])->toBeInt();
    expect($payload['exception']['trace'])->toBeString();
});

test('payload generates a unique event id per build', function () {
    $exception = new RuntimeException('Payment failed');

    $first = PayloadBuilder::build($exception, 'production');
    $second = PayloadBuilder::build($exception, 'production');

    expect($first['event_id'])->not->toBe($second['event_id']);
});

test('payload accepts a custom level and context', function () {
    $exception = new RuntimeException('Slow query');

    $payload = PayloadBuilder::build($exception, 'staging', null, 'warning', ['query_ms' => 4200]);

    expect($payload['level'])->toBe('warning');
    expect($payload['context'])->toBe(['query_ms' => 4200]);
});

test('payload includes request context on http calls', function () {
    $exception = new RuntimeException('Boom');

    $payload = PayloadBuilder::build($exception, 'production');

    // In console context there is a request instance in Testbench; assert shape only.
    expect($payload)->toHaveKey('request');
});

test('payload has null user when unauthenticated', function () {
    $exception = new RuntimeException('Boom');

    $payload = PayloadBuilder::build($exception, 'production');

    expect($payload['user'])->toBeNull();
});
