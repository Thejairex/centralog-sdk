<?php

namespace Centralog\ErrorMonitoring\Tests;

use Centralog\ErrorMonitoring\CentralogServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [CentralogServiceProvider::class];
    }
}
