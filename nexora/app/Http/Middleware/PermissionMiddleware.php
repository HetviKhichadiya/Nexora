<?php

namespace App\Http\Middleware;

use App\Constants\CommonConstant;
use App\Constants\HttpStatusConstant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return errorResponse(HttpStatusConstant::UNAUTHORIZED, 'UNAUTHORIZED', 'User not authenticated');
        }

        if($user->user_type === CommonConstant::USER_TYPE_ADMIN){
            return $next($request);
        }

        if(!hasPermission($user->id, $permission)){
            return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'Permission denied');
        }

        return $next($request);
    }
}
