<?php

use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

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
    function sendNotFoundResponse(string $message = 'record_not_found')
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_NOT_FOUND)
            ->view(config('response.views.errors.404', 'admin::errors.404'))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendServerErrorResponse')) {
    /*
    | Server error response.
    */
    function sendServerErrorResponse(string|null $message = null)
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->view(config('response.views.errors.500', 'admin::errors.500'))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendMaintenanceModeResponse')) {
    /*
    | Maintenance mode response (503).
    */
    function sendMaintenanceModeResponse(string $message = 'under_maintenance')
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_SERVICE_UNAVAILABLE)
            ->view(config('response.views.errors.503', 'admin::errors.503'))
            ->withDefaultMessage($message)
            ->send();
    }
}

if (! function_exists('sendDontHavePermissionResponse')) {
    /*
    | Forbidden response (403).
    */
    function sendDontHavePermissionResponse(string $message = 'dont_have_permission')
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_FORBIDDEN)
            ->withDefaultMessage($message)
            ->withData([
                'error' => trans(config('response.translations.namespace', 'response') . '::' . config('response.translations.file', 'messages') . '.' . config('response.translations.keys.web', 'web_response_messages') . '.dont_have_permission'),
            ])
            ->send();
    }
}

if (! function_exists('sendMethodNotAllowedResponse')) {
    /*
    | Method not allowed response (405).
    */
    function sendMethodNotAllowedResponse(string $message = 'method_not_allowed')
    {
        return app('response')
            ->fail()
            ->code(Response::HTTP_METHOD_NOT_ALLOWED)
            ->view(config('response.views.errors.405', 'admin::errors.405'))
            ->withCustomMessage($message)
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

