<?php

namespace Centralog\ErrorMonitoring\Facades;

use Centralog\ErrorMonitoring\CentralogClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool capture(\Throwable $exception, array<string, mixed> $context = [], string $level = 'error')
 * @method static void context(array<string, mixed> $context)
 * @method static array<string, mixed> getContext()
 * @method static void flushContext()
 *
 * @see CentralogClient
 */
class Centralog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'centralog';
    }
}
