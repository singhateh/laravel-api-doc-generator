<nav class="bg-gray-50 dark:bg-dark-700 px-4 flex space-x-1">
                                    <button
                                        @click="requestTab = 'params'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': requestTab === 'params',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': requestTab !== 'params'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        <i class="fas fa-list me-2"></i>Params
                                    </button>
                                    <button
                                        @click="requestTab = 'headers'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': requestTab === 'headers',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': requestTab !== 'headers'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        <i class="fas fa-heading me-2"></i>Headers
                                    </button>
                                    <button
                                        @click="requestTab = 'body'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': requestTab === 'body',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': requestTab !== 'body'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        <i class="fas fa-code me-2"></i>Body
                                    </button>
                                    <button
                                        @click="requestTab = 'auth'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': requestTab === 'auth',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': requestTab !== 'auth'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        <i class="fas fa-lock me-2"></i>Auth
                                    </button>
                                    <button
                                        @click="requestTab = 'scripts'"
                                        :class="{
                                            'bg-white dark:bg-dark-800 text-primary-600 dark:text-primary-400': requestTab === 'scripts',
                                            'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200': requestTab !== 'scripts'
                                        }"
                                        class="px-4 py-2 text-sm font-medium rounded-t-md transition-colors duration-200"
                                    >
                                        <i class="fas fa-code me-2"></i>Scripts
                                    </button>
                                </nav>