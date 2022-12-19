<?php

namespace App\Http\Controllers;

use App\Models\MTarifLembur;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
class CTarifLembur extends Controller
{
    use Helper;
    
    public $validate = [
            'jam_ke' => 'required', 
            'rate_hari_kerja' => 'required', 
            'rate_hari_libur_1' => 'required', 
            'rate_hari_libur_2' => 'required', 
    ];
    public function index()
    {
        return view('tarif_lembur.index')->with('title','Tarif Lembur');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('tarif_lembur/create-save');
        return view('tarif_lembur.form')
            ->with('data',null)
            ->with('title','Tarif Lembur')
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

        $tarifLembur = new MTarifLembur;
        $this->credentials($tarifLembur,$request);
        $tarifLembur->save();
        return redirect()->route('tarif-lembur-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('tarif_lembur.form')
            ->with('data',MTarifLembur::find($id))
            ->with('title','Kawin')
            ->with('titlePage','Edit')
            ->with('url',url('tarif_lembur/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $tarifLembur = MTarifLembur::find($id);
        $this->credentials($tarifLembur,$request);
        $tarifLembur->update();
        return redirect()->route('tarif-lembur-index')->with('msg','Sukses Mengubah Data');

    }
    public function credentials($tarifLembur,$request)
    {
        $tarifLembur->jam_ke = $request->jam_ke;
        $tarifLembur->rate_hari_kerja = floatval(str_replace(',', '.', str_replace('.', '', $request->rate_hari_kerja)));
        $tarifLembur->rate_hari_libur_1 = floatval(str_replace(',', '.', str_replace('.', '', $request->rate_hari_libur_1)));
        $tarifLembur->rate_hari_libur_2 = floatval(str_replace(',', '.', str_replace('.', '', $request->rate_hari_libur_2)));
        $tarifLembur->index_hari_libur_pendek = floatval(str_replace(',', '.', str_replace('.', '', $request->index_hari_libur_pendek)));
    }
    public function delete($id)
    {
        MTarifLembur::updateDeleted($id);
        return redirect()->route('tarif-lembur-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MTarifLembur::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('konfigurasi_tarif_lembur_delete')) {                    
                    $btn .= '<a href="'.url('tarif_lembur/delete/'.$row->id_tarif_lembur).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('konfigurasi_tarif_lembur_edit')) {
                    $btn .= '<a href="'.url('tarif_lembur/show/'.$row->id_tarif_lembur).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
