<?php

namespace Alagiesinghateh\LaravelApiDocGenerator;

use Alagiesinghateh\LaravelApiDocGenerator\Http\Middleware\ProtectApiDocs;
use Alagiesinghateh\LaravelApiDocGenerator\Services\ApiAnnotationGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ApiDocGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register middleware alias only if not overridden
        if (! isset($this->app['router']->getMiddleware()['protect.api.docs'])) {
            $this->app['router']->aliasMiddleware('protect.api.docs', ProtectApiDocs::class);
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/api-docs.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'api-doc-generator');
        $this->loadViewsFrom(__DIR__.'/../resources/views', '');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/api-doc-generator.php' => config_path('api-doc-generator.php'),
            ], 'config');

            // Auto-add env variables
            $this->addEnvDefaults([
                'API_DOCS_WEB_ENABLED' => 'false',
                'API_DOCS_ROUTE_PREFIX' => 'api-docs',
                'API_DOCS_TESTING_ENABLED' => 'true',
                'API_DOCS_PROTECTED' => 'true',
                'API_DOCS_USERNAME' => 'admin',
                'API_DOCS_PASSWORD' => 'password',
                'API_DOCS_ALLOW_PROD' => 'false',
                'API_DOCS_CACHE' => 'true',
                'CACHE_DRIVER' => 'file',
            ]);

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/api-doc-generator'),
            ], 'views');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/api-doc-generator'),
            ], 'public');

            $this->commands([
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\GenerateApiDocs::class,
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\DebugRoutes::class,
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\DebugApiDocs::class,
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\DebugRouteMapping::class,
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\ForceRegenerateAnnotations::class,
                \Alagiesinghateh\LaravelApiDocGenerator\Commands\RegenerateApiAnnotations::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api-doc-generator.php', 'api-doc-generator');

        $this->app->singleton(ApiAnnotationGenerator::class, fn ($app) => new ApiAnnotationGenerator($app->make(Router::class)));
        $this->app->singleton('api-doc-generator', fn ($app) => new ApiDocGenerator($app->make(Filesystem::class), $app->make(Router::class)));
        $this->app->bind(ApiDocGenerator::class, fn ($app) => new ApiDocGenerator($app->make(Filesystem::class), $app->make(Router::class)));

        $this->commands([\Alagiesinghateh\LaravelApiDocGenerator\Commands\GenerateApiDocs::class]);
    }

    protected function addEnvDefaults(array $defaults)
    {
        $envFile = base_path('.env');

        if (! file_exists($envFile)) {
            return;
        }

        $envContents = file_get_contents($envFile);

        foreach ($defaults as $key => $value) {
            if (! str_contains($envContents, $key.'=')) {
                file_put_contents($envFile, PHP_EOL.$key.'='.$value, FILE_APPEND);
            }
        }
    }
}
