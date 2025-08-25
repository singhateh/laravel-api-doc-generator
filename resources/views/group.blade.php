@extends('api-doc-generator::layout')

@section('title', $group . ' API Endpoints')

@section('content')
    <div class="max1-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center text-white">
                            <i class="fas fa-folder"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $group }} API</h1>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Explore all endpoints in the {{ $group }} API group</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full text-sm font-medium">
                        {{ count($endpoints) }} endpoints
                    </span>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <a href="{{ route('api-docs.index') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <span class="text-gray-400 dark:text-gray-600">/</span>
            <span class="text-primary-600 dark:text-primary-400">{{ $group }}</span>
        </nav>

        <!-- Search and Filter -->
        <div class="mb-6 bg-white dark:bg-dark-800 rounded-xl p-4 shadow-soft border border-gray-100 dark:border-dark-700">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        id="search-input"
                        placeholder="Search endpoints..." 
                        class="pl-10 w-full px-4 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                    >
                </div>
                <div class="flex space-x-2">
                    <select id="method-filter" class="px-4 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                        <option value="">All Methods</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Endpoints Grid -->
        <div class="grid grid-cols-1 gap-4" id="endpoints-container">
            @foreach($endpoints as $endpoint)
    @php
        $hoverClasses = match($endpoint['method']) {
            'GET' => 'hover:bg-green-50 hover:border-green-400 dark:hover:bg-green-900/20 dark:hover:border-green-500',
            'POST' => 'hover:bg-blue-50 hover:border-blue-400 dark:hover:bg-blue-900/20 dark:hover:border-blue-500',
            'PUT', 'PATCH' => 'hover:bg-yellow-50 hover:border-yellow-400 dark:hover:bg-yellow-900/20 dark:hover:border-yellow-500',
            'DELETE' => 'hover:bg-red-50 hover:border-red-400 dark:hover:bg-red-900/20 dark:hover:border-red-500',
            default => 'hover:bg-gray-50 hover:border-gray-400 dark:hover:bg-gray-900/20 dark:hover:border-gray-500'
        };
    @endphp

    <a href="{{ route('api-docs.endpoint', $endpoint['id']) }}" 
       class="group block bg-white dark:bg-dark-800 rounded-xl p-6 border border-gray-200 transition-colors duration-200 endpoint-card {{ $hoverClasses }}"
       data-method="{{ $endpoint['method'] }}"
       data-name="{{ strtolower($endpoint['name']) }}"
       data-description="{{ strtolower($endpoint['description'] ?? '') }}">
        
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-3 mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                        @if($endpoint['method'] === 'GET') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($endpoint['method'] === 'POST') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($endpoint['method'] === 'PUT' || $endpoint['method'] === 'PATCH') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                        @elseif($endpoint['method'] === 'DELETE') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400 @endif">
                        {{ $endpoint['method'] }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 font-mono bg-gray-50 dark:bg-dark-700 px-2 py-1 rounded">
                        {{ $endpoint['path'] }}
                    </span>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors mb-2">
                    {{ $endpoint['name'] }}
                </h3>
                
                @if($endpoint['description'])
                    <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                        {{ $endpoint['description'] }}
                    </p>
                @endif
                
                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                    @if(!empty($endpoint['parameters']))
                        <span class="flex items-center">
                            <i class="fas fa-list me-1 text-xs"></i>
                            {{ count($endpoint['parameters']) }} params
                        </span>
                    @endif
                    
                    @if(!empty($endpoint['headers']))
                        <span class="flex items-center">
                            <i class="fas fa-heading me-1 text-xs"></i>
                            {{ count($endpoint['headers']) }} headers
                        </span>
                    @endif
                    
                    <span class="flex items-center">
                        <i class="fas fa-clock me-1 text-xs"></i>
                        {{ $endpoint['created_at'] ?? 'Recent' }}
                    </span>
                </div>
            </div>
            
            <div class="ml-4 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-400">
                    <i class="fas fa-arrow-right text-sm"></i>
                </div>
            </div>
        </div>
    </a>
@endforeach

        </div>

        <!-- Empty State for Filtering -->
        <div id="no-results" class="hidden text-center py-12">
            <div class="w-20 h-20 bg-gray-100 dark:bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No endpoints found</h3>
            <p class="text-gray-600 dark:text-gray-400">Try adjusting your search or filter criteria</p>
        </div>
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
        
        .endpoint-card {
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
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
            const searchInput = document.getElementById('search-input');
            const methodFilter = document.getElementById('method-filter');
            const endpointCards = document.querySelectorAll('.endpoint-card');
            const noResults = document.getElementById('no-results');
            
            function filterEndpoints() {
                const searchTerm = searchInput.value.toLowerCase();
                const methodValue = methodFilter.value;
                let visibleCount = 0;
                
                endpointCards.forEach(card => {
                    const method = card.getAttribute('data-method');
                    const name = card.getAttribute('data-name');
                    const description = card.getAttribute('data-description');
                    
                    const matchesSearch = searchTerm === '' || 
                                        name.includes(searchTerm) || 
                                        description.includes(searchTerm);
                    
                    const matchesMethod = methodValue === '' || method === methodValue;
                    
                    if (matchesSearch && matchesMethod) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show/hide no results message
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }
            
            // Add event listeners
            searchInput.addEventListener('input', filterEndpoints);
            methodFilter.addEventListener('change', filterEndpoints);
            
            // Add animation to cards
            endpointCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
            });
            
            // Add keyboard shortcut for search
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        });
    </script>
    @endpush
@endsection