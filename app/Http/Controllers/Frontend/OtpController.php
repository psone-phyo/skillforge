<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OtpController extends Controller
{
    public function send(Request $request, $id){
        $user = User::find($id);
        $data = rand(100000, 999999);
        $email = $user->email;
        session()->put("otp.$email", $data);
        try {
            // if (!session("otp.$email")){
                Mail::to($email)->send(new OtpMail($data));
            // }
            return  Inertia::render('auth/VerifyOtp', compact('data', 'email', 'id'));
        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->back();
        }
    }

    public function verify(Request $request){
        $code = $request->code;
        $email = $request->email;
        $user = User::find($request->id);
        $otp = session("otp.$email");
        if($code == $otp){
            Auth::login($user);
            $user->email_verified_at = now();
            $user->save();
            return response()->json([
                'message' => "Verification Succeeded",
                'redirect' => '/dashboard'
            ]);
        }else{
            return response()->json([
                'user' => $user->name,
                'code' => $code,
                'otp' => $otp,
                'email' => $email
            ],400);
        }
    }
}
