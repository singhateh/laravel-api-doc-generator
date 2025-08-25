  <div class="border-b border-gray-200 dark:border-dark-700">
                    <nav class="flex space-x-1 px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'request'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'request',
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'request'
                            }"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-colors duration-200"
                        >
                            <i class="fas fa-paper-plane me-2"></i>
                            Request
                        </button>
                        <button
                            @click="activeTab = 'response'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'response',
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'response'
                            }"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-colors duration-200"
                        >
                            <i class="fas fa-reply me-2"></i>
                            Response
                        </button>
                        <button
                            @click="activeTab = 'variables'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'variables',
                                'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'variables'
                            }"
                            class="py-4 px-3 border-b-2 font-medium text-sm transition-colors duration-200"
                        >
                            <i class="fas fa-database me-2"></i>
                            Variables
                        </button>
                    </nav>
                </div>