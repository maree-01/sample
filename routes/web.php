<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AadharverifyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gstin', 'App\Http\Controllers\GstinController@index');
Route::post('/gstin/fetch', 'App\Http\Controllers\GstinController@fetchAndStoreGstinData')->name('gstin.fetch');


/* Route::get('/gstin', [GstinController::class, 'index']);
Route::post('/gstin/fetch', [GstinController::class, 'fetchAndStoreGstinData'])->name('gstin.fetch'); */

Route::get('/upload', [FileUploadController::class, 'showUploadForm'])->name('upload.form');
Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload.submit');
Route::get('/delete-file', [FileUploadController::class, 'showDeleteForm'])->name('file.delete.form');
Route::post('/delete', [FileUploadController::class, 'delete'])->name('file.delete');
Route::get('/file-list', [FileUploadController::class, 'showFileList'])->name('file.list');

Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
Route::post('/payment/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');


Route::get('/aadhar-verify',[AadharverifyController::class,'view']);
Route::post('/aadhar/send-otp', [AadharverifyController::class, 'sendOtp']);
Route::post('/aadhar/verify-otp', [AadharverifyController::class, 'verifyOtp']);
Route::get('/aadhar', [AadharverifyController::class, 'view']);
Route::post('/aadhar/get-access-token', [AadharverifyController::class, 'getAccessToken']);