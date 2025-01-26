<?php

namespace App\Http\Controllers\GccApi;

use App\Models\GccAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\AppealRepository;
use App\Traits\TokenVerificationTrait;
use App\Repositories\CitizenRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\LogManagementRepository;
use App\Repositories\CertificateAsstNoteRepository;
use App\Repositories\OrganizationCaseMappingRepository;
use App\Http\Controllers\Api\BaseController as BaseController;

class GetDataController extends BaseController
{
    use TokenVerificationTrait;
    //store requisition
    public function storeRequisition(Request $request)
    {
        $data_get = $request->getContent();
    
        
        $data = json_decode($data_get, true);
        
       

        DB::beginTransaction();

        try {

            $appealId = AppealRepository::storeAppealBYCitizen($data['body_data']);
            CitizenRepository::storeCitizen($data['body_data']['citizeninfo'], $appealId);
           
            // dd($appealId);
            OrganizationCaseMappingRepository::employeeOrgizationCaseMappingOnCaseCreate($appealId, $data['body_data']['citizeninfo']['user']);
            $attach_file = $data['body_data']['log_file_data'];

            if ($attach_file) {

                $log_file_data = AttachmentRepository::storeReqAttachment($attach_file, $appealId, $data['body_data']['citizeninfo']['user']);
            } else {
                $log_file_data = null;
            }

            LogManagementRepository::citizen_appeal_store($data['body_data']['appealinfo'], $data['body_data']['citizeninfo'], $appealId, $log_file_data);
            DB::commit();
        } catch (\Throwable $e) {

            DB::rollback();
            return $this->sendResponse(null, 'তথ্য সংরক্ষণ করা হয়নি');
            $flag = 'false';
          
        }


        return $this->sendResponse(null, 'তথ্য সফলভাবে সংরক্ষণ করা হয়েছে.');
       
    }

    //case count for gcc court
    public function case_count_for_gcc(Request $request){
        $all_case=DB::table('gcc_appeals')->count();
  
        return $this->sendResponse($all_case, null);
    }


    //count org rep dashboard data
    public function count_org_dashboard_data(Request $request)
    {
        $user = json_decode($request->jss);
        $total_case_count_applicant_for_cause_list = $this->total_case_count_applicant_cause_list($user);

        $data['total_pending_case_count_applicant'] = $this->total_pending_case_count_applicant($user);
        $data['total_case_count_applicant'] = $this->total_case_count_applicant($user);
        $data['total_running_case_count_applicant'] = $this->total_running_case_count_applicant($user);
        $data['total_completed_case_count_applicant'] = $this->total_completed_case_count_applicant($user);

        $data['total_pending_appeal_case_count_applicant'] = $this->total_pending_appeal_case_count_applicant($user);
        $data['total_appeal_case_count_applicant'] = $this->total_appeal_case_count_applicant($user);
        $data['total_running_appeal_case_count_applicant'] = $this->total_running_appeal_case_count_applicant($user);
        $data['total_completed_appeal_case_count_applicant'] = $this->total_completed_appeal_case_count_applicant($user);

        

        $data['payment_notice'] = $this->getPaymentNotice($user);
        $appeal = GccAppeal::orderby('id', 'DESC')->WhereIn('ID', $total_case_count_applicant_for_cause_list['appeal_id_array'])->limit(10)->get();

            if ($appeal != null || $appeal != '') {
                foreach ($appeal as $key => $value) {
                    $citizen_info = AppealRepository::getCauselistCitizen($value->id);
                    $notes = CertificateAsstNoteRepository::get_last_order_list($value->id);
                    if (isset($citizen_info) && !empty($citizen_info)) {
                        $citizen_info = $citizen_info;
                    } else {
                        $citizen_info = null;
                    }
                    if (isset($notes) && !empty($notes)) {
                        $notes = $notes;
                    } else {
                        $notes = null;
                    }

                    $data['appeal'][$key]['citizen_info'] = $citizen_info;
                    $data['appeal'][$key]['notes'] = $notes;
                    // $data["notes"] = $value->appealNotes;
                }
            } else {

                $data['appeal'][$key]['citizen_info'] = '';
                $data['appeal'][$key]['notes'] = '';
            }
        return $data;
    }

    //count gcc citizen dashboard data
    public function count_gcc_citizen_dashboard_data(Request $request)
    {  
        $user = json_decode($request->jss);
        $total_case_count__gcc_citizen_for_cause_list = $this->total_case_count_gcc_citizen_cause_list($user);
        
        $data['total_pending_case_count_gcc_citizen'] = $this->total_pending_case_count_gcc_citizen($user);
        $data['total_case_count_gcc_citizen'] = $this->total_case_count_gcc_citizen($user);
        $data['total_running_case_count_gcc_citizen'] = $this->total_running_case_count_gcc_citizen($user);
        $data['total_completed_case_count_gcc_citizen'] = $this->total_completed_case_count_gcc_citizen($user);
        $data['certify_copy_fee_count_gcc_citizen'] = $this->certify_copy_fee_count_gcc_citizen($user);
        $data['hearing_count_gcc_citizen'] = $this->hearing_count_gcc_citizen($user);
        $data['cancel_certify_copy'] = $this->cancel_certify_copy($user);

        $data['total_pending_appeal_case_count_gcc_citizen'] = $this->total_pending_appeal_case_count_gcc_citizen($user);
        $data['total_appeal_case_count_gcc_citizen'] = $this->total_appeal_case_count_gcc_citizen($user);
        $data['total_running_appeal_case_count_gcc_citizen'] = $this->total_running_appeal_case_count_gcc_citizen($user);
        $data['total_completed_appeal_case_count_gcc_citizen'] = $this->total_completed_appeal_case_count_gcc_citizen($user);

        

        // $data['payment_notice'] = $this->getPaymentNotice($user);
        $appeal = GccAppeal::orderby('id', 'DESC')->WhereIn('ID', $total_case_count__gcc_citizen_for_cause_list['appeal_id_array'])->limit(10)->get();

            if ($appeal != null || $appeal != '') {
                foreach ($appeal as $key => $value) {
                    $citizen_info = AppealRepository::getCauselistCitizen($value->id);
                    $notes = CertificateAsstNoteRepository::get_last_order_list($value->id);
                    if (isset($citizen_info) && !empty($citizen_info)) {
                        $citizen_info = $citizen_info;
                    } else {
                        $citizen_info = null;
                    }
                    if (isset($notes) && !empty($notes)) {
                        $notes = $notes;
                    } else {
                        $notes = null;
                    }

                    $data['appeal'][$key]['citizen_info'] = $citizen_info;
                    $data['appeal'][$key]['notes'] = $notes;
                    // $data["notes"] = $value->appealNotes;
                }
            } else {

                $data['appeal'][$key]['citizen_info'] = '';
                $data['appeal'][$key]['notes'] = '';
            }
        return $data;
    }

    // org data
    public function total_case_count_applicant_cause_list($user)
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', $user->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [1, 2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED', 'ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
            $count++;
        }

        return ['total_count' => $count, 'appeal_id_array' => $appeal_id_array];
    }
    public function total_case_count_applicant($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('gcc_appeals.organization_routing_number', $user->organization_id) 
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL', 'CLOSED'])
            ->orderBy('id', 'DESC')
            ->get();
            
        // $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_running_case_count_applicant($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['ON_TRIAL'])
            ->orderBy('id', 'DESC')
            ->get();
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_pending_case_count_applicant($user)
    {


        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['SEND_TO_GCO', 'SEND_TO_ASST_GCO'])
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_completed_case_count_applicant($user)
    {


        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['CLOSED'])
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }

    // gcc citizen data 
    public function total_case_count_gcc_citizen_cause_list($user)
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED', 'ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->get();

        $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
            $count++;
        }

        return ['total_count' => $count, 'appeal_id_array' => $appeal_id_array, 'court_type' => 'gcc'];
    }
    public function total_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL','CLOSED'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();
        // $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db , 'court_type' => 'gcc'];
    }
    public function total_running_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
        ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
        ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
        ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
        ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
        ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL'])
        ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
        ->orderBy('gcc_citizens.id', 'DESC')
        ->get();
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }
    public function total_pending_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['SEND_TO_GCO', 'SEND_TO_ASST_GCO'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }
    public function total_completed_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }


    public function total_appeal_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL_ADM','CLOSED_APPEAL','ON_TRIAL_DM'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();
        // $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_running_appeal_case_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL_DM','ON_TRIAL_ADM'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_pending_appeal_case_count_gcc_citizen($user)
    {


        $appeal_ids_from_db =DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['SENT_TO_ADM', 'SENT_TO_ASST_ADM', 'SENT_TO_DM', 'SENT_TO_ASST_DM'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();
        

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_completed_appeal_case_count_gcc_citizen($user)
    {


        $appeal_ids_from_db =DB::table('gcc_appeal_citizens')
        ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
        ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
        ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
        ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
        ->whereIn('gcc_appeals.appeal_status', ['CLOSED_ADM','CLOSED_DM'])
        ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
        ->orderBy('gcc_citizens.id', 'DESC')
        ->get();
        
       

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }

    public function certify_copy_fee_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_process_fee_status', ['SENT_TO_DEFAULTER'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }

    public function hearing_count_gcc_citizen($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->where('gcc_appeals.is_hearing_required', '=', 1)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }

    public function cancel_certify_copy($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_phone_no', '=', $user->citizen_phone_no)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_process_status', ['CANCEL_CERTIFY_COPY'])
            ->select('gcc_appeals.*' ,'gcc_appeal_citizens.citizen_type_id as type_id', 'gcc_citizens.citizen_name as citizen_name' )
            ->orderBy('gcc_citizens.id', 'DESC')
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db, 'court_type' => 'gcc'];
    }

    //gcc citizen certificate copy request 
    public function gcc_citizen_certificate_copy(Request $request){
         $requestData = $request->all();
         $appeal_id= $requestData['body_data']['id'];
         $status= $requestData['body_data']['status'];
         $certify_id= $requestData['body_data']['certify_id'];
         
         
         if ($status==1) {
             $data=$requestData['body_data']['data'];
            if ($data) {
                $s['id']                    =$data['appeal_payment_id'];
                $s['appeal_id']             =$data['appeal_id'];
                $s['certify_id']            =$certify_id;
                $s['amount']                =$data['amount'];
                $s['court_id']              =$data['court_id'];
                $s['applicent_name']        =$data['applicent_name'];
                $s['applicent_nid']         =$data['applicent_nid'];
                $s['applicent_phn'  ]       =$data['applicent_phn'];
                $s['applicent_p_address']   =$data['applicent_p_address'];
                $s['applicent_per_address'] =$data['applicent_per_address'];
                $s['description']           =$data['description'];
                $s['case_no' ]              =$data['case_no'];
    
                $payment_id= DB::table('certify_copy')->insertGetId($s);
            }
            DB::table('gcc_appeals')->where('id', $appeal_id)->update([
                'appeal_process_status' => 'DM_FOR_CERTIFICATE_COPY'
             ]);
         }
         if ($status==2) {
            $update= DB::table('certify_copy')->where('appeal_id', $appeal_id)->update([
                'status' => 2,
                'certify_copy_fee' => "FEE_COMPLATE",
            ]);
            DB::table('gcc_appeals')->where('id', $appeal_id)->update([
                'appeal_process_fee_status' => 'CERTIFY_COPY_FEE_COMPLETE'
             ]);
         }
       
         if ($status==3) {
           
            $update= DB::table('certify_copy')->where('appeal_id', $appeal_id)->update([
                'status' => 3,
            ]);
             DB::table('gcc_appeals')->where('id', $appeal_id)->update([
                'appeal_process_fee_status' => 'PROCESS_COMPLETE'
             ]);
         }
         if ($status==4) {
            DB::table('gcc_appeals')->where('id', $appeal_id)->update([
                'appeal_status' => 'SENT_TO_ASST_DM',
                'appeal_process_status' => 'REQUEST_APPEAL',
                'action_required' => 'ASST_DM',
             ]);
         }
         
      
    }

    

    public function total_appeal_case_count_applicant($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['ON_TRIAL_ADM', 'CLOSED_ADM'])
            ->get();
        // $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_running_appeal_case_count_applicant($user)
    {
        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['ON_TRIAL_ADM'])
            ->get();
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_pending_appeal_case_count_applicant($user)
    {


        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['SEND_TO_ADM', 'SEND_TO_ASST_ADM'])
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }
    public function total_completed_appeal_case_count_applicant($user)
    {


        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['CLOSED_ADM'])
            ->get();

        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            // array_push($appeal_id_array, $appeal_ids_from_db_single->id);
            $ct_info = DB::table('gcc_appeal_citizens')
                // ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $appeal_ids_from_db_single->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->select('gcc_appeal_citizens.citizen_id')
                ->first();

            $appeal_ids_from_db_single->citizen_id =  $ct_info ? $ct_info->citizen_id : null;
            // $count++;
        }

        return ['total_count' => count($appeal_ids_from_db), 'all_appeals' => $appeal_ids_from_db];
    }



    public function total_case_count_defaulter($citizen_nid)
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', $citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED', 'ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        $appeal_id_array = [];
        $count = 0;
        foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
            array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
            $count++;
        }

        return ['total_count' => $count, 'appeal_id_array' => $appeal_id_array];
    }
    public function total_running_case_count_defaulter($citizen_nid)
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', $citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_pending_case_count_defaulter($user)
    {

        $appeal_ids_from_db = DB::table('gcc_appeals')
            ->where('organization_routing_number', $user->organization_id)
            ->whereIn('appeal_status', ['SEND_TO_ASST_GCO', 'SEND_TO_GCO'])
            ->get();

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_completed_case_count_defaulter()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        // $appeal_id_array=[];
        // $count=0;
        // foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
        //     array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
        //     /;
        // }

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function getPaymentNotice($user){


         $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->join('gcc_notes_modified', 'gcc_notes_modified.appeal_id', '=', 'gcc_appeals.id')
            ->where('gcc_appeals.organization_routing_number', $user->organization_id)
            ->where('gcc_appeal_citizens.citizen_type_id',1)
            ->select('gcc_citizens.citizen_name','gcc_appeals.process_fee_status','gcc_appeals.loan_amount','gcc_appeals.interestRate','gcc_appeals.id as appeal_id','gcc_appeals.office_name','gcc_appeals.case_no',DB::raw('count(*) as count'))
            ->where('gcc_appeals.process_fee_status',0)
            ->groupBy('gcc_notes_modified.appeal_id')
            ->having('count', '<=', 1)
            ->get();
        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' =>  $appeal_ids_from_db];
    }
    public function paymentStatusUpdate(Request $request){
        $all=$request->all();
        $data= $all['body_data']['paymentstatus'];
        $appeal_id= $all['body_data']['appeal_id'];
        DB::table('gcc_appeals')
       ->where('id',$appeal_id)
       ->update(['process_fee_status' =>1]);
       return 'success';
        
    }

    //Org rep case for appeal 
    public function case_for_appeal(Request $request){
        // return 'gcc';
        $data = json_decode($request->body_data, true);
        $id = $data['id'];
        $user = $data['user'];
        
        GccAppeal::where('id', $id)->update([
            'appeal_status' => 'SEND_TO_ASST_ADM',
            'action_required' => 'ASST_ADM',
        ]);

        
        return $this->sendResponse(null, null);
       
       
       
     }
}