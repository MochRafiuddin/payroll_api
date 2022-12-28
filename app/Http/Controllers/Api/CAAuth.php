<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MApiKey;
use App\Traits\Helper;
use Auth;
use Hash;

class CAAuth extends Controller
{
    use Helper;

    public function logout(Request $request)
    {        
        MApiKey::where('token',$request->header('auth-key'))->delete();        
        Auth::logout();
        return response()->json([
            'message' => 'Logout Success',            
            'code' => 1,
        ]);
    }

    public function ubah_password(Request $request)
    {        
        $token = MApiKey::where('token',$request->header('auth-key'))->first();        
        
        $user = User::find($token->id_user);
        $user->password = Hash::make($request->password);
        $user->update();

        return response()->json([
            'message' => 'Password Update',
            'code' => 1,
        ]);
    }

    public function detail_profil(Request $request)
    {        
        $token = MApiKey::where('token',$request->header('auth-key'))->first();        
        
        $user = User::join('m_karyawan','m_karyawan.id_karyawan','m_users.id_karyawan')
                ->join('m_jabatan','m_jabatan.id_jabatan','m_karyawan.id_jabatan')
                ->join('m_departemen','m_departemen.id_departemen','m_karyawan.id_departemen_label')
                ->join('m_bank','m_bank.id_bank','m_karyawan.id_bank')
                ->join('m_status_karyawan','m_status_karyawan.id_status_karyawan','m_karyawan.id_status_karyawan')
                ->join('m_status_kawin','m_status_kawin.id_status_kawin','m_karyawan.id_status_kawin')
                ->join('m_agama','m_agama.id_agama','m_karyawan.id_agama')
                ->select('m_karyawan.*','m_jabatan.nama_jabatan','m_departemen.nama_departemen','m_bank.nama_bank','m_status_karyawan.nama_status_karyawan','m_agama.nama_agama','m_status_kawin.nama_status_kawin')
                ->where('m_users.id_user',$token->id_user)
                ->get();

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'code' => 1,
            'data' => $user
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password, 'deleted' => 1]))
        {
            return response()->json([
                'success' => true,
                'message' => "Oops, we couldn't find your account",
                'code' => 0,
            ], 400);
        }

        $cek_token = MApiKey::where('id_user',auth::user()->id_user)->first();

        if ($cek_token) {
            MApiKey::where('id_user',auth::user()->id_user)->delete();    
        }
            $key = Helper::generateRandomString();
            $token = new MApiKey();
            $token->id_user = auth::user()->id_user;
            $token->token = $key;
            $token->save();

            $get_user = User::join('m_karyawan','m_karyawan.id_karyawan','m_users.id_karyawan')
                ->join('m_departemen','m_departemen.id_departemen','m_karyawan.id_departemen_label')
                ->select('m_users.*','m_departemen.nama_departemen')
                ->where('m_users.id_user',auth::user()->id_user)->get();
        
        return response()->json([
            'message' => 'Login Success',
            'key' => $key,
            'code' => 1,
            'user_data' => $get_user,
        ]);
    }
}
