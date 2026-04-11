<?php

use ArRahmouni\ResponseHelper\RequestAdminArea;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

if (! function_exists('resolveWebErrorView')) {
    /**
     * Resolve the Blade view for an HTTP error page (admin control panel vs public site).
     */
    function resolveWebErrorView(string $code, ?string $webErrorContext = null): string
    {
        if ($webErrorContext === null && app()->bound('request') && request()) {
            $webErrorContext = RequestAdminArea::isAdminControlPanel(request())
                ? 'admin'
                : 'front';
        }

        $webErrorContext ??= config('response.views.default_context', 'front');

        $resolved = config("response.views.errors.{$code}");

        if (is_string($resolved)) {
            return $resolved;
        }

        if (is_array($resolved)) {
            return $resolved[$webErrorContext]
                ?? $resolved['front']
                ?? $resolved['admin']
                ?? "errors.{$code}";
        }

        return "errors.{$code}";
    }
}

/*
|--------------------------------------------------------------------------
| Global Helper Functions
|--------------------------------------------------------------------------
|
| Legacy compatibility helpers. They delegate to `app('response')` which is
| provided by the package service provider.
|
*/
if (! function_exists('debugEnabled')) {
    /*
    | Check if Laravel debug mode is enabled.
    */
    function debugEnabled(): bool
    {
        return (bool) config('app.debug');
    }
}

if (! function_exists('sendSuccessResponse')) {
    /*
    | Web success response (redirect/json based on request type).
    */
    function sendSuccessResponse(string|null $redirectTo = null, string|null $message = null, bool $withIntended = false, mixed $customMessage = null)
    {
        return app('response')
            ->success()
            ->code(Response::HTTP_OK)
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->redirectTo($redirectTo, $withIntended)
            ->send();
    }
}

if (! function_exists('sendUnauthorizedResponse')) {
    /*
    | Unauthorized response (401).
    */
    function sendUnauthorizedResponse(string|null $redirectTo = null, string $message = 'login_required')
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_UNAUTHORIZED)
            ->withDefaultMessage($message)
            ->redirectTo($redirectTo)
            ->send();
    }
}

if (! function_exists('sendFailResponse')) {
    /*
    | Generic fail response (400 by default).
    */
    function sendFailResponse(string|null $message = null, string|null $customMessage = null)
    {
        return app('response')
            ->fail()
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->code(Response::HTTP_BAD_REQUEST)
            ->send();
    }
}

if (! function_exists('sendValidationResponse')) {
    /*
    | Validation response (422).
    */
    function sendValidationResponse(Validator $validator)
    {
        return app('response')
            ->fail()
            ->validationErrors($validator->errors())
            ->withDefaultMessage()
            ->code(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->send();
    }
}

if (! function_exists('sendExceptionResponse')) {
    /*
    | Exception response (500).
    */
    function sendExceptionResponse(Exception $e)
    {
        return app('response')
            ->fail()
            ->withDefaultMessage()
            ->exception($e)
            ->code(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->send();
    }
}

if (! function_exists('sendNotFoundResponse')) {
    /*
    | Not found response.
    */
    function sendNotFoundResponse(string $message = 'record_not_found', ?string $webErrorContext = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_NOT_FOUND)
            ->view(resolveWebErrorView('404', $webErrorContext))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendServerErrorResponse')) {
    /*
    | Server error response.
    */
    function sendServerErrorResponse(string|null $message = null, ?string $webErrorContext = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->view(resolveWebErrorView('500', $webErrorContext))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendMaintenanceModeResponse')) {
    /*
    | Maintenance mode response (503).
    */
    function sendMaintenanceModeResponse(string $message = 'under_maintenance', ?string $webErrorContext = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_SERVICE_UNAVAILABLE)
            ->view(resolveWebErrorView('503', $webErrorContext))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendDontHavePermissionResponse')) {
    /*
    | Forbidden response (403).
    */
    function sendDontHavePermissionResponse(string $message = 'dont_have_permission', ?string $webErrorContext = null)
    {
        $builder = app('response')
            ->fail()
            ->code(Response::HTTP_FORBIDDEN)
            ->withDefaultMessage($message);

        if (array_key_exists('403', config('response.views.errors', []))) {
            $builder->view(resolveWebErrorView('403', $webErrorContext));
        }

        return $builder->send();
    }
}

if (! function_exists('sendMethodNotAllowedResponse')) {
    /*
    | Method not allowed response (405).
    */
    function sendMethodNotAllowedResponse(string $message = 'method_not_allowed', ?string $webErrorContext = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_METHOD_NOT_ALLOWED)
            ->view(resolveWebErrorView('405', $webErrorContext))
            ->withDefaultMessage($message)
            ->send();
    }
}

if(! function_exists('sendTooManyRequestsResponse')) {
    /*
    | Too many requests response (429).
    */
    function sendTooManyRequestsResponse(string $message = 'too_many_requests', ?string $webErrorContext = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_TOO_MANY_REQUESTS)
            ->view(resolveWebErrorView('429', $webErrorContext))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendSuccessInternalResponse')) {
    /*
    | Internal success response (always returns array).
    */
    function sendSuccessInternalResponse(string|null $message = null, array $data = [], string|null $customMessage = null)
    {
        return app('response')
            ->success()
            ->code(Response::HTTP_OK)
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->withData($data)
            ->send(isInternal: true);
    }
}

if (! function_exists('sendFailInternalResponse')) {
    /*
    | Internal fail response (always returns array).
    */
    function sendFailInternalResponse(string|null $message = null, array $errors = [], string|null $customMessage = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_BAD_REQUEST)
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->withErrors($errors)
            ->send(isInternal: true);
    }
}

if (! function_exists('sendApiSuccessResponse')) {
    /*
    | API success response (JSON).
    */
    function sendApiSuccessResponse(string $message = 'data_loaded_successfully', string|null $customMessage = null, array $data = [])
    {
        return app('response')
            ->success()
            ->code(Response::HTTP_OK)
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->withData($data)
            ->send();
    }
}

if (! function_exists('sendApiFailResponse')) {
    /*
    | API fail response (JSON).
    */
    function sendApiFailResponse(string|null $message = null, string|null $customMessage = null, array $errors = [], array $data = [])
    {
        return app('response')
            ->fail()
            ->withDefaultMessage($message)
            ->withCustomMessage($customMessage)
            ->code(Response::HTTP_BAD_REQUEST)
            ->withErrors($errors)
            ->withData($data)
            ->send();
    }
}

