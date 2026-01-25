# Laravel Response Helper Package

![Laravel Response Helper](https://img.shields.io/badge/Laravel-Response_Helper-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)

A fluent response builder + global helper functions for Laravel apps (web, ajax, api, internal).

## 📦 Features

- **Fluent Response Builder** via `app('response')`
- **Auto Output Mode** (API / Ajax / Web / Internal)
- **Publishable Config & Translations**
- **Compatibility Helpers** (`send*Response()` functions)
- **Configurable Payload Keys** (rename `success`, `message`, `data`, `errors`, ...)

## 🚀 Installation

1. Install via Composer:

```bash
composer require arrahmouni/response-helper
```

2. Publish configuration and translations (optional):

```bash
php artisan vendor:publish --provider="ArRahmouni\ResponseHelper\ResponseServiceProvider" --tag=response-config
php artisan vendor:publish --provider="ArRahmouni\ResponseHelper\ResponseServiceProvider" --tag=response-lang
```

- **Config**: `config/response.php`
- **Lang**: `lang/vendor/response/{en,ar}/messages.php`

## 🛠 Configuration

Edit `config/response.php` to customize:

```php
return [
    'binding' => [
        'key' => 'response',
    ],
    'paths' => [
        'api_pattern' => 'api/*',
    ],
    'payload' => [
        'keys' => [
            'success' => 'success',
            'code'    => 'code',
            'message' => 'message',
            'data'    => 'data',
            'errors'  => 'errors',
        ],
        'api' => [
            'message_as_string'    => true,
            'cast_empty_to_object' => true,
        ],
    ],
];
```

## 🎯 Basic Usage

Build and send a response:

```php
return app('response')
    ->success()
    ->withDefaultMessage('data_loaded_successfully')
    ->withData([
        'items' => $items,
    ])
    ->send();
```

## 🔧 Output Modes

`send()` automatically selects the output format:

- **API**: JSON when the request matches `config('response.paths.api_pattern')` (default: `api/*`)
- **Ajax**: JSON when `request()->ajax()` or when you force it via `send(asAjax: true)`
- **Web (non-ajax)**:
  - redirect when `redirectTo()` was set
  - view when `view()` was set
  - otherwise `back()` with flash message + old input + errors
- **Internal**: array when `send(isInternal: true)`

## 🧰 Global Helper Functions

This package autoloads `src/helpers.php` and provides:

- **Web/Ajax**
  - `sendSuccessResponse(?string $redirectTo = null, ?string $message = null, bool $withIntended = false, mixed $customMessage = null)`
  - `sendFailResponse(?string $message = null, ?string $customMessage = null)`
  - `sendValidationResponse(\Illuminate\Validation\Validator $validator)`
  - `sendExceptionResponse(\Exception $e)`
  - `sendUnauthorizedResponse(?string $redirectTo = null, string $message = 'login_required')`
  - `sendNotFoundResponse(string $message = 'record_not_found')`
  - `sendServerErrorResponse(?string $message = null)`
  - `sendMaintenanceModeResponse(string $message = 'under_maintenance')`
  - `sendDontHavePermissionResponse(string $message = 'dont_have_permission')`
  - `sendMethodNotAllowedResponse(string $message = 'method_not_allowed')`

- **Internal (always returns array)**
  - `sendSuccessInternalResponse(?string $message = null, array $data = [], ?string $customMessage = null)`
  - `sendFailInternalResponse(?string $message = null, array $errors = [], ?string $customMessage = null)`

- **API (JSON)**
  - `sendApiSuccessResponse(string $message = 'data_loaded_successfully', ?string $customMessage = null, array $data = [])`
  - `sendApiFailResponse(?string $message = null, ?string $customMessage = null, array $errors = [], array $data = [])`

## 🛡 Service Provider

The `ResponseServiceProvider` handles:

- Container binding (`response.binding.key`)
- Config + translations publishing
- Translation namespace registration (`response::messages.*`)

## 📜 License

This package is open-source software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

**Happy Response Building!**

