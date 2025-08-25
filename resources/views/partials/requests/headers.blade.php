 <div x-show="requestTab === 'headers'" x-transition>
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Request Headers</h4>
                                            <div class="flex items-center space-x-2">
                                                <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                    <input type="checkbox" x-model="showDefaultHeaders" class="mr-2 rounded">
                                                    Show Default Headers
                                                </label>
                                                <button 
                                                    @click="addHeader()"
                                                    class="flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors text-sm"
                                                >
                                                    <i class="fas fa-plus me-1"></i>Add Header
                                                </button>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <template x-for="(header, index) in requestData.headers" :key="index">
                                                <div class="flex items-center space-x-3" x-show="showDefaultHeaders || !isDefaultHeader(header.name)">
                                                    <input 
                                                        type="text" 
                                                        x-model="header.name"
                                                        placeholder="Header name" 
                                                        class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                    >
                                                    <input 
                                                        type="text" 
                                                        x-model="header.value"
                                                        placeholder="Header value" 
                                                        class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                    >
                                                    <button 
                                                        @click="requestData.headers.splice(index, 1)"
                                                        class="px-3 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                        x-show="!isDefaultHeader(header.name)"
                                                    >
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <span class="px-2 py-1 text-xs text-gray-500" x-show="isDefaultHeader(header.name)">Default</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>