<?php

declare(strict_types=1);

namespace Furushka\Observability\Context;

use Furushka\Observability\Storage\StorageInterface;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TraceContext
{
    private ?string $traceId = null;

    private float $startedAt = 0;

    private ?bool $sampled = null;

    private int $spanCount = 0;

    public function shouldSample(): bool
    {
        if ($this->sampled !== null) {
            return $this->sampled;
        }

        $rate = (float) config('observability.sampling.rate', 1.0);

        if ($rate >= 1.0) {
            return $this->sampled = true;
        }
        if ($rate <= 0.0) {
            return $this->sampled = false;
        }

        return $this->sampled = (mt_rand(0, 999) / 1000) < $rate;
    }

    public function startTrace(Request $request): void
    {
        // Сброс состояния между запросами (важно для Octane)
        $this->traceId = null;
        $this->startedAt = 0;
        $this->spanCount = 0;
        $this->sampled = null;

        if (! $this->shouldSample()) {
            return;
        }

        $this->traceId = Str::uuid()->toString();
        $this->startedAt = microtime(true);

        Log::shareContext([
            'trace_id' => $this->traceId,
        ]);
    }

    public function recordQuerySpan($query, StorageInterface $storage): void
    {
        if (! $this->traceId) {
            return;
        }

        $bindings = config('observability.redact_bindings', true)
            ? $this->sanitizeBindings($query->bindings)
            : $query->bindings;

        $this->spanCount++;

        $storage->storeSpan([
            'trace_id' => $this->traceId,
            'name' => 'sql',
            'sql' => $query->sql,
            'bindings' => json_encode($bindings),
            'duration_ms' => (float) $query->time,
            'is_slow' => $query->time > config('observability.slow_query_threshold', 200),
            'created_at' => now(),
        ]);
    }

    public function recordException(MessageLogged $event, StorageInterface $storage): void
    {
        if (! isset($event->context['exception'])) {
            return;
        }

        if (! in_array($event->level, ['error', 'critical', 'alert', 'emergency'], true)) {
            return;
        }

        // Если трейс не стартовал, но конфиг требует всегда писать ошибки — стартуем принудительно
        if (! $this->traceId && config('observability.sampling.always_sample_errors', true)) {
            $this->traceId = Str::uuid()->toString();
            $this->startedAt = microtime(true);
            $this->sampled = true;
        }

        if (! $this->traceId) {
            return;
        }

        /** @var \Throwable $e */
        $e = $event->context['exception'];

        $this->spanCount++;

        $storage->storeSpan([
            'trace_id' => $this->traceId,
            'name' => 'exception',
            'message' => $e->getMessage(),
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'created_at' => now(),
        ]);
    }

    public function endTrace(Request $request, Response $response, StorageInterface $storage): void
    {
        if (! $this->traceId) {
            return;
        }

        $storage->storeTrace([
            'trace_id' => $this->traceId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => (microtime(true) - $this->startedAt) * 1000,
            'spans_count' => $this->spanCount,
            'created_at' => now(),
        ]);

        // Чистим контекст логгера и состояние (важно для Octane)
        Log::shareContext(['trace_id' => null]);

        $this->traceId = null;
        $this->startedAt = 0;
        $this->spanCount = 0;
        $this->sampled = null;
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    /**
     * Прячем потенциально чувствительные значения: длинные строки,
     * похожие на токены/хеши/ключи.
     */
    private function sanitizeBindings(array $bindings): array
    {
        return array_map(function ($value) {
            if (is_string($value)
                && strlen($value) >= 32
                && preg_match('/^[A-Za-z0-9+\/=._\-]+$/', $value)
            ) {
                return '[REDACTED]';
            }

            return $value;
        }, $bindings);
    }
}
