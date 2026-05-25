<?php

namespace App\Http\Controllers;

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

    protected function user_type()
    {
        return auth()->user()->user_type;
    }
}
