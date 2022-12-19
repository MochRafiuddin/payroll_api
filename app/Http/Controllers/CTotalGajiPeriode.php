<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\Models\TTotalGajiPeriode;
use App\Models\MPeriode;
use App\Models\MSetting;
use App\Models\MRole;
use App\Models\User;
use App\Models\RefTemplateNotif;
use App\Models\Notif;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use App\Traits\Helper;
use Excel;
use App\Exports\ExportLaporanGajiKaryawan;
use App\Mail\Email_notif;
use Mail;
use Lang;

class CTotalGajiPeriode extends Controller
{
    use Helper;
    public function index()
    {
        $periode = MPeriode::withDeleted()->orderBy('id_periode','DESC')->limit(12)->get();
        return view('total_gaji_periode.index')
        ->with('title','Total Gaji Periode')
        ->with('periode',$periode);
    }

    public function export_gaji_karyawan($id_periode) 
    {
        $periode = MPeriode::find($id_periode);
        $param = [
            'id_periode' => $id_periode,
            'periode_bulan' => $periode->bulan,
            'periode_tahun' => $periode->tahun,
        ];
        return Excel::download(new ExportLaporanGajiKaryawan($param), 'Laporan Gaji Karyawan Periode '.$this->convertBulan($periode->bulan).' '.$periode->tahun.'.xlsx');
    }

    public function datatable($periode)
    {
        $model = TTotalGajiPeriode::withDeleted()->where('id_periode',$periode);
                // echo $model;
        return DataTables::eloquent($model)
            ->addColumn('action', function ($row) {
                $btn = '';
                $hr = MRole::where('kode_role','hr')->where('id_role',Auth::user()->id_role)->first();
                $accounting = MRole::where('kode_role','accounting')->where('id_role',Auth::user()->id_role)->first();
                $gm = MRole::where('kode_role','gm')->where('id_role',Auth::user()->id_role)->first();
                $presdir = MRole::where('kode_role','presdir')->where('id_role',Auth::user()->id_role)->first();
                
                // $btn = '<a href="'.route('export_gaji_karyawan2',$row->id_periode).'" data-toggle="modal" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-printer"></span></a>';
                
                if ($row->approval == 0 && $hr != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-id="'.$row->id.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                }elseif ($row->approval == 1 && $row->approval2 == 0 && $accounting != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-id="'.$row->id.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                }elseif ($row->approval == 1 && $row->approval2 == 1 && $row->approval3 == 0 && $gm != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-id="'.$row->id.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                }elseif ($row->approval == 1 && $row->approval2 == 1 && $row->approval3 == 1 && $row->approval4 == 0 && $presdir != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-id="'.$row->id.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                }else {
                    $btn = '';
                    // $btn .= "<a class='btn btn-success btn-small' href='{{route('export-gaji')}}'>Export Gaji</a>";
                    if (Helper::can_akses('penggajian_approval_total_gaji_export')) {
                        $btn .= '<a href="'.route('export_gaji_karyawan2',$row->id_periode).'" class="text-success"><span class="mdi mdi-printer"></span></a>';
                    }
                }
                return $btn;
            })
            ->addColumn('nominal',function ($row) {
                $nominal = '';
                $nominal .='<a href="'.url('riwayat/riwayat-penggajian?periode='.$row->id_periode).'">'.$this->ribuan(ceil($row->nominal)).'</a>';
                return $nominal;
            })
            ->addColumn('approval',function ($row) {
                $html = "";
                $html .= $this->approval1($row->approval,1);
                $html .= $this->approval1($row->approval2,2);
                $html .= $this->approval1($row->approval3,3);
                $html .= $this->approval1($row->approval4,4);
                return $html;
            })
            ->addColumn('periode',function ($row) {
                
                $periode=MPeriode::find($row->id_periode);
                $bulan = sprintf("%02s", $periode->bulan);
                $date = Carbon::createFromFormat('m', $bulan);
                $monthName = $date->format('F');
                // $monthName = Helper::convertBulan($periode->bulan);

                $hsl = $monthName." ".$periode->tahun;
                return $hsl;
            })
            ->editColumn('gaji_pokok',function ($row) {                
                $nominal =$this->ribuan(ceil($row->gaji_pokok));
                return $nominal;
            })
            ->editColumn('lembur',function ($row) {                
                $nominal =$this->ribuan(ceil($row->lembur));
                return $nominal;
            })
            ->editColumn('tunjangan',function ($row) {                
                $nominal =$this->ribuan(ceil($row->tunjangan));
                return $nominal;
            })
            ->editColumn('jht_karyawan',function ($row) {                
                $nominal =$this->ribuan(ceil($row->jht_karyawan));
                return $nominal;
            })
            ->editColumn('jpn_karyawan',function ($row) {                
                $nominal =$this->ribuan(ceil($row->jpn_karyawan));
                return $nominal;
            })
            ->editColumn('jkn_karyawan',function ($row) {                
                $nominal =$this->ribuan(ceil($row->jkn_karyawan));
                return $nominal;
            })
            ->editColumn('pph21',function ($row) {                
                $nominal =$this->ribuan(ceil($row->pph21));
                return $nominal;
            })
            ->editColumn('deduction',function ($row) {                
                $nominal =$this->ribuan(ceil(str_replace("-","",$row->deduction)));                
                return $nominal;
            })
            ->rawColumns(['action','approval','periode','nominal'])
            ->addIndexColumn()
            ->toJson();
    }
    public function persetujuan(Request $request)
    {
        $id = $request->id;
        $konfrim = $request->konfrim;
        $hr = MRole::where('kode_role','hr')->where('id_role',Auth::user()->id_role)->first();
        $accounting = MRole::where('kode_role','accounting')->where('id_role',Auth::user()->id_role)->first();
        $gm = MRole::where('kode_role','gm')->where('id_role',Auth::user()->id_role)->first();
        $presdir = MRole::where('kode_role','presdir')->where('id_role',Auth::user()->id_role)->first();
            if ($hr) {
                $mCuti = TTotalGajiPeriode::find($id);
                $mCuti->approval = $konfrim;
                $mCuti->approve_by = Auth::user()->id_user;
                $mCuti->approve_date = date('Y-m-d H:i:s');
                $mCuti->update();
                if ($konfrim==1) {
                    $this->notif($id,"accounting");
                }
            }elseif ($accounting) {
                $mCuti = TTotalGajiPeriode::find($id);
                $mCuti->approval2 = $konfrim;
                $mCuti->approve2_by = Auth::user()->id_user;
                $mCuti->approve2_date = date('Y-m-d H:i:s');
                $mCuti->update();
                if ($konfrim==1) {
                    $this->notif($id,"gm");
                }
            }elseif ($gm) {
                $mCuti = TTotalGajiPeriode::find($id);
                $mCuti->approval3 = $konfrim;
                $mCuti->approve3_by = Auth::user()->id_user;
                $mCuti->approve3_date = date('Y-m-d H:i:s');
                $mCuti->update();
                if ($konfrim==1) {
                    $this->notif($id,"presdir");
                }
            }elseif ($presdir) {
                $mCuti = TTotalGajiPeriode::find($id);
                $mCuti->approval4 = $konfrim;
                $mCuti->approve4_by = Auth::user()->id_user;
                $mCuti->approve4_date = date('Y-m-d H:i:s');
                $mCuti->update();
            }
            
        
        return response()->json(['success'=>'update successfully.']);
    }
    public function approval1($app,$no){
        if ($no==1) {
            if($app == 0){
                $html = '<div style="white-space: nowrap">approval HR : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space: nowrap">approval HR : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space: nowrap">approval HR : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }elseif ($no==2) {
            if($app == 0){
                $html = '<div style="white-space: nowrap">approval Accounting : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space: nowrap">approval Accounting : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space: nowrap">approval Accounting : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }elseif ($no==3) {
            if($app == 0){
                $html = '<div style="white-space: nowrap">approval GM : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space: nowrap">approval GM : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space: nowrap">approval GM : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }else {
            if($app == 0){
                $html = '<div style="white-space: nowrap">approval Presdir : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space: nowrap">approval Presdir : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space: nowrap">approval Presdir : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }
        return $html;
    }
    
    public function notif($id,$role){
        
        $ref_notif = RefTemplateNotif::where('kode','approval_total_gaji')->where('deleted',1)->first();
        $tgaji = TTotalGajiPeriode::find($id);
        $peri = MPeriode::find($tgaji->id_periode);
        $isi = $ref_notif->isi;        
        $isi = str_replace("{periode_gaji}",$this->convertBulan($peri->bulan).' '.$peri->tahun,$isi);
        $isi = str_replace("{total_gaji}",$this->ribuan(ceil($tgaji->nominal)),$isi);

        $rol = MRole::where('kode_role',$role)->first();
        $user = User::where('id_role',$rol->id_role)->where('deleted',1)->get();
            foreach ($user as $values) {                
                $not = new Notif;
                $not->id_user = $values->id_user;
                $not->judul = $ref_notif->judul;
                $not->url = "penggajian/approval-gaji";
                $not->isi = $isi;
                $not->is_read = 0;
                $not->deleted = 1;
                $not->save();
    
                $new = User::find($values->id_user);                    
                $new->new_notif = $values->new_notif + 1;
                $new->update();

                // Mail::to($values->email)->send(new Email_notif($values->name,$ref_notif->judul,$isi,"penggajian/approval-gaji"));
            }
        
    }
}
