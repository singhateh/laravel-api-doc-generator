<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Router;

class DebugRoutes extends Command
{
    protected $signature = 'api:docs:debug';

    protected $description = 'Debug route to controller mapping for API documentation';

    public function handle(Router $router)
    {
        $this->info('🔄 Scanning routes for API documentation...');

        $routes = $router->getRoutes();
        $controllerPaths = config('api-doc-generator.controller_paths', [app_path('Http/Controllers')]);

        $this->info("\n📋 Available routes:");
        $this->line(str_repeat('-', 80));

        $validRoutes = 0;
        $invalidRoutes = 0;

        foreach ($routes as $route) {
            $action = $route->getAction();
            if (isset($action['controller']) && is_string($action['controller'])) {
                $controllerAction = $action['controller'];

                if (str_contains($controllerAction, '@') && count(explode('@', $controllerAction)) === 2) {
                    $validRoutes++;
                    $this->line('✅ '.implode('|', $route->methods()).' '.$route->uri());
                    $this->line('   ➤ Controller: '.$controllerAction);
                } else {
                    $invalidRoutes++;
                    $this->line('❌ '.implode('|', $route->methods()).' '.$route->uri());
                    $this->line('   ➤ Invalid format: '.$controllerAction);
                }
                $this->line('');
            }
        }

        $this->info('📊 Route Statistics:');
        $this->line("   - Valid routes: {$validRoutes}");
        $this->line("   - Invalid routes: {$invalidRoutes}");
        $this->line('   - Total routes: '.($validRoutes + $invalidRoutes));

        $this->info("\n🔍 Controller paths being scanned:");
        $this->line(str_repeat('-', 80));

        foreach ($controllerPaths as $path) {
            $exists = file_exists($path) ? '✅ EXISTS' : '❌ MISSING';
            $this->line("{$exists}: {$path}");
        }
    }
}
