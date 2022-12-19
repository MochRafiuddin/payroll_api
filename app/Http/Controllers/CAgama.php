<?php

namespace App\Http\Controllers;

use App\Models\MAgama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;  

class CAgama extends Controller
{
    public function index()
    {
        return view('agama.index')->with('title','Agama');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('agama/create-save');
        return view('agama.form')
            ->with('data',null)
            ->with('title','Agama')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_agama' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mAgama = new MAgama;
        $mAgama->nama_agama = $request->nama_agama;
        $mAgama->save();
        return redirect()->route('agama-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('agama.form')
            ->with('data',MAgama::find($id))
            ->with('title','Agama')
            ->with('titlePage','Edit')
            ->with('url',url('agama/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_agama' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mAgama = MAgama::find($id);
        $mAgama->nama_agama = $request->nama_agama;
        $mAgama->update();
        return redirect()->route('agama-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MAgama::updateDeleted($id);
        return redirect()->route('agama-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MAgama::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('referensi_agama_delete')) {
                    $btn .= '<a href="'.url('agama/delete/'.$row->id_agama).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('referensi_agama_edit')) {
                    $btn .= '<a href="'.url('agama/show/'.$row->id_agama).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
