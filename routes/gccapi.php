<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CauseListController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GccApi\LoginController;
use App\Http\Controllers\AppealListApiController;
use App\Http\Controllers\ShortDecisionController;
use App\Http\Controllers\GccApi\GetDataController;
use App\Http\Controllers\GccRegisterApiController;
use App\Http\Controllers\LogManagementApiController;
use App\Http\Controllers\RolePermissionApiController;
use App\Http\Controllers\MobileApps\GetDataAppsController;
use App\Http\Controllers\OrganizationManagementApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//----------------------for web route

Route::post('get-log-in', [LoginController::class, 'get_log_in']);
Route::post('/store-requisition', [GetDataController::class, 'storeRequisition']);
Route::post('/count-org-rep-dashboard-data', [GetDataController::class, 'count_org_dashboard_data']);
Route::post('/count-gcc-citizen-dashboard-data', [GetDataController::class, 'count_gcc_citizen_dashboard_data']);
Route::post('/case/for/appeal', [GetDataController::class, 'case_for_appeal']);
//citizen certificate copy request 
Route::post('gcc/citizen/certificate/copy', [GetDataController::class, 'gcc_citizen_certificate_copy']);


Route::post('/gcc/v2/store-role-permission', [RolePermissionApiController::class, 'store']);

//gcc report module
Route::post('/gcc/report/generate', [ReportController::class, 'pdf_generate_new']);
Route::POST('/gcc/cause_list', [CauseListController::class, 'index'])->name('cause_lists.index');

//gcc dashboard statistics data 
Route::post('/dashboard/statistics/data', [DashboardController::class, 'ajaxCaseStatus_new']);
Route::post('/dashboard/payment/statistics/data', [DashboardController::class, 'ajaxPaymentReport_new']);
Route::post('/dashboard/pie/chart/data', [DashboardController::class, 'ajaxPieChart_new']);
Route::post('/dashboard/heigh/chart/data', [DashboardController::class, 'get_drildown_case_count_new']);
Route::post('/paymentStatusUpdate', [GetDataController::class, 'paymentStatusUpdate']);

//for parent office dashboard
Route::post('/dashboard/statistics/data/for/parent/office', [DashboardController::class, 'dashboard_data_for_parent_office']);
Route::post('/parent/office/traking', [DashboardController::class, 'parent_office_appeal_traking']);
Route::post('/parent/office/appeal/details', [DashboardController::class, 'parent_office_appeal_details']);


Route::post('/short-decision/store', [ShortDecisionController::class, 'store']);
Route::post('/short-decision/update/{id}', [ShortDecisionController::class, 'update']);

//Archive api 
Route::post('/gcc/appeal/closed-list', [AppealListApiController::class, 'closed_list']);
Route::post('/gcc/appeal/closed-list/search', [AppealListApiController::class, 'closed_list_search']);
Route::post('/gcc/appeal/closed-list/details', [AppealListApiController::class, 'closed_list_details']);
Route::post('/gcc/appeal/closed-list/nothi', [AppealListApiController::class, 'closed_list_nothi']);
Route::post('/gcc/appeal/old-closed-list', [AppealListApiController::class, 'old_closed_list']);
Route::post('/gcc/appeal/old-closed-list/search', [AppealListApiController::class, 'old_closed_list_search']);
Route::post('/gcc/appeal/old-closed-list/details/{id}', [AppealListApiController::class, 'showAppealViewPage']);
Route::post('/gcc/generate-pdf/{id}', [AppealListApiController::class, 'generate_pdf'])->name('gcc.generate.pdf');

Route::post('/gcc/appeal/short/order', [AppealListApiController::class, 'short_order']);
Route::post('/gcc/appeal/short/order/tmp', [AppealListApiController::class, 'short_order_tmp']);



//log cases api

Route::post('/gcc/log_index', [LogManagementApiController::class, 'index']);
Route::post('/gcc/log_index_single/{id}', [LogManagementApiController::class, 'log_index_single']);
Route::post('/gcc/log/logid/{id}', [LogManagementApiController::class, 'log_details_single_by_id']);
Route::post('/gcc/create_log_pdf/{id}', [LogManagementApiController::class, 'create_log_pdf']);



//Gcc Register 
Route::post('/gcc/register/list', [GccRegisterApiController::class, 'index']);
Route::post('/gcc/printPdf', [GccRegisterApiController::class, 'index']);


//pp change role
Route::post('/gcc/post/organization/change/applicant', [OrganizationManagementApiController::class, 'post_organization_change_by_applicant']);

//case count for gcc
Route::post('/case/count/for/gcc', [GetDataController::class, 'case_count_for_gcc']);




//---------------------------for mobile apps route
Route::post('/count-gcc-citizen-dashboard-data-app', [GetDataAppsController::class, 'count_gcc_citizen_dashboard_data']);
Route::post('/count-org-rep-dashboard-data-app', [GetDataAppsController::class, 'count_org_dashboard_data']);

Route::post('gcc/appeal/case/details/apps', [GetDataAppsController::class, 'gcc_appeal_case_details']);
Route::post('gcc/citizen/appeal/case/details/apps', [GetDataAppsController::class, 'gcc_citizen_appeal_case_details']);

Route::post('gcc/appeal/case/tracking/apps', [GetDataAppsController::class, 'gcc_appeal_case_tracking']);

