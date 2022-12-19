<?php

namespace App\Http\Controllers;

use App\Models\MPeriode;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\DocBlock\Tags\See;

class CLogin extends Controller
{
    use Helper;
    public function index()
    {
         return view('login')->with('title','Login');
    }
    public function authenticate(Request $request)
    {

        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password, 'deleted' => 1])) {
            $periode = MPeriode::where('aktif',1)->first();
            $this->setSessionPeriode($periode);
            
            $request->session()->regenerate();
            // dd(session()->all());
            Session::put('can_switch_account', Auth::user()->can_switch_account);
            Session::put('name', Auth::user()->name);
            Session::put('id_user', Auth::user()->id_user);
            if (($this->can_akses('dashboard_karyawan') && $this->can_akses('dashboard')) || $this->can_akses('dashboard')) {
                return redirect(url('dashboard'));
            }else if ($this->can_akses('dashboard_karyawan')) {                
                return redirect(url('dashboard/karyawan'));
            }
        }
 
        // return back()->withErrors([
        //     'username' => 'The provided credentials do not match our records.',
        // ]);
        return back()->withSuccess('Username / Password Salah');
    }
    public function logout(Request $request)
    {
        Auth::logout();
 
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect('/');
    }
}
