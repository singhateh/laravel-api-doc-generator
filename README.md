# Laravel API Doc Generator

[![Latest Version](https://img.shields.io/packagist/v/alagiesinghateh/laravel-api-doc-generator.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-api-doc-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/alagiesinghateh/laravel-api-doc-generator.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-api-doc-generator)
[![License](https://img.shields.io/packagist/l/alagiesinghateh/laravel-api-doc-generator.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-api-doc-generator)
[![PHP Version](https://img.shields.io/packagist/php-v/alagiesinghateh/laravel-api-doc-generator.svg?style=flat-square)](https://packagist.org/packages/alagiesinghateh/laravel-api-doc-generator)

A powerful Laravel package that automatically generates comprehensive API documentation from your controller annotations. Supports API Blueprint format with intelligent annotation parsing, authentication detection, and smart backup system.

## ✨ Features

- **Automatic Annotation Generation**: Auto-generate `@api` annotations for your controller methods
- **Smart Parameter Detection**: Automatically detects route parameters, form request validation, and model bindings
- **Authentication Detection**: Intelligent detection of authentication requirements from middleware and parameters
- **Backup System**: Smart backup system that only creates backups when content changes
- **Web Interface**: Beautiful web interface to browse your API documentation
- **Security**: Built-in security with IP whitelisting and user authentication
- **Multiple Formats**: Supports API Blueprint format with JSON output
- **Cross-Platform**: Works with any Laravel application

## 📦 Installation

Install via Composer:

```bash
composer require alagiesinghateh/laravel-api-doc-generator

```

## Publish the configuration file:

```bash
php artisan vendor:publish --provider="Alagiesinghateh\LaravelApiDocGenerator\LaravelApiDocGeneratorServiceProvider" --tag="config"
```

## Publish the views (optional):

```bash
php artisan vendor:publish --provider="Alagiesinghateh\LaravelApiDocGenerator\LaravelApiDocGeneratorServiceProvider" --tag="views"

```
