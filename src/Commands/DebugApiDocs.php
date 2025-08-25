<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class DebugApiDocs extends Command
{
    protected $signature = 'api:docs:debug-detailed';

    protected $description = 'Detailed debug of API documentation generation process';

    protected $router;

    public function __construct(Router $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    public function handle()
    {
        $this->info('🔍 Detailed API Documentation Debug');
        $this->line(str_repeat('=', 60));

        $this->debugRoutes();
        $this->debugControllers();
        $this->debugConfig();
    }

    protected function debugRoutes()
    {
        $this->info("\n🛣️  ROUTES ANALYSIS");
        $this->line(str_repeat('-', 40));

        $routesByController = [];
        $invalidRoutes = [];

        foreach ($this->router->getRoutes() as $route) {
            $action = $route->getAction();

            if (isset($action['controller']) && is_string($action['controller'])) {
                $controllerAction = $action['controller'];

                // Check if it's in controller@method format
                if (str_contains($controllerAction, '@')) {
                    $parts = explode('@', $controllerAction);
                    if (count($parts) === 2) {
                        [$controller, $method] = $parts;
                        $routesByController[$controller][] = [
                            'method' => $method,
                            'uri' => $route->uri(),
                            'httpMethods' => $route->methods(),
                            'action' => $controllerAction,
                        ];
                    } else {
                        $invalidRoutes[] = [
                            'uri' => $route->uri(),
                            'action' => $controllerAction,
                            'reason' => 'Invalid controller@method format',
                        ];
                    }
                } else {
                    $invalidRoutes[] = [
                        'uri' => $route->uri(),
                        'action' => $controllerAction,
                        'reason' => 'Not in controller@method format',
                    ];
                }
            } else {
                $invalidRoutes[] = [
                    'uri' => $route->uri(),
                    'action' => isset($action['controller']) ? json_encode($action['controller']) : 'No controller',
                    'reason' => 'No valid controller action',
                ];
            }
        }

        $this->info('✅ Valid routes grouped by controller:');
        foreach ($routesByController as $controller => $routes) {
            $this->line("📦 {$controller}");
            foreach ($routes as $route) {
                $this->line('   ➤ '.implode('|', $route['httpMethods'])." {$route['uri']} -> {$route['method']}()");
                $this->line("     Action: {$route['action']}");
            }
            $this->line('');
        }

        if (! empty($invalidRoutes)) {
            $this->info("❌ Invalid routes (won't be scanned for documentation):");
            foreach ($invalidRoutes as $invalidRoute) {
                $this->line('   ⚠️  '.implode('|', $invalidRoute['httpMethods'] ?? ['ANY'])." {$invalidRoute['uri']}");
                $this->line("     Action: {$invalidRoute['action']}");
                $this->line("     Reason: {$invalidRoute['reason']}");
                $this->line('');
            }
        }
    }

    protected function debugControllers()
    {
        $this->info("\n📁 CONTROLLERS ANALYSIS");
        $this->line(str_repeat('-', 40));

        $controllerPaths = config('api-doc-generator.controller_paths', [app_path('Http/Controllers')]);

        foreach ($controllerPaths as $path) {
            if (! File::exists($path)) {
                $this->error("Path does not exist: {$path}");

                continue;
            }

            $files = File::allFiles($path);
            $this->info("Scanning: {$path} (".count($files).' files)');

            foreach ($files as $file) {
                $className = $this->getClassNameFromPath($path, $file);
                $this->line("   📄 {$file->getFilename()} -> {$className}");

                try {
                    $reflection = new ReflectionClass($className);
                    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

                    $actionMethods = [];
                    foreach ($methods as $method) {
                        if ($this->isControllerAction($method)) {
                            $actionMethods[] = $method->getName();
                        }
                    }

                    if (! empty($actionMethods)) {
                        $this->line('      ⚡ Action methods: '.implode(', ', $actionMethods));
                    } else {
                        $this->line('      ℹ️  No action methods found');
                    }
                } catch (\Exception $e) {
                    $this->line('      ❌ Cannot reflect: '.$e->getMessage());
                }
            }
        }
    }

    protected function debugConfig()
    {
        $this->info("\n⚙️  CONFIGURATION");
        $this->line(str_repeat('-', 40));

        $config = config('api-doc-generator');

        $this->info('Current configuration:');
        $this->line('Controller Paths:');
        foreach ($config['controller_paths'] ?? [] as $path) {
            $this->line("   - {$path}");
        }

        $this->line('');
        $this->line('Scanning Options:');
        $this->line('   - Routes: '.($config['scan_routes'] ?? false ? '✅ Enabled' : '❌ Disabled'));
        $this->line('   - Requests: '.($config['scan_requests'] ?? false ? '✅ Enabled' : '❌ Disabled'));
        $this->line('   - Resources: '.($config['scan_resources'] ?? false ? '✅ Enabled' : '❌ Disabled'));

        $this->line('');
        $this->line('Output Directory: '.($config['output_dir'] ?? 'Not set'));
    }

    protected function getClassNameFromPath($basePath, $file)
    {
        $relativePath = str_replace([$basePath, '.php'], '', $file->getRealPath());
        $className = str_replace('/', '\\', trim($relativePath, '/'));

        $appNamespace = app()->getNamespace();

        if (str_contains($basePath, 'Http/Controllers/API')) {
            return $appNamespace.'Http\\Controllers\\API\\'.$className;
        }

        return $appNamespace.'Http\\Controllers\\'.$className;
    }

    protected function isControllerAction($method)
    {
        $nonActionMethods = ['middleware', 'validator', 'validate', 'authorize', 'getMiddleware', '__construct'];

        return ! in_array($method->getName(), $nonActionMethods) &&
               $method->isPublic() &&
               ! $method->isStatic();
    }
}
