<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GccRouteProtect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $roles=array(1,6,8,2,27,28,6,34,25,7,9,10,11,12);
        $user_court_info=DB::table('doptor_user_access_info')->where('common_login_user_id', Auth::user()->common_login_user_id)->select('court_type_id','role_id', 'court_id')->first();
        $currentrole=$user_court_info->role_id;
        if (in_array($currentrole, $roles))
        {
            // dd($currentrole);
            return $next($request);
        }
        else
        {
            
            return response()->json('Sorry return back');
        }
       
    }
}
