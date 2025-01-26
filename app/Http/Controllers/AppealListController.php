<?php

/**
 * Created by PhpStorm.
 * User: ashraful
 * Date: 12/12/17
 * Time: 12:56 PM
 */

namespace App\Http\Controllers;


use Mpdf\Mpdf;
use App\Models\GccAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AppealRepository;
use App\Traits\TokenVerificationTrait;
use Illuminate\Support\Facades\Session;
use App\Repositories\AppealListRepository;
use App\Services\ShortOrderTemplateServiceUpdated;

class AppealListController extends Controller
{
    use TokenVerificationTrait;
    public $permissionCode = 'certificateList';

    public function index(Request $request)
    {

        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();
        $userRole = $user_court_info->role_id;

        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        if ($userRole == 6) {
            // $results=[];
            $page_title = 'চলমান আপিল মামলার তালিকা';
        } else {
            $page_title = 'চলমান মামলার তালিকা';
        }
        $results = AppealListRepository::RoleWaysRunningAppealList();

        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function all_case(Request $request)
    {
        // dd(1);
        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();


        $userRole = $user_court_info->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $results = AppealListRepository::RoleWaysAllAppealList();
        $page_title = 'সকল মামলার তালিকা';
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function pending_list(Request $request)
    {

        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();
        $userRole = $user_court_info->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $results = AppealListRepository::RoleWaysPendingAppealList();
        if ($user_court_info->role_id == 27 || $user_court_info->role_id == 28) {

            $page_title = 'গ্রহণের জন্য অপেক্ষমান রিকুইজিশনের তালিকা';
        } else {
            $page_title = 'গ্রহণের জন্য অপেক্ষমান মামলার তালিকা';
        }

        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function closed_list(Request $request)
    {
        $results = AppealListRepository::RoleWaysClosedAppealList();
        // dd($request);
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();


        $userRole = $user_court_info->role_id;

        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = 'নিষ্পত্তিকৃত মামলার তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function certified_request_list(Request $request)
    {
        if (globalUserInfo()->role_id == 6) {
            $result = GccAppeal::whereIn('appeal_process_status', ['DM_FOR_CERTIFICATE_COPY'])->where('district_id', user_district()->id);
        }
        if (globalUserInfo()->role_id == 11) {
            $result = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RRDC'])->where('district_id', user_district()->id);
        }
        if (globalUserInfo()->role_id == 12) {
            $result = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RK'])->where('district_id', user_district()->id);
        }
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            // dd(1);
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = $result->whereBetween('case_date', [$dateFrom, $dateTo]);
        }
        if (!empty($_GET['case_no'])) {
            $results->where('case_no', 'like', '%' . $_GET['case_no'] . '%')->orWhere('manual_case_no', 'like', '%' . $_GET['case_no'] . '%');
        }
        $results = $result->paginate(10);

        // dd($results);
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();

        $userRole = $user_court_info->role_id;
        // dd($userRole);
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }


        if (globalUserInfo()->role_id == 6) {
            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['DM_FOR_CERTIFICATE_COPY'])->where('district_id', user_district()->id)->get();
        }
        if (globalUserInfo()->role_id == 11) {

            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RRDC'])->where('district_id', user_district()->id)->get();
        }
        if (globalUserInfo()->role_id == 12) {
            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RK'])->where('district_id', user_district()->id)->get();
        }


        $certify_copy_count = count($data['cartify_copy_req_data']);

        $page_title = 'সার্টিফিকেট কপির জন্য আবেদনের তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        // dd($page_title);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole', 'certify_copy_count'));
    }

    public function certify_copy_list(){

        $data['list']=DB::table('certify_copy')->where('court_id', globalUserInfo()->court_id)->orderBy('id','DESC')->get();
        $data['page_title']='সার্টিফিকেট কপির আবেদন লিস্ট';

        return view('citizenappealView.certiry_copy_list')->with($data);
        
    }



    public function certify_applicent_form($id){

        $data['list']=DB::table('certify_copy')->where('id', $id)->first();
        $data['page_title']='সার্টিফিকেট কপির আবেদন পত্র';
        return view('citizenappealView.certify_application_form')->with($data);
        
    }

    //sent to deputy collector
    public function certify_copy_action_dc($id){
        
        $data['list']=DB::table('certify_copy')->where('id', $id)->first();
        $data['page_title']='সার্টিফিকেট কপির আবেদন ';
       
        
        return view('appeal_process.dc_action_form')->with($data);
        
    }

    
    
    public function sent_to_deputy_collector(Request $request)
    {
       
        if (globalUserInfo()->role_id == 6) {
            DB::table('gcc_appeals')->where('id', $request->appeal_id)->update([
                'appeal_process_status' => 'SENT_TO_RRDC'
            ]);
            DB::table('certify_copy_process_log')->insert([
                'orderer_role_id' => globalUserInfo()->role_id,
                'appeal_id' => $request->appeal_id,
                'application_id' => $request->certify_id,
                'order_text' => $request->description,
                'appeal_process_status' => 'SENT_TO_RRDC',
            ]);
            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['DM_FOR_CERTIFICATE_COPY'])->where('district_id', user_district()->id)->get();
            $certify_copy_count = count($data['cartify_copy_req_data']);
        }

      

        return response([
            'status' => true,
        ]);
    }

    public function sent_to_record_keeper(Request $request)
    {
       
        if (globalUserInfo()->role_id == 11) {
            DB::table('gcc_appeals')->where('id', $request->appeal_id)->update([
                'appeal_process_status' => 'SENT_TO_RK'
            ]);
            DB::table('certify_copy_process_log')->insert([
                'orderer_role_id' => globalUserInfo()->role_id,
                'appeal_id' => $request->appeal_id,
                'application_id' => $request->certify_id,
                'order_text' => $request->description,
                'appeal_process_status' => 'SENT_TO_RK',
            ]);
            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['DM_FOR_CERTIFICATE_COPY'])->where('district_id', user_district()->id)->get();
            $certify_copy_count = count($data['cartify_copy_req_data']);
        }

      

        return response([
            'status' => true,
        ]);
    }

    //sent to drecord keeper
    public function certify_copy_action_rrdc($id){
            
        $data['list']=DB::table('certify_copy')->where('id', $id)->first();
        $data['page_title']='সার্টিফিকেট কপির আবেদন ';
    
        
        return view('appeal_process.rrdc_action_form')->with($data);
        
    }

    

    //cancel by deputy collector
    public function cancel_by_rrdc($id)
    {
        $data['list']=DB::table('certify_copy')->where('id', $id)->first();
        $data['page_title']='সার্টিফিকেট কপির আবেদন ';
       
        
        return view('appeal_process.cancel_by_rrdc')->with($data);
        
    }

    public function cancel_certify_copy_by_rrdc(Request $request)
    {
       
        if (globalUserInfo()->role_id == 11) {
            DB::table('gcc_appeals')->where('id', $request->appeal_id)->update([
                'appeal_process_status' => 'CANCEL_CERTIFY_COPY'
            ]);
            DB::table('certify_copy_process_log')->insert([
                'orderer_role_id' => globalUserInfo()->role_id,
                'appeal_id' => $request->appeal_id,
                'application_id' => $request->certify_id,
                'order_text' => $request->description,
                'appeal_process_status' => 'CANCEL_CERTIFY_COPY',
            ]);
            $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RRDC'])->where('district_id', user_district()->id)->get();
            $certify_copy_count = count($data['cartify_copy_req_data']);
        }

      

        return response([
            'status' => true
        ]);
    }

    //fee for nothi
    public function fee_for_nothi($id)
    {
        $id = decrypt($id);
        $get_appeal_id=DB::table('certify_copy')->where('id', $id)->first();
        // dd($get_appeal_id->id);
        $data['appeal'] = GccAppeal::findOrFail($get_appeal_id->appeal_id);
        $data['certify_id'] = $get_appeal_id->id;
        if ($data['appeal']->appeal_process_fee_status == 'SENT_TO_DEFAULTER') {
            return redirect()->back()->with('error', 'এতোমধ্যে ফি প্রদানের জন্য খাতককে বলা হয়েছে');
        } else {
            $data['user_info'] = AppealRepository::getAllAppealInfo($get_appeal_id->appeal_id);
            // dd( $data['user_info']['defaulterCitizen']['citizen_name']);

            $data['page_title'] = 'সার্টিফিকেট কপির জন্য ফি';
            return view('generalCertificateAppeal.nothiFee')->with($data);
        }
    }
    public function sent_to_defaulter(Request $request)
    {
        
        $appeal_id = $request->input('appeal_id');
        $total_page = $request->input('total_page');
        $cost_total = $request->input('cost_total');
   
        DB::table('gcc_appeals')->where('id', $appeal_id)->update([
            'appeal_process_fee_status' => 'SENT_TO_DEFAULTER'
        ]);
        DB::table('certify_copy_process_log')->insert([
            'orderer_role_id' => globalUserInfo()->role_id,
            'appeal_id' => $request->appeal_id,
            'application_id' => $request->certify_id,
            'order_text' => $request->description,
            'appeal_process_status' => 'SENT_TO_DEFAULTER',
        ]);

        DB::table('certify_copy')->where('appeal_id', $appeal_id)->update([
            'total_page' => $total_page,
            'cost_total' => $cost_total,
            'certify_copy_fee' => 'REQUEST_FOR_FEE',
        ]);

        $token = $this->verifySiModuleToken('WEB');
        $data['appeal_id'] = $appeal_id;
        $data['total_page'] = $total_page;
        $data['cost_total'] = $cost_total;
        
        if ($token) {
            $jsonData = json_encode($data, true);
            $url = getapiManagerBaseUrl() . '/api/v1/certify/copy/payment/data';

            $method = 'POST';
            $bodyData = $jsonData;
            $token = $token;
            $response = makeCurlRequestWithToken_update($url, $method, $bodyData, $token);
            
        }



        return response([
            'status' => true
        ]);
    }

    //sent to adm
    public function sent_to_adm($id){
    
        if (globalUserInfo()->role_id==6) {
            DB::table('gcc_appeals')->where('id', $id)->update([
                'appeal_status' => 'SENT_TO_ASST_ADM',
                'action_required' => 'ASST_ADM',
            ]);
           
        }

 

        return response([
            'status' => true
        ]);

    }

    //sent to adm
    public function sent_nothi_to_adm($id){
    
        if (globalUserInfo()->role_id==27 || globalUserInfo()->role_id==28) {
            DB::table('gcc_appeals')->where('id', $id)->update([
                'appeal_status' => 'SENT_TO_ASST_ADM',
                'action_required' => 'ASST_ADM',
                'is_required_for_nothi' => 2,
            ]);
           
        }

 

        return response([
            'status' => true
        ]);

    }


    public function case_for_appeal_running(Request $request)
    {

        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();
        $userRole = $user_court_info->role_id;

        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = 'চলমান আপিল মামলার তালিকা';

        $results = AppealListRepository::RoleWaysRunningAppealList();

        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function case_for_appeal_all(Request $request)
    {
        // dd(1);
        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();


        $userRole = $user_court_info->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $results = AppealListRepository::RoleWaysAllAppealList();
        $page_title = 'সকল মামলার তালিকা';
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function case_for_appeal_panding(Request $request)
    {

        $date = date($request->date);
        $caseStatus = 1;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();
        $userRole = $user_court_info->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $results = AppealListRepository::RoleWaysPendingAppealList();
        // dd($results);
        if ($user_court_info->role_id == 27 || $user_court_info->role_id == 28) {

            $page_title = 'গ্রহণের জন্য অপেক্ষমান রিকুইজিশনের তালিকা';
        } else {
            $page_title = 'গ্রহণের জন্য অপেক্ষমান মামলার তালিকা';
        }

        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }
    public function case_for_appeal_closed(Request $request)
    {
        $results = AppealListRepository::RoleWaysClosedAppealList();
        // dd($request);
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();


        $userRole = $user_court_info->role_id;

        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = 'নিষ্পত্তিকৃত মামলার তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }

    public function for_nothi(Request $request)
    {
        
        $results = AppealListRepository::NothiRequestAppealList();
        $request_for_nothi =count($results);
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $user_court_info=DB::table('doptor_user_access_info')->where('common_login_user_id', globalUserInfo()->common_login_user_id)->select('court_type_id','role_id', 'court_id')->first();
    

        $userRole = $user_court_info->role_id;
     
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = 'নথির জন্য আবেদনের তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole', 'request_for_nothi'));
    }

    // /generateShortOrderTemplatePDF


    public function generateShortOrderTemplatePDF($id)
    {
        // Get the data from the service
        $shortOrderTemplateList = ShortOrderTemplateServiceUpdated::getShortOrderTemplateListByAppealId(decrypt($id));

        // Initialize mpdf with custom options
        $mpdf = new Mpdf([
            'format' => 'A4',
            'default_font_size' => 12,
            'default_font' => 'kalpurush',
        ]);

        // Set footer with page number and total pages
        $mpdf->SetFooter('Page {PAGENO} of {nb}');  // {PAGENO} is the current page, {nb} is total pages

        // Start buffering the HTML content
        $html = '<h2 style="text-align:center">Short Order Template List</h2>';
        $html .= '<p style="text-align:center;">Total Pages: {nb}</p>';  // Placeholder for total page count

        foreach ($shortOrderTemplateList as $key => $shortOrderTemplate) {
            // Append each template_full to the HTML
            $html .= '<p>' . $shortOrderTemplate->template_full . '</p>';
            $html .= '<hr>';
        }

        // Write HTML content to the PDF
        $mpdf->WriteHTML($html);

        // Output the PDF
        $mpdf->Output();
    }


    public function trial_date_list(Request $request)
    {
        $results = AppealListRepository::RoleWaysTrialAppealList();
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' শুনানির তারিখ হয়েছে এমন মামলার তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }
    public function appeal_with_action_required(Request $request)
    {

        $results = AppealListRepository::RoleWaysActionRequiredAppealList();
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' চলমান মামলাতে পদক্ষেপ নিতে হবে';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results', 'userRole'));
    }

    public function draft_list(Request $request)
    {
        $date = date($request->date);
        $caseStatus = 1;
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $results = AppealListRepository::RoleWaysDraftAppealList();
        $page_title = 'খসড়া মামলার তালিকা';
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }
    public function rejected_list(Request $request)
    {
        $results = GccAppeal::orderby('id', 'desc')->where('appeal_status', 'REJECTED');

        if (globalUserInfo()->role_id == 27 || globalUserInfo()->role_id == 28) {
            $results = $results->where('court_id', globalUserInfo()->court_id);

            if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
                $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
                $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
                $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
            }
            if (!empty($_GET['case_no'])) {
                $results = $results->where('case_no', '=', $_GET['case_no'])->orWhere('manual_case_no', '=', $_GET['case_no']);
            }
        } elseif (globalUserInfo()->role_id == 6) {
            $results = $results->where('district_id', user_district()->id);

            if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
                $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
                $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
                $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
            }
            if (!empty($_GET['case_no'])) {
                $results = $results->where('case_no', '=', $_GET['case_no'])->orWhere('manual_case_no', '=', $_GET['case_no']);
            }
        } elseif (globalUserInfo()->role_id == 34) {
            $results = $results->where('division_id', user_office_info()->division_id);

            if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
                $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
                $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
                $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
            }
            if (!empty($_GET['case_no'])) {
                $results = $results->where('case_no', '=', $_GET['case_no'])->orWhere('manual_case_no', '=', $_GET['case_no']);
            }
        } elseif (globalUserInfo()->role_id == 25) {
            $results = $results->where('updated_by', globalUserInfo()->id);

            if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
                $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
                $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
                $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
            }
            if (!empty($_GET['case_no'])) {
                $results = $results->where('case_no', '=', $_GET['case_no'])->orWhere('manual_case_no', '=', $_GET['case_no']);
            }
        } else {
            if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
                $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
                $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
                $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
            }
            if (!empty($_GET['case_no'])) {
                $results = $results->where('appeal_status', 'REJECTED')->where('case_no', '=', $_GET['case_no']);
            }
        }

        $results = $results->paginate(10);

        $date = date($request->date);
        $caseStatus = 1;
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = 'বর্জনকৃত মামলার তালিকা';
        // return $results;
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }

    public function postponed_list(Request $request)
    {
        $results = GccAppeal::orderby('id', 'desc')
            ->where('appeal_status', 'POSTPONED')
            ->paginate(20);
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            // dd(1);
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = GccAppeal::orderby('id', 'desc')
                ->where('appeal_status', 'POSTPONED')
                ->whereBetween('case_date', [$dateFrom, $dateTo])
                ->paginate(10);
        }
        if (!empty($_GET['case_no'])) {
            $results = GccAppeal::orderby('id', 'desc')
                ->where('appeal_status', 'POSTPONED')
                ->where('case_no', '=', $_GET['case_no'])
                ->paginate(10);
        }
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' মুলতবি মামলার তালিকা';
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }

    public function arrest_warrent_list(Request $request)
    {
        // $results = GccAppeal::orderby('id', 'desc')->where('next_date', date('Y-m-d', strtotime(now())))->paginate(20);
        $results = DB::table('gcc_case_shortdecision_templates')
            ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
            ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
            ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 7)
            ->orderby('gcc_appeals.id', 'DESC')
            ->paginate(10);
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            // dd(1);
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = DB::table('gcc_case_shortdecision_templates')
                ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
                ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
                ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 7)
                ->whereBetween('gcc_appeals.next_date', [$dateFrom, $dateTo])
                ->orderby('gcc_appeals.id', 'DESC')
                ->paginate(10);
        }
        if (!empty($_GET['case_no'])) {
            $results = DB::table('gcc_case_shortdecision_templates')
                ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
                ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
                ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 7)
                ->where('gcc_appeals.case_no', '=', $_GET['case_no'])
                ->orderby('gcc_appeals.id', 'DESC')
                ->paginate(10);
        }
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' গ্রেপ্তারি পরোয়ানা জারি হয়েছে এমন মামলার তালিকা';
        // return $results;
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseWarrentList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }
    public function crock_order_list(Request $request)
    {
        // $results = GccAppeal::orderby('id', 'desc')->where('next_date', date('Y-m-d', strtotime(now())))->paginate(20);
        $results = DB::table('gcc_case_shortdecision_templates')
            ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
            ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
            ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 9)
            ->orderby('gcc_appeals.id', 'DESC')
            ->paginate(20);
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            // dd(1);
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = DB::table('gcc_case_shortdecision_templates')
                ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
                ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
                ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 9)
                ->whereBetween('gcc_appeals.next_date', [$dateFrom, $dateTo])
                ->orderby('gcc_appeals.id', 'DESC')
                ->paginate(10);
        }
        if (!empty($_GET['case_no'])) {
            $results = DB::table('gcc_case_shortdecision_templates')
                ->join('gcc_appeals', 'gcc_case_shortdecision_templates.appeal_id', '=', 'gcc_appeals.id')
                ->select('gcc_case_shortdecision_templates.appeal_id', 'gcc_case_shortdecision_templates.template_name', 'gcc_case_shortdecision_templates.template_full', 'gcc_appeals.*')
                ->where('gcc_case_shortdecision_templates.case_shortdecision_id', 9)
                ->where('gcc_appeals.case_no', '=', $_GET['case_no'])
                ->orderby('gcc_appeals.id', 'DESC')
                ->paginate(10);
        }
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' অস্থাবর সম্পত্তি ক্রোকের আদেশ হয়েছে এমন মামলার তালিকা';
        // return $results;
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseCrockList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }

    public function review_appeal_list(Request $request)
    {
        // $results = GccAppeal::orderby('id', 'desc')->where('next_date', date('Y-m-d', strtotime(now())))->paginate(20);
        $results = GccAppeal::orderby('id', 'desc')
            ->whereIn('appeal_status', ['SEND_TO_DC', 'SEND_TO_DIV_COM', 'SEND_TO_LAB_CM'])
            ->where('court_id', globalUserInfo()->court_id)
            ->where('is_applied_for_review', 1)
            ->paginate(10);
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            // dd(1);
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = GccAppeal::orderby('id', 'desc')
                ->whereIn('appeal_status', ['SEND_TO_DC', 'SEND_TO_DIV_COM', 'SEND_TO_LAB_CM'])
                ->where('court_id', globalUserInfo()->court_id)
                ->where('is_applied_for_review', 1)
                ->whereBetween('gcc_appeals.next_date', [$dateFrom, $dateTo])
                ->paginate(10);
        }
        if (!empty($_GET['case_no'])) {
            $results = GccAppeal::orderby('id', 'desc')
                ->whereIn('appeal_status', ['SEND_TO_DC', 'SEND_TO_DIV_COM', 'SEND_TO_LAB_CM'])
                ->where('court_id', globalUserInfo()->court_id)
                ->where('is_applied_for_review', 1)
                ->where('gcc_appeals.case_no', '=', $_GET['case_no'])
                ->paginate(10);
        }
        // return $results->appealCitizens;
        $date = date($request->date);
        $caseStatus = 1;
        // $userRole=Session::get('userRole');
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            // $gcoUserName=Session::get('userInfo')->username;
            $gcoUserName = globalUserInfo()->username;
        }
        $page_title = ' রিভিউ এর জন্য আবেদন করেছে এমন মামলা ';
        // return $results;
        //return view('appealList.appeallist')->with('date',$date);
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }

    public function appealData(Request $request)
    {
        $usersPermissions = Session::get('userPermissions');
        $appeals = AppealRepository::getAppealListBySearchParam($request);

        return response()->json([
            'data' => $appeals,
            'userPermissions' => $usersPermissions,
            'userName' => Session::get('userInfo')->username,
        ]);
    }

    public function closedList(Request $request)
    {
        $date = date($request->date);
        $caseStatus = 3;
        $userRole = Session::get('userRole');
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = Session::get('userInfo')->username;
        }
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus'));
    }

    public function postponedList(Request $request)
    {
        $date = date($request->date);
        $caseStatus = 2;
        $userRole = Session::get('userRole');
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = Session::get('userInfo')->username;
        }
        return view('appealList.appealCasewiseList', compact('date', 'gcoUserName', 'caseStatus'));
    }


    public function currentAppealEntryList(Request $request)
    {

        /* 
        $date = date($request->date);
        $caseStatus = 1;
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = globalUserInfo()->username;
        }

        $results = GccAppeal::orderby('id', 'desc');

        if (globalUserInfo()->role_id == 28) {
            $results = $results->where('case_entry_type', 'RUNNING')->whereIn('appeal_status', ['SEND_TO_ASST_GCO'])->where('court_id', globalUserInfo()->court_id);
        }

        $results = $results->paginate(10);

        dd($results);
        $page_title = 'পুরাতন চলমান মামলার তালিকা'; */

        $date = date($request->date);
        $caseStatus = 1;
        $userRole = globalUserInfo()->role_id;
        $gcoUserName = '';
        if ($userRole == 'GCO') {
            $gcoUserName = Auth::user()->username;
        }

        $results = GccAppeal::orderby('id', 'desc');
        // dd(globalUserInfo()->role_id, globalUserInfo()->court_id);
        if (globalUserInfo()->role_id == 28) {
            $results = $results->where('case_entry_type', 'RUNNING')->whereIn('appeal_status', ['DRAFT_FOR_RUNNING_ENTRY_CASE'])->where('court_id', globalUserInfo()->court_id);
        }
        if (!empty($_GET['case_no'])) {
            $results = $results->where('case_no', 'LIKE', '%' . $_GET['case_no'] . '%')->orWhere('manual_case_no', 'LIKE', '%' . $_GET['case_no'] . '%');
        }
        if (!empty($_GET['date_start']) && !empty($_GET['date_end'])) {
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_start'])));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $_GET['date_end'])));
            $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
        }

        $results = $results->paginate(10);


        // dd($results);
        $page_title = 'পুরাতন চলমান মামলার তালিকা';

        return view('appealList.currentAppealEntryList', compact('date', 'gcoUserName', 'caseStatus', 'page_title', 'results'));
    }
}
