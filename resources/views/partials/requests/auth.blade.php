<div x-show="requestTab === 'auth'" x-transition>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Global Token</label>
                                            <div class="flex space-x-2 mb-2">
                                                <select 
                                                    x-model="globalTokenType"
                                                    class="w-32 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                >
                                                    <option value="bearer">Bearer</option>
                                                    <option value="basic">Basic</option>
                                                    <option value="api_key">API Key</option>
                                                    <option value="custom">Custom</option>
                                                </select>
                                                <input 
                                                    type="text" 
                                                    x-model="globalToken"
                                                    placeholder="Token value" 
                                                    class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                >
                                                <button 
                                                    @click="saveGlobalToken()"
                                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200"
                                                >
                                                    Save
                                                </button>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                This token will be used for all requests unless overridden below
                                            </p>
                                        </div>
                                        
                                        <select x-model="requestData.auth.type" class="mb-4 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                                            <option value="">No Auth</option>
                                            <option value="bearer">Bearer Token</option>
                                            <option value="basic">Basic Auth</option>
                                            <option value="api_key">API Key</option>
                                            <option value="global">Use Global Token</option>
                                        </select>

                                        <div x-show="requestData.auth.type === 'bearer'" class="space-y-3">
                                            <input 
                                                type="text" 
                                                x-model="requestData.auth.token"
                                                placeholder="Bearer token" 
                                                class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                            >
                                        </div>

                                        <div x-show="requestData.auth.type === 'basic'" class="space-y-3">
                                            <input 
                                                type="text" 
                                                x-model="requestData.auth.username"
                                                placeholder="Username" 
                                                class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                            >
                                            <input 
                                                type="password" 
                                                x-model="requestData.auth.password"
                                                placeholder="Password" 
                                                class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                            >
                                        </div>

                                        <div x-show="requestData.auth.type === 'api_key'" class="space-y-3">
                                            <input 
                                                type="text" 
                                                x-model="requestData.auth.key"
                                                placeholder="Header name" 
                                                class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                value="X-API-Key"
                                            >
                                            <input 
                                                type="text" 
                                                x-model="requestData.auth.value"
                                                placeholder="API Key" 
                                                class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                            >
                                        </div>
                                        
                                        <div x-show="requestData.auth.type === 'global'" class="text-sm text-gray-600 dark:text-gray-400 p-3 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                            <p>Using global token: <span x-text="globalTokenType"></span> <span x-text="globalToken ? globalToken.substring(0, 20) + '...' : 'Not set'"></span></p>
                                        </div>
                                    </div>