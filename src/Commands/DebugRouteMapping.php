<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;

class DebugRouteMapping extends Command
{
    protected $signature = 'api:docs:debug-mapping';

    protected $description = 'Debug route to controller method mapping';

    protected $router;

    public function __construct(Router $router)
    {
        parent::__construct();
        $this->router = $router;
    }

    public function handle()
    {
        $this->info('🔍 Debugging Route to Controller Mapping');
        $this->line(str_repeat('=', 60));

        // Get routes from router
        $routesByController = $this->getRoutesByController();

        $this->info("\n🛣️  Routes grouped by controller:");
        $this->line(str_repeat('-', 60));

        foreach ($routesByController as $controller => $routes) {
            $this->line("📦 Controller: {$controller}");
            foreach ($routes as $routeInfo) {
                $this->line("   ➤ Method: {$routeInfo['method']}");
                $this->line('   ➤ URI: '.implode('|', $routeInfo['route']->methods())." {$routeInfo['route']->uri()}");
            }
            $this->line('');
        }

        // Check if our test controller is in the routes
        $testController = 'App\Http\Controllers\API\APITESTMemberController';
        if (isset($routesByController[$testController])) {
            $this->info('✅ APITESTMemberController found in routes!');
            $this->line('Methods found: '.implode(', ', array_column($routesByController[$testController], 'method')));
        } else {
            $this->error('❌ APITESTMemberController NOT found in routes!');
            $this->line('Available controllers: '.implode(', ', array_keys($routesByController)));
        }

        // Now check file scanning
        $this->info("\n📁 File scanning check:");
        $this->line(str_repeat('-', 60));

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

                if ($className === $testController) {
                    $this->info('   ✅ APITESTMemberController found in file scanning!');
                }
            }
        }
    }

    protected function getRoutesByController(): array
    {
        $routesByController = [];

        foreach ($this->router->getRoutes() as $route) {
            $action = $route->getAction();

            if (isset($action['controller']) && is_string($action['controller'])) {
                $controllerAction = $action['controller'];

                if (str_contains($controllerAction, '@')) {
                    $parts = explode('@', $controllerAction);
                    if (count($parts) === 2) {
                        [$controller, $method] = $parts;

                        // Normalize controller namespace
                        $controller = $this->normalizeControllerNamespace($controller);

                        $routesByController[$controller][] = [
                            'route' => $route,
                            'method' => $method,
                            'raw_action' => $controllerAction,
                        ];
                    }
                }
            }
        }

        return $routesByController;
    }

    protected function normalizeControllerNamespace(string $controller): string
    {
        // Remove any leading backslashes
        $controller = ltrim($controller, '\\');

        // If it's a relative namespace, make it fully qualified
        $appNamespace = app()->getNamespace();
        if (str_starts_with($controller, 'App\\') && $appNamespace !== 'App\\') {
            $controller = str_replace('App\\', $appNamespace, $controller);
        }

        return $controller;
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
}
