<?php

namespace App\Http\Controllers;

use App\Models\MapGajiKaryawan;
use App\Models\MapGajiKaryawanPeriode;
use App\Models\MGaji;
use App\Models\MKaryawan;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CGajiKaryawan extends Controller
{
    use Helper;
    public $validate = [
            'nama_gaji' => 'required', 
            'id_jenis_gaji' => 'required', 
            'periode_hitung' => 'required', 
    ];
    public function index()
    {
        return view('gaji_karyawan.index')->with('title','Gaji Karyawan');
    }
    public function set_gaji($id)
    {
        $karyawan = MKaryawan::where('id_karyawan',$id)->first(['nik','nama_karyawan']);
        
        $url = url('gaji_karyawan/set-gaji-save/'.$id);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal, b.deleted, a.id_jenis_gaji, c.nama_jenis_gaji, IF(a.periode_hitung = 1, 'Per Bulan', IF(a.periode_hitung = 3, 'Per Masuk hari libur', 'Per Hari')) as periode_hitung
				from m_gaji a
                left join ref_jenis_gaji c on a.id_jenis_gaji = c.id_jenis_gaji
				left join map_gaji_karyawan b on a.id_gaji = b.id_gaji and b.id_karyawan = '$id' and b.deleted = 1 where a.deleted = 1");
        return view('gaji_karyawan.form')->with('data',$query)->with('url',$url)->with('title','Gaji Karyawan')->with('karyawan',$karyawan);
    }
    public function set_gaji_save($id,Request $request)
    {
        // dd($request->all());
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
				from m_gaji a 
				left join map_gaji_karyawan b on a.id_gaji = b.id_gaji and b.id_karyawan = '$id' and b.deleted = 1 where a.deleted = 1");
        // dd($query);
        $periode = Session::get("periode");
        $id_periode = Session::get("id_periode");
        $ganti_gaji_periode = $request->ganti_gaji_periode;

        if ($id_periode == null) {
            return redirect()->route('gaji-pegawai-index')->with('msg','Sukses Setting Gaji');
        }

        foreach($query as $key){
            $nominal = $request->input('gaji_'.$key->id_gaji);
            $id_gaji = $key->id_gaji;
        
            $mapGajiK = MapGajiKaryawan::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->first();
            // dd($mapGajiK);
            if($mapGajiK != null){
                $mapGajiK->nominal = str_replace(",",".",$this->replaceNumeric($nominal));
                $mapGajiK->update();
            }else{
                // dd($mapGajiK);
                $mapGajiK = new MapGajiKaryawan;
                $mapGajiK->id_karyawan = $id;
                $mapGajiK->id_gaji = $id_gaji;
                $mapGajiK->nominal =  str_replace(",",".",$this->replaceNumeric($nominal));
                $mapGajiK->save();
            }



            $mapGajiKP = MapGajiKaryawanPeriode::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->where('id_periode',$id_periode)->where('deleted','1')->first();

            if(!is_null($periode)){
                if($mapGajiKP == null){
                    $mapGajiKP = new MapGajiKaryawanPeriode;
                    $mapGajiKP->id_periode = $id_periode;
                    $mapGajiKP->id_karyawan = $id;
                    $mapGajiKP->id_gaji = $id_gaji;
                    $mapGajiKP->nominal =  str_replace(",",".",$this->replaceNumeric($nominal));
                    $mapGajiKP->save();
                }
            }
            
            if($ganti_gaji_periode != null){
                $mapGajiKP->nominal = str_replace(",",".",$this->replaceNumeric($nominal));
                $mapGajiKP->update();
            }

        }
        $karyawan = MKaryawan::find($id);
        $karyawan->set_gaji = 1;
        $karyawan->update();
        return redirect()->route('gaji-pegawai-index')->with('msg','Sukses Setting Gaji');
        
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
                ->where('m_karyawan.aktif',1)
                ->where('m_karyawan.deleted',1);
        // dd($query->get());
        // foreach($query as $key){    
        //     dd($key->jenis_gaji->nama_jenis_gaji);
        // }
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $btn = '';                
                if (Helper::can_akses('penggajian_gaji_karyawan_set_gaji')) {                    
                    $btn .= '<a href="'.url('gaji_karyawan/set-gaji/'.$row->id_karyawan).'" class="text-warning"><span class="mdi mdi-coin"></span></a>';
                }
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
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal, a.id_jenis_gaji, c.nama_jenis_gaji, IF(a.periode_hitung = 1, 'Per Bulan', 'Per Hari') as periode_hitung
				from m_gaji a 
                left join ref_jenis_gaji c on a.id_jenis_gaji = c.id_jenis_gaji
				left join map_gaji_karyawan_periode b on a.id_gaji = b.id_gaji and b.id_periode = '$id_periode' and b.id_karyawan = '$id' and b.deleted = 1 where a.deleted = 1");
        // dd($query);
        return view('gaji_karyawan_periode.form')->with('data',$query)->with('url',$url)->with('title','Gaji Karyawan')->with('karyawan',$karyawan);
    }
    public function set_gaji_save_periode($id,Request $request)
    {
        $id_periode = Session::get('id_periode');
        // dd($request->ganti_gaji_periode);
        $query = DB::select("select a.id_gaji,a.nama_gaji, b.nominal
                from m_gaji a 
                left join map_gaji_karyawan_periode b on a.id_gaji = b.id_gaji and b.id_periode = '$id_periode' and b.id_karyawan = '$id' and b.deleted = 1 where a.deleted = 1");
        // dd($query);
        $periode = Session::get("periode");
        $id_periode = Session::get("id_periode");

        if ($id_periode == null) {
            return redirect()->route('gaji-period-index')->with('msg','Sukses Setting Gaji');
        }
        
        foreach($query as $key){
            $nominal = $request->input('gaji_'.$key->id_gaji);
            $id_gaji = $key->id_gaji;
        
            $mapGajiKP = MapGajiKaryawanPeriode::where('id_gaji',$id_gaji)->where('id_karyawan',$id)->where('id_periode',$id_periode)->where('deleted','1')->first();
            
            if(!is_null($periode)){
                if($mapGajiKP == null){
                    $mapGajiKP = new MapGajiKaryawanPeriode;
                    $mapGajiKP->id_periode = $id_periode;
                    $mapGajiKP->id_karyawan = $id;
                    $mapGajiKP->id_gaji = $id_gaji;
                    $mapGajiKP->nominal =  str_replace(",",".",$this->replaceNumeric($nominal));
                    $mapGajiKP->save();
                }else{
                    $mapGajiKP->nominal = str_replace(",",".",$this->replaceNumeric($nominal));
                    $mapGajiKP->update();
                }
            }


        }

        return redirect()->route('gaji-period-index')->with('msg','Sukses Setting Gaji Periode');
        
    }
    public function datatable_periode()
    {
        $query = DB::table('m_karyawan')
                ->select(
                    'm_karyawan.id_karyawan',
                    'm_karyawan.id_jabatan',
                    'm_karyawan.nik',
                    'm_karyawan.nama_karyawan',
                    'm_jabatan.nama_jabatan',
                )
                ->join('m_jabatan','m_karyawan.id_jabatan','=','m_jabatan.id_jabatan')
                ->join('map_gaji_karyawan_periode','map_gaji_karyawan_periode.id_karyawan','=','m_karyawan.id_karyawan')
                ->where('map_gaji_karyawan_periode.id_periode',Session::get('id_periode'))
                ->where('m_karyawan.deleted',1)
                ->where('m_karyawan.aktif',1)
                ->groupBy('m_karyawan.id_karyawan',
                    'm_karyawan.id_jabatan',
                    'm_karyawan.nik',
                    'm_karyawan.nama_karyawan',
                    'm_jabatan.nama_jabatan');
        // foreach($query as $key){    
        //     dd($key->jenis_gaji->nama_jenis_gaji);
        // }
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('penggajian_gaji_karyawan_periode_set_gaji')) {
                    $btn .= '<a href="'.url('gaji_karyawan/set-gaji-periode/'.$row->id_karyawan).'" class="text-warning"><span class="mdi mdi-coin"></span></a>';
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
            ->rawColumns(['action','periode'])
            ->addIndexColumn()
            ->toJson();
    }
}
