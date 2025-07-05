<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\MessageBag;

class BaseController extends Controller
{
    /**
     * Success response method.
     *
     * @param mixed $result
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, string $message, int $code = 200)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     *
     * @param string $error
     * @param MessageBag $errorMessages
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError(string $error, MessageBag $errorMessages = null, int $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages->getMessages();
        }

        return response()->json($response, $code);
    }

    /**
     * Success response with pagination.
     *
     * @param mixed $result
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPaginatedResponse($paginatedResult, string $message, int $code = 200)
    {
        $response = [
            'success' => true,
            'data' => $paginatedResult->items(),
            'pagination' => [
                'total' => $paginatedResult->total(),
                'per_page' => $paginatedResult->perPage(),
                'current_page' => $paginatedResult->currentPage(),
                'last_page' => $paginatedResult->lastPage(),
                'from' => $paginatedResult->firstItem(),
                'to' => $paginatedResult->lastItem(),
            ],
            'message' => $message,
        ];

        return response()->json($response, $code);
    }
}
