<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appeal;
use App\Models\GccAppeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AppealRepository;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\AttachmentRepository;
use App\Repositories\ShortOrderRepository;
use App\Repositories\CitizenEditRepository;
use App\Repositories\AppealCitizenRepository;
use App\Repositories\LogManagementRepository;
use App\Repositories\CitizenAttendanceRepository;
use App\Repositories\CertificateAsstNoteRepository;

class AppealInitiateController extends Controller
{
    public $permissionCode = 'certificateInitiate';
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    // public function __construct()
    // {
    //   //  $this->middleware('auth');
    //     AppealBaseController::__construct();

    // }

    /**
     * Show the form for creating a new Appeal.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // return GccAppeal::all();
        $user = globalUserInfo();
        $office_id = $user->office_id;
        $roleID = $user->role_id;
        $officeInfo = user_office_info();
        $gcoList = User::where('office_id', $user->office_id)
            ->where('id', '!=', $user->id)
            ->get();

        $appealId = null;
        $data = [
            'districtId' => $officeInfo->district_id,
            'divisionId' => $officeInfo->division_id,
            'office_id' => $office_id,
            'appealId' => $appealId,
            'gcoList' => $gcoList,
        ];

        // if($roleID == 5 || $roleID == 6 || $roleID == 7 || $roleID == 8 || $roleID == 13 || $roleID == 16){
        //     $query->where('case_register.district_id','=', $officeInfo->district_id);
        // }elseif($roleID == 9 || $roleID == 10 || $roleID == 11){
        //     $query->where('case_register.upazila_id','=', $officeInfo->upazila_id);
        // }elseif($roleID == 12){
        //     $moujaIDs = $this->get_mouja_by_ulo_office_id(Auth::user()->office_id);
        //     // dd($moujaIDs);
        //     // print_r($moujaIDs); exit;
        //     $query->whereIn('case_register.mouja_id', [$moujaIDs]);

        // $data=["districtId"=>Session::get('userInfo')->districtId,
        //         "divisionId"=>Session::get('userInfo')->divisionBbsCode,
        //         "appealId"=>$appealId];
        $page_title = 'সার্টিফিকেট রিকুজিশন প্রক্রিয়াকরন';
        return view('appealInitiate.appealCreate')->with(['data' => $data, 'page_title' => $page_title]);
    }

    // public function store(Request $request) {
    //     return $request;
    // }

    public function store(Request $request)
    {
        //dd($request->files);
        //  dd($request->all());exit;


        $return_validated = $this->validate_request_data($request);

        if (!empty($return_validated)) {
            return $return_validated;
        }

        //dd($request);

        // return $request;

        // if($request->ajax()){
        DB::beginTransaction();
        try {
            if (globalUserInfo()->role_id == 28) {
                $appealId = AppealRepository::storeAppealForCertificateAsst($request);
                CitizenEditRepository::storeCitizenBYCertificateAsst($request, $request->citizen, $appealId);
                $certificate_asst_note_id = CertificateAsstNoteRepository::store_certificate_asst_note($request, $appealId);
                if ($request->caseNo != 'অসম্পূর্ণ মামলা' && $request->is_attendence_required == 'attendence_required') {
                    CitizenAttendanceRepository::storeAttendenceByCertAsst($request->citizenAttendance);
                }
                $is_payememt_calculated = [27, 28, 29, 36];
                if (in_array($request->shortOrder[0], $is_payememt_calculated)) {
                    $storePaymentInfo_payment_id = CertificateAsstNoteRepository::storePaymentInfo($request, $appealId);
                } else {
                    $storePaymentInfo_payment_id = null;
                }

                if ($request->file_type && $_FILES['file_name']['name']) {
                    $log_file_data = AttachmentRepository::storeAttachment('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->file_type, $storePaymentInfo_payment_id);
                    // AttachmentRepository::storeAttachment('APPEAL', $appealId, $causeListId, $request->file_type);
                } else {
                    $log_file_data = null;
                }

                if (!empty($_FILES['court_fee_file'])) {
                    //dd('1');
                    $captions_main_court_Fee_report = 'কোর্ট ফি আদায় রশিদ এর স্ক্যান' . $appealId . date('Y-m-d');
                    $court_fee_file_main = AttachmentRepository::storeInvestirationCourtFree('COURT_Fee', $request->appealId, $captions_main_court_Fee_report);
                } else {
                    $court_fee_file_main = null;
                }

                $flag = 'true';
                DB::table('cer_asst_notes_modified')
                    ->where('id', $certificate_asst_note_id)
                    ->update([
                        'cer_asst_attachmets' => $log_file_data,
                    ]);

                if (!empty($court_fee_file_main)) {
                    DB::table('gcc_appeals')
                        ->where('id', $appealId)
                        ->update([
                            'court_fee_file' => $court_fee_file_main,
                        ]);
                }

                LogManagementRepository::cer_asst_appeal_store($request, $appealId, $log_file_data);
            }
            if (globalUserInfo()->role_id==10 || globalUserInfo()->role_id==9) {
                // dd('hi');
                $appealId = AppealRepository::storeAppealForAdmAsst($request);
                CitizenEditRepository::storeCitizenBYCertificateAsst($request, $request->citizen, $appealId);
                $certificate_asst_note_id = CertificateAsstNoteRepository::store_certificate_asst_note($request, $appealId);
                if ($request->caseNo != 'অসম্পূর্ণ মামলা' && $request->is_attendence_required == 'attendence_required') {
                    CitizenAttendanceRepository::storeAttendenceByCertAsst($request->citizenAttendance);
                }
                $is_payememt_calculated = [27, 28, 29, 36];
                // if (in_array($request->shortOrder[0], $is_payememt_calculated)) {
                //     $storePaymentInfo_payment_id = CertificateAsstNoteRepository::storePaymentInfo($request, $appealId);
                // } else {
                // }

                $storePaymentInfo_payment_id = null;

                // if ($request->file_type && $_FILES['file_name']['name']) {
                //     $log_file_data = AttachmentRepository::storeAttachment('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->file_type, $storePaymentInfo_payment_id);
                //     // AttachmentRepository::storeAttachment('APPEAL', $appealId, $causeListId, $request->file_type);
                // } else {
                // }

                $log_file_data = null;
                if (!empty($_FILES['court_fee_file'])) {
                    //dd('1');
                    $captions_main_court_Fee_report = 'কোর্ট ফি আদায় রশিদ এর স্ক্যান' . $appealId . date('Y-m-d');
                    $court_fee_file_main = AttachmentRepository::storeInvestirationCourtFree('COURT_Fee', $request->appealId, $captions_main_court_Fee_report);
                } else {
                    $court_fee_file_main = null;
                }

                $flag = 'true';
                DB::table('cer_asst_notes_modified')
                    ->where('id', $certificate_asst_note_id)
                    ->update([
                        'cer_asst_attachmets' => $log_file_data,
                    ]);

                if (!empty($court_fee_file_main)) {
                    DB::table('gcc_appeals')
                        ->where('id', $appealId)
                        ->update([
                            'court_fee_file' => $court_fee_file_main,
                        ]);
                }

                // LogManagementRepository::cer_asst_appeal_store($request, $appealId, $log_file_data);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return response()->json([
                'error' => 'আদেশ সংরক্ষণ করা হয় নাই',
                'message' => 'Internal Server Error',
                'status' => 'error',
            ]);
        }
        return response()->json([
            'success' => 'আদেশ সফলভাবে সংরক্ষণ করা হয়েছে',
            'status' => 'success',
            'message' => 'আদেশ সফলভাবে সংরক্ষণ করা হয়েছে',
        ]);
    }

    // public function edit(Request $request)
    // {
    //     $appealId=$request->id;
    //     $data=["districtId"=>'',
    //             "divisionId"=>'',
    //             "appealId"=>$appealId];

    //     return view('appealInitiate.appealCreate')->with('data',$data);
    // }
    public function edit($id)
    {
        $id = decrypt($id);
        $user = globalUserInfo();
        $office_id = $user->office_id;
        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', Auth::user()->common_login_user_id)->select('court_type_id', 'role_id', 'court_id')->first();
        $roleID = $user_court_info->role_id;

        $officeInfo = user_office_info();

        $appeal = GccAppeal::findOrFail($id);
        $data = AppealRepository::getAllAppealInfo($id);
        //dd($data);
        $data['appeal'] = $appeal;
        $data['notes'] = $appeal->appealNotes;
        $data['districtId'] = $officeInfo->district_id;
        $data['divisionId'] = $officeInfo->division_id;
        $data['office_id'] = $office_id;
        $data['gcoList'] = User::where('office_id', $user->office_id)
            ->where('id', '!=', $user->id)
            ->get();
        $data['id'] = $id;
        $data['shortOrderList'] = ShortOrderRepository::getShortOrderListForCerAsst();

        $shortoder_array = CertificateAsstNoteRepository::order_list($id);


        $data['shortoder_array'] = isset($shortoder_array) ? $shortoder_array : [];

        $data['gcc_last_order'] = CertificateAsstNoteRepository::gcc_last_order($id);

        $data['collection_payment_so_far'] = CertificateAsstNoteRepository::collection_payment_so_far($id);

        $data['page_title'] = 'সার্টিফিকেট মামলা সংশোধন';
        $data['roleID'] = $roleID;
        //dd($data);
        return view('appealInitiate.appealEdit')->with($data);
    }

    public function delete($id = null)
    {
        $id = decrypt($id);
        $appeal = GccAppeal::findOrFail($id);

        $cases = GccAppeal::where('id', $id)->get();
        foreach ($cases as $case) {
            DB::table('gcc_case_shortdecision_templates')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_appeal_order_sheets')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_attachments')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_cause_lists')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_appeal_citizens')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_notes')
                ->where('appeal_id', $case->id)
                ->delete();
            DB::table('gcc_causelist_caseshortdecisions')
                ->where('appeal_id', $case->id)
                ->delete();
        }

        $appeal->delete();
        return redirect()
            ->back()
            ->with('success', 'তথ্য সফলভাবে মুছে ফেলা হয়েছে');
    }

    public function fileDelete(Request $request, $id, $appeal_id)
    {
        $fileID = $id;
        AttachmentRepository::deleteFileByFileID($fileID, $appeal_id);
        return response()->json([
            'msg' => 'success',
        ]);
    }
    public function appealCitizenDelete($citizen_id)
    {
        $appealCitizen = AppealCitizenRepository::checkAppealCitizenExist($citizen_id);
        // return $citizen_id;
        if ($appealCitizen->delete()) {
            return response()->json([
                'msg' => 'success',
            ]);
        }
        return response()->json([
            'msg' => 'error',
        ]);
    }
    public function appealFullDelete($citizen_id)
    {
        $appealCitizen = AppealCitizenRepository::checkAppealCitizenExist($citizen_id);
        // return $citizen_id;
        if ($appealCitizen->delete()) {
            return response()->json([
                'msg' => 'success',
            ]);
        }
        return response()->json([
            'msg' => 'error',
        ]);
    }

    public function getAttendenceShit($appeal_id, $citizen_id, $citizen_type_id, $citizen_name, $citizen_designation = null)
    {
        //echo $appeal_id;
        // echo $citizen_name;
        // echo $citizen_designation;
        // echo $citizen_type_id;
        // echo $citizen_id;
        if ($citizen_type_id == 1) {
            $defaultername = DB::table('gcc_appeals')
                ->join('gcc_appeal_citizens', 'gcc_appeals.id', '=', 'gcc_appeal_citizens.appeal_id')
                ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', '=', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.citizen_type_id', 2)
                ->where('gcc_appeals.id', $appeal_id)
                ->select('gcc_citizens.citizen_name', 'gcc_citizens.designation')
                ->first();

            $officeInfo = user_office_info();
            $data['district'] = $officeInfo->district_name_bn;
            $data['defaulter_name'] = $defaultername->citizen_name;
            $data['defaulter_designation'] = $defaultername->designation;
            $data['applicant_name'] = $citizen_name;
            $data['applicant_designation'] = $citizen_designation;
        } elseif ($citizen_type_id == 2) {
            $applicantname = DB::table('gcc_appeals')
                ->join('gcc_appeal_citizens', 'gcc_appeals.id', '=', 'gcc_appeal_citizens.appeal_id')
                ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', '=', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->where('gcc_appeals.id', $appeal_id)
                ->select('gcc_citizens.citizen_name', 'gcc_citizens.designation')
                ->first();

            $officeInfo = user_office_info();
            $data['district'] = $officeInfo->district_name_bn;
            $data['defaulter_name'] = $citizen_name;
            $data['defaulter_designation'] = $citizen_designation;
            $data['applicant_name'] = $applicantname->citizen_name;
            $data['applicant_designation'] = $applicantname->designation;
        } elseif ($citizen_type_id == 5) {
            $applicantname = DB::table('gcc_appeals')
                ->join('gcc_appeal_citizens', 'gcc_appeals.id', '=', 'gcc_appeal_citizens.appeal_id')
                ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', '=', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->where('gcc_appeals.id', $appeal_id)
                ->select('gcc_citizens.citizen_name')
                ->first();
            $officeInfo = user_office_info();
            $data['district'] = $officeInfo->district_name_bn;
            $data['defaulter_name'] = $citizen_name;
            $data['defaulter_designation'] = $citizen_designation;
            $data['applicant_name'] = $applicantname->citizen_name;
            $data['applicant_designation'] = isset($applicantname->designation) ?: 'উত্তরাধিকারী';
        }
        $case_no = DB::table('gcc_appeals')
            ->select('case_no', 'manual_case_no')
            ->where('id', $appeal_id)
            ->first();

        $data['manual_case_no'] = $case_no->manual_case_no;
        $data['case_no'] = $case_no->case_no;

        return view('report.hajira')->with($data);
    }
    public function validate_request_data($requestInfo)
    {

        $appeal = DB::table('gcc_appeals')->where('id', $requestInfo->appealId)->first();
        $user = DB::table('users')->where('id', $appeal->updated_by)->first();
        if (!empty($user)) {
            if ($appeal->action_required == "GCO") {
                return response()->json([
                    'error' => 'আদেশ সংরক্ষণ করা হয় নাই',
                    'message' => 'মামলাটিতে ইতিমধ্যে ' . $user->name . ' তথ্য সংশোধন করেছেন ',
                    'div_id' => 'receiver_land_details',
                    'status' => 'error',
                ]);
            }
        }
        /*** Verify Nominee Data */
        if (isset($requestInfo->nominee) && !empty($requestInfo->nominee) && $requestInfo->nominee_already == "nominee_already_not" && in_array($requestInfo->shortOrder[0], [23, 24, 25])) {
            foreach ($requestInfo->nominee['nid'] as $key => $value) {
                if (!isset($value)) {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'উত্তরাধিকারীর জাতীয় পরিচয়পত্র গুলো দিন!!',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                }
            }
            foreach ($requestInfo->nominee['phn'] as $key => $value) {

                $mobile_error = $this->validatePhoneNumberOnTrial($value);
                if ($mobile_error == 'size_error') {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !  ',
                        'message' => 'উত্তরাধিকারীর মোবাইল নম্বর ইংরেজিতে 11 digit দিতে হবে!.' . en2bn($key + 1) . ' নং উত্তরাধিকারীর দেখুন',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                } elseif ($mobile_error == 'format_error') {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'উত্তরাধিকারীর মোবাইল নম্বর format সঠিক নয় !.' . en2bn($key + 1) . ' নং উত্তরাধিকারীর দেখুন',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                }
            }
            foreach ($requestInfo->nominee['email'] as $key => $value) {
                $email_check = $this->validateEmail($value);
                if ($email_check == 'format_error') {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই  !',
                        'message' => 'উত্তরাধিকারীর ইমেইল format সঠিক নয় !.' . en2bn($key + 1) . ' নং উত্তরাধিকারীর দেখুন',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                }
            }
        }

        if ($requestInfo->is_varified_org == 0) {
            return response()->json([
                'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                'message' => 'প্রতিষ্ঠানটি সত্যায়িত করুন!',
                'div_id' => 'appeal_date_time_status',
                'status' => 'error',
            ]);
        }
        $all_request_nids = [];

        if (!empty($requestInfo->nominee['nid'])) {
            foreach ($requestInfo->nominee['nid'] as $value_nid_single) {
                array_push($all_request_nids, $value_nid_single);
            }
        }
        if (!empty($requestInfo->applicant['nid'])) {
            foreach ($requestInfo->applicant['nid'] as $value_nid_single) {
                array_push($all_request_nids, $value_nid_single);
            }
        }

        if (!empty($requestInfo->defaulter['nid'])) {
            array_push($all_request_nids, $requestInfo->defaulter['nid']);
        }

        if (count(array_unique($all_request_nids)) != count($all_request_nids)) {
            return response()->json([
                'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                'message' => 'আপনি একই নাগরিককে একাধিকবার প্রাতিষ্ঠানিক প্রতিনিধি , ঋণগ্রহীতা অথবা উত্তরাধিকারী হিসেবে যোগ করেছেন!',
                'div_id' => 'appeal_date_time_status',
                'status' => 'error',
            ]);
        }
        if ($requestInfo->shortOrder[0] == 27 || $requestInfo->shortOrder[0] == 28 || $requestInfo->shortOrder[0] == 29 || $requestInfo->shortOrder[0] == 36) {

            if (!str_contains($requestInfo->file_type[0], 'টাকা আদায়ের রশিদ')) {
                return response()->json([
                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                    'message' => 'টাকা আদায়ের রশিদ কথাটি ফাইলের নামের মধ্যে থাকতে হবে!',
                    'div_id' => 'paymentInformation',
                    'status' => 'error',
                ]);
            }

            if (empty($requestInfo->TodayPaymentPaymentCollection)) {
                return response()->json([
                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                    'message' => 'প্রদেয় অর্থের পরিমাণ দিন!',
                    'div_id' => 'paymentInformation',
                    'status' => 'error',
                ]);
            }
            if (bn2en($requestInfo->TodayPaymentPaymentCollection) + bn2en($requestInfo->collectSoFarPaymentCollection) > bn2en($requestInfo->totalDemandPaymentCollection)) {
                return response()->json([
                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                    'message' => 'আপনি অতিরিক্ত টাকা দিয়েছন!',
                    'div_id' => 'paymentInformation',
                    'status' => 'error',
                ]);
            }
            if ($requestInfo->shortOrder[0] == 27 || $requestInfo->shortOrder[0] == 36) {
                if (!isset($_FILES['file_name'])) {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'অর্থ আদায়ের রশিদ দিন !!',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                } else {
                    if (empty($_FILES['file_name']['name'][0])) {
                        return response()->json([
                            'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                            'message' => 'অর্থ আদায়ের রশিদ দিন !!',
                            'div_id' => 'warrantExecutorDetails',
                            'status' => 'error',
                        ]);
                    } else {
                        foreach ($_FILES['file_name']['name'] as $key => $value) {
                            if (empty($value) && $key > 0) {
                                return response()->json([
                                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                                    'message' => 'প্রয়োজনীয় ফাইল ঠিক মত দিন!!',
                                    'div_id' => 'warrantExecutorDetails',
                                    'status' => 'error',
                                ]);
                            }
                        }
                    }
                }
            } elseif ($requestInfo->shortOrder[0] == 28 || $requestInfo->shortOrder[0] == 29) {
                if (!str_contains($requestInfo->file_type[1], 'নিষ্পত্তি আবেদন')) {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'নিষ্পত্তি আবেদন কথাটি ফাইলের নামের মধ্যে থাকতে হবে!',
                        'div_id' => 'paymentInformation',
                        'status' => 'error',
                    ]);
                }

                if (isset($_FILES['file_name'])) {
                    if (count($_FILES['file_name']['name']) < 2) {
                        return response()->json([
                            'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                            'message' => 'খাতকের বকেয়া পরিশোধ ও নিষ্পত্তি আবেদন এর Scan Copy দিন!',
                            'div_id' => 'warrantExecutorDetails',
                            'status' => 'error',
                        ]);
                    } else {
                        if (empty($_FILES['file_name']['name'][0])) {
                            return response()->json([
                                'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                                'message' => 'অর্থ আদায়ের রশিদ দিন !!',
                                'div_id' => 'warrantExecutorDetails',
                                'status' => 'error',
                            ]);
                        } elseif (empty($_FILES['file_name']['name'][1])) {
                            return response()->json([
                                'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                                'message' => 'নিষ্পত্তি আবেদন Scan Copy দিন !!',
                                'div_id' => 'warrantExecutorDetails',
                                'status' => 'error',
                            ]);
                        } else {
                            foreach ($_FILES['file_name']['name'] as $key => $value) {
                                if (empty($value) && $key > 1) {
                                    return response()->json([
                                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                                        'message' => 'প্রয়োজনীয় ফাইল ঠিক মত দিন!!',
                                        'div_id' => 'warrantExecutorDetails',
                                        'status' => 'error',
                                    ]);
                                }
                            }
                        }
                    }
                } else {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'খাতকের বকেয়া পরিশোধ ও নিষ্পত্তি আবেদন এর Scan Copy দিন!',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                }
            }
        }

        if ($requestInfo->shortOrder[0] == 33) {
            if (!str_contains($requestInfo->file_type[0], 'খাতকের দাবী অস্বীকার স্ক্যান কপি')) {
                return response()->json([
                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                    'message' => 'খাতকের দাবী অস্বীকার স্ক্যান কপি কথাটি ফাইলের নামের মধ্যে থাকতে হবে!',
                    'div_id' => 'paymentInformation',
                    'status' => 'error',
                ]);
            }
            if (!isset($_FILES['file_name'])) {

                return response()->json([
                    'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                    'message' => 'খাতকের দাবী অস্বীকার আবেদন এর Scan Copy দিন!',
                    'div_id' => 'warrantExecutorDetails',
                    'status' => 'error',
                ]);
            } else {
                if (empty($_FILES['file_name']['name'][0])) {
                    return response()->json([
                        'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                        'message' => 'খাতকের দাবী অস্বীকার আবেদন এর Scan Copy দিন !!',
                        'div_id' => 'warrantExecutorDetails',
                        'status' => 'error',
                    ]);
                } else {
                    foreach ($_FILES['file_name']['name'] as $key => $value) {
                        if (empty($value) && $key > 0) {
                            return response()->json([
                                'error' => 'আদেশের উপর গৃহীত ব্যবস্থা সংরক্ষণ করা হয় নাই !',
                                'message' => 'প্রয়োজনীয় ফাইল ঠিক মত দিন!!',
                                'div_id' => 'warrantExecutorDetails',
                                'status' => 'error',
                            ]);
                        }
                    }
                }
            }
        }
    }
    public function number_field_check(Request $request)
    {
        if (is_numeric(bn2en($request->court_fee_amount))) {
            return response()->json([
                'is_numeric' => true,
            ]);
        } else {
            return response()->json([
                'is_numeric' => false,
            ]);
        }
    }
    public function number_field_check_remainig_taka(Request $request)
    {
        if (is_numeric(bn2en($request->TodayPaymentPaymentCollection))) {
            $is_numeric = true;
        } else {
            $is_numeric = false;
        }
        if ($is_numeric) {
            $TodayPaymentPaymentCollection = bn2en($request->TodayPaymentPaymentCollection);
            $collectSoFarPaymentCollection = bn2en($request->collectSoFarPaymentCollection);
            $totalDemandPaymentCollection = bn2en($request->totalDemandPaymentCollection);

            if ($TodayPaymentPaymentCollection + $collectSoFarPaymentCollection > $totalDemandPaymentCollection) {
                return response()->json([
                    'is_overflow' => true,
                    'is_numeric' => $is_numeric,
                ]);
            } else {
                $remaining_collection = $totalDemandPaymentCollection - ($TodayPaymentPaymentCollection + $collectSoFarPaymentCollection);
                return response()->json([
                    'is_overflow' => false,
                    'is_numeric' => $is_numeric,
                    'TotalRemainingPaymentPaymentCollection' => en2bn($remaining_collection),

                    'message' => 'মোট দাবী ' . $request->totalDemandPaymentCollection . ' টাকা  ,অদ্য আদায়কৃত ' . $request->collectSoFarPaymentCollection . ' টাকা, প্রদেয় ' . $request->TodayPaymentPaymentCollection . ' টাকা , মোট বকেয়া ' . en2bn($remaining_collection) . ' টাকা ।',
                ]);
            }
        } else {
            return response()->json([
                'is_numeric' => $is_numeric,
            ]);
        }
    }

    public function validatePhoneNumberOnTrial($phone_number)
    {
        $phone_number = trim($phone_number);
        if (strlen($phone_number) != 11) {
            return 'size_error';
        } else {
            $pattern = '/(01)[0-9]{9}/';
            $preg_answer = preg_match($pattern, $phone_number);
            if (!$preg_answer) {
                return 'format_error';
            }
        }
    }

    public function validateEmail($email)
    {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //Valid email!
            return 'format_ok';
        } else {
            return 'format_error';
        }
    }

    public function getdependentOfficeName($id)
    {
        $office_address_routing_no = DB::table("office")->where("id", $id)->select("office_name_bn", "office_name_en", "organization_physical_address", "organization_routing_id")->first();
        return $office_address_routing_no;
    }
    public function currentAppealEntryFrom()
    {
        // return GccAppeal::all();
        // $user = globalUserInfo();
        // $office_id = $user->office_id;
        // $roleID = $user->role_id;
        // $officeInfo = user_office_info();
        // $gcoList = User::where('office_id', $user->office_id)
        //     ->where('id', '!=', $user->id)
        //     ->get();

        // $appealId = null;
        // $data = [
        //     'districtId' => $officeInfo->district_id,
        //     'divisionId' => $officeInfo->division_id,
        //     'office_id' => $office_id,
        //     'appealId' => $appealId,
        //     'gcoList' => $gcoList,
        // ];

        // return GccAppeal::all();

        $user = globalUserInfo();

        $office_id = $user->office_id;
        $roleID = $user->role_id;
        $officeInfo = user_office_info();
        // $available_court = DB::table('court')->where('id', $user->court_id)->where('upazila_id', $officeInfo->upazila_id)->orWhere('level', 1)->where('district_id', $officeInfo->district_id)->orderBy('id', 'desc')->get();
        $available_court = DB::table('court')->where('id', $user->court_id)->orderBy('id', 'desc')->get();

        dd($user);
        $upazila = DB::table("upazila")->where("district_id", $officeInfo->district_id)->select("upazila_name_bn", "id")->get();


        //  dd($available_court);
        //  $office_information = DB::table("office")
        //  ->where("division_id",$officeInfo->division_id)
        //  ->where("district_id",$officeInfo->district_id)
        //  ->where("upazila_id",$officeInfo->upazila_id)
        //  ->where('organization_type',$officeInfo->organization_type)
        //  ->where('status',1)
        //  ->where('is_organization',1)
        //  ->select("office_name_bn","id")
        //  ->get();
        //  dd($officeInfo->organization_type);
        //  switch($officeInfo->organization_type)
        // {
        //     case "BANK":
        //     $organization_type_bn_name='ব্যাংক';
        //     break;
        //     case "GOVERNMENT":
        //     $organization_type_bn_name='সরকারি প্রতিষ্ঠান';
        //     break;
        //     case "OTHER_COMPANY":
        //     $organization_type_bn_name='স্বায়ত্তশাসিত প্রতিষ্ঠান';
        //     break;   
        // }  
        $appealId = null;
        $data = [
            'districtId'      => $officeInfo->district_id,
            'divisionId'      => $officeInfo->division_id,
            'office_id'       => $office_id,
            'appealId'        => $appealId,
            'available_court' => $available_court,
            'upazila'         => $upazila

        ];
        $page_title = 'চলমান মামলার এন্ট্রি ';
        return view('appealInitiate.currentAppealEntryFrom')->with(['data' => $data, 'page_title' => $page_title]);
    }

    public function currentAppealstore(Request $request)
    {

        //  dd($request->all());


        // $return_validated = $this->validate_request_data($request);

        // if (!empty($return_validated)) {
        //     return $return_validated;
        // }

        // dd($request);

        // return $request;

        // if($request->ajax()){
        DB::beginTransaction();
        try {
            $appealId = AppealRepository::storeRunningAppealBYAsst($request);
            // dd($appealId);
            $asdf = AppealRepository::runningApplealstoreCitizen($request, $request->citizen, $appealId);
            //    dd($asdf);
            if ($appealId) {
                $office_id = $request->applicant['organization'][0];
                // dd()
                $getCitizensWithSameOffice = DB::table('users')->where('office_id', $office_id)->get();
                foreach ($getCitizensWithSameOffice as $key => $value) {
                    // && $value->id !=$user->id
                    if (!empty($value->citizen_id)) {
                        $data = [
                            'appeal_id' => $appealId,
                            'citizen_id' => $value->citizen_id,
                            'citizen_type_id' => 1,
                            'created_by' => globalUserInfo()->id,
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        DB::table('gcc_appeal_citizens')->insert($data);
                    }
                }
            }

            if ($request->file_type && $_FILES['file_name']['name']) {


                $log_file_data = AttachmentRepository::storeAttachment('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->file_type, null);
            } else {
                $log_file_data = null;
            }



            // if ($request->order_file_type && $_FILES['order_file_name']['name']) {
            //     $logfiledata = AttachmentRepository::runningAppealStoreAttachment('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->order_file_type, null);
            // } else {
            //     $logfiledata = null;
            // }

            // if ($request->onama_file_type && $_FILES['onama_file_name']['name']) {
            //     $logfiledata = AttachmentRepository::runningAppealOrder('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->onama_file_type, null);
            // } else {
            //     $logfiledata = null;
            // }
            // if ($request->attendance_file_type && $_FILES['onama_file_name']['name']) {
            //     $logfiledata = AttachmentRepository::runningAppealOrderTemplate('APPEAL', $appealId, $causeListId = date('Y-m-d'), $request->attendance_file_type, null);
            // } else {
            //     $logfiledata = null;
            // }



            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return response()->json([
                'error' => 'আদেশ সংরক্ষণ করা হয় নাই',
                'message' => 'Internal Server Error',
                'status' => 'error',
            ]);
        }

        return redirect('/appeal/currentAppealEntryList')->with('success', 'তথ্য সফলভাবে সংরক্ষণ করা হয়েছে');
        // return response()->json([
        //     'success' => 'আদেশ সফলভাবে সংরক্ষণ করা হয়েছে',
        //     'status' => 'success',
        //     'message' => 'আদেশ সফলভাবে সংরক্ষণ করা হয়েছে',
        // ]);
    }
}
