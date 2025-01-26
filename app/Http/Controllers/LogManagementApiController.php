<?php

namespace App\Http\Controllers;

use App\Models\GccAppeal;
use App\Repositories\AppealRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogManagementApiController extends Controller
{
    public function index(Request $request)
    {
        $requestData = $request->all();
        $userInfo = $requestData['body_data'];
        $case_no=$userInfo['case_no'];
        $cases = DB::table('gcc_appeals');
        if (!empty($case_no)) {
            $cases=$cases->where('gcc_appeals.case_no', 'LIKE', '%'.$case_no.'%');
        }
        $cases=$cases->orderBy('id', 'DESC')
            ->join('court', 'gcc_appeals.court_id', '=', 'court.id')
            ->join('division', 'court.division_id', '=', 'division.id')
            ->join('district', 'court.district_id', '=', 'district.id')
            ->join('upazila', 'gcc_appeals.upazila_id', '=', 'upazila.id')
            ->select('gcc_appeals.*', 'court.court_name', 'division.division_name_bn', 'district.district_name_bn', 'upazila.upazila_name_bn')->get();

        return ['success' => true, "data" => $cases];
    }
    public function log_index_single(Request $request, $id = null)
    {
        $requestData = $request->all();
        $userInfo = $requestData['body_data'];  
        $user = $userInfo['user'];
        $office_id = $userInfo['office_id'];
        $officeInfo = $userInfo['officeInfo'];
        $roleId = $userInfo['roleID'];
        $id = $userInfo['id'];
        // return ['msg'=> $userInfo];
        $data = AppealRepository::getAllAppealInfoForApi($id, $office_id, $roleId);
        // return  ['ms'=> $data]; 
        $info = DB::table('gcc_appeals')
            ->join('court', 'gcc_appeals.court_id', '=', 'court.id')
            ->join('division', 'court.division_id', '=', 'division.id')
            ->join('district', 'court.district_id', '=', 'district.id')
            ->join('upazila', 'gcc_appeals.upazila_id', '=', 'upazila.id')
            ->select('gcc_appeals.*', 'court.court_name', 'division.division_name_bn', 'district.district_name_bn', 'upazila.upazila_name_bn')
            ->where('gcc_appeals.id', '=',  $id)
            ->first();

        $data['info'] = $info;
        $data['page_title'] = 'মামলার কার্যকলাপ নিরীক্ষার বিস্তারিত তথ্য';
        // return $data;
        $data['apepal_id'] = encrypt($id);
        $case_details = DB::table('gcc_log_book')->where('appeal_id', '=', $id)->orderBy('id', 'desc')->get();
        $data['case_details'] = $case_details;
        return ['success' => true, "data" => $data];
    }

    public function log_details_single_by_id(Request $request)
    {
        $requestData = $request->all();
        $userInfo = $requestData['body_data'];
        $id = $userInfo['id'];
        $log_details_single_by_id = DB::table('gcc_log_book')->where('id', $id)->first();
        if ($log_details_single_by_id->case_basic_info) {
            $data['case_basic_info'] = json_decode($log_details_single_by_id->case_basic_info);
            $data['appeal_data_from_gcc_appeal'] = DB::table('gcc_appeals')
                ->join('court', 'gcc_appeals.court_id', 'court.id')
                ->where('gcc_appeals.id', $log_details_single_by_id->appeal_id)
                ->select('gcc_appeals.division_name', 'gcc_appeals.district_name', 'gcc_appeals.upazila_name', 'court.court_name')
                ->first();
        } else {
            $data['case_basic_info'] = null;
        }

        $data['log_details_single_by_id'] = $log_details_single_by_id;
        $data['page_title'] = 'মামলার বিস্তারিত তথ্য';

        return ['success' => true, "data" => $data];
    }
    public function create_log_pdf(Request $request, $id = null)
    {

        $id = decrypt($id);
        $requestData = $request->all();
        $userInfo = $requestData['body_data'];
        $office_id = $userInfo['office_id'];
        $roleId = $userInfo['roleID'];
        $data = AppealRepository::getAllAppealInfoForApi($id, $office_id, $roleId);

        $info = DB::table('gcc_appeals')
            ->join('court', 'gcc_appeals.court_id', '=', 'court.id')
            ->join('division', 'court.division_id', '=', 'division.id')
            ->join('district', 'court.district_id', '=', 'district.id')
            ->join('upazila', 'gcc_appeals.upazila_id', '=', 'upazila.id')
            ->select('gcc_appeals.*', 'court.court_name', 'division.division_name_bn', 'district.district_name_bn', 'upazila.upazila_name_bn')
            ->where('gcc_appeals.id', '=',  $id)
            ->first();

        $data['info'] = $info;
        $data['page_title'] = 'মামলার কার্যকলাপ নিরীক্ষার বিস্তারিত তথ্য';
        // return $data;
        $data['apepal_id'] = encrypt($id);
        $case_details = DB::table('gcc_log_book')->where('appeal_id', '=', $id)->orderBy('id', 'desc')->get();
        $data['case_details'] = $case_details;

        return ['success' => true, "data" => $data];
    }
}