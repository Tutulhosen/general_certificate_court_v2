<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DoptorUserActiveMiddlewire
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
        $user_court_info=DB::table('doptor_user_access_info')->where('common_login_user_id', Auth::user()->common_login_user_id)->select('court_type_id','role_id', 'court_id')->first();
        $doptor_user_active = Auth::user()->doptor_user_active;
        $doptor_user_flag = Auth::user()->doptor_user_flag;
        $role_id=$user_court_info->role_id;
        $peshkar_active=Auth::user()->peshkar_active;

        $office=DB::table('office')->where('id','=',Auth::user()->office_id)->first();

    

        if($role_id == 28)
         {
            if($office->level==4)
            {

                if($peshkar_active == 1)
                {
                   return $next($request);
                }
                else
                {
                   Auth::logout();
                   
                   return redirect('/disable/certificate_asst/4')->with('disable_messeage_middlewire','আপনাকে ডিজেবেল করে রাখা হয়েছে');
                }
            }
            elseif($office->level == 3)
            {
                if($peshkar_active == 1)
                {
                   return $next($request);
                }
                else
                {
                   Auth::logout();
                   
                   return redirect('/disable/certificate_asst/3')->with('disable_messeage_middlewire','আপনাকে ডিজেবেল করে রাখা হয়েছে');
                }
            }
         }
         
        if ($doptor_user_flag == 0) {
            return $next($request);
        } elseif ($doptor_user_flag == 1) {
            if ($doptor_user_active == 1) {
                return $next($request);
            } else {

                if($role_id == 34)
                {

                    Auth::logout();
                    
                    return redirect('/disable/doptor/user/34')->with('disable_messeage_middlewire','আপনাকে ডিজেবেল করে রাখা হয়েছে');
                }
                else if($role_id == 6)
                {
                    Auth::logout();
                    
                    return redirect('/disable/doptor/user/6')->with('disable_messeage_middlewire','আপনাকে ডিজেবেল করে রাখা হয়েছে');
                }
                else
                {
                    Auth::logout();
                    
                    return redirect('/disable/doptor/user/27')->with('disable_messeage_middlewire','আপনাকে ডিজেবেল করে রাখা হয়েছে');
                }

                
            }
        }
    }
}
