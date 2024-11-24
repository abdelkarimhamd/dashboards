<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use App\Models\Pluser;
use App\Models\ProjectDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
class PLUsersController extends Controller
{
    public function getregister() {
       
        $isAdmin = Pluser::count() === 0;
    
        return response()->json([
          'isAdmin' => $isAdmin
        ], Response::HTTP_OK);
      }
    
      public function register(Request $request) {
        // Check if the username already exists
        $existingUser = Pluser::where('username', $request->input('username'))->first();
        if ($existingUser) {
          return response()->json([
            'message' => 'Username already exists. Please choose a different username.'
          ], Response::HTTP_CONFLICT);
        }      
        $role = Pluser::count() === 0 ? 'admin' : $request->input('role', 'user');
        $user = Pluser::create([
          'name' => $request->input('name'),
          'username' => $request->input('username'),
          'password' => Hash::make($request->input('password')),
          'role' => $role,
          'branch' => $request->input('branch'),
          'email' => $request->input('email'),
          
        ]);
    
        $isAdmin = $role === 'admin';
    
      
        return response()->json([
          'user' => $user,
          'isAdmin' => $isAdmin
        ], Response::HTTP_CREATED);
      }
    //   public function login(Request $request) {
        
    //     $user = Pluser::where('username', $request->input('username'))->first();
    //     if (!$user || !Hash::check($request->input('password'), $user->password)) {
    //         return response()->json(['error' => 'Username or password is not matched'], Response::HTTP_UNAUTHORIZED);
    //     }
    //     else{
    //       $token= $user->createToken('morganti')->plainTextToken;
    
    //       return response()->json(['user' => $user, 'token' => $token], 200);
    
    //     }
        
    // }

    public function login(Request $request) {
      $request->validate([
          'username' => 'required|string',
          'password' => 'required|string'
      ]);
  
      $user = Pluser::where('username', $request->username)->first();
  
      if (!$user || !Hash::check($request->password, $user->password)) {
          return response()->json(['error' => 'Invalid username or password'], Response::HTTP_UNAUTHORIZED);
      }
  
      $token = $user->createToken('morganti_PL')->plainTextToken;
      return response()->json(['user' => $user, 'token' => $token], Response::HTTP_OK);
  }

  public function logout(Request $request)
{
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out successfully'], 200);
}
  
public function getProjectManagers(Request $request) {
  $branch = $request->query('branch');
  $query = Pluser::where('role', 'Project Manager');

  if ($branch) {
      $query->where('branch', $branch);
  }

  $projectManagers = $query->get();
  return response()->json($projectManagers);
}
//   public function getAssignedProjects(Request $request) {
//     // Ensure the user is authenticated
//     if (!auth()->check()) {
//         return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
//     }

//     $user = auth()->user();

//     // Optionally, ensure the user has the appropriate role
//     if ($user->role !== 'Project Manager') {
//         return response()->json(['message' => 'Access Denied'], Response::HTTP_FORBIDDEN);
//     }

//     // Fetch projects that are assigned to the authenticated user by both ID and username
//     $projects = ProjectDetail::where('ProjectManagerId', $user->id)
//                               ->orWhere('ProjectManager', $user->username)
//                               ->get(['ProjectName', 'id']);  // Assuming you have 'ProjectManagerId' column

//     return response()->json([
//         'projects' => $projects
//     ], Response::HTTP_OK);
// }
public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Attempt to reset the user's password using the correct broker
        $status = Password::broker('plusers')->reset(
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
        $status = Password::broker('plusers')->sendResetLink(
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


public function getAllProjects()
{
    try {
        $projects = ProjectDetail::all('ProjectName', 'ProjectID'); 
        return response()->json(['projects' => $projects], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch projects', 'message' => $e->getMessage()], 500);
    }
}


}
