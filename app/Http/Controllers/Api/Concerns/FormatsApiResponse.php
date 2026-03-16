<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Helpers\FileHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait FormatsApiResponse
{
    protected function successResponse(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json(FileHelper::formatResponse(true, $data, $message), $status);
    }

    protected function errorResponse(string $message, int $status = 500, mixed $data = null): JsonResponse
    {
        return response()->json(FileHelper::formatResponse(false, $data, $message), $status);
    }

    protected function paginatedResourceResponse(
        LengthAwarePaginator $paginator,
        array $data,
        string $message,
        int $status = 200
    ): JsonResponse {
        $data['pagination'] = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];

        return $this->successResponse($data, $message, $status);
    }
}
