<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenderingUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class TenderingUserController extends Controller
{
    public function getRegister()
    {
        try {
            // Log::info('Entering getRegister method');
            $isAdmin = TenderingUser::count() === 0;
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

    public function register(Request $request)
    {
        try {
            Log::info($request);

            $existingUser = TenderingUser::where('username', $request->input('username'))->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'Username already exists. Please choose a different username.'
                ], Response::HTTP_CONFLICT);
            }

            $role = TenderingUser::count() === 0 ? 'admin' : $request->input('role', 'user');

            $user = TenderingUser::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password')),
                'role' => $role,
                'branch' => $request->input('branch'),
            ]);
            Log::info("is admin" . $user);

            $isAdmin = $role === 'admin';
            Log::info("is admin" . $user);
            return response()->json([
                'user' => $user,
                'isAdmin' => $isAdmin
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error registering user.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            $user = TenderingUser::where('username', $request->input('username'))->first();
            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                return response()->json(['error' => 'Username or password is not matched'], Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('TenderingManagement')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error during login.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
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
    public function getUserNameById($id)
    {
        try {
            $user = TenderingUser::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['name' => $user->name], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error fetching user by ID: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching user by ID.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
        $status = Password::broker('tendering_users')->sendResetLink(
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
    public function resetPassword2(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Attempt to reset the user's password
        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        // Handle the response based on the status
        if ($status === Password::PASSWORD_RESET) {
            Log::info('Password reset successfully for email: ' . $request->email);
            return response()->json(['message' => 'Password has been reset successfully.'], Response::HTTP_OK);
        } else {
            Log::warning('Password reset failed for email: ' . $request->email . '. Status: ' . $status);
            return response()->json(['message' => 'Invalid token or email.'], Response::HTTP_BAD_REQUEST);
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
        $status = Password::broker('tendering_users')->reset(
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

    // Reset Password
    // public function resetPassword(Request $request)
    // {
    //     Log::info('Password reset process started');

    //     // Validate the new password
    //     $request->validate([
    //         'password' => 'required|confirmed|min:8',
    //     ]);

    //     // Assuming the user is authenticated (if you’re using a password reset form while logged in)
    //     $user = Auth::user();

    //     if (!$user) {
    //         return response()->json(['message' => 'User not authenticated.'], Response::HTTP_UNAUTHORIZED);
    //     }

    //     // Find the TenderingUser instance (assuming TenderingUser is your model)
    //     $tenderUser = TenderingUser::where('id', $user->id)->first();

    //     if (!$tenderUser) {
    //         Log::error('User not found for id: ' . $user->id);
    //         return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
    //     }

    //     // Update the password for the user
    //     $tenderUser->password = Hash::make($request->password);
    //     $tenderUser->save();

    //     Log::info('Password reset successfully for user: ' . $tenderUser->email);

    //     // Return success response
    //     return response()->json(['message' => 'Password has been reset successfully.'], Response::HTTP_OK);
    // }





}
