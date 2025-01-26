<?php

namespace App\Http\Controllers;

use App\Models\GccAppeal;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use App\Repositories\AppealRepository;
use App\Repositories\ArchiveRepository;
use App\Models\GccCaseShortdecisionTemplates;
use App\Repositories\CitizenAttendanceRepository;
use App\Services\ShortOrderTemplateServiceUpdated;
use App\Repositories\CertificateAsstNoteRepository;
use App\Http\Controllers\Api\BaseController as BaseController;

class AppealListApiController extends BaseController
{
    public function closed_list(Request $request)
    {
        // return 'come from gcc'; 
        $results = GccAppeal::orderby('id', 'desc')->where('appeal_status', 'CLOSED')->get();
        foreach ($results as $key => $result) {
            $applicant_name = DB::table('gcc_appeal_citizens')
                ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $result->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_citizens.citizen_name')
                ->first()->citizen_name;
            $court_name = DB::table('court')
                ->where('id', $result->court_id)
                ->first()->court_name;
            $result['applicant_name'] = $applicant_name;
            $result['court_name'] = $court_name;
        }

        return ['message' => 'success', "data" => $results];
        // return $this->sendResponse($results, null);
    }

    public function closed_list_search(Request $request)
    {
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $date_start = $datas['date_start'];
        $date_end = $datas['date_end'];
        $case_no = $datas['case_no'];

        $results = GccAppeal::orderby('id', 'desc')->where('appeal_status', 'CLOSED');
      

        if (!empty($date_start) && !empty($date_end)) {
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $date_start)));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $date_end)));
            $results = $results->whereBetween('case_date', [$dateFrom, $dateTo]);
        }
        if (!empty($case_no)) {
            $results = $results->where('case_no', 'LIKE', '%'.$case_no.'%');
        }
        $result_get= $results->get();
        foreach ($result_get as $key => $result) {
            $applicant_name = DB::table('gcc_appeal_citizens')
                ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                ->where('gcc_appeal_citizens.appeal_id', $result->id)
                ->where('gcc_appeal_citizens.citizen_type_id', 1)
                ->select('gcc_citizens.citizen_name')
                ->first()->citizen_name;
            $court_name = DB::table('court')
                ->where('id', $result->court_id)
                ->first()->court_name;
            $result['applicant_name'] = $applicant_name;
            $result['court_name'] = $court_name;
        }
   
        return $this->sendResponse($result_get, null);
        
        
    }

    public function closed_list_details(Request $request){
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $case_id = $datas['case_id'];
        $auth_user_info = $datas['auth_user_info'];
     
        $appeal = GccAppeal::findOrFail($case_id);
        $data = AppealRepository::getAllAppealInfo_new($case_id, $auth_user_info);
        $data['appeal']  = $appeal;
        $data["notes"] = $appeal->appealNotes;

        $data["runningcommandList"] = DB::table("gcc_running_appeal_attachments")->where('attatch_type',1)->where("appeal_id",$case_id)->select("*")->first();
        $data["runningorderTemplate"] = DB::table("gcc_running_appeal_attachments")->where('attatch_type',2)->where("appeal_id",$case_id)->select("*")->get();
        $data["attendance_list"] = DB::table("gcc_running_appeal_attachments")->where('attatch_type',3)->where("appeal_id",$case_id)->select("*")->first();
        $data['page_title'] = 'সার্টিফিকেট রিকুইজিশান এর  বিস্তারিত তথ্য';

        return $this->sendResponse($data, null);

    }

    public function closed_list_nothi(Request $request){
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $case_id = $datas['case_id'];
        
        $data['caseNumber'] = GccAppeal::find($case_id)->case_no;
        $data['caseInfo'] = AppealRepository::getAppealCaseAndCriminalId($case_id);
        $data['nothiData'] = AppealRepository::getNothiListFromAppeal($case_id);
        $data['citizenAttendanceList'] = CitizenAttendanceRepository::getCitizenAttendanceByAppealId($case_id);
        $data['shortOrderTemplateList']=ShortOrderTemplateServiceUpdated::getShortOrderTemplateListByAppealId($case_id);
        //dd($shortOrderTemplateList);
        $data['paymentAttachment']=PaymentService::getPaymentAttachmentByAppealId($case_id);
        $data['page_title']  = 'বিস্তারিত নথি | '. $data['caseNumber'];

        return $this->sendResponse($data, null);

    }

    public function old_closed_list(Request $request)
    {
        $results = DB::table('archive_case')->orderby('id', 'desc')->get();
        foreach ($results as $key => $result) {
            $court_name = DB::table('court')
                ->where('id', $result->court_id)
                ->first()->court_name;
            $result->court_name = $court_name;
        } 

        return ['message' => 'success', "data" => $results];
    }

    public function old_closed_list_search(Request $request)
    {
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $date_start = $datas['date_start'];
        $date_end = $datas['date_end'];
        $case_no = $datas['case_no'];

        $results = DB::table('archive_case')->orderby('id', 'desc');
      

        if (!empty($date_start) && !empty($date_end)) {
            $dateFrom = date('Y-m-d', strtotime(str_replace('/', '-', $date_start)));
            $dateTo = date('Y-m-d', strtotime(str_replace('/', '-', $date_end)));
            $results = $results->whereBetween('appeal_date', [$dateFrom, $dateTo]);
        }
        if (!empty($case_no)) {
            $results = $results->where('case_no', 'LIKE', '%'.$case_no.'%');
        }
        $result_get= $results->get();
        foreach ($result_get as $key => $result) {
            $court_name = DB::table('court')
                ->where('id', $result->court_id)
                ->first()->court_name;
            $result->court_name = $court_name;
        } 
   
        return $this->sendResponse($result_get, null);
        
        
    }

    public function showAppealViewPage(Request $request, $id = '')
    {
         
        
        $results['details'] = ArchiveRepository::old_dismiss_case_details($id);

        // $results['crpc_name']=DB::table('crpc_sections')->where('id',$results['details']->related_act)->first()->crpc_name;
   
        $results['url']=url('/');
        $page_title = 'মামলার বিস্তারিত তথ্য ';

     
        $results['all_dis_div_upa']= DB::table('archive_case as A')->where('A.id', $results['details']->id)
                    ->join('division as B', 'A.div_id', 'B.id')
                    ->join('district as C', 'A.dis_id', 'C.id')
                    ->join('upazila as D', 'A.upa_id', 'D.id')
                    ->select('B.division_name_bn as div_name','C.district_name_bn as dis_name','D.upazila_name_bn as upa_name')
                    ->first();

        return ['message' => 'success', 'page_title' =>$page_title, "data" =>$results];
    }


    public function generate_pdf($id){
       

        
        $case_details = ArchiveRepository::old_dismiss_case_details($id);
        if ($case_details->id) {
            $results['attachmentList']=ArchiveRepository::old_dismiss_case_attach_file($case_details->id);
        }
        $results['url']=url('/');
      
        return ['message' => 'success',"data" =>$results];



        // $data['attachmentList']=$attachmentList;
        // $data['page_title'] = 'পুরাতন নিষ্পত্তিকৃত মামলার বিবরণ';
        // $data['case_details']=$case_details;
        // return view('archive.generate_pdf')->with($data);
    }

    public function short_order(Request $request){
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $id = $datas['id'];
        
        $data_to_qr_codded = url()->full();
        $imageName = 'QR_' . encrypt($id);

        // $content = file_get_contents('https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $data_to_qr_codded . '');
        // file_put_contents(public_path() . '/QRCodes/' . $imageName, $content);
        //dd($content);
        //file_put_contents(public_path() . $upload_path . $imageName, $content);

        $appealId = $id;

        // $data['data_image_path'] = '/QRCodes/' . $imageName;
        $data['data_image_path'] = $data_to_qr_codded;

        $data['appealOrderLists'] = CertificateAsstNoteRepository::generate_order_shit($appealId);
        $data['nothi_id'] = $id;
        $data['page_title'] = 'আদেশ নামা';
        // return $data;
        return $this->sendResponse($data, null);

    }

    public function short_order_tmp(Request $request){
        // return 'come from gcc'; 
        $data_get = $request->getContent();

        $json_data = json_decode($data_get, true);

        $datas= $json_data['body_data'];

        $id = $datas['id'];
        
        $data_to_qr_codded = url()->full();
        $imageName = 'QR_short_decision_template' . $id;

        $data['data_image_path'] = $data_to_qr_codded;

        $data['appealShortOrderLists'] = GccCaseShortdecisionTemplates::where('id', $id)->get();

        $data['page_title'] = 'সংক্ষিপ্ত আদেশ';
        $data['nothi_id'] = $id;
        // return $data;
        return $this->sendResponse($data, null);

    }
}