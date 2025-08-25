<?php

use Alagiesinghateh\LaravelApiDocGenerator\Http\Controllers\ApiDocumentationController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Merge default 'web' middleware with package/user-defined middleware
$defaultMiddleware = ['web'];
$customMiddleware = config('api-doc-generator.web_interface.middleware', []);
$middleware = array_unique(array_merge($defaultMiddleware, $customMiddleware));

Route::middleware($middleware)
    ->prefix(config('api-doc-generator.web_interface.route_prefix', 'api-docs'))
    ->name('api-docs.')
    ->group(function () {
        Route::get('/', [ApiDocumentationController::class, 'index'])->name('index');
        Route::get('/group/{group?}', [ApiDocumentationController::class, 'show'])->name('group');
        Route::get('/endpoint/{id}', [ApiDocumentationController::class, 'endpoint'])->name('endpoint');
        Route::get('/json', [ApiDocumentationController::class, 'json'])->name('json');
        Route::post('/test', [ApiDocumentationController::class, 'testEndpoint'])->name('test');

        Route::post('/generate', function () {
            Artisan::call('singhateh:generate', ['--dry-run' => true]);

            return redirect()->route('api-docs.index')
                ->with('success', 'API documentation generated successfully!');
        })->name('generate');
    });
