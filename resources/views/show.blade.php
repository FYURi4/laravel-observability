<h1>Trace {{ $trace['trace_id'] }}</h1>

<p>
    <strong>{{ $trace['method'] }}</strong> {{ $trace['path'] }} —
    Status: {{ $trace['status'] }} —
    Duration: {{ round($trace['duration_ms']) }} ms
</p>

<h2>Spans ({{ count($spans) }})</h2>
<table>
    <thead>
        <tr><th>Type</th><th>Details</th><th>Duration</th></tr>
    </thead>
    <tbody>
        @foreach ($spans as $span)
        <tr>
            <td>{{ $span->name }}</td>
            <td>
                @if ($span->name === 'sql')
                    {{ $span->sql }}
                    @if ($span->is_slow) <span style="color:red">⚠ SLOW</span> @endif
                @elseif ($span->name === 'exception')
                    {{ $span->class }}: {{ $span->message }}
                @endif
            </td>
            <td>{{ $span->duration_ms ? round($span->duration_ms) . ' ms' : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
