<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    | Directory where generated API documentation files will be stored.
    | Default is: storage/app/api-docs
    */
    'output_dir' => storage_path('app/api-docs'),

    'backup' => [
        'enabled' => true,
        'max_backups' => 10, // Keep last 10 backups
        'backup_dir' => 'backups',
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Paths
    |--------------------------------------------------------------------------
    | Array of paths where your API controllers are located. The generator
    | will scan these directories for annotations to generate documentation.
    */
    'controller_paths' => [
        app_path('Http/Controllers/API'),
        // app_path('Http/Controllers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values for Endpoints
    |--------------------------------------------------------------------------
    | Used when no specific annotation is provided for a method.
    */
    'defaults' => [
        'method' => 'GET',
        'path' => '/',
        'name' => 'Untitled Endpoint',
        'group' => 'General',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scanning Options
    |--------------------------------------------------------------------------
    | Enable or disable scanning of routes, Form Requests, and Resources.
    */
    'scan_routes' => true,
    'scan_requests' => true,
    'scan_resources' => true,

    /*
    |--------------------------------------------------------------------------
    | Excluded Methods
    |--------------------------------------------------------------------------
    | Methods that should never be included in the documentation.
    */
    'excluded_methods' => [
        'middleware', 'validator', 'validate', 'authorize',
        'getMiddleware', '__construct', '__destruct',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    | The prefix used in your API routes, e.g., ['api', 'api/v1'].
    */
    'api_prefix' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Documentation Format
    |--------------------------------------------------------------------------
    | Output format for generated API docs. Default is JSON.
    | 'pretty_print' ensures readable formatting.
    */
    'format' => 'json',
    'pretty_print' => true,

    /*
    |--------------------------------------------------------------------------
    | Web Interface Settings
    |--------------------------------------------------------------------------
    | Configure the route prefix, middleware, and whether the web interface is enabled.
    | Middleware can be overridden by adding custom middleware here.
    */
    'web_interface' => [
        'enabled' => env('API_DOCS_WEB_ENABLED', false), // Turn on/off the web interface
        'route_prefix' => env('API_DOCS_ROUTE_PREFIX', 'api-docs'), // URL prefix
        'middleware' => ['web', 'protect.api.docs'], // Default middleware
    ],

    /*
    |--------------------------------------------------------------------------
    | API Testing
    |--------------------------------------------------------------------------
    | Configure API test endpoint settings, including timeout and default headers.
    */
    'testing' => [
        'enabled' => env('API_DOCS_TESTING_ENABLED', true),
        'timeout' => 30,
        'default_headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Settings
    |--------------------------------------------------------------------------
    | Control which controllers/methods to exclude and the order of groups.
    */
    'documentation' => [
        'include_non_api_methods' => false, // Include methods without annotations?
        'exclude' => [
            'controllers' => [],
            'methods' => [],
        ],
        'groups_order' => [
            'Authentication', 'Users', 'Products', 'Orders', 'General',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Examples
    |--------------------------------------------------------------------------
    | Settings for how example responses are formatted in the documentation.
    */
    'response_examples' => [
        'pretty_print' => true,
        'indentation' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    | Control access to the web interface and allow IP whitelisting.
    */
    'security' => [
        'protected' => env('API_DOCS_PROTECTED', true), // Enable basic auth protection
        'username' => env('API_DOCS_USERNAME', 'admin'),
        'password' => env('API_DOCS_PASSWORD', 'password'),
        'ip_whitelist' => env('API_DOCS_IP_WHITELIST', '127.0.0.1,::1'), // List of IPs allowed to access without auth
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    | Cache API documentation files to improve performance.
    */
    'cache' => [
        'enabled' => env('API_DOCS_CACHE', true),
        'duration' => 3600, // in seconds
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Handling
    |--------------------------------------------------------------------------
    | Detect middleware automatically and map auth middleware to security schemes.
    */
    'middleware' => [
        'detect' => true, // Auto-detect middleware in controllers
        'auth_middleware' => [
            'auth', 'auth:*', 'sanctum', 'jwt', 'jwt.auth', 'passport', 'auth:api', 'auth:sanctum',
        ],
        'exclude' => [
            'web', 'cookie', 'start_session', 'share.errors', 'verify_csrf_token', 'encrypt_cookies',
        ],
        'security_schemes' => [
            'auth' => 'bearer',
            'auth:api' => 'bearer',
            'auth:sanctum' => 'bearer',
            'sanctum' => 'bearer',
            'jwt' => 'bearer',
            'jwt.auth' => 'bearer',
            'passport' => 'bearer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Access Control
    |--------------------------------------------------------------------------
    | Prevent access in production by default; can be overridden by environment variable.
    */
    'allow_in_production' => env('API_DOCS_ALLOW_PROD', false),

    /*
    |--------------------------------------------------------------------------
    | Allowed Users
    |--------------------------------------------------------------------------
    | Emails of authenticated users allowed to access the docs.
    */
    'allowed_users' => [
        'admin@example.com',
        'developer@example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Restricted Paths
    |--------------------------------------------------------------------------
    | Sections of the documentation to restrict (e.g., internal or beta endpoints)
    */
    'restricted_paths' => [
        'internal',
        'beta',
    ],

];
