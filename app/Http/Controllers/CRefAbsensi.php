<?php

namespace App\Http\Controllers;

use App\Models\RefTipeAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;

class CRefAbsensi extends Controller
{
    public function index()
    {
        return view('ref_tipe_absensi.index')->with('title','Referensi Tipe Absensi');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('ref-tipe-absensi/create-save');
        return view('ref_tipe_absensi.form')
            ->with('data',null)
            ->with('title','Referensi Tipe Absensi')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_tipe_absensi' => 'required', 
            'tipe_batas_waktu' => 'required', 
            'batas_waktu' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mRefTipeAbsensi = new RefTipeAbsensi;
        $mRefTipeAbsensi->nama_tipe_absensi = $request->nama_tipe_absensi;
        $mRefTipeAbsensi->tipe_batas_waktu = $request->tipe_batas_waktu;
        $mRefTipeAbsensi->batas_waktu = $request->batas_waktu;
        $mRefTipeAbsensi->save();
        return redirect()->route('ref-tipe-absensi-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(RefTipeAbsensi::find($id));
        
        return view('ref_tipe_absensi.form')
            ->with('data',RefTipeAbsensi::find($id))
            ->with('title','Referensi Tipe Absensi')
            ->with('titlePage','Edit')
            ->with('url',url('ref-tipe-absensi/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_tipe_absensi' => 'required', 
            'tipe_batas_waktu' => 'required', 
            'batas_waktu' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mRefTipeAbsensi = RefTipeAbsensi::find($id);
        $mRefTipeAbsensi->nama_tipe_absensi = $request->nama_tipe_absensi;
        $mRefTipeAbsensi->tipe_batas_waktu = $request->tipe_batas_waktu;
        $mRefTipeAbsensi->batas_waktu = $request->batas_waktu;
        $mRefTipeAbsensi->update();
        return redirect()->route('ref-tipe-absensi-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        RefTipeAbsensi::updateDeleted($id);
        return redirect()->route('ref-tipe-absensi-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = RefTipeAbsensi::withDeleted()->where('is_show','=',1);
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('absensi_tipe_absensi_delete')) {                    
                    $btn .= '<a href="'.url('ref-tipe-absensi/delete/'.$row->id_tipe_absensi).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('absensi_tipe_absensi_edit')) {                    
                    $btn .= '<a href="'.url('ref-tipe-absensi/show/'.$row->id_tipe_absensi).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->editColumn('batas',function ($row) {
                if ($row->tipe_batas_waktu==1) {
                    $html = "Mulai H - ".$row->batas_waktu;
                }else {
                    $html = "Setelah H + ".$row->batas_waktu;
                }
                return $html;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
