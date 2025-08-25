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
            <a href="{{ route('api-docs.index') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-dark-800 transition-colors  {{ request()->routeIs('api-docs.index') ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 dark:text-primary-400' : '' }}">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="mt-6 mb-2">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-3">API Groups</h3>
            </div>
            
            @isset($allGroups)
                <div class="api-groups-tree space-y-1">
                    @foreach($allGroups as $groupName)
                        @php
                            $endpoints = $docs[$groupName]['endpoints'] ?? [];
                            $hasEndpoints = count($endpoints) > 0;
                            $isCurrentGroup = isset($group) && $group === $groupName;
                        @endphp
                        
                        <div class="group-item" x-data="{ 
                            isOpen: {{ $isCurrentGroup ? 'true' : 'false' }},
                            groupName: '{{ $groupName }}'
                        }">
                            <!-- Group Header -->
                            <button 
                                @click="isOpen = !isOpen" 
                                class="w-full flex items-center justify-between p-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-dark-800 transition-colors  {{ $isCurrentGroup ? 'bg-primary-50 dark:bg-dark-800 text-primary-600 dark:text-primary-400' : '' }}"
                                :class="{ 'bg-primary-50 dark:bg-dark-800 text-primary-600 dark:text-primary-400': isOpen }">
                                <div class="flex items-center space-x-3">
                                    <i class="fas w-5 transition-transform duration-200" 
                                       :class="isOpen ? 'fa-folder-open text-yellow-500' : 'fa-folder text-yellow-400'"></i>
                                    <span class="truncate text-left">{{ $groupName }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs bg-gray-100 dark:bg-dark-700 px-2 py-1 rounded-full">
                                        {{ count($endpoints) }}
                                    </span>
                                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                                       :class="{ 'transform rotate-180': isOpen }"></i>
                                </div>
                            </button>
                            
                            <!-- Endpoints List -->
                            <div x-show="isOpen" x-collapse class="ml-6 pl-2 border-l-2 border-gray-200 dark:border-dark-600 mt-1">
                                <div class="space-y-1 py-1">
                                    @foreach($endpoints as $endpoint)
                                        @php
                                            $isSecure = $endpoint['authenticated'] ?? false;
                                            $methodColor = [
                                                'GET' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                                                'POST' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
                                                'PUT' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'PATCH' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'DELETE' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
                                                'default' => 'text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-400'
                                            ];
                                            $methodClass = $methodColor[$endpoint['method']] ?? $methodColor['default'];
                                            
                                            // Generate safe endpoint URL - FIXED
                                            $endpointUrl = '#';
                                            try {
                                                // Try to generate the URL with the endpoint ID
                                                $endpointUrl = route('api-docs.endpoint', ['id' => $endpoint['id']]);
                                            } catch (Exception $e) {
                                                // Fallback to group view if endpoint route fails
                                                $endpointUrl = route('api-docs.group', $groupName) . '#endpoint-' . $endpoint['id'];
                                            }
                                        @endphp
                                        
                                        <a href="{{ $endpointUrl }}" 
                                           class="flex items-center space-x-2 p-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors {{ request()->routeIs('api-docs.endpoint') && request()->route('id') == $endpoint['id'] ? 'bg-gray-50 dark:bg-dark-700' : '' }}"
                                           @if(strpos($endpointUrl, '#') === 0) onclick="scrollToEndpoint('endpoint-{{ $endpoint['id'] }}')" @endif>
                                            <span class="w-12 px-2 py-1 text-xs font-medium rounded {{ $methodClass }}">
                                                {{ $endpoint['method'] }}
                                            </span>
                                            <span class="truncate flex-1">{{ $endpoint['name'] }}</span>
                                            @if($isSecure)
                                                <i class="fas fa-lock text-xs text-red-500" title="Authentication Required"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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

@push('styles')
<style>
    .api-groups-tree {
        user-select: none;
    }
    
    .group-item {
        transition: all 0.2s ease;
    }
    
    /* . {
        transition: all 0.2s ease;
    }
    
    .:hover {
        transform: translateX(2px);
    } */
    
    /* Smooth collapse animation */
    [x-cloak] { display: none !important; }
    
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
    
    .dark .scrollbar-thin::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.5);
    }
    
    .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: rgba(75, 85, 99, 0.7);
    }
</style>
@endpush

@push('scripts')
<script>
// Function to scroll to a specific endpoint
function scrollToEndpoint(endpointId) {
    const element = document.getElementById(endpointId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Add highlight effect
        element.classList.add('highlight');
        setTimeout(() => element.classList.remove('highlight'), 2000);
    }
}

document.addEventListener('alpine:init', () => {
    // Initialize Alpine.js components
    Alpine.data('sidebarState', () => ({
        init() {
            // Restore collapsed/expanded states from localStorage
            this.$el.querySelectorAll('.group-item').forEach(item => {
                const groupName = item.__x.$data.groupName;
                const isOpen = localStorage.getItem(`api-group-${groupName}`) === 'true';
                if (isOpen) {
                    item.__x.$data.isOpen = true;
                }
            });
            
            // Save state when changed
            this.$el.querySelectorAll('.group-item').forEach(item => {
                const groupName = item.__x.$data.groupName;
                item.__x.$watch('isOpen', (value) => {
                    localStorage.setItem(`api-group-${groupName}`, value);
                });
            });
        }
    }));
});

// Auto-expand current group
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($group))
        const currentGroup = '{{ $group }}';
        const groupElements = document.querySelectorAll('.group-item');
        groupElements.forEach(element => {
            if (element.__x && element.__x.$data.groupName === currentGroup) {
                element.__x.$data.isOpen = true;
            }
        });
    @endif
});
</script>
@endpush