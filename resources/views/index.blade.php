@extends('api-doc-generator::layout')

@section('title', 'API Documentation Dashboard')

@section('content')
    <div class="max1-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">API Documentation</h1>
                    <p class="text-gray-600 dark:text-gray-400">Explore and test all available API endpoints for {{ config('app.name') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex items-center space-x-2 px-4 py-2 bg-primary-50 dark:bg-dark-800 rounded-lg">
                        <i class="fas fa-api text-primary-600 dark:text-primary-400"></i>
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ array_sum(array_map(fn($group) => count($group['endpoints']), $docs ?? [])) }} endpoints</span>
                    </div>
                </div>
            </div>
        </div>

        @if(empty($docs))
            <!-- Empty State -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 text-center animate-fade-in">
                <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Documentation Found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">We couldn't find any API documentation. Generate it to get started.</p>
                <form action="{{ route('api-docs.generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-bolt me-2"></i>
                        Generate Documentation
                    </button>
                </form>
            </div>
        @else
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-dark-800 rounded-xl p-6 shadow-soft border border-gray-100 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-layer-group text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">API Groups</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($docs) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-800 rounded-xl p-6 shadow-soft border border-gray-100 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-link text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Endpoints</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ array_sum(array_map(fn($group) => count($group['endpoints']), $docs)) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-dark-800 rounded-xl p-6 shadow-soft border border-gray-100 dark:border-dark-700">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-clock text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Last Updated</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ now()->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Groups Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($docs as $groupName => $groupData)
                    @php
                        // Determine if the group is private (has authentication)
                        $isPrivate = false;
                        $hasAuth = false;
                        
                        // Check if any endpoint in the group has authentication
                        foreach ($groupData['endpoints'] as $endpoint) {
                            if (isset($endpoint['authenticated']) && $endpoint['authenticated']) {
                                $hasAuth = true;
                                break;
                            }
                        }
                        
                        // Consider private if more than 50% of endpoints require auth
                        $authCount = count(array_filter($groupData['endpoints'], fn($ep) => $ep['authenticated'] ?? false));
                        $totalCount = count($groupData['endpoints']);
                        $isPrivate = $totalCount > 0 && ($authCount / $totalCount) > 0.5;
                    @endphp
                    
                    <div class="bg-white dark:bg-dark-800 rounded-xl shadow-soft border border-gray-100 dark:border-dark-700 overflow-hidden transition-all duration-300 hover:shadow-lg hover:transform hover:scale-105 card-hover">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center text-white relative">
                                        <i class="fas fa-folder"></i>
                                        <!-- Security Indicator -->
                                        <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-sm">
                                            @if($isPrivate)
                                                <i class="fas fa-lock text-red-500 text-xs" title="Private API (Authentication Required)"></i>
                                            @else
                                                <i class="fas fa-lock-open text-green-500 text-xs" title="Public API"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $groupName }}
                                    </h3>
                                </div>
                                <span class="px-2 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-sm rounded-full">
                                    {{ count($groupData['endpoints']) }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                                {{ $groupData['endpoints'][0]['description'] ?? 'No description available' }}
                            </p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center">
                                        <i class="fas fa-link me-1 text-xs"></i>
                                        {{ count($groupData['endpoints']) }} endpoints
                                    </span>
                                    <span class="flex items-center">
                                        @if($hasAuth)
                                            @if($isPrivate)
                                                <i class="fas fa-lock me-1 text-xs text-red-500"></i>
                                                <span class="text-red-500">Private</span>
                                            @else
                                                <i class="fas fa-lock-open me-1 text-xs text-green-500"></i>
                                                <span class="text-green-500">Mixed</span>
                                            @endif
                                        @else
                                            <i class="fas fa-lock-open me-1 text-xs text-green-500"></i>
                                            <span class="text-green-500">Public</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-6 py-4 bg-gray-50 dark:bg-dark-700 border-t border-gray-100 dark:border-dark-600">
                            <a href="{{ route('api-docs.group', $groupName) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                                <span>Explore Endpoints</span>
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Security Legend -->
            <div class="mt-6 bg-white dark:bg-dark-800 rounded-xl p-4 shadow-soft border border-gray-100 dark:border-dark-700">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Security Legend</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="fas fa-lock-open text-green-500 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Public API</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-5 h-5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                            <i class="fas fa-lock text-yellow-500 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Mixed (Some require auth)</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="fas fa-lock text-red-500 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Private (Auth required)</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white dark:bg-dark-800 rounded-xl p-6 shadow-soft border border-gray-100 dark:border-dark-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('api-docs.json') }}" target="_blank" class="flex items-center space-x-3 p-4 rounded-lg bg-gray-50 dark:bg-dark-700 hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-code text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Raw JSON</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">View complete API spec</p>
                        </div>
                    </a>
                    
                    <form action="{{ route('api-docs.generate') }}" method="POST" class="flex items-center space-x-3 p-4 rounded-lg bg-gray-50 dark:bg-dark-700 hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors cursor-pointer" onclick="this.submit()">
                        @csrf
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sync-alt text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Regenerate</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Update documentation</p>
                        </div>
                    </form>
                    
                    <div class="flex items-center space-x-3 p-4 rounded-lg bg-gray-50 dark:bg-dark-700">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-download text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Export</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Download API spec</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .dark .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .dark .card-hover:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to cards
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });
            
            // Add click animation to buttons
            const buttons = document.querySelectorAll('button, a');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.getAttribute('disabled')) return;
                    
                    this.classList.add('transform', 'scale-95');
                    setTimeout(() => {
                        this.classList.remove('transform', 'scale-95');
                    }, 150);
                });
            });
        });
    </script>
    @endpush
@endsection