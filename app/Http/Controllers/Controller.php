<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;

abstract class Controller
{
    use ResponseTrait;

/*     public function respondSuccess(array $data = [], string $message = 'Request successful', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public function respondError(string $message = 'An error occurred', int $statusCode = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function respondCreated(string $message = 'Resource created successfully', int $statusCode = 201)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $statusCode);
    }

    public function respondOk(string $message = 'Request successfully processed', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $statusCode);
    }

    public function respondForbidden(string $message = 'You are not authorized to access this resource', int $statusCode = 403)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    public function respondValidationError($errors, int $statusCode = 422)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $errors,
        ], $statusCode);
    }

    public function check(mixed $data)
    {
        return response()->json([
            'success' => true,
            'message' => $data,
        ], 200);
    } */
}
