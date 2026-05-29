<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return success response
     *
     * @param mixed $data - Response data
     * @param string $message - Success message
     * @param int $statusCode - HTTP status code
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error response
     *
     * @param string $message - Error message
     * @param int $statusCode - HTTP status code
     * @param mixed $errors - Validation errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return created response
     *
     * @param mixed $data - Created resource data
     * @param string $message - Success message
     * @return JsonResponse
     */
    protected function createdResponse(mixed $data, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return no content response
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return validation error response
     *
     * @param mixed $errors - Validation errors
     * @param string $message - Error message
     * @return JsonResponse
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return not found response
     *
     * @param string $resource - Resource name
     * @return JsonResponse
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse($resource . ' not found', 404);
    }
}
