<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        $users = User::all();

        if ($users) {
            return response()->json(["users" => $users]);
        }

        return response()->json(["message" => "No users found"], 404); // Optional: handle no users case
    }

}
