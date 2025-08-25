<!-- Variables Tab -->
<div x-show="activeTab === 'variables'" x-transition:enter="transition ease-out duration-300" 
     x-transition:enter-start="opacity-0 transform translate-y-4" 
     x-transition:enter-end="opacity-100 transform translate-y-0">
    <div class="space-y-4">
        <!-- Header with simplified actions -->
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Environment Variables</h3>
            <div class="flex items-center gap-2">
                <button @click="addVariable()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-primary-600 hover:bg-primary-700 text-white">
                    <i class="fas fa-plus mr-1"></i> New Variable
                </button>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-sm">
            <div class="font-medium text-blue-800 dark:text-blue-300 mb-1 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>Using Variables
            </div>
            <p class="text-blue-700 dark:text-blue-300">
                Reference variables with <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded text-xs">&#123;&#123;variable_name&#125;&#125;</code> 
                syntax in URLs, headers, parameters, or body.
            </p>
        </div>

        <!-- Variables Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-dark-600">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Variable Name</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Value</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-16">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-dark-600 bg-white dark:bg-dark-800">
                    <template x-for="(variable, index) in variables" :key="index">
                        <tr>
                            <td class="px-3 py-2">
                                <input 
                                    type="text" 
                                    x-model="variable.name"
                                    placeholder="variable_name" 
                                    class="w-full px-2 py-1 text-sm border border-gray-200 dark:border-dark-600 rounded focus:ring-1 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                >
                            </td>
                            <td class="px-3 py-2">
                                <input 
                                    type="text" 
                                    x-model="variable.value"
                                    placeholder="variable value" 
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
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                    <button 
                                        @click="variables.splice(index, 1)"
                                        class="p-1 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                        title="Delete variable"
                                    >
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="variables.length === 0">
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No variables defined. Click "New Variable" to create one.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Consolidated Action Buttons -->
        <div class="flex flex-wrap gap-2">
            <button @click="saveVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-primary-600 hover:bg-primary-700 text-white">
                <i class="fas fa-save mr-1"></i> Save
            </button>
            <button @click="exportVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-gray-600 hover:bg-gray-700 text-white">
                <i class="fas fa-download mr-1"></i> Export
            </button>
            <button @click="$refs.importInput.click()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-gray-600 hover:bg-gray-700 text-white">
                <i class="fas fa-upload mr-1"></i> Import
            </button>
            <input type="file" x-ref="importInput" @change="importVariables($event)" class="hidden" accept=".json">
            <button @click="clearVariables()" class="px-3 py-1.5 text-sm rounded transition-colors duration-200 flex items-center bg-red-600 hover:bg-red-700 text-white">
                <i class="fas fa-broom mr-1"></i> Clear All
            </button>
        </div>

        <!-- Predefined Variables with improved labeling -->
        <div class="mt-4">
            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2 text-sm">Common Variables</h4>
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
</div>