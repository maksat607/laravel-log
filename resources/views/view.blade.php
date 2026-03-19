<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $filename }} — Laravel Logs</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container { max-width: 1100px; margin: 0 auto; }

        .top-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.15s;
        }

        .back-link:hover { color: #94a3b8; }

        h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f8fafc;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .count-badge {
            font-size: 0.75rem;
            background: #1e293b;
            border: 1px solid #334155;
            color: #94a3b8;
            padding: 0.25rem 0.625rem;
            border-radius: 999px;
            white-space: nowrap;
        }

        .empty {
            text-align: center;
            padding: 4rem 2rem;
            color: #475569;
        }

        /* Date group */
        .date-group { margin-bottom: 1.25rem; }

        .date-toggle {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 1rem;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 0.875rem;
            font-weight: 600;
            color: #94a3b8;
            width: 100%;
            text-align: left;
            transition: background 0.15s;
        }

        .date-toggle:hover { background: #263248; }

        .date-toggle .chevron {
            color: #475569;
            transition: transform 0.2s;
            margin-left: auto;
        }

        .date-group.open .date-toggle .chevron { transform: rotate(180deg); }

        .date-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #475569;
            flex-shrink: 0;
        }

        .entries { display: none; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem; padding-left: 0.5rem; }
        .date-group.open .entries { display: flex; }

        /* Entry card */
        .entry {
            background: #1e293b;
            border: 1px solid #334155;
            border-left: 3px solid #334155;
            border-radius: 6px;
            overflow: hidden;
        }

        .entry.level-error,
        .entry.level-critical,
        .entry.level-alert,
        .entry.level-emergency { border-left-color: #ef4444; }

        .entry.level-warning { border-left-color: #f59e0b; }
        .entry.level-info    { border-left-color: #3b82f6; }
        .entry.level-debug   { border-left-color: #8b5cf6; }
        .entry.level-notice  { border-left-color: #10b981; }

        .entry-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.875rem;
            cursor: pointer;
            user-select: none;
        }

        .level-pill {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .level-error .level-pill,
        .level-critical .level-pill,
        .level-alert .level-pill,
        .level-emergency .level-pill { background: #450a0a; color: #fca5a5; }

        .level-warning .level-pill { background: #451a03; color: #fcd34d; }
        .level-info .level-pill    { background: #172554; color: #93c5fd; }
        .level-debug .level-pill   { background: #2e1065; color: #c4b5fd; }
        .level-notice .level-pill  { background: #022c22; color: #6ee7b7; }

        .entry-time {
            font-size: 0.75rem;
            color: #64748b;
            font-variant-numeric: tabular-nums;
            flex-shrink: 0;
        }

        .entry-preview {
            font-size: 0.8125rem;
            color: #94a3b8;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .entry-chevron {
            color: #475569;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .entry.open .entry-chevron { transform: rotate(180deg); }

        .entry-body {
            display: none;
            border-top: 1px solid #334155;
            padding: 0.875rem;
            background: #0f172a;
        }

        .entry.open .entry-body { display: block; }

        pre {
            font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
            font-size: 0.75rem;
            color: #94a3b8;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="{{ route('laravel-log.index') }}" class="back-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            All logs
        </a>
        <h1>{{ $filename }}</h1>
        <span class="count-badge">{{ count($entries) }} entries</span>
    </div>

    @if(empty($entries))
        <div class="empty">No log entries found.</div>
    @else
        @php
            $grouped = [];
            foreach ($entries as $i => $entry) {
                $date = $entry['date'] ?? 'Unknown';
                $grouped[$date][] = ['index' => $i, 'entry' => $entry];
            }
        @endphp

        @foreach($grouped as $date => $group)
            @php $groupId = 'group-' . Str::slug($date); @endphp
            <div class="date-group open" id="{{ $groupId }}">
                <button class="date-toggle" onclick="toggleGroup('{{ $groupId }}')">
                    <span class="date-dot"></span>
                    {{ $date }}
                    <span style="color:#475569;font-weight:400;font-size:0.75rem">{{ count($group) }} entries</span>
                    <svg class="chevron" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                    </svg>
                </button>
                <div class="entries">
                    @foreach($group as $item)
                        @php $entry = $item['entry']; $idx = $item['index']; @endphp
                        <div class="entry level-{{ $entry['level'] }}" id="entry-{{ $idx }}">
                            <div class="entry-header" onclick="toggleEntry('entry-{{ $idx }}')">
                                <span class="level-pill">{{ $entry['level'] }}</span>
                                @if($entry['timestamp'])
                                    <span class="entry-time">{{ $entry['timestamp'] }}</span>
                                @endif
                                <span class="entry-preview">{{ Str::limit($entry['body'], 120) }}</span>
                                <svg class="entry-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                                </svg>
                            </div>
                            <div class="entry-body">
                                <pre>{{ $entry['body'] }}</pre>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>

<script>
    function toggleGroup(id) {
        document.getElementById(id).classList.toggle('open');
    }

    function toggleEntry(id) {
        document.getElementById(id).classList.toggle('open');
    }
</script>
</body>
</html>
