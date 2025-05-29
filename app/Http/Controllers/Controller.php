<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Pastikan ini ada

class Controller extends BaseController // Dan extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}