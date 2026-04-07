<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Print')</title>
    <style>
        :root { --text:#111827; --muted:#6b7280; --line:#111827; }
        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; color:var(--text); font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif; }
        body { background:#e5e7eb; }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 18mm 16mm;
            background: #f6f6f6;
            /* soft paper texture */
            background-image:
                radial-gradient(1200px 900px at 10% 0%, rgba(0,0,0,.03), transparent 60%),
                radial-gradient(900px 700px at 90% 100%, rgba(0,0,0,.02), transparent 60%),
                repeating-linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.10) 6px, rgba(0,0,0,.015) 6px, rgba(0,0,0,.015) 12px);
            box-shadow: 0 12px 28px rgba(0,0,0,.12);
        }
        .no-print { display: block; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; }
            .page { width: auto; min-height: auto; margin: 0; padding: 18mm 16mm; box-shadow:none; }
            .no-print { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
@yield('content')
@stack('scripts')
</body>
</html>
