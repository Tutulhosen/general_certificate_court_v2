<?php

namespace App\Http\Controllers\MobileApps;

use App\Models\User;
use App\Models\GccAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\AppealRepository;
use App\Repositories\CertificateAsstNoteRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetDataAppsController extends Controller
{
    //count gcc citizen dashboard data
    public function count_gcc_citizen_dashboard_data(Request $request)
    {  

        $get_data = json_decode($request->jss);
        $user = $get_data->auth_user;
        $request_data = $get_data->request_data;
        $data['total_pending_case_count_gcc_citizen'] = $this->total_pending_case_count_gcc_citizen($user, $request_data);
        $data['total_case_count_gcc_citizen'] = $this->total_case_count_gcc_citizen($user, $request_data);
        $data['total_running_case_count_gcc_citizen'] = $this->total_running_case_count_gcc_citizen($user, $request_data);
        $data['total_completed_case_count_gcc_citizen'] = $this->total_completed_case_count_gcc_citizen($user, $request_data);

        return $data;
    }

    public function total_case_count_gcc_citizen($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL','CLOSED'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC');


        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;
        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
     
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }
        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
      
    }
    public function total_running_case_count_gcc_citizen($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
        ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
        ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
        ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
        ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
        ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL'])
        ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
        ->orderBy('gcc_citizens.id', 'DESC');

        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;
        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {

            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }
        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    
    }
    public function total_pending_case_count_gcc_citizen($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['SEND_TO_GCO', 'SEND_TO_ASST_GCO'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC');

        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;
        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
     
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }
        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    }
    
    public function total_completed_case_count_gcc_citizen($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC');

        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;
        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
     
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }
        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
            
    }

    //count org rep dashboard data
    public function count_org_dashboard_data(Request $request)
    {
        $get_data = json_decode($request->jss);
        $user = $get_data->auth_user;
        $request_data = $get_data->request_data;

        $data['total_pending_case_count_applicant'] = $this->total_pending_case_count_applicant($user, $request_data);
        $data['total_case_count_applicant'] = $this->total_case_count_applicant($user, $request_data);
        $data['total_running_case_count_applicant'] = $this->total_running_case_count_applicant($user, $request_data);
        $data['total_completed_case_count_applicant'] = $this->total_completed_case_count_applicant($user, $request_data);

        return $data;
    }

    public function total_case_count_applicant($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('gcc_appeals.organization_routing_number', $user->organization_id) 
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL', 'CLOSED'])
            ->orderBy('id', 'DESC');
            
        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;

        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
     
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }

        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    }
    public function total_running_case_count_applicant($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['ON_TRIAL'])
            ->orderBy('id', 'DESC');
            
        $totalCount = count($appeal_ids_from_db->get());
        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;

        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
        
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }

        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    }
    public function total_pending_case_count_applicant($user, $request_data)
    {

        $page = $request_data->page;
        $limit = $request_data->limit;
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['SEND_TO_GCO', 'SEND_TO_ASST_GCO'])
            ->orderBy('id', 'DESC');

        $totalCount = count($appeal_ids_from_db->get());

        $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page); 
        $data['totalCount'] = $totalCount;
        $caseList=null;

        foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
        
            $ct_info = DB::table('users')
                ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                ->select('gcc_citizens.citizen_name')
                ->first();
            if ($ct_info) {
                $applicant_name= $ct_info->citizen_name;
            }else {
                $applicant_name= null;
            }

            $court_name =  DB::table('court')
            ->where('id', $appeal_ids_from_db_single->court_id)
            ->first()->court_name;
            
            $caseList[] = [
                'id' => $appeal_ids_from_db_single->id,
                'applicant_name' => $applicant_name,
                'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                'case_no' => $appeal_ids_from_db_single->case_no,
                'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                'court_name' => $court_name,
                'next_date' => $appeal_ids_from_db_single->next_date,
            ];
        }

        $data['caseList'] = $caseList;
        return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    }
    public function total_completed_case_count_applicant($user, $request_data)
    {
        $page = $request_data->page;
        $limit = $request_data->limit;

        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['CLOSED'])
            ->orderBy('id', 'DESC');
        

            $totalCount = count($appeal_ids_from_db->get());

            $appeal_ids_from_db_data = $appeal_ids_from_db->paginate($limit, ['*'], 'page', $page);

            $data['totalCount'] = $totalCount;
            $caseList=null;
    
            foreach ($appeal_ids_from_db_data as $appeal_ids_from_db_single) {
         
                $ct_info = DB::table('users')
                    ->where('users.common_login_user_id', $appeal_ids_from_db_single->created_by)
                    ->join('gcc_citizens', 'users.citizen_id', 'gcc_citizens.id')
                    ->select('gcc_citizens.citizen_name')
                    ->first();
                if ($ct_info) {
                    $applicant_name= $ct_info->citizen_name;
                }else {
                    $applicant_name= null;
                }
    
                $court_name =  DB::table('court')
                ->where('id', $appeal_ids_from_db_single->court_id)
                ->first()->court_name;
                
                $caseList[] = [
                    'id' => $appeal_ids_from_db_single->id,
                    'applicant_name' => $applicant_name,
                    'appeal_status' => $appeal_ids_from_db_single->appeal_status,
                    'case_no' => $appeal_ids_from_db_single->case_no,
                    'manual_case_no' => $appeal_ids_from_db_single->manual_case_no,
                    'court_name' => $court_name,
                    'next_date' => $appeal_ids_from_db_single->next_date,
                ];
            }
    
            $data['caseList'] = $caseList;
            return ['total_count' => $data['totalCount'], 'all_appeals' => $data['caseList']];
    }

    // gcc  org case details
    public function gcc_appeal_case_details(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);

        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;

        try {
            $appeal = GccAppeal::findOrFail($id);
            $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
            $data['appeal']  = $appeal;
            $data["notes"] = $appeal->appealNotes;
            $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';
            return ['status' => true,  "data" => $data];
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
            ], 404);
        }

    }

    //gcc citizen case details
    public function gcc_citizen_appeal_case_details(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        $office_id = $userInfo->office_id;
        $roleID = $userInfo->role_id;
        $officeInfo = $allInfo->officeInfo;
        try {
            $appeal = GccAppeal::findOrFail($id);
            $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
            $data['appeal']  = $appeal;
            $data["notes"] = $appeal->appealNotes;
            $data["districtId"] = $officeInfo->district_id;
            $data["divisionId"] = $officeInfo->division_id;
            $data["office_id"] = $office_id;


            $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';
            return ['status' => true,  "data" => $data];
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
            ], 404);
        }
        
    }

    //case tracking
    public function gcc_appeal_case_tracking(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        $office_id = $userInfo->office_id;
        $roleID = $userInfo->role_id;
        $officeInfo = $allInfo->officeInfo;

        try {
            $appeal = GccAppeal::findOrFail($id);
            $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
            $data['appeal']  = $appeal;
            $data["notes"] = $appeal->appealNotes;
            $data["districtId"] = $officeInfo->district_id;
            $data["divisionId"] = $officeInfo->division_id;
            $data["office_id"] = $office_id;
            $data["gcoList"] = User::where('office_id', $office_id)->where('id', '!=', $userInfo->id)->get();

            $data['page_title'] = 'মামলা ট্র্যাকিং';
            $data['shortOrderTemplateList'] = DB::table('gcc_notes_modified')
                ->where('gcc_notes_modified.appeal_id', $id)
                ->join('gcc_case_shortdecisions', 'gcc_notes_modified.case_short_decision_id', '=', 'gcc_case_shortdecisions.id')
                ->select('gcc_case_shortdecisions.case_short_decision', 'gcc_notes_modified.*')
                ->get();
            return ['status' => true,  "data" => $data];
        } catch (ModelNotFoundException $th) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found',
            ], 404);
        }
        
    }
}
