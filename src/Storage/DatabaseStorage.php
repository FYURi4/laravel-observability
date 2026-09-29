<?php

declare(strict_types=1);

namespace Furushka\Observability\Storage;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DatabaseStorage implements StorageInterface
{
    public function storeTrace(array $data): void
    {
        DB::table('observability_traces')->insert($data);
    }

    public function storeSpan(array $data): void
    {
        DB::table('observability_spans')->insert($data);
    }

    public function getTraces(int $perPage = 50): LengthAwarePaginator
    {
        return DB::table('observability_traces')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getTrace(string $traceId): ?array
    {
        $trace = DB::table('observability_traces')
            ->where('trace_id', $traceId)
            ->first();

        return $trace ? (array) $trace : null;
    }

    public function getSpans(string $traceId): array
    {
        return DB::table('observability_spans')
            ->where('trace_id', $traceId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function prune(int $days): int
    {
        $cutoff = now()->subDays($days);

        $spans = DB::table('observability_spans')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $traces = DB::table('observability_traces')
            ->where('created_at', '<', $cutoff)
            ->delete();

        return $spans + $traces;
    }
}
