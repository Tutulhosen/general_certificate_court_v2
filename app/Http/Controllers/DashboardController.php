<?php

namespace App\Http\Controllers;

// use Auth;
use App\Models\GccAppeal;
use App\Models\CaseHearing;

// use App\Models\AtCaseRegister;
// use App\Models\RM_CaseRgister;
use App\Models\CaseRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\NoteRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\AppealRepository;
use App\Repositories\NDoptorRepository;
use App\Repositories\CertificateAsstNoteRepository;
use App\Http\Resources\calendar\CaseHearingCollection;
use App\Http\Controllers\Api\BaseController as BaseController;

// use App\Http\Resources\calendar\RM_CaseHearingCollection;
// use App\Models\RM_CaseHearing;

// use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use App\Http\Controllers\CommonController;

class DashboardController extends BaseController
{

    // use AuthenticatesUsers;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $court_type_id = 2;

        $user_court_info = DB::table('doptor_user_access_info')->where('common_login_user_id', Auth::user()->common_login_user_id)->where('court_type_id', $court_type_id)->select('court_type_id', 'role_id', 'court_id')->first();
        // dd($user_court_info);
        if (Auth::user()->doptor_user_active == 1) {



            if ($user_court_info->court_type_id == 2) {
                // dd($user_court_info);
                // $callbackurl =  'getLogin' . $id;
                // $zoom_join_url = 'http://127.0.0.1:7878/getLogin/?'. 'referer=' . $user_court_info->court_type_id ;
                // dd($zoom_join_url);
                // dd($user_court_info->role_id);
                // return redirect()->away($zoom_join_url);
                if ($user_court_info->role_id == 6 || $user_court_info->role_id == 9 || $user_court_info->role_id == 11 || $user_court_info->role_id == 12 || $user_court_info->role_id == 10 || $user_court_info->role_id == 7) {
                    $data['total_case'] = GccAppeal::whereIn('appeal_status', ['CLOSED_APPEAL', 'ON_TRIAL_DM', 'ON_TRIAL_ADM'])->where('district_id', user_district()->id)->count();
                    
                    $data['pending_case'] = GccAppeal::whereIn('appeal_status', ['SENT_TO_DM','SENT_TO_ASST_DM','SENT_TO_ADM','SENT_TO_ASST_ADM'])->where('district_id', user_district()->id)->count();
                    
                    $data['running_case'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL_DM','ON_TRIAL_ADM'])->where('district_id', user_district()->id)->count();
                    
                    $data['completed_case'] = GccAppeal::where('appeal_status', 'CLOSED_APPEAL')->where('district_id', user_district()->id)->count();
                    
                    $data['rejected_case'] = GccAppeal::where('appeal_status', 'REJECTED')->where('district_id', user_district()->id)->count();
                    
                    $data['draft_case'] = GccAppeal::where('appeal_status', 'DRAFT')->where('district_id', user_office_info()->district_id)->count();
                    
                    $data['postpond_case'] = GccAppeal::where('appeal_status', 'POSTPONED')->where('district_id', user_office_info()->district_id)->count();
                    
                    $data['total_office'] = DB::table('office')->where('is_gcc', 1)->whereNotIn('id', [1, 2, 7])->count();
                    $data['total_user'] = DB::table('users')->count();
                    $data['total_court'] = DB::table('court')->whereNotIn('id', [1, 2])->count();
                    $data['total_mouja'] = DB::table('mouja')->count();
                    // dd('hi');
                    $data['total_ct'] = DB::table('case_type')->count();
                    $data['total_sf_count'] = CaseRegister::orderby('id', 'desc')->where('is_sf', 1)->where('status', 1)->get()->count();

                    $data['cases'] = DB::table('case_register')
                        ->select('case_register.*')
                        ->get();

                    $data['pending_case_list'] = GccAppeal::orderby('id', 'desc')->whereIn('appeal_status', ['SEND_TO_DC'])->where('district_id', user_office_info()->district_id)->count();

                    $data['trial_date_list'] = GccAppeal::orderby('id', 'desc')->whereIn('appeal_status', ['ON_TRIAL_DC'])->where('next_date', date('Y-m-d', strtotime(now())))->where('is_hearing_required', 1)->where('district_id', user_office_info()->district_id)->count();

                    $data['notifications'] = $data['pending_case_list'] + $data['trial_date_list'];

                    // Drildown Statistics
                    $division_list = DB::table('division')
                        ->select('division.id', 'division.division_name_bn', 'division.division_name_en')
                        ->get();
                        
                        $data['cartify_copy_req_data']=null;
                        $data['cartify_copy_fee_complete']=null;
                    if (globalUserInfo()->role_id==6) {
                        $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['DM_FOR_CERTIFICATE_COPY'])->where('district_id', user_district()->id)->get();
                        $data['cartify_copy_fee_complete'] = GccAppeal::whereIn('appeal_process_fee_status', ['CERTIFY_COPY_FEE_COMPLETE'])->where('district_id', user_district()->id)->get();
                        // dd(user_district()->id);
                    }
                    if (globalUserInfo()->role_id==11) {
                        $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RRDC'])->where('district_id', user_district()->id)->get();
                        $data['cartify_copy_fee_complete'] = GccAppeal::whereIn('appeal_process_fee_status', ['CERTIFY_COPY_FEE_COMPLETE'])->where('district_id', user_district()->id)->get();
                    }
                    if (globalUserInfo()->role_id==12) {
                        $data['cartify_copy_req_data'] = GccAppeal::whereIn('appeal_process_status', ['SENT_TO_RK'])->where('district_id', user_district()->id)->get();
                        $data['cartify_copy_fee_complete'] = GccAppeal::whereIn('appeal_process_fee_status', ['CERTIFY_COPY_FEE_COMPLETE'])->where('district_id', user_district()->id)->get();
                    }
                    if ($data['cartify_copy_req_data']) {
                        $data['certify_copy_count']=count($data['cartify_copy_req_data']);
                    }
                    if ($data['cartify_copy_fee_complete']) {
                        $data['cartify_copy_fee_complete']=count($data['cartify_copy_fee_complete']);
                    }
                    

                    $divisiondata = array();
                    $districtdata = array();
                    // $dis_data=array();
                    $upazilatdata = array();

                    // Division List
                    foreach ($division_list as $division) {
                        // $data_arr[$item->id] = $this->get_drildown_case_count($item->id);
                        // Division Data
                        $data['divisiondata'][] = array('name' => $division->division_name_bn, 'y' => $this->get_drildown_case_count($division->id), 'drilldown' => $division->id);

                        // District List
                        $district_list = DB::table('district')->select('district.id', 'district.district_name_bn')->where('division_id', $division->id)->get();
                        foreach ($district_list as $district) {
                            // $dis_count = $this->Employee_model->get_count_employees('', '', '', $district->id);
                            // $number2 = (int) $dis_count['count']; //exit;
                            $dis_data[$division->id][] = array('name' => $district->district_name_bn, 'y' => $this->get_drildown_case_count('', $district->id), 'drilldown' => $district->id);
                            // Upazila Data
                            // $upazila_list = $this->Common_model->get_data_where('upazilas', 'district_id', $district->id);
                            $upazila_list = DB::table('upazila')->select('upazila.id', 'upazila.upazila_name_bn')->where('district_id', $district->id)->get();
                            foreach ($upazila_list as $upazila) {
                                // $upa_count = $this->Employee_model->get_count_employees('', '', '', '', $upazila->id);
                                // $number3 = (int) $upa_count['count']; //exit;
                                $upa_data[$district->id][] = array($upazila->upazila_name_bn, $this->get_drildown_case_count('', '', $upazila->id));
                            }

                            $upadata = $upa_data[$district->id];
                            $upazilatdata[] = array('name' => $district->district_name_bn, 'id' => $district->id, 'data' => $upadata);
                        }

                        $disdata = $dis_data[$division->id];
                        $districtdata[] = array('name' => $division->division_name_bn, 'id' => $division->id, 'data' => $disdata);

                        $data['dis_upa_data'] = array_merge($upazilatdata, $districtdata); //$districtdata;  $upazilatdata;
                    }
                    // dd($result);
                    // $data['divisiondata'] = $divisiondata;
                    // dd($data['division_arr']);

                    $hearingCalender = CaseHearing::select('id', 'case_id', 'hearing_comment', 'hearing_date', DB::raw('count(*) as total'))
                        ->orderby('id', 'DESC')
                        ->groupBy('hearing_date');
                    $data['hearingCalender'] = CaseHearingCollection::collection($hearingCalender->get());

                    $data['upazilas'] = DB::table('upazila')->select('id', 'upazila_name_bn')->where('district_id', user_office_info()->district_id)->get();

                    // View
                    $data['page_title'] = 'আদালত';
                    return view('dashboard.admin_dc')->with($data);
                }
                
                if ($user_court_info->role_id == 27) {
                    $data['total_case'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL', 'CLOSED'])->where('court_id', $user_court_info->court_id)->count();
                    $data['running_case'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL'])->where('court_id', $user_court_info->court_id)->count();
                    $data['completed_case'] = GccAppeal::where('appeal_status', 'CLOSED')->where('court_id', $user_court_info->court_id)->count();
                    $data['pending_case'] = GccAppeal::whereIn('appeal_status', ['SEND_TO_GCO', 'SEND_TO_ASST_GCO'])->where('court_id', $user_court_info->court_id)->count();
                    $data['draft_case'] = GccAppeal::where('appeal_status', 'DRAFT')->where('court_id', $user_court_info->court_id)->count();
                    $data['rejected_case'] = GccAppeal::where('appeal_status', 'REJECTED')->where('court_id', $user_court_info->court_id)->count();
                    $data['postpond_case'] = GccAppeal::where('appeal_status', 'POSTPONED')->where('court_id', $user_court_info->court_id)->count();
                    $data['total_office'] = DB::table('office')->where('is_gcc', 1)->whereNotIn('id', [1, 2, 7])->count();
                    $data['total_user'] = DB::table('users')->count();
                    $data['total_court'] = DB::table('court')->whereNotIn('id', [1, 2])->count();
                    $data['total_mouja'] = DB::table('mouja')->count();
                    $data['total_ct'] = DB::table('case_type')->count();
                    $data['pending_case_list'] = GccAppeal::orderby('id', 'desc')->whereIn('appeal_status', ['SEND_TO_GCO'])->where('court_id', $user_court_info->court_id)->count();
                    $data['trial_date_list'] = GccAppeal::orderby('id', 'desc')->where('next_date', date('Y-m-d', strtotime(now())))->where('is_hearing_required', 1)->where('court_id', $user_court_info->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->count();
                    $data['CaseRunningCountActionRequired'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL'])
                        ->where('court_id', $user_court_info->court_id)
                        ->where('action_required', 'GCO')
                        ->count();
                    $data['notifications'] = $data['pending_case_list'] + $data['trial_date_list'] + $data['CaseRunningCountActionRequired'];
                    /*$data['cases'] = DB::table('case_register')
                    ->select('case_register.*')
                    ->get();*/

                    // Drildown Statistics
                    $division_list = DB::table('division')
                        ->select('division.id', 'division.division_name_bn', 'division.division_name_en')
                        ->get();

                    $divisiondata = array();
                    $districtdata = array();
                    // $dis_data=array();
                    $upazilatdata = array();

                    /*****
                     * This is needed when a2i said no need latest one 
                     */
                    $appeal = GccAppeal::where('court_id', globalUserInfo()->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->orderBy('updated_at', 'DESC')->limit(10)->get();

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
                        }
                    } else {

                        $data['appeal'][$key]['citizen_info'] = '';
                        $data['appeal'][$key]['notes'] = '';
                    }



                    $hearingCalender = CaseHearing::select('id', 'case_id', 'hearing_comment', 'hearing_date', DB::raw('count(*) as total'))
                        ->orderby('id', 'DESC')
                        ->groupBy('hearing_date');
                    $data['hearingCalender'] = CaseHearingCollection::collection($hearingCalender->get());

                    $data['running_case_paginate'] = GccAppeal::where('court_id', $user_court_info->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->count();

                    // return $data;
                    // View
                    $data['page_title'] = 'জেনারেল সার্টিফিকেট অফিসার ড্যাশবোর্ড';
                    return view('dashboard.gco')->with($data);
                }
                if ($user_court_info->role_id == 28) {

                    $data['total_case'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL', 'CLOSED'])->where('court_id', $user_court_info->court_id)->count();
                    $data['running_case'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL'])->where('court_id', $user_court_info->court_id)->count();
                    $data['completed_case'] = GccAppeal::where('appeal_status', 'CLOSED')->where('court_id', $user_court_info->court_id)->count();
                    $data['pending_case'] = GccAppeal::whereIn('appeal_status', ['SEND_TO_ASST_GCO'])->where('court_id', $user_court_info->court_id)->count();

                    $data['draft_case'] = GccAppeal::where('appeal_status', 'DRAFT')->where('court_id', $user_court_info->court_id)->count();
                    $data['rejected_case'] = GccAppeal::where('appeal_status', 'REJECTED')->where('court_id', $user_court_info->court_id)->count();
                    $data['postpond_case'] = GccAppeal::where('appeal_status', 'POSTPONED')->where('court_id', $user_court_info->court_id)->count();

                    $data['total_office'] = DB::table('office')->where('is_gcc', 1)->whereNotIn('id', [1, 2, 7])->count();
                    $data['total_user'] = DB::table('users')->count();
                    $data['total_court'] = DB::table('court')->whereNotIn('id', [1, 2])->count();

                    $data['pending_case_list'] = GccAppeal::orderby('id', 'desc')->whereIn('appeal_status', ['SEND_TO_ASST_GCO'])->where('court_id', $user_court_info->court_id)->count();
                    $data['trial_date_list'] = GccAppeal::orderby('id', 'desc')->where('next_date', date('Y-m-d', strtotime(now())))->where('is_hearing_required', 1)->where('court_id', $user_court_info->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->count();
                    $data['CaseRunningCountActionRequired'] = GccAppeal::whereIn('appeal_status', ['ON_TRIAL'])
                        ->where('court_id', $user_court_info->court_id)
                        ->where('action_required', 'ASST')
                        ->count();
                    $data['notifications'] = $data['pending_case_list'] + $data['trial_date_list'] + $data['CaseRunningCountActionRequired'];

                    $data['cases'] = DB::table('case_register')
                        ->select('case_register.*')
                        ->get();

                    // Drildown Statistics
                    $division_list = DB::table('division')
                        ->select('division.id', 'division.division_name_bn', 'division.division_name_en')
                        ->get();
                    $data['request_for_nothi']=GccAppeal::where('is_required_for_nothi', 1)
                        ->where('court_id', $user_court_info->court_id)
                        ->count();
                     
                    $divisiondata = array();
                    $districtdata = array();
                    // $dis_data=array();
                    $upazilatdata = array();

                    // Division List
                    foreach ($division_list as $division) {
                        // $data_arr[$item->id] = $this->get_drildown_case_count($item->id);
                        // Division Data
                        $data['divisiondata'][] = array('name' => $division->division_name_bn, 'y' => $this->get_drildown_case_count($division->id), 'drilldown' => $division->id);

                        // District List
                        $district_list = DB::table('district')->select('district.id', 'district.district_name_bn')->where('division_id', $division->id)->get();
                        foreach ($district_list as $district) {
                            // $dis_count = $this->Employee_model->get_count_employees('', '', '', $district->id);
                            // $number2 = (int) $dis_count['count']; //exit;
                            $dis_data[$division->id][] = array('name' => $district->district_name_bn, 'y' => $this->get_drildown_case_count('', $district->id), 'drilldown' => $district->id);

                            // Upazila Data
                            // $upazila_list = $this->Common_model->get_data_where('upazilas', 'district_id', $district->id);
                            $upazila_list = DB::table('upazila')->select('upazila.id', 'upazila.upazila_name_bn')->where('district_id', $district->id)->get();
                            foreach ($upazila_list as $upazila) {
                                // $upa_count = $this->Employee_model->get_count_employees('', '', '', '', $upazila->id);
                                // $number3 = (int) $upa_count['count']; //exit;
                                $upa_data[$district->id][] = array($upazila->upazila_name_bn, $this->get_drildown_case_count('', '', $upazila->id));
                            }

                            $upadata = $upa_data[$district->id];
                            $upazilatdata[] = array('name' => $district->district_name_bn, 'id' => $district->id, 'data' => $upadata);
                        }

                        $disdata = $dis_data[$division->id];
                        $districtdata[] = array('name' => $division->division_name_bn, 'id' => $division->id, 'data' => $disdata);

                        $data['dis_upa_data'] = array_merge($upazilatdata, $districtdata); //$districtdata;  $upazilatdata;
                    }



                    $appeal = GccAppeal::where('court_id', globalUserInfo()->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->orderBy('updated_at', 'DESC')->limit(10)->get();

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
                        }
                    } else {

                        $data['appeal'][$key]['citizen_info'] = '';
                        $data['appeal'][$key]['notes'] = '';
                    }


                    $hearingCalender = CaseHearing::select('id', 'case_id', 'hearing_comment', 'hearing_date', DB::raw('count(*) as total'))
                        ->orderby('id', 'DESC')
                        ->groupBy('hearing_date');
                    $data['hearingCalender'] = CaseHearingCollection::collection($hearingCalender->get());
                    $data['running_case_paginate'] = GccAppeal::where('court_id', $user_court_info->court_id)->whereIn('appeal_status', ['ON_TRIAL'])->count();
                    // View
                    $data['page_title'] = 'সার্টিফিকেট সহকারী ড্যাশবোর্ড';

                    return view('dashboard.certificate_assistent')->with($data);
                }
            }
        }
    }


    public function ajaxCaseStatus(Request $request)
    {
        // dd($request->division);
        // Get Data
        $roleID = Auth::user()->role_id;
        $result = [];
        $str = '';
        $data['division'] = $request->division;
        $data['district'] = $request->district;
        $data['upazila'] = $request->upazila;
        // Convert DB date formate
        $data['dateFrom'] = isset($request->dateFrom) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateFrom))) : null;
        $data['dateTo'] = isset($request->dateTo) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateTo))) : null;

        // Data filtering
        if ($request) {
            if ($roleID == 2 || $roleID == 25) { // Superadmin

                if ($request->division) {
                    $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
                    $str = $divisionName . ' বিভাগের ';
                }
                if ($request->district) {
                    $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
                    $str .= $districtName . ' জেলার ';
                }
                if ($request->upazila) {
                    $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
                    $str .= $upazilaName . ' উপজেলা/থানার ';
                }

                if ($request->division) {

                    $str .= 'তথ্য';
                }
            } elseif ($roleID == 34) { // Divitional Comm.
                if ($request->district) {
                    $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
                    $str = $districtName . ' জেলার তথ্য';
                }
            } elseif ($roleID == 6) { // DC
                if ($request->upazila) {
                    $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
                    $str = $upazilaName . ' উপজেলা/থানার তথ্য';
                }
            }

            // Get Statistics
            $result['ON_TRIAL'] = $this->statistics_case_status('ON_TRIAL', $data);
            $result['SEND_TO_GCO'] = $this->statistics_case_status('SEND_TO_GCO', $data);
            $result['SEND_TO_ASST_GCO'] = $this->statistics_case_status('SEND_TO_ASST_GCO', $data);
            $result['SEND_TO_DC'] = $this->statistics_case_status('SEND_TO_DC', $data);
            $result['SEND_TO_DIV_COM'] = $this->statistics_case_status('SEND_TO_DIV_COM', $data);
            $result['SEND_TO_LAB_CM'] = $this->statistics_case_status('SEND_TO_LAB_CM', $data);
            $result['CLOSED'] = $this->statistics_case_status('CLOSED', $data);
            $result['REJECTED'] = $this->statistics_case_status('REJECTED', $data);
            $result['ON_TRIAL_DC'] = $this->statistics_case_status('ON_TRIAL_DC', $data);
            $result['ON_TRIAL_DIV_COM'] = $this->statistics_case_status('ON_TRIAL_DIV_COM', $data);
            $result['ON_TRIAL_LAB_CM'] = $this->statistics_case_status('ON_TRIAL_LAB_CM', $data);
        } else {
            if ($roleID == 2 || $roleID == 25) { // Superadmin

                if ($request->division) {
                    $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
                    $str = $divisionName . ' বিভাগের ';
                }
                if ($request->district) {
                    $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
                    $str .= $districtName . ' জেলার ';
                }
                if ($request->upazila) {
                    $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
                    $str .= $upazilaName . ' উপজেলা/থানার ';
                }

                if ($request->division) {

                    $str .= 'তথ্য';
                }
            } elseif ($roleID == 34) { // Divitional Comm.
                $str = 'সকল জেলার তথ্য';
            } elseif ($roleID == 6) { // DC
                $str = 'সকল উপজেলা/থানার তথ্য';
            }

            $result['ON_TRIAL'] = $this->statistics_case_status('ON_TRIAL', '');
            $result['SEND_TO_GCO'] = $this->statistics_case_status('SEND_TO_GCO', '');
            $result['SEND_TO_ASST_GCO'] = $this->statistics_case_status('SEND_TO_ASST_GCO', '');
            $result['SEND_TO_DC'] = $this->statistics_case_status('SEND_TO_DC', '');
            $result['SEND_TO_DIV_COM'] = $this->statistics_case_status('SEND_TO_DIV_COM', $data);
            $result['SEND_TO_LAB_CM'] = $this->statistics_case_status('SEND_TO_LAB_CM', $data);
            $result['CLOSED'] = $this->statistics_case_status('CLOSED', '');
            $result['REJECTED'] = $this->statistics_case_status('REJECTED', '');
            $result['ON_TRIAL_DC'] = $this->statistics_case_status('ON_TRIAL_DC', '');
            $result['ON_TRIAL_DIV_COM'] = $this->statistics_case_status('ON_TRIAL_DIV_COM', '');
            $result['ON_TRIAL_LAB_CM'] = $this->statistics_case_status('ON_TRIAL_LAB_CM', '');
        }
        // print_r($result); exit;

        return response()->json(['msg' => $str, 'data' => $result]);
    }

    //for admin
    public function ajaxCaseStatus_new(Request $request)
    {

        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas = $json_data['body_data'];
        $roleID = $datas['role_id'];
        // return ['ss dtest' => $request->district];
        $result = [];
        $str = '';
        $data['division'] = $datas['division'];
        $data['district'] = $datas['district'];
        $data['upazila'] = $datas['upazila'];
        $data['role_id'] = $roleID;
        // Convert DB date formate
        $data['dateFrom'] = isset($datas['dateFrom']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateFrom']))) : null;
        $data['dateTo'] = isset($datas['dateTo']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateTo']))) : null;
        $division_id = $datas['division_id'];
        // Data filtering
        if ($roleID == 2 || $roleID == 25 || $roleID == 8) { // Superadmin

            if ($data['division']) {
                $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
                $str = $divisionName . ' বিভাগের ';
            }
            if ($data['district']) {
                $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;
                $str .= $districtName . ' জেলার ';
            }
            if ($data['upazila']) {
                $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;
                $str .= $upazilaName . ' উপজেলা/থানার ';
            }

            if ($request->division) {

                $str .= 'তথ্য';
            }
            // return ['for gco'=> $data];
        } elseif ($roleID == 34) { // Divitional Comm.
            // if ($request->district) {
            //     $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
            //     $str = $districtName . ' জেলার তথ্য';
            // }
            $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $division_id)->first()->division_name_bn;
            $str = $divisionName . ' বিভাগের ';
            $data['division'] = $division_id;
            if ($data['district']) {
                $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;
                $str .= $districtName . ' জেলার ';
            }
            if ($data['upazila']) {
                $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;
                $str .= $upazilaName . ' উপজেলা/থানার ';
            }
            // return ['for dom'=> $data];
        }

        // Get Statistics
        $result['ON_TRIAL'] = $this->statistics_case_status('ON_TRIAL', $data);
        $result['SEND_TO_GCO'] = $this->statistics_case_status('SEND_TO_GCO', $data);
        $result['SEND_TO_ASST_GCO'] = $this->statistics_case_status('SEND_TO_ASST_GCO', $data);
        $result['SEND_TO_DC'] = $this->statistics_case_status('SEND_TO_DC', $data);
        $result['SEND_TO_DIV_COM'] = $this->statistics_case_status('SEND_TO_DIV_COM', $data);
        $result['SEND_TO_LAB_CM'] = $this->statistics_case_status('SEND_TO_LAB_CM', $data);
        $result['CLOSED'] = $this->statistics_case_status('CLOSED', $data);
        $result['REJECTED'] = $this->statistics_case_status('REJECTED', $data);
        $result['ON_TRIAL_DC'] = $this->statistics_case_status('ON_TRIAL_DC', $data);
        $result['ON_TRIAL_DIV_COM'] = $this->statistics_case_status('ON_TRIAL_DIV_COM', $data);
        $result['ON_TRIAL_LAB_CM'] = $this->statistics_case_status('ON_TRIAL_LAB_CM', $data);

        return $this->sendResponse($result, 'তথ্য পাওয়া গেছে ');
    }

    //for parent office
    public function dashboard_data_for_parent_office(Request $request)
    {
        // return 'come from gcc';
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas = $json_data['body_data'];
        $parent_id = $datas['parent_id'];
        $result['total_case'] = $this->data_for_parent_office(['ON_TRIAL', 'CLOSED'], $parent_id);
        $result['running_case'] = $this->data_for_parent_office(['ON_TRIAL'], $parent_id);
        $result['pending_case'] = $this->data_for_parent_office(['SEND_TO_ASST_GCO', 'SEND_TO_GCO'], $parent_id);
        $result['completed_case'] = $this->data_for_parent_office(['CLOSED'], $parent_id);

        return $this->sendResponse($result, 'তথ্য পাওয়া গেছে ');
    }

    //for parent office appeal traking
    public function parent_office_appeal_traking(Request $request)
    {
        //    return 'come from gcc';
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);


        $datas = $json_data['body_data'];

        $id = $datas['parent_id'];
        $data['shortOrderNameList'] = NoteRepository::get_order_list($id);
        $data['appeal'] = GccAppeal::findOrFail($id);

        $data['page_title'] = 'মামলা ট্র্যাকিং';


        return $this->sendResponse($data, 'তথ্য পাওয়া গেছে ');
    }

    //parent office appeal details
    public function parent_office_appeal_details(Request $request)
    {
        //    return 'come from gcc';
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);


        $datas = $json_data['body_data'];

        $id = $datas['parent_id'];

        $appeal = GccAppeal::findOrFail($id);
        $data = AppealRepository::getAllAppealInfo_for_parent_office($id);
        $data['appeal']  = $appeal;
        $data["notes"] = $appeal->appealNotes;

        //    return $data;

        $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';


        return $this->sendResponse($data, 'তথ্য পাওয়া গেছে ');
    }

    public function ajaxPieChart(Request $request)
    {
        $roleID = Auth::user()->role_id;
        $result = [];
        $str = '';
        $data['division'] = $request->division;
        $data['district'] = $request->district;
        $data['upazila'] = $request->upazila;
        $totalClaimed = 0;
        $totalReceived = 0;
        // Convert DB date formate
        $data['dateFrom'] = isset($request->dateFrom) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateFrom))) : null;
        $data['dateTo'] = isset($request->dateTo) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateTo))) : null;

        // Data filtering   
        if (!empty($request->division) && empty($request->district) && empty($request->district)) {
            $totalClaimed = 0;
            $totalReceived = 0;
            $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $data['districts'] = DB::table('district')->where('division_id', $request->division)->select('id', 'district_name_bn')->get();
            foreach ($data['districts'] as $key => $value) {
                $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $claimed = $this->statistics_pament_claimed_by_district($value->id, $data);
                $received = $this->statistics_payment_received_by_district($value->id, $data);
                $data['results'][$key]['claimed'] = $claimed;
                $data['results'][$key]['received'] = $received;
                $totalClaimed += $claimed;
                $totalReceived += $received;
            }
            $data['totalClaimed'] = $totalClaimed;
            $data['totalReceived'] = $totalReceived;
        } elseif (!empty($request->division) && !empty($request->district) && empty($request->upazila)) {
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $totalClaimed = 0;
            $totalReceived = 0;
            $data['upazilas'] = DB::table('upazila')->where('district_id', $request->district)->select('id', 'upazila_name_bn')->get();
            foreach ($data['upazilas'] as $key => $value) {
                $data['results'][$key]['upazila_name_bn'] = $value->upazila_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $claimed = $this->statistics_pament_claimed_by_division($value->id, $data);
                $received = $this->statistics_payment_received_by_division($value->id, $data);
                $data['results'][$key]['claimed'] = $claimed;
                $data['results'][$key]['received'] = $received;
                $totalClaimed += $claimed;
                $totalReceived += $received;
            }
            $data['totalClaimed'] = $totalClaimed;
            $data['totalReceived'] = $totalReceived;
        } elseif (!empty($request->division) && !empty($request->district) && !empty($request->upazila)) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($request->upazila, $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($request->upazila, $data);
            $data['totalClaimed'] = $totalClaimed;
            $data['totalReceived'] = $totalReceived;
        } elseif (!empty($request->division) && !empty($request->district) && !empty($request->upazila)) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($request->upazila, $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($request->upazila, $data);
            $data['totalClaimed'] = $totalClaimed;
            $data['totalReceived'] = $totalReceived;
        } elseif (empty($request->division) && empty($request->district) && !empty($request->upazila)) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($request->upazila, $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($request->upazila, $data);
            $data['totalClaimed'] = $totalClaimed;
            $data['totalReceived'] = $totalReceived;
        } else {
            if ($roleID == 8 || $roleID == 2 || $roleID == 25) { // Superadmin
                // dd('come here');
                $str = 'সকল বিভাগের তথ্য';

                $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();


                foreach ($data['divisions'] as $key => $value) {
                    $data['results'][$key]['division_name_bn'] = $value->division_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $claimed = $this->statistics_pament_claimed_by_division($value->id, $data);
                    $received = $this->statistics_payment_received_by_division($value->id, $data);
                    $data['results'][$key]['claimed'] = $claimed;
                    $data['results'][$key]['received'] = $received;
                    $totalClaimed += $claimed;
                    $totalReceived += $received;
                }
                $data['totalClaimed'] = $totalClaimed;
                $data['totalReceived'] = $totalReceived;
            }
        }
        // print_r($result); exit;

        return response()->json(['msg' => $str, 'data' => $data]);
    }

    //for admin
    public function ajaxPieChart_new(Request $request)
    {
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas = $json_data['body_data'];



        $roleID = $datas['role_id'];
        $result = [];
        $str = '';
        $data['division'] = $datas['division'];
        $data['district'] = $datas['district'];
        $data['upazila'] = $datas['upazila'];
        $data['role_id'] = $roleID;
        $totalClaimed = 0;
        $totalReceived = 0;
        // Convert DB date formate
        $data['dateFrom'] = isset($datas['dateFrom']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateFrom']))) : null;
        $data['dateTo'] = isset($datas['dateTo']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateTo']))) : null;



        // Data filtering   
        if (!empty($data['division']) && empty($data['district']) && empty($data['upazila'])) {
            $totalClaimed = 0;
            $totalReceived = 0;
            $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $data['districts'] = DB::table('district')->where('division_id', $data['division'])->select('id', 'district_name_bn')->get();
            foreach ($data['districts'] as $key => $value) {
                $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $claimed = $this->statistics_pament_claimed_by_district($value->id, $data);
                $received = $this->statistics_payment_received_by_district($value->id, $data);
                $data['results'][$key]['claimed'] = $claimed;
                $data['results'][$key]['received'] = $received;
                $totalClaimed += $claimed;
                $totalReceived += $received;
            }
            $result['totalClaimed'] = $totalClaimed;
            $result['totalReceived'] = $totalReceived;
        } elseif (!empty($data['division']) && !empty($data['district']) && empty($data['upazila'])) {
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $totalClaimed = 0;
            $totalReceived = 0;
            $data['upazilas'] = DB::table('upazila')->where('district_id', $data['district'])->select('id', 'upazila_name_bn')->get();
            foreach ($data['upazilas'] as $key => $value) {
                $data['results'][$key]['upazila_name_bn'] = $value->upazila_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $claimed = $this->statistics_pament_claimed_by_division($value->id, $data);
                $received = $this->statistics_payment_received_by_division($value->id, $data);
                $data['results'][$key]['claimed'] = $claimed;
                $data['results'][$key]['received'] = $received;
                $totalClaimed += $claimed;
                $totalReceived += $received;
            }
            $result['totalClaimed'] = $totalClaimed;
            $result['totalReceived'] = $totalReceived;
        } elseif (!empty($data['division']) && !empty($data['district']) && !empty($data['upazila'])) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($data['upazila'], $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($data['upazila'], $data);
            $result['totalClaimed'] = $totalClaimed;
            $result['totalReceived'] = $totalReceived;
        } elseif (!empty($data['division']) && !empty($data['district']) && !empty($data['upazila'])) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($data['upazila'], $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($data['upazila'], $data);
            $result['totalClaimed'] = $totalClaimed;
            $result['totalReceived'] = $totalReceived;
        } elseif (empty($data['division']) && empty($data['district']) && !empty($data['upazila'])) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;

            $totalClaimed = $this->statistics_pament_claimed_by_upazila($data['upazila'], $data);
            $totalReceived = $this->statistics_payment_received_by_upazila($data['upazila'], $data);
            $result['totalClaimed'] = $totalClaimed;
            $result['totalReceived'] = $totalReceived;
        } else {
            if ($roleID == 8 || $roleID == 2 || $roleID == 25) { // Superadmin
                // dd('come here');
                $str = 'সকল বিভাগের তথ্য';

                $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();


                foreach ($data['divisions'] as $key => $value) {
                    $data['results'][$key]['division_name_bn'] = $value->division_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $claimed = $this->statistics_pament_claimed_by_division($value->id, $data);
                    $received = $this->statistics_payment_received_by_division($value->id, $data);
                    $data['results'][$key]['claimed'] = $claimed;
                    $data['results'][$key]['received'] = $received;
                    $totalClaimed += $claimed;
                    $totalReceived += $received;
                }
                $result['totalClaimed'] = $totalClaimed;
                $result['totalReceived'] = $totalReceived;
            }
        }
        // print_r($result); exit;
        return $this->sendResponse($result, $str);
    }

    public function ajaxPaymentReport(Request $request)
    {
        // dd($request->division);
        
        
        // Get Data
        $roleID = globalUserInfo()->role_id;
    
        $result = [];
        $str = '';
        $data['division'] = $request->division;
        $data['district'] = $request->district;
        $data['upazila'] = $request->upazila;
        // Convert DB date formate
        $data['dateFrom'] = isset($request->dateFrom) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateFrom))) : null;
        $data['dateTo'] = isset($request->dateTo) ? date('Y-m-d', strtotime(str_replace('/', '-', $request->dateTo))) : null;

        // Data filtering
        if (!empty($request->division) && empty($request->district) && empty($request->district)) {
            $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $data['districts'] = DB::table('district')->where('division_id', $request->division)->select('id', 'district_name_bn')->get();
            foreach ($data['districts'] as $key => $value) {
                $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_district($value->id, $data);
                $data['results'][$key]['received'] = $this->statistics_payment_received_by_district($value->id, $data);
            }
            $str = $divisionName . ' বিভাগের তথ্য';
            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
         <thead class="table">
            <tr>
               <th class="text-left">জেলার নাম</th>
               <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
               <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
            </tr>
         </thead>
         <tbody>';
            $i = $grandTotal = 0;
            foreach ($data['results'] as $row) {
                $i++;

                $result .= '<tr>
               <td class="text-left">' . $row["district_name_bn"] . '</td>
               <td class="text-center">' . $row["claimed"] . '</td>
               <td class="text-center">' . $row["received"] . '</td>
            </tr>';
            }
            $result .= '</tbody>
            </table>';
        } elseif (!empty($request->division) && !empty($request->district) && empty($request->upazila)) {
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;

            $data['upazilas'] = DB::table('upazila')->where('district_id', $request->district)->select('id', 'upazila_name_bn')->get();
            foreach ($data['upazilas'] as $key => $value) {
                $data['results'][$key]['upazila_name_bn'] = $value->upazila_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_upazila($value->id, $data);
                $data['results'][$key]['received'] = $this->statistics_payment_received_by_upazila($value->id, $data);
            }
            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
            <thead class="table">
                <tr>
                    <th class="text-left">উপজেলার নাম</th>
                    <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                    <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                </tr>
            </thead>
            <tbody>';
            $i = $grandTotal = 0;
            foreach ($data['results'] as $row) {
                $i++;

                $result .= '<tr>
                    <td class="text-left">' . $row["upazila_name_bn"] . '</td>
                    <td class="text-center">' . $row["claimed"] . '</td>
                    <td class="text-center">' . $row["received"] . '</td>
                </tr>';
            }
            $result .= '</tbody>
                </table>';
        } elseif (!empty($request->division) && !empty($request->district) && !empty($request->upazila)) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $request->division)->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $request->district)->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $claimed = $this->statistics_pament_claimed_by_upazila($request->upazila, $data);
            $received = $this->statistics_payment_received_by_upazila($request->upazila, $data);

            $str .= $upazilaName . ' উপজেলা/থানার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                    <thead class="table">
                        <tr>
                            <th class="text-left">উপজেলার নাম</th>
                            <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                            <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-left">' . $upazilaName . '</td>
                        <td class="text-center">' . $claimed . '</td>
                        <td class="text-center">' . $received . '</td>
                    </tr>';
            $result .= '</tbody>
                </table>';
        } elseif (empty($request->division) && empty($request->district) && !empty($request->upazila)) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $request->upazila)->first()->upazila_name_bn;

            $claimed = $this->statistics_pament_claimed_by_upazila($request->upazila, $data);
            $received = $this->statistics_payment_received_by_upazila($request->upazila, $data);

            $str = $upazilaName . ' উপজেলা/থানার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                <thead class="table">
                    <tr>
                        <th class="text-left">উপজেলার নাম</th>
                        <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                        <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-left">' . $upazilaName . '</td>
                    <td class="text-center">' . $claimed . '</td>
                    <td class="text-center">' . $received . '</td>
                </tr>';
            $result .= '</tbody>
                </table>';
        } else {

            // Check role wise
            if ($roleID == 2 || $roleID == 8  || $roleID == 25) { // Superadmin
                $str = 'সকল বিভাগের তথ্য';

                $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();
                foreach ($data['divisions'] as $key => $value) {
                    $data['results'][$key]['division_name_bn'] = $value->division_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_division($value->id, $data);
                    $data['results'][$key]['received'] = $this->statistics_payment_received_by_division($value->id, $data);
                }

                $result = '';
                $result .= '<table class="table table-hover table-bordered report">
                 <thead class="table">
                <tr>
                <th class="text-left">বিভাগের নাম</th>
                <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                </tr>
                </thead>
                <tbody>';
                $i = $grandTotal = 0;
                foreach ($data['results'] as $row) {
                    $i++;

                    $result .= '<tr>
               <td class="text-left">' . $row["division_name_bn"] . '</td>
               <td class="text-center">' . $row["claimed"] . '</td>
               <td class="text-center">' . $row["received"] . '</td>
                </tr>';
                }
                $result .= '</tbody>
                </table>';
            } elseif ($roleID == 34) { // Divitional Comm.
                $str = 'সকল জেলার তথ্য';
                $data['districts'] = DB::table('district')->where('division_id', user_office_info()->division_id)->select('id', 'district_name_bn')->get();
                // $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();

                foreach ($data['districts'] as $key => $value) {
                    $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_district($value->id, $data);
                    $data['results'][$key]['received'] = $this->statistics_payment_received_by_district($value->id, $data);
                }

                $result = '';
                $result .= '<table class="table table-hover table-bordered report">
                <thead class="table">
                    <tr>
                    <th class="text-left">জেলার নাম</th>
                    <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                    <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                    </tr>
                </thead>
                <tbody>';
                $i = $grandTotal = 0;
                foreach ($data['results'] as $row) {
                    $i++;

                    $result .= '<tr>
                    <td class="text-left">' . $row["district_name_bn"] . '</td>
                    <td class="text-center">' . $row["claimed"] . '</td>
                    <td class="text-center">' . $row["received"] . '</td>
                    </tr>';
                }
                $result .= '</tbody>
                </table>';
            } elseif ($roleID == 6|| $roleID == 7) { // DC
                $str = 'উপজেলা/থানার তথ্য';

                $data['upazilas'] = DB::table('upazila')->where('district_id', user_office_info()->district_id)->select('id', 'upazila_name_bn')->get();
                // $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();

                foreach ($data['upazilas'] as $key => $value) {
                    $data['results'][$key]['upazila_name_bn'] = $value->upazila_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_upazila($value->id, $data);
                    $data['results'][$key]['received'] = $this->statistics_payment_received_by_upazila($value->id, $data);
                }
                $result = '';
                $result .= '<table class="table table-hover table-bordered report">
                    <thead class="table">
                        <tr>
                        <th class="text-left">উপজেলা/ থানার নাম</th>
                        <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                        <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>';
                $i = $grandTotal = 0;
                foreach ($data['results'] as $row) {
                    $i++;

                    $result .= '<tr>
                        <td class="text-left">' . $row["upazila_name_bn"] . '</td>
                        <td class="text-center">' . $row["claimed"] . '</td>
                        <td class="text-center">' . $row["received"] . '</td>
                        </tr>';
                }
                $result .= '</tbody>
                </table>';
            }
        }

        return response()->json(['msg' => $str, 'data' => $result]);
    }

    //for admin
    public function ajaxPaymentReport_new(Request $request)
    {
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas = $json_data['body_data'];



        $roleID = $datas['role_id'];
        $result = [];
        $str = '';
        $data['division'] = $datas['division'];
        $data['district'] = $datas['district'];
        $data['upazila'] = $datas['upazila'];
        $data['role_id'] = $roleID;

        // Convert DB date formate
        $data['dateFrom'] = isset($datas['dateFrom']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateFrom']))) : null;
        $data['dateTo'] = isset($datas['dateTo']) ? date('Y-m-d', strtotime(str_replace('/', '-', $datas['dateTo']))) : null;
        // Data filtering
        if (!empty($data['division']) && empty($data['district']) && empty($data['upazila'])) {
            $divisionName = $division = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $data['districts'] = DB::table('district')->where('division_id', $data['division'])->select('id', 'district_name_bn')->get();
            foreach ($data['districts'] as $key => $value) {
                $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_district($value->id, $data);
                $data['results'][$key]['received'] = $this->statistics_payment_received_by_district($value->id, $data);
            }
            $str = $divisionName . ' বিভাগের তথ্য';
            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                <thead class="table">
                    <tr>
                    <th class="text-left">জেলার নাম</th>
                    <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                    <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                    </tr>
                </thead>
                <tbody>';
            $i = $grandTotal = 0;
            foreach ($data['results'] as $row) {
                $i++;

                $result .= '<tr>
                    <td class="text-left">' . $row["district_name_bn"] . '</td>
                    <td class="text-center">' . $row["claimed"] . '</td>
                    <td class="text-center">' . $row["received"] . '</td>
                </tr>';
            }
            $result .= '</tbody>
                </table>';
        } elseif (!empty($data['division']) && !empty($data['district']) && empty($data['upazila'])) {
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;

            $data['upazilas'] = DB::table('upazila')->where('district_id', $data['district'])->select('id', 'upazila_name_bn')->get();
            foreach ($data['upazilas'] as $key => $value) {
                $data['results'][$key]['upazila_name_bn'] = $value->upazila_name_bn;
                $data['results'][$key]['id'] = $value->id;
                $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_upazila($value->id, $data);
                $data['results'][$key]['received'] = $this->statistics_payment_received_by_upazila($value->id, $data);
            }
            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                    <thead class="table">
                        <tr>
                            <th class="text-left">উপজেলার নাম</th>
                            <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                            <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>';
            $i = $grandTotal = 0;
            foreach ($data['results'] as $row) {
                $i++;

                $result .= '<tr>
                            <td class="text-left">' . $row["upazila_name_bn"] . '</td>
                            <td class="text-center">' . $row["claimed"] . '</td>
                            <td class="text-center">' . $row["received"] . '</td>
                        </tr>';
            }
            $result .= '</tbody>
                        </table>';
        } elseif (!empty($data['division']) && !empty($data['district']) && !empty($data['upazila'])) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;
            $divisionName = DB::table('division')->select('division_name_bn')->where('id', $data['division'])->first()->division_name_bn;
            $districtName = DB::table('district')->select('district_name_bn')->where('id', $data['district'])->first()->district_name_bn;

            $str = $divisionName . ' বিভাগের ';
            $str .= $districtName . ' জেলার ';

            $claimed = $this->statistics_pament_claimed_by_upazila($data['upazila'], $data);
            $received = $this->statistics_payment_received_by_upazila($data['upazila'], $data);

            $str .= $upazilaName . ' উপজেলা/থানার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                    <thead class="table">
                        <tr>
                            <th class="text-left">উপজেলার নাম</th>
                            <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                            <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-left">' . $upazilaName . '</td>
                        <td class="text-center">' . $claimed . '</td>
                        <td class="text-center">' . $received . '</td>
                    </tr>';
            $result .= '</tbody>
                </table>';
        } elseif (empty($data['division']) && empty($data['district']) && !empty($data['upazila'])) {
            $upazilaName = DB::table('upazila')->select('upazila_name_bn')->where('id', $data['upazila'])->first()->upazila_name_bn;

            $claimed = $this->statistics_pament_claimed_by_upazila($data['upazila'], $data);
            $received = $this->statistics_payment_received_by_upazila($data['upazila'], $data);

            $str = $upazilaName . ' উপজেলা/থানার তথ্য';

            $result = '';
            $result .= '<table class="table table-hover table-bordered report">
                <thead class="table">
                    <tr>
                        <th class="text-left">উপজেলার নাম</th>
                        <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                        <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-left">' . $upazilaName . '</td>
                    <td class="text-center">' . $claimed . '</td>
                    <td class="text-center">' . $received . '</td>
                </tr>';
            $result .= '</tbody>
                </table>';
        } else {

            // >?Check role wise
            if ($roleID == 2 || $roleID == 8  || $roleID == 25) { // Superadmin
                $str = 'সকল বিভাগের তথ্য';

                $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();
                foreach ($data['divisions'] as $key => $value) {
                    $data['results'][$key]['division_name_bn'] = $value->division_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_division($value->id, $data);
                    $data['results'][$key]['received'] = $this->statistics_payment_received_by_division($value->id, $data);
                }

                $result = '';
                $result .= '<table class="table table-hover table-bordered report">
                 <thead class="table">
                <tr>
                <th class="text-left">বিভাগের নাম</th>
                <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                </tr>
                </thead>
                <tbody>';
                $i = $grandTotal = 0;
                foreach ($data['results'] as $row) {
                    $i++;

                    $result .= '<tr>
               <td class="text-left">' . $row["division_name_bn"] . '</td>
               <td class="text-center">' . $row["claimed"] . '</td>
               <td class="text-center">' . $row["received"] . '</td>
                </tr>';
                }
                $result .= '</tbody>
                </table>';
            } elseif ($roleID == 34) { // Divitional Comm.
                $str = 'সকল জেলার তথ্য';
                $data['districts'] = DB::table('district')->where('division_id', $data['division'])->select('id', 'district_name_bn')->get();
                // $data['divisions'] = DB::table('division')->select('id', 'division_name_bn')->get();

                foreach ($data['districts'] as $key => $value) {
                    $data['results'][$key]['district_name_bn'] = $value->district_name_bn;
                    $data['results'][$key]['id'] = $value->id;
                    $data['results'][$key]['claimed'] = $this->statistics_pament_claimed_by_district($value->id, $data);
                    $data['results'][$key]['received'] = $this->statistics_payment_received_by_district($value->id, $data);
                }

                $result = '';
                $result .= '<table class="table table-hover table-bordered report">
                    <thead class="table">
                        <tr>
                        <th class="text-left">জেলার নাম</th>
                        <th class="text-center">দাবীকৃত অর্থ (টাকা)</th>
                        <th class="text-center">আদায়কৃত অর্থ (টাকা)</th>
                        </tr>
                    </thead>
                    <tbody>';
                $i = $grandTotal = 0;
                foreach ($data['results'] as $row) {
                    $i++;

                    $result .= '<tr>
                            <td class="text-left">' . $row["district_name_bn"] . '</td>
                            <td class="text-center">' . $row["claimed"] . '</td>
                            <td class="text-center">' . $row["received"] . '</td>
                            </tr>';
                }
                $result .= '</tbody>
                        </table>';
            }
        }

        return $this->sendResponse($result, $str);
    }

    public function get_drildown_case_count($division = null, $district = null, $upazila = null, $status = null)
    {
        $query = DB::table('gcc_appeals')->whereNotIn('appeal_status', ['DRAFT']);

        if ($division != null) {
            $query->where('division_id', $division);
        }
        if ($district != null) {
            $query->where('district_id', $district);
        }
        if ($upazila != null) {
            $query->where('upazila_id', $upazila);
        }

        return $query->count();
    }

    //for admin
    public function get_drildown_case_count_new(Request $request)
    {


        $division_list = DB::table('division')
            ->select('division.id', 'division.division_name_bn', 'division.division_name_en', 'division.division_bbs_code')
            ->get();

        foreach ($division_list as $division) {

            $data['divisiondata'][] = array('name' => $division->division_name_bn, 'y' => $this->get_drildown_case_count($division->id), 'drilldown' => $division->id);

            // District List
            $district_list = DB::table('district')->select('district.id', 'district.district_name_bn', 'district.district_bbs_code')->where('division_id', $division->id)->get();
            foreach ($district_list as $district) {

                $dis_data[$division->id][] = array('name' => $district->district_name_bn, 'y' => $this->get_drildown_case_count('', $district->id), 'drilldown' => $district->district_bbs_code);

                $upazila_list = DB::table('upazila')->select('upazila.id', 'upazila.upazila_name_bn')->where('district_id', $district->id)
                    ->where('division_id', $division->id)->get();

                foreach ($upazila_list as $upazila) {
                    $upa_data[$district->district_bbs_code][] = array($upazila->upazila_name_bn, $this->get_drildown_case_count('', '', $upazila->id));
                }

                $upadata = $upa_data[$district->district_bbs_code];
                $upazilatdata[] = array('name' => $district->district_name_bn, 'id' => $district->district_bbs_code, 'data' => $upadata);
            }

            $disdata = $dis_data[$division->id];
            $districtdata[] = array('name' => $division->division_name_bn, 'id' => $division->id, 'data' => $disdata);

            $data['dis_upa_data'] = array_merge($upazilatdata, $districtdata); //$districtdata;  $upazilatdata;
            // $data['dis_upa_data'] = array_merge($upazilatdata);
        }


        return $this->sendResponse($data, null);
    }

    public function statistics_pament_claimed_by_division($id, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('division_id', $id)->whereNotIn('appeal_status', ['REJECTED', 'DRAFT']);
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        return $query->sum('loan_amount');
        // return $query;
    }

    public function statistics_pament_claimed_by_upazila($id, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('upazila_id', $id)->whereNotIn('appeal_status', ['REJECTED']);
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        return $query->sum('loan_amount');
        // return $query;
    }

    public function statistics_payment_received_by_division($id, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('division_id', $id)->whereNotIn('appeal_status', ['REJECTED'])->select('id');
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        $appealId = $query->get();

        $sum = 0;
        foreach ($appealId as $value) {
            $query = DB::table('gcc_payment_lists')->where('appeal_id', $value->id)->sum('paid_loan_amount');
            $sum = $sum + $query;
        }

        return $sum;
    }

    public function statistics_payment_received_by_upazila($id, $data = null)
    {

        $query = DB::table('gcc_appeals')->where('upazila_id', $id)->whereNotIn('appeal_status', ['REJECTED'])->select('id');
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        $appealId = $query->get();

        $sum = 0;
        foreach ($appealId as $value) {
            $query = DB::table('gcc_payment_lists')->where('appeal_id', $value->id)->sum('paid_loan_amount');
            $sum = $sum + $query;
        }

        return $sum;
    }

    public function statistics_pament_claimed_by_district($id, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('district_id', $id)->whereNotIn('appeal_status', ['REJECTED']);
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        return $query->sum('loan_amount');
        // return $query;
    }

    public function statistics_payment_received_by_district($id, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('district_id', $id)->whereNotIn('appeal_status', ['REJECTED'])->select('id');
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            // dd($dateFrom);
            $query->whereBetween('gcc_appeals.case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        $appealId = $query->get();

        $sum = 0;
        foreach ($appealId as $value) {
            $query = DB::table('gcc_payment_lists')->where('appeal_id', $value->id)->sum('paid_loan_amount');
            $sum = $sum + $query;
        }

        return $sum;
    }

    public function statistics_case_status($status, $data = null)
    {
        $query = DB::table('gcc_appeals')->where('appeal_status', $status);


        // if ($data['role_id'] == 34) {
        //     $data['division'] = user_office_info()->division_id;
        // }

        if ($data['division']) {
            $query->where('division_id', '=', $data['division']);
        }
        if ($data['district']) {
            $query->where('district_id', '=', $data['district']);
        }
        if ($data['upazila']) {
            $query->where('upazila_id', '=', $data['upazila']);
        }
        if ($data['dateFrom'] != null && $data['dateTo'] != null) {
            $query->whereBetween('case_date', [$data['dateFrom'], $data['dateTo']]);
        }

        return $query->count();
        // return $query;
    }

    //for parent office dashboard
    public function data_for_parent_office($status, $data = null)
    {

        $sub_office_ids = [];
        $datas['total_data'] = [];
        $datas['total_count'] = 0;

        // Fetch the sub office IDs where parent equals $data
        $sub_office_ids = DB::table('office')
            ->where('parent', $data)
            ->pluck('id')
            ->toArray();

        // Fetch the data from gcc_appeals where office_id is in the sub_office_ids
        $query = DB::table('gcc_appeals')
            ->whereIn('office_id', $sub_office_ids)
            ->whereIn('appeal_status', $status)
            ->get();

        // Assign the data to the $data array
        $datas['total_data'] = $query;

        // Optionally, you can also get the total count
        $datas['total_count'] = count($query);

        return $datas;
    }

    public function hearing_date_today()
    {
        $data['hearing'] = DB::table('case_hearing')
            ->join('case_register', 'case_hearing.case_id', '=', 'case_register.id')
            ->join('court', 'case_register.court_id', '=', 'court.id')
            ->join('upazila', 'case_register.upazila_id', '=', 'upazila.id')
            ->join('mouja', 'case_register.mouja_id', '=', 'mouja.id')
            ->select('case_hearing.*', 'case_register.id', 'case_register.court_id', 'case_register.case_number', 'case_register.status', 'court.court_name')
            ->where('case_hearing.hearing_date', '=', date('Y-m-d'))
            ->get();

        // dd($data['hearing']);

        $data['page_title'] = 'আজকের দিনে শুনানী/মামলার তারিখ';
        return view('dashboard.hearing_date')->with($data);
    }

    public function hearing_date_tomorrow()
    {
        $d = date('Y-m-d', strtotime('+1 day'));
        $data['hearing'] = DB::table('case_hearing')
            ->join('case_register', 'case_hearing.case_id', '=', 'case_register.id')
            ->join('court', 'case_register.court_id', '=', 'court.id')
            ->join('upazila', 'case_register.upazila_id', '=', 'upazila.id')
            ->join('mouja', 'case_register.mouja_id', '=', 'mouja.id')
            ->select('case_hearing.*', 'case_register.id', 'case_register.court_id', 'case_register.case_number', 'case_register.status', 'court.court_name')
            ->where('case_hearing.hearing_date', '=', $d)
            ->get();

        // dd($data['hearing']);
        $data['page_title'] = 'আগামী দিনে শুনানী/মামলার তারিখ';
        return view('dashboard.hearing_date')->with($data);
    }

    public function hearing_date_nextWeek()
    {

        $d = date('Y-m-d', strtotime('+7 day'));
        $data['hearing'] = DB::table('case_hearing')
            ->join('case_register', 'case_hearing.case_id', '=', 'case_register.id')
            ->join('court', 'case_register.court_id', '=', 'court.id')
            ->join('upazila', 'case_register.upazila_id', '=', 'upazila.id')
            ->join('mouja', 'case_register.mouja_id', '=', 'mouja.id')
            ->select('case_hearing.*', 'case_register.id', 'case_register.court_id', 'case_register.case_number', 'case_register.status', 'court.court_name')
            ->where('case_hearing.hearing_date', '>=', date('Y-m-d'))
            ->where('case_hearing.hearing_date', '<=', $d)
            ->get();
        // dd($data['hearing']);

        $data['page_title'] = 'আগামী সপ্তাহের শুনানী/মামলার তারিখ';
        return view('dashboard.hearing_date')->with($data);
    }

    public function hearing_date_nextMonth()
    {
        $d = date('Y-m-d', strtotime('+1 month'));
        /* $m = date('m',strtotime($d));
        dd($d);*/
        $data['hearing'] = DB::table('case_hearing')
            ->join('case_register', 'case_hearing.case_id', '=', 'case_register.id')
            ->join('court', 'case_register.court_id', '=', 'court.id')
            ->join('upazila', 'case_register.upazila_id', '=', 'upazila.id')
            ->join('mouja', 'case_register.mouja_id', '=', 'mouja.id')
            ->select('case_hearing.*', 'case_register.id', 'case_register.court_id', 'case_register.case_number', 'case_register.status', 'court.court_name')
            ->where('case_hearing.hearing_date', '>=', date('Y-m-d'))
            ->where('case_hearing.hearing_date', '<=', $d)
            ->get();

        // dd($data['hearing']);

        $data['page_title'] = 'আগামী মাসের শুনানী/মামলার তারিখ';
        return view('dashboard.hearing_date')->with($data);
    }

    public function hearing_case_details($id)
    {

        $data['info'] = DB::table('case_register')
            ->join('court', 'case_register.court_id', '=', 'court.id')
            ->join('upazila', 'case_register.upazila_id', '=', 'upazila.id')
            ->join('mouja', 'case_register.mouja_id', '=', 'mouja.id')
            // ->join('case_type', 'case_register.ct_id', '=', 'case_type.id')
            ->join('case_status', 'case_register.cs_id', '=', 'case_status.id')
            // ->join('case_badi', 'case_register.id', '=', 'case_badi.case_id')
            // ->join('case_bibadi', 'case_register.id', '=', 'case_bibadi.case_id')
            ->select('case_register.*', 'court.court_name', 'upazila.upazila_name_bn', 'mouja.mouja_name_bn', 'case_status.status_name')
            ->where('case_register.id', '=', $id)
            ->first();
        // dd($data['info']);
        // dd($data['info']);

        $data['badis'] = DB::table('case_badi')
            ->join('case_register', 'case_badi.case_id', '=', 'case_register.id')
            ->select('case_badi.*')
            ->where('case_badi.case_id', '=', $id)
            ->get();

        $data['bibadis'] = DB::table('case_bibadi')
            ->join('case_register', 'case_bibadi.case_id', '=', 'case_register.id')
            ->select('case_bibadi.*')
            ->where('case_bibadi.case_id', '=', $id)
            ->get();

        $data['surveys'] = DB::table('case_survey')
            ->join('case_register', 'case_survey.case_id', '=', 'case_register.id')
            ->join('survey_type', 'case_survey.st_id', '=', 'survey_type.id')
            ->join('land_type', 'case_survey.lt_id', '=', 'land_type.id')
            ->select('case_survey.*', 'survey_type.st_name', 'land_type.lt_name')
            ->where('case_survey.case_id', '=', $id)
            ->get();

        // Get SF Details
        $data['sf'] = DB::table('case_sf')
            ->select('case_sf.*')
            ->where('case_sf.case_id', '=', $id)
            ->first();
        // dd($data['sf']);

        // Get SF Details
        $data['logs'] = DB::table('case_log')
            ->select('case_log.comment', 'case_log.created_at', 'case_status.status_name', 'role.role_name', 'users.name')
            ->join('case_status', 'case_status.id', '=', 'case_log.status_id')
            ->leftJoin('role', 'case_log.send_user_group_id', '=', 'role.id')
            ->join('users', 'case_log.user_id', '=', 'users.id')
            ->where('case_log.case_id', '=', $id)
            ->orderBy('case_log.id', 'desc')
            ->get();
        // dd($data['sf']);

        // Get SF Details
        $data['hearings'] = DB::table('case_hearing')
            ->select('case_hearing.*')
            ->where('case_hearing.case_id', '=', $id)
            ->orderBy('case_hearing.id', 'desc')
            ->get();

        // Dropdown
        $data['roles'] = DB::table('role')
            ->select('id', 'role_name')
            ->where('in_action', '=', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        // dd($data['bibadis']);

        $data['page_title'] = 'শুনানী মামলার বিস্তারিত তথ্য';
        return view('dashboard.hearing_case_details')->with($data);
    }



    public function get_mouja_by_ulo_office_id($officeID)
    {
        return DB::table('mouja_ulo')->where('ulo_office_id', $officeID)->pluck('mouja_id');
        // return DB::table('mouja_ulo')->select('mouja_id')->where('ulo_office_id', $officeID)->get();
        // return DB::table('division')->select('id', 'division_name_bn')->get();
    }

    public function total_case_count_applicant()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
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
    public function total_running_case_count_applicant()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [1, 2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_pending_case_count_applicant()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [1, 2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['SEND_TO_DC', 'SEND_TO_DIV_COM', 'SEND_TO_LAB_CM', 'SEND_TO_GCO', 'SEND_TO_ASST_GCO'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();


        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_completed_case_count_applicant()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [1, 2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['CLOSED'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        // $appeal_id_array=[];
        // $count=0;
        // foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
        //     array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
        //     $count++;
        // }

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_case_count_defaulter()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2, 5])
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
    public function total_running_case_count_defaulter()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['ON_TRIAL', 'ON_TRIAL_DC', 'ON_TRIAL_LAB_CM', 'ON_TRIAL_DIV_COM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        return ['total_count' => count($appeal_ids_from_db), 'appeal_id_array' => ''];
    }
    public function total_pending_case_count_defaulter()
    {

        $appeal_ids_from_db = DB::table('gcc_appeal_citizens')
            ->join('gcc_citizens', 'gcc_citizens.id', '=', 'gcc_appeal_citizens.citizen_id')
            ->join('gcc_appeals', 'gcc_appeal_citizens.appeal_id', 'gcc_appeals.id')
            ->where('gcc_citizens.citizen_NID', '=', globalUserInfo()->citizen_nid)
            ->whereIn('gcc_appeal_citizens.citizen_type_id', [2, 5])
            ->whereIn('gcc_appeals.appeal_status', ['SEND_TO_DC', 'SEND_TO_DIV_COM', 'SEND_TO_LAB_CM'])
            ->select('gcc_appeal_citizens.appeal_id')
            ->get();

        // $appeal_id_array=[];
        // $count=0;
        // foreach ($appeal_ids_from_db as $appeal_ids_from_db_single) {
        //     array_push($appeal_id_array, $appeal_ids_from_db_single->appeal_id);
        //     $count++;
        // }

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
}