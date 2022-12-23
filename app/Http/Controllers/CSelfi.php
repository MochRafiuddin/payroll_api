<?php

namespace App\Http\Controllers;

use App\Models\LogSelfi;
use App\Models\MKaryawan;
use Illuminate\Http\Request;
use DataTables;
class CSelfi extends Controller
{
    public function index()
    {
        $karyawan = MKaryawan::where('deleted',1)->orderBy('nama_karyawan','asc')->get();
        $mulai=date('d-m-Y', strtotime(date('d-m-Y')." -1 day"));
        $akhir=date('d-m-Y');
        $title_page = "Absensi Selfi";
        return view('absen_selfi.index')
            ->with('titlePage',$title_page)
            ->with('karyawan',$karyawan)
            ->with('mulai',$mulai)
            ->with('akhir',$akhir)
            ->with('title','Absen Selfi');
    }
    public function submit()
    {
        $karyawan = MKaryawan::where('deleted',1)->orderBy('nama_karyawan','asc')->get();
        $mulai=date('d-m-Y', strtotime(date('d-m-Y')." -1 day"));
        $akhir=date('d-m-Y');
        $title_page = "Absensi Selfi";
        return view('absen_selfi.submit_data')
            ->with('titlePage',$title_page)
            ->with('karyawan',$karyawan)
            ->with('mulai',$mulai)
            ->with('akhir',$akhir)
            ->with('title','Absen Selfi');
    }
    public function datatable(Request $request)
    {
        $karyawan = $request->karyawan;
        $mulai = date('Y-m-d', strtotime($request->mulai));
        $akhir = date('Y-m-d', strtotime($request->akhir));

        $model = LogSelfi::join('m_karyawan','m_karyawan.id_karyawan','log_selfi.id_karyawan')
            ->select('m_karyawan.nama_karyawan','log_selfi.*')            
            ->orderBy('log_selfi.id_karyawan','asc');

        if ($mulai != null && $akhir != null) {
            $model = $model->whereDate('log_selfi.jam_selfi', '>=', $mulai)
                ->whereDate('log_selfi.jam_selfi', '<=', $akhir);
        }
        if ($karyawan != 0) {
            $model = $model->where('log_selfi.id_karyawan', $karyawan);
        }
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                $btn .= '<a href="javascript:void(0)" data-toggle="modal"  data-id="'.$row->id.'" data-latitude="'.$row->latitude.'" data-longitude="'.$row->longitude.'" data-gambar="'.$row->foto.'" data-original-title="Password" class="text-success editPass mr-2"><span class="mdi mdi-lock-reset"></span></a>';
                return $btn;
            })
            ->addColumn('tipe', function ($row) {
                if ($row->type == 0) {
                    $btn = 'Masuk';
                }else {
                    $btn = 'Keluar';
                }
                return $btn;
            })
            ->addColumn('status_selfi', function ($row) {
                if ($row->status == 0) {
                    $btn = 'Menunggu';
                }else if($row->status == 1){
                    $btn = 'Disetujui';
                }else{
                    $btn = 'Ditolak';
                }
                return $btn;
            })
            ->rawColumns(['action','tipe','status_selfi'])
            ->addIndexColumn()
            ->toJson();
    }
    public function set_status(Request $request)
    {
        $log = LogSelfi::find($request->id);
        $log->status = $request->status;
        $log->update();
        // dd($dataExcel);
        return response()->json(['status'=>true]);
    }
    public function datatable_submit(Request $request)
    {
        $dataTam = $request->tampil;
        $karyawan = $request->karyawan;
        $mulai = date('Y-m-d', strtotime($request->mulai));
        $akhir = date('Y-m-d', strtotime($request->akhir));

        $model = LogSelfi::join('m_karyawan','m_karyawan.id_karyawan','log_selfi.id_karyawan')
            ->select('m_karyawan.nama_karyawan','log_selfi.*');
        if ($dataTam == 0) {
            $model = $model->where('log_selfi.submitted',$dataTam);
        }
        if ($mulai != null && $akhir != null) {
            $model = $model->whereDate('log_selfi.jam_selfi', '>=', $mulai)
                ->whereDate('log_selfi.jam_selfi', '<=', $akhir);
        }
        if ($karyawan != 0) {
            $model = $model->where('log_selfi.id_karyawan', $karyawan);
        }
            $model = $model->where('log_selfi.status','=',1)
            ->orderBy('log_selfi.id_karyawan','asc');
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                $btn .= '<a href="javascript:void(0)" data-toggle="modal"  data-id="'.$row->id.'" data-latitude="'.$row->latitude.'" data-longitude="'.$row->longitude.'" data-gambar="'.$row->foto.'" data-original-title="Password" class="text-success editPass mr-2"><span class="mdi mdi-lock-reset"></span></a>';
                return $btn;
            })
            ->addColumn('tipe', function ($row) {
                if ($row->type == 0) {
                    $btn = 'Masuk';
                }else {
                    $btn = 'Keluar';
                }
                return $btn;
            })
            ->addColumn('submitted', function ($row) {
                if ($row->submitted == 1) {
                    $btn = '<p class="text-success"><span class="mdi mdi-check"></span></p>';
                }else {
                    $btn = '';
                }
                
                return $btn;
            })
            ->rawColumns(['action','submitted','tipe'])            
            ->addIndexColumn()
            ->toJson();
    }
}
