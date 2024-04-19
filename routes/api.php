<?php

use App\Http\Controllers\ApiExamResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiWordController;
use App\Http\Controllers\ApiDepartmentController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\ApiExamController;
use App\Http\Controllers\ApiExamBankController;
use App\Http\Controllers\ApiExamShiftController;
use App\Http\Controllers\ApiDashBoardController;

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

header('Access-Control-Allow-Origin: http://localhost:9091');
header('Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT,DELETE');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Content-Disposition, X-Token');
header('Access-Control-Allow-Credentials: true');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [ApiUserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [ApiDashBoardController::class, 'index']);
    Route::post('/word/upload-file', [ApiWordController::class, 'uploadFile']);
    Route::post('/word/calculate', [ApiWordController::class, 'calculate']);
    Route::get('/word/calculate', [ApiWordController::class, 'index']);
    Route::get('/departments', [ApiDepartmentController::class, 'get']);
    Route::post('/departments', [ApiDepartmentController::class, 'save']);
    Route::put('/departments/{id}', [ApiDepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [ApiDepartmentController::class, 'delete']);
    Route::get('/users', [ApiUserController::class, 'get']);
    Route::post('/users', [ApiUserController::class, 'save']);
    Route::put('/users/{id}', [ApiUserController::class, 'update']);
    Route::delete('/users/{id}', [ApiUserController::class, 'delete']);
    Route::get('/exams', [ApiExamController::class, 'get']);
    Route::post('/exams', [ApiExamController::class, 'save']);
    Route::put('/exams', [ApiExamController::class, 'update']);
    Route::delete('/exams/{id}', [ApiExamController::class, 'delete']);
    Route::post('/exams/getExamShifts', [ApiExamController::class, 'getExamShifts']);
    Route::get('/exam-banks', [ApiExamBankController::class, 'get']);
    Route::get('/exam-banks/settings', [ApiExamBankController::class, 'getSettings']);
    Route::get('/exam-banks/{id}', [ApiExamBankController::class, 'configureExam']);
    Route::post('/exam-banks', [ApiExamBankController::class, 'save']);
    Route::post('/exam-banks/saveCriteria', [ApiExamBankController::class, 'saveCriteria']);
    Route::post('/exam-banks/getCriteria', [ApiExamBankController::class, 'getCriteria']);
    Route::put('/exam-banks/{id}', [ApiExamBankController::class, 'update']);
    Route::delete('/exam-banks/{id}', [ApiExamBankController::class, 'delete']);
    Route::post('/exam-shifts', [ApiExamShiftController::class, 'save']);
    Route::post('/exam-shifts/checkCodeExits', [ApiExamShiftController::class, 'checkExamShiftCodeExits']);
    Route::put('/exam-shifts', [ApiExamShiftController::class, 'update']);
    Route::delete('/exam-shifts', [ApiExamShiftController::class, 'delete']);
    Route::post('/exam-results/detail', [ApiExamResultController::class, 'getExamResultDetail']);
    Route::post('/exam-results', [ApiExamResultController::class, 'getExamResult']);
});
