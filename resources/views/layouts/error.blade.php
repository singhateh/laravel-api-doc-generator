<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'API Docs Error') - {{ config('app.name') }}</title>

    <!-- Tailwind CSS from published package -->
    <link href="{{ asset('vendor/api-doc-generator/css/api-docs.css') }}" rel="stylesheet">

    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Lucide Icons -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/lucide@0.259.0/dist/lucide.min.js"></script>


    <!-- Highlight.js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; transition: all 0.3s ease; }
    </style>
    @stack('styles')
    
</head>
<body class="h-full bg-gray-50 dark:bg-dark-950 transition-colors duration-300">

        <main class="text-center px-4">
            @yield('content')
        </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.5.0/axios.min.js"></script>

    <script>
        // Initialize highlight.js
        document.addEventListener('DOMContentLoaded', function() {
            hljs?.highlightAll();
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.replace();
            }

            lucide.createIcons();
        });

        // Optional: theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        html.classList.toggle('dark', savedTheme === 'dark');

        themeToggle?.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

    </script>

    @stack('scripts')
</body>
</html>
