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