<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationManagementApiController extends Controller
{
    public function post_organization_change_by_applicant(Request $request){


        $requestInfo= $request->all();
        $user = $requestInfo['body_data'];
        $office_id= $user['office_id'];
        // return ['every ok'=> $user['office_id']];
        DB::table('gcc_appeal_citizens')->where('citizen_type_id', 1)->where('citizen_id', $user['citizen_id'])->delete();
    
        $all_cases = DB::table('gcc_appeals')->where('office_id', $office_id)->whereNotIn('appeal_status', ['REJECTED'])->select('gcc_appeals.id as appeal_id')->get();
    
        if (count($all_cases) > 0) {
            foreach ($all_cases as $key => $value) {
    
                $data = [
                    'appeal_id' => $value->appeal_id,
                    'citizen_id' => $user['citizen_id'],
                    'citizen_type_id' => 1,
                    'created_by' => $user['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                DB::table('gcc_appeal_citizens')->insert($data);
            }
        }
        return ['success' => true];
    }
}
