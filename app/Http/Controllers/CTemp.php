<?php

namespace App\Http\Controllers;

use App\Models\TIzinnCuti;
use App\Models\TIzinDetail;
use Illuminate\Http\Request;

use DateInterval;
use DatePeriod;
use DateTime;

class CTemp extends Controller
{
    public function kirim_detail_izin()
    {        
        $izin = TIzinnCuti::where('deleted',1)->get();
        foreach ($izin as $value) {
            $detail_izin = TIzinDetail::where('id_izin',$value->id_izin)->get();
            if ($detail_izin->count() <= 0) {

                $period = new DatePeriod(
                    new DateTime($value->tanggal_mulai),
                    new DateInterval('P1D'),
                    new DateTime($value->tanggal_selesai.' +1 days')
                );
                    
                foreach($period as $key){
                    $absensi = new TIzinDetail;
                    $absensi->id_karyawan = $value->id_karyawan;
                    $absensi->tanggal = $key->format('Y-m-d');            
                    $absensi->id_izin = $value->id_izin;
                    $absensi->save();
                }
            }
        }
        return response()->json(['status'=>true,'msg'=>'Berhasil Input ke Detail Izin']);
    }
}
