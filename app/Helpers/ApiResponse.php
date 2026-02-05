<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Additional metadata to attach to response
     */
    protected static ?array $meta = null;

    /**
     * Return a success response
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @return JsonResponse
     */
    public static function success(int $code, string $message, mixed $data = null): JsonResponse
    {
        return self::respond(true, $code, $message, $data);
    }

    /**
     * Return an error response
     *
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @return JsonResponse
     */
    public static function error(int $code, string $message, mixed $data = null): JsonResponse
    {
        return self::respond(false, $code, $message, $data);
    }

    /**
     * Return a 201 Created response
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return JsonResponse
     */
    public static function created(string $message, mixed $data = null): JsonResponse
    {
        return self::respond(true, 201, $message, $data);
    }

    /**
     * Return a 404 Not Found response
     *
     * @param  string  $message
     * @param  mixed  $data
     * @return JsonResponse
     */
    public static function notFound(string $message, mixed $data = null): JsonResponse
    {
        return self::respond(false, 404, $message, $data);
    }

    /**
     * Return a 422 Validation Error response
     *
     * @param  string  $message
     * @param  array  $errors
     * @return JsonResponse
     */
    public static function validationError(string $message, array $errors = []): JsonResponse
    {
        $data = empty($errors) ? null : ['errors' => $errors];

        return self::respond(false, 422, $message, $data);
    }

    /**
     * Attach metadata to the next response (chainable)
     *
     * @param  array  $meta
     * @return static
     */
    public static function withMeta(array $meta): static
    {
        self::$meta = $meta;

        return new static();
    }

    /**
     * Build and return the JSON response
     *
     * @param  bool  $success
     * @param  int  $code
     * @param  string  $message
     * @param  mixed  $data
     * @return JsonResponse
     */
    protected static function respond(bool $success, int $code, string $message, mixed $data = null): JsonResponse
    {
        $response = [
            'success' => $success,
            'code' => $code,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (self::$meta !== null) {
            $response['meta'] = self::$meta;
            self::$meta = null; // Reset meta after use
        }

        return response()->json($response, $code);
    }
}
