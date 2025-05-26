<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class AdminController extends BaseController
{
    public function home()
    {
        return response()->json([
            'message' => 'Welcome to the Admin Dashboard',
        ]);
    }
}
