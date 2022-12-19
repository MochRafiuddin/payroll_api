<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKaryawan;
use App\Models\MShiftKaryawan;
use App\Models\MShift;
use App\Models\MPeriode;
use App\Models\TTotalGajiPeriode;
use App\Models\TReportAbsensi;
use App\Models\TIzinnCuti;
use App\Models\TLembur;
use App\Models\TAbsensi;
use App\Models\LogAbsensi;
use App\Models\MapApprIzin;
use App\Models\MapAnggota;
use App\Models\MRole;
use Session;
use DataTables;
use App\Traits\Helper;
use Carbon\Carbon;

use DB;
use DateInterval;
use DatePeriod;
use DateTime;
use Lang;
use Auth;
class CDashboard extends Controller
{
    use Helper;
    public function index()
    {
        $tanggal = date('m-01-Y').' - '.date('m-d-Y');        
        $karyawan = MKaryawan::withDeleted()->count();
        $tahun_periode = Session::get('periode_tahun');
        // $Izin = TReportAbsensi::where('bulan',date('m'))->where('tahun',date('Y'))->where('deleted',1)->where('id_tipe_absensi','>',3)->sum('jumlah_hari');
        $Izin = TReportAbsensi::join('ref_tipe_absensi','ref_tipe_absensi.id_tipe_absensi','=','t_report_absensi.id_tipe_absensi')                
                ->selectRaw('sum(t_report_absensi.jumlah_hari) as total,t_report_absensi.id_tipe_absensi,ref_tipe_absensi.nama_tipe_absensi')
                ->where('t_report_absensi.deleted',1)
                ->where('ref_tipe_absensi.is_show',1)
                ->where('bulan',date('m'))->where('tahun',date('Y'))
                ->groupBy('t_report_absensi.id_tipe_absensi')
                ->get();
        
        $TMasuk = TReportAbsensi::where('bulan',date('m'))->where('tahun',date('Y'))->where('deleted',1)->where('id_tipe_absensi',2)->sum('jumlah_hari');
        $periode = MPeriode::withDeleted()->whereIn('bulan', ['1','2','3','4','5','6','7','8','9','10','11','12'])->where('tahun', $tahun_periode)->pluck('id_periode')->toArray();
        // if ($id_periode) {
            $gaji = TTotalGajiPeriode::withDeleted()->whereIn('id_periode',$periode)->get()->sum('nominal');  
            $gaji = $gaji ?? 0;
            // dd($gaji);
        // }else {
        //     $gaji=0;
        // }

        return view("dashboard.index")
                ->with('karyawan',$karyawan)
                ->with('gaji',$gaji)
                ->with('tanggal',$tanggal)
                ->with('izin',$Izin)
                ->with('tmasuk',$TMasuk)
                ->with('title','Dashboard');
    }

    public function chart(){
        $q = (isset($_GET['tgl'])) ? $_GET['tgl'] : "";
        if ($q==2) {
            $awal=date('Y-m-11',strtotime('-1 month',strtotime(date('Y-m-d'))));
            $akhir=date('Y-m-10');

            $dric_title=[];
            $dric=[];
            $key=0;
            while ($awal <= $akhir) {
                $dric_title[$key]=date('d-M',strtotime($awal));
                $dric['tidak_masuk'][$key]=$this->chart_tidak_masuk($awal);
                $dric['izin'][$key]=$this->chart_izin($awal);
                $key++;
                $awal = date('Y-m-d',strtotime('+1 days',strtotime($awal)));
            }
        }else{
            $awal=date('Y-m-01');
            $akhir=date('Y-m-t');
            $dric_title=[];
            $dric=[];
            $key=0;
            while ($awal <= $akhir) {
                $dric_title[$key]=date('d-M',strtotime($awal));
                $dric['tidak_masuk'][$key]=$this->chart_tidak_masuk($awal);
                $dric['izin'][$key]=$this->chart_izin($awal);
                $key++;
                $awal = date('Y-m-d',strtotime('+1 days',strtotime($awal)));
            }   
        }
        // dd($dric_title,$dric);
        return response()->json(['status'=>true,'dric'=>$dric,'dric_title'=>$dric_title]);
    }
    public function chart_gaji(){                    
            $dric_title=[];
            $dric=[];
            $month = [];            
            $key=0;
            for ($m=1; $m<=12; $m++) {
                $dric_title[$key]=date('F', mktime(0,0,0,$m, 1, date('Y')));
                $dric['tgaji'][$key]=$this->chart_tgaji($m);       
                $key++;
            }

        return response()->json(['status'=>true,'dric'=>$dric,'dric_title'=>$dric_title]);
    }

    public function chart_tgaji($bulan){
        $tahun_periode = Session::get('periode_tahun');    
        $periode = MPeriode::withDeleted()->where('bulan',$bulan)->where('tahun', $tahun_periode)->pluck('id_periode')->toArray();
        if ($periode) {            
            $gaji = TTotalGajiPeriode::withDeleted()->whereIn('id_periode',$periode)->get()->sum('nominal');  
            $no = $gaji ?? 0;            
        }else {
            $no = 0;            
        }
        return $no;        
    }

    public function chart_tidak_masuk($tgl){
        $chart = DB::table('t_report_absensi as a')
            ->join('t_report_absensi_det as b','a.id_report_absensi','b.id_report_absensi')
            ->selectRaw('b.tanggal as tgl ,count(b.id) as hsl')
            ->where('a.deleted',1)
            ->where('b.deleted',1)
            ->where('a.id_tipe_absensi',2)
            ->where('b.tanggal',$tgl)
            ->groupBy('b.tanggal')
            ->first();
            if ($chart) {
                $no=$chart->hsl;            
            }else{
                $no=0;
            }
        return $no;
    }

    public function chart_izin($tgl){
        $chart = DB::table('t_report_absensi as a')
            ->join('t_report_absensi_det as b','a.id_report_absensi','b.id_report_absensi')
            ->selectRaw('b.tanggal as tgl ,count(b.id) as hsl')
            ->where('a.deleted',1)
            ->where('b.deleted',1)
            ->where('a.id_tipe_absensi','>',3)
            ->where('b.tanggal',$tgl)
            ->groupBy('b.tanggal')
            ->first();
            if ($chart) {
                $no=$chart->hsl;            
            }else{
                $no=0;
            }
        return $no;
    }

    public function datatable_izcu(Request $request)
    {
        $arr_approval = [];
        if ($request->status_menunggu) {
            $arr_approval[] = '0';
        }
        if ($request->status_disetujui) {
            $arr_approval[] = '1';
        }
        if ($request->status_ditolak) {
            $arr_approval[] = '2';
        }

        $start = Carbon::createFromFormat('m-d-Y',$request->awal)->format('Y-m-d');
        $end = Carbon::createFromFormat('m-d-Y',$request->akhir)->format('Y-m-d');
        $departemen = MKaryawan::find(Auth::user()->id_karyawan);
        $rol = MRole::find(Auth::user()->id_role);

        $model = TIzinnCuti::select("t_izin.*",'m_karyawan.nama_karyawan','ref_tipe_absensi.nama_tipe_absensi')
        ->join('m_karyawan','m_karyawan.id_karyawan','=','t_izin.id_karyawan','left')
        ->join('ref_tipe_absensi','ref_tipe_absensi.id_tipe_absensi','=','t_izin.id_tipe_absensi','left')
        ->whereBetween('t_izin.tanggal_mulai', [$start,$end])
        ->where('t_izin.deleted',1);
        // ->whereMonth('t_izin.tanggal_mulai',Session::get('periode_bulan'))
        // ->whereYear('t_izin.tanggal_mulai',Session::get('periode_tahun'))

        if (count($arr_approval) > 0) {
            $model->whereIn('t_izin.approval',$arr_approval);
        }
        if ($rol->kode_role == "leader") {
            $anggota = MapAnggota::where('id_submitter',Auth::user()->id_karyawan)->pluck('id_karyawan')->toArray();
            $sendiri = Auth::user()->id_karyawan;
            array_push($anggota, $sendiri);                
            $model = $model->whereIn('m_karyawan.id_karyawan',$anggota);
        }
        if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {                
            $model = $model->where('m_karyawan.id_departemen',$departemen->id_departemen);
        }
        
        return DataTables::of($model)
            ->addColumn('approval',function ($row) {
                return $this->cek_appr($row->id_izin);
            })
            ->editColumn('tanggal_mulai',function ($row) {
                $html = $this->convertDate($row->tanggal_mulai,true,false);
                return $html;
            })
            ->editColumn('tanggal_selesai',function ($row) {
                $html = $this->convertDate($row->tanggal_selesai,true,false);
                return $html;
            })
            ->rawColumns(['approval'])
            ->addIndexColumn()
            ->toJson();
    }

    public function datatable_lembur(Request $request)
    {
        $arr_approval = [];
        if ($request->status_menunggu) {
            $arr_approval[] = '0';
        }
        if ($request->status_disetujui) {
            $arr_approval[] = '1';
        }
        if ($request->status_ditolak) {
            $arr_approval[] = '2';
        }

        $start = Carbon::createFromFormat('m-d-Y',$request->awal)->format('Y-m-d');
        $end = Carbon::createFromFormat('m-d-Y',$request->akhir)->format('Y-m-d');
        $departemen = MKaryawan::find(Auth::user()->id_karyawan);
        $rol = MRole::find(Auth::user()->id_role);

        $model = TLembur::from('t_lembur')
                ->join('m_karyawan','m_karyawan.id_karyawan','=','t_lembur.id_karyawan','left')
                ->selectRaw('sum(jumlah_jam) as total_jam,m_karyawan.nama_karyawan,t_lembur.tanggal,approval')
                ->where('t_lembur.deleted',1)
                ->groupBy('m_karyawan.nama_karyawan','t_lembur.tanggal')
                ->whereBetween('t_lembur.tanggal', [$start,$end]);
                // ->orderBy('t_lembur.tanggal', 'DESC'); 
                // ->whereMonth('t_lembur.tanggal',Session::get('periode_bulan'))
                // ->whereYear('t_lembur.tanggal',Session::get('periode_tahun'))

        if (count($arr_approval) > 0) {
            $model->whereIn('t_lembur.approval',$arr_approval);
        }

        if ($rol->kode_role == "leader") {
            $anggota = MapAnggota::where('id_submitter',Auth::user()->id_karyawan)->pluck('id_karyawan')->toArray();
            $sendiri = Auth::user()->id_karyawan;
            array_push($anggota, $sendiri);                
            $model = $model->whereIn('m_karyawan.id_karyawan',$anggota);
        }

        if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {                
            $model = $model->where('m_karyawan.id_departemen',$departemen->id_departemen);
        }
        
        return DataTables::of($model)
            ->editColumn('approval',function ($row) {
                $html = "";
                $html .= $this->approval1($row->approval,1);
                $html .= $this->approval1($row->approval2,2);
                $html .= $this->approval1($row->approval3,3);
                return $html;
            })
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->rawColumns(['approval','tanggal_lembur'])
            ->addIndexColumn()
            ->toJson();
    }

    public function datatable_tidak_masuk(Request $request)
    {
        $start = Carbon::createFromFormat('m-d-Y',$request->awal)->format('Y-m-d');
        $end = Carbon::createFromFormat('m-d-Y',$request->akhir)->format('Y-m-d');

        $model = TReportAbsensi::from('t_report_absensi as a')
                ->leftJoin('t_report_absensi_det as b','a.id_report_absensi','=','b.id_report_absensi')
                ->leftJoin('m_karyawan as c','a.id_karyawan','=','c.id_karyawan')
                ->select('c.nik','c.nama_karyawan','b.tanggal')
                ->where('a.id_tipe_absensi',2)
                ->where('a.deleted',1)
                ->where('b.deleted',1)
                ->whereBetween('b.tanggal', [$start,$end]);
                // ->orderBy('b.tanggal','desc');
                // ->whereMonth('b.tanggal',Session::get('periode_bulan'))
                // ->whereYear('b.tanggal',Session::get('periode_tahun'))

        
        return DataTables::of($model)
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->addIndexColumn()
            ->toJson();
    }

    public function grafik_absensi(Request $request)
    {
        $tanggal = $request->tanggal;
        // $tanggal = "2022-06-02";

        $data_shift = MShiftKaryawan::from('m_shift_karyawan as a')
                        ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                        ->select('a.id_karyawan','a.id_shift','b.nama_shift')
                        ->where('a.deleted','1')->where('a.tanggal',$tanggal)->where('a.id_shift','<>','1')
                        ->orderBy('a.id_shift','asc')->get()->toArray();

        $karyawan_by_shift = [];

        foreach ($data_shift as $key) {
            $id_karyawan = $key['id_karyawan'];
            $id_shift = $key['id_shift'];
            $nama_shift = $key['nama_shift'];

            if(!array_key_exists($id_shift, $karyawan_by_shift)){
                $karyawan_by_shift[$id_shift]['id_shift'] = $id_shift;
                $karyawan_by_shift[$id_shift]['nama_shift'] = ucwords($nama_shift);
                $karyawan_by_shift[$id_shift]['id_karyawan'] = [];
            }
            array_push($karyawan_by_shift[$id_shift]['id_karyawan'], $id_karyawan);
        }

        foreach ($karyawan_by_shift as $key) {
            $data_log = LogAbsensi::select('waktu_masuk','waktu_keluar','nama_karyawan')->where('tanggal_shift',$tanggal)->where('id_shift',$key['id_shift'])->get()->toArray();

            $hasil_log = $this->hitung_masuk_keluar($data_log);
            $karyawan_by_shift[$key['id_shift']]['masuk'] = $hasil_log['masuk'];
            $karyawan_by_shift[$key['id_shift']]['pulang'] = $hasil_log['pulang'];
            $karyawan_by_shift[$key['id_shift']]['total_karyawan'] = count($karyawan_by_shift[$key['id_shift']]['id_karyawan']);
        }

        $data = $karyawan_by_shift;
        
        return response()->json(['status'=>true,'data'=>$data]);
    }

    function hitung_masuk_keluar($data){

        $ret['masuk'] = 0;
        $ret['pulang'] = 0;

        foreach ($data as $key) {
            if($key['waktu_masuk'] != null && $key['waktu_masuk'] != ""){
                $ret['masuk']++;
            }
            if($key['waktu_keluar'] != null && $key['waktu_keluar'] != ""){
                $ret['pulang']++;
            }
        }

        return $ret;

    }

    function cari_data_absensi($array,$id_karyawan){

        foreach ($array as $key => $value) {
            if ($value->id_karyawan == $id_karyawan) {
                return $value;
            }
        }
        return false;

    }

    function get_detail_list_pershift(Request $request){ 
        $id_shift = $request->id_shift;
        $tanggal = $request->tanggal;
        $list = $request->list;
        $kategori = $request->kategori;

        $data_karyawan_by_shift = MShiftKaryawan::from('m_shift_karyawan as a')
                                ->leftJoin('m_karyawan as b','a.id_karyawan','=','b.id_karyawan')
                                ->where('a.id_shift',$id_shift)->where('a.tanggal',$tanggal)->where('a.deleted','1')->select('b.nama_karyawan','a.id_karyawan')->get()->toArray();
        $arr_id_karyawan = [];
        foreach ($data_karyawan_by_shift as $key => $value) {
            $arr_id_karyawan[] = $value['id_karyawan'];
        }
        $data_absensi = LogAbsensi::whereIn('id_karyawan',$arr_id_karyawan)->where('tanggal_shift',$tanggal)->where('id_shift',$id_shift)->get();

        $data_karyawan = [];
        foreach ($data_karyawan_by_shift as $karyawan) {
            $absen = $this->cari_data_absensi($data_absensi,$karyawan['id_karyawan']);
            if ($absen) {
                $data_karyawan[] = [
                    'id_karyawan' => $karyawan['id_karyawan'],
                    'nama_karyawan' => $karyawan['nama_karyawan'],
                    'waktu_masuk' => $absen->waktu_masuk,
                    'waktu_keluar' => $absen->waktu_keluar,
                ];
            }else{
                $data_karyawan[] = [
                    'id_karyawan' => $karyawan['id_karyawan'],
                    'nama_karyawan' => $karyawan['nama_karyawan'],
                    'waktu_masuk' => null,
                    'waktu_keluar' => null,
                ];
            }
        }
        
        $res_data = [];
        $num = 1;
        if ($kategori == 'belum_masuk') {
            foreach ($data_karyawan as $key => $data) {
                if ($data['waktu_masuk'] == null || $data['waktu_masuk'] == "") {
                    $res_data[] = [
                        'no' => $num++,
                        'nama_karyawan' => ucwords($data['nama_karyawan']),
                    ];
                }
            }
        }elseif ($kategori == 'sudah_masuk') {
            foreach ($data_karyawan as $key => $data) {
                if ($data['waktu_masuk'] != null && $data['waktu_masuk'] != "") {
                    $res_data[] = [
                        'no' => $num++,
                        'nama_karyawan' => ucwords($data['nama_karyawan']),
                    ];
                }
            }
        }elseif ($kategori == 'belum_pulang') {
            foreach ($data_karyawan as $key => $data) {
                if ($data['waktu_keluar'] == null || $data['waktu_keluar'] == "") {
                    $res_data[] = [
                        'no' => $num++,
                        'nama_karyawan' => ucwords($data['nama_karyawan']),
                    ];
                }
            }
        }elseif ($kategori == 'sudah_pulang') {
            foreach ($data_karyawan as $key => $data) {
                if ($data['waktu_keluar'] != null && $data['waktu_keluar'] != "") {
                    $res_data[] = [
                        'no' => $num++,
                        'nama_karyawan' => ucwords($data['nama_karyawan']),
                    ];
                }
            }
        }

        return response()->json(['status'=>true,'data'=>$res_data]);
    }

    public function karyawan()
    {   
        $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
        $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');
        $bulan = $this->convertBulan(Session::get('periode_bulan'));
        $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = $this->data_gaji_karyawan($id_karyawan);
        $gaji_per_jam = $data_gaji[0]->nominal / 173;
        $gaji_per_hari = $data_gaji[0]->nominal / 22;

        $tidak_masuk = TReportAbsensi::selectRaw("t_report_absensi_det.tanggal, {$gaji_per_hari} as denda")
                ->join('t_report_absensi_det','t_report_absensi_det.id_report_absensi','=','t_report_absensi.id_report_absensi','left')
                ->where('t_report_absensi.deleted',1)
                ->where('t_report_absensi.id_tipe_absensi',2)                
                ->where('t_report_absensi.id_karyawan',$id_karyawan)
                ->whereBetween('t_report_absensi_det.tanggal', [$tanggal_start,$tanggal_end])                
                ->get(); 
        
        $lembur = TLembur::selectRaw("sum(jumlah_jam) as total_jam, sum(index_tarif * jumlah_jam * {$gaji_per_jam}) as total")
                ->where('deleted',1)
                ->where('approval',1)
                ->where('approval2',1)
                ->where('approval3',1)
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('t_lembur.tanggal', [$tanggal_start,$tanggal_end])
                ->get(); 

        $terlambat = TAbsensi::selectRaw("(floor(menit_terlambat / 30)/2) as jam_terlambat,((floor(menit_terlambat / 30)/2) * {$gaji_per_jam}) as denda")
                ->where('deleted',1)
                ->where('menit_terlambat','>=',30) 
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('tanggal', [$tanggal_start,$tanggal_end])->get(); 

        $early = TAbsensi::selectRaw("(floor(menit_early_leave / 30)/2) as jam_early_leave,((floor(menit_early_leave / 30)/2) * {$gaji_per_jam}) as denda")
                ->where('deleted',1)
                ->where('menit_early_leave','>=',30) 
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('tanggal', [$tanggal_start,$tanggal_end])                
                ->get(); 
        // dd($terlambat->sum('denda'));
        return view("dashboard.karyawan")
                ->with('bulan',$bulan)
                ->with('jam_lembur',$lembur->sum('total_jam'))
                ->with('nominal_lembur',$lembur->sum('total'))
                ->with('jam_terlambat',$terlambat->sum('jam_terlambat'))
                ->with('nominal_terlambat',$terlambat->sum('denda'))
                ->with('jam_early_leave',$early->sum('jam_early_leave'))
                ->with('nominal_early_leave',$early->sum('denda'))                
                ->with('nominal_tidak_masuk',$tidak_masuk->sum('denda'))
                ->with('title','Dahsboard');
    }

    public function data_gaji_karyawan($id_karyawan)
    {   
        $id_periode = Session::get('id_periode');     
        // $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = DB::select("select a.id_periode,a.id_karyawan,a.id_gaji,a.nominal, b.id_jenis_gaji, b.periode_hitung, b.nama_gaji
        from map_gaji_karyawan_periode a, m_gaji b 
        where a.id_gaji = b.id_gaji and a.id_gaji = 1 and a.deleted = 1 and a.id_periode = {$id_periode} and a.id_karyawan = {$id_karyawan}");
        
        return $data_gaji;
    }

    public function datatable_lembur_karyawan(Request $request)
    {   
        $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
        $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');

        $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = $this->data_gaji_karyawan($id_karyawan);

        $gaji_per_jam = $data_gaji[0]->nominal / 173;     
        $model = TLembur::selectRaw("sum(jumlah_jam) as total_jam, sum(index_tarif * jumlah_jam * {$gaji_per_jam}) as total, tanggal")
                ->where('deleted',1)
                ->where('approval',1)
                ->where('approval2',1)
                ->where('approval3',1)
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('t_lembur.tanggal', [$tanggal_start,$tanggal_end])
                ->groupBy('tanggal');
                // ->orderBy('tanggal', 'DESC'); 
                // ->whereMonth('t_lembur.tanggal',Session::get('periode_bulan'))
                // ->whereYear('t_lembur.tanggal',Session::get('periode_tahun'))
        // dd($model->get());
        return DataTables::of($model)
            ->editColumn('jam_lembur',function ($row) {                
                $html = $row->total_jam.' Jam';
                return $html;
            })
            ->editColumn('nominal',function ($row) {                            
                $html = "Rp.".$this->ribuan($row->total);              
                return $html;
            })
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->rawColumns(['jam','nominal'])
            ->addIndexColumn()
            ->toJson();
    }
    public function datatable_terlambat_karyawan(Request $request)
    {   
        $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
        $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');

        $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = $this->data_gaji_karyawan($id_karyawan);

        $gaji_per_jam = $data_gaji[0]->nominal / 173;     
        $model = TAbsensi::selectRaw("tanggal,id_shift,tanggal_masuk,tanggal_keluar,menit_terlambat,(floor(menit_terlambat / 30)/2) as jam_terlambat,((floor(menit_terlambat / 30)/2) * {$gaji_per_jam}) as denda")
                ->where('deleted',1)
                ->where('menit_terlambat','>=',30) 
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('tanggal', [$tanggal_start,$tanggal_end]);
                // ->orderBy('tanggal', 'DESC'); 
                // ->whereMonth('t_lembur.tanggal',Session::get('periode_bulan'))
                // ->whereYear('t_lembur.tanggal',Session::get('periode_tahun'))
        // dd($model->get());
        return DataTables::of($model)
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->addColumn('shift',function ($row) {                
                return $this->data_shift($row->id_shift);
            })
            ->addColumn('absensi',function ($row) {
                $masuk=new DateTime($row->tanggal_masuk);
                $keluar=new DateTime($row->tanggal_keluar);
                $html = "<p style='white-space:nowrap;'>".$masuk->format('H:i:s')." s/d ".$keluar->format('H:i:s')."</p>";
                return $html;
            })
            ->editColumn('jam_early_leave',function ($row) {                
                $html = $row->jam_terlambat.' Jam';
                return $html;
            })
            ->editColumn('nominal',function ($row) {                            
                $html = "Rp.".$this->ribuan($row->denda);              
                return $html;
            })
            ->rawColumns(['absensi','shift'])
            ->addIndexColumn()
            ->toJson();
    }

    public function datatable_early_karyawan(Request $request)
    {   
        $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
        $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');
        
        $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = $this->data_gaji_karyawan($id_karyawan);

        $gaji_per_jam = $data_gaji[0]->nominal / 173;     
        $model = TAbsensi::selectRaw("tanggal,id_shift,tanggal_masuk,tanggal_keluar,menit_early_leave,(floor(menit_early_leave / 30)/2) as jam_early_leave,((floor(menit_early_leave / 30)/2) * {$gaji_per_jam}) as denda")
                ->where('deleted',1)
                ->where('menit_early_leave','>=',30) 
                ->where('id_karyawan',$id_karyawan)
                ->whereBetween('tanggal', [$tanggal_start,$tanggal_end]);
                // ->orderBy('tanggal', 'DESC'); 
                // ->whereMonth('t_lembur.tanggal',Session::get('periode_bulan'))
                // ->whereYear('t_lembur.tanggal',Session::get('periode_tahun'))
        // dd($model->get());
        return DataTables::of($model)
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->addColumn('shift',function ($row) {                
                return $this->data_shift($row->id_shift);
            })
            ->addColumn('absensi',function ($row) {
                $masuk=new DateTime($row->tanggal_masuk);
                $keluar=new DateTime($row->tanggal_keluar);
                $html = "<p style='white-space:nowrap;'>".$masuk->format('H:i:s')." s/d ".$keluar->format('H:i:s')."</p>";
                return $html;
            })
            ->editColumn('jam_terlambat',function ($row) {                
                $html = $row->jam_early_leave.' Jam';
                return $html;
            })
            ->editColumn('nominal',function ($row) {                            
                $html = "Rp.".$this->ribuan($row->denda);              
                return $html;
            })
            ->rawColumns(['absensi','shift'])
            ->addIndexColumn()
            ->toJson();
    }

    public function datatable_tidak_masuk_karyawan(Request $request)
    {   
        $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
        $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');
        
        $id_karyawan = Auth::user()->id_karyawan;
        $data_gaji = $this->data_gaji_karyawan($id_karyawan);

        $gaji_per_hari = $data_gaji[0]->nominal / 22;     
        $model = TReportAbsensi::selectRaw("t_report_absensi_det.tanggal, {$gaji_per_hari} as denda")
                ->join('t_report_absensi_det','t_report_absensi_det.id_report_absensi','=','t_report_absensi.id_report_absensi','left')
                ->where('t_report_absensi.deleted',1)
                ->where('t_report_absensi.id_tipe_absensi',2)                
                ->where('t_report_absensi.id_karyawan',$id_karyawan)
                ->whereBetween('t_report_absensi_det.tanggal', [$tanggal_start,$tanggal_end]);
                // ->orderBy('t_report_absensi_det.tanggal', 'DESC'); 
                // ->whereMonth('t_lembur.tanggal',Session::get('periode_bulan'))
                // ->whereYear('t_lembur.tanggal',Session::get('periode_tahun'))
        // dd($model->get());
        return DataTables::of($model)
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })            
            ->editColumn('nominal',function ($row) {                            
                $html = "Rp.".$this->ribuan($row->denda);              
                return $html;
            })            
            ->addIndexColumn()
            ->toJson();
    }

    public function data_shift($id_shift)
    {
        $shif_id = MShift::withDeleted()->where('id_shift',$id_shift)->first();
        // dd($shif_id);
        $html = '<p style="white-space:nowrap;">'.$shif_id->jam_masuk.' s/d '.$shif_id->jam_keluar.'</p>';
        return $html;
    }

    public function cek_appr($id)
    {   
        $html="";
        $map=MapApprIzin::where('id_izin',$id)->where('urutan','!=',0)->get();
        foreach ($map as $key) {
            $role = MRole::where('id_role',$key->id_role)->first();
            if ($key->approval==0) {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></p>';
            }elseif ($key->approval==1) {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-success">'.Lang::get('umum.setuju').'</span></p>';
            }else {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-danger">'.Lang::get('umum.tolak').'</span></p>';
            }
        }
        return $html;
    }
    public function approval1($app,$no){
        if ($no==1) {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }elseif ($no==2) {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }else {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }
        return $html;
    }
}
