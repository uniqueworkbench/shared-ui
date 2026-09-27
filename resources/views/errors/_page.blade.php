<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ config('app.name', 'Unique Workbench') }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Figtree, ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            padding: 1.5rem;
        }
        .card {
            max-width: 32rem;
            width: 100%;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .code {
            font-size: .875rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #0d6efd;
            margin: 0 0 .5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 .75rem;
        }
        p.message {
            color: #4b5563;
            line-height: 1.5;
            margin: 0 0 1.75rem;
        }
        .actions {
            display: flex;
            gap: .75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: .625rem 1.25rem;
            border-radius: .5rem;
            font-weight: 500;
            font-size: .9375rem;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: #0d6efd;
            color: #fff;
        }
        .btn-primary:hover { background: #0b5ed7; }
        .btn-secondary {
            background: #fff;
            color: #374151;
            border-color: #d1d5db;
        }
        .btn-secondary:hover { background: #f3f4f6; }
        .ref {
            margin-top: 1.75rem;
            font-size: .8125rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="code">Error {{ $code }}</p>
        <h1>{{ $heading }}</h1>
        <p class="message">{{ $message }}</p>
        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="trigNav('back')">
                &larr; Go back
            </button>
            <a href="/" class="btn btn-primary" onclick="return trigNav('home')">Home</a>
        </div>
        @if ($errorId ?? null)
            <p class="ref">Reference #{{ $errorId }}</p>
        @endif
    </div>
    <script>
        // Some browser/link-preview contexts (e.g. chat-app link unfurling, embedded
        // browser panes) render this page inside a sandboxed, cross-origin srcdoc
        // iframe. In that case a sandboxed top-level navigation attempt is blocked
        // *silently* (console warning only — it doesn't throw), so we can't detect
        // failure with try/catch and branch on it. Instead we always attempt the
        // top-level navigation AND queue a same-frame fallback a beat later: if the
        // top navigation actually went through, this frame is torn down before the
        // fallback fires and it never runs; if it was blocked, the fallback still
        // gets the user somewhere instead of leaving the button dead.
        function trigNav(mode) {
            if (window.top !== window.self) {
                try {
                    if (mode === 'back' && window.top.document && window.top.document.referrer) {
                        window.top.history.back();
                    } else {
                        window.top.location.href = '/';
                    }
                } catch (e) {
                    // cross-origin without allow-same-origin — ignore, fallback below covers it
                }
            }

            setTimeout(function () {
                if (mode === 'back' && window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/';
                }
            }, 100);

            return false;
        }
    </script>
</body>
</html>
