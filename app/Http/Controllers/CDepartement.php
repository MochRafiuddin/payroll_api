<?php

namespace App\Http\Controllers;

use App\Models\MDepartement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;  

class CDepartement extends Controller
{
    public function index()
    {
        return view('departement.index')->with('title','Departement');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('departement/create-save');
        return view('departement.form')
            ->with('data',null)
            ->with('title','Departement')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_departemen' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mDepartement = new MDepartement;
        $mDepartement->nama_departemen = $request->nama_departemen;
        $mDepartement->save();
        return redirect()->route('departement-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('departement.form')
            ->with('data',MDepartement::find($id))
            ->with('title','Departement')
            ->with('titlePage','Edit')
            ->with('url',url('departement/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_departemen' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mDepartement = MDepartement::find($id);
        $mDepartement->nama_departemen = $request->nama_departemen;
        $mDepartement->update();
        return redirect()->route('departement-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MDepartement::updateDeleted($id);
        return redirect()->route('departement-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MDepartement::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('referensi_departement_delete')) {                    
                    $btn .= '<a href="'.url('departement/delete/'.$row->id_departemen).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('referensi_departement_edit')) {
                    $btn .= '<a href="'.url('departement/show/'.$row->id_departemen).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';                    
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
