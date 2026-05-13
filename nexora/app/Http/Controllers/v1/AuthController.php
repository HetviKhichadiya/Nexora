<?php

namespace App\Http\Controllers\v1;

use App\Helpers\CommonHelpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Signup user
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $email = $request->email;
        $password = $request->password;
        $name= $request->name;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $status = $request->status;
        $user_type = $request->user_type;

        // Check email exists in users table
        $check_email = CommonHelpers::emailExists($email);
        if ($check_email) {
            return response()->json(['message' => 'Email already exists'], 400);
        }

        $create_user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => $status,
            'user_type' => $user_type,
        ]);

        if(!$create_user) {
            return response()->json(['message' => 'User registration failed'], 500);
        }

        return response()->json(['message' => 'User registered successfully']);
    }
}
