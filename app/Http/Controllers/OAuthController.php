<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
        public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider_id' => $googleUser->getId(),
                    'provider' => 'google',
                    'profile_url' => $googleUser->getAvatar(),
                ]);
            }

            Auth::login($user);

            return redirect('/dashboard'); // or wherever you want
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Something went wrong!');
        }
    }
}
