<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKaryawan;
use App\Models\MPeriode;
use App\Models\TAbsensi;
use App\Models\TLembur;
use App\Models\MapGajiKaryawanPeriode;
use DataTables;
use Carbon\Carbon;
use App\Traits\Helper;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportAbsensiLembur;

class CAbsensiLembur extends Controller
{
    use Helper;
    public function index()
    {   
        $tanggal = date('m-01-Y').' - '.date('m-d-Y');
        $date = explode(' - ', $tanggal);
        $karyawan = MKaryawan::withDeleted()->get();
        return view('absensi_lembur.index',compact('karyawan'))
            ->with('title','Riwayat Absensi Lembur')
            ->with('tanggal',$tanggal)
            ->with('awal',$date[0])
            ->with('akhir',$date[1]);
    }
    public function datatable($date,$date1,$id_karyawan)
    {                
        $start = Carbon::createFromFormat('m-d-Y',$date)->format('Y-m-d');
        $end = Carbon::createFromFormat('m-d-Y',$date1)->format('Y-m-d');
        
        if ($id_karyawan!=0) {
            $model = TAbsensi::where('t_absensi.deleted',1)
            ->selectRaw('m_karyawan.nama_karyawan,m_karyawan.employee_id,m_karyawan.no_bpjs,t_absensi.tanggal,m_shift.nama_shift,t_absensi.jam_keluar_shift,t_absensi.jam_masuk_shift,t_absensi.tanggal_masuk,t_absensi.tanggal_keluar,m_departemen.nama_departemen,m_karyawan.id_karyawan')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_absensi.id_karyawan','left')
            ->join('m_shift','m_shift.id_shift','=','t_absensi.id_shift','left')
            ->join('m_departemen','m_departemen.id_departemen','=','m_karyawan.id_departemen','left')
            ->where('id_tipe_absensi', 1)
            ->where('m_karyawan.id_karyawan',$id_karyawan)
            ->whereBetween('t_absensi.tanggal', [$start, $end])
            ->orderBy('t_absensi.id_karyawan')
            ->orderBy('t_absensi.tanggal'); 
        }else{
            $model = TAbsensi::where('t_absensi.deleted',1)
            ->selectRaw('m_karyawan.nama_karyawan,m_karyawan.employee_id,m_karyawan.no_bpjs,t_absensi.tanggal,m_shift.nama_shift,t_absensi.jam_keluar_shift,t_absensi.jam_masuk_shift,t_absensi.tanggal_masuk,t_absensi.tanggal_keluar,m_departemen.nama_departemen,m_karyawan.id_karyawan')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_absensi.id_karyawan','left')
            ->join('m_shift','m_shift.id_shift','=','t_absensi.id_shift','left')
            ->join('m_departemen','m_departemen.id_departemen','=','m_karyawan.id_departemen','left')
            ->where('id_tipe_absensi', 1)
            ->where('m_karyawan.deleted', 1)
            ->whereBetween('t_absensi.tanggal', [$start, $end])
            ->orderBy('t_absensi.id_karyawan')
            ->orderBy('t_absensi.tanggal'); 
        }

        return DataTables::eloquent($model)
        ->editColumn('tanggal_masuk',function ($row) {
                    $html = date('H:i:s', strtotime($row->tanggal_masuk));
                    return $html;
                })
            ->editColumn('tanggal_keluar',function ($row) {
                    $html = date('H:i:s', strtotime($row->tanggal_keluar));
                    return $html;
                })
            ->editColumn('absent',function ($row) {
                    $html = "";
                    return $html;
                })
            ->editColumn('total_hour',function ($row) {
                    return $this->sum_total_jam($row->id_karyawan,$row->tanggal,"0");
                })
            ->editColumn('reason',function ($row) {
                $data = TLembur::where('id_karyawan',$row->id_karyawan)
                    ->where('tanggal',$row->tanggal)                    
                    ->where('deleted',1)
                    ->first();
                if ($data) {
                    $html=$data->alasan_lembur;
                }else {
                    $html="";
                }
                    return $html;
                })
            ->editColumn('ke1',function ($row) {
                    return $this->sum_total_jam($row->id_karyawan,$row->tanggal,"1.5");
                })
            ->editColumn('ke2',function ($row) {
                    return $this->sum_total_jam($row->id_karyawan,$row->tanggal,"2");
                })
            ->editColumn('ke3',function ($row) {
                    return $this->sum_total_jam($row->id_karyawan,$row->tanggal,"3");
                })
            ->editColumn('ke4',function ($row) {
                    return $this->sum_total_jam($row->id_karyawan,$row->tanggal,"4");
                })
            ->editColumn('total_count',function ($row) {
                    $ke1=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"1.5") * 1.5;
                    $ke2=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"2") * 2;
                    $ke3=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"3") * 3;
                    $ke4=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"4") * 4;
                    $tot=$ke4+$ke3+$ke2+$ke1;
                    return $tot;
                })
            ->editColumn('basic_salary',function ($row) {                    
                    return $this->ribuan(ceil($this->salary($row->id_karyawan,$row->tanggal,0)));
                })
            ->editColumn('salary_hour',function ($row) {                    
                    return $this->ribuan(ceil($this->salary($row->id_karyawan,$row->tanggal,1)));
                })
            ->editColumn('total_cost',function ($row) {                    
                    $ke1=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"1.5") * 1.5;
                    $ke2=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"2") * 2;
                    $ke3=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"3") * 3;
                    $ke4=$this->sum_total_jam($row->id_karyawan,$row->tanggal,"4") * 4;
                    $tot=$ke4+$ke3+$ke2+$ke1;
                    $tot1=$this->salary($row->id_karyawan,$row->tanggal,1);
                    return $this->ribuan(ceil($tot1*$tot));
                })
            ->addIndexColumn()
            ->toJson();
    }

    public function sum_total_jam($karyawan,$tanggal,$index)
    {   
        if ($index== "0") {
            $data = TLembur::where('id_karyawan',$karyawan)
            ->where('tanggal',$tanggal)
            ->where('approval',1)
            ->where('approval2',1)
            ->where('approval3',1)
            ->where('deleted',1)
            ->get();
        }else {
            $data = TLembur::where('id_karyawan',$karyawan)
            ->where('tanggal',$tanggal)
            ->where('approval',1)
            ->where('approval2',1)
            ->where('approval3',1)
            ->where('deleted',1)
            ->where('index_tarif',$index)
            ->get();
        }
            
        $total = 0;
        foreach ($data as $key) {
            $total += $key->jumlah_jam;
        }
        
        return $total;
    }
    public function salary($karyawan,$tanggal,$beda)
    {   
        $tahun = date('Y', strtotime($tanggal));
        $bulan = date('m', strtotime($tanggal));
        $nom=0;
        $periode = MPeriode::where('bulan',$bulan)
            ->where('tahun',$tahun)
            ->first();
        if ($periode) {
            $data = MapGajiKaryawanPeriode::where('id_karyawan',$karyawan)
                ->where('id_gaji',1)
                ->where('deleted',1)
                ->where('id_periode',$periode->id_periode)
                ->first();
            if ($beda==0) {
                if ($data) {
                    $nom=$data->nominal;
                }else{
                    $nom=0;
                }
            }else {
                if ($data) {
                    $nom=$data->nominal/173;
                }else{
                    $nom=0/173;
                }
            }            
        }
        return $nom;
    }

    public function ExportAbsensiLembur(){
        $date1 = (!empty($_GET["start"])) ? ($_GET["start"]) : ('');
        $date2 = (!empty($_GET["akhir"])) ? ($_GET["akhir"]) : ('');
        $id_karyawan = $_GET["id_karyawan"];
        return Excel::download(new ExportAbsensiLembur($id_karyawan,$date1,$date2), 'ExportAbsensiLembur.xlsx');
    }
}
