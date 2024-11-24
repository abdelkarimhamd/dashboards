<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch all users
            $users = User::all();

            // Return a success response with the users
            return response()->json([
                'message' => 'Users retrieved successfully',
                'users' => $users,
            ], 200);

        } catch (\Exception $e) {
            // Handle any errors that occur
            return response()->json([
                'message' => 'Error fetching users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        // Step 1: Validate the login request
        $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:6',
        ]);

        // Step 2: Find the user by username
        $user = User::where('username', $request->username)->first();

        // Step 3: Check if the user exists and if the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Step 4: Generate a token for the user (if using token-based authentication)
        $token = $user->createToken('CRMToken')->plainTextToken;

        // Step 5: Return a success response with the token
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        Log::info("forget password call");
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
        ]);

        // Attempt to send the password reset link
        $status = Password::broker('crm_users')->sendResetLink(
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

    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Attempt to reset the user's password using the correct broker
        $status = Password::broker('crm_users')->reset(
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
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:CRM_users,username',
            'email' => 'required|email|max:255|unique:CRM_users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Step 2: Check if this is the first user being created
        $isFirstUser = User::count() === 0;

        // Step 3: Determine the role (first user is Admin, others are Sales by default)
        $role = $isFirstUser ? 'Admin' : 'BD';

        // Step 4: Create the user and hash the password
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // Step 5: Return a success response
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Return a success response with the user details
            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            // If user not found or other error
            return response()->json([
                'message' => 'Error retrieving user',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Step 1: Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => "required|string|max:100|unique:CRM_users,username,$id",
            'email' => "required|email|max:255|unique:CRM_users,email,$id",
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            // Step 2: Find the user by ID
            $user = User::findOrFail($id);

            // Step 3: Update the user's details
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;

            // Step 4: Only update the password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Step 5: Save the changes
            $user->save();

            // Step 6: Return a success response
            return response()->json([
                'message' => 'User updated successfully',   
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            // Handle any errors that occur
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
