<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Return success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return success response with pagination meta
     */
    protected function successResponseWithMeta($data, array $meta, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], 200);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, $errors = null, string $errorCode = 'ERROR', int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'status_code' => $statusCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 'VALIDATION_ERROR', 422);
    }

    /**
     * Return not found error response
     */
    protected function notFoundResponse(string $message = 'Không tìm thấy dữ liệu'): JsonResponse
    {
        return $this->errorResponse($message, null, 'NOT_FOUND', 404);
    }

    /**
     * Return unauthorized error response
     */
    protected function unauthorizedResponse(string $message = 'Không có quyền truy cập'): JsonResponse
    {
        return $this->errorResponse($message, null, 'UNAUTHORIZED', 401);
    }

    /**
     * Return forbidden error response
     */
    protected function forbiddenResponse(string $message = 'Bạn không có quyền thực hiện hành động này'): JsonResponse
    {
        return $this->errorResponse($message, null, 'FORBIDDEN', 403);
    }

    /**
     * Return server error response
     */
    protected function serverErrorResponse(string $message = 'Lỗi hệ thống, vui lòng thử lại sau'): JsonResponse
    {
        return $this->errorResponse($message, null, 'SERVER_ERROR', 500);
    }

    /**
     * Format pagination meta from Laravel paginator
     */
    protected function getPaginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
