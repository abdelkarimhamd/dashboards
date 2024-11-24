<?php

use App\Http\Controllers\ActualHrValueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\OperationValueController;
use App\Http\Controllers\ActualValueController;
use App\Http\Controllers\CashinValueController;
use App\Http\Controllers\EpectedSalaryController;
use App\Http\Controllers\ExpectedCollectionController;
use App\Http\Controllers\ExpectedPettyCashController;
use App\Http\Controllers\ExpectedSuppliersController;
use App\Http\Controllers\FitOutControllers\FitOutActualPercentageController;
use App\Http\Controllers\FitOutControllers\FitOutActualValueController;
use App\Http\Controllers\FitOutControllers\FitOutCashinValueController;
use App\Http\Controllers\FitOutControllers\FitOutCashoutValueController;
use App\Http\Controllers\FitOutControllers\FitOutPlanController;
use App\Http\Controllers\FitOutControllers\FitOutPlanPercentageController;
use App\Http\Controllers\FitOutControllers\FitOutProjectController;
use App\Http\Controllers\FitOutControllers\FitOutUploadImagesController;
use App\Http\Controllers\KeyIssuesNotesController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PettyCashValueController;
use App\Http\Controllers\PLDashboardControllers\GetCummulativeDataController;
use App\Http\Controllers\PLDashboardControllers\GetCummulativeProjectDataController;
use App\Http\Controllers\PLDashboardControllers\GetProjectMonthlyData;
use App\Http\Controllers\PLDashboardControllers\InsertCummulativeDataController;
use App\Http\Controllers\PLDashboardControllers\InsertProjectDataController;
use App\Http\Controllers\PLDashboardControllers\InsertProjectDetailsController;
use App\Http\Controllers\PLDashboardControllers\InsertTotalDataController;
use App\Http\Controllers\PLDashboardControllers\InsertYearNotes;
use App\Http\Controllers\SalaryValueController;
use App\Http\Controllers\StaffRegistartionValue;
use App\Http\Controllers\SuppliersValueController;
use App\Http\Controllers\PLDashboardControllers\PLUsersController;
use App\Http\Controllers\PmNotesController;
use App\Http\Controllers\TenderingControllers\AssignedTendersController;
use App\Http\Controllers\TenderingControllers\TenderingInsertController;
use App\Http\Controllers\TenderingControllers\TenderingUserController;
use App\Models\Header;
use App\Http\Controllers\TenderingControllers\EmailController;
use App\Http\Controllers\TenderingControllers\FactSheetController;
use App\Http\Controllers\TenderingControllers\TenderingDecisionController;
use App\Http\Controllers\TenderingControllers\TenderNotesController;
use App\Http\Controllers\TenderingControllers\TRFController;






use App\Http\Controllers\HR\CVEmployeesController;
use App\Http\Controllers\HR\ExperienceController;
use App\Http\Controllers\HR\EducationController;
use App\Http\Controllers\HR\SkillsController;
use App\Http\Controllers\HR\CertificationController;
use App\Http\Controllers\HR\StatusHistoryController;
use App\Http\Controllers\HR\UserController as HRUserController;
use App\Http\Controllers\HR\JobDescriptionController;
use App\Http\Controllers\HR\ProjectController;
use App\Http\Controllers\HR\OrgChartController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\CvDocumentController;
use App\Http\Controllers\HR\DashboardController;
use App\Http\Controllers\HR\HRAuthController;
use App\Http\Controllers\HR\InterviewController;
use App\Http\Controllers\HR\PositionController;
use App\Http\Controllers\OperationProjectImagesController;
use App\Http\Controllers\OperationTotalSummurizedController;
use App\Http\Controllers\PmFcInvoiceNextMonthController;



use App\Http\Controllers\CRM\ActivityController;
use App\Http\Controllers\CRM\CompanyController;
use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\CRM\DashboardController as CRMDashboardController;
use App\Http\Controllers\CRM\DealController;
use App\Http\Controllers\CRM\DealLeadController;
use App\Http\Controllers\CRM\DealStageController;
use App\Http\Controllers\CRM\EmailActivityController;
use App\Http\Controllers\CRM\LeadController;
use App\Http\Controllers\CRM\OutlookController;
use App\Http\Controllers\CRM\TaskController;
use App\Http\Controllers\CRM\UserController as CRMUserController;
use App\Http\Controllers\PettyCashControllers\PettyCashInsertData;
use App\Http\Controllers\ProcurementControllers\ProcurementDailyTransfersController;
use App\Http\Controllers\ProcurementControllers\ProcurementNotesController;
use App\Http\Controllers\ProcurementControllers\ProcurementPaymentsController;
use App\Http\Controllers\ProcurementControllers\ProcurementProjectsController;
use App\Http\Controllers\ProcurementControllers\ProcurementSuppliersController;
use App\Http\Controllers\ProcurementControllers\ProcurementUsersController;
use App\Http\Controllers\TenderingControllers\TenderTotalSummurizedController;



use App\Http\Controllers\ServiceProvider\PurchaseOrderController;
use App\Http\Controllers\ServiceProvider\ServiceProviderController;
use App\Http\Controllers\ServiceProvider\VariationOrderController;

//operation dashboard routes
Route::post('/login', [UserController::class, 'login'])->name('login');

Route::get('/register/check', [UserController::class, 'getregister']);


Route::post('/register', [UserController::class, 'register']);

Route::post('/operation/reset-password', [UserController::class, 'resetPassword']);
Route::post('/operation/forget-password', [UserController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/operation_dashboard/user_logout', [UserController::class, 'logout']);
    Route::get('/project-data/operation/{id}/{year}', [HeaderController::class, 'getDataByProjectId']);
    Route::post('/header', [HeaderController::class, 'addHeader']);
    Route::get('/list', [HeaderController::class, 'list']);
    Route::get('/list_all', [HeaderController::class, 'listAll']);
    Route::get('/operation_KSA_projects', [HeaderController::class, 'getKSAProjectYears']);
    Route::get('/operation_UAE_projects', [HeaderController::class, 'getUAEProjectYears']);
    Route::get('/operation_KSA_Fo_projects', [HeaderController::class, 'getKSAFoProjectYears']);
    Route::get('/operation_UAE_Fo_projects', [HeaderController::class, 'getUAEFoProjectYears']);
    Route::get('/operationprojects/managed-by/{username}', [HeaderController::class, 'getManagedProjects']);
    Route::get('/project/{projectName}', [HeaderController::class, 'getProjectByName']);
    Route::get('/operationDshboard/projectType', [HeaderController::class, 'getProjectsBasedOnRoleAndDepartment']);
    Route::get('/operation-project-managers', [UserController::class, 'getProjectManagerNames']);
    Route::post('/insertMonthlyBudget', [HeaderController::class, 'insertMonthlyBudget']);
    Route::get('/getCurrentMonthBudgetValue/{projectId}', [HeaderController::class, 'getCurrentMonthBudgetValue']);
    Route::get('/getCurrentMonthManpowerValue/{projectId}', [HeaderController::class, 'getCurrentMonthManpowerValue']);
    Route::get('/project/{projectId}/cumulative-budget', [HeaderController::class, 'calculateCumulativeBudget']);
    Route::post('/editBudgetMonthlyValue/{projectId}', [HeaderController::class, 'storeBudgetMonthlyValue']);

    Route::post('/intializingActualValue', [HeaderController::class, ' intializingActualValue']);
    Route::post('/storeActualMonthlyValue/{projectId}', [ActualValueController::class, 'storeActualMonthlyValue']);
    Route::get('/calculateCumulativeValueBeforeCurrentMonth/{projectId}', [ActualValueController::class, 'calculateCumulativeValueBeforeCurrentMonth']);
    Route::get('/getCurrentMonthActualValue/{projectId}', [ActualValueController::class, 'getCurrentMonthActualValue']);

    Route::post('/intializingActualHrValue', [HeaderController::class, ' intializingActualHrValue']);
    Route::post('/storeActualMonthlyHrValue/{projectId}', [ActualHrValueController::class, 'storeActualHrMonthlyValue']);
    Route::get('/calculateCumulativeActualHrValueBeforeCurrentMonth/{projectId}', [ActualHrValueController::class, 'calculateCumulativeActualHrValueBeforeCurrentMonth']);
    Route::get('/getCurrentMonthActualHrValue/{projectId}', [ActualHrValueController::class, 'getCurrentMonthActualHrValue']);


    Route::post('/initializingPettyCashValue', [HeaderController::class, ' initializingPettyCashValue']);
    Route::post('/storePettyCashMonthlyValue/{projectId}', [PettyCashValueController::class, 'storePettyCashMonthlyValue']);
    Route::get('/calculatePettyCashCumulativeValue/{projectId}', [PettyCashValueController::class, 'calculatePettyCashCumulativeValue']);
    Route::get('/getCurrentMonthPettyValue/{projectId}', [PettyCashValueController::class, 'getCurrentMonthPettyValue']);

    Route::post('/initializingSalaryValue', [HeaderController::class, ' initializingSalaryValue']);
    Route::post('/storeSalaryMonthlyValue/{projectId}', [SalaryValueController::class, 'storeSalaryMonthlyValue']);
    Route::get('/calculateCumulativeSalaryValue/{projectId}', [SalaryValueController::class, 'calculateCumulativeSalaryValue']);
    Route::get('/getCurrentMonthSalaryValue/{projectId}', [SalaryValueController::class, 'getCurrentMonthSalaryValue']);

    Route::post('/initializingSuppliersValue', [HeaderController::class, ' initializingSuppliersValue']);
    Route::post('/storeSuppliersMonthlyValue/{projectId}', [SuppliersValueController::class, 'storeSuppliersMonthlyValue']);
    Route::get('/calculateCumulativeSuppliersValue/{projectId}', [SuppliersValueController::class, 'calculateCumulativeSuppliersValue']);
    Route::get('/getCurrentMonthSuppliersValue/{projectId}', [SuppliersValueController::class, 'getCurrentMonthSuppliersValue']);

    Route::post('/ intializingOperationValue', [HeaderController::class, ' intializingOperationValue']);
    Route::post('/storeOperationValuesMonthlyValue/{projectId}', [OperationValueController::class, 'storeOperationValuesMonthlyValue']);
    Route::get('/getOperationValuesMonthlyValue/{projectId}', [OperationValueController::class, 'getOperationValuesMonthlyValue']);

    Route::post('/ initializingCashinValue', [HeaderController::class, ' initializingCashinValue']);
    Route::post('/storeCashinMonthlyValue/{projectId}', [CashinValueController::class, 'storeCashinMonthlyValue']);
    Route::get('/calculateCumulativeCashinValue/{projectId}', [CashinValueController::class, 'calculateCumulativeCashinValue']);
    Route::get('/getCurrentMonthCashinValue/{projectId}', [CashinValueController::class, 'getCurrentMonthCashinValue']);

    Route::post('/initializingStaffValue', [HeaderController::class, ' initializingSalaryValue']);
    Route::post('/storeStaffMonthlyValue/{projectId}', [StaffRegistartionValue::class, 'storeStaffMonthlyValue']);
    Route::get('/calculateCumulativeStaffValue/{projectId}', [StaffRegistartionValue::class, 'calculateCumulativeStaffValue']);
    Route::get('/getCurrentMonthStaffValue/{projectId}', [StaffRegistartionValue::class, 'getCurrentMonthStaffValue']);

    Route::post('/insertManpowerValue', [HeaderController::class, 'insertManpowerValue']);
    Route::get('/project/{projectId}/cumulative-manpower', [HeaderController::class, 'calculateCumulativeManpower']);
    Route::post('/editPlannedMonthlyValue/{projectId}', [HeaderController::class, 'storePlannedMonthlyValue']);

    Route::post('/notes/{projectId}', 'App\Http\Controllers\KeyIssuesNotesController@store');
    Route::put('/notes/{id}', 'App\Http\Controllers\KeyIssuesNotesController@update');
    Route::delete('/notes/{id}', 'App\Http\Controllers\KeyIssuesNotesController@destroy');
    Route::get('/notes/{projectId}', 'App\Http\Controllers\KeyIssuesNotesController@index');


    Route::post('/staffNotes/{projectId}', 'App\Http\Controllers\NoteController@store');
    Route::put('/staffNotes/{id}', 'App\Http\Controllers\NoteController@update');
    Route::delete('/staffNotes/{id}', 'App\Http\Controllers\NoteController@destroy');
    Route::get('/staffNotes/{projectId}', 'App\Http\Controllers\NoteController@index');

    Route::get('/pmNotes/{projectId}', [PmNotesController::class, 'show']);
    Route::post('/pmNotes/{projectId}', [PmNotesController::class, 'storeOrUpdate']);

    Route::post('/store-current-month-pminvoice/{projectId}', [PmFcInvoiceNextMonthController::class, 'storePmFcNextMonthMonthValue']);
    Route::get('/calculate-cumulative-pminvoice/{projectId}', [PmFcInvoiceNextMonthController::class, 'calculatePmFcNextMonthMonthValue']);
    Route::get('/get-current-month-pminvoice/{projectId}', [PmFcInvoiceNextMonthController::class, 'getPmFcNextMonthMonthValue']);

    Route::post('/store-expected_salary/{projectId}', action: [EpectedSalaryController::class, 'storeExpectedSalaryValue']);
    Route::get('/calculate-cumulative-expected_salary/{projectId}', [EpectedSalaryController::class, 'calculateCumulativeExpectedSalaryValue']);
    Route::get('/get-current-month-expected_salary/{projectId}', [EpectedSalaryController::class, 'getExpectedSalaryValue']);

    Route::post('/store-expected-suppliers/{projectId}', [ExpectedSuppliersController::class, 'storeExpectedSuppliersValue']);
    Route::get('/calculate-cumulative-expected-suppliers/{projectId}', [ExpectedSuppliersController::class, 'calculateCumulativeExpectedSuppliersValue']);
    Route::get('/get-current-month-expected-suppliers/{projectId}', [ExpectedSuppliersController::class, 'getExpectedSuppliersValue']);


    Route::post('/store-current-month-expected-petty-cash/{projectId}', [ExpectedPettyCashController::class, 'storeExpectedPettyCashValue']);
    Route::get('/calculate-cumulative-expected-petty-cash/{projectId}', [ExpectedPettyCashController::class, 'calculateCumulativeExpectedPettyCashValue']);
    Route::get('/get-current-month-expected-petty-cash/{projectId}', [ExpectedPettyCashController::class, 'getExpectedPettyCashValue']);

    Route::post('/store-current-month-expected-collection/{projectId}', [ExpectedCollectionController::class, 'storeExpectedCollectionValue']);
    Route::get('/calculate-cumulative-expected-collection/{projectId}', [ExpectedCollectionController::class, 'calculateCumulativeExpectedCollectioValue']);
    Route::get('/get-current-month-expected-collection/{projectId}', [ExpectedCollectionController::class, 'getExpectedCollectionValue']);

    Route::post('/operation/project/upload-images', [OperationProjectImagesController::class, 'uploadImages']);
    Route::get('/operation/project/{projectId}/images', [OperationProjectImagesController::class, 'getProjectImages']);
    Route::delete('/operation/project/images/{id}', [OperationProjectImagesController::class, 'deleteImage']);


    Route::get('/operation/monthly-summary', [OperationTotalSummurizedController::class, 'getMonthlyProjectSummary']);
    Route::get('/tenders-summary', [OperationTotalSummurizedController::class, 'countSubmittedTenders']);
});




//Procument routes
Route::get('/procurement/register/check', [ProcurementUsersController::class, 'getRegister']);
Route::post('/procurement/register', [ProcurementUsersController::class, 'register']);
Route::post('/procurement/login', [ProcurementUsersController::class, 'login']);
Route::post('/procurement/forgot-password', [ProcurementUsersController::class, 'forgotPassword']);
Route::post('/procurement/reset-password', [ProcurementUsersController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/procurement/logout', [ProcurementUsersController::class, 'logout']);
    Route::post('/procurements/store/payments', [ProcurementPaymentsController::class, 'store']);
    Route::post('/procurements/store/suppiers', [ProcurementSuppliersController::class, 'store']);
    Route::post('/procurements/store/daily_transfers', [ProcurementDailyTransfersController::class, 'store']);
    Route::post('/procurements/store/projects', [ProcurementProjectsController::class, 'store']);
    Route::get('/procurements/projects_name_type', [ProcurementProjectsController::class, 'getProjects']);
    Route::get('/procurements/suppliers-with-services', [ProcurementSuppliersController::class, 'getSuppliersWithServices']);
    Route::post('/procurements/get-suppliers-by-project', [ProcurementDailyTransfersController::class, 'getSuppliersByProject']);
    Route::get('/procurements/get-transfers-data', [ProcurementDailyTransfersController::class, 'getTransfersData']);
    Route::get('/procurements/get-paymentss-data', [ProcurementPaymentsController::class, 'getPaymentsData']);
    Route::get('/procurements/get-paymentss-data/{project_name}', [ProcurementPaymentsController::class, 'getInvoiceProjectData']);
    Route::post('/procurements/apply-transfer-amount', [ProcurementDailyTransfersController::class, 'applyTransferAmount']);
    Route::get('/procurement_notes/{month}', [ProcurementNotesController::class, 'fetchNotes']);
    Route::post('/procurement_notes/{month}', [ProcurementNotesController::class, 'addNote']);
    Route::put('/procurement_notes/{id}', [ProcurementNotesController::class, 'update']);
    Route::delete('/procurement_notes/{id}', [ProcurementNotesController::class, 'destroy']);
    Route::get('/suppliers/status-counts', [ProcurementSuppliersController::class, 'getSuppliersStatusCounts']);
    Route::get('/invoices/average-invoicing', [ProcurementPaymentsController::class, 'calculateAverageInvoicing']);
    Route::get('/invoices/total-amount', [ProcurementPaymentsController::class, 'getTotalInvoiceAmount']);
    Route::get('/transfers/total-current-month', [ProcurementDailyTransfersController::class, 'getCurrentMonthTotalTransferAmount']);
    Route::get('/invoices/forecasted-total', [ProcurementPaymentsController::class, 'getTotalForecastedInvoiceAmount']);
    Route::get('/procurement/projects/financial-data', [ProcurementPaymentsController::class, 'getProjectFinancials']);

     //petty cash routes

     Route::post('/pettycash/store/expenses', [PettyCashInsertData::class, 'storeExpenses']);
     Route::post('/pettycash/store/general_expenses', [PettyCashInsertData::class, 'storeGeneralExpenses']);
     Route::get('/pettycash/get-requests-count', [PettyCashInsertData::class, 'countUniqueRequests']);
     Route::get('/petty-cash/pending-requests', [PettyCashInsertData::class, 'getPendingRequests']);
     Route::get('/petty-cash/request-details', [PettyCashInsertData::class, 'getRequestDetailsById']);
     Route::POST('/petty-cash/update-request/{id}', [PettyCashInsertData::class, 'updateRequest']);
     Route::get('/petty-cash/get-project-data', [PettyCashInsertData::class, 'getPettyCashData']);
     Route::get('/pettycash/available_months', [PettyCashInsertData::class, 'getAvailableMonths']);
     Route::get('/pettycash/expenses/{year}/{month}', [PettyCashInsertData::class, 'getMonthlyExpenses']);
     Route::get('/pettycash/invoices/{project_name}/{year}/{month}', [PettyCashInsertData::class, 'getExpensesByProjectAndDate']);
     Route::get('/pettycash/invoices/available_months', [PettyCashInsertData::class, 'getAvailableInvoiceMonths']);
     Route::get('/pettycash/project-and-general-totals/{year}/{month}', [PettyCashInsertData::class, 'getProjectAndGeneralTotals']);
     Route::get('/pettycash/project-and-general-counts/{year}/{month}', [PettyCashInsertData::class, 'getStatusCountsAndProjectCount']);
});


// profit loss dashboard routes

Route::get('/plregister/check', [PLUsersController::class, 'getregister']);
Route::post('/pllogin', [PLUsersController::class, 'login']);
Route::post('/plregister', [PLUsersController::class, 'register']);
Route::post('/profitloss/reset-password', [PLUsersController::class, 'resetPassword']);
Route::post('/profitloss/forget-password', [PLUsersController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/Pl_dashboard/user_logout', [PLUsersController::class, 'logout']);
    Route::post('/project-details', [InsertProjectDetailsController::class, 'store']);
    Route::get('/project-managers', [PLUsersController::class, 'getProjectManagers']);
    Route::get('/projects', [PLUsersController::class, 'getAllProjects']);
    Route::get('/projects/managed-by/{username}', [InsertProjectDetailsController::class, 'getManagedProjects']);
    Route::get('/project-years', [InsertProjectDetailsController::class, 'getAllProjectYears']);
    Route::get('/UAE_projects', [InsertProjectDetailsController::class, 'getUAEProjectYears']);
    Route::get('/KSA_projects', [InsertProjectDetailsController::class, 'getKSAPLProjectYears']);
    Route::get('/projects-by-year/{year}', [InsertProjectDetailsController::class, 'getProjectsByYear']);
    Route::get('/manager-years/{username}', [InsertProjectDetailsController::class, 'getYearsForManager']);

    Route::get('projects/{username}/{year}', [InsertProjectDetailsController::class, 'getProjectsByManagerAndYear']);

    Route::get('projects/details/get/{id}/{year}', [InsertProjectDetailsController::class, 'getProjectDetailsByName']);
    //insert/certifiedInvoice
    Route::post('insert/actualInvoices', [InsertProjectDataController::class, 'storeAcInvoiceMonthlyValues']);
    Route::post('insert/fcInvoices', [InsertProjectDataController::class, 'storeFcInvoiceMonthlyValues']);
    Route::post('insert/planStaff', [InsertProjectDataController::class, 'storePlanStaffMonthlyValues']);
    Route::post('insert/actualStaff', [InsertProjectDataController::class, 'storeActualStaffMonthlyValues']);
    Route::post('insert/hoCosetOverHd', [InsertProjectDataController::class, 'storeHoCostOverHdMonthlyValues']);
    Route::post('insert/certifiedInvoice', [InsertProjectDataController::class, 'storeCertifiedMonthlyValues']);
    Route::post('insert/cashin', [InsertProjectDataController::class, 'storeCashinMonthlyValues']);
    Route::post('insert/cashout', [InsertProjectDataController::class, 'storeCashoutMonthlyValues']);


    Route::get('/project-data/{ProjectID}/{year}', [InsertProjectDataController::class, 'getDataByProjectId']);
    Route::get('/project-total-data/{ProjectID}', [InsertProjectDataController::class, 'getTotalDataByProjectId']);

    Route::post('/update-invoice', [InsertProjectDataController::class, 'updateActualFcInvoiceVarianceAndPerformance']);
    Route::post('/update-staff', [InsertProjectDataController::class, 'updateActualPlanStaffVarianceAndPerformance']);
    Route::post('/update-plan-staff', [InsertProjectDataController::class, 'updatePlanActualStaffVarianceAndPerformance']);
    Route::post('/update-cashin', [InsertProjectDataController::class, 'updateCashinfVarianceAndPerformance']);
    Route::post('/update-cashout', [InsertProjectDataController::class, 'updateCashoutVarianceAndPerformance']);

    Route::post('/update-branch', [InsertProjectDetailsController::class, 'updateBranch']);
    //handleCertifiedInvoiceCumulativeData
    Route::get('/cumulativeprojects/year/{year}', [GetCummulativeProjectDataController::class, 'getAllProjectsByYear']);
    Route::post('/get-cumulative-plan-staff/{year}', [GetCummulativeDataController::class, 'handlePlanStaffCumulativeData']);
    Route::post('/get-cumulative-actual-staff/{year}', [GetCummulativeDataController::class, 'handleActualStaffCumulativeData']);
    Route::post('/get-cumulative-actual-invoice/{year}', [GetCummulativeDataController::class, 'handleActualInvoiceCumulativeData']);
    Route::post('/get-cumulative-forcast-invoice_pl/{year}', [GetCummulativeDataController::class, 'handleFcInvoiceCumulativeData']);
    Route::post('/get-cumulative-cashin/{year}', [GetCummulativeDataController::class, 'handleCashInCumulativeData']);
    Route::post('/get-cumulative-cashout/{year}', [GetCummulativeDataController::class, 'handleCashOutCumulativeData']);
    Route::post('/get-cumulative-costoverhd/{year}', [GetCummulativeDataController::class, 'handleHoCostOverHdCumulativeData']);
    Route::post('/get-cumulative-certifiedInvoice/{year}', [GetCummulativeDataController::class, 'handleCertifiedInvoiceCumulativeData']);
    Route::post('/financial/update-totals/{year}', [InsertTotalDataController::class, 'updateFinancialTotals']);



    // Route to fetch notes for a specific year
    Route::get('/pl_notes/{year}', [InsertYearNotes::class, 'fetchNotes']);

    // Route to add a new note for a specific year
    Route::post('/pl_notes/{year}', [InsertYearNotes::class, 'addNote']);

    // Route to update an existing note
    Route::put('/pl_notes/{id}', [InsertYearNotes::class, 'update']);

    // Route to delete a note
    Route::delete('/pl_notes/{id}', [InsertYearNotes::class, 'destroy']);

    Route::post('profitloss/forgot-password', [PLUsersController::class, 'forgotPassword']);


    Route::get('/projects/{projectID}/fc-invoices/{year}', [GetProjectMonthlyData::class, 'getFcInvoiceMonthlyValues']);
    Route::get('/projects/{projectID}/actual-invoices/{year}', [GetProjectMonthlyData::class, 'getAcInvoiceMonthlyValues']);
    Route::get('/projects/{projectID}/cashin/{year}', [GetProjectMonthlyData::class, 'getCashinMonthlyValues']);
    Route::get('/projects/{projectID}/cashout/{year}', [GetProjectMonthlyData::class, 'getCashoutMonthlyValues']);


    Route::get('/cumulative/fc-invoices/{year}', [GetProjectMonthlyData::class, 'getFcInvoiceCumulativeValues']);
    Route::get('/cumulative/actual-invoices/{year}', [GetProjectMonthlyData::class, 'getAcInvoiceCumulativeValues']);
    Route::get('/cumulative/cashin/{year}', [GetProjectMonthlyData::class, 'getCashinCumulativeValues']);
    Route::get('/cumulative/cashout/{year}', [GetProjectMonthlyData::class, 'getCashoutCumulativeValues']);
    Route::get('/cumulative/cashout/{year}', [GetProjectMonthlyData::class, 'getCashoutCumulativeValues']);
    Route::get('/cumulative/cashout/{year}', [GetProjectMonthlyData::class, 'getCashoutCumulativeValues']);
    Route::get('/cumulative/Ho-cost-over-hd/{year}', [GetProjectMonthlyData::class, 'getHoCostOverHdCumulativeValues']);
    Route::get('/cumulative/plan-staff/{year}', [GetProjectMonthlyData::class, 'getPlanStaffCumulativeValues']);
    Route::get('/cumulative/actual-staff/{year}', [GetProjectMonthlyData::class, 'getActualStaffCumulativeValues']);
});
// Tendering dashboard routes
Route::get('/tenderRegister/check', [TenderingUserController::class, 'getRegister']);
Route::post('/tenderRegister', [TenderingUserController::class, 'register']);
Route::post('/tenderLogin', [TenderingUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tenderLogout', [TenderingUserController::class, 'logout']);
    Route::post('/insert_tenders', [TenderingInsertController::class, 'store']);
    Route::post('/tender/decision/go/{id}', [TenderingDecisionController::class, 'goDecision']);
    Route::post('/tender/decision/not-to-go/{id}', [TenderingDecisionController::class, 'notToGoDecision']);
    Route::get('/tenders/{id}/gono', [TenderingDecisionController::class, 'getGonoValue']);
    Route::get('/get_tenders_titles', [TenderingInsertController::class, 'getAllTitles']);
    Route::get('/gettendersdata/{id}', [TenderingInsertController::class, 'getTenderDataById']);
    Route::post('/update_tender/{id}', [TenderingInsertController::class, 'updateTenders']);
    Route::get('/tenders/details', [TenderingInsertController::class, 'getAllTendersDetails']);
    Route::get('/tenders/Bdetails', [TenderingInsertController::class, 'getAllBDTendersDetails']);
    Route::get('/user/{id}/name', [TenderingUserController::class, 'getUserNameById']);
    Route::get('/tender/{id}/files', [TenderingInsertController::class, 'getTenderFiles']);
    Route::get('/tender-counts', [TenderingInsertController::class, 'getTenderCounts']);
    Route::get('/tenders/Quarterdetails', [TenderingInsertController::class, 'getQuarterTendersDetails']);

    // fact sheet
    Route::post('/tenders/{tenderId}/fact-sheet', [FactSheetController::class, 'store']);
    Route::get('tenders/{tenderId}/fact-sheet', [FactSheetController::class, 'show']);
    Route::get('/tenders/role', [TenderingInsertController::class, 'getRole']);
    // In routes/api.php
    Route::post('/tenderstrf/{id}/approved_status', [TRFController::class, 'updateTRFStatus']);
    Route::post('/tenderstrf/{id}/reject_status', [TRFController::class, 'rejectTRFStatus']);

    Route::post('/tenders/{id}/finalStatus', [TenderingInsertController::class, 'finalStatus']);
    Route::post('/tenders/{id}/update-status', [TenderingInsertController::class, 'updateStatus']);
    Route::post('/tenders/{id}/reject', [TenderingInsertController::class, 'reject']);

    // Route to retrieve a specific fact sheet by its ID
    Route::get('/fact-sheets/{id}', [FactSheetController::class, 'show']);
    Route::get('/tenders/assigned', [AssignedTendersController::class, 'getAssignedTenders']);
    Route::get('/trf/assigned', [TRFController::class, 'getAssignedTRF']);
    Route::get('/tender/finalStatusChart', [TenderingInsertController::class, 'getFinalStatusTenderCounts']);

    Route::get('/tender/allStatusChart', [AssignedTendersController::class, 'getTenderCountsByStatus']);
    Route::get('/tender/assigned_tenders_count', [AssignedTendersController::class, 'getAssignedTendersCount']);
    // Route to update a specific fact sheet by its ID
    Route::put('/fact-sheets/{id}', [FactSheetController::class, 'update']);
    // Tender notes routes
    Route::get('tender/notes/{tenderId}', [TenderNotesController::class, 'index']);
    Route::post('tender/notes/{tenderId}', [TenderNotesController::class, 'store']);
    Route::put('tender/notes/{id}', [TenderNotesController::class, 'update']);
    Route::delete('tender/notes/{id}', [TenderNotesController::class, 'destroy']);
    // Route::post('/tender/reset-password', [TenderingUserController::class, 'resetPassword']);
    Route::get('/tenders_data/previous-month', [TenderTotalSummurizedController::class, 'getTendersForPreviousMonth']);

    //Fit out routes

    Route::get('/FODshboard/projectType', [HeaderController::class, 'getProjectsFODepartment']);
    Route::get('/fo/projects/{projectName}', [HeaderController::class, 'getFOProjectByName']);

    Route::post('/fit_out_header', [FitOutProjectController::class, 'store']);
    Route::post('/edit/fit_out_header', [FitOutProjectController::class, 'update']);

    Route::post('/fo/actual-value-monthly/{projectId}', [FitOutActualValueController::class, 'storeFoActualMonthlyValue']);
    Route::get('/fo/actual-value-cumulative/{projectId}', [FitOutActualValueController::class, 'calculateFoActualCumulativeValue']);
    Route::get('/fo/actual-value-current-month/{projectId}', [FitOutActualValueController::class, 'getCurrentFoActualMonthValue']);

    Route::post('/fo/plan-value-monthly/{projectId}', [FitOutPlanController::class, 'storeFoPlanMonthlyValue']);
    Route::get('/fo/plan-value-cumulative/{projectId}', [FitOutPlanController::class, 'calculateFoPlanCumulativeValue']);
    Route::get('/fo/plan-value-current-month/{projectId}', [FitOutPlanController::class, 'getCurrentFoPlanMonthValue']);

    Route::post('/fo/cashin-value-monthly/{projectId}', [FitOutCashinValueController::class, 'storeFoCashinMonthlyValue']);
    Route::get('/fo/cashin-value-cumulative/{projectId}', [FitOutCashinValueController::class, 'calculateFoCashinCumulativeValue']);
    Route::get('/fo/cashin-value-current-month/{projectId}', [FitOutCashinValueController::class, 'getCurrentFoCashinMonthValue']);

    Route::post('/fo/cashout-value-monthly/{projectId}', [FitOutCashoutValueController::class, 'storeFoCashoutMonthlyValue']);
    Route::get('/fo/cashout-value-cumulative/{projectId}', [FitOutCashoutValueController::class, 'calculateFoCashoutCumulativeValue']);
    Route::get('/fo/cashout-value-current-month/{projectId}', [FitOutCashoutValueController::class, 'getCurrentFoCashoutMonthValue']);

    Route::post('/fo/actual-percentage-monthly/{projectId}', [FitOutActualPercentageController::class, 'storeFoActualPercentageMonthlyValue']);
    Route::get('/fo/actual-percentage-current-month/{projectId}', [FitOutActualPercentageController::class, 'getCurrentFoActualPercentageMonthValue']);

    Route::post('/fo/plan-percentage-monthly/{projectId}', [FitOutPlanPercentageController::class, 'storeFoPlanPercentageMonthlyValue']);
    Route::get('/fo/plan-percentage-current-month/{projectId}', [FitOutPlanPercentageController::class, 'getCurrentFoPlanPercentageMonthValue']);


    Route::post('/fo/notes/{projectId}', 'App\Http\Controllers\FitOutControllers\FitOutKeyIssuesController@store');
    Route::put('/fo/notes/{id}', 'App\Http\Controllers\FitOutControllers\FitOutKeyIssuesController@update');
    Route::delete('/fo/notes/{id}', 'App\Http\Controllers\FitOutControllers\FitOutKeyIssuesController@destroy');
    Route::get('/fo/notes/{projectId}', 'App\Http\Controllers\FitOutControllers\FitOutKeyIssuesController@index');

    Route::post('/project/upload-images', [FitOutUploadImagesController::class, 'uploadImages']);
    Route::get('/project/{projectId}/images', [FitOutUploadImagesController::class, 'getProjectImages']);
    Route::delete('/project/images/{id}', [FitOutUploadImagesController::class, 'deleteImage']);

    // Route::put('/edit/fit_out_project/{projectName}', [FitOutProjectController::class, 'update']);
    Route::get('/editable/fit_out_project/{projectName}', [FitOutProjectController::class, 'show']);
});

Route::post('/tender/forgot-password', [TenderingUserController::class, 'forgotPassword']);

Route::post('tender/reset-password', [TenderingUserController::class, 'resetPassword']);
Route::post('/hr/forgot-password', [HRAuthController::class, 'forgotPassword']);

Route::post('hr/reset-password', [HRAuthController::class, 'resetPassword']);


Route::post('/crm/forgot-password', [CRMUserController::class, 'forgotPassword']);

Route::post('/crm/reset-password', [CRMUserController::class, 'resetPassword']);


// HR ROUTES



Route::middleware('auth:sanctum')->group(function () {
    // Status History routes
    Route::get('/cv/{id}/generate-pdf', [CVEmployeesController::class, 'generatePDF']);

    Route::get('/statushistories', [StatusHistoryController::class, 'index']);
    Route::get('/statushistories/{id}', [StatusHistoryController::class, 'show']);
    Route::post('/statushistories', [StatusHistoryController::class, 'store']);
    Route::put('/statushistories/{id}', [StatusHistoryController::class, 'update']);
    Route::delete('/statushistories/{id}', [StatusHistoryController::class, 'destroy']);
    Route::post('/hr/login', [HRAuthController::class, 'login']);
    Route::post('/hr/register', [HRAuthController::class, 'register']);
    // HR User routes
    Route::get('/hr-users', [HRUserController::class, 'index']);
    Route::get('/hr-users/{id}', [HRUserController::class, 'show']);
    Route::post('/hr-users', [HRAuthController::class, 'register']);
    Route::put('/hr-users/{id}', [HRUserController::class, 'update']);
    Route::delete('/hr-users/{id}', [HRUserController::class, 'destroy']);

    // Job Description routes
    Route::get('/jobdescriptions', [JobDescriptionController::class, 'index']);
    Route::get('/jobdescriptions/{id}', [JobDescriptionController::class, 'show']);
    Route::post('/jobdescriptions', [JobDescriptionController::class, 'store']);
    Route::put('/jobdescriptions/{id}', [JobDescriptionController::class, 'update']);
    Route::delete('/jobdescriptions/{id}', [JobDescriptionController::class, 'destroy']);

    // Project routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // Org Chart routes
    Route::get('/orgcharts', [OrgChartController::class, 'index']);
    Route::get('/orgcharts/{id}', [OrgChartController::class, 'show']);
    Route::post('/orgcharts', [OrgChartController::class, 'store']);
    Route::put('/orgcharts/{id}', [OrgChartController::class, 'update']);
    Route::delete('/orgcharts/{id}', [OrgChartController::class, 'destroy']);

    // Employee routes
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::post('/employees/update/{id}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // CV Document routes
    Route::get('/cvdocuments', [CvDocumentController::class, 'index']);
    Route::get('/cvdocuments/{id}', [CvDocumentController::class, 'show']);
    Route::post('/cvdocuments', [CvDocumentController::class, 'store']);
    Route::put('/cvdocuments/{id}', [CvDocumentController::class, 'update']);
    Route::delete('/cvdocuments/{id}', [CvDocumentController::class, 'destroy']);

    // Interview routes
    Route::get('/interviews', [InterviewController::class, 'index']);
    Route::get('/interviews/{id}', [InterviewController::class, 'show']);
    Route::post('/interviews', [InterviewController::class, 'store']);
    Route::put('/interviews/{id}', [InterviewController::class, 'update']);
    Route::delete('/interviews/{id}', [InterviewController::class, 'destroy']);
    Route::post('/hrLogout', [HRAuthController::class, 'hrLogout']);


    // Route::get('/orgcharts', [OrgChartController::class, 'index']); // Fetch all org charts
    Route::get('/orgcharts/project/{projectId}', [OrgChartController::class, 'getOrgChartByProject']); // Fetch org charts by project

    // Position routes
    Route::get('/positions', [PositionController::class, 'index']);
    Route::get('/positions/{id}', [PositionController::class, 'show']);
    Route::post('/positions', [PositionController::class, 'store']);
    Route::put('/positions/{id}', [PositionController::class, 'update']);
    Route::delete('/positions/{id}', [PositionController::class, 'destroy']);
    Route::get('/dashboard/positions', [DashboardController::class, 'getPositions']);
    Route::get('/dashboard/projects', [DashboardController::class, 'getProjects']);
    Route::get('/dashboard/employees', [DashboardController::class, 'getEmployees']);
    Route::get('/dashboard/employee-growth', [DashboardController::class, 'getEmployeeGrowth']);
    Route::get('/dashboard/employee-distribution', [DashboardController::class, 'getEmployeeDistribution']);
    Route::get('/dashboard/employees-by-project', [DashboardController::class, 'getEmployeesByProject']);
    Route::get('/dashboard/employee-status-count', [DashboardController::class, 'getEmployeeStatusCount']);

    Route::prefix('cv')->group(function () {
        // Employee-related routes
        Route::prefix('employees')->group(function () {
            Route::get('/', [CVEmployeesController::class, 'index']); // List all employees
            Route::get('/{id}', [CVEmployeesController::class, 'show']); // Get a specific employee
            Route::post('/', [CVEmployeesController::class, 'store']); // Create a new employee
            Route::put('/{id}', [CVEmployeesController::class, 'update']); // Update an employee
            Route::delete('/{id}', [CVEmployeesController::class, 'destroy']); // Delete an employee
        });

        // Experience-related routes
        Route::prefix('experiences')->group(function () {
            Route::get('/', [ExperienceController::class, 'index']); // List all experiences
            Route::get('/{id}', [ExperienceController::class, 'show']); // Get a specific experience
            Route::post('/', [ExperienceController::class, 'store']); // Create a new experience
            Route::put('/{id}', [ExperienceController::class, 'update']); // Update an experience
            Route::delete('/{id}', [ExperienceController::class, 'destroy']); // Delete an experience
        });

        // Education-related routes
        Route::prefix('education')->group(function () {
            Route::get('/', [EducationController::class, 'index']); // List all education records
            Route::get('/{id}', [EducationController::class, 'show']); // Get a specific education record
            Route::post('/', [EducationController::class, 'store']); // Create a new education record
            Route::put('/{id}', [EducationController::class, 'update']); // Update an education record
            Route::delete('/{id}', [EducationController::class, 'destroy']); // Delete an education record
        });

        // Skills-related routes
        Route::prefix('skills')->group(function () {
            Route::get('/', [SkillsController::class, 'index']); // List all skills
            Route::get('/{id}', [SkillsController::class, 'show']); // Get a specific skill
            Route::post('/', [SkillsController::class, 'store']); // Create a new skill
            Route::put('/{id}', [SkillsController::class, 'update']); // Update a skill
            Route::delete('/{id}', [SkillsController::class, 'destroy']); // Delete a skill
        });

        // Certification-related routes
        Route::prefix('certifications')->group(function () {
            Route::get('/', [CertificationController::class, 'index']); // List all certifications
            Route::get('/{id}', [CertificationController::class, 'show']); // Get a specific certification
            Route::post('/', [CertificationController::class, 'store']); // Create a new certification
            Route::put('/{id}', [CertificationController::class, 'update']); // Update a certification
            Route::delete('/{id}', [CertificationController::class, 'destroy']); // Delete a certification
        });
    });
});
Route::post('/hr/login', [HRAuthController::class, 'login']);
Route::post('/hr/register', [HRAuthController::class, 'register']);

Route::prefix('cv/ex')->group(function () {
    // Employee-related routes
    Route::prefix('employees')->group(function () {
        Route::get('/', [CVEmployeesController::class, 'index']); // List all employees
        Route::get('/{id}', [CVEmployeesController::class, 'show']); // Get a specific employee
        Route::post('/', [CVEmployeesController::class, 'store']); // Create a new employee
        Route::put('/{id}', [CVEmployeesController::class, 'update']); // Update an employee
        Route::delete('/{id}', [CVEmployeesController::class, 'destroy']); // Delete an employee
    });

    // Experience-related routes
    Route::prefix('experiences')->group(function () {
        Route::get('/', [ExperienceController::class, 'index']); // List all experiences
        Route::get('/{id}', [ExperienceController::class, 'show']); // Get a specific experience
        Route::post('/', [ExperienceController::class, 'store']); // Create a new experience
        Route::put('/{id}', [ExperienceController::class, 'update']); // Update an experience
        Route::delete('/{id}', [ExperienceController::class, 'destroy']); // Delete an experience
    });

    // Education-related routes
    Route::prefix('education')->group(function () {
        Route::get('/', [EducationController::class, 'index']); // List all education records
        Route::get('/{id}', [EducationController::class, 'show']); // Get a specific education record
        Route::post('/', [EducationController::class, 'store']); // Create a new education record
        Route::put('/{id}', [EducationController::class, 'update']); // Update an education record
        Route::delete('/{id}', [EducationController::class, 'destroy']); // Delete an education record
    });

    // Skills-related routes
    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillsController::class, 'index']); // List all skills
        Route::get('/{id}', [SkillsController::class, 'show']); // Get a specific skill
        Route::post('/', [SkillsController::class, 'store']); // Create a new skill
        Route::put('/{id}', [SkillsController::class, 'update']); // Update a skill
        Route::delete('/{id}', [SkillsController::class, 'destroy']); // Delete a skill
    });

    // Certification-related routes
    Route::prefix('certifications')->group(function () {
        Route::get('/', [CertificationController::class, 'index']); // List all certifications
        Route::get('/{id}', [CertificationController::class, 'show']); // Get a specific certification
        Route::post('/', [CertificationController::class, 'store']); // Create a new certification
        Route::put('/{id}', [CertificationController::class, 'update']); // Update a certification
        Route::delete('/{id}', [CertificationController::class, 'destroy']); // Delete a certification
    });
});


//
Route::post('/public/employees', [EmployeeController::class, 'store']);






// CRM
// Route::get('/crm/outlook/auth', [OutlookController::class, 'redirectToOutlook']);
// Route::get('/crm/outlook/callback', [OutlookController::class, 'handleOutlookCallback']);
Route::prefix('crm')->group(function () {
    Route::post('register', [CRMUserController::class, 'store']);
    Route::post('login', [CRMUserController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/outlook/store-token', [OutlookController::class, 'storeToken']);
        Route::get('/email-activities', [EmailActivityController::class, 'index']);
        // Route::get('/outlook/auth', [OutlookController::class, 'redirectToOutlook']);
        // Route::get('/outlook/callback', [OutlookController::class, 'handleOutlookCallback']);
        Route::get('/outlook/emails', [OutlookController::class, 'getEmails']);
        Route::get('dashboard/metrics', [CRMDashboardController::class, 'getMetrics']);
        Route::get('/dashboard/tasks', [DashboardController::class, 'getTaskMetrics']);

        Route::get('dashboard/departments', [CRMDashboardController::class, 'getDepartments']);
        Route::get('users', [CRMUserController::class, 'index']);
        Route::get('users/{id}', [CRMUserController::class, 'show']);
        Route::put('users/{id}', [CRMUserController::class, 'update']);
        Route::delete('users/{id}', [CRMUserController::class, 'destroy']);

        Route::get('companies', [CompanyController::class, 'index']);
        Route::post('companies', [CompanyController::class, 'store']);
        Route::get('companies/{id}', [CompanyController::class, 'show']);
        Route::put('companies/{id}', [CompanyController::class, 'update']);
        Route::delete('companies/{id}', [CompanyController::class, 'destroy']);

        Route::get('leads', [LeadController::class, 'index']);
        Route::post('leads', [LeadController::class, 'store']);
        Route::get('leads/{id}', [LeadController::class, 'show']);
        Route::put('leads/{id}', [LeadController::class, 'update']);
        Route::delete('leads/{id}', [LeadController::class, 'destroy']);

        Route::get('activities', [ActivityController::class, 'index']);
        Route::post('activities', [ActivityController::class, 'store']);
        Route::get('activities/{id}', [ActivityController::class, 'show']);
        Route::put('activities/{id}', [ActivityController::class, 'update']);
        Route::delete('activities/{id}', [ActivityController::class, 'destroy']);

        Route::get('deals', [DealController::class, 'index']);
        Route::post('deals', [DealController::class, 'store']);
        Route::get('deals/{id}', [DealController::class, 'show']);
        Route::put('deals/{id}', [DealController::class, 'update']);
        Route::delete('deals/{id}', [DealController::class, 'destroy']);
        Route::post('/deals/{deal}/attachments', [DealController::class, 'uploadAttachments']);
        Route::delete('/deals/{deal}/attachments/{attachment}', [DealController::class, 'deleteAttachment']);

        Route::get('/tasks', [TaskController::class, 'index'])
            ->name('tasks.index');
        Route::get('/tasks/calendar', [TaskController::class, 'calendar'])
            ->name('tasks.calendar');

        // Create a new task
        Route::post('/tasks', [TaskController::class, 'store'])
            ->name('tasks.store');

        // Get a specific task by ID
        Route::get('/tasks/{task}', [TaskController::class, 'show'])
            ->name('tasks.show');

        // Update a specific task by ID (full update)
        Route::put('/tasks/{task}', [TaskController::class, 'update'])
            ->name('tasks.update');

        // Update a specific task by ID (partial update)
        Route::patch('/tasks/{task}', [TaskController::class, 'update'])
            ->name('tasks.update');

        // Delete a specific task by ID
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy');

        Route::get('contacts', [ContactController::class, 'index']);
        Route::post('contacts', [ContactController::class, 'store']);
        Route::get('contacts/{id}', [ContactController::class, 'show']);
        Route::put('contacts/{id}', [ContactController::class, 'update']);
        Route::delete('contacts/{id}', [ContactController::class, 'destroy']);

        Route::get('deal-stages',  [DealStageController::class, 'index']);
        Route::post('deal-stages', [DealStageController::class, 'store']);
        Route::get('deal-stages/{id}', [DealStageController::class, 'show']);
        Route::put('deal-stages/{id}', [DealStageController::class, 'update']);
        Route::delete('deal-stages/{id}', [DealStageController::class, 'destroy']);

        Route::get('deal-leads', [DealLeadController::class, 'index']);
        Route::post('deal-leads', [DealLeadController::class, 'store']);
        Route::get('deal-leads/{id}', [DealLeadController::class, 'show']);
        Route::put('deal-leads/{id}', [DealLeadController::class, 'update']);
        Route::delete('deal-leads/{id}', [DealLeadController::class, 'destroy']);
    });
});

// ------------------------------------Service Provider-----------------------------------------
Route::get('service-providers', [ServiceProviderController::class, 'index']);  // Get all service providers
Route::get('get/projects/service-providers', [ServiceProviderController::class, 'project']);  // Get all service providers
Route::post('service-providers', [ServiceProviderController::class, 'store']); // Create a new service provider
Route::get('service-providers/{id}', [ServiceProviderController::class, 'show']);  // Get a specific service provider
Route::put('service-providers/{id}', [ServiceProviderController::class, 'update']); // Update a service provider
Route::delete('service-providers/{id}', [ServiceProviderController::class, 'destroy']);  // Delete a service provider
// -------------------------------------Purchase Order-----------------------------------------
Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);  // Get all purchase orders
Route::post('purchase-orders', [PurchaseOrderController::class, 'store']); // Create a new purchase order
Route::get('purchase-orders/{id}', [PurchaseOrderController::class, 'show']);  // Get a specific purchase order
Route::put('purchase-orders/{id}', [PurchaseOrderController::class, 'update']); // Update a purchase order
Route::delete('purchase-orders/{id}', [PurchaseOrderController::class, 'destroy']);  // Delete a purchase order
Route::get('next-po-number', [PurchaseOrderController::class, 'getNextPONumber']);

// --------------------------------------Variation Order-----------------------------------------
Route::get('variation-orders', [VariationOrderController::class, 'index']);  // Get all variation orders
Route::post('variation-orders', [VariationOrderController::class, 'store']); // Create a new variation order
Route::get('variation-orders/{id}', [VariationOrderController::class, 'show']);  // Get a specific variation order
Route::put('variation-orders/{id}', [VariationOrderController::class, 'update']); // Update a variation order
Route::delete('variation-orders/{id}', [VariationOrderController::class, 'destroy']);  // Delete a variation order
Route::get('/next-vo-number', [VariationOrderController::class, 'getNextVONumber']);
