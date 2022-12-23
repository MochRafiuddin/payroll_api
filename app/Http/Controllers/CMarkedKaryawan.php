<?php

namespace App\Http\Controllers;

use App\Models\MarkedKaryawan;
use App\Models\TAbsensi;
use App\Models\MKaryawan;
use App\Models\MDepartement;
use App\Models\MGrupKaryawan;
use App\Models\MShift;
use App\Models\LogAbsensi;
use Illuminate\Http\Request;
use DataTables;
use Carbon\Carbon;
use DB;
use DateInterval;
use DatePeriod;
use DateTime;
class CMarkedKaryawan extends Controller
{
    public function index()
    {        
        $departemen = MDepartement::where('deleted',1)->get();
        $group = MGrupKaryawan::where('deleted',1)->get();
        $shift = MShift::where('deleted',1)->get();
        return view('mark.index')            
            ->with('departemen',$departemen)
            ->with('shift',$shift)
            ->with('group',$group)
            ->with('title','Marked');
    }
    public function save(Request $request)
    {        
        $id_log = LogAbsensi::where('id_karyawan',$request->id_karyawan)->where('tanggal_shift',$request->tanggal)->first();
        // dd($id_log->id);
        $m = new MarkedKaryawan;
        $m->id_log_absensi = $id_log->id;
        $m->id_karyawan = $request->id_karyawan;
        $m->tanggal = $request->tanggal;        
        $m->save();

        return response()->json(['status'=>true,'msg'=>'Sukses Mengubah Data']);        
    }

    public function save_update(Request $request)
    {                        
        // dd($request->val_shift_masuk);
        foreach ($request->box as $key) {            
            if ($request->shift != 0) {
                $shi = MShift::where('id_shift',$request->shift)->first();
                LogAbsensi::where('id',$key)->update([ 
                    'id_shift' => $request->shift, 
                    'jam_masuk_shift' => $shi->jam_masuk, 
                    'jam_keluar_shift' => $shi->jam_keluar,
                ]);
            }

            if ($request->val_shift_masuk != null) {
                if ($request->op_shift_masuk == 0) {                    
                   LogAbsensi::where('id',$key)->update([ 
                        'jam_masuk_shift' => $request->val_shift_masuk,
                    ]); 
                }elseif ($request->op_shift_masuk == 1) {
                    DB::statement('update log_absensi set jam_masuk_shift = DATE_ADD(jam_masuk_shift, INTERVAL '.$request->val_shift_masuk.' MINUTE) where id ='.$key);
                }elseif ($request->op_shift_masuk == 2) {
                    DB::statement('update log_absensi set jam_masuk_shift = DATE_SUB(jam_masuk_shift, INTERVAL '.$request->val_shift_masuk.' MINUTE) where id ='.$key);
                }
            }

            if ($request->val_shift_keluar != null) {
                if ($request->op_shift_keluar == 0) {                    
                   LogAbsensi::where('id',$key)->update([ 
                        'jam_keluar_shift' => $request->val_shift_keluar,
                    ]); 
                }elseif ($request->op_shift_keluar == 1) {
                    DB::statement('update log_absensi set jam_keluar_shift = DATE_ADD(jam_keluar_shift, INTERVAL '.$request->val_shift_keluar.' MINUTE) where id ='.$key);
                }elseif ($request->op_shift_keluar == 2) {
                    DB::statement('update log_absensi set jam_keluar_shift = DATE_SUB(jam_keluar_shift, INTERVAL '.$request->val_shift_keluar.' MINUTE) where id ='.$key);
                }
            }

            if ($request->val_absen_masuk != null) { 
                $log = LogAbsensi::where('id',$key)->first('waktu_masuk');
                if ($request->op_absen_masuk == 0) {
                    $log = date('Y-m-d',strtotime($log->waktu_masuk)).' '.$request->val_absen_masuk;
                   LogAbsensi::where('id',$key)->update([ 
                        'waktu_masuk' => $log,
                    ]); 
                }elseif ($request->op_absen_masuk == 1) {
                    DB::statement('update log_absensi set waktu_masuk = DATE_ADD(waktu_masuk, INTERVAL '.$request->val_absen_masuk.' MINUTE) where id ='.$key);
                }elseif ($request->op_absen_masuk == 2) {
                    DB::statement('update log_absensi set waktu_masuk = DATE_SUB(waktu_masuk, INTERVAL '.$request->val_absen_masuk.' MINUTE) where id ='.$key);
                }
            }

            if ($request->val_absen_keluar != null) { 
                $log = LogAbsensi::where('id',$key)->first('waktu_keluar');
                if ($request->op_absen_keluar == 0) {
                    $log = date('Y-m-d',strtotime($log->waktu_keluar)).' '.$request->val_absen_keluar;
                   LogAbsensi::where('id',$key)->update([ 
                        'waktu_keluar' => $log,
                    ]); 
                }elseif ($request->op_absen_keluar == 1) {
                    DB::statement('update log_absensi set waktu_keluar = DATE_ADD(waktu_keluar, INTERVAL '.$request->val_absen_keluar.' MINUTE) where id ='.$key);
                }elseif ($request->op_absen_keluar == 2) {
                    DB::statement('update log_absensi set waktu_keluar = DATE_SUB(waktu_keluar, INTERVAL '.$request->val_absen_keluar.' MINUTE) where id ='.$key);
                }
            }
        }

        return response()->json(['status'=>true,'msg'=>'Sukses Mengubah Data']);
        // return response()->json(['status'=>true,'msg'=>$cek]);
    }

    public function add_bulk(Request $request)
    {        
        $kar = MKaryawan::where('id_departemen_label',$request->departemen)->where('aktif',1)->where('deleted',1)->get();
        $date = explode(' - ', $request->tanggal);
        $start = Carbon::createFromFormat('m/d/Y',$date[0])->format('Y-m-d');
        $end = Carbon::createFromFormat('m/d/Y',$date[1])->format('Y-m-d'); 
        $period = new DatePeriod(
            new DateTime($start),
            new DateInterval('P1D'),
            new DateTime($end.' +1 days')
        );

        foreach ($period as $key) {
            foreach ($kar as $key1) {
                $id_log = LogAbsensi::where('id_karyawan',$key1->id_karyawan)->where('tanggal_shift',$key->format('Y-m-d'))->first();
                // dd($id_log->id);
                if ($id_log != null) {
                    $m = new MarkedKaryawan;
                    $m->id_log_absensi = $id_log->id;
                    $m->id_karyawan = $key1->id_karyawan;
                    $m->tanggal = $key->format('Y-m-d');
                    $m->save();
                }
            }
        }
            
        return response()->json(['status'=>true,'msg'=>'Sukses Tambah Data']);
    }

    public function delete_bulk(Request $request)
    {        
        foreach ($request->box as $key) {
            MarkedKaryawan::where('id_marked',$key)->delete();
            // dd($key);
        }
            
        return response()->json(['status'=>true,'msg'=>'Sukses Hapus Data']);
    }

    public function query_text($opra,$query_utama,$valu,$nilai,$id_karyawan,$tanggal)
    {        
        $query = str_replace("{string_operator}",$opra,$query_utama);
        $query = str_replace("{value}",$valu,$query);
        $query = str_replace("{x}",$nilai,$query);
        $query = str_replace("{karyawan}",$id_karyawan,$query);
        $query = str_replace("{tanggal}",$tanggal,$query);
        return $query;        
    }

    public function datatable(Request $request)
    {   
        if ($request->data == 0) {
            $model = MarkedKaryawan::where('tanggal',null);
        }elseif($request->data == 1){
            $date = explode(' - ', $request->tanggal);
            $start = Carbon::createFromFormat('m/d/Y',$date[0])->format('Y-m-d');
            $end = Carbon::createFromFormat('m/d/Y',$date[1])->format('Y-m-d'); 
    
            $model = MarkedKaryawan::join('log_absensi','log_absensi.id','marked_karyawan.id_log_absensi')
                ->join('m_shift','m_shift.id_shift','log_absensi.id_shift')
                ->select('log_absensi.*','m_shift.nama_shift','marked_karyawan.id_marked')
                ->whereBetween('log_absensi.tanggal_shift',[$start,$end])
                ->orderBy('log_absensi.tanggal_shift','asc');
        }else {
            $date = explode(' - ', $request->tanggal);
            $start = Carbon::createFromFormat('m/d/Y',$date[0])->format('Y-m-d');
            $end = Carbon::createFromFormat('m/d/Y',$date[1])->format('Y-m-d'); 

            $model = LogAbsensi::join('m_shift','m_shift.id_shift','log_absensi.id_shift')
                ->join('m_karyawan','m_karyawan.id_karyawan','log_absensi.id_karyawan')
                ->select('log_absensi.*','m_shift.nama_shift','m_karyawan.id_departemen_label','m_karyawan.id_grup_karyawan')
                ->whereBetween('log_absensi.tanggal_shift',[$start,$end])
                ->orderBy('log_absensi.tanggal_shift','asc');
            if ($request->departemen != 0) {
                $model = $model->where('m_karyawan.id_departemen_label',$request->departemen);
            }
            if ($request->grup != 0) {
                $model = $model->where('m_karyawan.id_grup_karyawan',$request->grup);
            }
            if ($request->shift != 0) {
                $model = $model->where('log_absensi.id_shift',$request->shift);
            }
        }
        // dd($model->get());
        return DataTables::of($model)  
        ->addColumn('action', function ($row) {
            $btn = '<div class="d-flex justify-content-center"><input type="checkbox" name="cekbox[]" id="cekbox" value="'.$row->id.'"></div>';
            return $btn;
        })
        ->addColumn('waktu_masuk', function ($row) {
            $btn = "<p style='white-space:nowrap;'>".$row->waktu_masuk."</p>";
            return $btn;
        })
        ->addColumn('waktu_keluar', function ($row) {
            $btn = "<p style='white-space:nowrap;'>".$row->waktu_keluar."</p>";
            return $btn;
        })
        ->rawColumns(['action','waktu_masuk','waktu_keluar'])          
        ->addIndexColumn()
        ->toJson();
    }

}
