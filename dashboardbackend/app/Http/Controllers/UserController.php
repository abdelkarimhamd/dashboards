<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
class UserController extends Controller
{
  public function getregister() {
    
    $isAdmin = User::count() === 0;

    return response()->json([
      'isAdmin' => $isAdmin
    ], Response::HTTP_OK); 
  }

  public function register(Request $request) {
    
  //   $request->validate([
  //     'name' => 'required|string|max:255',
  //     'username' => 'required|string|max:255|unique:users',
  //     'password' => 'required|string|min:8|confirmed',
  //     'role' => 'required|string',
  //     'department' => 'nullable|string', // department is optional
  // ]);


    $existingUser = User::where('username', $request->input('username'))->first();
    if ($existingUser) {
      return response()->json([
        'message' => 'Username already exists. Please choose a different username.'
      ], Response::HTTP_CONFLICT);
    }

    
    $role = User::count() === 0 ? 'admin' : $request->input('role', 'user'); // Use 'user' as default if not specified

    
    $user = User::create([
      'name' => $request->input('name'),
      'username' => $request->input('username'),
      'password' => Hash::make($request->input('password')),
      'role' => $role,
      'department' => $role === 'Department Head' ? $request->input('department') : null,
      'branch' => $request->input('branch'),
      'email' => $request->input('email'),
      
    ]);

    $isAdmin = $role === 'admin';

   
    return response()->json([
      'user' => $user,
      'isAdmin' => $isAdmin
    ], Response::HTTP_CREATED);
  }
  public function login(Request $request) {
    $request->validate([
      'username' => 'required',
      'password' => 'required',
  ]);
    $user = User::where('username', $request->input('username'))->first();
    if (!$user || !Hash::check($request->input('password'), $user->password)) {
        return response()->json(['error' => 'Username or password is not matched'], Response::HTTP_UNAUTHORIZED);
    }
    else{
      $token= $user->createToken('morganti')->plainTextToken;

      return response()->json(['user' => $user, 'token' => $token], 200);

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
        $status = Password::broker('users')->reset(
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
        $status = Password::broker('users')->sendResetLink(
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
public function logout(Request $request)
{
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out successfully'], 200);
}


public function getProjectManagerNames(Request $request) {
  $branch = $request->query('branch');
  $query = User::where('role', 'Project Manager');

  if ($branch) {
      $query->where('branch', $branch);
  }

  $projectManagers = $query->get();
  return response()->json($projectManagers);
}
// public function getProjectManagerNames() {
//   $projectManagers = User::where('role', 'Project Manager')->get();
//   return response()->json($projectManagers);
// }

}

