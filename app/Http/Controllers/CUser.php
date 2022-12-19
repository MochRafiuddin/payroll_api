<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use Hash;
use App\Traits\Helper;
use App\Models\User;
use App\Models\MRole;
use App\Models\MKaryawan;
use App\Models\MapAnggota;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CUser extends Controller
{
    public function index()
    {
        return view('user.index')->with('title','User');
    }
    public function datatable()
    {
        $model = User::join('m_role','m_role.id_role','=','m_users.id_role','left')
            ->where('m_users.deleted',1);
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                if (Helper::can_akses('setting_user_delete')) {
                    $btn .= '<a href="'.url('user/delete/'.$row->id_user).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('setting_user_edit')) {                    
                    $btn .= '<a href="'.url('user/show/'.$row->id_user).'" class="text-danger mr-2"><span class="mdi mdi-pen"></span></a>';
                }
                if (Helper::can_akses('setting_user_reset_password')) {                    
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal"  data-id="'.$row->id_user.'" data-original-title="Password" class="text-success editPass mr-2"><span class="mdi mdi-lock-reset"></span></a>';
                }
                    $btn .= '<a href="'.url('user/switch-user/'.$row->id_user).'" class="text-warning mr-2"><span class="mdi mdi-account-convert "></span></a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
    public function create($title_page = 'Tambah')
    {
        $role = MRole::withDeleted()->get();
        $url = url('user/create-save');
        $karyawan = MKaryawan::withDeleted()->get();
        return view('user.form', compact('karyawan'))
            ->with('data',null)
            ->with('departemen',null)
            ->with('role',$role)
            ->with('title','User')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        // dd($request->multi_anggota);
        $validator = Validator::make($request->all(),[
            'id_karyawan' => 'required', 
            'name' => 'required', 
            'username' => 'required', 
            'email' => 'required', 
            'password' => 'required', 
            'id_role' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $user = new User();
        $user->id_karyawan = $request->id_karyawan;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->id_role = $request->id_role;
        $user->save();
        if ($request->multi_anggota) {
            for ($i=0; $i < count($request->multi_anggota); $i++) {                 
                $anggota = new MapAnggota();
                $anggota->id_submitter = $request->id_karyawan;                
                $anggota->id_karyawan = $request['multi_anggota'][$i];                
                $anggota->save();
            }            
        }
        return redirect()->route('user-index')->with('msg','Sukses Menambahkan Data');
    }
    public function show($id)
    {
        // dd(TIzinnCuti::find($id));
        $role = MRole::withDeleted()->get();
        $karyawan = MKaryawan::withDeleted()->get();
        $data = User::find($id);
        $departemen = MKaryawan::find($data->id_karyawan);
        return view('user.form', compact('karyawan'))
            ->with('data',$data)
            ->with('departemen',$departemen)
            ->with('title','User')
            ->with('titlePage','Edit')
            ->with('role',$role)
            ->with('url',url('user/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_karyawan' => 'required', 
            'name' => 'required', 
            'username' => 'required', 
            'email' => 'required', 
            'id_role' => 'required'
        ]);
        // dd($validator);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $user = User::find($id);
        $user->id_karyawan = $request->id_karyawan;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;        
        $user->id_role = $request->id_role;
        $user->update();
        if ($request->multi_anggota) {
            MapAnggota::where('id_submitter',$request->id_karyawan)->delete();
            for ($i=0; $i < count($request->multi_anggota); $i++) {                 
                $anggota = new MapAnggota();
                $anggota->id_submitter = $request->id_karyawan;                
                $anggota->id_karyawan = $request['multi_anggota'][$i];                
                $anggota->save();
            }            
        }
        return redirect()->route('user-index')->with('msg','Sukses Mengubah Data');
    }
    public function delete($id)
    {   
        // echo $id;
        $user = User::find($id);
        $user->deleted = 0;
        $user->update();
        return redirect()->route('user-index')->with('msg','Sukses Menambahkan Data');

    }
    public function reset_pass(Request $request)
    {   
        // echo $id;
        $user = User::find($request->id_role);
        $user->password = Hash::make($request->password);
        $user->update();
        return response()->json(['success'=>'Sukses Update Data']);
    }
    
    public function get_nama_karyawan($id)
    {   
        $user = MKaryawan::find($id);
        return response()->json($user);
    }

    public function get_role($id_role,$id_departemen)
    {                   
        $user['role'] = MRole::find($id_role);
        $kar = MKaryawan::where("id_departemen",$id_departemen)->where('deleted',1)->orderBy('nama_karyawan','asc')->get();
        $user['karyawan']="";
        foreach ($kar as $key) {
            $user['karyawan'] .="<option value='".$key->id_karyawan."' >".$key->nama_karyawan."</option>";            
        }
        // return response()->json($user);
        return response()->json(['error'=>0,'data'=>$user]);        
    }

    public function get_anggota($id,$id_departemen,$id_role)
    {   
        if ($id_role != null && $id != null && $id_departemen != null) {                        
            $user['role'] = MRole::find($id_role);
            $kar = MKaryawan::where("id_departemen",$id_departemen)->where('deleted',1)->orderBy('nama_karyawan','asc')->get();
            $user['karyawan']="";
            foreach ($kar as $key) {
                $kar = MapAnggota::where("id_submitter",$id)->where("id_karyawan",$key->id_karyawan)->first();
                if ($kar) {                    
                    $user['karyawan'] .="<option value='".$key->id_karyawan."' selected>".$key->nama_karyawan."</option>";            
                }else{
                    $user['karyawan'] .="<option value='".$key->id_karyawan."'>".$key->nama_karyawan."</option>";            
                }
            }
            // return response()->json($user);
            return response()->json(['error'=>0,'data'=>$user]);
        }else{
            return response()->json(['error'=>1]);
        }
    }
    public function switch_user($id)
    {        
        Auth::loginUsingId($id, true);
        return redirect(url('dashboard'));
        // echo "sdas";
    }
}
