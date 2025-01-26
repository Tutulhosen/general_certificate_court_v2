<?php

namespace App\Http\Controllers\GccApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //get login data
    public function get_log_in(Request $request){
        $auth_data = json_decode($request->jss, true);
        
        // Store data in session
        session(['auth_data' => $auth_data]);
    }
}
