<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Services;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class ApiAnnotationGenerator
{
    protected $router;

    protected $middlewareConfig;

    protected $forceMode = false;

    protected $dryRun = false;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->middlewareConfig = config('api-doc-generator.middleware', []);
    }

    /**
     * Set force mode to regenerate all annotations
     */
    public function setForceMode(bool $forceMode): self
    {
        $this->forceMode = $forceMode;

        return $this;
    }

    /**
     * Set dry run mode to preview changes
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    public function annotateControllers(array $controllerPaths, bool $isCrossCheck = false): int
    {
        $annotatedCount = 0;

        foreach ($controllerPaths as $path) {
            if (! File::exists($path)) {
                $this->log("Path does not exist: {$path}", 'error');

                continue;
            }

            $controllers = File::allFiles($path);
            foreach ($controllers as $controller) {
                $className = $this->getFullyQualifiedClassName($controller->getPathname());
                if (! class_exists($className)) {
                    $this->log("Class does not exist: {$className}", 'warning');

                    continue;
                }

                $count = $this->processController($controller->getPathname(), $className, $isCrossCheck);
                $annotatedCount += $count;

                if ($count > 0) {
                    $this->log("Processed {$className}: {$count} methods annotated", 'info');
                }
            }
        }

        return $annotatedCount;
    }

    public function processController(string $filePath, string $className, bool $isCrossCheck = false): int
    {
        $content = File::get($filePath);
        $originalContent = $content;
        $reflection = new ReflectionClass($className);
        $annotatedCount = 0;

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $className) {
                continue;
            }

            $methodName = $method->getName();
            $docComment = $method->getDocComment();

            // Skip if already has @api and not in force mode
            if ($docComment && str_contains($docComment, '@api') && ! $this->forceMode) {

                // Run Pint on the file, regardless of whether annotations were added
                if (file_exists(base_path('vendor/bin/pint'))) {
                    $process = proc_open(
                        base_path('vendor/bin/pint').' '.escapeshellarg($filePath),
                        [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                        $pipes
                    );
                    proc_close($process);
                }

                continue;
            }

            // During cross-check run, skip adding new annotations
            if ($isCrossCheck) {
                $annotatedCount++;

                continue;
            }

            // Get route info
            $route = $this->getRouteForMethod($className, $methodName);
            if (! $route) {
                $this->log("No route found for {$className}@{$methodName}", 'warning');

                continue;
            }

            // Merge route + form request params and remove duplicates
            $routeParams = $this->getRouteParameters($route['uri']);
            $formRequestParams = $this->getFormRequestParameters($method);

            // Remove duplicates - prioritize form request params over route params
            $params = $this->mergeParametersWithoutDuplicates($routeParams, $formRequestParams);

            // Merge controller::$apiParams if defined
            if (property_exists($className, 'apiParams') && isset($className::$apiParams)) {
                foreach ($className::$apiParams as $name => $info) {
                    // Check if parameter already exists
                    $existingIndex = array_search($name, array_column($params, 'name'));
                    if ($existingIndex === false) {
                        $params[] = array_merge([
                            'name' => $name,
                            'type' => 'mixed',
                            'required' => false,
                            'description' => '',
                        ], $info);
                    }
                }
            }

            // Detect authentication requirements
            $authInfo = $this->detectAuthenticationRequirements($className, $methodName, $params);

            // Detect additional information
            $additionalInfo = $this->detectAdditionalInfo($method, $params);

            // Build annotation with additional info
            $annotation = $this->buildAnnotation(
                $methodName,
                $className,
                $route['uri'],
                $route['methods'][0],
                $params,
                $authInfo,
                $additionalInfo
            );

            // Insert annotation exactly 1 line above method
            $pattern = '/(\n\s*)(public|protected|private)\s+function\s+'.$methodName.'\s*\([^)]*\)\s*\{/';

            $content = preg_replace_callback(
                $pattern,
                function ($matches) use ($annotation) {
                    return $matches[1].$annotation."\n".$matches[0];
                },
                $content,
                1
            );

            $annotatedCount++;
        }

        if ($content !== $originalContent && ! $this->dryRun) {
            File::put($filePath, $content);

            if (file_exists(base_path('vendor/bin/pint'))) {
                $process = proc_open(
                    base_path('vendor/bin/pint').' '.escapeshellarg($filePath),
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                    $pipes
                );
                proc_close($process);
            }

            $this->log("Updated file: {$filePath}", 'info');
        } elseif ($content !== $originalContent && $this->dryRun) {
            $this->log("Would update file: {$filePath}", 'info');
        }

        return $annotatedCount;
    }

    /**
     * Log messages with different levels
     */
    protected function log(string $message, string $level = 'info'): void
    {
        if (function_exists('logger')) {
            logger()->$level($message);
        }

        // Also output to console if running in command context
        if (app()->runningInConsole()) {
            switch ($level) {
                case 'error':
                    $this->error($message);
                    break;
                case 'warning':
                    $this->warn($message);
                    break;
                default:
                    $this->line($message);
                    break;
            }
        }
    }

    // Only add this helper method for console output
    private function line(string $message)
    {
        if (app()->runningInConsole()) {
            echo $message.PHP_EOL;
        }
    }

    private function error(string $message)
    {
        if (app()->runningInConsole()) {
            echo "\033[31m".$message."\033[0m".PHP_EOL;
        }
    }

    private function warn(string $message)
    {
        if (app()->runningInConsole()) {
            echo "\033[33m".$message."\033[0m".PHP_EOL;
        }
    }

    public function detectAuthenticationRequirements(string $className, string $methodName, array $params): array
    {
        $authInfo = [
            'requiresAuth' => false,
            'authType' => null,
            'middleware' => [], // Add middleware information
        ];

        if (! $this->middlewareConfig['detect'] ?? true) {
            return $authInfo;
        }

        // 1. First check the actual route for middleware
        $route = $this->getRouteForMethod($className, $methodName);

        if ($route && isset($route['routeObject'])) {
            $middleware = $route['routeObject']->middleware();

            // Filter and store middleware
            $authInfo['middleware'] = $this->filterMiddleware($middleware);

            // Check for authentication middleware
            foreach ($middleware as $mw) {
                if ($this->isAuthMiddleware($mw)) {
                    $authInfo['requiresAuth'] = true;
                    $authInfo['authType'] = $this->mapMiddlewareToAuthType($mw);
                    break;
                }
            }
        }

        // 2. If no middleware found, check controller constructor
        if (! $authInfo['requiresAuth']) {
            $controllerMiddleware = $this->getControllerMiddleware($className);
            $authInfo['middleware'] = array_merge($authInfo['middleware'], $controllerMiddleware);

            foreach ($controllerMiddleware as $mw) {
                if ($this->isAuthMiddleware($mw)) {
                    $authInfo['requiresAuth'] = true;
                    $authInfo['authType'] = $this->mapMiddlewareToAuthType($mw);
                    break;
                }
            }
        }

        // 3. If still no auth found, check for authentication parameters
        if (! $authInfo['requiresAuth']) {
            $authParamIndicators = [
                'token', 'api_key', 'api-key', 'bearer', 'jwt', 'auth',
                'authorization', 'access_token', 'access-token',
            ];

            foreach ($params as $param) {
                $paramName = strtolower($param['name']);
                if (in_array($paramName, $authParamIndicators)) {
                    $authInfo['requiresAuth'] = true;
                    $authInfo['authType'] = 'bearer';
                    break;
                }
            }
        }

        return $authInfo;
    }

    /**
     * Filter middleware to exclude unwanted ones and format for display
     */
    protected function filterMiddleware(array $middleware): array
    {
        $excluded = $this->middlewareConfig['exclude'] ?? [];
        $filtered = [];

        foreach ($middleware as $mw) {
            // Skip excluded middleware
            if (in_array($mw, $excluded)) {
                continue;
            }

            // Skip middleware that contains excluded patterns
            $skip = false;
            foreach ($excluded as $pattern) {
                if (str_contains($mw, $pattern)) {
                    $skip = true;
                    break;
                }
            }

            if (! $skip) {
                $filtered[] = $mw;
            }
        }

        return array_unique($filtered);
    }

    /**
     * Check if middleware is an authentication middleware
     */
    protected function isAuthMiddleware(string $middleware): bool
    {
        $authMiddleware = $this->middlewareConfig['auth_middleware'] ?? [];

        foreach ($authMiddleware as $pattern) {
            if ($pattern === $middleware) {
                return true;
            }

            // Handle patterns with wildcards (e.g., 'auth:*')
            if (str_contains($pattern, '*') && str_contains($middleware, str_replace('*', '', $pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map middleware to authentication type
     */
    protected function mapMiddlewareToAuthType(string $middleware): string
    {
        $schemes = $this->middlewareConfig['security_schemes'] ?? [];

        foreach ($schemes as $pattern => $authType) {
            if ($pattern === $middleware) {
                return $authType;
            }

            // Handle patterns with parameters (e.g., 'auth:api')
            if (str_contains($pattern, ':') && str_contains($middleware, explode(':', $pattern)[0])) {
                return $authType;
            }
        }

        return 'bearer'; // Default
    }

    /**
     * Get middleware from controller constructor
     */
    protected function getControllerMiddleware(string $className): array
    {
        try {
            $reflection = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            if (! $constructor) {
                return [];
            }

            $middleware = [];
            $source = file($constructor->getFileName());
            $start = $constructor->getStartLine();
            $end = $constructor->getEndLine();

            $constructorCode = implode('', array_slice($source, $start, $end - $start));

            // Look for middleware declarations
            if (preg_match_all('/\$this->middleware\([\'"]([^\'"]+)[\'"]\)/', $constructorCode, $matches)) {
                $middleware = array_merge($middleware, $matches[1]);
            }

            if (preg_match_all('/middleware\([\'"]([^\'"]+)[\'"]\)/', $constructorCode, $matches)) {
                $middleware = array_merge($middleware, $matches[1]);
            }

            return $this->filterMiddleware($middleware);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function mergeParametersWithoutDuplicates(array $routeParams, array $formRequestParams): array
    {
        $mergedParams = [];
        $seenNames = [];

        // First add route parameters
        foreach ($routeParams as $param) {
            if (! in_array($param['name'], $seenNames)) {
                $mergedParams[] = $param;
                $seenNames[] = $param['name'];
            }
        }

        // Then add form request parameters (they take precedence for type/description)
        foreach ($formRequestParams as $param) {
            $existingIndex = array_search($param['name'], array_column($mergedParams, 'name'));

            if ($existingIndex !== false) {
                // Update existing parameter with form request info
                $mergedParams[$existingIndex] = array_merge($mergedParams[$existingIndex], [
                    'type' => $param['type'] ?? $mergedParams[$existingIndex]['type'],
                    'description' => $param['description'] ?? $mergedParams[$existingIndex]['description'],
                    'required' => $param['required'] ?? $mergedParams[$existingIndex]['required'],
                    'enum' => $param['enum'] ?? $mergedParams[$existingIndex]['enum'],
                ]);
            } else {
                // Add new parameter
                if (! in_array($param['name'], $seenNames)) {
                    $mergedParams[] = $param;
                    $seenNames[] = $param['name'];
                }
            }
        }

        return $mergedParams;
    }

    public function getRouteForMethod(string $className, string $methodName): ?array
    {
        foreach ($this->router->getRoutes() as $route) {
            $action = $route->getAction();
            if (isset($action['controller']) && $action['controller'] === "$className@$methodName") {
                return [
                    'uri' => '/'.$route->uri(),
                    'methods' => $route->methods(),
                    'routeObject' => $route, // Return the actual route object for middleware inspection
                ];
            }
        }

        return null;
    }

    public function getRouteParameters(string $uri): array
    {
        $params = [];
        preg_match_all('/{(\w+)}/', $uri, $matches);
        foreach ($matches[1] ?? [] as $paramName) {
            $params[] = [
                'name' => $paramName,
                'type' => 'string',
                'required' => true,
                'description' => 'Route parameter',
            ];
        }

        return $params;
    }

    public function getFormRequestParameters1(ReflectionMethod $method): array
    {
        $params = [];
        $seenNames = [];

        // First, check for FormRequest parameters
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            if ($type && class_exists($type->getName()) && is_subclass_of($type->getName(), FormRequest::class)) {
                $formRequestClass = $type->getName();
                $formRequest = new $formRequestClass;
                $rules = $formRequest->rules();

                foreach ($rules as $field => $rule) {
                    if (in_array($field, $seenNames)) {
                        continue; // Skip duplicates
                    }

                    $ruleArray = is_array($rule) ? $rule : explode('|', $rule);

                    $params[] = [
                        'name' => $field,
                        'type' => $this->getTypeFromRules($ruleArray),
                        'required' => ! in_array('nullable', $ruleArray) && ! $this->containsSometimes($rule),
                        'description' => $this->getDescriptionFromRules($ruleArray),
                        'default' => null,
                        'enum' => $this->getEnumFromRules($ruleArray),
                    ];

                    $seenNames[] = $field;
                }

                return $params; // Return early if FormRequest found
            }
        }

        // If no FormRequest, analyze method body for validation
        $methodBodyParams = $this->getParametersFromMethodBody($method);
        if (! empty($methodBodyParams)) {
            return $methodBodyParams;
        }

        // If no validation in method body, check for model binding
        $modelParams = $this->getParametersFromModelBinding($method);
        if (! empty($modelParams)) {
            return $modelParams;
        }

        // Fallback to regular method parameters
        return $this->getParametersFromMethodSignature($method);
    }

    public function getFormRequestParameters(ReflectionMethod $method): array
    {
        $params = [];
        $seenNames = [];

        // First, check for FormRequest parameters
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            if ($type && class_exists($type->getName()) && is_subclass_of($type->getName(), FormRequest::class)) {
                $formRequestClass = $type->getName();

                try {
                    // Try to create FormRequest instance safely
                    $formRequest = app()->make($formRequestClass);

                    // Use reflection to call rules() without triggering constructor logic
                    $reflection = new ReflectionClass($formRequestClass);
                    $rulesMethod = $reflection->getMethod('rules');

                    // Get rules without executing potentially problematic constructor code
                    $rules = $rulesMethod->invoke($formRequest);

                    foreach ($rules as $field => $rule) {
                        if (in_array($field, $seenNames)) {
                            continue; // Skip duplicates
                        }

                        $ruleArray = is_array($rule) ? $rule : explode('|', $rule);

                        $params[] = [
                            'name' => $field,
                            'type' => $this->getTypeFromRules($ruleArray),
                            'required' => ! in_array('nullable', $ruleArray) && ! $this->containsSometimes($rule),
                            'description' => $this->getDescriptionFromRules($ruleArray),
                            'default' => null,
                            'enum' => $this->getEnumFromRules($ruleArray),
                        ];

                        $seenNames[] = $field;
                    }

                    return $params; // Return early if FormRequest found
                } catch (\Exception $e) {
                    // If FormRequest instantiation fails, fall back to alternative methods
                    continue;
                }
            }
        }

        // If no FormRequest, analyze method body for validation
        $methodBodyParams = $this->getParametersFromMethodBody($method);
        if (! empty($methodBodyParams)) {
            return $methodBodyParams;
        }

        // If no validation in method body, check for model binding
        $modelParams = $this->getParametersFromModelBinding($method);
        if (! empty($modelParams)) {
            return $modelParams;
        }

        // Fallback to regular method parameters
        return $this->getParametersFromMethodSignature($method);
    }

    // Alternative method to get rules from FormRequest without instantiation
    protected function getRulesFromFormRequest(string $formRequestClass): array
    {
        try {
            // Use reflection to analyze the rules method
            $reflection = new ReflectionClass($formRequestClass);

            if (! $reflection->hasMethod('rules')) {
                return [];
            }

            $rulesMethod = $reflection->getMethod('rules');
            $methodSource = file($rulesMethod->getFileName());
            $startLine = $rulesMethod->getStartLine();
            $endLine = $rulesMethod->getEndLine();

            // Extract method body
            $methodBody = implode('', array_slice($methodSource, $startLine, $endLine - $startLine));

            // Simple pattern matching for common rule definitions
            if (preg_match('/return\s*\[(.*?)\]\s*;/s', $methodBody, $matches)) {
                $rulesString = $matches[1];

                // Parse simple array syntax (this is a basic implementation)
                $rules = [];
                if (preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*([^,]+),?/s', $rulesString, $ruleMatches)) {
                    foreach ($ruleMatches[1] as $index => $field) {
                        $rules[$field] = trim($ruleMatches[2][$index]);
                    }
                }

                return $rules;
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if a rule contains 'sometimes' - FIXED VERSION
     * This replaces the problematic str_contains() call
     */
    protected function containsSometimes($rule): bool
    {
        if (is_string($rule)) {
            return str_contains($rule, 'sometimes');
        } elseif (is_array($rule)) {
            return in_array('sometimes', $rule);
        }

        return false;
    }

    protected function getParametersFromMethodBody(ReflectionMethod $method): array
    {
        $params = [];
        $seenNames = [];
        $methodSource = file($method->getFileName());
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        // Extract method body
        $methodBody = implode('', array_slice($methodSource, $startLine, $endLine - $startLine));

        // Look for validation patterns
        $patterns = [
            '/\$request->validate\(\[(.*?)\]\)/s',
            '/Validator::make\(.*?\[(.*?)\].*?\)/s',
            '/\$this->validate\(.*?\[(.*?)\].*?\)/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $methodBody, $matches)) {
                foreach ($matches[1] as $rulesBlock) {
                    $newParams = $this->parseValidationRules($rulesBlock);
                    foreach ($newParams as $param) {
                        if (! in_array($param['name'], $seenNames)) {
                            $params[] = $param;
                            $seenNames[] = $param['name'];
                        }
                    }
                }
            }
        }

        return $params;
    }

    protected function parseValidationRules(string $rulesBlock): array
    {
        $params = [];

        // Parse rules array: 'field' => 'required|string|max:255'
        if (preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $rulesBlock, $ruleMatches)) {
            foreach ($ruleMatches[1] as $index => $field) {
                $ruleString = $ruleMatches[2][$index];
                $ruleArray = explode('|', $ruleString);

                $params[] = [
                    'name' => $field,
                    'type' => $this->getTypeFromRules($ruleArray),
                    'required' => ! in_array('nullable', $ruleArray) && ! in_array('sometimes', $ruleArray),
                    'description' => $this->getDescriptionFromRules($ruleArray),
                    'default' => null,
                    'enum' => $this->getEnumFromRules($ruleArray),
                ];
            }
        }

        // Parse array syntax: ['field' => ['required', 'string']]
        if (preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*\[(.*?)\]/s', $rulesBlock, $arrayRuleMatches)) {
            foreach ($arrayRuleMatches[1] as $index => $field) {
                $rulesString = $arrayRuleMatches[2][$index];
                $ruleArray = array_map('trim', explode(',', preg_replace('/[\'"]/', '', $rulesString)));

                $params[] = [
                    'name' => $field,
                    'type' => $this->getTypeFromRules($ruleArray),
                    'required' => ! in_array('nullable', $ruleArray) && ! in_array('sometimes', $ruleArray),
                    'description' => $this->getDescriptionFromRules($ruleArray),
                    'default' => null,
                    'enum' => $this->getEnumFromRules($ruleArray),
                ];
            }
        }

        return $params;
    }

    protected function getParametersFromModelBinding(ReflectionMethod $method): array
    {
        $params = [];

        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $paramName = $param->getName();

            // Skip common parameter names
            if (in_array($paramName, ['request', 'req', 'input', 'data', 'id'])) {
                continue;
            }

            if ($type && class_exists($type->getName())) {
                $className = $type->getName();

                // Check if it's an Eloquent model
                if (is_subclass_of($className, \Illuminate\Database\Eloquent\Model::class)) {
                    try {
                        $model = new $className;
                        $fillable = $model->getFillable();
                        $casts = $model->getCasts();

                        foreach ($fillable as $field) {
                            $castType = $casts[$field] ?? 'string';
                            $apiType = $this->mapCastToApiType($castType);

                            $params[] = [
                                'name' => $field,
                                'type' => $apiType,
                                'required' => false, // Assume optional since we don't know validation
                                'description' => 'Model field',
                                'default' => null,
                                'enum' => [],
                            ];
                        }
                    } catch (\Exception $e) {
                        // If model instantiation fails, just add the parameter itself
                        $params[] = [
                            'name' => $paramName,
                            'type' => $this->getTypeName($type),
                            'required' => ! $param->isDefaultValueAvailable(),
                            'description' => 'Model parameter',
                            'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                            'enum' => [],
                        ];
                    }
                } else {
                    // Regular class parameter
                    $params[] = [
                        'name' => $paramName,
                        'type' => $this->getTypeName($type),
                        'required' => ! $param->isDefaultValueAvailable(),
                        'description' => 'Parameter',
                        'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                        'enum' => [],
                    ];
                }
            } else {
                // Basic parameter without type hint
                $params[] = [
                    'name' => $paramName,
                    'type' => 'mixed',
                    'required' => ! $param->isDefaultValueAvailable(),
                    'description' => 'Parameter',
                    'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                    'enum' => [],
                ];
            }
        }

        return $params;
    }

    protected function getParametersFromMethodSignature(ReflectionMethod $method): array
    {
        $params = [];

        foreach ($method->getParameters() as $param) {
            $paramName = $param->getName();
            $type = $param->getType();

            // Skip common parameter names that are likely not API parameters
            if (in_array($paramName, ['request', 'req', 'input', 'data'])) {
                continue;
            }

            $params[] = [
                'name' => $paramName,
                'type' => $type ? $this->getTypeName($type) : 'mixed',
                'required' => ! $param->isDefaultValueAvailable(),
                'description' => $param->isDefaultValueAvailable()
                    ? 'Optional. Default: '.json_encode($param->getDefaultValue())
                    : 'Required',
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                'enum' => [],
            ];
        }

        return $params;
    }

    protected function mapCastToApiType(string $castType): string
    {
        $castMap = [
            'int' => 'integer',
            'integer' => 'integer',
            'real' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'string' => 'string',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'object' => 'object',
            'array' => 'array',
            'json' => 'array',
            'collection' => 'array',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
        ];

        return $castMap[$castType] || 'string';
    }

    protected function getTypeFromRules(array $rules): string
    {
        if (in_array('integer', $rules) || in_array('numeric', $rules)) {
            return 'integer';
        }
        if (in_array('boolean', $rules)) {
            return 'boolean';
        }
        if (in_array('array', $rules)) {
            return 'array';
        }
        if (in_array('file', $rules) || in_array('image', $rules)) {
            return 'file';
        }
        if (in_array('date', $rules) || in_array('datetime', $rules)) {
            return 'date';
        }
        if (in_array('email', $rules)) {
            return 'email';
        }
        if (in_array('url', $rules)) {
            return 'url';
        }

        return 'string'; // Default to string
    }

    protected function getDescriptionFromRules(array $rules): string
    {
        $descriptions = [];

        if (in_array('required', $rules)) {
            $descriptions[] = 'Required';
        }
        if (in_array('nullable', $rules)) {
            $descriptions[] = 'Optional';
        }
        if (in_array('email', $rules)) {
            $descriptions[] = 'Must be a valid email address';
        }
        if (in_array('url', $rules)) {
            $descriptions[] = 'Must be a valid URL';
        }
        if (in_array('numeric', $rules)) {
            $descriptions[] = 'Must be numeric';
        }
        if (in_array('integer', $rules)) {
            $descriptions[] = 'Must be an integer';
        }
        if (in_array('boolean', $rules)) {
            $descriptions[] = 'Must be true or false';
        }
        if (in_array('array', $rules)) {
            $descriptions[] = 'Must be an array';
        }
        if (in_array('file', $rules)) {
            $descriptions[] = 'Must be a file';
        }
        if (in_array('image', $rules)) {
            $descriptions[] = 'Must be an image file';
        }
        if (in_array('date', $rules)) {
            $descriptions[] = 'Must be a valid date';
        }

        // Handle Password rule object specifically
        foreach ($rules as $rule) {
            // Check if it's a Password rule object
            if ($rule instanceof Password) {
                $descriptions[] = 'Must be a secure password';

                continue;
            }

            // Ensure we're working with a string
            if (! is_string($rule)) {
                continue;
            }

            if (Str::startsWith($rule, 'min:')) {
                $value = Str::after($rule, 'min:');
                $descriptions[] = "Minimum: {$value}";
            }
            if (Str::startsWith($rule, 'max:')) {
                $value = Str::after($rule, 'max:');
                $descriptions[] = "Maximum: {$value}";
            }
            if (Str::startsWith($rule, 'size:')) {
                $value = Str::after($rule, 'size:');
                $descriptions[] = "Size must be: {$value}";
            }
            if (Str::startsWith($rule, 'between:')) {
                $values = Str::after($rule, 'between:');
                $descriptions[] = "Must be between: {$values}";
            }
            if (Str::startsWith($rule, 'in:')) {
                $values = Str::after($rule, 'in:');
                $descriptions[] = "Allowed values: {$values}";
            }
        }

        return implode('. ', $descriptions);
    }

    protected function getEnumFromRules(array $rules): array
    {
        $enums = [];

        foreach ($rules as $rule) {
            // Skip non-string rules
            if (! is_string($rule)) {
                continue;
            }

            if (Str::startsWith($rule, 'in:')) {
                $values = Str::after($rule, 'in:');
                $enums = array_merge($enums, explode(',', $values));
            }
        }

        return array_unique($enums);
    }

    public function buildAnnotation(
        string $methodName,
        string $className,
        string $uri,
        string $httpMethod,
        array $params = [],
        array $authInfo = [],
        array $additionalInfo = []
    ): string {
        $group = str_replace('Controller', '', class_basename($className));
        $description = ucfirst($methodName)." {$group}";

        // Parameter lines
        $paramLines = '';
        foreach ($params as $p) {
            $type = $p['type'] ?? 'mixed';
            $required = isset($p['required']) ? (bool) $p['required'] : false; // Force boolean
            $desc = $p['description'] ?? '';
            $enum = isset($p['enum']) && is_array($p['enum']) && count($p['enum'])
                ? ' Enum: '.implode(', ', $p['enum'])
                : '';
            $default = isset($p['default']) ? ' Default: '.$p['default'] : '';

            // Only add description, don't append "Optional" or "Required" here
            $paramLines .= " * @apiParam {{$type}} {$p['name']} {$desc}{$enum}{$default}\n";
        }

        // Header lines (if any headers are detected)
        $headerLines = '';
        $headers = $additionalInfo['headers'] ?? [];
        foreach ($headers as $header) {
            $headerType = $header['type'] ?? 'string';
            $headerDesc = $header['description'] ?? '';
            $headerRequired = $header['required'] ?? false;
            $headerReqText = $headerRequired ? 'Required' : 'Optional';
            $headerLines .= " * @apiHeader {{$headerType}} {$header['name']} {$headerReqText}. {$headerDesc}\n";
        }

        // Authentication annotation
        $authLine = '';
        if ($authInfo['requiresAuth']) {
            $authType = $authInfo['authType'] ?? 'bearer';
            $authLine = " * @apiAuth {$authType}\n";
        }

        // Middleware information
        $middlewareLines = '';
        if (! empty($authInfo['middleware'])) {
            foreach ($authInfo['middleware'] as $middleware) {
                $middlewareLines .= " * @apiMiddleware {$middleware}\n";
            }
        }

        // Permission/role information
        $permissionLines = '';
        if (! empty($authInfo['permissions'])) {
            $permissionLines = ' * @apiPermission '.implode(', ', $authInfo['permissions'])."\n";
        }

        // Success response examples
        $successExample = '';
        if (! empty($additionalInfo['success_example'])) {
            $successExample = " * @apiSuccessExample {json} Success-Response:\n".
                             ' *     '.str_replace("\n", "\n *     ", $additionalInfo['success_example'])."\n";
        }

        // Error response examples
        $errorExample = '';
        if (! empty($additionalInfo['error_example'])) {
            $errorExample = " * @apiErrorExample {json} Error-Response:\n".
                           ' *     '.str_replace("\n", "\n *     ", $additionalInfo['error_example'])."\n";
        }

        // Error definitions
        $errorLines = '';
        $errors = $additionalInfo['errors'] ?? [];
        foreach ($errors as $error) {
            $errorCode = $error['code'] ?? 'Error';
            $errorDesc = $error['description'] ?? '';
            $errorLines .= " * @apiError {$errorCode} {$errorDesc}\n";
        }

        // Version information
        $versionLine = '';
        if (! empty($additionalInfo['version'])) {
            $versionLine = " * @apiVersion {$additionalInfo['version']}\n";
        }

        // Deprecation notice
        $deprecatedLine = '';
        if (! empty($additionalInfo['deprecated'])) {
            $deprecatedLine = " * @apiDeprecated {$additionalInfo['deprecated']}\n";
        }

        // Sample usage
        $sampleLine = '';
        if (! empty($additionalInfo['sample'])) {
            $sampleLine = " * @apiSampleRequest {$additionalInfo['sample']}\n";
        }

        // Query parameters (for GET requests)
        $queryParamLines = '';
        if (strtoupper($httpMethod) === 'GET' && ! empty($params)) {
            foreach ($params as $p) {
                if (str_contains($uri, '{'.$p['name'].'}')) {
                    continue;
                } // Skip route params

                $type = $p['type'] ?? 'string';
                $required = $p['required'] ?? false;
                $desc = $p['description'] ?? '';
                $optional = $required ? '' : 'Optional. ';
                $queryParamLines .= " * @apiQuery {{$type}} {$p['name']} {$optional}{$desc}\n";
            }
        }

        // Body parameters (for POST/PUT/PATCH requests)
        $bodyParamLines = '';
        if (in_array(strtoupper($httpMethod), ['POST', 'PUT', 'PATCH']) && ! empty($params)) {
            foreach ($params as $p) {
                if (str_contains($uri, '{'.$p['name'].'}')) {
                    continue;
                } // Skip route params

                $type = $p['type'] ?? 'string';
                $required = $p['required'] ?? false;
                $desc = $p['description'] ?? '';
                $optional = $required ? '' : 'Optional. ';
                $bodyParamLines .= " * @apiBody {{$type}} {$p['name']} {$optional}{$desc}\n";
            }
        }

        // Success response fields
        $successFieldLines = '';
        $successFields = $additionalInfo['success_fields'] ?? [];
        foreach ($successFields as $field) {
            $fieldType = $field['type'] ?? 'string';
            $fieldDesc = $field['description'] ?? '';
            $successFieldLines .= " * @apiSuccess {{$fieldType}} {$field['name']} {$fieldDesc}\n";
        }

        if (empty($params)) {
            $paramLines = " *\n";
        }

        return <<<EOT
/**
 * @api {{$httpMethod}} {$uri} {$description}
 * @apiName {$methodName}
 * @apiGroup {$group}
{$versionLine}{$deprecatedLine}{$authLine}{$permissionLines}{$middlewareLines}{$headerLines}{$paramLines}{$queryParamLines}{$bodyParamLines}{$successFieldLines}{$errorLines}{$successExample}{$errorExample}{$sampleLine} * @apiSuccess {Object} data Response data
 * @apiSuccess {Boolean} success Operation status
 * @apiSuccess {String} message Response message
 */
EOT;
    }

    /**
     * Detect additional information from the method
     */
    protected function detectAdditionalInfo(ReflectionMethod $method, array $params): array
    {
        $additionalInfo = [
            'headers' => $this->detectHeaders($method),
            'errors' => $this->detectErrorResponses($method),
            'success_example' => $this->generateSuccessExample($params),
            'error_example' => $this->generateErrorExample(),
            'success_fields' => $this->detectSuccessFields($method),
            'version' => $this->detectApiVersion($method),
        ];

        return $additionalInfo;
    }

    /**
     * Detect common headers from method analysis
     */
    protected function detectHeaders(ReflectionMethod $method): array
    {
        $headers = [];
        $methodBody = $this->getMethodBody($method);

        // Detect Content-Type headers
        if (preg_match('/header\([\'"]Content-Type[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)/i', $methodBody, $matches)) {
            $headers[] = [
                'name' => 'Content-Type',
                'type' => 'string',
                'required' => true,
                'description' => 'Must be '.$matches[1],
            ];
        }

        // Detect Accept headers
        if (preg_match('/header\([\'"]Accept[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\)/i', $methodBody, $matches)) {
            $headers[] = [
                'name' => 'Accept',
                'type' => 'string',
                'required' => false,
                'description' => 'Should be '.$matches[1],
            ];
        }

        // Add common API headers
        $headers[] = [
            'name' => 'Authorization',
            'type' => 'string',
            'required' => false,
            'description' => 'Bearer token for authentication',
        ];

        return $headers;
    }

    /**
     * Detect common error responses
     */
    protected function detectErrorResponses(ReflectionMethod $method): array
    {
        $errors = [];
        $methodBody = $this->getMethodBody($method);

        // Detect common error patterns
        if (preg_match('/abort\((\d+)/', $methodBody, $matches)) {
            $statusCode = $matches[1];
            $errors[] = [
                'code' => $statusCode,
                'description' => $this->getHttpStatusText($statusCode),
            ];
        }

        // Common error codes for APIs
        $commonErrors = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ];

        foreach ($commonErrors as $code => $description) {
            if (str_contains($methodBody, (string) $code)) {
                $errors[] = ['code' => $code, 'description' => $description];
            }
        }

        return $errors;
    }

    /**
     * Generate a success response example
     */
    protected function generateSuccessExample(array $params): string
    {
        $example = ['success' => true, 'message' => 'Operation successful', 'data' => []];

        foreach ($params as $param) {
            if (! str_contains($param['name'], 'password') && ! str_contains($param['name'], 'secret')) {
                $example['data'][$param['name']] = $this->generateExampleValue($param['type'] ?? 'string');
            }
        }

        return json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate an error response example
     */
    protected function generateErrorExample(): string
    {
        $example = [
            'success' => false,
            'message' => 'Error message describing what went wrong',
            'errors' => [
                'field_name' => ['Specific error message for this field'],
            ],
        ];

        return json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate example value based on type
     */
    protected function generateExampleValue(string $type): mixed
    {
        $examples = [
            'string' => 'example_string',
            'integer' => 123,
            'number' => 123.45,
            'boolean' => true,
            'array' => ['item1', 'item2'],
            'object' => ['key' => 'value'],
            'email' => 'user@example.com',
            'date' => '2023-12-31',
            'datetime' => '2023-12-31T23:59:59Z',
        ];

        return $examples[$type] ?? $examples['string'];
    }

    /**
     * Detect success response fields
     */
    protected function detectSuccessFields(ReflectionMethod $method): array
    {
        $fields = [];
        $methodBody = $this->getMethodBody($method);

        // Look for common response patterns
        if (preg_match('/response\(\)->json\(\[(.*?)\]\)/s', $methodBody, $matches)) {
            // Parse response array to detect fields
        }

        // Add common success fields
        $fields[] = ['name' => 'success', 'type' => 'boolean', 'description' => 'Operation status'];
        $fields[] = ['name' => 'message', 'type' => 'string', 'description' => 'Response message'];
        $fields[] = ['name' => 'data', 'type' => 'object', 'description' => 'Response data'];

        return $fields;
    }

    /**
     * Detect API version from class or method docblock
     */
    protected function detectApiVersion(ReflectionMethod $method): ?string
    {
        $class = $method->getDeclaringClass();
        $classDocComment = $class->getDocComment();
        $methodDocComment = $method->getDocComment();

        // Check for @apiVersion in method docblock
        if ($methodDocComment && preg_match('/@apiVersion\s+([^\s]+)/', $methodDocComment, $matches)) {
            return $matches[1];
        }

        // Check for @apiVersion in class docblock
        if ($classDocComment && preg_match('/@apiVersion\s+([^\s]+)/', $classDocComment, $matches)) {
            return $matches[1];
        }

        return config('api-doc-generator.default_version', '1.0.0');
    }

    /**
     * Get HTTP status text from code
     */
    protected function getHttpStatusText(int $code): string
    {
        $statusTexts = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ];

        return $statusTexts[$code] ?? 'Error';
    }

    /**
     * Get method body as string
     */
    protected function getMethodBody(ReflectionMethod $method): string
    {
        try {
            $filename = $method->getFileName();
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();

            $source = file($filename);
            $body = implode('', array_slice($source, $startLine, $endLine - $startLine));

            return $body;
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getFullyQualifiedClassName(string $filePath): ?string
    {
        $content = File::get($filePath);
        if (! preg_match('/namespace\s+(.+?);/', $content, $nsMatch)) {
            return null;
        }
        if (! preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        return $nsMatch[1].'\\'.$classMatch[1];
    }

    protected function getTypeName($type): string
    {
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        return 'mixed';
    }
}
