<?php

declare(strict_types=1);

namespace Furushka\Observability\Http\Controllers;

use Furushka\Observability\Storage\StorageInterface;

class TraceController
{
    public function __construct(private StorageInterface $storage) {}

    public function index()
    {
        $perPage = (int) config('observability.ui.per_page', 50);
        $traces = $this->storage->getTraces($perPage);

        return view('observability::index', ['traces' => $traces]);
    }

    public function show(string $traceId)
    {
        $trace = $this->storage->getTrace($traceId);
        abort_if($trace === null, 404);

        $spans = $this->storage->getSpans($traceId);

        return view('observability::show', [
            'trace' => $trace,
            'spans' => $spans,
        ]);
    }
}
