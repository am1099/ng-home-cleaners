<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Something went wrong') — NG Home Cleaners</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        :root { color-scheme: light; }
        body { margin: 0; font-family: Georgia, 'Times New Roman', serif; background: #fff; color: #14201e; }
        .wrap { max-width: 40rem; margin: 0 auto; padding: 4rem 1.25rem; }
        .eyebrow { font-family: system-ui, sans-serif; font-size: 0.75rem; letter-spacing: 0.14em; text-transform: uppercase; color: #208378; font-weight: 600; }
        h1 { font-size: clamp(1.75rem, 4vw, 2.5rem); line-height: 1.15; margin: 0.5rem 0 1rem; }
        p { font-family: system-ui, sans-serif; font-size: 1.0625rem; line-height: 1.6; color: #3d4f4b; }
        .actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
        a.btn { font-family: system-ui, sans-serif; display: inline-flex; align-items: center; min-height: 2.75rem; padding: 0 1.25rem; border-radius: 999px; text-decoration: none; font-weight: 600; font-size: 0.875rem; }
        a.primary { background: #269b8e; color: #fff; }
        a.outline { border: 1px solid #d5ddd9; color: #14201e; }
    </style>
</head>
<body>
    <main class="wrap" id="main-content">
        @yield('content')
    </main>
</body>
</html>
