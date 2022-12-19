<?php

namespace App\Http\Controllers;

use App\Models\MTarifPph;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;

class CTarifPPH extends Controller
{
    use Helper;
    public $validate = [
            'batas_bawah' => 'required', 
            'batas_atas' => 'required', 
            'tarif' => 'required', 
    ];
    public function index()
    {
        return view('tarif_pph.index')->with('title','Tarif PPH');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('tarif_pph/create-save');
        return view('tarif_pph.form')
            ->with('data',null)
            ->with('title','Tarif PPH')
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

        $mTarifPPH = new MTarifPph;
        $mTarifPPH->batas_atas = $this->replaceNumeric($request->batas_atas);
        $mTarifPPH->batas_bawah = $this->replaceNumeric($request->batas_bawah);
        $mTarifPPH->tarif = $request->tarif;
        $mTarifPPH->save();
        return redirect()->route('tarif-pph-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        $data = MTarifPph::find($id);
        
        return view('tarif_pph.form')
            ->with('data',$data)
            ->with('title','Tarif PPH')
            ->with('titlePage','Edit')
            ->with('url',url('tarif_pph/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mTarifPPH = MTarifPph::find($id);
        $mTarifPPH->batas_atas = $this->replaceNumeric($request->batas_atas);
        $mTarifPPH->batas_bawah = $this->replaceNumeric($request->batas_bawah);
        $mTarifPPH->tarif = floatval(str_replace(',', '.', str_replace('.', '', $request->tarif)));
        $mTarifPPH->update();
        return redirect()->route('tarif-lembur-index')->with('msg','Sukses Mengubah Data');
    }
    public function delete($id)
    {
        MTarifPph::updateDeleted($id);
        return redirect()->route('tarif-lembur-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MTarifPph::withDeleted()->orderBy('batas_bawah','asc');
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('konfigurasi_tarif_PPH_delete')) {                    
                    $btn .= '<a href="'.url('tarif_pph/delete/'.$row->id_tarif_pph).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('konfigurasi_tarif_PPH_edit')) {                    
                    $btn .= '<a href="'.url('tarif_pph/show/'.$row->id_tarif_pph).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
