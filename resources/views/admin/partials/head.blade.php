<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#14532d">
<title>@yield('title', 'Voz & Cifra')</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        color-scheme: light;
    }

    .theme-dark {
        color-scheme: dark;
    }

    .theme-dark body,
    body.theme-dark {
        background-color: #08111f !important;
        color: #e5eef8 !important;
    }

    body.theme-dark .bg-white,
    body.theme-dark .bg-white\/95,
    body.theme-dark .bg-white\/10 {
        background-color: #0f172a !important;
    }

    body.theme-dark .bg-gray-50,
    body.theme-dark .bg-slate-50 {
        background-color: #111c31 !important;
    }

    body.theme-dark .bg-gray-100 {
        background-color: #162236 !important;
    }

    body.theme-dark .bg-slate-900,
    body.theme-dark .bg-slate-950,
    body.theme-dark .bg-slate-950\/50,
    body.theme-dark .bg-green-900,
    body.theme-dark .bg-green-950,
    body.theme-dark .bg-green-950\/50 {
        background-color: #0b1424 !important;
    }

    body.theme-dark .border-gray-100,
    body.theme-dark .border-gray-200,
    body.theme-dark .border-gray-300,
    body.theme-dark .border-slate-700,
    body.theme-dark .border-green-800,
    body.theme-dark .border-green-700 {
        border-color: #243247 !important;
    }

    body.theme-dark .text-gray-900,
    body.theme-dark .text-gray-800,
    body.theme-dark .text-slate-900,
    body.theme-dark .text-slate-800,
    body.theme-dark .text-gray-700 {
        color: #f8fafc !important;
    }

    body.theme-dark .text-gray-600,
    body.theme-dark .text-gray-500,
    body.theme-dark .text-slate-700,
    body.theme-dark .text-slate-500,
    body.theme-dark .text-slate-400,
    body.theme-dark .text-green-300,
    body.theme-dark .text-green-400 {
        color: #94a3b8 !important;
    }

    body.theme-dark .text-green-700,
    body.theme-dark .text-emerald-700,
    body.theme-dark .text-blue-700,
    body.theme-dark .text-amber-700,
    body.theme-dark .text-red-700 {
        color: #dbeafe !important;
    }

    body.theme-dark .shadow-sm,
    body.theme-dark .shadow-xl,
    body.theme-dark .shadow-2xl {
        box-shadow: 0 18px 40px rgba(2, 6, 23, 0.35) !important;
    }

    body.theme-dark input,
    body.theme-dark select,
    body.theme-dark textarea {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    body.theme-dark input::placeholder,
    body.theme-dark textarea::placeholder {
        color: #64748b !important;
    }

    body.theme-dark .bg-emerald-50,
    body.theme-dark .bg-green-50,
    body.theme-dark .bg-blue-50,
    body.theme-dark .bg-amber-50,
    body.theme-dark .bg-red-50,
    body.theme-dark .bg-indigo-50,
    body.theme-dark .bg-indigo-100,
    body.theme-dark .bg-blue-100,
    body.theme-dark .bg-amber-100,
    body.theme-dark .bg-green-100,
    body.theme-dark .bg-gray-200 {
        background-color: #162236 !important;
    }

    body.theme-dark .hover\:bg-gray-50:hover,
    body.theme-dark .hover\:bg-gray-100:hover,
    body.theme-dark .hover\:bg-emerald-50:hover,
    body.theme-dark .hover\:bg-red-50:hover,
    body.theme-dark .hover\:bg-green-800:hover {
        background-color: #1d2a3d !important;
    }
</style>
<script>
    (() => {
        const preference = @json(auth()->user()->theme_preference ?? 'system');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const resolved = preference === 'system' ? (prefersDark ? 'dark' : 'light') : preference;
        document.documentElement.classList.toggle('theme-dark', resolved === 'dark');
        document.documentElement.classList.toggle('theme-light', resolved !== 'dark');
        document.body?.classList?.toggle('theme-dark', resolved === 'dark');
        document.body?.classList?.toggle('theme-light', resolved !== 'dark');
    })();
</script>
@stack('styles')
