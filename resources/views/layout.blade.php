<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Observability') — Windows 95</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 16px;
            background: #008080; /* классический teal-фон рабочего стола */
            font-family: "MS Sans Serif", Tahoma, Geneva, sans-serif;
            font-size: 11px;
            color: #000;
            min-height: 100vh;
        }

        a { color: #0000c0; text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ===== ОКНО ===== */
        .win-window {
            background: #c0c0c0;
            border: 2px solid;
            border-color: #ffffff #000000 #000000 #ffffff;
            box-shadow: 1px 1px 0 0 #000;
            margin: 0 auto 16px;
            max-width: 1200px;
            padding: 2px;
        }

        .win-titlebar {
            background: linear-gradient(90deg, #000080 0%, #1084d0 100%);
            color: #fff;
            padding: 2px 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
            user-select: none;
        }

        .win-titlebar-text {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .win-titlebar-icon {
            width: 14px;
            height: 14px;
            background: #c0c0c0;
            border: 1px solid #000;
            display: inline-block;
        }

        .win-titlebar-buttons {
            display: flex;
            gap: 2px;
        }

        .win-btn-title {
            width: 16px;
            height: 14px;
            background: #c0c0c0;
            border: 1px solid;
            border-color: #ffffff #000000 #000000 #ffffff;
            font-size: 9px;
            font-weight: bold;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: default;
            color: #000;
        }

        /* ===== МЕНЮ ===== */
        .win-menubar {
            display: flex;
            padding: 1px 2px;
            background: #c0c0c0;
            border-bottom: 1px solid #808080;
        }

        .win-menubar-item {
            padding: 2px 6px;
            cursor: default;
        }
        .win-menubar-item:hover { background: #000080; color: #fff; }
        .win-menubar-item u { text-decoration: underline; }

        /* ===== ТЕЛО ОКНА ===== */
        .win-body {
            padding: 6px;
            background: #c0c0c0;
        }

        /* ===== ПАНЕЛИ ===== */
        .win-panel-raised {
            background: #c0c0c0;
            border: 2px solid;
            border-color: #ffffff #808080 #808080 #ffffff;
            padding: 6px;
        }

        .win-panel-sunken {
            background: #ffffff;
            border: 2px solid;
            border-color: #808080 #ffffff #ffffff #808080;
            padding: 0;
            overflow: auto;
        }

        /* ===== КНОПКИ ===== */
        .win-btn {
            display: inline-block;
            background: #c0c0c0;
            border: 2px solid;
            border-color: #ffffff #000000 #000000 #ffffff;
            padding: 3px 10px;
            font-family: inherit;
            font-size: 11px;
            color: #000;
            cursor: pointer;
            text-decoration: none;
            min-width: 70px;
            text-align: center;
            user-select: none;
        }
        .win-btn:active,
        .win-btn.is-active {
            border-color: #000000 #ffffff #ffffff #000000;
            padding: 4px 9px 2px 11px;
        }
        .win-btn:focus {
            outline: 1px dotted #000;
            outline-offset: -4px;
        }
        .win-btn:disabled,
        .win-btn.is-disabled {
            color: #808080;
            text-shadow: 1px 1px 0 #fff;
            cursor: default;
        }

        /* ===== ПОЛЯ ВВОДА ===== */
        .win-input {
            background: #fff;
            border: 2px solid;
            border-color: #808080 #ffffff #ffffff #808080;
            padding: 2px 4px;
            font-family: inherit;
            font-size: 11px;
            color: #000;
        }

        /* ===== ТАБЛИЦА ===== */
        .win-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 11px;
        }

        .win-table thead th {
            background: #c0c0c0;
            border: 1px solid;
            border-color: #ffffff #808080 #808080 #ffffff;
            padding: 2px 6px;
            text-align: left;
            font-weight: normal;
            white-space: nowrap;
            position: sticky;
            top: 0;
            user-select: none;
        }

        .win-table tbody td {
            padding: 2px 6px;
            border-bottom: 1px solid #e0e0e0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 320px;
        }

        .win-table tbody tr:hover { background: #000080; color: #fff; }
        .win-table tbody tr:hover a { color: #ffff80; }

        .win-table tbody tr.is-selected { background: #000080; color: #fff; }
        .win-table tbody tr.is-selected a { color: #ffff80; }

        /* ===== СТАТУС-БАР ===== */
        .win-statusbar {
            display: flex;
            gap: 2px;
            margin-top: 4px;
        }

        .win-statusbar-cell {
            background: #c0c0c0;
            border: 1px solid;
            border-color: #808080 #ffffff #ffffff #808080;
            padding: 2px 6px;
            font-size: 11px;
            flex: 1;
        }
        .win-statusbar-cell.is-narrow { flex: 0 0 120px; }

        /* ===== БЕЙДЖИ ===== */
        .win-badge {
            display: inline-block;
            padding: 1px 5px;
            background: #c0c0c0;
            border: 1px solid;
            border-color: #ffffff #808080 #808080 #ffffff;
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        .win-badge.is-ok    { background: #c0ffc0; }
        .win-badge.is-warn  { background: #ffe080; }
        .win-badge.is-error { background: #ff8080; color: #fff; }
        .win-badge.is-info  { background: #c0e0ff; }

        /* ===== ДЕТАЛИ (property list) ===== */
        .win-props {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 2px 8px;
            padding: 4px;
        }
        .win-props dt { font-weight: bold; }
        .win-props dd { margin: 0; word-break: break-all; }

        /* ===== ПАГИНАЦИЯ ===== */
        .win-pagination {
            display: flex;
            gap: 3px;
            align-items: center;
            justify-content: center;
            padding: 4px 0;
        }

        .win-pagination .win-btn { min-width: 32px; padding: 3px 6px; }

        /* ===== УТИЛИТЫ ===== */
        .win-row { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
        .win-row-between { justify-content: space-between; }
        .win-mt { margin-top: 6px; }
        .win-mb { margin-bottom: 6px; }
        .win-text-mono { font-family: "Courier New", monospace; font-size: 11px; }
        .win-text-small { font-size: 10px; color: #404040; }
        .win-text-right { text-align: right; }
        .win-pre {
            background: #fff;
            border: 2px solid;
            border-color: #808080 #ffffff #ffffff #808080;
            padding: 4px;
            margin: 0;
            font-family: "Courier New", monospace;
            font-size: 11px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 400px;
            overflow: auto;
        }

        /* ===== СПАН-СТРОКА ===== */
        .win-span {
            background: #fff;
            border: 2px solid;
            border-color: #808080 #ffffff #ffffff #808080;
            padding: 4px;
            margin-bottom: 4px;
        }
        .win-span-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px solid #c0c0c0;
        }
        .win-span-name { font-weight: bold; }
    </style>
</head>
<body>

<div class="win-window">
    <div class="win-titlebar">
        <div class="win-titlebar-text">
            <span class="win-titlebar-icon"></span>
            <span>@yield('window-title', 'Laravel Observability')</span>
        </div>
        <div class="win-titlebar-buttons">
            <span class="win-btn-title">_</span>
            <span class="win-btn-title">□</span>
            <span class="win-btn-title">×</span>
        </div>
    </div>

    <div class="win-menubar">
        <div class="win-menubar-item"><u>F</u>ile</div>
        <div class="win-menubar-item"><u>V</u>iew</div>
        <div class="win-menubar-item"><u>T</u>ools</div>
        <div class="win-menubar-item"><u>H</u>elp</div>
    </div>

    <div class="win-body">
        @yield('content')
    </div>
</div>

</body>
</html>