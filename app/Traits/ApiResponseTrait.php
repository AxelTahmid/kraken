<?php

namespace App\Traits;


trait ApiResponseTrait
{

    protected function successResponse($data = null, $message = null, $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function errorResponse($message = null, $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $status);
    }
}
