<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Traits\TokenVerificationTrait;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CdapUserManagementController;


class LandingPageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use TokenVerificationTrait;
    public function index()
    {

        // $token = $this->verifySiModuleToken('WEB');
       
        // if ($token) {
            
            
        //     $idpData = self::getSiModule();
           
        //     // dd($idpData['idp_url'] . 'api/v1/get/requisition/data');
        //     $curl = curl_init();
        //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => $idpData['idp_url'] . 'api/v1/get/requisition/data',
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //         CURLOPT_POSTFIELDS => [
                    
        //         ],
        //         CURLOPT_HTTPHEADER => array(
        //             'Accept: application/json',
        //             "Authorization: Bearer $token",
        //             "secrate_key: common-court-key"
        //         ),
        //     ));

        //     $response = curl_exec($curl);
           
        //     curl_close($curl);
          
            
        //     $data['gcc_role']=json_decode($response);
            
        //     return $data;
        // }
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status', 1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status', 1)->get();
        // return $data;
        return view('publicHomeH')->with($data);
    }

    public function policy(){
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status',1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status',1)->get();
        // return $data;
        return view('policy')->with($data); 
    }

    public function show_log_in_page(Request $request)
    {
        // $cookie_doptor=$request->cookie('_ndortor');
        // if(isset($cookie_doptor))
        // {
        //     Cookie::queue(Cookie::forget('_ndortor'));
        // }
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status', 1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status', 1)->get();
        // return $data;
        return view('login')->with($data);
    }

    public function process_map_view()
    {
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status', 1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status', 1)->get();
        return view('process_map_view')->with($data);
    }

    public function cprc_home_page()
    {

        return view('crpc_home_page_details');
    }

    public function logout()
    {
        
        Auth::logout();
        $url=getCommonModulerBaseUrl();

        $callbackurl = url($url);
        $zoom_join_url = DOPTOR_ENDPOINT() . '/logout?' . 'referer=' . base64_encode($url."custom-logout");
        return redirect($zoom_join_url); 
    }
    public function cslogout()
    {
       
        Auth::logout();
        $url=getCommonModulerBaseUrl();

        $callbackurl = url($url);
        $zoom_join_url = DOPTOR_ENDPOINT() . '/logout?' . 'referer=' . base64_encode($url."custom-logout");
        return redirect($zoom_join_url); 
    }
   
    public function crawling()
    {
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status', 1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status', 1)->get();
        $data['link']=mygov_endpoint().'/profile';   
        return view('cdap_nid_error')->with($data);
    }
    public function email_error()
    {
        $data['short_news'] = News::orderby('id', 'desc')->where('news_type', 1)->where('status', 1)->get();
        $data['big_news'] = News::orderby('id', 'desc')->where('news_type', 2)->where('status', 1)->get();
        $data['link']=mygov_endpoint().'/profile';   
        return view('cdap_email_error')->with($data);
    }

    public function home_redirct(){
        $url=getCommonModulerBaseUrl();
        $callbackurl = url($url.'doptor/court');
        $zoom_join_url = DOPTOR_ENDPOINT() . '/logout?' . 'referer=' . base64_encode($callbackurl);
        // return redirect($zoom_join_url); 
        Auth::logout();
        return redirect( $callbackurl);
        

    }
    
    
    
}
