<?php

namespace App\Http\Controllers;

use App\Models\MapGajiKaryawan;
use App\Models\MapGajiKaryawanPeriode;
use App\Models\TGajiKaryawanPeriode;
use App\Models\MGaji;
use App\Models\MPeriode;
use App\Models\MKaryawan;
use App\Traits\Helper;
use App\Traits\HitungPenggajian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Auth;
use Excel;
use App\Exports\ExportLaporanGajiKaryawan;

class CRiwayatGajiKaryawan extends Controller
{
    use Helper;
    use HitungPenggajian;
    public $validate = [
            'nama_gaji' => 'required', 
            'id_jenis_gaji' => 'required', 
            'periode_hitung' => 'required', 
    ];
    public function index()
    {
        $q = (isset($_GET['periode'])) ? $_GET['periode'] : "";
        if ($q) {
            $id_per = $q;
        }else {
            $id_per = Session::get('id_periode');
        }

        $periode = MPeriode::withDeleted()->orderBy('id_periode','DESC')->limit(12)->get();
        return view('riwayat_gaji_karyawan.index')
            ->with('title','Riwayat Gaji Karyawan')
            ->with('periode',$periode)
            ->with('id_per',$id_per);
    }
    public function set_gaji($id)
    {
        $karyawan = MKaryawan::where('id_karyawan',$id)->first(['nik','nama_karyawan']);
        
        $url = url('gaji_karyawan/set-gaji-save/'.$id);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
				from m_gaji a 
				left join map_gaji_karyawan b on a.id_gaji = b.id_gaji and b.id_karyawan = {$id} and b.deleted = 1");
        return view('gaji_karyawan.form')->with('data',$query)->with('url',$url)->with('title','Gaji Karyawan')->with('karyawan',$karyawan);
    }
    public function set_gaji_save($id,Request $request)
    {
        // dd($request->ganti_gaji_periode);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
				from m_gaji a 
				left join map_gaji_karyawan b on a.id_gaji = b.id_gaji and b.id_karyawan = {$id} and b.deleted = 1");
        // dd($query);
        $periode = Session::get("periode");
        $id_periode = Session::get("id_periode");
        $ganti_gaji_periode = $request->ganti_gaji_periode;
        foreach($query as $key){
            $nominal = $request->input('gaji_'.$key->id_gaji);
            $id_gaji = $key->id_gaji;
        
            $mapGajiK = MapGajiKaryawan::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->first();
            if($mapGajiK != null){
                $mapGajiK->nominal = $this->replaceNumeric($nominal);
                $mapGajiK->update();
            }else{
                // dd($mapGajiK);
                $mapGajiK = new MapGajiKaryawan;
                $mapGajiK->id_karyawan = $id;
                $mapGajiK->id_gaji = $id_gaji;
                $mapGajiK->nominal =  $this->replaceNumeric($nominal);
                $mapGajiK->save();
            }



            $mapGajiKP = MapGajiKaryawanPeriode::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->where('id_periode',$id_periode)->first();
            
            if(!is_null($periode)){
                if($ganti_gaji_periode != null){
                    if($mapGajiKP == null){
                        $mapGajiKP = new MapGajiKaryawanPeriode;
                        $mapGajiKP->id_periode = $id_periode;
                        $mapGajiKP->id_karyawan = $id;
                        $mapGajiKP->id_gaji = $id_gaji;
                        $mapGajiKP->nominal =  $this->replaceNumeric($nominal);
                        $mapGajiKP->save();
                    }else{
                        $mapGajiKP->nominal = $this->replaceNumeric($nominal);
                        $mapGajiKP->update();
                    }
                }
            }


        }
        $karyawan = MKaryawan::find($id);
        $karyawan->set_gaji = 1;
        $karyawan->update();
        return redirect(url('gaji_karyawan/view'))->with('msg','Sukses Setting Gaji');
        
    }
    public function datatable()
    {
        $query = DB::table('m_karyawan')
                ->select(
                    'm_karyawan.id_karyawan',
                    'm_karyawan.id_jabatan',
                    'm_karyawan.nik',
                    'm_karyawan.nama_karyawan',
                    'm_jabatan.id_jabatan',
                    'm_jabatan.nama_jabatan',
                )->join('m_jabatan','m_karyawan.id_jabatan','=','m_jabatan.id_jabatan')
                ->where('m_karyawan.deleted',1);
        // dd($query->get());
        // foreach($query as $key){    
        //     dd($key->jenis_gaji->nama_jenis_gaji);
        // }
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $btn = '';                
                
                $btn .= '<a href="'.url('gaji_karyawan/set-gaji/'.$row->id_karyawan).'" class="text-warning"><span class="mdi mdi-coin"></span></a>';
                return $btn;
            })    
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
    public function index_periode()
    {
        return view('gaji_karyawan_periode.index')->with('title','Gaji Karyawan Periode');
    }
    public function set_gaji_periode($id)
    {

        $id_periode = Session::get('id_periode');
        // dd($id_periode);
        $karyawan = MKaryawan::where('id_karyawan',$id)->first(['nik','nama_karyawan']);
        
        $url = url('gaji_karyawan/set-gaji-save-periode/'.$id);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
				from m_gaji a 
				left join map_gaji_karyawan_periode b on a.id_gaji = b.id_gaji and b.id_periode = {$id_periode}  and b.id_karyawan = {$id} and b.deleted = 1");
        // dd($query);
        return view('gaji_karyawan_periode.form')->with('data',$query)->with('url',$url)->with('title','Gaji Karyawan')->with('karyawan',$karyawan);
    }
    public function set_gaji_save_periode($id,Request $request)
    {
        $id_periode = Session::get('id_periode');
        // dd($id_periode);
        // dd($request->ganti_gaji_periode);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
                from m_gaji a 
                left join map_gaji_karyawan_periode b on a.id_gaji = b.id_gaji and b.id_periode = {$id_periode} and b.id_karyawan = {$id} and b.deleted = 1");
        // dd($query);
        $periode = Session::get("periode");
        $id_periode = Session::get("id_periode");
        $ganti_gaji_periode = $request->ganti_gaji_periode;
        foreach($query as $key){
            $nominal = $request->input('gaji_'.$key->id_gaji);
            $id_gaji = $key->id_gaji;
        
            $mapGajiK = MapGajiKaryawan::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->first();
            if($mapGajiK != null){
                $mapGajiK->nominal = $this->replaceNumeric($nominal);
                $mapGajiK->update();
            }else{
                // dd($mapGajiK);
                $mapGajiK = new MapGajiKaryawan;
                $mapGajiK->id_karyawan = $id;
                $mapGajiK->id_gaji = $id_gaji;
                $mapGajiK->nominal =  $this->replaceNumeric($nominal);
                $mapGajiK->save();
            }



            $mapGajiKP = MapGajiKaryawanPeriode::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->where('id_periode',$id_periode)->first();
            
            if(!is_null($periode)){
                if($ganti_gaji_periode != null){
                    if($mapGajiKP == null){
                        $mapGajiKP = new MapGajiKaryawanPeriode;
                        $mapGajiKP->id_periode = $id_periode;
                        $mapGajiKP->id_karyawan = $id;
                        $mapGajiKP->id_gaji = $id_gaji;
                        $mapGajiKP->nominal =  $this->replaceNumeric($nominal);
                        $mapGajiKP->save();
                    }else{
                        $mapGajiKP->nominal = $this->replaceNumeric($nominal);
                        $mapGajiKP->update();
                    }
                }
            }


        }
        $karyawan = MKaryawan::find($id);
        $karyawan->set_gaji = 1;
        $karyawan->update();
        return redirect(url('gaji_karyawan/view'))->with('msg','Sukses Setting Gaji');
        
    }
    public function datatable_riwayat($periode)
    {
        $query = DB::table('m_karyawan as a')
                    ->leftJoin('t_gaji_karyawan_periode as b','a.id_karyawan','=','b.id_karyawan')
                    ->select(
                        'a.nik',
                        'a.nama_karyawan',
                        'b.gaji_bersih',
                        'b.id',
                    )
                    ->where('b.id_periode', $periode)
                    ->where('b.deleted',1);

        return DataTables::of($query)
            ->addColumn('slip_gaji', function ($row) {
                $btn = '';                
                if (Helper::can_akses('riwayat_penggajian_print')) {                    
                    $btn .= '<a href="'.url('invoice/print/'.$row->id).'" class="text-warning mr-2" target="_blank"><span class="mdi mdi-printer"></span></a>';
                    $btn .= '<a href="'.url('gaji_karyawan/detail_riwayat/'.$row->id).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })    
            ->addColumn('periode', function ($row) {
                $html = '';
                $periode_bulan = Session::get('periode_bulan');
                $periode_tahun = Session::get('periode_tahun');
                if($periode_bulan != null){
                    $html = '<div class="badge badge-outline-success badge-pill">'.$this->convertBulan($periode_bulan).' '.$periode_tahun.'</div>';
                }
                return $html;
                
            })  
            ->addColumn('gaji_bersih', function ($row) {
                $html = $this->ribuan($row->gaji_bersih);
                return $html;
                
            })    
            ->rawColumns(['periode','slip_gaji'])
            ->addIndexColumn()
            ->toJson();
    }

    public function calculate_gaji_karyawan(){
        $this->hitung();
        return redirect()->route('riwayat-gaji-index');
    }

    public function detail_gaji_karyawan($id){
        $query = TGajiKaryawanPeriode::from('t_gaji_karyawan_periode as b')
                    ->leftJoin('m_karyawan as a','a.id_karyawan','=','b.id_karyawan')
                    ->select(
                        'a.nik',
                        'a.nama_karyawan',
                        'b.*',                        
                    )
                    ->where('b.id', $id)
                    ->where('b.deleted',1)->first();
        return json_decode($query);
    }

    public function export_gaji_karyawan() 
    {
        $param = [
            'id_periode' => Session::get('id_periode'),
            'periode_bulan' => Session::get('periode_bulan'),
            'periode_tahun' => Session::get('periode_tahun'),
        ];
        return Excel::download(new ExportLaporanGajiKaryawan($param), 'Laporan Gaji Karyawan Periode '.$this->convertBulan(Session::get('periode_bulan')).' '.Session::get('periode_tahun').'.xlsx');
    }
}
