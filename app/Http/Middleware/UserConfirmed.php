<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next): Response
{
    $user = Auth::user();

    // If not logged in, continue (login routes will handle redirect)
    if (!$user) {
        return redirect('/login');
    }
    // Skip OTP and login routes to avoid loops
    // if ($request->is('send/otp/*') || $request->is('verify/otp') || $request->is('logout') || $request->is('login')) {
    //     return $next($request);
    // }

    // Redirect unapproved users
    if (!$user->status) {
        Auth::logout();
        return redirect('/login');
    }

    // Redirect users who haven't verified email
    if (($user->isStudent() || $user->isInstructor()) && !$user->email_verified_at) {
        Auth::logout();
        return redirect('/send/otp/'.$user->id);
    }
    return $next($request);
}

}
