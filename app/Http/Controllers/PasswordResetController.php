<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function send_reset_password_email(Request $request){
        $request->validate([
            'email' => 'required|email',
        ]);
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if(!$user){
            return response([
                'message' => 'User not found',
                'status' => 'failed',
            ],404);
        }
        //generate token
        $token = Str::random(60);

        Mail::send('reset',['token'=>$token], function(Message $message)use($email){
            $message->subject('Reset your password');
            $message->to($email);
        });

        PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);
       // dump("http://127.0.0.1:3000/api/user/reset/". $token);

        // Mail::send('reset',['token'=>$token], function(Message $message)use($email){
        //     $message->subject('Reset your password');
        //     $message->to($email);
        // });
        return response([
            'message' => 'Password reset email has been sent. Please check your email',
            'status' => 'success',
        ],200);
    }

    public function reset(Request $request, $token){

        $formatted = Carbon::now()->subMinutes(3)->toDateTimeString();
        PasswordReset::where('created_at', '<=', $formatted)->delete();
        $request->validate([
            'password' => 'required|confirmed',
        ]);

        $passwordreset = PasswordReset::where('token', $token)->first();
        if(!$passwordreset){
            return response([
                'message' => 'Password reset token invalid or expired',
                'status' => 'failed',
            ],404);
        }
        $user = User::where('email', $passwordreset->email)->first(); 

        $user->password = Hash::make($request->password);

        PasswordReset::where('email', $user->email)->delete();

        return response([
            'message' => 'Password reset Success',
            'status' => 'success',
        ],200);
    }
}
