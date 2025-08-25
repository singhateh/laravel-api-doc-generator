<div x-show="requestTab === 'params'" x-transition>
                                        <div class="space-y-3">
                                            <!-- Path Parameters -->
                                            <template x-if="pathParams.length > 0">
                                                <div>
                                                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Path Parameters</h4>
                                                    <template x-for="(param, index) in pathParams" :key="index">
                                                        <div class="flex items-center space-x-3 mb-3">
                                                            <input 
                                                                type="text" 
                                                                x-model="param.name"
                                                                placeholder="Key" 
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-gray-100 dark:bg-dark-600 text-gray-900 dark:text-white"
                                                                readonly
                                                            >
                                                            <input 
                                                                type="text" 
                                                                x-model="param.value"
                                                                placeholder="Value" 
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                                @input="updateUrl()"
                                                            >
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            
                                            <!-- Query Parameters -->
                                            <template x-if="queryParams.length > 0">
                                                <div>
                                                    <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Query Parameters</h4>
                                                    <template x-for="(param, index) in queryParams" :key="index">
                                                        <div class="flex items-center space-x-3 mb-3">
                                                            <input 
                                                                type="text" 
                                                                x-model="param.name"
                                                                placeholder="Key" 
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                            >
                                                            <input 
                                                                type="text" 
                                                                x-model="param.value"
                                                                placeholder="Value" 
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                                @input="updateUrl()"
                                                            >
                                                            <button 
                                                                @click="queryParams.splice(index, 1)"
                                                                class="px-3 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                            >
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <button 
                                                        @click="queryParams.push({ name: '', value: '' })"
                                                        class="flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors"
                                                    >
                                                        <i class="fas fa-plus me-2"></i>Add Query Parameter
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>