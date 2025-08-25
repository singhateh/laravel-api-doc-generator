<!DOCTYPE html>
<!-- resources/views/layout.blade.php -->
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Highlight.js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'pulse-soft': 'pulseSoft 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        pulseSoft: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.7' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dark .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
        }
        
        .sidebar-hover-effect {
            transition: all 0.1s ease;
        }
        
        .sidebar-hover-effect:hover {
            /* transform: translateX(2px); */
        }
        
        .code-block {
            font-family: 'Fira Code', 'Monaco', 'Cascadia Code', monospace;
        }
        
        .shadow-soft {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dark .shadow-soft {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.7);
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-dark-950 transition-colors duration-300">
    <!-- Theme Toggle -->
    <button id="theme-toggle" class="fixed top-4 right-4 z-50 w-10 h-10 rounded-full glass-effect shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:scale-110 transition-transform">
        <i class="fas fa-moon dark:hidden"></i>
        <i class="fas fa-sun hidden dark:block"></i>
    </button>

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="md:hidden fixed top-4 left-4 z-50 w-10 h-10 rounded-full glass-effect shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300">
        <i class="fas fa-bars"></i>
    </button>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="hidden md:flex flex-col w-80 bg-white dark:bg-dark-900 border-r border-gray-200 dark:border-dark-700 transition-all duration-300 transform -translate-x-full md:translate-x-0 fixed h-full z-40">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-100 dark:border-dark-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center text-white">
                        <i class="fas fa-code text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">API Docs</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ config('app.name') }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto scrollbar-thin p-4">
                <div class="space-y-1">
                    <a href="{{ route('api-docs.index') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-dark-800 transition-colors sidebar-hover-effect {{ request()->routeIs('api-docs.index') ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 dark:text-primary-400' : '' }}">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <div class="mt-6 mb-2">
                        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-3">API Groups</h3>
                    </div>
                    
                    @isset($allGroups)
                        @foreach($allGroups as $groupName)
                            <a href="{{ route('api-docs.group', $groupName) }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-dark-800 transition-colors sidebar-hover-effect {{ isset($group) && $group === $groupName ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 dark:text-primary-400' : '' }}">
                                <i class="fas fa-folder w-5"></i>
                                <span class="truncate">{{ $groupName }}</span>
                                <span class="ml-auto text-xs bg-gray-100 dark:bg-dark-700 px-2 py-1 rounded-full">
                                    {{ count($docs[$groupName]['endpoints'] ?? []) }}</span>
                            </a>
                        @endforeach
                    @endisset
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-dark-700">
                <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-dark-800">
                    <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white text-sm">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden md:ml-80 transition-all duration-300"> <!-- Changed from md:ml-0 to md:ml-80 -->
            <!-- Header -->
            <header class="bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700 shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            @yield('title', 'API Documentation')
                        </h1>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('api-docs.json') }}" target="_blank" class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-dark-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                            <i class="fas fa-code"></i>
                            <span>Raw JSON</span>
                        </a>
                        
                        <form id="generate-docs-form" action="{{ route('api-docs.generate') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        
                        <button onclick="event.preventDefault(); document.getElementById('generate-docs-form').submit();" class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                            <i class="fas fa-sync-alt"></i>
                            <span>Regenerate</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-dark-950">
                <div class="mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div id="mobile-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.5.0/axios.min.js"></script>
    
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or respect OS preference
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        html.classList.toggle('dark', savedTheme === 'dark');
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const mobileBackdrop = document.getElementById('mobile-backdrop');
        
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            mobileBackdrop.classList.toggle('hidden');
        });
        
        mobileBackdrop.addEventListener('click', () => {
            sidebar.classList.add('hidden');
            mobileBackdrop.classList.add('hidden');
        });

        // Initialize highlight.js
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof hljs !== 'undefined') {
                hljs.highlightAll();
            }
            
            // Add smooth animations to elements
            const animatedElements = document.querySelectorAll('.animate-on-load');
            animatedElements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
                el.classList.add('animate-fade-in');
            });
        });

        // Responsive sidebar handling
        function handleResize() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('hidden');
                mobileBackdrop.classList.add('hidden');
            } else {
                sidebar.classList.add('hidden');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call

        // Add nice hover effects to cards
        const cards = document.querySelectorAll('.card-hover');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.classList.add('shadow-lg', 'transform', 'scale-105');
            });
            
            card.addEventListener('mouseleave', () => {
                card.classList.remove('shadow-lg', 'transform', 'scale-105');
            });
        });

        // Toast notification system
        window.showToast = function(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-600 text-white' : 
                type === 'error' ? 'bg-red-600 text-white' : 
                'bg-blue-600 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-0');
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };
    </script>
    
    @stack('scripts')
</body>
</html>