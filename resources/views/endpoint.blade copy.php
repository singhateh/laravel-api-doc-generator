@extends('api-doc-generator::layout')

@section('title', $endpoint['name'] . ' - API Documentation')

@section('content')
    <div class="mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-10 h-10 rounded-lg gradient-bg flex items-center justify-center text-white">
                            <i class="fas fa-link"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $endpoint['name'] }}</h1>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">{{ $endpoint['description'] ?? 'No description available' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        @if($endpoint['method'] === 'GET') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                        @elseif($endpoint['method'] === 'POST') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                        @elseif($endpoint['method'] === 'PUT' || $endpoint['method'] === 'PATCH') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                        @elseif($endpoint['method'] === 'DELETE') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400 @endif">
                        {{ $endpoint['method'] }}
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
            <a href="{{ route('api-docs.group', $group) }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                {{ $group }}
            </a>
            <span class="text-gray-400 dark:text-gray-600">/</span>
            <span class="text-primary-600 dark:text-primary-400">{{ $endpoint['name'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Endpoint Details -->
            <div class="bg-white dark:bg-dark-800 rounded-xl p-6 shadow-soft border border-gray-100 dark:border-dark-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-link text-primary-600 dark:text-primary-400 me-2"></i>
                        Endpoint Details
                    </h2>
                    <button 
                        onclick="copyEndpointUrl('{{ $endpoint['method'] }} {{ url('') }}{{ $endpoint['path'] }}')"
                        class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors border border-gray-200 dark:border-dark-600 rounded-md"
                        title="Copy endpoint URL"
                    >
                        <i class="fas fa-copy me-1"></i> Copy URL
                    </button>
                </div>
                
                <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-4 mb-4">
                    <code class="text-lg font-mono text-gray-900 dark:text-white break-all">
                        <span class="font-bold 
                            @if($endpoint['method'] === 'GET') text-green-600 dark:text-green-400
                            @elseif($endpoint['method'] === 'POST') text-blue-600 dark:text-blue-400
                            @elseif($endpoint['method'] === 'PUT' || $endpoint['method'] === 'PATCH') text-yellow-600 dark:text-yellow-400
                            @elseif($endpoint['method'] === 'DELETE') text-red-600 dark:text-red-400
                            @else text-gray-600 dark:text-gray-400 @endif">
                            {{ $endpoint['method'] }}
                        </span>
                        <span class="mx-2 text-gray-400 dark:text-gray-600">/</span>
                        <span>{{ $endpoint['path'] }}</span>
                    </code>
                </div>

                <!-- Parameters -->
                @if(!empty($endpoint['parameters']))
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <i class="fas fa-list text-primary-600 dark:text-primary-400 me-2"></i>
                            Parameters
                        </h3>
                        <div class="grid gap-3">
                            @foreach($endpoint['parameters'] as $param)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-1">
                                            <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $param['name'] }}</span>
                                            <span class="px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 text-xs rounded">
                                                {{ $param['type'] }}
                                            </span>
                                            @if($param['required'])
                                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs rounded">
                                                    Required
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400 text-xs rounded">
                                                    Optional
                                                </span>
                                            @endif
                                        </div>
                                        @if($param['description'])
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $param['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Headers -->
                @if(!empty($endpoint['headers']))
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <i class="fas fa-heading text-primary-600 dark:text-primary-400 me-2"></i>
                            Headers
                        </h3>
                        <div class="grid gap-3">
                            @foreach($endpoint['headers'] as $header)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-1">
                                            <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $header['name'] }}</span>
                                            <span class="px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 text-xs rounded">
                                                {{ $header['type'] }}
                                            </span>
                                        </div>
                                        @if($header['description'] ?? null)
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $header['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Success Response -->
                @if(!empty($endpoint['success']))
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 me-2"></i>
                            Success Response
                        </h3>
                        <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 dark:text-white"><code class="language-json">{{ json_encode($endpoint['success'], JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif

                <!-- Error Response -->
                @if(!empty($endpoint['error']))
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 me-2"></i>
                            Error Response
                        </h3>
                        <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-4">
                            <pre class="text-sm text-gray-900 dark:text-white"><code class="language-json">{{ json_encode($endpoint['error'], JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Testing Interface -->
            <div class="bg-white dark:bg-dark-800 rounded-xl shadow-soft border border-gray-100 dark:border-dark-700 overflow-hidden" 
                 x-data="endpointTester()">
                <!-- Tabs Header -->
               @include('api-doc-generator::partials.endpoints.tab_header')

                <!-- Tabs Content -->
                <div class="p-6">
                    <!-- Request Tab -->
                    <div x-show="activeTab === 'request'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="space-y-6">
                            <!-- URL and Method -->
                            <div class="flex flex-col sm:flex-row gap-4">
                                <select x-model="requestData.method" class="flex-shrink-0 sm:w-32 px-4 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-90 dark:text-white">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="PATCH">PATCH</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                                <div class="flex-1 relative">
                                    <input 
                                        type="url" 
                                        x-model="requestData.baseUrl"
                                        placeholder="https://example.com/api/endpoint" 
                                        class="w-full px-4 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                        readonly
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500" x-text="getQueryString()"></span>
                                    </div>
                                </div>
                                <button 
                                    @click="sendRequest()"
                                    :disabled="isLoading"
                                    class="flex-shrink-0 px-6 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400 text-white rounded-lg transition-colors duration-200 flex items-center justify-center"
                                >
                                    <i class="fas fa-paper-plane me-2" :class="{ 'fa-spin': isLoading }" x-show="!isLoading"></i>
                                    <i class="fas fa-spinner fa-spin me-2" x-show="isLoading"></i>
                                    <span x-text="isLoading ? 'Sending...' : 'Send'"></span>
                                </button>
                            </div>

                            <!-- Request Tabs -->
                            <div class="border border-gray-200 dark:border-dark-600 rounded-lg overflow-hidden">
                                
                                @include('api-doc-generator::partials.requests.tab_headers')

                                <div class="p-4 bg-white dark:bg-dark-800">
                                    <!-- Params Tab -->
                                     @include('api-doc-generator::partials.requests.params')

                                    <!-- Headers Tab -->
                                     @include('api-doc-generator::partials.requests.headers')

                                    <!-- Body Tab -->
                                     @include('api-doc-generator::partials.requests.body')

                                    <!-- Auth Tab -->
                                    @include('api-doc-generator::partials.requests.auth')

                                    <!-- Scripts Tab -->
                                    @include('api-doc-generator::partials.requests.script')
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Tab -->
                     @include('api-doc-generator::partials.responses')

                  <!-- Variables Tab -->
                    {{-- <div x-show="activeTab === 'variables'" x-transition:enter="transition ease-out duration-300" 
                        x-transition:enter-start="opacity-0 transform translate-y-4" 
                        x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="space-y-4">
                            <!-- Header -->
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                    <i data-lucide="sliders" class="w-4 h-4"></i>
                                    Variables
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button @click="addVariable()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-primary-600 hover:bg-primary-700 text-white">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Add
                                    </button>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-sm">
                                <div class="font-medium text-blue-800 dark:text-blue-300 mb-1 flex items-center gap-1">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                    How to use variables
                                </div>
                                <p class="text-blue-700 dark:text-blue-300">
                                    Use <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded text-xs">&#123;&#123;variable_name&#125;&#125;</code> 
                                    syntax in URL, headers, parameters, or body.
                                </p>
                            </div>

                            <!-- Variables Table -->
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-dark-600">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-dark-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Value</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-20">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-dark-600 bg-white dark:bg-dark-800">
                                        <template x-for="(variable, index) in variables" :key="index">
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <input 
                                                        type="text" 
                                                        x-model="variable.name"
                                                        placeholder="name" 
                                                        class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-dark-600 rounded focus:ring-1 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                    >
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input 
                                                        type="text" 
                                                        x-model="variable.value"
                                                        placeholder="value" 
                                                        class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-dark-600 rounded focus:ring-1 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                    >
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="flex items-center gap-1">
                                                        <button 
                                                            @click="copyToClipboard('{{' + variable.name + '}}')"
                                                            class="p-1 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                                            title="Copy variable syntax"
                                                        >
                                                            <i data-lucide="copy" class="w-4 h-4"></i>
                                                        </button>
                                                        <button 
                                                            @click="variables.splice(index, 1)"
                                                            class="p-1 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                            title="Delete variable"
                                                        >
                                                            <i data-lucide="trash" class="w-4 h-4"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="variables.length === 0">
                                            <tr>
                                                <td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    No variables yet. Click "Add" to create one.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-2">
                                <button @click="saveVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-primary-600 hover:bg-primary-700 text-white">
                                    <i data-lucide="save" class="w-4 h-4"></i> Save
                                </button>
                                <button @click="exportVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-green-600 hover:bg-green-700 text-white">
                                    <i data-lucide="download" class="w-4 h-4"></i> Export
                                </button>
                                <button @click="importVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-blue-600 hover:bg-blue-700 text-white">
                                    <i data-lucide="upload" class="w-4 h-4"></i> Import
                                </button>
                                <button @click="clearVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-red-600 hover:bg-red-700 text-white">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Clear All
                                </button>
                            </div>

                            <!-- Predefined Variables -->
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2 text-sm">Quick Add</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button 
                                        @click="addPredefinedVariable('base_url', 'https://api.example.com')"
                                        class="text-left p-2 text-xs bg-gray-50 dark:bg-dark-700 rounded hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors"
                                        title="Add base URL variable"
                                    >
                                        <div class="font-medium text-gray-900 dark:text-white">base_url</div>
                                        <div class="text-gray-600 dark:text-gray-400 truncate">API base URL</div>
                                    </button>
                                    <button 
                                        @click="addPredefinedVariable('auth_token', 'your_token_here')"
                                        class="text-left p-2 text-xs bg-gray-50 dark:bg-dark-700 rounded hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors"
                                        title="Add auth token variable"
                                    >
                                        <div class="font-medium text-gray-900 dark:text-white">auth_token</div>
                                        <div class="text-gray-600 dark:text-gray-400 truncate">Auth token</div>
                                    </button>
                                    <button 
                                        @click="addPredefinedVariable('user_id', '123')"
                                        class="text-left p-2 text-xs bg-gray-50 dark:bg-dark-700 rounded hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors"
                                        title="Add user ID variable"
                                    >
                                        <div class="font-medium text-gray-900 dark:text-white">user_id</div>
                                        <div class="text-gray-600 dark:text-gray-400 truncate">User ID</div>
                                    </button>
                                    <button 
                                        @click="addPredefinedVariable('api_key', 'your_api_key')"
                                        class="text-left p-2 text-xs bg-gray-50 dark:bg-dark-700 rounded hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors"
                                        title="Add API key variable"
                                    >
                                        <div class="font-medium text-gray-900 dark:text-white">api_key</div>
                                        <div class="text-gray-600 dark:text-gray-400 truncate">API key</div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                     @include('api-doc-generator::partials.variables')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .dark .gradient-bg {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }
    
    .shadow-soft {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .dark .shadow-soft {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }
    
    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endpush

@push('scripts')


<script>
    // Global helper functions for scripts
    function getEnvironmentVariable(key) {
        return localStorage.getItem(`env_${key}`);
    }
    
    function setEnvironmentVariable(key, value) {
        localStorage.setItem(`env_${key}`, value);
        return value;
    }
    
    function clearEnvironment() {
        Object.keys(localStorage)
            .filter(key => key.startsWith('env_'))
            .forEach(key => localStorage.removeItem(key));
    }
    
</script>


<script>
    // Global copy function
    function copyEndpointUrl(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Endpoint URL copied to clipboard! 📋');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showToast('Failed to copy to clipboard ❌', 'error');
        });
    }

    // Global toast function
    function showToast(message, type = 'success') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-y-8 opacity-0 toast-notification ${
            type === 'success' ? 'bg-green-600 text-white' :
            type === 'error' ? 'bg-red-600 text-white' :
            'bg-gray-800 text-white'
        }`;
        
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-bell'
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-8', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-8', 'opacity-0');
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }


     // Global copy function
    function copyToClipboard(text) {
        if (!text || text.trim() === '') {
            showToast('Nothing to copy', 'error');
            return false;
        }
        
        // Create a temporary textarea element
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textarea);
            
            if (successful) {
                showToast('Copied to clipboard! 📋', 'success');
                return true;
            } else {
                showToast('Failed to copy to clipboard', 'error');
                return false;
            }
        } catch (err) {
            document.body.removeChild(textarea);
            console.error('Failed to copy: ', err);
            showToast('Failed to copy to clipboard', 'error');
            return false;
        }
    }

    // Make sure you have the showToast function defined
    function showToast(message, type = 'success') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-y-8 opacity-0 toast-notification ${
            type === 'success' ? 'bg-green-600 text-white' :
            type === 'error' ? 'bg-red-600 text-white' :
            'bg-gray-800 text-white'
        }`;
        
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-bell'
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-8', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-8', 'opacity-0');
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }


    document.addEventListener('alpine:init', () => {
        Alpine.data('endpointTester', () => ({
            activeTab: 'request',
            requestTab: 'params',
            responseTab: 'body',
            isLoading: false,
            bodyType: 'json',
            formDataFields: [],
            urlEncodedFields: [],
            responseData: null,
            pathParams: [],
            queryParams: [],
            showDefaultHeaders: false,
            globalToken: localStorage.getItem('api_docs_global_token') || '',
            globalTokenType: localStorage.getItem('api_docs_global_token_type') || 'bearer',
            
            // Scripts-related properties
            scriptType: 'pre-request',
            scriptOutput: '',
            scripts: {
                'pre-request': '',
                'post-response': ''
            },


            // Add these properties to your Alpine component
            showTypeHints: true,
            showAutocomplete: false,
            autocompleteSuggestions: [],
            autocompleteIndex: 0,
            autocompletePrefix: '',


            // Update the getAutocompleteSuggestions method to read from localStorage
            getAutocompleteSuggestions(prefix) {
                const suggestions = [];
                
                // AD object methods
                const adMethods = [
                    { name: 'ad.request', type: 'object', description: 'Request manipulation methods' },
                    { name: 'ad.request.headers', type: 'object', description: 'Request headers' },
                    { name: 'ad.request.headers.add()', type: 'method', description: 'Add a header to the request' },
                    { name: 'ad.environment', type: 'object', description: 'Environment variables' },
                    { name: 'ad.environment.set()', type: 'method', description: 'Set an environment variable' },
                    { name: 'ad.environment.get()', type: 'method', description: 'Get an environment variable' },
                    { name: 'ad.response', type: 'object', description: 'Response object' },
                    { name: 'ad.response.json()', type: 'method', description: 'Parse response as JSON' },
                    { name: 'ad.test()', type: 'method', description: 'Create a test' },
                    { name: 'ad.expect()', type: 'method', description: 'Create an assertion' }
                ];
                
                // Get variables from localStorage
                let savedVariables = [];
                try {
                    const variablesData = localStorage.getItem('api_docs_variables');
                    if (variablesData) {
                        savedVariables = JSON.parse(variablesData);
                    }
                } catch (error) {
                    console.error('Error reading variables from localStorage:', error);
                }
                
                // Add user variables to suggestions
                savedVariables.forEach(variable => {
                    if (variable && variable.name) {
                        const valuePreview = variable.value ? ` (value: ${variable.value})` : ' (not set)';
                        suggestions.push({
                            name: `ad.environment.get('${variable.name}')`,
                            type: 'variable',
                            description: `Get variable: ${variable.name}${valuePreview}`
                        });
                        suggestions.push({
                            name: `ad.environment.set('${variable.name}', 'value')`,
                            type: 'variable',
                            description: `Set variable: ${variable.name}`
                        });
                        
                        // Also add direct variable access suggestions
                        suggestions.push({
                            name: `'${variable.name}'`,
                            type: 'variable',
                            description: `Variable name: ${variable.name}${valuePreview}`
                        });
                    }
                });
                
                // Filter by prefix if provided
                if (prefix) {
                    const filtered = [...adMethods, ...suggestions].filter(item => 
                        item.name.toLowerCase().includes(prefix.toLowerCase())
                    );
                    
                    // Sort so that exact matches come first
                    return filtered.sort((a, b) => {
                        const aStartsWith = a.name.toLowerCase().startsWith(prefix.toLowerCase());
                        const bStartsWith = b.name.toLowerCase().startsWith(prefix.toLowerCase());
                        
                        if (aStartsWith && !bStartsWith) return -1;
                        if (!aStartsWith && bStartsWith) return 1;
                        return 0;
                    });
                }
                
                return [...adMethods, ...suggestions];
            },


            // Update the checkForAutocomplete method to better detect variable patterns
            checkForAutocomplete(event) {
                const textarea = event.target;
                const text = textarea.value;
                const cursorPos = textarea.selectionStart;
                
                // Get the current line and text before cursor
                const textBeforeCursor = text.substring(0, cursorPos);
                const lines = textBeforeCursor.split('\n');
                const currentLine = lines[lines.length - 1];
                
                // Check for various patterns that should trigger autocomplete
                const patterns = [
                    /ad\.(\w*)$/, // ad.something
                    /ad\.environment\.(\w*)$/, // ad.environment.something
                    /ad\.environment\.get\(['"]([^'"]*)$/, // ad.environment.get('partial
                    /ad\.environment\.set\(['"]([^'"]*)$/, // ad.environment.set('partial
                    /['"]([^'"]*)$/ // Inside quotes (for variable names)
                ];
                
                let match = null;
                let patternType = '';
                
                for (const pattern of patterns) {
                    match = currentLine.match(pattern);
                    if (match) {
                        patternType = pattern.toString();
                        break;
                    }
                }
                
                if (match) {
                    const prefix = match[1] || '';
                    this.autocompletePrefix = prefix;
                    this.autocompleteSuggestions = this.getAutocompleteSuggestions(prefix);
                    this.showAutocomplete = this.autocompleteSuggestions.length > 0;
                    this.autocompleteIndex = 0;
                    
                    // Position the autocomplete dropdown near the cursor
                    this.positionAutocompleteDropdown(textarea, cursorPos);
                } else {
                    this.showAutocomplete = false;
                    this.autocompleteSuggestions = [];
                }
            },

            // Helper method to position the autocomplete dropdown
            positionAutocompleteDropdown(textarea, cursorPos) {
                // This would require additional CSS and DOM manipulation
                // For now, we'll just show it below the textarea
                // In a real implementation, you'd calculate the cursor position
            },


            // Enhanced insert method to handle different patterns
            insertAutocompleteSuggestion(suggestion) {
                const textarea = this.$refs.scriptTextarea;
                const text = this.scripts[this.scriptType];
                const cursorPos = textarea.selectionStart;
                
                // Find the position where we need to replace
                const textBeforeCursor = text.substring(0, cursorPos);
                const lines = textBeforeCursor.split('\n');
                const currentLine = lines[lines.length - 1];
                
                // Check which pattern we're matching
                const patterns = [
                    /(ad\.)(\w*)$/,
                    /(ad\.environment\.)(\w*)$/,
                    /(ad\.environment\.get\(['"])([^'"]*)$/,
                    /(ad\.environment\.set\(['"])([^'"]*)$/,
                    /(['"])([^'"]*)$/
                ];
                
                let match = null;
                let basePattern = '';
                let partialText = '';
                
                for (const pattern of patterns) {
                    match = currentLine.match(pattern);
                    if (match) {
                        basePattern = match[1];
                        partialText = match[2] || '';
                        break;
                    }
                }
                
                if (match) {
                    const startPos = cursorPos - partialText.length;
                    const before = text.substring(0, startPos);
                    const after = text.substring(cursorPos);
                    
                    let insertText = '';
                    
                    // Handle different types of suggestions
                    if (suggestion.type === 'variable' && suggestion.name.startsWith("'")) {
                        // Variable name inside quotes
                        insertText = suggestion.name.replace(/^'/, '').replace(/'$/, '');
                    } else if (suggestion.name.includes('ad.environment.get')) {
                        // Complete the get method
                        insertText = suggestion.name.replace(/^ad\.environment\.get\(['"]/, '');
                        if (!insertText.endsWith(')')) {
                            insertText = insertText.replace(/\)$/, '');
                        }
                    } else if (suggestion.name.includes('ad.environment.set')) {
                        // Complete the set method
                        insertText = suggestion.name.replace(/^ad\.environment\.set\(['"]/, '');
                    } else {
                        // Regular method completion
                        insertText = suggestion.name.replace(/^ad\./, '');
                    }
                    
                    this.scripts[this.scriptType] = before + insertText + after;
                    
                    // Set cursor position after inserted text
                    setTimeout(() => {
                        textarea.focus();
                        const newPos = startPos + insertText.length;
                        
                        // If we're inside a function call, position cursor appropriately
                        if (suggestion.name.includes('ad.environment.get')) {
                            textarea.selectionStart = newPos + 2; // Position after the closing quote
                            textarea.selectionEnd = newPos + 2;
                        } else if (suggestion.name.includes('ad.environment.set')) {
                            textarea.selectionStart = newPos + 2; // Position after the closing quote
                            textarea.selectionEnd = newPos + 2;
                        } else {
                            textarea.selectionStart = newPos;
                            textarea.selectionEnd = newPos;
                        }
                    }, 10);
                }
                
                this.showAutocomplete = false;
            },

            // Add this method to refresh variables when they change
            refreshVariables() {
                // This method can be called whenever variables are updated
                if (this.showAutocomplete) {
                    this.autocompleteSuggestions = this.getAutocompleteSuggestions(this.autocompletePrefix);
                }
            },

            handleScriptKeydown(event) {
                // Tab key inserts 4 spaces instead of moving focus
                if (event.key === 'Tab') {
                    event.preventDefault();
                    this.insertSnippet('    ');
                }
                
                // Arrow keys for autocomplete navigation
                if (this.showAutocomplete && this.autocompleteSuggestions.length > 0) {
                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        this.autocompleteIndex = Math.min(this.autocompleteIndex + 1, this.autocompleteSuggestions.length - 1);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        this.autocompleteIndex = Math.max(this.autocompleteIndex - 1, 0);
                    } else if (event.key === 'Enter' || event.key === 'Tab') {
                        event.preventDefault();
                        this.insertAutocompleteSuggestion(this.autocompleteSuggestions[this.autocompleteIndex]);
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        this.showAutocomplete = false;
                    }
                }
            },

            insertSnippet(snippet) {
                const textarea = this.$refs.scriptTextarea;
                if (!textarea) return;
                
                const startPos = textarea.selectionStart;
                const endPos = textarea.selectionEnd;
                const currentText = this.scripts[this.scriptType];
                
                this.scripts[this.scriptType] = 
                    currentText.substring(0, startPos) + 
                    snippet + 
                    currentText.substring(endPos);
                
                // Set cursor position after inserted text
                setTimeout(() => {
                    textarea.focus();
                    textarea.selectionStart = startPos + snippet.length;
                    textarea.selectionEnd = startPos + snippet.length;
                }, 10);
            },


            // Variables section
            variables: JSON.parse(localStorage.getItem('api_docs_variables') || '[]'),
            
            requestData: {
                baseUrl: '{{ url('') }}',
                method: '{{ $endpoint['method'] }}',
                headers: [
                    { name: 'Content-Type', value: 'application/json' },
                    { name: 'Accept', value: 'application/json' }
                ],
                body: '',
                auth: { type: '', token: '', username: '', password: '', key: '', value: '' }
            },
            
            defaultHeaders: ['host', 'user-agent', 'accept', 'accept-encoding', 'connection', 'content-length'],
           
            init() {
                // Parse the endpoint path to extract parameters
                const path = '{{ $endpoint['path'] }}';
                const baseUrl = '{{ url('') }}';
                this.requestData.baseUrl = baseUrl + path;
                
                // Extract path parameters like :id or {id}
                const pathParamRegex = /[:{]([\w-]+)[}]?/g;
                let match;
                while ((match = pathParamRegex.exec(path)) !== null) {
                    this.pathParams.push({
                        name: match[1],
                        value: '',
                        required: true
                    });
                }
                
                // Initialize parameters from endpoint definition
                @if(!empty($endpoint['parameters']))
                    @foreach($endpoint['parameters'] as $param)
                        @if(Str::contains($endpoint['path'], [':'.$param['name'], '{'.$param['name'].'}']))
                            // This is a path parameter
                            const pathParam = this.pathParams.find(p => p.name === '{{ $param['name'] }}');
                            if (pathParam) {
                                pathParam.required = {{ $param['required'] ? 'true' : 'false' }};
                            }
                        @else
                            // This is a query parameter
                            this.queryParams.push({
                                name: '{{ $param['name'] }}',
                                value: '',
                                required: {{ $param['required'] ? 'true' : 'false' }}
                            });
                        @endif
                    @endforeach
                @endif
                
                // Set appropriate content type header based on method
                if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                    const contentTypeHeader = this.requestData.headers.find(h => h.name.toLowerCase() === 'content-type');
                    if (contentTypeHeader) {
                        if (contentTypeHeader.value.includes('json')) this.bodyType = 'json';
                        else if (contentTypeHeader.value.includes('x-www-form-urlencoded')) this.bodyType = 'x-www-form-urlencoded';
                        else if (contentTypeHeader.value.includes('form-data')) this.bodyType = 'form-data';
                        else if (contentTypeHeader.value.includes('text/plain')) this.bodyType = 'text';
                    }
                }
                
                // Initialize scripts
                this.initScripts();
            },
            
            // Check if header is a default header
            isDefaultHeader(headerName) {
                return this.defaultHeaders.includes(headerName.toLowerCase());
            },
            
            // Add a new header
            addHeader() {
                this.requestData.headers.push({ name: '', value: '' });
            },
            
            // Save global token
            saveGlobalToken() {
                localStorage.setItem('api_docs_global_token', this.globalToken);
                localStorage.setItem('api_docs_global_token_type', this.globalTokenType);
                this.scriptOutput = 'Global token saved successfully.';
            },
            
            // Scripts-related methods
            initScripts() {
                // Load saved scripts from localStorage
                const savedScripts = JSON.parse(localStorage.getItem('api_docs_scripts') || '{}');
                
                // Set scripts with defaults if not saved
                this.scripts['pre-request'] = savedScripts['pre-request'] || '// Pre-request script\n// This runs before the request is sent\n\n// Example: Set timestamp header\nconst timestamp = new Date().toISOString();\nad.request.headers.add({\n    key: \'X-Timestamp\',\n    value: timestamp\n});\n\nconsole.log(`Pre-request executed at ${timestamp}`);';
                
                this.scripts['post-response'] = savedScripts['post-response'] || '// Post-response script\n// This runs after the response is received\n\n// Example: Basic response validation\nad.test("Status code is 200", function () {\n    ad.response.to.have.status(200);\n});\n\n// Parse and log response\nconst response = ad.response.json();\nconsole.log("Response:", response);';
            },
            
            saveScript(type) {
                // Save script to localStorage
                const scriptsToSave = {...this.scripts};
                localStorage.setItem('api_docs_scripts', JSON.stringify(scriptsToSave));
                this.scriptOutput = `${type === 'pre-request' ? 'Pre-request' : 'Post-response'} script saved successfully.`;
            },
            
            resetScript(type) {
                // Reset to default script
                if (type === 'pre-request') {
                    this.scripts['pre-request'] = '// Pre-request script\n// This runs before the request is sent\n\n// Example: Set timestamp header\nconst timestamp = new Date().toISOString();\nad.request.headers.add({\n    key: \'X-Timestamp\',\n    value: timestamp\n});\n\nconsole.log(`Pre-request executed at ${timestamp}`);';
                } else {
                    this.scripts['post-response'] = '// Post-response script\n// This runs after the response is received\n\n// Example: Basic response validation\nad.test("Status code is 200", function () {\n    ad.response.to.have.status(200);\n});\n\n// Parse and log response\nconst response = ad.response.json();\nconsole.log("Response:", response);';
                }
                
                this.scriptOutput = `${type === 'pre-request' ? 'Pre-request' : 'Post-response'} script reset to default.`;
            },
            
           
            async runScript(type) {
                try {
                    const script = this.scripts[type];
                    
                    // Create a function from the script
                    const executeScript = new Function('ad', 'console', 'return (async () => {' + script + '})()');
                    
                    // Mock ad object similar to Postman but using your actual variable storage
                    const mockAD = {
                        request: {
                            headers: {
                                add: (header) => {
                                    // Add header to request
                                    this.requestData.headers.push(header);
                                }
                            }
                        },
                        environment: {
                            set: (key, value) => {
                                // Save to your actual variable storage format
                                try {
                                    const currentVariables = JSON.parse(localStorage.getItem('api_docs_variables') || '[]');
                                    const existingIndex = currentVariables.findIndex(v => v.name === key);
                                    
                                    if (existingIndex >= 0) {
                                        currentVariables[existingIndex].value = value;
                                    } else {
                                        currentVariables.push({ name: key, value: value });
                                    }
                                    
                                    localStorage.setItem('api_docs_variables', JSON.stringify(currentVariables));
                                    
                                    // Also update the component's variables array if it exists
                                    if (this.variables) {
                                        const compIndex = this.variables.findIndex(v => v.name === key);
                                        if (compIndex >= 0) {
                                            this.variables[compIndex].value = value;
                                        } else {
                                            this.variables.push({ name: key, value: value });
                                        }
                                    }
                                } catch (error) {
                                    console.error('Error saving variable:', error);
                                }
                            },
                            get: (key) => {
                                // Get from your actual variable storage format
                                try {
                                    const currentVariables = JSON.parse(localStorage.getItem('api_docs_variables') || '[]');
                                    const variable = currentVariables.find(v => v.name === key);
                                    return variable ? variable.value : null;
                                } catch (error) {
                                    console.error('Error reading variable:', error);
                                    return null;
                                }
                            }
                        },
                        test: (name, assertion) => {
                            // Simple test implementation
                            try {
                                const result = assertion();
                                this.scriptOutput += `✓ ${name}\n`;
                                return result;
                            } catch (error) {
                                this.scriptOutput += `✗ ${name} - ${error.message}\n`;
                                return false;
                            }
                        },
                        expect: (value) => ({
                            to: {
                                have: {
                                    status: (expectedStatus) => () => {
                                        return this.responseData?.status === expectedStatus;
                                    }
                                }
                            }
                        }),
                        // Add response object for post-response scripts
                        response: this.responseData ? {
                            status: this.responseData.status,
                            json: () => {
                                try {
                                    return JSON.parse(this.responseData.body);
                                } catch {
                                    return {};
                                }
                            },
                            text: () => this.responseData.body
                        } : null
                    };
                    
                    // Mock console for script output
                    const mockConsole = {
                        log: (...args) => {
                            this.scriptOutput += args.join(' ') + '\n';
                        },
                        error: (...args) => {
                            this.scriptOutput += 'ERROR: ' + args.join(' ') + '\n';
                        },
                        warn: (...args) => {
                            this.scriptOutput += 'WARN: ' + args.join(' ') + '\n';
                        },
                        info: (...args) => {
                            this.scriptOutput += 'INFO: ' + args.join(' ') + '\n';
                        }
                    };
                    
                    // Execute the script
                    this.scriptOutput = `Running ${type} script...\n\n`;
                    const result = await executeScript(mockAD, mockConsole);
                    
                    if (result !== undefined) {
                        this.scriptOutput += `\nScript returned: ${JSON.stringify(result, null, 2)}`;
                    }
                    
                } catch (error) {
                    this.scriptOutput = `Error executing script: ${error.message}\n\nStack trace:\n${error.stack}`;
                    console.error('Script execution error:', error);
                }
            },
            
            // Variables section methods
            addVariable() {
                this.variables.push({ name: '', value: '' });
                this.refreshVariables(); // Refresh autocomplete
            },
            
            saveVariables() {
                localStorage.setItem('api_docs_variables', JSON.stringify(this.variables));
                showToast('Variables saved successfully!');
                this.refreshVariables(); // Refresh autocomplete
            },
            
            exportVariables() {
                const dataStr = JSON.stringify(this.variables, null, 2);
                const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                
                const exportFileDefaultName = 'api-docs-variables.json';
                
                const linkElement = document.createElement('a');
                linkElement.setAttribute('href', dataUri);
                linkElement.setAttribute('download', exportFileDefaultName);
                linkElement.click();
            },
            
            importVariables() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.json';
                
                input.onchange = e => {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = event => {
                        try {
                            const importedVariables = JSON.parse(event.target.result);
                            if (Array.isArray(importedVariables)) {
                                this.variables = importedVariables;
                                this.saveVariables();
                                showToast('Variables imported successfully!');
                            } else {
                                showToast('Invalid file format', 'error');
                            }
                        } catch (error) {
                            showToast('Error parsing JSON file', 'error');
                        }
                    };
                    
                    reader.readAsText(file);
                };
                
                input.click();
            },

           addPredefinedVariable(name, value) {
                if (!this.variables.find(v => v.name === name)) {
                    this.variables.push({ name, value });
                    this.saveVariables();
                    showToast(`Added variable: ${name}`);
                    this.refreshVariables(); // Refresh autocomplete
                } else {
                    showToast(`Variable ${name} already exists`, 'info');
                }
            },

            clearVariables() {
               
                customConfirmation('Are you sure you want to clear all variables?', {
                    onConfirm: () => {
                       this.variables = [];
                    this.saveVariables();
                    showToast('Variables cleared');
                    this.refreshVariables(); // Refresh autocomplete
                    },
                    onCancel: () => {
                       showToast('Action cancelled', 'error');
                    }
                    });
            },

        

            // Enhanced variable replacement with error handling
            replaceVariables(text) {
                if (!text || typeof text !== 'string') return text;
                
                try {
                    return text.replace(/\{\{(\w+)\}\}/g, (match, variableName) => {
                        const variable = this.variables.find(v => v.name === variableName);
                        if (variable) {
                            return variable.value;
                        } else {
                            console.warn(`Variable ${variableName} not found`);
                            return match; // Return the original pattern if variable not found
                        }
                    });
                } catch (error) {
                    console.error('Error replacing variables:', error);
                    return text;
                }
            },
            
            getQueryString() {
                const params = this.queryParams.filter(p => p.name && p.value);
                if (params.length === 0) return '';
                
                const queryString = params.map(p => `${encodeURIComponent(p.name)}=${encodeURIComponent(p.value)}`).join('&');
                return `?${queryString}`;
            },

            updateUrl() {
                // Build the URL with path parameters
                let url = '{{ url('') }}' + '{{ $endpoint['path'] }}';
                
                // Replace path parameters for both :param and {param} formats
                this.pathParams.forEach(param => {
                    if (param.value) {
                        const encodedValue = encodeURIComponent(param.value);
                        // Replace :param format
                        url = url.replace(`:${param.name}`, encodedValue);
                        // Replace {param} format
                        url = url.replace(`{${param.name}}`, encodedValue);
                    } else {
                        // If no value provided, keep the placeholder but show it's missing
                        url = url.replace(`:${param.name}`, `:${param.name}`);
                        url = url.replace(`{${param.name}}`, `{${param.name}}`);
                    }
                });
                
                this.requestData.baseUrl = url;
            },

            handleFileUpload(event, index) {
                const file = event.target.files[0];
                if (file) {
                    this.formDataFields[index].value = file;
                }
            },

            async sendRequest() {
                // Run pre-request script if it exists
                if (this.scripts['pre-request'] && this.scripts['pre-request'].trim() !== '') {
                    await this.runScript('pre-request');
                }
                
                this.isLoading = true;
                this.activeTab = 'response';
                
                try {
                    // Prepare headers with variable replacement
                    const headers = {};
                    this.requestData.headers.forEach(header => {
                        if (header.name && header.value) {
                            const headerName = this.replaceVariables(header.name);
                            const headerValue = this.replaceVariables(header.value);
                            headers[headerName] = headerValue;
                        }
                    });
                    
                    // Set appropriate Content-Type header based on body type
                    if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                        switch(this.bodyType) {
                            case 'json':
                                headers['Content-Type'] = 'application/json';
                                break;
                            case 'text':
                                headers['Content-Type'] = 'text/plain';
                                break;
                            case 'form-data':
                                // Let the browser set the content type with boundary
                                delete headers['Content-Type'];
                                break;
                            case 'x-www-form-urlencoded':
                                headers['Content-Type'] = 'application/x-www-form-urlencoded';
                                break;
                        }
                    }
                    

                    // Add auth headers if needed with variable replacement
                    // if (this.requestData.auth.type === 'bearer' && this.requestData.auth.token) {
                    //     headers['Authorization'] = `Bearer ${this.replaceVariables(this.requestData.auth.token)}`;
                    // } else if (this.requestData.auth.type === 'basic' && this.requestData.auth.username && this.requestData.auth.password) {
                    //     const username = this.replaceVariables(this.requestData.auth.username);
                    //     const password = this.replaceVariables(this.requestData.auth.password);
                    //     headers['Authorization'] = `Basic ${btoa(`${username}:${password}`)}`;
                    // } else if (this.requestData.auth.type === 'api_key' && this.requestData.auth.key && this.requestData.auth.value) {
                    //     const key = this.replaceVariables(this.requestData.auth.key);
                    //     const value = this.replaceVariables(this.requestData.auth.value);
                    //     headers[key] = value;
                    // } else if (this.requestData.auth.type === 'global' && this.globalToken) {
                    //     // Use global token with selected type
                    //     const token = this.replaceVariables(this.globalToken);
                    //     if (this.globalTokenType === 'bearer') {
                    //         headers['Authorization'] = `Bearer ${token}`;
                    //     } else if (this.globalTokenType === 'basic') {
                    //         headers['Authorization'] = `Basic ${token}`;
                    //     } else if (this.globalTokenType === 'api_key') {
                    //         headers['X-API-Key'] = token;
                    //     } else if (this.globalTokenType === 'custom') {
                    //         // For custom token type, assume it's already formatted correctly
                    //         headers['Authorization'] = token;
                    //     }
                    // }


                    // Add auth headers if needed with variable replacement
                    const hasLocalToken = this.requestData.auth && (
                        (this.requestData.auth.type === 'bearer' && this.requestData.auth.token) ||
                        (this.requestData.auth.type === 'basic' && this.requestData.auth.username && this.requestData.auth.password) ||
                        (this.requestData.auth.type === 'api_key' && this.requestData.auth.key && this.requestData.auth.value)
                    );

                    if (hasLocalToken) {
                        // Use local auth first
                        if (this.requestData.auth.type === 'bearer') {
                            headers['Authorization'] = `Bearer ${this.replaceVariables(this.requestData.auth.token)}`;
                        } else if (this.requestData.auth.type === 'basic') {
                            const username = this.replaceVariables(this.requestData.auth.username);
                            const password = this.replaceVariables(this.requestData.auth.password);
                            headers['Authorization'] = `Basic ${btoa(`${username}:${password}`)}`;
                        } else if (this.requestData.auth.type === 'api_key') {
                            const key = this.replaceVariables(this.requestData.auth.key);
                            const value = this.replaceVariables(this.requestData.auth.value);
                            headers[key] = value;
                        }
                    } else if (this.globalToken) {
                        // No local token, use global token
                        const token = this.replaceVariables(this.globalToken);
                        if (this.globalTokenType === 'bearer') {
                            headers['Authorization'] = `Bearer ${token}`;
                        } else if (this.globalTokenType === 'basic') {
                            headers['Authorization'] = `Basic ${token}`;
                        } else if (this.globalTokenType === 'api_key') {
                            headers['X-API-Key'] = token;
                        } else if (this.globalTokenType === 'custom') {
                            headers['Authorization'] = token; // assume already formatted
                        }
                    }

                    
                    // Prepare URL with variable replacement
                    let url = this.replaceVariables(this.requestData.baseUrl);
                    const urlParams = new URLSearchParams();
                    
                    // Process query parameters with variable replacement
                    this.queryParams.forEach(param => {
                        if (param.name && param.value) {
                            const paramName = this.replaceVariables(param.name);
                            const paramValue = this.replaceVariables(param.value);
                            urlParams.append(paramName, paramValue);
                        }
                    });
                    
                    const queryString = urlParams.toString();
                    if (queryString) {
                        url += (url.includes('?') ? '&' : '?') + queryString;
                    }
                    
                    // Prepare request options
                    const options = {
                        method: this.requestData.method,
                        headers: headers
                    };
                    
                    // Prepare body based on body type with variable replacement
                    if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                        switch(this.bodyType) {
                            case 'json':
                                options.body = this.replaceVariables(this.requestData.body);
                                break;
                            case 'text':
                                options.body = this.replaceVariables(this.requestData.body);
                                break;
                            case 'form-data':
                                const formData = new FormData();
                                this.formDataFields.forEach(field => {
                                    if (field.key) {
                                        const fieldKey = this.replaceVariables(field.key);
                                        let fieldValue = field.value;
                                        
                                        // Handle file uploads differently
                                        if (field.type === 'file' && field.value instanceof File) {
                                            fieldValue = field.value;
                                        } else {
                                            fieldValue = this.replaceVariables(field.value);
                                        }
                                        
                                        formData.append(fieldKey, fieldValue);
                                    }
                                });
                                options.body = formData;
                                break;
                            case 'x-www-form-urlencoded':
                                const urlEncodedData = new URLSearchParams();
                                this.urlEncodedFields.forEach(field => {
                                    if (field.key && field.value) {
                                        const fieldKey = this.replaceVariables(field.key);
                                        const fieldValue = this.replaceVariables(field.value);
                                        urlEncodedData.append(fieldKey, fieldValue);
                                    }
                                });
                                options.body = urlEncodedData.toString();
                                break;
                        }
                    }
                    
                    // Send the request
                    const startTime = Date.now();
                    const response = await fetch(url, options);
                    const endTime = Date.now();
                    
                    // Process response
                    const responseText = await response.text();
                    
                    this.responseData = {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries([...response.headers]),
                        body: responseText,
                        time: endTime - startTime,
                        size: new TextEncoder().encode(responseText).length
                    };
                    
                    // Run post-response script if it exists
                    if (this.scripts['post-response'] && this.scripts['post-response'].trim() !== '') {
                        await this.runScript('post-response');
                    }
                    
                    // Re-run highlight.js for syntax highlighting
                    if (typeof hljs !== 'undefined') {
                        setTimeout(() => hljs.highlightAll(), 100);
                    }
                } catch (error) {
                    console.error('Request failed:', error);
                    this.responseData = {
                        status: 0,
                        statusText: 'Error',
                        headers: {},
                        body: error.message,
                        time: 0,
                        size: 0
                    };
                } finally {
                    this.isLoading = false;
                }
            },
            
            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + sizes[i];
            },
            
            getStatusText(status) {
                const statusTexts = {
                    200: 'OK',
                    201: 'Created',
                    204: 'No Content',
                    400: 'Bad Request',
                    401: 'Unauthorized',
                    403: 'Forbidden',
                    404: 'Not Found',
                    500: 'Internal Server Error'
                };
                return statusTexts[status] || '';
            },
            
            formatResponseBody(body) {
                if (typeof body === 'object') {
                    return JSON.stringify(body, null, 2);
                }
                try {
                    return JSON.stringify(JSON.parse(body), null, 2);
                } catch {
                    return body;
                }
            }
        }));
    });
</script>


<!-- Alpine.js -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<!-- Highlight.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof hljs !== 'undefined') {
            hljs.highlightAll();
        }
    });
</script>
@endpush