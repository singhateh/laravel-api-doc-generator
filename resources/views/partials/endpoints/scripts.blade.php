@push('scripts')
    <script>
    // Global helper functions for scripts
    function getEnvironmentVariable(key) {
        return localStorage.getItem(`env_${key}`);
    }
    
    function setEnvironmentVariable(key, value) {
        localStorage.setItem(`env_${key}`, value);
        return value;
    }
    
    function clearEnvironment() {
        Object.keys(localStorage)
            .filter(key => key.startsWith('env_'))
            .forEach(key => localStorage.removeItem(key));
    }
    
    // Global copy function
    function copyEndpointUrl(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Endpoint URL copied to clipboard! 📋');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showToast('Failed to copy to clipboard ❌', 'error');
        });
    }

    // Global toast function
    function showToast(message, type = 'success') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-y-8 opacity-0 toast-notification ${
            type === 'success' ? 'bg-green-600 text-white' :
            type === 'error' ? 'bg-red-600 text-white' :
            'bg-gray-800 text-white'
        }`;
        
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-bell'
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-y-8', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-8', 'opacity-0');
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Global copy function
    function copyToClipboard(text) {
        if (!text || text.trim() === '') {
            showToast('Nothing to copy', 'error');
            return false;
        }
        
        // Create a temporary textarea element
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textarea);
            
            if (successful) {
                showToast('Copied to clipboard! 📋', 'success');
                return true;
            } else {
                showToast('Failed to copy to clipboard', 'error');
                return false;
            }
        } catch (err) {
            document.body.removeChild(textarea);
            console.error('Failed to copy: ', err);
            showToast('Failed to copy to clipboard', 'error');
            return false;
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('endpointTester', () => ({
            activeTab: 'request',
            requestTab: 'params',
            responseTab: 'body',
            isLoading: false,
            bodyType: 'json',
            formDataFields: [],
            urlEncodedFields: [],
            responseData: null,
            pathParams: [],
            queryParams: [
                { name: 'limit', value: '20' },
                { name: 'offset', value: '0' }
            ],
            showDefaultHeaders: false,
            globalToken: localStorage.getItem('api_docs_global_token') || '',
            globalTokenType: localStorage.getItem('api_docs_global_token_type') || 'bearer',
            
            // Scripts-related properties
            scriptType: 'pre-request',
            scriptOutput: '',
            scripts: {
                'pre-request': '// Pre-request script\n// This runs before the request is sent\n\n// Example: Set timestamp header\nconst timestamp = new Date().toISOString();\nad.request.headers.add({\n    key: \'X-Timestamp\',\n    value: timestamp\n});\n\nconsole.log(`Pre-request executed at ${timestamp}`);',
                'post-response': '// Post-response script\n// This runs after the response is received\n\n// Example: Basic response validation\nad.test("Status code is 200", function () {\n    ad.response.to.have.status(200);\n});\n\n// Parse and log response\nconst response = ad.response.json();\nconsole.log("Response:", response);'
            },

            // Variables section
            variables: JSON.parse(localStorage.getItem('api_docs_variables') || '[]'),
            
            requestData: {
                baseUrl: 'https://api.example.com/api/apitest/items',
                method: 'GET',
                headers: [
                    { name: 'Content-Type', value: 'application/json' },
                    { name: 'Accept', value: 'application/json' }
                ],
                body: '',
                auth: { type: 'none', token: '', username: '', password: '', key: '', value: '' }
            },
            
            defaultHeaders: ['host', 'user-agent', 'accept', 'accept-encoding', 'connection', 'content-length'],
           
            init() {
                // Parse the endpoint path to extract parameters
                const path = '/api/apitest/items';
                
                // Extract path parameters like :id or {id}
                const pathParamRegex = /[:{]([\w-]+)[}]?/g;
                let match;
                while ((match = pathParamRegex.exec(path)) !== null) {
                    this.pathParams.push({
                        name: match[1],
                        value: '',
                        required: true
                    });
                }
                
                // Set appropriate content type header based on method
                if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                    const contentTypeHeader = this.requestData.headers.find(h => h.name.toLowerCase() === 'content-type');
                    if (contentTypeHeader) {
                        if (contentTypeHeader.value.includes('json')) this.bodyType = 'json';
                        else if (contentTypeHeader.value.includes('x-www-form-urlencoded')) this.bodyType = 'x-www-form-urlencoded';
                        else if (contentTypeHeader.value.includes('form-data')) this.bodyType = 'form-data';
                        else if (contentTypeHeader.value.includes('text/plain')) this.bodyType = 'text';
                    }
                }
            },
            
            // Check if header is a default header
            isDefaultHeader(headerName) {
                return this.defaultHeaders.includes(headerName.toLowerCase());
            },
            
            // Add a new header
            addHeader() {
                this.requestData.headers.push({ name: '', value: '' });
            },
            
            // Save global token
            saveGlobalToken() {
                localStorage.setItem('api_docs_global_token', this.globalToken);
                localStorage.setItem('api_docs_global_token_type', this.globalTokenType);
                this.scriptOutput = 'Global token saved successfully.';
            },
            
            // Scripts-related methods
            saveScript(type) {
                // Save script to localStorage
                const scriptsToSave = {...this.scripts};
                localStorage.setItem('api_docs_scripts', JSON.stringify(scriptsToSave));
                this.scriptOutput = `${type === 'pre-request' ? 'Pre-request' : 'Post-response'} script saved successfully.`;
            },
            
            resetScript(type) {
                // Reset to default script
                if (type === 'pre-request') {
                    this.scripts['pre-request'] = '// Pre-request script\n// This runs before the request is sent\n\n// Example: Set timestamp header\nconst timestamp = new Date().toISOString();\nad.request.headers.add({\n    key: \'X-Timestamp\',\n    value: timestamp\n});\n\nconsole.log(`Pre-request executed at ${timestamp}`);';
                } else {
                    this.scripts['post-response'] = '// Post-response script\n// This runs after the response is received\n\n// Example: Basic response validation\nad.test("Status code is 200", function () {\n    ad.response.to.have.status(200);\n});\n\n// Parse and log response\nconst response = ad.response.json();\nconsole.log("Response:", response);';
                }
                
                this.scriptOutput = `${type === 'pre-request' ? 'Pre-request' : 'Post-response'} script reset to default.`;
            },
            
            async runScript(type) {
                try {
                    const script = this.scripts[type];
                    
                    // Create a function from the script
                    const executeScript = new Function('ad', 'console', 'return (async () => {' + script + '})()');
                    
                    // Mock ad object similar to Postman but using your actual variable storage
                    const mockAD = {
                        request: {
                            headers: {
                                add: (header) => {
                                    // Add header to request
                                    this.requestData.headers.push(header);
                                }
                            }
                        },
                        environment: {
                            set: (key, value) => {
                                // Save to your actual variable storage format
                                try {
                                    const currentVariables = JSON.parse(localStorage.getItem('api_docs_variables') || '[]');
                                    const existingIndex = currentVariables.findIndex(v => v.name === key);
                                    
                                    if (existingIndex >= 0) {
                                        currentVariables[existingIndex].value = value;
                                    } else {
                                        currentVariables.push({ name: key, value: value });
                                    }
                                    
                                    localStorage.setItem('api_docs_variables', JSON.stringify(currentVariables));
                                    
                                    // Also update the component's variables array if it exists
                                    if (this.variables) {
                                        const compIndex = this.variables.findIndex(v => v.name === key);
                                        if (compIndex >= 0) {
                                            this.variables[compIndex].value = value;
                                        } else {
                                            this.variables.push({ name: key, value: value });
                                        }
                                    }
                                } catch (error) {
                                    console.error('Error saving variable:', error);
                                }
                            },
                            get: (key) => {
                                // Get from your actual variable storage format
                                try {
                                    const currentVariables = JSON.parse(localStorage.getItem('api_docs_variables') || '[]');
                                    const variable = currentVariables.find(v => v.name === key);
                                    return variable ? variable.value : null;
                                } catch (error) {
                                    console.error('Error reading variable:', error);
                                    return null;
                                }
                            }
                        },
                        test: (name, assertion) => {
                            // Simple test implementation
                            try {
                                const result = assertion();
                                this.scriptOutput += `✓ ${name}\n`;
                                return result;
                            } catch (error) {
                                this.scriptOutput += `✗ ${name} - ${error.message}\n`;
                                return false;
                            }
                        },
                        expect: (value) => ({
                            to: {
                                have: {
                                    status: (expectedStatus) => () => {
                                        return this.responseData?.status === expectedStatus;
                                    }
                                }
                            }
                        }),
                        // Add response object for post-response scripts
                        response: this.responseData ? {
                            status: this.responseData.status,
                            json: () => {
                                try {
                                    return JSON.parse(this.responseData.body);
                                } catch {
                                    return {};
                                }
                            },
                            text: () => this.responseData.body
                        } : null
                    };
                    
                    // Mock console for script output
                    const mockConsole = {
                        log: (...args) => {
                            this.scriptOutput += args.join(' ') + '\n';
                        },
                        error: (...args) => {
                            this.scriptOutput += 'ERROR: ' + args.join(' ') + '\n';
                        },
                        warn: (...args) => {
                            this.scriptOutput += 'WARN: ' + args.join(' ') + '\n';
                        },
                        info: (...args) => {
                            this.scriptOutput += 'INFO: ' + args.join(' ') + '\n';
                        }
                    };
                    
                    // Execute the script
                    this.scriptOutput = `Running ${type} script...\n\n`;
                    const result = await executeScript(mockAD, mockConsole);
                    
                    if (result !== undefined) {
                        this.scriptOutput += `\nScript returned: ${JSON.stringify(result, null, 2)}`;
                    }
                    
                } catch (error) {
                    this.scriptOutput = `Error executing script: ${error.message}\n\nStack trace:\n${error.stack}`;
                    console.error('Script execution error:', error);
                }
            },
            
            // Variables section methods
            addVariable() {
                this.variables.push({ name: '', value: '' });
            },
            
            saveVariables() {
                localStorage.setItem('api_docs_variables', JSON.stringify(this.variables));
                showToast('Variables saved successfully!');
            },
            
            exportVariables() {
                const dataStr = JSON.stringify(this.variables, null, 2);
                const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                
                const exportFileDefaultName = 'api-docs-variables.json';
                
                const linkElement = document.createElement('a');
                linkElement.setAttribute('href', dataUri);
                linkElement.setAttribute('download', exportFileDefaultName);
                linkElement.click();
            },
            
            importVariables() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.json';
                
                input.onchange = e => {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = event => {
                        try {
                            const importedVariables = JSON.parse(event.target.result);
                            if (Array.isArray(importedVariables)) {
                                this.variables = importedVariables;
                                this.saveVariables();
                                showToast('Variables imported successfully!');
                            } else {
                                showToast('Invalid file format', 'error');
                            }
                        } catch (error) {
                            showToast('Error parsing JSON file', 'error');
                        }
                    };
                    
                    reader.readAsText(file);
                };
                
                input.click();
            },

           addPredefinedVariable(name, value) {
                if (!this.variables.find(v => v.name === name)) {
                    this.variables.push({ name, value });
                    this.saveVariables();
                    showToast(`Added variable: ${name}`);
                } else {
                    showToast(`Variable ${name} already exists`, 'info');
                }
            },

            clearVariables() {
                if (confirm('Are you sure you want to clear all variables?')) {
                    this.variables = [];
                    this.saveVariables();
                    showToast('Variables cleared');
                }
            },

            // Enhanced variable replacement with error handling
            replaceVariables(text) {
                if (!text || typeof text !== 'string') return text;
                
                try {
                    return text.replace(/\{\{(\w+)\}\}/g, (match, variableName) => {
                        const variable = this.variables.find(v => v.name === variableName);
                        if (variable) {
                            return variable.value;
                        } else {
                            console.warn(`Variable ${variableName} not found`);
                            return match; // Return the original pattern if variable not found
                        }
                    });
                } catch (error) {
                    console.error('Error replacing variables:', error);
                    return text;
                }
            },
            
            getQueryString() {
                const params = this.queryParams.filter(p => p.name && p.value);
                if (params.length === 0) return '';
                
                const queryString = params.map(p => `${encodeURIComponent(p.name)}=${encodeURIComponent(p.value)}`).join('&');
                return `?${queryString}`;
            },

            updateUrl() {
                // Build the URL with path parameters
                let url = 'https://api.example.com/api/apitest/items';
                
                // Replace path parameters for both :param and {param} formats
                this.pathParams.forEach(param => {
                    if (param.value) {
                        const encodedValue = encodeURIComponent(param.value);
                        // Replace :param format
                        url = url.replace(`:${param.name}`, encodedValue);
                        // Replace {param} format
                        url = url.replace(`{${param.name}}`, encodedValue);
                    } else {
                        // If no value provided, keep the placeholder but show it's missing
                        url = url.replace(`:${param.name}`, `:${param.name}`);
                        url = url.replace(`{${param.name}}`, `{${param.name}}`);
                    }
                });
                
                this.requestData.baseUrl = url;
            },

            handleFileUpload(event, index) {
                const file = event.target.files[0];
                if (file) {
                    this.formDataFields[index].value = file;
                }
            },

            async sendRequest() {
                // Run pre-request script if it exists
                if (this.scripts['pre-request'] && this.scripts['pre-request'].trim() !== '') {
                    await this.runScript('pre-request');
                }
                
                this.isLoading = true;
                this.activeTab = 'response';
                
                try {
                    // Prepare headers with variable replacement
                    const headers = {};
                    this.requestData.headers.forEach(header => {
                        if (header.name && header.value) {
                            const headerName = this.replaceVariables(header.name);
                            const headerValue = this.replaceVariables(header.value);
                            headers[headerName] = headerValue;
                        }
                    });
                    
                    // Set appropriate Content-Type header based on body type
                    if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                        switch(this.bodyType) {
                            case 'json':
                                headers['Content-Type'] = 'application/json';
                                break;
                            case 'text':
                                headers['Content-Type'] = 'text/plain';
                                break;
                            case 'form-data':
                                // Let the browser set the content type with boundary
                                delete headers['Content-Type'];
                                break;
                            case 'x-www-form-urlencoded':
                                headers['Content-Type'] = 'application/x-www-form-urlencoded';
                                break;
                        }
                    }
                    
                    // Add auth headers if needed with variable replacement
                    const hasLocalToken = this.requestData.auth && (
                        (this.requestData.auth.type === 'bearer' && this.requestData.auth.token) ||
                        (this.requestData.auth.type === 'basic' && this.requestData.auth.username && this.requestData.auth.password) ||
                        (this.requestData.auth.type === 'api_key' && this.requestData.auth.key && this.requestData.auth.value)
                    );

                    if (hasLocalToken) {
                        // Use local auth first
                        if (this.requestData.auth.type === 'bearer') {
                            headers['Authorization'] = `Bearer ${this.replaceVariables(this.requestData.auth.token)}`;
                        } else if (this.requestData.auth.type === 'basic') {
                            const username = this.replaceVariables(this.requestData.auth.username);
                            const password = this.replaceVariables(this.requestData.auth.password);
                            headers['Authorization'] = `Basic ${btoa(`${username}:${password}`)}`;
                        } else if (this.requestData.auth.type === 'api_key') {
                            const key = this.replaceVariables(this.requestData.auth.key);
                            const value = this.replaceVariables(this.requestData.auth.value);
                            headers[key] = value;
                        }
                    } else if (this.globalToken) {
                        // No local token, use global token
                        const token = this.replaceVariables(this.globalToken);
                        if (this.globalTokenType === 'bearer') {
                            headers['Authorization'] = `Bearer ${token}`;
                        } else if (this.globalTokenType === 'basic') {
                            headers['Authorization'] = `Basic ${token}`;
                        } else if (this.globalTokenType === 'api_key') {
                            headers['X-API-Key'] = token;
                        } else if (this.globalTokenType === 'custom') {
                            headers['Authorization'] = token; // assume already formatted
                        }
                    }

                    
                    // Prepare URL with variable replacement
                    let url = this.replaceVariables(this.requestData.baseUrl);
                    const urlParams = new URLSearchParams();
                    
                    // Process query parameters with variable replacement
                    this.queryParams.forEach(param => {
                        if (param.name && param.value) {
                            const paramName = this.replaceVariables(param.name);
                            const paramValue = this.replaceVariables(param.value);
                            urlParams.append(paramName, paramValue);
                        }
                    });
                    
                    const queryString = urlParams.toString();
                    if (queryString) {
                        url += (url.includes('?') ? '&' : '?') + queryString;
                    }
                    
                    // Prepare request options
                    const options = {
                        method: this.requestData.method,
                        headers: headers
                    };
                    
                    // Prepare body based on body type with variable replacement
                    if (['POST', 'PUT', 'PATCH'].includes(this.requestData.method)) {
                        switch(this.bodyType) {
                            case 'json':
                                options.body = this.replaceVariables(this.requestData.body);
                                break;
                            case 'text':
                                options.body = this.replaceVariables(this.requestData.body);
                                break;
                            case 'form-data':
                                const formData = new FormData();
                                this.formDataFields.forEach(field => {
                                    if (field.key) {
                                        const fieldKey = this.replaceVariables(field.key);
                                        let fieldValue = field.value;
                                        
                                        // Handle file uploads differently
                                        if (field.type === 'file' && field.value instanceof File) {
                                            fieldValue = field.value;
                                        } else {
                                            fieldValue = this.replaceVariables(field.value);
                                        }
                                        
                                        formData.append(fieldKey, fieldValue);
                                    }
                                });
                                options.body = formData;
                                break;
                            case 'x-www-form-urlencoded':
                                const urlEncodedData = new URLSearchParams();
                                this.urlEncodedFields.forEach(field => {
                                    if (field.key && field.value) {
                                        const fieldKey = this.replaceVariables(field.key);
                                        const fieldValue = this.replaceVariables(field.value);
                                        urlEncodedData.append(fieldKey, fieldValue);
                                    }
                                });
                                options.body = urlEncodedData.toString();
                                break;
                        }
                    }
                    
                    // Send the request
                    const startTime = Date.now();
                    const response = await fetch(url, options);
                    const endTime = Date.now();
                    
                    // Process response
                    const responseText = await response.text();
                    
                    this.responseData = {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries([...response.headers]),
                        body: responseText,
                        time: endTime - startTime,
                        size: new TextEncoder().encode(responseText).length
                    };
                    
                    // Run post-response script if it exists
                    if (this.scripts['post-response'] && this.scripts['post-response'].trim() !== '') {
                        await this.runScript('post-response');
                    }
                    
                    // Re-run highlight.js for syntax highlighting
                    if (typeof hljs !== 'undefined') {
                        setTimeout(() => hljs.highlightAll(), 100);
                    }
                } catch (error) {
                    console.error('Request failed:', error);
                    this.responseData = {
                        status: 0,
                        statusText: 'Error',
                        headers: {},
                        body: error.message,
                        time: 0,
                        size: 0
                    };
                } finally {
                    this.isLoading = false;
                }
            },
            
            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + sizes[i];
            },
            
            getStatusText(status) {
                const statusTexts = {
                    200: 'OK',
                    201: 'Created',
                    204: 'No Content',
                    400: 'Bad Request',
                    401: 'Unauthorized',
                    403: 'Forbidden',
                    404: 'Not Found',
                    500: 'Internal Server Error'
                };
                return statusTexts[status] || '';
            },
            
            formatResponseBody(body) {
                if (typeof body === 'object') {
                    return JSON.stringify(body, null, 2);
                }
                try {
                    return JSON.stringify(JSON.parse(body), null, 2);
                } catch {
                    return body;
                }
            }
        }));
    });
</script>
@endpush