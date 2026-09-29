@extends('observability::layout')

@section('title', 'Traces')
@section('window-title', 'Laravel Observability — Traces')

@section('content')

    {{-- Панель действий --}}
    <div class="win-panel-raised win-mb">
        <div class="win-row win-row-between">
            <div class="win-row">
                <a href="{{ route('observability.index') }}" class="win-btn">⟳ Обновить</a>
                <span class="win-text-small">
                    Показано: {{ $traces->count() }} из {{ $traces->total() }}
                </span>
            </div>
            <div class="win-row">
                <span class="win-text-small">Фильтр:</span>
                <input type="text" class="win-input" placeholder="path..." style="width: 180px" disabled>
                <button class="win-btn is-disabled" disabled>Найти</button>
            </div>
        </div>
    </div>

    {{-- Таблица трейсов --}}
    <div class="win-panel-sunken win-mb" style="max-height: 600px;">
        <table class="win-table">
            <thead>
                <tr>
                    <th style="width: 150px;">Время</th>
                    <th style="width: 70px;">Метод</th>
                    <th>Путь</th>
                    <th style="width: 70px;">Статус</th>
                    <th style="width: 100px;" class="win-text-right">Длит., мс</th>
                    <th style="width: 80px;" class="win-text-right">Спанов</th>
                    <th style="width: 60px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($traces as $trace)
                    @php
                        $status = (int) $trace->status;
                        $badgeClass = $status >= 500 ? 'is-error'
                                    : ($status >= 400 ? 'is-warn'
                                    : ($status >= 300 ? 'is-info' : 'is-ok'));
                        $isSlow = $trace->duration_ms > 500;
                    @endphp
                    <tr>
                        <td class="win-text-mono">
                            {{ \Carbon\Carbon::parse($trace->created_at)->format('d.m H:i:s') }}
                        </td>
                        <td class="win-text-mono">
                            <span class="win-badge">{{ $trace->method }}</span>
                        </td>
                        <td title="{{ $trace->path }}">
                            {{ Str::limit($trace->path, 80) }}
                        </td>
                        <td>
                            <span class="win-badge {{ $badgeClass }}">{{ $status }}</span>
                        </td>
                        <td class="win-text-right win-text-mono">
                            {{ number_format((float) $trace->duration_ms, 1) }}
                            @if ($isSlow) <span class="win-badge is-warn" title="Медленный запрос">!</span> @endif
                        </td>
                        <td class="win-text-right win-text-mono">{{ $trace->spans_count }}</td>
                        <td>
                            <a href="{{ route('observability.show', $trace->trace_id) }}" class="win-btn" style="min-width: 0; padding: 1px 6px;">→</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #808080;">
                            Нет данных. Трейсы появятся после первого запроса к сайту.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Пагинация --}}
    @if ($traces->hasPages())
        <div class="win-panel-raised win-mb">
            <div class="win-pagination">
                @if ($traces->onFirstPage())
                    <span class="win-btn is-disabled">|◄</span>
                    <span class="win-btn is-disabled">◄</span>
                @else
                    <a href="{{ $traces->url(1) }}" class="win-btn">|◄</a>
                    <a href="{{ $traces->previousPageUrl() }}" class="win-btn">◄</a>
                @endif

                <span class="win-text-small">
                    Страница {{ $traces->currentPage() }} из {{ $traces->lastPage() }}
                </span>

                @if ($traces->hasMorePages())
                    <a href="{{ $traces->nextPageUrl() }}" class="win-btn">►</a>
                    <a href="{{ $traces->url($traces->lastPage()) }}" class="win-btn">►|</a>
                @else
                    <span class="win-btn is-disabled">►</span>
                    <span class="win-btn is-disabled">►|</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Статусбар --}}
    <div class="win-statusbar">
        <div class="win-statusbar-cell">
            Всего трейсов: <b>{{ $traces->total() }}</b>
        </div>
        <div class="win-statusbar-cell is-narrow">
            Страница {{ $traces->currentPage() }} / {{ $traces->lastPage() }}
        </div>
    </div>

@endsection