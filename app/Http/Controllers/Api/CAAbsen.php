<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MApiKey;
use App\Models\TAbsensi;
use App\Models\TLembur;
use App\Models\TIzinnCuti;
use App\Models\TIzinDetail;
use App\Models\MRole;
use App\Models\MapApprIzin;
use App\Models\Notif;
use App\Models\RefTemplateNotif;
use App\Models\RefTipeAbsensi;
use App\Models\MKaryawan;
use App\Models\User;
use App\Models\LogSelfi;

use DateInterval;
use DatePeriod;
use DateTime;

class CAAbsen extends Controller
{
    public function history_absen(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();
        $start = $request->tanggal_mulai;
        $end = $request->tanggal_akhir;

        if ($start == null && $end == null) {
            $start = date('Y/m/01');
            $end = date('Y/m/d');
        }

        $m = TAbsensi::from('t_absensi as a')
            ->leftJoin('ref_tipe_absensi as b','a.id_tipe_absensi','=','b.id_tipe_absensi')
            ->select('a.*','b.nama_tipe_absensi')
            ->whereBetween('a.tanggal', [$start, $end])
            ->where('a.id_karyawan',$user->id_karyawan)
            ->orderBy('a.tanggal','asc')
            ->get()->toArray();

        // $hsl = array_search("2022-08-06", array_column($m, 'tanggal'));
        // array_unshift($m, '');     // Prepend a dummy element to the start of the array.
        // unset($m[0]); 
        // dd($m);
        $data = [];
        $period = new DatePeriod(
            new DateTime($start),
            new DateInterval('P1D'),
            new DateTime($end.' +1 days')
        );

        foreach($period as $key => $value){            
            $hsl = array_search($value->format('Y-m-d'), array_column($m, 'tanggal'));
            if ($hsl !== false) {
                $data[$key]['tanggal'] = $m[$hsl]["tanggal"];
                $data[$key]['masuk'] = $m[$hsl]["tanggal_masuk"];
                $data[$key]['keluar'] = $m[$hsl]["tanggal_keluar"];
                $data[$key]['terlambat'] = $m[$hsl]["menit_terlambat"];
                $data[$key]['early leave'] = $m[$hsl]["menit_early_leave"];
                $data[$key]['nama_tipe_absensi'] = $m[$hsl]["nama_tipe_absensi"];
            }else{
                $data[$key]['tanggal'] = $value->format('Y-m-d');
                $data[$key]['masuk'] = null;
                $data[$key]['keluar'] = null;
                $data[$key]['terlambat'] = null;
                $data[$key]['early leave'] = null;
                $data[$key]['nama_tipe_absensi'] = "tidak masuk";
            }
        }

        // dd($data);
        return response()->json([
            'success' => true,
            'message' => 'Success',
            'code' => 1,
            'data' => $data
        ], 200);
    }    

    public function list_lembur(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();

        $start = $request->tanggal_mulai;
        $end = $request->tanggal_akhir;

        $m = TLembur::where('t_lembur.deleted',1)
            ->selectRaw('t_lembur.approval,t_lembur.approval2,t_lembur.approval3,sum(jumlah_jam) as total_jam,m_karyawan.nama_karyawan,t_lembur.tanggal,m_karyawan.id_karyawan, t_lembur.alasan_lembur')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_lembur.id_karyawan','left')
            ->groupBy('m_karyawan.id_karyawan','m_karyawan.nama_karyawan','tanggal','t_lembur.approval')
            ->whereBetween('t_lembur.tanggal',[$start, $end])
            ->where('t_lembur.id_karyawan',$user->id_karyawan)
            ->get();


        return response()->json([
            'success' => true,
            'message' => 'Success',
            'code' => 1,
            'data' => $m
        ], 200);
    }

    public function riwayat_cuti(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $model = TIzinnCuti::select("t_izin.*",'m_karyawan.nama_karyawan','ref_tipe_absensi.nama_tipe_absensi')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_izin.id_karyawan','left')
            ->join('ref_tipe_absensi','ref_tipe_absensi.id_tipe_absensi','=','t_izin.id_tipe_absensi','left')
            ->where('t_izin.deleted',1)
            ->whereMonth('t_izin.tanggal_mulai',$bulan)
            ->whereYear('t_izin.tanggal_mulai',$tahun)
            ->where('t_izin.id_karyawan',$user->id_karyawan)
            ->get();


        return response()->json([
            'success' => true,
            'message' => 'Success',
            'code' => 1,
            'data' => $model
        ], 200);
    }    
    
    public function pengajuan_cuti(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();

        $id_tipe_absensi = $request->id_tipe_absensi;
        $alasan = $request->alasan;
        $start = $request->tanggal_mulai;
        $end = $request->tanggal_selesai;  

        $mCuti = new TIzinnCuti;
        $mCuti->id_tipe_absensi = $id_tipe_absensi;
        $mCuti->id_karyawan = $user->id_karyawan;
        $mCuti->tanggal_mulai = date('Y-m-d',strtotime($start));
        $mCuti->tanggal_selesai = date('Y-m-d',strtotime($end));
        $mCuti->alasan = $alasan;
        $mCuti->approval = 0;
        $mCuti->approve_by = 0;
        $mCuti->created_by = 0;
        $mCuti->updated_by = 0;
        $mCuti->save();

        $period = new DatePeriod(
            new DateTime($request->tanggal_mulai),
            new DateInterval('P1D'),
            new DateTime($request->tanggal_selesai.' +1 days')
        );
            
        foreach($period as $key){
            $absensi = new TIzinDetail;
            $absensi->id_karyawan = $user->id_karyawan;
            $absensi->tanggal = $key->format('Y-m-d');            
            $absensi->id_izin = $mCuti->id_izin;
            $absensi->save();
        }

        $urutan = MRole::withDeleted()->where('id_role',$user->id_role)->first();
        $list_approval = MRole::withDeleted()
            ->where('urutan_approval_cuti','>=',$urutan->urutan_approval_cuti)
            ->groupBy('urutan_approval_cuti')
            ->orderBy('urutan_approval_cuti','ASC')
            ->get();
        foreach ($list_approval as $key => $value) {
            if ($key==0) {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 1;
                $appr->approve2_by = $user->id_user;
                $appr->approve_date = date('Y-m-d H:i:s');
                $appr->save();
            }elseif ($key==1) {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 0;
                $appr->approve2_by = 0;                
                $appr->save();
                $cekrole = MRole::where('id_role',$value->id_role)->first();
                if ($cekrole->kode_role == "asman" || $cekrole->kode_role == "manager") {
                    
                    $departemen = MKaryawan::select('id_departemen')->where('id_karyawan',$user->id_karyawan)->first();
                    $userA = MKaryawan::join('m_users','m_users.id_karyawan','=','m_karyawan.id_karyawan')                                
                                    ->where('m_karyawan.id_departemen',$departemen->id_departemen)
                                    ->where('m_users.id_role',$value->id_role)
                                    ->get(); 
                }else{
                    $userA = User::where('id_role',$value->id_role)->where('deleted',1)->get();                    
                }                   
                    $ref_notif = RefTemplateNotif::where('kode','approval_izin')->where('deleted',1)->first();
                    // dd($userA);
                    foreach ($userA as $values) {                    
                        $abs = RefTipeAbsensi::where('id_tipe_absensi',$request->id_tipe_absensi)->first();
                        $kar = MKaryawan::where('id_karyawan',$request->id_karyawan)->first();
                        $isi = $ref_notif->isi;
                        $isi = str_replace("{nama_karyawan}",$user->id_karyawan,$isi);
                        $isi = str_replace("{tipe_absensi}",$abs->nama_tipe_absensi,$isi);
                        $isi = str_replace("{tanggal_mulai}",$request->tanggal_mulai,$isi);
                        $isi = str_replace("{tanggal_selesai}",$request->tanggal_selesai,$isi);
                        $isi = str_replace("{alasan}",$request->alasan,$isi);
                        
                        $not = new Notif;
                        $not->id_user = $values->id_user;
                        $not->judul = $ref_notif->judul;
                        $not->url = "absensi/izin-cuti";
                        $not->isi = $isi;
                        $not->is_read = 0;
                        $not->deleted = 1;
                        $not->save();
    
                        $new = User::find($values->id_user);                    
                        $new->new_notif = $values->new_notif + 1;
                        $new->update();
    
                        // Mail::to($values->email)->send(new Email_notif($values->name,$ref_notif->judul,$isi,"absensi/izin-cuti"));
                    }
                
            }else {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 0;
                $appr->approve2_by = 0;                
                $appr->save();
            }
        }
        if (count($list_approval)==1) {

            $Cuti = TIzinnCuti::find($mCuti->id_izin);

            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->whereBetween('tanggal',[$Cuti->tanggal_mulai,$Cuti->tanggal_selesai])->update(['deleted' => 0]);
            
            // TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_tipe_absensi',$Cuti->id_tipe_absensi)->delete();
            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_izin',$Cuti->id_izin)->delete();
            
            
            $period = new DatePeriod(
                new DateTime($Cuti->tanggal_mulai),
                new DateInterval('P1D'),
                new DateTime($Cuti->tanggal_selesai.' +1 days')
            );
            
            foreach($period as $key){
                $absensi = new TAbsensi;
                $absensi->id_karyawan = $Cuti->id_karyawan;
                $absensi->tanggal = $key->format('Y-m-d');
                $absensi->id_tipe_absensi = $Cuti->id_tipe_absensi;
                $absensi->id_izin = $Cuti->id_izin;
                $absensi->save();
            }   
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan Cuti Success',
            'code' => 1,
        ], 200);
    }

    public function clockin(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();

        if(!$request->hasFile('foto')) {
            return response()->json(['upload_file_not_found'], 400);
        }

        $file = $request->file('foto');
        
        if(!$file->isValid()) {
            return response()->json(['invalid_file_upload'], 400);
        }

        $path = public_path().'/upload/foto';
        $file->move($path, $file->getClientOriginalName());

        // $foto = round(microtime(true) * 1000).'.'.$request->file('foto')->extension();
        // $request->file('foto')->move(public_path('upload/foto'), $foto);

        $mCuti = new LogSelfi;
        $mCuti->id_karyawan = $user->id_karyawan;
        $mCuti->jam_selfi = $request->jam_selfi;
        $mCuti->type = 0;
        $mCuti->latitude = $request->latitude;
        $mCuti->longitude = $request->longitude;
        $mCuti->foto = $file->getClientOriginalName();
        $mCuti->status = 0;
        $mCuti->save();

        return response()->json([
            'success' => true,
            'message' => 'Absensi Success',
            'code' => 1,
        ], 200);
    }    

    public function clockout(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();

        $foto = round(microtime(true) * 1000).'.'.$request->file('foto')->extension();
        $request->file('foto')->move(public_path('upload/foto'), $foto);

        $mCuti = new LogSelfi;
        $mCuti->id_karyawan = $user->id_karyawan;
        $mCuti->jam_selfi = $request->jam_selfi;
        $mCuti->type = 1;
        $mCuti->latitude = $request->latitude;
        $mCuti->longitude = $request->longitude;
        $mCuti->foto = $foto;
        $mCuti->status = 0;
        $mCuti->save();

        return response()->json([
            'success' => true,
            'message' => 'Absensi Success',
            'code' => 1,
        ], 200);
    }

    public function get_status_absen(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();        

        $status = LogSelfi::whereDate('jam_selfi', date('Y-m-d'))->where('id_karyawan',$user->id_karyawan)->orderBy('jam_selfi','desc')->first();
        if ($status) {
            if ($status->type == 0) {
                $data = 'Masuk';
            }elseif ($status->type == 1) {
                $data = 'Keluar';
            }
        }else {
            $data ='Belum_absen';
        }

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'status' => $data,
            'code' => 1,
        ], 200);
    }
    
    public function get_jam(Request $request)
    {
        $token = MApiKey::where('token',$request->header('auth-key'))->first();
        $user = User::where('id_user',$token->id_user)->first();        
        
        $in = LogSelfi::whereDate('jam_selfi', date('Y-m-d'))->where('id_karyawan',$user->id_karyawan)->where('type',0)->orderBy('jam_selfi','desc')->first();
        $out = LogSelfi::whereDate('jam_selfi', date('Y-m-d'))->where('id_karyawan',$user->id_karyawan)->where('type',1)->orderBy('jam_selfi','desc')->first();        

        if ($in != null) {
            $clockin = date('H:i',strtotime($in->jam_selfi));
        }else {
            $clockin = '-';
        }

        if ($out) {
            $clockout = date('H:i',strtotime($out->jam_selfi));
        }else {
            $clockout = '-';
        }

        if ($in != null && $out != null) {            
            $seconds = strtotime($out->jam_selfi) - strtotime($in->jam_selfi);
            $selisih = intval($seconds / 60 / 60);
        }elseif ($in != null && $out == null) {
            $seconds = strtotime(date('Y-m-d H:i:s')) - strtotime($in->jam_selfi);
            $selisih = intval($seconds / 60 / 60);
        }else{
            $selisih = '-';
        } 

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'clockin' => $clockin,
            'clockout' => $clockout,
            'working_hr' => $selisih,
            'code' => 1,
        ], 200);
    }
}
