<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#4a2b22">
<title>@yield('title', 'Voz & Cifra')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
@include('partials.frontend-assets')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="{{ asset('css/admin/theme.css') }}">
<style>
    :root {
        color-scheme: light;
    }

    .theme-dark {
        color-scheme: dark;
    }
</style>
<script>
    (() => {
        const preference = @json(auth()->user()->theme_preference ?? 'system');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const resolved = preference === 'system' ? (prefersDark ? 'dark' : 'light') : preference;
        const applyTheme = () => {
            document.documentElement.classList.toggle('theme-dark', resolved === 'dark');
            document.documentElement.classList.toggle('theme-light', resolved !== 'dark');
            document.body?.classList?.toggle('theme-dark', resolved === 'dark');
            document.body?.classList?.toggle('theme-light', resolved !== 'dark');
        };

        applyTheme();

        if (!document.body) {
            document.addEventListener('DOMContentLoaded', applyTheme, { once: true });
        }
    })();
</script>
@stack('styles')
