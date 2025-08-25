@extends('api-doc-generator::layout')

@section('content')
<div class="min-h1-screen bg1-white dark1:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800 transition-colors duration-300 flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full space-y-8">
        <!-- Animated 404 Illustration -->
        <div class="relative">
            <div class="absolute -inset-4 bg-purple-600/10 dark:bg-purple-600/20 rounded-lg blur-lg animate-pulse"></div>
            <div class="relative bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-2xl p-8 border border-gray-200 dark:border-gray-700/50 shadow-lg dark:shadow-2xl transition-all duration-300">
                <!-- Floating circles -->
                <div class="absolute -top-3 -left-3 w-6 h-6 bg-purple-500 rounded-full animate-bounce opacity-60"></div>
                <div class="absolute -bottom-2 -right-2 w-4 h-4 bg-blue-400 rounded-full animate-bounce delay-150 opacity-40"></div>
                <div class="absolute top-4 -right-4 w-3 h-3 bg-cyan-400 rounded-full animate-ping opacity-30"></div>
                
                <!-- Main content -->
                <div class="text-center">
                    <!-- Animated 404 text -->
                    <div class="relative mb-6">
                        <div class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 animate-pulse">
                            404
                        </div>
                        <div class="absolute inset-0 text-9xl font-bold text-purple-400/20 blur-sm">
                            404
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4 tracking-wide transition-colors duration-300">
                        <i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>
                        Documentation Not Found
                    </h1>

                    <!-- Message -->
                    <p class="text-gray-600 dark:text-gray-300 mb-8 leading-relaxed text-lg transition-colors duration-300">
                        {{ $message ?? 'The API documentation you\'re looking for seems to be missing.' }}
                    </p>

                    <!-- Action buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <!-- Home button -->
                        <a href="{{ route('api-docs.index') }}" 
                           class="group relative flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg font-semibold shadow-lg hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-r from-purple-700 to-blue-700 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-home mr-2 group-hover:animate-bounce transition-transform duration-300"></i>
                            <span class="relative">Go Home</span>
                        </a>

                        <!-- Generate button -->
                        <button onclick="event.preventDefault(); document.getElementById('generate-form').submit();"
                                class="group relative flex items-center justify-center px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-r from-cyan-900 to-blue-900 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <i class="fas fa-sync-alt mr-2 group-hover:animate-spin transition-transform duration-300"></i>
                            <span class="relative">Generate Docs</span>
                        </button>
                    </div>

                    <!-- Hidden form -->
                    <form id="generate-form" action="{{ route('api-docs.generate') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>

        <!-- Additional help text -->
        <div class="text-center">
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-6 transition-colors duration-300">
                <i class="fas fa-lightbulb text-yellow-400 mr-1"></i>
                Tip: Make sure your controllers have proper annotations
            </p>
        </div>

        <!-- Dark mode toggle button (optional) -->
        <div class="text-center">
            <button onclick="toggleDarkMode()" class="text-gray-500 dark:text-gray-400 hover:text-purple-400 transition-colors duration-300">
                <i class="fas fa-moon dark:fa-sun mr-1"></i>
                Toggle Dark Mode
            </button>
        </div>
    </div>
</div>

<script>
    // Check for saved dark mode preference or respect OS preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    function toggleDarkMode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }
</script>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(168, 85, 247, 0.3); }
        50% { box-shadow: 0 0 40px rgba(168, 85, 247, 0.6); }
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    
    .animate-glow {
        animation: glow 2s ease-in-out infinite;
    }
</style>
@endsection