<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CAAuth;
use App\Http\Controllers\Api\CAAbsen;
use App\Http\Controllers\Api\CAGaji;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/auth-signin', [CAAuth::class, 'login']);

Route::group(['middleware' => 'myauth'], function () {	
    Route::get('/history-absen', [CAAbsen::class, 'history_absen']);
    Route::get('/history-lembur', [CAAbsen::class, 'list_lembur']);
    Route::get('/riwayat-cuti', [CAAbsen::class, 'riwayat_cuti']);
    Route::post('/clock-in', [CAAbsen::class, 'clockin']);
    Route::post('/clock-out', [CAAbsen::class, 'clockout']);

    Route::get('/history-slip-gaji', [CAGaji::class, 'slip_gaji']);
    Route::get('/detail-slip-gaji', [CAGaji::class, 'detail_slip_gaji']);

    Route::post('/pengajuan-cuti', [CAAbsen::class, 'pengajuan_cuti']);

    Route::get('/ubah-password', [CAAuth::class, 'ubah_password']);
    Route::get('/auth-signout', [CAAuth::class, 'logout']);

    Route::get('/detail-profil', [CAAuth::class, 'detail_profil']);    
});