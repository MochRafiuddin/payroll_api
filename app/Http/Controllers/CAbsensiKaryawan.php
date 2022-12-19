<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use DateTime;
use App\Models\TAbsensi;
use App\Models\MKaryawan;
use App\Exports\ExportAbsensikaryawan;
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class CAbsensiKaryawan extends Controller
{
    public function index()
    {
        $tanggal = date('m/01/Y').' - '.date('m/d/Y');
        $date = explode(' - ', $tanggal);

        return view('absensi_karyawan.index')
                ->with('title','Absensi Karyawan')
                ->with('tanggal',$tanggal)
                ->with('awal',$date[0])
                ->with('akhir',$date[1]);
                // ->with('akhir',$akhir);
    }
    public function filter(Request $request)
    {
            $tanggal = $request->tanggal;
            if ($tanggal) {
                $date = explode(' - ', $request->tanggal);
                $awal = $date[0];
                $akhir = $date[1];
            }else{
                $tanggal = date('m/01/Y').' - '.date('m/d/Y');
                $date = explode(' - ', $tanggal);
                $awal = $date[0];
                $akhir = $date[1];
            }
        return view('absensi_karyawan.index')
                ->with('title','Absensi Karyawan')
                ->with('tanggal',$tanggal)
                ->with('awal',$date[0])
                ->with('akhir',$date[1]);
    }
    public function datatable(Request $request)
    {
        $date = explode(' - ', $request->start);
        $start = Carbon::createFromFormat('m/d/Y',$date[0])->format('Y-m-d');
        $end = Carbon::createFromFormat('m/d/Y',$date[1])->format('Y-m-d');

        $model = TAbsensi::join("m_karyawan","m_karyawan.id_karyawan","=","t_absensi.id_karyawan","left")
                ->select("m_karyawan.nama_karyawan","m_karyawan.id_karyawan")
                ->where('t_absensi.deleted',1)
                ->where('t_absensi.id_tipe_absensi',1)
                ->distinct()->get('t_absensi.id_karyawan');

        $pluck_id_karyawan = $model->pluck('id_karyawan')->toArray();

        $m = TAbsensi::from('t_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b','a.id_tipe_absensi','=','b.id_tipe_absensi')
                        ->select('a.*','b.nama_tipe_absensi')
                        ->whereBetween('a.tanggal', [$start, $end])
                        ->whereIn('a.id_karyawan',$pluck_id_karyawan)->get();
        // dd($m);
        $data = [
            'data_karyawan' => $model,
            'data_absensi_karyawan' => $m,
        ];

        $response = array("message"=>"ok","data"=>$data);
        return response()->json($response,200);
    }
    public function getAbsensiById($tanggal,$id_karyawan)
    {
        $m = TAbsensi::from('t_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b','a.id_tipe_absensi','=','b.id_tipe_absensi')
                        ->select('a.*','b.nama_tipe_absensi')
                        ->where('a.tanggal',$tanggal)->where('a.id_karyawan',$id_karyawan)->first();

        return response()->json($m,200);
    }
    public function ExportAbsensikaryawan($awal,$akhir){
        
        $awal = Carbon::createFromFormat('m-d-Y',$awal)->format('Y-m-d');
        $akhir = Carbon::createFromFormat('m-d-Y',$akhir)->format('Y-m-d');        
        return Excel::download(new ExportAbsensikaryawan($awal,$akhir), 'ExportAbsensikaryawan.xlsx');
        // $model = TAbsensi::where('deleted',1)->where('id_tipe_absensi',1)->distinct()->get('id_karyawan');
        // return view('absensi_karyawan.export')
        //         ->with('title','Absensi Karyawan')
        //         ->with('hsl',$model)
        //         ->with('awal',$awal)
        //         ->with('akhir',$akhir);
    }
    
}
