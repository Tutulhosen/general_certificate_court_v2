<?php

namespace App\Http\Controllers;

use App\Models\GccAppeal;
use App\Models\User;
use App\Repositories\AppealRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetDataController extends Controller
{
    //org rep 
    public function appeal_case_details(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        $office_id = $userInfo->office_id;
        $roleID = $userInfo->role_id;
        $officeInfo = $allInfo->officeInfo;

        $appeal = GccAppeal::findOrFail($id);
        $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
        $data['appeal']  = $appeal;
        $data["notes"] = $appeal->appealNotes;
        $data["districtId"] = $officeInfo->district_id;
        $data["divisionId"] = $officeInfo->division_id;
        $data["office_id"] = $office_id;


        $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';
        return ['success' => true,  "data" => $data];
        // return $data;
    }
    public function appeal_case_tracking(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        $office_id = $userInfo->office_id;
        $roleID = $userInfo->role_id;
        $officeInfo = $allInfo->officeInfo;

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
        return ['success' => true,  "data" => $data];
    }

    // gcc citizen org
    public function gcc_citizen_case_details(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        // $office_id = $userInfo->office_id;
        // $roleID = $userInfo->role_id;
        // $officeInfo = $allInfo->officeInfo;

        $appeal = GccAppeal::findOrFail($id);
        $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
        // return $data;
        $data['appeal']  = $appeal;
        $data["notes"] = $appeal->appealNotes;
   


        $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';
        return ['success' => true,  "data" => $data];
        // return $data;
    }
    public function gcc_citizen_tracking(Request $request)
    {

        $requestData = $request->all();
        $allInfo = json_decode($requestData['body_data']);
        // return ['mkessage' => $allInfo];
        $id = $allInfo->id;
        $userInfo = $allInfo->userInfo;
        

        $appeal = GccAppeal::findOrFail($id);
        $data = AppealRepository::getAllAppealInfoApi($id, $userInfo);
        $data['appeal']  = $appeal;
        $data["notes"] = $appeal->appealNotes;


        $data['page_title'] = 'মামলা ট্র্যাকিং';
        $data['shortOrderTemplateList'] = DB::table('gcc_notes_modified')
            ->where('gcc_notes_modified.appeal_id', $id)
            ->join('gcc_case_shortdecisions', 'gcc_notes_modified.case_short_decision_id', '=', 'gcc_case_shortdecisions.id')
            ->select('gcc_case_shortdecisions.case_short_decision', 'gcc_notes_modified.*')
            ->get();
        return ['success' => true,  "data" => $data];
    }
}