<?php

namespace App\Helpers;

use App\Models\User;

class CommonHelpers
{
    /**
     * Check email exists in users table
     */
    function emailExists($email)
    {
        $check_email = User::where('email', $email)->exists();
        return $check_email;
    }

    /**
     * Success response
     */
    function successResponse($status_code = 200, $data, $pagination = [])
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

        $request = request();
        $userAgent = strtolower($request->header('User-Agent'));
        if (str_contains(strtolower($userAgent), 'postman')) {
            $response = response()->json($res, $status_code);
        } else {
            $response = response()->json(['data' => encryptAes256($res)], $status_code);
        }
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, DELETE, PATCH');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response->send();
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
}