<?php

declare(strict_types=1);

namespace Furushka\Observability\Storage;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StorageInterface
{
    public function storeTrace(array $data): void;

    public function storeSpan(array $data): void;

    public function getTraces(int $perPage = 50): LengthAwarePaginator;

    public function getTrace(string $traceId): ?array;

    public function getSpans(string $traceId): array;

    public function prune(int $days): int;
}
