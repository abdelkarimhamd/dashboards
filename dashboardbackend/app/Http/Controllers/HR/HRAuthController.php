<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\User; // Ensure the User model is correctly namespaced
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class HRAuthController extends Controller
{
    /**
     * Handle the HR login request using username and password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'username' => 'required|string', // Changed from 'email' to 'username'
            'password' => 'required|string',
        ]);

        // 2. Attempt to find the user by username
        $user = User::where('username', $request->username)->first();

        // 3. Check if user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            // 4. Generate a token using Laravel Sanctum
            $token = $user->createToken('hr_token')->plainTextToken;

            // 5. Return the token and user data as a JSON response
            return response()->json([
                'token' => $token,
                'user' => $user,
            ], 200);
        } else {
            // 6. Return an error response if authentication fails
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
    }
    public function forgotPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
        ]);

        // Attempt to send the password reset link
        $status = Password::broker('hr_users')->sendResetLink(
            $request->only('email')
        );
        // Handle the response based on the status
        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent to email: ' . $request->email);
            return response()->json(['message' => 'Reset link sent to your email.'], Response::HTTP_OK);
        } else {
            // For security reasons, do not reveal whether the email exists
            Log::warning('Password reset link failed to send for email: ' . $request->email);
            return response()->json(['message' => 'Unable to send reset link.'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
    
        // Attempt to reset the user's password using the correct broker
        $status = Password::broker('hr_users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
    
                // Optionally, log the user in or perform other actions
            }
        );
    
        // Handle the response based on the status
        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully.'], Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Invalid token or email.'], Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * Handle the HR logout request by revoking the current token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function hrLogout(Request $request)
    {
        // 1. Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        // 2. Return a success response
        return response()->json([
            'message' => 'Successfully logged out',
        ], 200);
    }

    /**
     * Handle the HR user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:hr_user,username', // Ensure username is unique
            'email' => 'required|email|unique:hr_user,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',
        ]);

        // 2. Create a new HR user instance
        $user = new User();
        $user->full_name = $request->full_name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);

        // 3. If this is the first user, assign the 'admin' role
        if (User::count() == 0) {
            $user->role = 'admin';
        }

        // 4. Save the user to the database
        $user->save();

        // 5. Return a success response
        return response()->json([
            'message' => 'User registered successfully',
        ], 201);
    }
}
