<?php

namespace Centralog\ErrorMonitoring;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CentralogClient
{
    /**
     * @var array<string, mixed>
     */
    protected array $extraContext = [];

    public function __construct(
        protected string $endpoint,
        protected string $apiKey,
        protected string $environment,
        protected ?string $release = null,
        protected int $timeout = 2,
        protected bool $enabled = true,
    ) {}

    /**
     * Capture a throwable and send a copy to Centralog.
     *
     * Never throws: any failure is logged locally and swallowed so
     * reporting can never break the host application.
     *
     * @param  array<string, mixed>  $context
     */
    public function capture(Throwable $exception, array $context = [], string $level = 'error'): bool
    {
        if (! $this->enabled || $this->endpoint === '' || $this->apiKey === '') {
            return false;
        }

        try {
            $payload = PayloadBuilder::build(
                $exception,
                $this->environment,
                $this->release,
                $level,
                array_merge($this->extraContext, $context),
            );

            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->connectTimeout($this->timeout)
                ->post($this->endpoint, $payload);

            return $response->successful();
        } catch (Throwable $reportingFailure) {
            Log::debug('Centralog reporting failed: '.$reportingFailure->getMessage());

            return false;
        }
    }

    /**
     * Add extra context merged into every captured event.
     *
     * @param  array<string, mixed>  $context
     */
    public function context(array $context): void
    {
        $this->extraContext = array_merge($this->extraContext, $context);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->extraContext;
    }

    public function flushContext(): void
    {
        $this->extraContext = [];
    }
}
