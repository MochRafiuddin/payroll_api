<?php

namespace App\Http\Controllers;

use App\Models\MShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Validation\Rule;
use App\Traits\Helper;  

class CShift extends Controller
{
    public function index()
    {
        return view('shift.index')->with('title','Shift');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('shift/create-save');
        return view('shift.form')
            ->with('data',null)
            ->with('title','Shift')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(),[
            'nama_shift' => 'required', 
            'kode_shift' => [
                "required",
                Rule::unique('m_shift', 'kode_shift')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    }),
            ],
            'jam_masuk' => 'required', 
            'jam_keluar' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mShift = new MShift;
        $mShift->nama_shift = $request->nama_shift;
        $mShift->kode_shift = $request->kode_shift;
        $mShift->jam_masuk = $request->jam_masuk;
        $mShift->jam_keluar = $request->jam_keluar;
        $mShift->save();
        return redirect()->route('shift-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('shift.form')
            ->with('data',MShift::find($id))
            ->with('title','Shift')
            ->with('titlePage','Edit')
            ->with('url',url('shift/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_shift' => 'required', 
            'kode_shift' => [
                "required",
                Rule::unique('m_shift', 'kode_shift')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    })->ignore($id, 'id_shift'),
            ], 
            'jam_masuk' => 'required', 
            'jam_keluar' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mShift = MShift::find($id);
        $mShift->nama_shift = $request->nama_shift;
        $mShift->kode_shift = $request->kode_shift;
        $mShift->jam_masuk = $request->jam_masuk;
        $mShift->jam_keluar = $request->jam_keluar;
        $mShift->update();
        return redirect()->route('shift-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MShift::updateDeleted($id);
        return redirect()->route('shift-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MShift::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';

                if ($row->id_shift == 1) {
                    $btn = '';
                }else{
                    if (Helper::can_akses('master_shift_delete')) {                        
                        $btn .= '<a href="'.url('shift/delete/'.$row->id_shift).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                    }
                    if (Helper::can_akses('master_shift_edit')) {                        
                        $btn .= '<a href="'.url('shift/show/'.$row->id_shift).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                    }
                }
                
                return $btn;
            })
            ->rawColumns(['action','hari_kerja_render'])
            ->addIndexColumn()
            ->toJson();
    }
}
