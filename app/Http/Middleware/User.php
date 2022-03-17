<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if ($request->user()->tokenCan('server:user_external') || $request->user()->tokenCan('server:user_internal')) {
                if (! $request->user() ||
                    ($request->user() instanceof MustVerifyEmail &&
                    ! $request->user()->hasVerifiedEmail())) {
                        return response()->json([
                            'meta' => [
                                'code' => 403,
                                'message' => 'Your email address is not verified.',
                                'status' => 200
                            ],
                        ], 200);
                }
                return $next($request);
            } else {
                return response()->json([
                    'message' => 'Access  Denied! as you are not User.',
                    'status' => 403
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Please login first',
                'status' => 401
            ], 401);
        }
    }
}
