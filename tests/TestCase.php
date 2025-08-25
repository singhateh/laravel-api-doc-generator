<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Tests;

use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ApiDocGeneratorServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'ApiDocGenerator' => \Alagiesinghateh\LaravelApiDocGenerator\Facades\ApiDocGenerator::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up your package configuration
        $app['config']->set('api-doc-generator', [
            'output_dir' => storage_path('app/api-docs'),
            'controller_paths' => [app_path('Http/Controllers')],
            'defaults' => [
                'method' => 'GET',
                'path' => '/',
                'name' => 'Untitled Endpoint',
                'group' => 'General',
            ],
        ]);
    }
}
