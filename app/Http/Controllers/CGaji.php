<?php

namespace App\Http\Controllers;

use App\Models\MapGajiKaryawan;
use App\Models\MapGajiKaryawanPeriode;
use App\Models\MGaji;
use App\Models\MKaryawan;
use App\Models\RefJenisGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Traits\Helper;

class CGaji extends Controller
{
    public $validate = [
            'nama_gaji' => 'required', 
            'id_jenis_gaji' => 'required', 
            'periode_hitung' => 'required', 
    ];
    public function index()
    {
        return view('gaji.index')->with('title','Gaji');
    }
    public function create($title_page = 'Tambah')
    {
        $jenisGaji = RefJenisGaji::withDeleted()->get();
        $url = url('gaji/create-save');
        return view('gaji.form')
            ->with('data',null)
            ->with('title','Gaji')
            ->with('jenis_gaji',$jenisGaji)
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mGaji = new MGaji;
        $mGaji->nama_gaji = $request->nama_gaji;
        $mGaji->periode_hitung = $request->periode_hitung;
        $mGaji->id_jenis_gaji = $request->id_jenis_gaji;
        $mGaji->save();
        $karyawan = MKaryawan::withDeleted()->where('set_gaji',1)->get();
        $id_gaji = $mGaji->id_gaji;
        $nominal = 0;
        $id_periode = Session::get('id_periode');
        foreach($karyawan as $kar){
            // dd($id_gaji);  
            //select id from map_gaji_karyawan where deleted = 1 and id_karyawan = $id_karyawan and id_gaji = $id_gaji
            $mapGajiK = MapGajiKaryawan::withDeleted()->where('id_karyawan',$kar->id_karyawan)->where('id_gaji',$id_gaji)->get()->count();
            // dd($mapGajiK);
            if($mapGajiK == 0){
                $mapGajiKNew = new MapGajiKaryawan;
                $mapGajiKNew->id_karyawan = $kar->id_karyawan;
                $mapGajiKNew->id_gaji = $id_gaji;
                $mapGajiKNew->nominal = $nominal;
                $mapGajiKNew->save();
            }
            $mapGajiKP = MapGajiKaryawanPeriode::withDeleted()
                            ->where('id_karyawan',$kar->id_karyawan)
                            ->where('id_gaji',$id_gaji)
                            ->where('id_periode',$id_periode)
                            ->get()->count();
            if($mapGajiKP == 0){
                $mapGajiKPNew = new MapGajiKaryawanPeriode;
                $mapGajiKPNew->id_karyawan = $kar->id_karyawan;
                $mapGajiKPNew->id_gaji = $id_gaji;
                $mapGajiKPNew->nominal = $nominal;
                $mapGajiKPNew->id_periode = $id_periode;
                $mapGajiKPNew->save();
            }
        }
        
        return redirect()->route('gaji-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MGaji::find($id));
        $jenisGaji = RefJenisGaji::withDeleted()->get();
        
        return view('gaji.form')
            ->with('data',MGaji::find($id))
            ->with('jenis_gaji',$jenisGaji)
            ->with('title','Gaji')
            ->with('titlePage','Edit')
            ->with('url',url('gaji/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mGaji = MGaji::find($id);
        $mGaji->nama_gaji = $request->nama_gaji;
        $mGaji->periode_hitung = $request->periode_hitung;
        $mGaji->id_jenis_gaji = $request->id_jenis_gaji;
        $mGaji->update();
        return redirect()->route('gaji-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MGaji::updateDeleted($id);
        MapGajiKaryawan::where('id_gaji',$id)->update(['deleted'=>0]);
        MapGajiKaryawanPeriode::where('id_gaji',$id)->update(['deleted'=>0]);
        return redirect()->route('gaji-index')->with('msg','Sukses Menghapus Data');

    }
    public function datatable()
    {
        $model = DB::table('m_gaji')->select('m_gaji.*','ref_jenis_gaji.*')
        ->join('ref_jenis_gaji','m_gaji.id_jenis_gaji','=','ref_jenis_gaji.id_jenis_gaji','left')
        ->where('m_gaji.deleted',1)->get();
        // dd($model);
        // foreach($model as $key){    
        //     dd($key->jenis_gaji->nama_jenis_gaji);
        // }
        return DataTables::of($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if($row->id_gaji != 1){
                    if (Helper::can_akses('penggajian_gaji_delete')) {
                        $btn .= '<a href="'.url('gaji/delete/'.$row->id_gaji).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                    }
                }
                if (Helper::can_akses('penggajian_gaji_edit')) {
                    $btn .= '<a href="'.url('gaji/show/'.$row->id_gaji).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->editColumn('periode_hitung',function ($row)
            {
                if($row->periode_hitung == 1){
                    return 'Bulan';
                }elseif($row->periode_hitung == 2){
                    return 'Hari';
                }elseif($row->periode_hitung == 3){
                    return 'Masuk hari libur';
                }
                return '-';
            })
            
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
