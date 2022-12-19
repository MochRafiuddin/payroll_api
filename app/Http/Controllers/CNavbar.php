<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notif;
use App\Models\User;

class CNavbar extends Controller
{
    public function update_user($id_user)
    {
        User::where('id_user',$id_user)            
            ->update([
                'new_notif'=>0
            ]);

        return response()->json(['success'=>'update successfully.']);
    }

    public function update_notif($id_notif)
    {
        Notif::where('id_notif',$id_notif)
            ->update([
                'is_read'=>1
            ]);

        return response()->json(['success'=>'update successfully.']);
    }    
}
