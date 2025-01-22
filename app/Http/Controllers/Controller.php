<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Redis;

abstract class Controller
{
    //
    public function list()  {
        $user=User::all();
        return $user;
    }
    
}
