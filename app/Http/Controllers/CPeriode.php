<?php

namespace App\Http\Controllers;

use App\Models\MapGajiKaryawanPeriode;
use App\Models\MPeriode;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CPeriode extends Controller
{
    use Helper;
    public function index()
    {
        return view('periode.index')->with('title','Periode');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('periode/create-save');
        return view('periode.form')
            ->with('data',null)
            ->with('title','Periode')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'tanggal_periode' => 'required', 
        ]);

        $date = explode("-",$request->tanggal_periode);
        $bulan = $date[0];
        $tahun = $date[1];
        // dd($tahun);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $periode = new MPeriode;
        $periode->bulan = $bulan;
        $periode->tahun = $tahun;
        $periode->save();
        $id_periode = $periode->id_periode;
        $id_user = Auth::user()->id_user;
        $query = "INSERT INTO map_gaji_karyawan_periode (id_periode, id_karyawan,id_gaji,nominal, created_by, updated_by) 
                            SELECT {$id_periode} as id_periode ,id_karyawan, id_gaji, nominal, $id_user as created_by, $id_user as updated_by FROM map_gaji_karyawan where deleted = 1";
        DB::select($query);

        return redirect()->route('periode-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('periode.form')
            ->with('data',MPeriode::find($id))
            ->with('title','Periode')
            ->with('titlePage','Edit')
            ->with('url',url('periode/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'tanggal_periode' => 'required', 
        ]);

        $date = explode("-",$request->tanggal_periode);
        $bulan = $date[0];
        $tahun = $date[1];
        // dd($tahun);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mShift = MPeriode::find($id);
        $mShift->bulan = $bulan;
        $mShift->tahun = $tahun;
        $mShift->update();
        
        return redirect()->route('periode-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MPeriode::updateDeleted($id);
        MapGajiKaryawanPeriode::where('id_periode',$id)->update(['deleted'=>'0']);
        return redirect()->route('periode-index')->with('msg','Sukses Menghapus Data');

    }
    public function actived_periode($id_periode)
    {
        MPeriode::query()->update(['aktif'=>0]);
        $periode = MPeriode::find($id_periode);
        $periode->aktif = 1;
        $periode->update();
        $this->setSessionPeriode($periode);
        // MapGajiKaryawanPeriode::updateAllDeleted($id_periode);
        return response()->json(['status'=> true]);
    }
    public function datatable()
    {
        $model = MPeriode::withDeleted()->orderBy('bulan','desc')->orderBy('tahun','desc');
        return DataTables::eloquent($model)
            ->editColumn('bulan', function ($row) {
                return $this->convertBulan($row->bulan);
            })
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('penggajian_periode_delete')) {                    
                    $btn .= '<a href="'.url('periode/delete/'.$row->id_periode).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                // $btn .= '<a href="'.url('periode/show/'.$row->id_periode).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                return $btn;
            })
            ->addColumn('aktif_render', function ($row) {
                $html = "";
                if($row->aktif == 1){
                    $html = '<div class="form-check form-check-flat form-check-success">
                      <label class="form-check-label">
                        <input type="checkbox" value="'.$row->id_periode.'"  class="form-check-input aktif-check" checked>
                      <i class="input-helper"></i></label>
                    </div>';
                }elseif($row->aktif == 0){
                    $html = '<div class="form-check form-check-flat form-check-primary">
                      <label class="form-check-label">
                        <input type="checkbox" value="'.$row->id_periode.'" class="form-check-input aktif-check">
                      <i class="input-helper"></i></label>
                    </div>';
                }
                return $html;
            })
            ->rawColumns(['action','aktif_render'])
            ->addIndexColumn()
            ->toJson();
    }

}
