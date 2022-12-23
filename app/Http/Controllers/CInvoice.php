<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\TGajiKaryawanPeriode;
use App\Models\MPeriode;
use App\Models\MKaryawan;
use App\Models\MDepartement;
use App\Models\MJabatan;
use Carbon\Carbon;
use Response;
class CInvoice extends Controller
{
    public function pdfVoice($id)
    {
        $m_gaji_p = TGajiKaryawanPeriode::where('id',$id)->first();
        $m_p = MPeriode::where('id_periode',$m_gaji_p->id_periode)->first();
        $m_k = MKaryawan::where('id_karyawan',$m_gaji_p->id_karyawan)->first();
        $m_d = MDepartement::where('id_departemen',$m_k->id_departemen_label)->first();
        $m_j = MJabatan::where('id_jabatan',$m_k->id_jabatan)->first();
        
        $html = view("invoice.voice")
            ->with('tgl',$m_p)
            ->with('karyawan',$m_k)
            ->with('departemen',$m_d)
            ->with('gaji_p',$m_gaji_p)
            ->with('jabatan',$m_j);
        $my_pdf_path_for_example = 'download/invoice/';
        if (!file_exists(public_path($my_pdf_path_for_example))) {
            mkdir(public_path($my_pdf_path_for_example), 0777, true);
            $path =public_path($my_pdf_path_for_example).time().rand(1,100).'-invoice.pdf';
            PDF::loadHTML($html, 'utf-8')->save($path);
        }else{
            $path =public_path($my_pdf_path_for_example).time().rand(1,100).'-invoice.pdf';
            PDF::loadHTML($html, 'utf-8')->save($path);
        }        
            return response()->file($path);
            
        // $pdf = PDF::loadView("invoice.voice", ['tgl'=>$m_p,
        // 'karyawan'=>$m_k,
        // 'departemen'=>$m_d,
        // 'gaji_p'=>$m_gaji_p,
        // 'jabatan'=>$m_j]);
        // return $pdf->stream($path, array("Attachment" => false));
    }
}
