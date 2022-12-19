<?php

namespace App\Http\Controllers;

use App\Models\MStatusKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;  
class CStatusKaryawan extends Controller
{
    public function index()
    {
        return view('status_karyawan.index')->with('title','Status Karyawan');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('status_karyawan/create-save');
        return view('status_karyawan.form')
            ->with('data',null)
            ->with('title','Status Karyawan')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_status_karyawan' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mStatusKaryawan = new MStatusKaryawan;
        $mStatusKaryawan->nama_status_karyawan = $request->nama_status_karyawan;
        $mStatusKaryawan->save();
        return redirect()->route('status-karyawan-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('status_karyawan.form')
            ->with('data',MStatusKaryawan::find($id))
            ->with('title','Shift')
            ->with('titlePage','Edit')
            ->with('url',url('status_karyawan/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_status_karyawan' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mStatusKaryawan = MStatusKaryawan::find($id);
        $mStatusKaryawan->nama_status_karyawan = $request->nama_status_karyawan;
        $mStatusKaryawan->update();
        return redirect()->route('status-karyawan-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MStatusKaryawan::updateDeleted($id);
        return redirect()->route('status-karyawan-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MStatusKaryawan::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('referensi_status_karyawan_delete')) {
                    $btn .= '<a href="'.url('status_karyawan/delete/'.$row->id_status_karyawan).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('referensi_status_karyawan_edit')) {
                    $btn .= '<a href="'.url('status_karyawan/show/'.$row->id_status_karyawan).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
