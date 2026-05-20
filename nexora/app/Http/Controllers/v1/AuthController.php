<?php

namespace App\Http\Controllers\v1;

use App\Constants\HttpStatusConstant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        try{
            $email = $request->email;
            $password = $request->password;
            $name = $request->name;
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

            if (!$create_user) {
                return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to create user');
            }
            return successResponse(HttpStatusConstant::CREATED, $create_user);
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'An error occurred during signup');
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try{
            $user = User::find($id);
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            $user->delete();
            return successResponse(HttpStatusConstant::OK, 'User deleted successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'An error occurred while deleting user');
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try{
            $email = $request->email;
            $password = $request->password;
            $user = User::where('email', $email)->first();
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            $password_match = Hash::check($password, $user->password);
            if (!$password_match) {
                return errorResponse(HttpStatusConstant::UNAUTHORIZED, 'UNAUTHORIZED', 'Invalid credentials');
            }
            //generate token for further authentication
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->update(['last_login_at' => now()]);
            return successResponse(HttpStatusConstant::OK, $token);
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try{
            $user = auth()->user();
            $logout = $user->currentAccessToken()->delete();
            if(!$logout) {
                return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to logout');
            }
            $user->currentAccessToken()->delete();
            return successResponse(HttpStatusConstant::OK, 'Logged out successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'An error occurred while logging out');
        }
    } 
}
