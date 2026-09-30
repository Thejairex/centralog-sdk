<?php

namespace Centralog\ErrorMonitoring;

use Illuminate\Support\Str;
use Throwable;

class PayloadBuilder
{
    /**
     * Build an ingest payload from a throwable and Laravel context.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function build(
        Throwable $exception,
        string $environment,
        ?string $release = null,
        string $level = 'error',
        array $context = [],
    ): array {
        return [
            'event_id' => (string) Str::uuid(),
            'environment' => $environment,
            'release' => $release,
            'level' => $level,
            'exception' => [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ],
            'request' => self::requestContext(),
            'user' => self::userContext(),
            'context' => $context === [] ? null : $context,
        ];
    }

    /**
     * @return array{method: string, url: string, route: string|null}|null
     */
    protected static function requestContext(): ?array
    {
        if (! app()->bound('request')) {
            return null;
        }

        try {
            $request = request();

            return [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()->getName(),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{id: mixed, email: string|null, name: string|null}|null
     */
    protected static function userContext(): ?array
    {
        try {
            $user = auth()->guard()->user();

            if ($user === null) {
                return null;
            }

            return [
                'id' => $user->getAuthIdentifier(),
                'email' => $user->email ?? null,
                'name' => $user->name ?? null,
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
