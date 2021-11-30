<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(RegisterRequest $request){

        // $devRole = Role::developer()->first();

        $scholarRole = Role::scholar()->first();

        $user = User::create([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->roles()->attach($scholarRole->id);

        if(!$user){
            return response()->json(['message' => 'Registration Failed']);
        }

        return response()->json(['message' => 'Registered Successfully']);
    }

    public function login(LoginRequest $request){

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active' => 1])) {
            throw ValidationException::withMessages([
                'email' => 'Invalid Credentials. Please contact your manager, thank you.'
            ]);
        }
        

        return response()->json(['message' => 'Login Successfully']);

    }

    public function logout(Request $request){

        if(Auth::check()){
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request){
        $response = $this->broker()->sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
        ? $this->sendResetLinkResponse($request, $response)
        : $this->sendResetLinkFailedResponse($request, $response);
    }

    public function reset(ResetPasswordRequest $request){
        $response = $this->broker()->reset(
            $this->credentials($request), function($user, $password){
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);

    }

    protected function resetPassword($user, $password){
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
    }

    public function broker(){
        return Password::broker();
    }

    protected function sendResetLinkResponse(Request $request, $response){
        return response()->json(['message' => 'Email Sent. Please check your email for reset password link.', 'response' => $response], 200);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response){
        return response()->json(['message' => 'Failed to send email. There is something error', 'response' => $response], 500);
    }

    protected function credentials(Request $request){
        return $request->only('email', 'password', 'password_confirmation', 'token');
    }

    protected function sendResetResponse(Request $request, $response){
        return response()->json(['message' => 'Password Reset Successfully', 'response' => $response], 200);
    }

    protected function sendResetFailedResponse(Request $request, $response){
        return response()->json(['message' => 'Reset Password Failed. There is something error', 'response' => $response], 500);
    }
}
