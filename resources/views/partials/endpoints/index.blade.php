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