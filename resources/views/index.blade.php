<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Logs</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container { max-width: 900px; margin: 0 auto; }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #f8fafc;
        }

        .subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            background: #052e16;
            border: 1px solid #166534;
            color: #86efac;
        }

        .empty {
            text-align: center;
            padding: 4rem 2rem;
            color: #475569;
        }

        .empty svg { margin: 0 auto 1rem; display: block; opacity: 0.4; }

        .log-list { display: flex; flex-direction: column; gap: 0.75rem; }

        .log-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            overflow: hidden;
            transition: border-color 0.15s;
        }

        .log-card:hover { border-color: #475569; }

        .log-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            cursor: pointer;
            user-select: none;
        }

        .log-icon {
            width: 36px;
            height: 36px;
            background: #0f172a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .log-icon svg { color: #64748b; }

        .log-info { flex: 1; min-width: 0; }

        .log-name {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-meta {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.125rem;
        }

        .log-meta span + span::before { content: ' · '; }

        .chevron {
            color: #475569;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .log-card.open .chevron { transform: rotate(180deg); }

        .log-body {
            display: none;
            border-top: 1px solid #334155;
            padding: 1rem 1.25rem;
            gap: 0.625rem;
            flex-wrap: wrap;
        }

        .log-card.open .log-body { display: flex; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s, background 0.15s;
        }

        .btn:hover { opacity: 0.85; }

        .btn-view {
            background: #1d4ed8;
            color: #fff;
        }

        .btn-delete {
            background: #7f1d1d;
            color: #fca5a5;
            border: 1px solid #991b1b;
        }

        .btn-delete:hover { background: #991b1b; opacity: 1; }

        form { display: inline; }
    </style>
</head>
<body>
<div class="container">
    <h1>Laravel Logs</h1>
    <p class="subtitle">{{ count($logs) }} file{{ count($logs) !== 1 ? 's' : '' }} in storage/logs</p>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    @if(empty($logs))
        <div class="empty">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
            <p>No log files found.</p>
        </div>
    @else
        <div class="log-list">
            @foreach($logs as $log)
                <div class="log-card" id="card-{{ $loop->index }}">
                    <div class="log-header" onclick="toggle({{ $loop->index }})">
                        <div class="log-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <div class="log-info">
                            <div class="log-name">{{ $log['name'] }}</div>
                            <div class="log-meta">
                                <span>{{ $log['modified_formatted'] }}</span>
                                <span>{{ $log['size'] }}</span>
                            </div>
                        </div>
                        <svg class="chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="log-body">
                        <a href="{{ route('laravel-log.view', ['file' => $log['name']]) }}" class="btn btn-view">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            View
                        </a>
                        <form method="POST" action="{{ route('laravel-log.delete', ['file' => $log['name']]) }}"
                              onsubmit="return confirm('Delete {{ $log['name'] }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    function toggle(index) {
        const card = document.getElementById('card-' + index);
        card.classList.toggle('open');
    }
</script>
</body>
</html>
