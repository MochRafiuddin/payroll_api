<?php

use App\Http\Controllers\CAbsen;
use App\Http\Controllers\CAgama;
use App\Http\Controllers\CBank;
use App\Http\Controllers\CCalculateGaji;
use App\Http\Controllers\CDashboard;
use App\Http\Controllers\CDepartement;
use App\Http\Controllers\CGaji;
use App\Http\Controllers\CGajiKaryawan;
use App\Http\Controllers\CRiwayatGajiKaryawan;
use App\Http\Controllers\CInvoice;
use App\Http\Controllers\CJabatan;
use App\Http\Controllers\CKaryawan;
use App\Http\Controllers\CLogin;
use App\Http\Controllers\CPeriode;
use App\Http\Controllers\CShift;
use App\Http\Controllers\CStatusKaryawan;
use App\Http\Controllers\CStatusKawin;
use App\Http\Controllers\CTarifLembur;
use App\Http\Controllers\CTarifPPH;
use App\Http\Controllers\CGrupKaryawan;
use App\Http\Controllers\CAturShiftGrupKaryawan;
use App\Http\Controllers\CAturShiftKaryawan;
use App\Http\Controllers\CIzinCuti;
use App\Http\Controllers\CRefAbsensi;
use App\Http\Controllers\CUser;
use App\Http\Controllers\CRole;
use App\Http\Controllers\CAsuransiEkses;
use App\Http\Controllers\CLembur;
use App\Http\Controllers\CAbsensiKaryawan;
use App\Http\Controllers\CTotalGajiPeriode;
use App\Http\Controllers\CAbsensiLembur;
use App\Http\Controllers\CTotalAbsensi;
use App\Http\Controllers\CCron;
use App\Http\Controllers\CTemp;
use App\Http\Controllers\CNavbar;
use App\Http\Controllers\CMarkedKaryawan;
use App\Http\Controllers\CSelfi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [CLogin::class,'index'])->middleware("guest");
Route::post('/auth', [CLogin::class,'authenticate']);
Route::get('/logout', [CLogin::class,'logout']);
Route::get('setlocale/{locale}',function($lang){
       \Session::put('locale',$lang);
       // $langlang = \DB::table('m_bahasa')->select('id_bahasa')
       // 				->where('kode_bahasa','=',$lang)
       //                  ->first();

       // \Session::put('id_locale',$langlang->id_bahasa);
       return redirect()->back();   
});

Route::middleware(['auth','language'])->group(function ()
{
    ### DROPDOWN LIST MENU ###
    Route::group(['prefix' => 'referensi'],function ()
    {
        Route::get('agama',[CAgama::class,'index'])->name('agama-index')->middleware('check.role:referensi_agama_list'); 
        Route::get('bank',[CBank::class,'index'])->name('bank-index')->middleware('check.role:referensi_bank_list');
        Route::get('departement',[CDepartement::class,'index'])->name('departement-index')->middleware('check.role:referensi_departement_list');
        Route::get('jabatan',[CJabatan::class,'index'])->name('jabatan-index')->middleware('check.role:referensi_jabatan_list');
        Route::get('status-pegawai',[CStatusKaryawan::class,'index'])->name('status-karyawan-index')->middleware('check.role:referensi_status_karyawan_list'); 
    });
    Route::group(['prefix' => 'master'],function ()
    {
        Route::get('shift',[CShift::class,'index'])->name('shift-index')->middleware('check.role:master_shift_list');
        Route::get('kawin-status',[CStatusKawin::class,'index'])->name('status-kawin-index')->middleware('check.role:master_status_kawin_list');
        Route::get('karyawan',[CKaryawan::class,'index'])->name('karyawan-index')->middleware('check.role:master_karyawan_list');
        Route::get('officer-grup',[CGrupKaryawan::class,'index'])->name('grup-karyawan-index')->middleware('check.role:master_grup_karyawan_list');
    });
    Route::group(['prefix' => 'konfigurasi'],function ()
    {
        Route::get('tarif-pph',[CTarifPPH::class,'index'])->name('tarif-pph-index')->middleware('check.role:konfigurasi_tarif_PPH_list');
        Route::get('tarif-lembur',[CTarifLembur::class,'index'])->name('tarif-lembur-index')->middleware('check.role:konfigurasi_tarif_lembur_list');
    });
    Route::group(['prefix' => 'absensi'],function ()
    {
        Route::get('atur-shift-grup-karyawan',[CAturShiftGrupKaryawan::class,'index'])->name('atur-shift-grup-karyawan-index')->middleware('check.role:absensi_atur_shift_grup_karyawan_list');
        Route::get('atur-shift-karyawan',[CAturShiftKaryawan::class,'index'])->name('atur-shift-karyawan-index')->middleware('check.role:absensi_atur_shift_karyawan_list');
        Route::get('fingerprint',[CAbsen::class,'index'])->name('absen-index')->middleware('check.role:absensi_data_fingerprint_list');
        Route::get('ref-tipe-absensi',[CRefAbsensi::class,'index'])->name('ref-tipe-absensi-index')->middleware('check.role:absensi_tipe_absensi_list');
        Route::get('izin-cuti',[CIzinCuti::class,'index'])->name('izin-cuti-index')->middleware('check.role:absensi_izincuti_list');
        Route::get('over-time',[CLembur::class,'index'])->name('lembur-index')->middleware('check.role:absensi_lembur_karyawan_list');
    });
    Route::group(['prefix' => 'penggajian'],function ()
    {
        Route::get('salary',[CGaji::class,'index'])->name('gaji-index')->middleware('check.role:penggajian_gaji_list');
        Route::get('periode',[CPeriode::class,'index'])->name('periode-index')->middleware('check.role:penggajian_periode_list');
        Route::get('gj-pegawai',[CGajiKaryawan::class,'index'])->name('gaji-pegawai-index')->middleware('check.role:penggajian_gaji_karyawan_list');
        Route::get('g-period',[CGajiKaryawan::class,'index_periode'])->name('gaji-period-index')->middleware('check.role:penggajian_gaji_karyawan_periode_list');
        Route::get('asuransi',[CAsuransiEkses::class,'index'])->name('asuransi-ekses-index')->middleware('check.role:penggajian_asuransi_ekses_list');
        Route::get('approval-gaji',[CTotalGajiPeriode::class,'index'])->name('approval-gaji-index')->middleware('check.role:penggajian_approval_total_gaji_list');
    });
    Route::group(['prefix' => 'riwayat'],function ()
    {
        Route::get('riwayat-penggajian',[CRiwayatGajiKaryawan::class,'index'])->name('riwayat-gaji-index')->middleware('check.role:riwayat_penggajian_list');
        Route::get('riwayat-absensi',[CAbsensiKaryawan::class,'index'])->name('absensi-karyawan-view')->middleware('check.role:riwayat_absensi_karyawan_list');
        Route::get('riwayat-lembur',[CAbsensiLembur::class,'index'])->name('absensi-lembur-view')->middleware('check.role:riwayat_absensi_lembur_list');
        Route::group(['prefix' => 'total-absensi'],function ()
        {
            Route::get('/',[CTotalAbsensi::class,'index'])->name('total-absensi-index')->middleware('check.role:riwayat_total_absensi_list');
            Route::get('/data',[CTotalAbsensi::class,'datatable'])->name('total-absensi-data');
            Route::get('/detail',[CTotalAbsensi::class,'detail'])->name('total-absensi-detail');
        });
    });
    Route::group(['prefix' => 'setting'],function ()
    {
        Route::get('user',[CUser::class,'index'])->name('user-index')->middleware('check.role:setting_user_list');
        Route::get('role',[CRole::class,'index'])->name('role-index')->middleware('check.role:setting_role_list');
    });

    ### DASHBOARD ###
    Route::group(['prefix' => 'dashboard'],function ()
    {
        Route::get('/',[CDashboard::class,'index'])->middleware('check.role:dashboard');
        Route::get('/karyawan',[CDashboard::class,'karyawan'])->middleware('check.role:dashboard_karyawan');
        Route::get('/data-lembur-karyawan',[CDashboard::class,'datatable_lembur_karyawan']);
        Route::get('/data-terlambat-karyawan',[CDashboard::class,'datatable_terlambat_karyawan']);
        Route::get('/data-early-karyawan',[CDashboard::class,'datatable_early_karyawan']);
        Route::get('/data-tidak-masuk-karyawan',[CDashboard::class,'datatable_tidak_masuk_karyawan']);
        Route::get('/chart',[CDashboard::class,'chart']);
        Route::get('/chart-gaji',[CDashboard::class,'chart_gaji']);
        Route::get('/data-izcu',[CDashboard::class,'datatable_izcu']);
        Route::get('/data-lembur',[CDashboard::class,'datatable_lembur']);
        Route::get('/data-tidak-masuk',[CDashboard::class,'datatable_tidak_masuk']);
        Route::get('/grafik-absensi',[CDashboard::class,'grafik_absensi']);
        Route::get('/detail-list-pershift',[CDashboard::class,'get_detail_list_pershift']);
    });

    ### AGAMA ###
    Route::group(['prefix' => 'agama'],function ()
    {
        Route::get('/create',[CAgama::class,'create'])->middleware('check.role:referensi_agama_add');
        Route::post('/create-save',[CAgama::class,'create_save']);
        Route::get('/show/{id}',[CAgama::class,'show'])->middleware('check.role:referensi_agama_edit');
        Route::post('/show-save/{id}',[CAgama::class,'show_save']);
        Route::get('/delete/{id}',[CAgama::class,'delete'])->middleware('check.role:referensi_agama_delete');
        Route::get('/data',[CAgama::class,'datatable']);
    });

    ### BANK ###
    Route::group(['prefix' => 'bank'],function ()
    {
        Route::get('/create',[CBank::class,'create'])->middleware('check.role:referensi_bank_add');
        Route::post('/create-save',[CBank::class,'create_save']);
        Route::get('/show/{id}',[CBank::class,'show'])->middleware('check.role:referensi_bank_edit');
        Route::post('/show-save/{id}',[CBank::class,'show_save']);
        Route::get('/delete/{id}',[CBank::class,'delete'])->middleware('check.role:referensi_bank_delete');
        Route::get('/data',[CBank::class,'datatable']);
    });
    ### DEPARTEMENT ###
    Route::group(['prefix' => 'departement'],function ()
    {
        Route::get('/create',[CDepartement::class,'create'])->middleware('check.role:referensi_departement_add');
        Route::post('/create-save',[CDepartement::class,'create_save']);
        Route::get('/show/{id}',[CDepartement::class,'show'])->middleware('check.role:referensi_departement_edit');
        Route::post('/show-save/{id}',[CDepartement::class,'show_save']);
        Route::get('/delete/{id}',[CDepartement::class,'delete'])->middleware('check.role:referensi_departement_delete');
        Route::get('/data',[CDepartement::class,'datatable']);
    });
    ### SHIFT ###
    Route::group(['prefix' => 'shift'],function ()
    {
        Route::get('/create',[CShift::class,'create'])->middleware('check.role:master_shift_add');
        Route::post('/create-save',[CShift::class,'create_save']);
        Route::get('/show/{id}',[CShift::class,'show'])->middleware('check.role:master_shift_edit');
        Route::post('/show-save/{id}',[CShift::class,'show_save']);
        Route::get('/delete/{id}',[CShift::class,'delete'])->middleware('check.role:master_shift_delete');
        Route::get('/data',[CShift::class,'datatable']);
    });
    ### JABATAN ###
    Route::group(['prefix' => 'jabatan'],function ()
    {
        Route::get('/create',[CJabatan::class,'create'])->middleware('check.role:referensi_jabatan_add');
        Route::post('/create-save',[CJabatan::class,'create_save']);
        Route::get('/show/{id}',[CJabatan::class,'show'])->middleware('check.role:referensi_jabatan_edit');
        Route::post('/show-save/{id}',[CJabatan::class,'show_save']);
        Route::get('/delete/{id}',[CJabatan::class,'delete'])->middleware('check.role:referensi_jabatan_delete');
        Route::get('/data',[CJabatan::class,'datatable']);
    });
    ### STATUS KARYAWAN ###
    Route::group(['prefix' => 'status_karyawan'],function ()
    {
        Route::get('/create',[CStatusKaryawan::class,'create'])->middleware('check.role:referensi_status_karyawan_add');
        Route::post('/create-save',[CStatusKaryawan::class,'create_save']);
        Route::get('/show/{id}',[CStatusKaryawan::class,'show'])->middleware('check.role:referensi_status_karyawan_edit');
        Route::post('/show-save/{id}',[CStatusKaryawan::class,'show_save']);
        Route::get('/delete/{id}',[CStatusKaryawan::class,'delete'])->middleware('check.role:referensi_status_karyawan_delete');
        Route::get('/data',[CStatusKaryawan::class,'datatable']);
    });
    ### STATUS KAWIN ###
    Route::group(['prefix' => 'status_kawin'],function ()
    {
        Route::get('/create',[CStatusKawin::class,'create'])->middleware('check.role:master_status_kawin_add');
        Route::post('/create-save',[CStatusKawin::class,'create_save']);
        Route::get('/show/{id}',[CStatusKawin::class,'show'])->middleware('check.role:master_status_kawin_edit');
        Route::post('/show-save/{id}',[CStatusKawin::class,'show_save']);
        Route::get('/delete/{id}',[CStatusKawin::class,'delete'])->middleware('check.role:master_status_kawin_delete');
        Route::get('/data',[CStatusKawin::class,'datatable']);
    });
    ### KARYAWAN ###
    Route::group(['prefix' => 'karyawan'],function ()
    {
        Route::get('/create',[CKaryawan::class,'create'])->middleware('check.role:master_karyawan_add');
        Route::post('/create-save',[CKaryawan::class,'create_save']);
        // Route::get('/show/{id}',[CKaryawan::class,'show'])->middleware('check.role:master_karyawan_edit');
        Route::get('/show/{id}/{id1?}',[CKaryawan::class,'show'])->middleware('check.role:master_karyawan_edit');
        // Route::post('/show-save/{id}',[CKaryawan::class,'show_save']);
        Route::post('/show-save/{id}/{id1?}',[CKaryawan::class,'show_save']);
        Route::get('/delete/{id}',[CKaryawan::class,'delete'])->middleware('check.role:master_karyawan_delete');
        Route::get('/data',[CKaryawan::class,'datatable']);
        Route::post('/cek-shift-karyawan',[CKaryawan::class,'cek_shift_karyawan']);
        Route::post('/cek-shift',[CKaryawan::class,'cek_shift']);
    });
    ### GAJI ###
    Route::group(['prefix' => 'gaji'],function ()
    {
        Route::get('/create',[CGaji::class,'create'])->middleware('check.role:penggajian_gaji_add');
        Route::post('/create-save',[CGaji::class,'create_save']);
        Route::get('/show/{id}',[CGaji::class,'show'])->middleware('check.role:penggajian_gaji_edit');
        Route::post('/show-save/{id}',[CGaji::class,'show_save']);
        Route::get('/delete/{id}',[CGaji::class,'delete'])->middleware('check.role:penggajian_gaji_delete');
        Route::get('/data',[CGaji::class,'datatable']);
    });
    ### PERIODE ###
    Route::group(['prefix' => 'periode'],function ()
    {
        Route::get('/create',[CPeriode::class,'create'])->middleware('check.role:penggajian_periode_add');
        Route::post('/create-save',[CPeriode::class,'create_save']);
        Route::get('/show/{id}',[CPeriode::class,'show']);
        Route::post('/show-save/{id}',[CPeriode::class,'show_save']);
        Route::get('/delete/{id}',[CPeriode::class,'delete'])->middleware('check.role:penggajian_periode_delete');
        Route::get('/data',[CPeriode::class,'datatable']);
        Route::get('/actived-periode/{id_periode}',[CPeriode::class,'actived_periode']);
    });
    ### TARIF PPH ###
    Route::group(['prefix' => 'tarif_pph'],function ()
    {
        Route::get('/create',[CTarifPPH::class,'create'])->middleware('check.role:konfigurasi_tarif_PPH_add');
        Route::post('/create-save',[CTarifPPH::class,'create_save']);
        Route::get('/show/{id}',[CTarifPPH::class,'show'])->middleware('check.role:konfigurasi_tarif_PPH_edit');
        Route::post('/show-save/{id}',[CTarifPPH::class,'show_save']);
        Route::get('/delete/{id}',[CTarifPPH::class,'delete'])->middleware('check.role:konfigurasi_tarif_PPH_delete');
        Route::get('/data',[CTarifPPH::class,'datatable']);
    });
    ### GAJI KARYAWAN ###
    Route::group(['prefix' => 'gaji_karyawan'],function ()
    {
        Route::get('/set-gaji/{id}',[CGajiKaryawan::class,'set_gaji'])->middleware('check.role:penggajian_gaji_karyawan_set_gaji');
        Route::post('/set-gaji-save/{id}',[CGajiKaryawan::class,'set_gaji_save']);
        Route::get('/data',[CGajiKaryawan::class,'datatable']);

        ### PERIODE ###
        Route::get('/set-gaji-periode/{id}',[CGajiKaryawan::class,'set_gaji_periode'])->middleware('check.role:penggajian_gaji_karyawan_periode_set_gaji');
        Route::post('/set-gaji-save-periode/{id}',[CGajiKaryawan::class,'set_gaji_save_periode']);
        Route::get('/data-periode',[CGajiKaryawan::class,'datatable_periode']);

        ### RIWAYAT ###
        Route::get('/data-riwayat/{id}',[CRiwayatGajiKaryawan::class,'datatable_riwayat']);
        Route::get('/detail_riwayat/{id}',[CRiwayatGajiKaryawan::class,'detail_gaji_karyawan']);
        Route::get('/hitung-gaji-karyawan',[CRiwayatGajiKaryawan::class,'calculate_gaji_karyawan'])->name('calculate-gaji')->middleware('check.role:riwayat_penggajian_calculate');
        Route::get('/export-gaji-karyawan',[CRiwayatGajiKaryawan::class,'export_gaji_karyawan'])->name('export-gaji')->middleware('check.role:riwayat_penggajian_export');

        ### ASURANSI EKSES ###
        Route::get('/data-asuransi-ekses',[CAsuransiEkses::class,'datatable_asuransi']);
        Route::get('/create-asuransi',[CAsuransiEkses::class,'form_tambah'])->name('create-asuransi')->middleware('check.role:penggajian_asuransi_ekses_add');
        Route::get('/edit-asuransi/{id}',[CAsuransiEkses::class,'form_edit'])->name('edit-asuransi')->middleware('check.role:penggajian_asuransi_ekses_edit');
        Route::get('/get-detail-asuransi-ekses',[CAsuransiEkses::class,'get_asuransi_ekses']);
        Route::get('/get-cicilan-asuransi',[CAsuransiEkses::class,'get_cicilan_asuransi']);
        Route::post('/submit-asuransi-ekses',[CAsuransiEkses::class,'submit_asuransi_ekses'])->name('submit-asuransi');
        Route::post('/update-asuransi-ekses',[CAsuransiEkses::class,'update_cicilan_asuransi'])->name('update-asuransi');
        Route::get('/delete-asuransi/{id}',[CAsuransiEkses::class,'delete'])->name('delete-asuransi')->middleware('check.role:penggajian_asuransi_ekses_delete');

    });
    ### TARIF LEMBUR ###
    Route::group(['prefix' => 'tarif_lembur'],function ()
    {
        Route::get('/create',[CTarifLembur::class,'create'])->middleware('check.role:konfigurasi_tarif_lembur_add');
        Route::post('/create-save',[CTarifLembur::class,'create_save']);
        Route::get('/show/{id}',[CTarifLembur::class,'show'])->middleware('check.role:konfigurasi_tarif_lembur_edit');
        Route::post('/show-save/{id}',[CTarifLembur::class,'show_save']);
        Route::get('/delete/{id}',[CTarifLembur::class,'delete'])->middleware('check.role:konfigurasi_tarif_lembur_delete');
        Route::get('/data',[CTarifLembur::class,'datatable']);
    });
    ### GRUP KARYAWAN ###
    Route::group(['prefix' => 'grup_karyawan'],function ()
    {
        Route::get('/create',[CGrupKaryawan::class,'create']);
        Route::post('/create-save',[CGrupKaryawan::class,'create_save']);
        Route::get('/show/{id}',[CGrupKaryawan::class,'show']);
        Route::post('/show-save/{id}',[CGrupKaryawan::class,'show_save']);
        Route::get('/delete/{id}',[CGrupKaryawan::class,'delete']);
        Route::get('/data',[CGrupKaryawan::class,'datatable']);
    });
    ### ATUR SHIFT GRUP KARYAWAN ###
    Route::group(['prefix' => 'atur-shift-grup-karyawan'],function ()
    {
        Route::get('/data',[CAturShiftGrupKaryawan::class,'datatable']);
        Route::get('/format-excel',[CAturShiftGrupKaryawan::class,'format_excel']);
        // Route::post('/create-save',[CGrupKaryawan::class,'create_save']);
        // Route::get('/show/{id}',[CGrupKaryawan::class,'show']);
        // Route::post('/show-save/{id}',[CGrupKaryawan::class,'show_save']);
        // Route::get('/delete/{id}',[CGrupKaryawan::class,'delete']);
        Route::post('/import',[CAturShiftGrupKaryawan::class,'import']);
        Route::post('/set-shift-karyawan',[CAturShiftGrupKaryawan::class,'set_shift'])->name('set_shift');
        
    });
    ### ATUR SHIFT KARYAWAN ###
    Route::group(['prefix' => 'atur-shift-karyawan'],function ()
    {
        Route::get('/data',[CAturShiftKaryawan::class,'datatable']);
        Route::get('/format-excel',[CAturShiftKaryawan::class,'format_excel']);
        // Route::post('/create-save',[CGrupKaryawan::class,'create_save']);
        // Route::get('/show/{id}',[CGrupKaryawan::class,'show']);
        // Route::post('/show-save/{id}',[CGrupKaryawan::class,'show_save']);
        // Route::get('/delete/{id}',[CGrupKaryawan::class,'delete']);
        Route::post('/import',[CAturShiftKaryawan::class,'import']);
        Route::post('/set-shift-karyawan',[CAturShiftKaryawan::class,'set_shift'])->name('set_shift_karyawan');
        
    });
    ### ABSENSI ####
    Route::group(['prefix' => 'absen'],function ()
    {
        Route::get('/data/{tanggal}/{karyawan}',[CAbsen::class,'datatable']);
        Route::get('/import',[CAbsen::class,'import']);
        Route::get('/fingerprint',[CAbsen::class,'fingerprint']);
        Route::get('/get-log-absensi',[CAbsen::class,'get_log_absensi']);
        Route::post('/add-log-absensi',[CAbsen::class,'add_log_absensi']);
        Route::get('/edit-log-absensi',[CAbsen::class,'edit_log_absensi']);
        Route::post('/update-log-absensi',[CAbsen::class,'update_log_absensi']);
        Route::get('/delete-log-absensi',[CAbsen::class,'delete_log_absensi']);
        Route::post('/priview-import',[CAbsen::class,'priview_import']);
        Route::post('/save-import',[CAbsen::class,'save_import']);
        Route::get('/unduh-format',[CAbsen::class,'download_format_import']);
       Route::get('/data-fingerprint',[CAbsen::class,'fingerprint_datatable']);
       Route::get('/get-filter-fingerprint',[CAbsen::class,'get_filter_fingerprint']);
       Route::get('/get-fingerprint/{id}',[CAbsen::class,'get_fingerprint']);
       Route::get('/delete-fingerprint/{id}',[CAbsen::class,'delete_fingerprint']);
       Route::post('/add-fingerprint',[CAbsen::class,'add_fingerprint']);
       Route::post('/update-fingerprint',[CAbsen::class,'update_fingerprint']);
       Route::post('/get-fingerprint-data',[CCron::class,'get_fingerprint']);
        
    });
    ### TIPE ABSENSI ###
    Route::group(['prefix' => 'ref-tipe-absensi'],function ()
    {
        Route::get('/create',[CRefAbsensi::class,'create'])->middleware('check.role:absensi_tipe_absensi_add');
        Route::post('/create-save',[CRefAbsensi::class,'create_save']);
        Route::get('/show/{id}',[CRefAbsensi::class,'show'])->middleware('check.role:absensi_tipe_absensi_edit');
        Route::post('/show-save/{id}',[CRefAbsensi::class,'show_save']);
        Route::get('/delete/{id}',[CRefAbsensi::class,'delete'])->middleware('check.role:absensi_tipe_absensi_delete');
        Route::get('/data',[CRefAbsensi::class,'datatable']);
    });
    ### IZIN ###
    Route::group(['prefix' => 'izin-cuti'],function ()
    {
        Route::get('/create',[CIzinCuti::class,'create'])->middleware('check.role:absensi_izincuti_add');
        Route::post('/create-save',[CIzinCuti::class,'create_save']);
        Route::get('/show/{id}/{id1}/{id2}',[CIzinCuti::class,'show'])->middleware('check.role:absensi_izincuti_edit');
        Route::post('/show-save/{id}',[CIzinCuti::class,'show_save']);
        Route::get('/delete/{id}',[CIzinCuti::class,'delete'])->middleware('check.role:absensi_izincuti_delete');
        Route::get('/data',[CIzinCuti::class,'datatable']);
        Route::get('/get-karyawan',[CIzinCuti::class,'get_karyawan']);
        Route::get('/data-karyawan-departemen',[CIzinCuti::class,'karyawanBydepartemen']);
        Route::post('/persetujuan',[CIzinCuti::class,'persetujuan']);
        Route::get('/get-tipe-absensi/{id}',[CIzinCuti::class,'get_tipe_absensi']);
        Route::get('/get-izin/{id}',[CIzinCuti::class,'get_jumlah_izin']);
    });
    ### USER ###
    Route::group(['prefix' => 'user'],function ()
    {
        Route::get('/create',[CUser::class,'create'])->middleware('check.role:setting_user_add');
        Route::post('/create-save',[CUser::class,'create_save']);
        Route::get('/show/{id}',[CUser::class,'show'])->middleware('check.role:setting_user_edit');
        Route::post('/show-save/{id}',[CUser::class,'show_save']);
        Route::get('/delete/{id}',[CUser::class,'delete'])->middleware('check.role:setting_user_delete');
        Route::get('/data',[CUser::class,'datatable']);
        Route::post('/reset-pass',[CUser::class,'reset_pass'])->middleware('check.role:setting_user_reset_password');
        Route::get('/get-karyawan/{id}',[CUser::class,'get_nama_karyawan']);
        Route::get('/get-role/{id}/{id1}',[CUser::class,'get_role']);
        Route::get('/get-anggota/{id}/{id1}/{id3}',[CUser::class,'get_anggota']);
        Route::get('/switch-user/{id}',[CUser::class,'switch_user']);
    });
    ### ROLE ###
    Route::group(['prefix' => 'role'],function ()
    {
        Route::get('/create',[CRole::class,'create'])->middleware('check.role:setting_role_add');
        Route::post('/create-save',[CRole::class,'create_save']);
        Route::get('/show/{id}',[CRole::class,'show'])->middleware('check.role:setting_role_edit');
        Route::post('/show-save/{id}',[CRole::class,'show_save']);
        Route::get('/delete/{id}',[CRole::class,'delete'])->middleware('check.role:setting_role_delete');
        Route::get('/data',[CRole::class,'datatable']);
        Route::get('/set-menu/{id}',[CRole::class,'set_menu'])->middleware('check.role:setting_role_set_menu');
        Route::post('/set-menu-save/{id}',[CRole::class,'Set_menu_save']);
    });
    ### LEMBUR ###
    Route::group(['prefix' => 'lembur'],function ()
    {
        Route::get('/data',[CLembur::class,'datatable']);
        Route::post('/persetujuan',[CLembur::class,'persetujuan']);
        Route::get('/edit/{id_karyawan}/{tanggal}/{id_karyawan_filter}',[CLembur::class,'edit']);
        Route::get('/detail/{id_karyawan}/{tanggal}/{id_karyawan_filter}',[CLembur::class,'detail']);
        Route::post('/update-lembur',[CLembur::class,'update']);
    });
    ### LEMBUR ###
    Route::group(['prefix' => 'absensi-lembur'],function ()
    {
        Route::get('/data/{tanggal}/{tanggal1}/{id_karyawan}/',[CAbsensiLembur::class,'datatable']);
        Route::get('/export',[CAbsensiLembur::class,'ExportAbsensiLembur'])->middleware('check.role:riwayat_absensi_lembur_export');
    });
    ### total gaji periode ###
    Route::group(['prefix' => 'total_gaji_periode'],function ()
    {
        Route::get('/data/{id}',[CTotalGajiPeriode::class,'datatable']);
        Route::get('/export-gaji-karyawan/{id_periode}',[CTotalGajiPeriode::class,'export_gaji_karyawan'])->name('export_gaji_karyawan2');
        Route::post('/persetujuan',[CTotalGajiPeriode::class,'persetujuan']);
    });
    ### absensi karyawan ###
    Route::group(['prefix' => 'absensi-karyawan'],function ()
    {
        Route::get('/view-filter',[CAbsensiKaryawan::class,'filter']);
        Route::get('/data',[CAbsensiKaryawan::class,'datatable']);
        Route::get('/get-absensi/{id}/{id1}',[CAbsensiKaryawan::class,'getAbsensiById']);
        Route::get('/export/{id}/{id1}',[CAbsensiKaryawan::class,'ExportAbsensikaryawan'])->middleware('check.role:riwayat_absensi_karyawan_export');
    });
    ### INVOICE ###
    Route::group(['prefix' => 'invoice'],function ()
    {
        Route::get('/print/{id}',[CInvoice::class,'pdfVoice'])->middleware('check.role:riwayat_penggajian_print');
    });

    Route::group(['prefix' => 'marked'],function ()
    {
        Route::get('/',[CMarkedKaryawan::class,'index'])->name('marked');
        Route::post('/save',[CMarkedKaryawan::class,'save']);
        Route::post('/add-bulk',[CMarkedKaryawan::class,'add_bulk']);
        Route::get('/delete-bulk',[CMarkedKaryawan::class,'delete_bulk']);
        Route::post('/save-update',[CMarkedKaryawan::class,'save_update']);
        Route::get('/data',[CMarkedKaryawan::class,'datatable']);
    });
    Route::group(['prefix' => 'selfi'],function ()
    {
        Route::get('/',[CSelfi::class,'index'])->name('selfi');        
        Route::get('/data',[CSelfi::class,'datatable']);
        Route::post('/set-status',[CSelfi::class,'set_status']);
        Route::get('/submit',[CSelfi::class,'submit']);
        Route::get('/data-submit',[CSelfi::class,'datatable_submit']);
        Route::post('/get-selfi-data',[CCron::class,'get_selfi']);

    });
    Route::get('/hitung-gaji',[CCalculateGaji::class,'hitung']);

});
 Route::get('/count-attendance/{bulan?}/{tahun?}',[CAbsen::class,'cron']);
 Route::get('/count-attendance-before/{bulan?}/{tahun?}',[CAbsen::class,'cron_mount_before']);
 Route::get('/send-izin-detail',[CTemp::class,'kirim_detail_izin']);
//  Route::get('/get-fingerprint-data',[CCron::class,'get_fingerprint']);
 Route::get('/update-user-new-notif/{id}',[CNavbar::class,'update_user']);
 Route::get('/update-notif/{id}',[CNavbar::class,'update_notif']);
 
 Route::get('/get-user-fingerprint-data',[CCron::class,'get_user_info']);
 Route::get('/get-data-fingerprint',[CCron::class,'get_chechinout']);