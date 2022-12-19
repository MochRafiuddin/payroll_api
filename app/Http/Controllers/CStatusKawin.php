<?php

namespace App\Http\Controllers;

use App\Models\MStatusKawin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;  

class CStatusKawin extends Controller
{
    public function index()
    {
        return view('status_kawin.index')->with('title','Status Kawin');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('status_kawin/create-save');
        return view('status_kawin.form')
            ->with('data',null)
            ->with('title','Status Kawin')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'kode_status_kawin' => 'required', 
            'nama_status_kawin' => 'required', 
            'nilai_ptkp' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mStatusKawin = new MStatusKawin;
        $mStatusKawin->kode_status_kawin = $request->kode_status_kawin;
        $mStatusKawin->nama_status_kawin = $request->nama_status_kawin;
        $mStatusKawin->nilai_ptkp = str_replace(".","",$request->nilai_ptkp);
        $mStatusKawin->save();
        return redirect()->route('status-kawin-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('status_kawin.form')
            ->with('data',MStatusKawin::find($id))
            ->with('title','Kawin')
            ->with('titlePage','Edit')
            ->with('url',url('status_kawin/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'kode_status_kawin' => 'required', 
            'nama_status_kawin' => 'required', 
            'nilai_ptkp' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mStatusKawin = MStatusKawin::find($id);
        $mStatusKawin->kode_status_kawin = $request->kode_status_kawin;
        $mStatusKawin->nama_status_kawin = $request->nama_status_kawin;
        $mStatusKawin->nilai_ptkp = str_replace(".","",$request->nilai_ptkp);
        $mStatusKawin->update();
        return redirect()->route('status-kawin-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MStatusKawin::updateDeleted($id);
        return redirect()->route('status-kawin-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MStatusKawin::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('master_status_kawin_delete')) {
                    $btn .= '<a href="'.url('status_kawin/delete/'.$row->id_status_kawin).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('master_status_kawin_edit')) {
                    $btn .= '<a href="'.url('status_kawin/show/'.$row->id_status_kawin).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';                    
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
