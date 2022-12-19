<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\Traits\Helper;
use App\Models\MRole;
use App\Models\MapRoleAction;
use App\Models\MMenu;
use App\Models\MAction;
use Illuminate\Support\Facades\Validator;

class CRole extends Controller
{
    public function index()
    {
        return view('role.index')->with('title','Role');
    }
    public function datatable()
    {
        $model = MRole::withDeleted();
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if ($row->kode_role == "superadmin") {
                    $btn = '';
                }elseif ($row->kode_role == "leader" || $row->kode_role == "asman" || $row->kode_role == "manager" || $row->kode_role == "gm" || $row->kode_role == "hr" || $row->kode_role == "presdir" || $row->kode_role == "accounting") {
                    if (Helper::can_akses('setting_role_set_menu')) {                        
                        $btn .= '<a href="'.url('role/set-menu/'.$row->id_role).'" class="text-success"><span class="mdi mdi-account-key"></span></a>';
                    }
                }else{
                    if (Helper::can_akses('setting_role_delete')) {                        
                        $btn .= '<a href="'.url('role/delete/'.$row->id_role).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                    }
                    if (Helper::can_akses('setting_role_edit')) {                        
                        $btn .= '<a href="'.url('role/show/'.$row->id_role).'" class="text-danger mr-2"><span class="mdi mdi-pen"></span></a>';
                    }
                    if (Helper::can_akses('setting_role_set_menu')) {                        
                        $btn .= '<a href="'.url('role/set-menu/'.$row->id_role).'" class="text-success"><span class="mdi mdi-account-key"></span></a>';
                    }
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
    public function create($title_page = 'Tambah')
    {
        $url = url('role/create-save');
        return view('role.form')
            ->with('data',null)
            ->with('title','Role')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_role' => 'required',             
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $user = new MRole();
        $user->nama_role = $request->nama_role;
        $user->save();
        return redirect()->route('role-index')->with('msg','Sukses Menambahkan Data');
    }
    public function show($id)
    {
        return view('role.form')
            ->with('data',MRole::find($id))
            ->with('title','Role')
            ->with('titlePage','Edit')
            ->with('url',url('role/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_role' => 'required'
        ]);
        // echo $request->id_role;
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $user = MRole::find($id);
        $user->nama_role = $request->nama_role;
        $user->update();
        return redirect()->route('role-index')->with('msg','Sukses Mengubah Data');
    }
    public function delete($id)
    {   
        // echo $id;
        $user = MRole::find($id);
        $user->deleted = 0;
        $user->update();
        return redirect()->route('role-index')->with('msg','Sukses Menambahkan Data');
    }
    public function set_menu($id)
    {
        $url = url('role/set-menu-save');
        return view('role.set_menu')
            ->with('data',MapRoleAction::where(['id_role'=>$id])->get())
            ->with('menu',MMenu::withDeleted()->get())
            ->with('title','Role')
            ->with('titlePage','Set Menu')
            ->with('url',url('role/set-menu-save/'.$id));

    }
    public function set_menu_save($id, Request $request)
    {
        MapRoleAction::where('id_role',$id)->delete();

        for ($i=0; $i < count($request->id_action); $i++) { 
            $user = new MapRoleAction;
            $user->id_role = $id;
            $user->id_action = $request['id_action'][$i];
            $user->save();
        }
        return redirect()->route('role-index')->with('msg','Sukses Mengubah Data');
    }
}
