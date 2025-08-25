<?php

// tests/Unit/ApiDocParserTest.php

use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGenerator;

test('it parses basic api annotations', function () {
    $generator = new ApiDocGenerator(app('files'));
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('parseDocBlock');
    $method->setAccessible(true);

    $docBlock = <<<'DOC'
/**
 * @api {get} /api/test Get test data
 * @apiName GetTest
 * @apiGroup Test
 * @apiDescription Test description
 */
DOC;

    $result = $method->invoke($generator, $docBlock);

    expect($result)->not->toBeEmpty();
    expect($result['method'])->toBe('GET');
    expect($result['path'])->toBe('/api/test');
    expect($result['name'])->toBe('GetTest');
    expect($result['group'])->toBe('Test');
    expect($result['description'])->toBe('Test description');
});

test('it parses api parameters', function () {
    $generator = new ApiDocGenerator(app('files'));
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('parseDocBlock');
    $method->setAccessible(true);

    $docBlock = <<<'DOC'
/**
 * @api {post} /api/users Create user
 * @apiName CreateUser
 * @apiGroup User
 * 
 * @apiParam {String} name User name
 * @apiParam {String} [email] User email
 */
DOC;

    $result = $method->invoke($generator, $docBlock);

    expect($result['parameters'])->toHaveCount(2);
    expect($result['parameters'][0]['name'])->toBe('name');
    expect($result['parameters'][0]['required'])->toBeTrue();
    expect($result['parameters'][1]['name'])->toBe('email');
    expect($result['parameters'][1]['required'])->toBeFalse();
});

test('it parses response examples', function () {
    $generator = new ApiDocGenerator(app('files'));
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('parseDocBlock');
    $method->setAccessible(true);

    $docBlock = <<<'DOC'
/**
 * @api {get} /api/test Get test
 * @apiName GetTest
 * @apiGroup Test
 * 
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "data": []
 *     }
 * 
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "Not found"
 *     }
 */
DOC;

    $result = $method->invoke($generator, $docBlock);

    expect($result['responses']['examples']['success'])->toContain('"data": []');
    expect($result['responses']['examples']['error'])->toContain('"error": "Not found"');
});
