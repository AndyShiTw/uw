<?php

namespace App\Traits;


use Illuminate\Support\Facades\Redis;

trait ApiTrait
{
    public function apiResponse($message, $code, $data)
    {
        return response()->json([
            'message' => (string)$message,
            'statusCode' => (string)$code,
            'payload' => $data
        ]);
    }
}
