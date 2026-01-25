<?php

namespace ArRahmouni\ResponseHelper;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| Response Helper
|--------------------------------------------------------------------------
|
| Fluent response builder for web, ajax, api, and internal use-cases.
| Output is intended to be compatible with existing usages of:
| - app('response')->success()->...->send()
| - global send*Response() helpers
|
*/

class ResponseHelper
{
    /*
    | HTTP status code used by response()->json(..., $this->code).
    */
    public int $code = Response::HTTP_OK;

    /*
    | Response payload that is returned or flashed.
    */
    public array $data = [];

    /*
    | Translation namespace used by translate().
    */
    public string $module = '';

    /*
    | Attached exception instance (when provided).
    */
    public Exception|null $exception = null;

    /*
    | Initialize default payload values.
    */
    public function __construct()
    {
        $this->data['notify_type']   = config('response.notify.default', 'toastr');
        $this->module                = config('response.translations.namespace', 'response');
    }

    /*
    | Set the translation namespace (e.g. admin, response).
    */
    public function module(string $module = 'response'): static
    {
        $this->module = strtolower($module);
        return $this;
    }

    /*
    | Set the UI notification type used by the front-end (web/ajax only).
    */
    public function notifyType(string $type): static
    {
        $this->data['notify_type'] = strtolower($type);
        return $this;
    }

    /*
    | Mark as successful response and set default code (200).
    */
    public function success(): static
    {
        $this->data[$this->payloadKey('success')]  = true;
        $this->data[$this->payloadKey('code')]     = Response::HTTP_OK;
        return $this;
    }

    /*
    | Mark as failed response and set default code (422).
    */
    public function fail(): static
    {
        $this->data[$this->payloadKey('success')]  = false;
        $this->data[$this->payloadKey('code')]     = Response::HTTP_UNPROCESSABLE_ENTITY;
        return $this;
    }

    /*
    | Override the HTTP status code for the outgoing response.
    */
    public function code(int $code): static
    {
        $this->code                                 = $code;
        $this->data[$this->payloadKey('code')]  = $code;

        return $this;
    }

    /*
    | Configure redirect behavior for non-ajax web requests.
    */
    public function redirectTo(string|null $url = null, bool $withIntended = false): static
    {
        $this->data['redirect']     = $url;
        $this->data['withIntended'] = $withIntended;

        return $this;
    }

    /*
    | Configure a view response for non-ajax web requests.
    */
    public function view(string $view, array $data = []): static
    {
        $this->data['view'] = $view;
        $this->data['data'] = $data;

        return $this;
    }

    /*
    | Attach validation errors (kept as-is for compatibility).
    */
    public function validationErrors(mixed $errors): static
    {
        $this->data[$this->payloadKey('errors')] = $errors;

        return $this;
    }

    /*
    | Build the default message structure using translation keys.
    | `$description` is treated as a translation key under the configured message group.
    */
    public function withDefaultMessage(string|null $description = null): static
    {
        $typesKey        = config('response.translations.keys.types', 'response_message_types');
        $messagesKey     = $this->isApiRequest()
            ? config('response.translations.keys.api', 'api_response_messages')
            : config('response.translations.keys.web', 'web_response_messages');

        $isSuccess       = (bool) ($this->data[$this->payloadKey('success')] ?? false);
        $type            = $isSuccess ? 'success' : 'error';

        $this->data[$this->payloadKey('message')] = [
            'type'          => $type,
            'title'         => $this->translate("{$typesKey}.{$type}.title"),
            'description'   => is_null($description)
                ? $this->translate("{$typesKey}.{$type}.description")
                : $this->translate("{$messagesKey}.{$description}"),
        ];

        if (! $isSuccess) {
            if (is_null($description) && isset($this->data[$this->payloadKey('errors')])) {
                $this->data[$this->payloadKey('message')]['description'] = $this->translate(config('response.translations.keys.web', 'web_response_messages') . '.validation_error');
            }
        }

        return $this;
    }

    /*
    | Build a custom message. Useful when the message is already translated or dynamic.
    */
    public function withCustomMessage(string|null $message, string|null $title = null): static
    {
        if (empty($message)) {
            return $this;
        }

        $typesKey    = config('response.translations.keys.types', 'response_message_types');
        $isSuccess   = (bool) ($this->data[$this->payloadKey('success')] ?? false);
        $type        = $isSuccess ? 'success' : 'error';

        $title = empty($title)
            ? $this->translate("{$typesKey}.{$type}.title")
            : $title;

        $this->data[$this->payloadKey('message')] = [
            'type'          => $type,
            'title'         => $title,
            'description'   => $message,
        ];

        return $this;
    }

    /*
    | Attach exception details (optionally exposed to message description in dev/debug).
    */
    public function exception(Exception $exception): static
    {
        $this->exception = $exception;

        /*
        | Exception details are stored on the payload.
        */
        $this->data['exception'] = [
            'message'   => $this->exception->getMessage(),
            'file'      => $this->exception->getFile(),
            'line'      => $this->exception->getLine(),
        ];

        if ($this->shouldExposeExceptionDetails() && isset($this->data[$this->payloadKey('message')]['description'])) {
            $this->data[$this->payloadKey('message')]['description'] = 'Message : '
                . $this->exception->getMessage()
                . ' [in File : '
                . $this->exception->getFile()
                . '] - on line : '
                . $this->exception->getLine();
        }

        return $this;
    }

    /*
    | Attach payload data. Stored as object for backwards compatibility.
    */
    public function withData(array $data): static
    {
        $this->data[$this->payloadKey('data')] = ! empty($data) ? (object) $data : null;

        return $this;
    }

    /*
    | Attach error bag. Stored as object for backwards compatibility.
    */
    public function withErrors(array $errors): static
    {
        $this->data[$this->payloadKey('errors')] = ! empty($errors) ? (object) $errors : null;

        return $this;
    }

    /*
    | Send response in one of 4 modes: internal, api, ajax, non-ajax web.
    */
    public function send(bool $isInternal = false, bool $asAjax = false)
    {
        /*
        | Internal mode always returns a plain array.
        */
        if ($isInternal) {
            return $this->handleInternalResponse();
        }

        /*
        | API mode is selected based on response.paths.api_pattern.
        */
        if ($this->isApiRequest()) {
            return $this->handleApiResponse();
        }

        /*
        | Ajax mode forces JSON even for non-api requests.
        */
        if ($asAjax || request()->ajax()) {
            return $this->handleAjaxRequest();
        }

        return $this->handleNonAjaxRequest();
    }

    /*
    | Handle JSON response for ajax requests.
    */
    public function handleAjaxRequest()
    {
        if (isset($this->data['redirect']) && ! empty($this->data['redirect'])) {
            session()->flash('message', $this->data);
        }

        if (isset($this->data['view'])) {
            unset($this->data['view']);
        }

        return response()->json($this->data, $this->code);
    }

    /*
    | Handle normal web requests:
    | - redirect (when redirect is present)
    | - view (when view is present)
    | - fallback back() with flash + input + errors
    */
    public function handleNonAjaxRequest()
    {
        if (isset($this->data['redirect']) && ! empty($this->data['redirect'])) {
            if (isset($this->data['withIntended']) && $this->data['withIntended'] === true) {
                return redirect()
                    ->intended($this->data['redirect'])
                    ->with('message', $this->data);
            }

            return redirect()
                ->to($this->data['redirect'])
                ->with('message', $this->data);
        } elseif (isset($this->data['view']) && ! empty($this->data['view'])) {
            return response()->view($this->data['view'], $this->data['data']);
        }

        return back()
            ->with('message', $this->data)
            ->withInput()
            ->withErrors($this->data[$this->payloadKey('errors')] ?? []);
    }

    /*
    | Internal responses always return an array.
    */
    public function handleInternalResponse(): array
    {
        $successKey = $this->payloadKey('success');
        $codeKey    = $this->payloadKey('code');
        $messageKey = $this->payloadKey('message');
        $errorsKey  = $this->payloadKey('errors');
        $dataKey    = $this->payloadKey('data');

        $data = [
            'success'   => $this->data[$successKey] ?? false,
            'code'      => $this->code,
            'message'   => $this->data[$messageKey]['description'] ?? '',
            'errors'    => $this->data[$errorsKey] ?? [],
            'data'      => $this->data[$dataKey] ?? [],
        ];

        $data['errors'] = (array) $data['errors'];
        $data['data']   = (array) $data['data'];

        return $data;
    }

    /*
    | API responses are always JSON and may cast empty data/errors to objects.
    */
    public function handleApiResponse()
    {
        /*
        | Remove web-only keys from the API payload.
        */
        unset($this->data['notify_type'], $this->data['view'], $this->data['withIntended'], $this->data['redirect']);

        $messageKey = $this->payloadKey('message');
        $errorsKey  = $this->payloadKey('errors');
        $dataKey    = $this->payloadKey('data');

        if (isset($this->data[$messageKey])) {
            if (config('response.payload.api.message_as_string', true)) {
                $this->data[$messageKey] = $this->data[$messageKey]['description'] ?? '';
            }

            $this->data = array_merge([$messageKey => $this->data[$messageKey]], $this->data);
        }

        if (! isset($this->data[$messageKey])) {
            $this->data[$messageKey] = '';
        }

        if (! isset($this->data[$errorsKey]) || empty($this->data[$errorsKey])) {
            $this->data[$errorsKey] = config('response.payload.api.cast_empty_to_object', true) ? (object) [] : [];
        }

        if (! isset($this->data[$dataKey]) || empty($this->data[$dataKey])) {
            $this->data[$dataKey] = config('response.payload.api.cast_empty_to_object', true) ? (object) [] : [];
        }

        return response()->json($this->data, $this->code);
    }

    /*
    | Resolve the payload key name.
    | Allows renaming keys without code changes (ex: success -> ok).
    */
    private function payloadKey(string $key): string
    {
        return config("response.payload.keys.{$key}", $key);
    }

    /*
    | Detect API request based on configurable pattern (default: api/*).
    */
    private function isApiRequest(): bool
    {
        return request()->is(config('response.paths.api_pattern', 'api/*'));
    }

    /*
    | Translate using <namespace>::<file>.<key> (defaults: response::messages.*).
    */
    private function translate(string $key): string
    {
        $namespace = $this->module ?: config('response.translations.namespace', 'response');
        $file      = config('response.translations.file', 'messages');

        return trans("{$namespace}::{$file}.{$key}");
    }

    /*
    | Decide if exception details should be exposed to the message description.
    */
    private function shouldExposeExceptionDetails(): bool
    {
        $configured = config('response.debug.expose_exception_details', null);

        /*
        | When explicitly configured, it overrides the auto rule.
        */
        if (! is_null($configured)) {
            return (bool) $configured;
        }

        $devEnvs = config('response.debug.dev_environments', ['local', 'development', 'staging']);

        return config('app.debug') && app()->environment($devEnvs);
    }
}

