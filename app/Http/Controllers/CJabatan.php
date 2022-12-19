<?php

namespace App\Http\Controllers;

use App\Models\MJabatan;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;
use App\Traits\Helper;  

class CJabatan extends Controller
{
    public function index()
    {
        return view('jabatan.index')->with('title','Jabatan');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('jabatan/create-save');
        return view('jabatan.form')
            ->with('data',null)
            ->with('title','Jabatan')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_jabatan' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        

        $mJabatan = new MJabatan;
        $mJabatan->nama_jabatan = $request->nama_jabatan;
        $mJabatan->save();
        return redirect()->route('jabatan-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('jabatan.form')
            ->with('data',MJabatan::find($id))
            ->with('title','Departement')
            ->with('titlePage','Edit')
            ->with('url',url('jabatan/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_jabatan' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mJabatan = MJabatan::find($id);
        $mJabatan->nama_jabatan = $request->nama_jabatan;
        $mJabatan->update();
        return redirect()->route('jabatan-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MJabatan::updateDeleted($id);
        return redirect()->route('jabatan-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MJabatan::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if ($row->id_jabatan != 2 && $row->id_jabatan != 5 && $row->id_jabatan != 3) {                    
                    if (Helper::can_akses('referensi_jabatan_delete')) {                    
                        $btn .= '<a href="'.url('jabatan/delete/'.$row->id_jabatan).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                    }
                    if (Helper::can_akses('referensi_jabatan_edit')) {
                        $btn .= '<a href="'.url('jabatan/show/'.$row->id_jabatan).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                    }
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
