   <div x-show="activeTab === 'response'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div x-show="!responseData" class="text-center py-12 text-gray-400 dark:text-gray-600">
                            <i class="fas fa-inbox fa-3x mb-4"></i>
                            <p>Send a request to see the response here</p>
                        </div>

                        <div x-show="responseData" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium" 
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400': responseData.status >= 200 && responseData.status < 300,
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': responseData.status >= 400,
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400': responseData.status >= 300 && responseData.status < 400
                                        }">
                                        <span x-text="responseData.status || 'Error'"></span> 
                                        <span x-text="getStatusText(responseData.status)"></span>
                                    </span>
                                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400 rounded-full text-sm">
                                        <span x-text="formatBytes(responseData.size || 0)"></span> • 
                                        <span x-text="(responseData.time || 0) + 'ms'"></span>
                                    </span>
                                </div>
                                <button 
                                    @click="copyToClipboard(formatResponseBody(responseData))"
                                    class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                    title="Copy Response"
                                >
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                            </div>

                            <!-- Response Tabs -->
                            <div class="border border-gray-200 dark:border-dark-600 rounded-lg overflow-hidden">
                                <nav class="bg-gray-50 dark:bg-dark-700 px-4 flex space-x-1">
                                    <button
                                        @click="responseTab = 'body'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': responseTab === 'body',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': responseTab !== 'body'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        Body
                                    </button>
                                    <button
                                        @click="responseTab = 'headers'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': responseTab === 'headers',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': responseTab !== 'headers'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        Headers
                                    </button>
                                </nav>

                                <div class="p-4 bg-gray-50 dark:bg-dark-800">
                                    <!-- Body Tab -->
                                    <div x-show="responseTab === 'body'" x-transition>
                                        <div class="relative">
                                            <pre class="bg-white dark:bg-dark-700 rounded-lg p-4 overflow-x-auto"><code class="language-json" x-text="formatResponseBody(responseData.body)"></code></pre>
                                            <button 
                                                @click="copyToClipboard(formatResponseBody(responseData.body))"
                                                class="absolute top-4 right-4 px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded text-sm hover:bg-gray-300 dark:hover:bg-dark-500 transition-colors"
                                                title="Copy Response Body"
                                            >
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Headers Tab -->
                                    <div x-show="responseTab === 'headers'" x-transition>
                                        <div class="relative">
                                            <pre class="bg-white dark:bg-dark-700 rounded-lg p-4 overflow-x-auto"><code class="language-json" x-text="formatResponseBody(responseData.headers)"></code></pre>
                                            <button 
                                                @click="copyToClipboard(formatResponseBody(responseData.headers))"
                                                class="absolute top-4 right-4 px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded text-sm hover:bg-gray-300 dark:hover:bg-dark-500 transition-colors"
                                                title="Copy Response Headers"
                                            >
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>