<div x-show="requestTab === 'scripts'" x-transition>
    <div class="space-y-6">
        <!-- Script Type Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Script Type</label>
            <div class="flex space-x-4">
                <label class="inline-flex items-center px-4 py-2 rounded-lg border transition-colors duration-200"
                       :class="scriptType === 'pre-request' 
                           ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                           : 'border-gray-200 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-dark-500'">
                    <input type="radio" x-model="scriptType" value="pre-request" class="sr-only">
                    <i class="fas fa-play-circle mr-2"></i>
                    <span>Pre-request</span>
                </label>
                <label class="inline-flex items-center px-4 py-2 rounded-lg border transition-colors duration-200"
                       :class="scriptType === 'post-response' 
                           ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                           : 'border-gray-200 dark:border-dark-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-dark-500'">
                    <input type="radio" x-model="scriptType" value="post-response" class="sr-only">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>Post-response</span>
                </label>
            </div>
        </div>

        <!-- Script Editor with Type Hints -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Script Code</label>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span x-text="scriptType === 'pre-request' ? 'Runs before request is sent' : 'Runs after response is received'"></span>
                </div>
            </div>
            
            <div class="relative border border-gray-200 dark:border-dark-600 rounded-lg overflow-hidden shadow-sm">
                <div class="flex items-center justify-between bg-gray-50 dark:bg-dark-700 px-4 py-2 border-b border-gray-200 dark:border-dark-600">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                        <span x-text="scriptType === 'pre-request' ? 'Pre-request Script' : 'Post-response Script'"></span>
                    </span>
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="showTypeHints = !showTypeHints"
                            class="p-1 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                            :class="showTypeHints ? 'text-blue-600 dark:text-blue-400' : ''"
                            title="Toggle Type Hints"
                        >
                            <i class="fas fa-lightbulb text-sm"></i>
                        </button>
                        <button 
                            @click="copyToClipboard(scripts[scriptType])"
                            class="p-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                            title="Copy Script"
                        >
                            <i class="fas fa-copy text-sm"></i>
                        </button>
                        <button 
                            @click="scripts[scriptType] = ''"
                            class="p-1 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                            title="Clear Script"
                        >
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Type Hints Panel -->
                <div x-show="showTypeHints" x-transition class="bg-blue-50 dark:bg-blue-900/20 p-3 border-b border-blue-100 dark:border-blue-800">
                    <div class="text-xs text-blue-800 dark:text-blue-200 mb-2 font-medium">Available ad.* methods:</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center">
                            <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded mr-1">ad.request</code>
                            <span class="text-blue-700 dark:text-blue-300">Request manipulation</span>
                        </div>
                        <div class="flex items-center">
                            <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded mr-1">ad.response</code>
                            <span class="text-blue-700 dark:text-blue-300">Response handling</span>
                        </div>
                        <div class="flex items-center">
                            <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded mr-1">ad.environment</code>
                            <span class="text-blue-700 dark:text-blue-300">Environment variables</span>
                        </div>
                        <div class="flex items-center">
                            <code class="bg-blue-100 dark:bg-blue-800 px-1 py-0.5 rounded mr-1">ad.test()</code>
                            <span class="text-blue-700 dark:text-blue-300">Create tests</span>
                        </div>
                    </div>
                </div>
                
                <textarea 
                    x-model="scripts[scriptType]"
                    x-ref="scriptTextarea"
                    rows="10"
                    class="w-full px-4 py-3 bg-white dark:bg-dark-800 text-gray-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-0 resize-y"
                    :placeholder="scriptType === 'pre-request' 
                        ? '// Pre-request script example:\n// Set headers or environment variables before the request\n\nconst timestamp = new Date().toISOString();\nad.request.headers.add({\n    key: \\'X-Timestamp\\',\n    value: timestamp\n});\n\nconsole.log(`Request sent at: ${timestamp}`);'
                        : '// Post-response script example:\n// Validate response or process data after the request\n\nad.test(\\'Status is 200\\', () => ad.response.status === 200);\n\nconst response = ad.response.json();\nif (response.token) {\n    ad.environment.set(\\'auth_token\\', response.token);\n    console.log(\\'Auth token saved\\');\n}'"
                    @keydown="handleScriptKeydown($event)"
                    @keyup="checkForAutocomplete($event)"
                    @input="checkForAutocomplete($event)"
                ></textarea>

                <!-- Autocomplete Suggestions -->
                <div x-show="autocompleteSuggestions.length > 0 && showAutocomplete" 
                     x-transition
                     class="absolute left-0 right-0 bottom-full mb-1 bg-white dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg shadow-lg z-10 max-h-48 overflow-y-auto">
                    <template x-for="(suggestion, index) in autocompleteSuggestions" :key="index">
                        <div 
                            @click="insertAutocompleteSuggestion(suggestion)"
                            class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-600 transition-colors"
                            :class="{ 'bg-gray-100 dark:bg-dark-600': index === autocompleteIndex }"
                        >
                            <div class="flex items-center">
                                <span class="font-mono text-sm" x-text="suggestion.name"></span>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400" x-text="suggestion.type"></span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="suggestion.description"></div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Quick Insert Buttons -->
            <div x-show="showTypeHints" x-transition class="bg-gray-50 dark:bg-dark-700 p-3 rounded-lg">
                <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Quick Insert:</div>
                <div class="flex flex-wrap gap-2">
                    <button 
                        @click="insertSnippet('ad.request.headers.add({ key: \\'\\', value: \\'\\' });')"
                        class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 rounded transition-colors"
                    >
                        ad.request.headers.add()
                    </button>
                    <button 
                        @click="insertSnippet('ad.environment.set(\\'key\\', \\'value\\');')"
                        class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-700 dark:text-green-300 rounded transition-colors"
                    >
                        ad.environment.set()
                    </button>
                    <button 
                        @click="insertSnippet('ad.environment.get(\\'key\\');')"
                        class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900 hover:bg-purple-200 dark:hover:bg-purple-800 text-purple-700 dark:text-purple-300 rounded transition-colors"
                    >
                        ad.environment.get()
                    </button>
                    <button 
                        @click="insertSnippet('ad.test(\\'Test name\\', () => { /* Test logic */ });')"
                        class="px-2 py-1 text-xs bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-700 dark:text-yellow-300 rounded transition-colors"
                    >
                        ad.test()
                    </button>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button 
                    @click="saveScript(scriptType)"
                    class="flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200 shadow-sm"
                >
                    <i class="fas fa-save mr-2"></i>Save Script
                </button>
                
                <button 
                    @click="runScript(scriptType)"
                    class="flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 shadow-sm"
                >
                    <i class="fas fa-play mr-2"></i>Run Script
                </button>
                
                <button 
                    @click="resetScript(scriptType)"
                    class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 shadow-sm"
                >
                    <i class="fas fa-undo mr-2"></i>Reset to Default
                </button>
                
                <button 
                    @click="scriptOutput = ''"
                    class="flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-colors duration-200 shadow-sm"
                    x-show="scriptOutput"
                >
                    <i class="fas fa-broom mr-2"></i>Clear Output
                </button>
            </div>
        </div>

        <!-- Script Output -->
        <div x-show="scriptOutput" x-transition>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Script Output</label>
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="new Date().toLocaleTimeString()"></span>
            </div>
            
            <div class="bg-gray-50 dark:bg-dark-700 border border-gray-200 dark:border-dark-600 rounded-lg overflow-hidden">
                <div class="bg-gray-100 dark:bg-dark-600 px-4 py-2 border-b border-gray-200 dark:border-dark-600 flex items-center">
                    <i class="fas fa-terminal text-primary-600 mr-2"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Console Output</span>
                </div>
                <div class="p-4 font-mono text-sm overflow-x-auto max-h-60">
                    <pre class="whitespace-pre-wrap break-words" x-text="scriptOutput"></pre>
                </div>
            </div>
        </div>
    </div>
</div>