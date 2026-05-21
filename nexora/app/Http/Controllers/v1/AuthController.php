<?php

namespace App\Http\Controllers\v1;

use App\Constants\EmailConstant;
use App\Constants\HttpStatusConstant;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    /**
     * Signup user
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
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

            $subject = 'Welcome to Nexora';
            $email_content = 'Thank you for signing up with Nexora. We are excited to have you on board! If you have any questions or need assistance, feel free to reach out to our support team.';
            $button_url = 'https://nexora.com'; //temporary url for dashboard page
            $button_text = 'Visit Nexora';
            $from_email = EmailConstant::FROM_EMAIL;
            $mail_data = [
                'form_type' => $subject,
                'greeting' => "Hi {$create_user->first_name} {$create_user->last_name},",
                'email_data' => [
                    'body_text'  => $email_content,
                    'footer_txt' => "Thanks for using Nexora!",
                    'button_url' => $button_url,
                    'button_text' => $button_text,
                    'product_name' => 'Team Nexora',
                ],
                'result' => [],
                'view' => 'EmailTemplate',
                'from_email' => $from_email,
                'from_name' => "Team Nexora",
            ];

            SendEmailJob::dispatch($email, $mail_data);
            if (!$create_user) {
                return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to create user');
            }
            return successResponse(HttpStatusConstant::CREATED, $create_user);
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id = null)
    {
        try{
            if(!$id) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'INVALID_ID', 'Invalid user ID');
            }
            $user = User::find($id);
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            $user->delete();
            return successResponse(HttpStatusConstant::OK, 'User deleted successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
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
            //need to chech email verification for login
            if(!$user->email_verified_at){
                return errorResponse(HttpStatusConstant::UNAUTHORIZED, 'EMAIL_NOT_VERIFIED', 'Email not verified');
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
            return successResponse(HttpStatusConstant::OK, 'Logged out successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    } 

    /**
     * Permanently delete user
     */
    public function deleteForeverUser($id = null)
    {
        try{
            if(empty($id)) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'INVALID_ID', 'Invalid user ID');
            }
            $user = User::withTrashed()->find($id);
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            $user->forceDelete();
            return successResponse(HttpStatusConstant::OK, 'User deleted permanently');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        try{
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            if(!hash::check($request->token, $user->password_reset_token)) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'INVALID_TOKEN', 'Invalid token');
            }
            $user->update(['password' => Hash::make($request->password), 'password_reset_token' => null]);
            return successResponse(HttpStatusConstant::OK, 'Password reset successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        try{
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            $token = Str::random(64);
            $hashed_token = Hash::make($token);
            $user->update(['remember_token' => $hashed_token]);
            //send mail for forgot password
            $subject = 'Reset Your Password';
            $email_content = 'You have requested to reset your password. Please click the button below to reset your password.';
            $button_url = 'https://nexora.com/reset-password?token=' . $token; //temporary url for reset password page
            $button_text = 'Reset Password';
            $from_email = EmailConstant::FROM_EMAIL;
            $mail_data = [
                'form_type' => $subject,
                'greeting' => "Hi {$user->first_name} {$user->last_name},",
                'email_data' => [
                    'body_text'  => $email_content,
                    'footer_txt' => "Thanks for using Nexora!",
                    'button_url' => $button_url,
                    'button_text' => $button_text,
                    'product_name' => 'Team Nexora',
                ],
                'result' => [],
                'view' => 'EmailTemplate',
                'from_email' => $from_email,
                'from_name' => "Team Nexora",
            ];
            SendEmailJob::dispatch($email, $mail_data);
            return successResponse(HttpStatusConstant::OK, 'Password reset link sent to your email');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Send email verification notification
     */
    public function sendVerificationEmail($email = null)
    {
        try{
            if(empty($email)) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'EMAIL_REQUIRED', 'Email is required');
            }
            $user_email = $email;
            $user = User::where('email', $user_email)->first();
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }

            //send mail for email verification
            $verification_token = Str::random(64);
            $hashed_token = Hash::make($verification_token);
            $user->update(['email_verification_token' => $hashed_token]);
            $subject = 'Email Verification';
            $email_content = 'Please click the button below to verify your email address.';
            $button_url = 'https://nexora.com/verify-email/'.$verification_token; //temporary url for email verification page
            $button_text = 'Verify Email';
            $from_email = EmailConstant::FROM_EMAIL;
            $mail_data = [
                'form_type' => $subject,
                'greeting' => "Hi {$user->first_name} {$user->last_name},",
                'email_data' => [
                    'body_text'  => $email_content,
                    'footer_txt' => "Thanks for using Nexora!",
                    'button_url' => $button_url,
                    'button_text' => $button_text,
                    'product_name' => 'Team Nexora',
                ],
                'result' => [],
                'view' => 'EmailTemplate',
                'from_email' => $from_email,
                'from_name' => "Team Nexora",
            ];
            SendEmailJob::dispatch($user_email, $mail_data);
            return successResponse(HttpStatusConstant::OK, 'Verification email sent successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'token' => 'required|string',
        ]);
        try{
            $user = User::where('email', $request->email)->first();
            $request_token = $request->token;
            if (!$user) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'USER_NOT_FOUND', 'User not found');
            }
            if($user->email_verified_at){
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'ALREADY_VERIFIED', 'Email already verified');
            }
            if (!$user->email_verification_token) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'INVALID_TOKEN', 'Invalid token');
            }
            if (!Hash::check($request_token, $user->email_verification_token)) {
                return errorResponse(HttpStatusConstant::BAD_REQUEST, 'INVALID_TOKEN', 'Invalid token');
            }
            $user->update(['email_verified_at' => now(), 'email_verification_token' => null]);
            return successResponse(HttpStatusConstant::OK, 'Email verified successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', $e->getMessage());
        }
    }
}
