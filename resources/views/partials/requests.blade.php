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

                                <div class="p-4 bg-white dark:bg-dark-800">
                                    <!-- Params Tab -->
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

                                    <!-- Headers Tab -->
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

                                    <!-- Body Tab -->
                                    <div x-show="requestTab === 'body'" x-transition>
                                        <select x-model="bodyType" class="mb-3 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                                            <option value="json">JSON</option>
                                            <option value="text">Text</option>
                                            <option value="form-data">Form Data</option>
                                            <option value="x-www-form-urlencoded">Form URL Encoded</option>
                                        </select>
                                        
                                        <!-- JSON Body -->
                                        <template x-if="bodyType === 'json'">
                                            <div class="relative">
                                                <textarea 
                                                    x-model="requestData.body"
                                                    rows="8"
                                                    placeholder='{"key": "value"}' 
                                                    class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white font-mono"
                                                ></textarea>
                                                <button 
                                                    @click="copyToClipboard(requestData.body)"
                                                    class="absolute top-2 right-2 px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded text-sm hover:bg-gray-300 dark:hover:bg-dark-500 transition-colors"
                                                    title="Copy JSON"
                                                >
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <!-- Text Body -->
                                        <template x-if="bodyType === 'text'">
                                            <div class="relative">
                                                <textarea 
                                                    x-model="requestData.body"
                                                    rows="8"
                                                    placeholder="Enter plain text here..." 
                                                    class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                ></textarea>
                                                <button 
                                                    @click="copyToClipboard(requestData.body)"
                                                    class="absolute top-2 right-2 px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded text-sm hover:bg-gray-300 dark:hover:bg-dark-500 transition-colors"
                                                    title="Copy Text"
                                                >
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <!-- Form Data -->
                                        <template x-if="bodyType === 'form-data'">
                                            <div class="space-y-3">
                                                <template x-for="(field, index) in formDataFields" :key="index">
                                                    <div class="flex items-center space-x-3">
                                                        <input 
                                                            type="text" 
                                                            x-model="field.key"
                                                            placeholder="Field name" 
                                                            class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                        >
                                                        <template x-if="field.type === 'text'">
                                                            <input 
                                                                type="text" 
                                                                x-model="field.value"
                                                                placeholder="Value" 
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                            >
                                                        </template>
                                                        <template x-if="field.type === 'file'">
                                                            <input 
                                                                type="file" 
                                                                @change="handleFileUpload($event, index)"
                                                                class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                            >
                                                        </template>
                                                        <select 
                                                            x-model="field.type"
                                                            class="w-24 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                        >
                                                            <option value="text">Text</option>
                                                            <option value="file">File</option>
                                                        </select>
                                                        <button 
                                                            @click="formDataFields.splice(index, 1)"
                                                            class="px-3 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                                <button 
                                                    @click="formDataFields.push({ key: '', value: '', type: 'text' })"
                                                    class="flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors"
                                                >
                                                    <i class="fas fa-plus me-2"></i>Add Field
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <!-- Form URL Encoded -->
                                        <template x-if="bodyType === 'x-www-form-urlencoded'">
                                            <div class="space-y-3">
                                                <template x-for="(field, index) in urlEncodedFields" :key="index">
                                                    <div class="flex items-center space-x-3">
                                                        <input 
                                                            type="text" 
                                                            x-model="field.key"
                                                            placeholder="Field name" 
                                                            class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                        >
                                                        <input 
                                                            type="text" 
                                                            x-model="field.value"
                                                            placeholder="Value" 
                                                            class="flex-1 px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white"
                                                        >
                                                        <button 
                                                            @click="urlEncodedFields.splice(index, 1)"
                                                            class="px-3 py-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors"
                                                        >
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                                <button 
                                                    @click="urlEncodedFields.push({ key: '', value: '' })"
                                                    class="flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 transition-colors"
                                                >
                                                    <i class="fas fa-plus me-2"></i>Add Field
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Auth Tab -->
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

                                    <!-- Scripts Tab -->
                                    {{-- <div x-show="requestTab === 'scripts'" x-transition>
                                        <div class="space-y-4">
                                            <!-- Script Selection -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Script</label>
                                                <select x-model="selectedScript" class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white">
                                                    <option value="">-- Select a script --</option>
                                                    <option value="login">Login & Store Token</option>
                                                    <option value="authHeader">Set Auth Header</option>
                                                    <option value="refreshToken">Refresh Token</option>
                                                    <option value="custom">Custom Script</option>
                                                </select>
                                            </div>

                                            <!-- Script Editor -->
                                            <template x-if="selectedScript">
                                                <div class="space-y-3">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Script Code</label>
                                                    <div class="relative">
                                                        <textarea 
                                                            x-model="scripts[selectedScript]"
                                                            rows="8"
                                                            class="w-full px-3 py-2 border border-gray-200 dark:border-dark-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white dark:bg-dark-700 text-gray-900 dark:text-white font-mono text-sm"
                                                            placeholder="Write your script here..."
                                                        ></textarea>
                                                        <button 
                                                            @click="copyToClipboard(scripts[selectedScript])"
                                                            class="absolute top-2 right-2 px-2 py-1 bg-gray-200 dark:bg-dark-600 text-gray-600 dark:text-gray-400 rounded text-sm hover:bg-gray-300 dark:hover:bg-dark-500 transition-colors"
                                                            title="Copy Script"
                                                        >
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="flex space-x-3">
                                                        <button 
                                                            @click="saveScript(selectedScript)"
                                                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors duration-200"
                                                        >
                                                            <i class="fas fa-save me-2"></i>Save Script
                                                        </button>
                                                        
                                                        <button 
                                                            @click="runScript(selectedScript)"
                                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200"
                                                        >
                                                            <i class="fas fa-play me-2"></i>Run Script
                                                        </button>
                                                        
                                                        <button 
                                                            @click="resetScript(selectedScript)"
                                                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200"
                                                        >
                                                            <i class="fas fa-undo me-2"></i>Reset to Default
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Script Output -->
                                            <template x-if="scriptOutput">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Script Output</label>
                                                    <div class="bg-gray-100 dark:bg-dark-700 p-4 rounded-lg font-mono text-sm overflow-x-auto">
                                                        <pre x-text="scriptOutput"></pre>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div> --}}
                                    <div x-show="requestTab === 'scripts'" x-transition>
                                    @include('api-doc-generator::partials.requests.script')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>