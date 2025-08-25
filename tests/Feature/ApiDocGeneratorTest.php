<?php

// tests/Feature/ApiDocGeneratorTest.php

use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->generator = new ApiDocGenerator(app('files'));
    Config::set('api-doc-generator.output_dir', storage_path('test-api-docs'));
});

afterEach(function () {
    // Clean up test files
    if (File::exists(storage_path('test-api-docs'))) {
        File::deleteDirectory(storage_path('test-api-docs'));
    }
});

test('it can parse doc blocks without file scanning', function () {
    // Test the parser directly without file system operations
    $docBlock = <<<'DOC'
/**
 * @api {get} /api/test Get test data
 * @apiName GetTest
 * @apiGroup Test
 * @apiDescription Get test data from the API
 * 
 * @apiParam {String} [page] Page number
 * 
 * @apiSuccess {Object[]} data Array of test data
 * 
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": []
 *     }
 */
DOC;

    // Use reflection to test the parsing directly
    $reflection = new ReflectionClass($this->generator);
    $method = $reflection->getMethod('parseDocBlock');
    $method->setAccessible(true);

    $result = $method->invoke($this->generator, $docBlock);

    expect($result)->not->toBeEmpty();
    expect($result['method'])->toBe('GET');
    expect($result['path'])->toBe('/api/test');
    expect($result['name'])->toBe('GetTest');
    expect($result['group'])->toBe('Test');
    expect($result['parameters'])->toHaveCount(1);
    expect($result['parameters'][0]['name'])->toBe('page');
    expect($result['parameters'][0]['required'])->toBeFalse();
});

test('it can generate documentation from mock reflection', function () {
    // Create a mock reflection class
    $mockReflection = Mockery::mock(ReflectionClass::class);

    // Create mock methods with API annotations
    $mockMethod1 = Mockery::mock();
    $mockMethod1->shouldReceive('getDocComment')->andReturn(
        '/** @api {get} /api/users GetUsers @apiName GetUsers @apiGroup User */'
    );

    $mockMethod2 = Mockery::mock();
    $mockMethod2->shouldReceive('getDocComment')->andReturn(
        '/** @api {post} /api/users CreateUser @apiName CreateUser @apiGroup User */'
    );

    $mockReflection->shouldReceive('getMethods')->andReturn([$mockMethod1, $mockMethod2]);

    // Test the processControllerMethods method
    $reflection = new ReflectionClass($this->generator);
    $method = $reflection->getMethod('processControllerMethods');
    $method->setAccessible(true);

    $result = $method->invoke($this->generator, $mockReflection);

    expect($result)->not->toBeEmpty();
    expect($result)->toHaveCount(2);
    expect($result[0]['method'])->toBe('GET');
    expect($result[0]['path'])->toBe('/api/users');
    expect($result[0]['name'])->toBe('GetUsers');
    expect($result[0]['group'])->toBe('User');
    expect($result[1]['method'])->toBe('POST');
    expect($result[1]['path'])->toBe('/api/users');
    expect($result[1]['name'])->toBe('CreateUser');
    expect($result[1]['group'])->toBe('User');
});

test('it handles empty doc blocks gracefully', function () {
    $reflection = new ReflectionClass($this->generator);
    $method = $reflection->getMethod('parseDocBlock');
    $method->setAccessible(true);

    $result = $method->invoke($this->generator, '');

    expect($result)->toBeArray();
    expect($result['method'])->toBe('GET'); // Default value
    expect($result['name'])->toBe('Untitled Endpoint'); // Default value
});
