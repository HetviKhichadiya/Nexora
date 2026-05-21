<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    protected function user()
    {
        return auth()->user();
    }

    protected function user_id()
    {
        return auth()->id();
    }
}
