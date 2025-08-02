<?php

namespace App\Traits;


trait ApiResponse
{
    public function withSuccess($data, $message = null)
    {
        return [
            'status' => 'success',
            'data' => $data,
        ];
    }
    public function withError($error){
        return [
            'status' => 'error',
            'data' => $error,
        ];
    }

    public function jsonError($error, $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'data' => $error,
        ],200);
    }
    public function jsonSuccess($data, $message = null, $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ],$statusCode);
    }

}

