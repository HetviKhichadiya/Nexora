<?php

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Check email exists in users table
 */
function emailExists($email)
{
    $check_email = User::where('email', $email)->exists();
    return $check_email;
}

/**
 * Encrypt data using Laravel's Crypt facade
 */
function encryptData($data)
{
    return Crypt::encryptString(json_encode($data));
}

/**
 * Decrypt data using Laravel's Crypt facade
 */
function decryptData($encrypted)
{
    try {
        $decrypted = Crypt::decryptString($encrypted);
        return json_decode($decrypted, true);
    } catch (\Exception $e) {
        return null; // Return null if decryption fails
    }
}

/**
 * Base JSON response handler
 */
function baseJsonResponse(array $payload, int $status_code, bool $encrypt = true)
{

    $request   = request();
    $userAgent = strtolower($request->header('User-Agent'));

    // If Postman, send plain JSON; else, send encrypted JSON
    if (str_contains($userAgent, 'postman')) {
        $response = response()->json($payload, $status_code);
    } else {
        $encrypted = $encrypt ? ['data' => encryptData($payload)] : $payload;
        $response  = response()->json($encrypted, $status_code);
    }

    // Add CORS headers
    return $response
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, DELETE, PATCH')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
}

/**
 * Success response
 */
function successResponse($status_code = 200, $data = [], $pagination = [])
{
    $res = [];

    if (!empty($data)) {
        $res['data'] = $data;

        if ($pagination) {
            $res['meta'] = metaResponse(
                    $pagination['total'] ?? 0,
                    $pagination['next_cursor'] ?? null,
                    $pagination['previous_cursor'] ?? null,
                    $pagination['limit'] ?? 0
                );
        } else {
            $res['meta'] = ['request_id' => (string) Str::uuid()];
        }
    } else {
        $res['data'] = [];
        $res['meta'] = (object) [];
    }

    return baseJsonResponse($res, $status_code);
}

/**
 * Error response
 */
function errorResponse(int $status_code = 400, string $error_code = "UNKNOWN_ERROR", string|array $message = "", array $data = [])
{

    if ($status_code < 100 || $status_code > 599) {
        $status_code = 400;
    }

    // Normalize array messages to a readable string
    if (is_array($message)) {
        // Flatten and pick the first non-empty string; fallback to JSON encoding
        $first = collect($message)->flatten()->filter(fn($m) => !empty($m))->first();
        $message = is_string($first) ? $first : json_encode($message);
    }

    $res = [
        'error' => [
            'code'    => $error_code,
            'message' => $message
        ],
    ];

    if (!empty($data)) {
        $res['error']['data'] = $data;
    }

    return baseJsonResponse($res, $status_code);
}

function metaResponse($total = 0, $nextCursor = null, $prevCursor = null, $limit = 0)
{
    return [
        'total' => (int) $total,
        'next_cursor' => !empty($nextCursor) ? (int) $nextCursor : '',
        'previous_cursor' => !empty($prevCursor) ? (int) $prevCursor : '',
        'limit' => (int) $limit,
        'request_id' => (string) Str::uuid()
    ];
}