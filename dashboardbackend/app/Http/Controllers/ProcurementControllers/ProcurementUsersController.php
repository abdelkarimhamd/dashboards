<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
class ProcurementUsersController extends Controller
{
    public function getRegister() {
        try {
            // Log::info('Entering getRegister method');
            $isAdmin = ProcurementUser::count() === 0;
            // Log::info('isAdmin status: ' . $isAdmin);

            return response()->json([
                'isAdmin' => $isAdmin
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error fetching registration status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching registration status.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function register(Request $request) {
        try {
            // Check if the username already exists
            $existingUser = ProcurementUser::where('username', $request->input('username'))->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'Username already exists. Please choose a different username.'
                ], Response::HTTP_CONFLICT);
            }
    
            // Set role as 'admin' if no users exist, otherwise use the role from the request (default to 'user')
            $role = ProcurementUser::count() === 0 ? 'admin' : $request->input('role', 'user');
    
            // Create new user
            $user = ProcurementUser::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
                'role' => $role,  // Assign the role correctly
                'branch' => $request->input('branch'),
            ]);
    
            // Return response
            return response()->json([
                'user' => $user,
                'isAdmin' => $role === 'admin'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::info($e);
            // Handle errors
            return response()->json([
                'message' => 'Error registering user.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    public function login(Request $request) {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            $user = ProcurementUser::where('username', $request->input('username'))->first();
            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Username or password is not matched'], Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('Procurement_dashboard_morganti')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error during login.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request) {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error during logout.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Attempt to reset the user's password using the correct broker
        $status = Password::broker('procurement_users')->reset(
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

    public function forgotPassword(Request $request)
    {
        Log::info("forget password call");
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
        ]);

        // Attempt to send the password reset link
        $status = Password::broker('procurement_users')->sendResetLink(
            $request->only('email')
        );
Log::info($status);
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

    
}


