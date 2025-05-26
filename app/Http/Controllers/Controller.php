<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // <-- PENTING: ini yang menyediakan middleware()

class Controller extends BaseController // <-- PENTING: meng-extend BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}