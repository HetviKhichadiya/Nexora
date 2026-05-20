<?php

namespace App\Http\Controllers\v1;

use App\Constants\HttpStatusConstant;
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
        $first_name = $request->first_name ?? '';
        $last_name = $request->last_name ?? '';
        $status = $request->status ?? 1;
        $user_type = $request->user_type ?? 1;

        // Check email exists in users table
        $check_email = emailExists($email);
        if ($check_email) {
            return errorResponse(HttpStatusConstant::BAD_REQUEST, 'EMAIL_EXISTS', 'Email already exists');
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
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to create user');
        }
        return successResponse(HttpStatusConstant::CREATED, $create_user);
    }
}
