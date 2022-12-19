<?php

namespace App\Http\Controllers;

use App\Models\MGrupKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Validation\Rule;
use App\Traits\Helper;  

class CGrupKaryawan extends Controller
{
    public function index()
    {
        return view('grup_karyawan.index')->with('title','Grup Karyawan');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('grup_karyawan/create-save');
        return view('grup_karyawan.form')
            ->with('data',null)
            ->with('title','Grup Karyawan')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(),[
            'nama_grup' => 'required', 
            'hari_kerja' => 'required', 
            'kode_grup' => [
                "required",
                Rule::unique('m_grup_karyawan', 'kode_grup')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    }),
            ], 
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mGrupKaryawan = new MGrupKaryawan;
        $this->credentials($mGrupKaryawan,$request);
        $mGrupKaryawan->save();
        return redirect()->route('grup-karyawan-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('grup_karyawan.form')
            ->with('data',MGrupKaryawan::find($id))
            ->with('title','Grup Karyawan')
            ->with('titlePage','Edit')
            ->with('url',url('grup_karyawan/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_grup' => 'required', 
            'hari_kerja' => 'required',
            'kode_grup' => [
                "required",
                Rule::unique('m_grup_karyawan', 'kode_grup')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    })->ignore($id, 'id_grup_karyawan'),
            ],
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mGrupKaryawan = MGrupKaryawan::find($id);
        $this->credentials($mGrupKaryawan,$request);
        $mGrupKaryawan->update();
        return redirect()->route('grup-karyawan-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MGrupKaryawan::updateDeleted($id);
        return redirect()->route('grup-karyawan-index')->with('msg','Sukses Menambahkan Data');

    }
    public function credentials($mGrupKaryawan,$request)
    {
        $mGrupKaryawan->nama_grup = $request->nama_grup;
        $mGrupKaryawan->kode_grup = $request->kode_grup;
        $mGrupKaryawan->hari_kerja = $request->hari_kerja;
    }
    public function datatable()
    {
        $model = MGrupKaryawan::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';

                if ($row->id_grup != 1) {
                    if (Helper::can_akses('master_grup_karyawan_delete')) {
                        $btn .= '<a href="'.url('grup_karyawan/delete/'.$row->id_grup_karyawan).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';                        
                    }
                }
                if (Helper::can_akses('master_grup_karyawan_edit')) {                    
                    $btn .= '<a href="'.url('grup_karyawan/show/'.$row->id_grup_karyawan).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
             ->addColumn('hari_kerja_render',function ($row) {
                $html = "";
                if($row->hari_kerja == 1){
                    $html = '<div class="badge badge-outline-primary badge-pill">5 Hari Kerja</div>';
                }elseif($row->hari_kerja == 2){
                    $html = '<div class="badge badge-outline-primary badge-pill">6 Hari Kerja</div>';
                }
                return $html;
            })
            ->rawColumns(['action','hari_kerja_render'])
            ->addIndexColumn()
            ->toJson();
    }
}
