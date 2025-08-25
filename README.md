# Laravel API Doc Generator

[![Issues](https://img.shields.io/github/issues/singhateh/laravel-docmaker)](https://github.com/singhateh/laravel-docmaker/issues)
[![Latest Version](https://img.shields.io/packagist/v/alagiesinghateh/laravel-docmaker.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-docmaker)
[![License](https://img.shields.io/github/license/singhateh/laravel-docmaker)](https://github.com/singhateh/laravel-docmaker/license)
[![Stars](https://img.shields.io/github/stars/singhateh/laravel-docmaker)](https://github.com/singhateh/laravel-docmaker/stargazers)
[![Total Downloads](https://img.shields.io/packagist/dt/alagiesinghateh/laravel-docmaker.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-docmaker)

A powerful Laravel package that automatically generates comprehensive API documentation from your controller annotations. Supports API Blueprint format with intelligent annotation parsing, authentication detection, and smart backup system.

---


<p align="center">
    <img src="LARAVEL-API-DOC.gif" alt="Laravel API Doc Generator" style="width:100%; max-width:100%; height:auto;">
</p>


## ✨ Features

- **Automatic Annotation Generation**: Auto-generate `@api` annotations for your controller methods  
- **Smart Parameter Detection**: Automatically detects route parameters, form request validation, and model bindings  
- **Authentication Detection**: Intelligent detection of authentication requirements from middleware and parameters  
- **Backup System**: Smart backup system that only creates backups when content changes  
- **Web Interface**: Beautiful web interface to browse your API documentation  
- **Security**: Built-in security with IP whitelisting and user authentication  
- **Multiple Formats**: Supports API Blueprint format with JSON output  
- **Cross-Platform**: Works with any Laravel application  

---

## 📦 Installation

Install via Composer:

```bash
composer require alagiesinghateh/laravel-docmaker
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Alagiesinghateh\LaravelApiDocGenerator\ApiDocGeneratorServiceProvider" --tag="config"
```

Publish the views (optional):

```bash
php artisan vendor:publish --provider="Alagiesinghateh\LaravelApiDocGenerator\ApiDocGeneratorServiceProvider" --tag="views"
```

---

## ⚙️ Configuration

After publishing the config file, you can modify `config/api-doc-generator.php`:

```php
return [
    'output_dir' => storage_path('docs/api'),
    
    'controller_paths' => [
        app_path('Http/Controllers/API'),
        app_path('Http/Controllers/Api'),
    ],
    
    'web_interface' => [
        'enabled' => true,
        'route_prefix' => 'api-docs',
        'middleware' => ['web', 'api-docs'],
    ],
    
    'security' => [
        'ip_whitelist' => ['127.0.0.1', '::1'],
        'restricted_paths' => [],
    ],
    
    'allowed_users' => [],
    
    'middleware' => [
        'detect' => true,
        'auth_middleware' => ['auth', 'auth:api', 'auth:sanctum'],
        'security_schemes' => [
            'auth' => 'bearer',
            'auth:api' => 'bearer',
            'auth:sanctum' => 'bearer',
        ],
        'exclude' => ['web', 'throttle', 'bindings'],
    ],
    
    'backup' => [
        'max_backups' => 10,
    ],
    
    'defaults' => [
        'method' => 'GET',
        'path' => '/api/endpoint',
        'name' => 'Untitled Endpoint',
        'group' => 'General',
    ],
];
```

---

## 🚀 Usage

### Generate API Documentation

```bash
# Generate documentation from controller annotations
php artisan singhateh:generate

# Force regenerate all annotations (even existing ones)
php artisan singhateh:generate --force

# Dry run (see what would change without modifying files)
php artisan singhateh:generate --dry-run
```

### Regenerate Controller Annotations

```bash
# Regenerate annotations for all controllers
php artisan singhateh:annotate:regenerate

# Regenerate for specific directory
php artisan singhateh:annotate:regenerate --path=Http/Controllers/API

# Cross-check without modifying files
php artisan singhateh:annotate:regenerate --cross-check

# Force regenerate all annotations
php artisan singhateh:annotate:regenerate --force
```

### Backup Management

```bash
# List available backups
php artisan singhateh:backups:list

# Restore from latest backup
php artisan singhateh:backups:restore

# Restore specific backup
php artisan singhateh:backups:restore filename=api-docs_2023-12-15_143022.json
```

---

## 📖 Annotation Reference

### Basic Annotation Structure

```php
/**
 * @api {GET} /api/users Get Users
 * @apiName GetUsers
 * @apiGroup User
 * @apiDescription Retrieve a list of all users
 * 
 * @apiParam {String} [page] Optional page number
 * @apiParam {String} [per_page] Optional items per page
 * 
 * @apiSuccess {Object[]} data Array of users
 * @apiSuccess {Number} data.id User ID
 * @apiSuccess {String} data.name User name
 * @apiSuccess {String} data.email User email
 * 
 * @apiSuccessExample {json} Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "data": [
 *             {
 *                 "id": 1,
 *                 "name": "John Doe",
 *                 "email": "john@example.com"
 *             }
 *         ]
 *     }
 * 
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 500 Internal Server Error
 *     {
 *         "error": "Server error occurred"
 *     }
 */
public function index()
{
    // Controller logic
}
```

### Supported Annotations

| Annotation       | Description             | Example                                   |
|------------------|-------------------------|-------------------------------------------|
| `@api`           | HTTP method and endpoint | `@api {GET} /api/users Get Users`         |
| `@apiName`       | Endpoint name           | `@apiName GetUsers`                       |
| `@apiGroup`      | Group/category          | `@apiGroup User`                          |
| `@apiDescription`| Endpoint description    | `@apiDescription Get all users`           |
| `@apiParam`      | Request parameter       | `@apiParam {String} name User name`       |
| `@apiHeader`     | Request header          | `@apiHeader {String} Authorization Bearer token` |
| `@apiSuccess`    | Success response field  | `@apiSuccess {Number} id User ID`         |
| `@apiError`      | Error response          | `@apiError {401} Unauthorized`            |
| `@apiAuth`       | Authentication type     | `@apiAuth bearer`                         |
| `@apiMiddleware` | Middleware used         | `@apiMiddleware auth:api`                 |
| `@apiPermission` | Required permissions    | `@apiPermission users.read,users.write`   |

---

## 🛡️ Security

The package includes built-in security features:

### Production Environment
- API documentation is disabled by default in production  
- Only allowed users (by email) can access  
- IP whitelist support  

### Development Environment
- Localhost access allowed by default  
- Configurable IP restrictions  
- User-based access control  

### Configuration Example

```php
// config/api-doc-generator.php
'security' => [
    'ip_whitelist' => ['192.168.1.100', '10.0.0.0/24'],
    'restricted_paths' => ['internal', 'admin'],
],

'allowed_users' => [
    'admin@example.com',
    'developer@example.com',
],
```

---

## 🔧 Advanced Usage

### Custom Controller Properties

```php
class UserController extends Controller
{
    public static $apiParams = [
        'custom_param' => [
            'type' => 'string',
            'required' => true,
            'description' => 'Custom parameter description',
        ],
    ];
}
```

### Manual Annotation Overrides

```php
/**
 * @api {POST} /api/users Create User
 * @apiName CreateUser
 * @apiGroup User
 * @apiDescription Create a new user account
 * 
 * @apiParam {String} name User's full name
 * @apiParam {String} email User's email address
 * @apiParam {String} password User's password
 * 
 * @apiAuth bearer
 * @apiMiddleware auth:api
 */
public function store(CreateUserRequest $request)
{
    // Your controller logic
}
```

### Integration with Form Requests

```php
class CreateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```

---

## 🌐 Web Interface

Access the web interface at:  
`http://your-app.com/api-docs`

![API Documentation Interface]
<p align="center">
    <img src="LARAVEL-API-DOC.gif" alt="Laravel API Doc Generator" style="width:100%; max-width:100%; height:auto;">
</p>



### Customizing the Web Interface

Publish the views and modify them as needed:

```bash
php artisan vendor:publish --provider="Alagiesinghateh\LaravelApiDocGenerator\ApiDocGeneratorServiceProvider" --tag="views"
```

Views will be published to:  
`resources/views/vendor/api-doc-generator/`

---

## 🔄 Backup System

The package includes a smart backup system:

- **Automatic Backups**: Created only when documentation content changes  
- **Configurable Retention**: Keep last N backups (default: 10)  
- **Easy Restoration**: Restore from any backup via artisan commands  
- **Content-based**: Backups are created based on content changes, not file modifications  

---

## 🧪 Testing

Run the package tests:

```bash
composer test
```

---

## 🤝 Contributing

We welcome contributions! Please see `CONTRIBUTING.md` for details.

1. Fork the project  
2. Create your feature branch (`git checkout -b feature/amazing-feature`)  
3. Commit your changes (`git commit -m 'Add some amazing feature'`)  
4. Push to the branch (`git push origin feature/amazing-feature`)  
5. Open a Pull Request  

---

## 📄 License

This package is open-source software licensed under the MIT license.

---

## 🐛 Bug Reports

If you discover any bugs, please create an issue on GitHub.

---

## 📞 Support

For support and questions:  
- Create an issue on GitHub  
- Email: 3939919@gmail.com 
- Documentation: [GitHub Wiki](https://github.com/alagiesinghateh/laravel-api-doc-generator/wiki)

---

## 🙏 Acknowledgments

- Inspired by various API documentation generators  
- Built with the Laravel framework  
- Thanks to all contributors  

> **Note**: This package is actively maintained. For the latest updates and features, always check the GitHub repository.
