<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateWithJwt
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Read token from cookie
            $token = $request->cookie('jwt');

            if (!$token) {

                return redirect()->route('login')->with('error', 'لطفاً ابتدا وارد شوید.');
            }

            // Set token in JWTAuth
            JWTAuth::setToken($token);

            // Authenticate token
            if (!$user = JWTAuth::authenticate()) {
                return redirect()->route('login')->with('error', 'توکن نامعتبر است.');
            }

            // Set user in Auth system
            Auth::setUser($user);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return redirect()->route('login')->with('error', 'توکن منقضی شده است. لطفاً دوباره وارد شوید.');
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return redirect()->route('login')->with('error', 'توکن نامعتبر است.');
        } catch (\Exception $e) {

            return redirect()->route('login')->with('error', 'خطای احراز هویت: ' . $e->getMessage());
        }

        return $next($request);
    }
}