<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WipController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('get-user', [UserController::class, 'getUser']);
    Route::get('get-alluser', [UserController::class, 'getAllUser']);
    Route::get('get-client', [ClientController::class, 'getClient']);
    Route::get('get-allticket/{company}', [TicketController::class, 'getAllTicket']);
    Route::get('get-quotation/{id}', [QuotationController::class, 'getQuotation']);
    Route::get('get-allquotation/{company}', [QuotationController::class, 'getAllQuotation']);
    Route::get('get-allwip/{company}', [WipController::class, 'getAllWip']);
    Route::get('get-wipbyid/{id}', [WipController::class, 'getWipById']);
    Route::get('get-wipage/{company}', [WipController::class, 'wipAge']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('post-client', [ClientController::class, 'uploadClient']);
    Route::post('post-ticket/{company}', [TicketController::class, 'uploadTicket']);
    Route::post('post-quotation', [QuotationController::class, 'uploadQuotation']);
    Route::post('post-wipatt', [WipController::class, 'uploadWipAtt']);
    Route::post('post-files/{company}', [WipController::class, 'uploadFiles']);
    Route::post('post-otherfiles/{company}', [WipController::class, 'uploadOtherFiles']);
    Route::post('post-deletefiles', [WipController::class, 'deleteFiles']);
    Route::post('post-deleteothers', [WipController::class, 'deleteOtherFiles']);
    Route::post('post-deletephotoinfo', [WipController::class, 'deletePhotoInfo']);
    Route::post('post-photoinfo', [WipController::class, 'uploadPhotoInfo']);
    Route::put('put-quotstatus/{id}', [QuotationController::class, 'changeStatus']);
    Route::put('put-infodesc/{id}', [WipController::class, 'updateInfo']);
});
