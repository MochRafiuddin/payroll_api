<?php

namespace App\Http\Controllers;

use App\Models\MBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use App\Traits\Helper;  
class CBank extends Controller
{
     public function index()
    {
        return view('bank.index')->with('title','Bank');
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('bank/create-save');
        return view('bank.form')
            ->with('data',null)
            ->with('title','Bank')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_bank' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $MBank = new MBank;
        $MBank->nama_bank = $request->nama_bank;
        $MBank->save();
        return redirect()->route('bank-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MBank::find($id));
        
        return view('bank.form')
            ->with('data',MBank::find($id))
            ->with('title','bank')
            ->with('titlePage','Edit')
            ->with('url',url('bank/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_bank' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $MBank = MBank::find($id);
        $MBank->nama_bank = $request->nama_bank;
        $MBank->update();
        return redirect()->route('bank-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MBank::updateDeleted($id);
        return redirect()->route('bank-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $model = MBank::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('referensi_bank_delete')) {
                    $btn .= '<a href="'.url('bank/delete/'.$row->id_bank).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('referensi_bank_edit')) {
                    $btn .= '<a href="'.url('bank/show/'.$row->id_bank).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
}
