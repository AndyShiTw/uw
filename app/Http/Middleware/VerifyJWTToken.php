<?php
namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\ApiTrait;

class VerifyJWTToken
{
    use ApiTrait;

    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request->merge(['user_id' => (string) $user->user_id]);
        } catch (Exception $e) {;
            return $this->apiResponse('JWT異常', 1040, (object) []);
        }

        // 如果一切正常，继续请求流程
        return $next($request);
    }
}

