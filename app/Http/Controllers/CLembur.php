<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\Models\TLembur;
use App\Models\MTarifLembur;
use App\Models\TAbsensi;
use App\Models\MShiftKaryawan;
use App\Models\MGrupKaryawan;
use App\Models\MKaryawan;
use App\Models\MRole;
use App\Models\MapAnggota;
use App\Models\User;
use App\Models\RefTemplateNotif;
use App\Models\Notif;
use Illuminate\Support\Facades\Validator;
use App\Traits\Helper;
use App\Mail\Email_notif;

use Mail;
use Auth;
use Carbon\Carbon;
use Session;
use Lang;

class CLembur extends Controller
{
    use Helper;

    public function index()
    {
        $bulan = Session::get('bulan');
        $tahun = Session::get('tahun');
        $id_karyawan_filter = Session::get('id_karyawan_filter');
        $karyawan = MKaryawan::withDeleted()->get();
        return view('lembur.index', compact('karyawan'))->with(['title'=>'Lembur Karyawan','bulan'=>$bulan,'tahun'=>$tahun,'id_karyawan_filter'=>$id_karyawan_filter]);
    }

    public function edit($id_karyawan,$tanggal,$id_karyawan_filter)
    {
        $absen_karyawan = TAbsensi::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->first();
        $m_shift_karyawan = MShiftKaryawan::from('m_shift_karyawan as a')
                    ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                    ->select('a.*','b.*')
                    ->where('a.id_karyawan',$id_karyawan)->where('a.tanggal',$tanggal)
                    ->where('a.deleted','1')
                    ->where('b.deleted','1')
                    ->first();
        $data_lembur = TLembur::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->where('deleted','1')->get();
        $arr_lembur = [];
        foreach ($data_lembur as $value) {
            $arr_lembur[] = [
                'index_tarif' => $value->index_tarif,
                'jumlah_jam' => $value->jumlah_jam,
            ];
        }

        $data1 = TLembur::where('id_karyawan',$id_karyawan)
                    ->where('tanggal',$tanggal)
                    ->where('deleted',1)
                    ->first();
        $alasan = $data1->alasan_lembur;
        $data = [
            'tanggal_shift' => Carbon::createFromFormat('Y-m-d',$tanggal)->format('d-m-Y'),
            'def_tanggal_shift' => $tanggal,
            'id_karyawan' => $id_karyawan,
            'id_shift' => $m_shift_karyawan->id_shift,
            'shift_masuk' => $m_shift_karyawan->jam_masuk ?? Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_masuk)->format('d-m-Y H:i'),
            'shift_keluar' => $m_shift_karyawan->jam_keluar ?? Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_keluar)->format('d-m-Y H:i'),
            'waktu_masuk' => Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_masuk)->format('d-m-Y H:i'),
            'waktu_keluar'=> Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_keluar)->format('d-m-Y H:i'),
            'data_lembur' => $arr_lembur,
            
        ];

        $data = (object) $data;
        
        return view('lembur.edit', compact('data'))->with(['title'=>'Edit Lembur Karyawan','alasan'=>$alasan,'id_karyawan_filter'=>$id_karyawan_filter]);
    }

    public function detail($id_karyawan,$tanggal,$id_karyawan_filter)
    {
        $absen_karyawan = TAbsensi::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->first();
        $m_shift_karyawan = MShiftKaryawan::from('m_shift_karyawan as a')
                    ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                    ->select('a.*','b.*')
                    ->where('a.id_karyawan',$id_karyawan)->where('a.tanggal',$tanggal)
                    ->where('a.deleted','1')
                    ->where('b.deleted','1')
                    ->first();
        $data_lembur = TLembur::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->where('deleted','1')->get();
        $arr_lembur = [];
        foreach ($data_lembur as $value) {
            $arr_lembur[] = [
                'index_tarif' => $value->index_tarif,
                'jumlah_jam' => $value->jumlah_jam,
            ];
        }

        $data1 = TLembur::where('id_karyawan',$id_karyawan)
                    ->where('tanggal',$tanggal)
                    ->where('deleted',1)
                    ->first();
        $alasan = $data1->alasan_lembur;
        $data = [
            'tanggal_shift' => Carbon::createFromFormat('Y-m-d',$tanggal)->format('d-m-Y'),
            'def_tanggal_shift' => $tanggal,
            'id_karyawan' => $id_karyawan,
            'id_shift' => $m_shift_karyawan->id_shift,
            'shift_masuk' => $m_shift_karyawan->jam_masuk ?? Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_masuk)->format('d-m-Y H:i'),
            'shift_keluar' => $m_shift_karyawan->jam_keluar ?? Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_keluar)->format('d-m-Y H:i'),
            'waktu_masuk' => Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_masuk)->format('d-m-Y H:i'),
            'waktu_keluar'=> Carbon::createFromFormat('Y-m-d H:i:s',$absen_karyawan->tanggal_keluar)->format('d-m-Y H:i'),
            'data_lembur' => $arr_lembur,
            
        ];

        $data = (object) $data;
        
        return view('lembur.detail', compact('data'))->with(['title'=>'Edit Lembur Karyawan','alasan'=>$alasan,'id_karyawan_filter'=>$id_karyawan_filter]);
    }
    
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'index_tarif' => 'required',             
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $this->MGrupKaryawan = MGrupKaryawan::withDeleted()->get();
        $this->mTarifLembur = MTarifLembur::withDeleted()->get();
        $this->MKaryawan = MKaryawan::withDeleted()->get();

        $id_karyawan = $request->id_karyawan;
        $tanggal = $request->tanggal_shift;
        $id_shift = $request->id_shift;
        $tipe_hari = TLembur::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->first()->tipe_hari ?? '0';
        $arr_lembur = [];

        $function = function($n) { return str_replace(',', '.', $n); };
        $index_tarif = array_map($function, $request->index_tarif);
        $jumlah_jam = array_map($function, $request->jumlah_jam);
        if (count($request->index_tarif) > 0) {
            for ($i=0; $i < count($index_tarif); $i++) { 
                $indexKerja = $this->getIndexLembur(($i+1),$id_shift,$tanggal,$id_karyawan);
                $arr_lembur[] = [
                    'id_karyawan'=>$id_karyawan,
                    'id_tarif_lembur' => $indexKerja['id_tarif_lembur'],
                    'index_tarif' => $index_tarif[$i],
                    'tipe_hari'=>$tipe_hari,
                    'tanggal' => $tanggal,
                    'alasan_lembur' => $request->alasan,
                    'jumlah_jam' => $jumlah_jam[$i],
                    'approval'=>0,
                    'approve_by'=>0,
                    'approval2'=>0,
                    'approve2_by'=>0,
                    'approval3'=>0,
                    'approve3_by'=>0,
                    'created_by'=>Auth::user()->id_user,
                    'updated_by'=>0,
                ];
            }
        }
        TLembur::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->update(['deleted'=>'0']);
        TLembur::insert($arr_lembur);

        $tahun = Carbon::createFromFormat('Y-m-d',$tanggal)->format('Y');
        $bulan = Carbon::createFromFormat('Y-m-d',$tanggal)->format('m');
        Session::flash('bulan', $bulan); 
        Session::flash('tahun', $tahun); 
        Session::flash('id_karyawan_filter', $request->id_karyawan_filter); 
        return redirect()->route('lembur-index');
    }
    public function datatable()
    {
        $start_date = (!empty($_GET["start_date"])) ? ($_GET["start_date"]) : ('');
        $end_date = (!empty($_GET["end_date"])) ? ($_GET["end_date"]) : ('');
        // $id_karyawan = $_GET["id_karyawan"];
        $id_karyawan = (!empty($_GET["id_karyawan"])) ? ($_GET["id_karyawan"]) : 0;
        $departemen = MKaryawan::find(Auth::user()->id_karyawan);
        $rol = MRole::find(Auth::user()->id_role);

        $model = TLembur::where('t_lembur.deleted',1)
            ->selectRaw('t_lembur.approval,t_lembur.approval2,t_lembur.approval3,sum(jumlah_jam) as total_jam,m_karyawan.nama_karyawan,t_lembur.tanggal,m_karyawan.id_karyawan')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_lembur.id_karyawan','left')
            ->groupBy('m_karyawan.id_karyawan','m_karyawan.nama_karyawan','tanggal','t_lembur.approval');
        
        if ($rol->kode_role == "leader") {
            $anggota = MapAnggota::where('id_submitter',Auth::user()->id_karyawan)->pluck('id_karyawan')->toArray();
            $sendiri = Auth::user()->id_karyawan;
            array_push($anggota, $sendiri);                
            $model = $model->whereIn('m_karyawan.id_karyawan',$anggota);
        }

        if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {                
            $model = $model->where('m_karyawan.id_departemen',$departemen->id_departemen);
        }

        if ($start_date && $end_date) {
            $model->whereMonth('t_lembur.tanggal',$start_date);
            $model->whereYear('t_lembur.tanggal',$end_date); 
        }

        if ($id_karyawan != 0) {
            $model->where('t_lembur.id_karyawan',$id_karyawan); 
        }
            // $model->orderBy('m_karyawan.nama_karyawan','asc'); 
            $model->orderBy('t_lembur.tanggal','asc'); 

        return DataTables::of($model)
            ->addColumn('action', function ($row) use ($id_karyawan) {
                $btn = '';
                $asman = MRole::where('kode_role','asman')->where('id_role',Auth::user()->id_role)->first();
                $manager = MRole::where('kode_role','manager')->where('id_role',Auth::user()->id_role)->first();
                $gm = MRole::where('kode_role','gm')->where('id_role',Auth::user()->id_role)->first();
                
                if ($row->approval == 0 && $asman != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-tanggal="'.$row->tanggal.'" data-karyawanFilter="'.$id_karyawan.'" data-approval="'.$row->approval.'" data-karyawan="'.$row->id_karyawan.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';                    
                    // if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                    //     $btn .= '<a href="'.url('/lembur/edit/'.$row->id_karyawan.'/'.$row->tanggal.'').'" data-tanggal="'.$row->tanggal.'" data-karyawan="'.$row->id_karyawan.'" class="text-warning"><span class="mdi mdi-pen"></span></a>';                        
                    // }
                    $btn .= $this->cek_edit($row->approval,$row->approval2,$row->approval3,$row->id_karyawan,$row->tanggal,$id_karyawan);

                    $btn .= '<a href="javascript:void(0)" class="text-primary mr-2"><span class="mdi mdi-information-outline"></span></a>';  
                }elseif ($row->approval == 1 && $row->approval2 == 0 && $manager != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-tanggal="'.$row->tanggal.'" data-karyawanFilter="'.$id_karyawan.'" data-approval="'.$row->approval2.'" data-karyawan="'.$row->id_karyawan.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                    // if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                    //     $btn .= '<a href="'.url('/lembur/edit/'.$row->id_karyawan.'/'.$row->tanggal.'').'" data-tanggal="'.$row->tanggal.'" data-karyawan="'.$row->id_karyawan.'" class="text-warning"><span class="mdi mdi-pen"></span></a>';                        
                    // }
                    $btn .= $this->cek_edit($row->approval,$row->approval2,$row->approval3,$row->id_karyawan,$row->tanggal,$id_karyawan);
                    $btn .= '<a href="javascript:void(0)" class="text-primary mr-2"><span class="mdi mdi-information-outline"></span></a>';  
                }elseif ($row->approval == 1 && $row->approval2 == 1 && $row->approval3 == 0 && $gm != null) {
                    $btn .= '<a href="javascript:void(0)" data-toggle="modal" data-tanggal="'.$row->tanggal.'" data-karyawanFilter="'.$id_karyawan.'" data-approval="'.$row->approval3.'" data-karyawan="'.$row->id_karyawan.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                    // if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                    //     $btn .= '<a href="'.url('/lembur/edit/'.$row->id_karyawan.'/'.$row->tanggal.'').'" data-tanggal="'.$row->tanggal.'" data-karyawan="'.$row->id_karyawan.'" class="text-warning"><span class="mdi mdi-pen"></span></a>';                        
                    // }
                    $btn .= $this->cek_edit($row->approval,$row->approval2,$row->approval3,$row->id_karyawan,$row->tanggal,$id_karyawan);
                    $btn .= '<a href="javascript:void(0)" class="text-primary mr-2"><span class="mdi mdi-information-outline"></span></a>';  
                }else {
                    // if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                    //     $btn .= '<a href="'.url('/lembur/edit/'.$row->id_karyawan.'/'.$row->tanggal.'').'" data-tanggal="'.$row->tanggal.'" data-karyawan="'.$row->id_karyawan.'" class="text-warning"><span class="mdi mdi-pen"></span></a>';                        
                    // }
                    $btn .= $this->cek_edit($row->approval,$row->approval2,$row->approval3,$row->id_karyawan,$row->tanggal,$id_karyawan);
                    $btn .= '<a href="'.url('/lembur/detail/'.$row->id_karyawan.'/'.$row->tanggal.'/'.$id_karyawan).'" class="text-primary mr-2"><span class="mdi mdi-information-outline"></span></a>';  
                }                
                return $btn;
            })
            ->addColumn('approval',function ($row) {
                $html = "";
                $html .= $this->approval1($row->approval,1);
                $html .= $this->approval1($row->approval2,2);
                $html .= $this->approval1($row->approval3,3);
                return $html;
            })
            ->editColumn('tanggal',function ($row) {
                $html = $this->convertDate($row->tanggal,true,false);
                return $html;
            })
            ->editColumn('alasan',function ($row) {
                $data = TLembur::where('id_karyawan',$row->id_karyawan)
                    ->where('tanggal',$row->tanggal)
                    ->where('deleted',1)
                    ->first();
                if ($data) {
                    $html=$data->alasan_lembur;
                }else {
                    $html="";
                }
                    return $html;
            })
            ->rawColumns(['action','approval'])
            ->addIndexColumn()
            ->toJson();
    }
    public function cek_edit($aproval,$aproval2,$aproval3,$id_karyawan,$tanggal,$id_karyawan_filter){
        $btn='';
        if ($aproval==0 || $aproval==2) {
            if ($aproval2==0 || $aproval2==2) {
                if ($aproval3==0 || $aproval3==2) {
                    if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                        $btn .= '<a href="'.url('/lembur/edit/'.$id_karyawan.'/'.$tanggal.'/'.$id_karyawan_filter).'" data-tanggal="'.$tanggal.'" data-karyawan="'.$id_karyawan.'" class="text-warning mr-2"><span class="mdi mdi-pen"></span></a>';                        
                    }                    
                }
            }
        }else {
            if ($aproval2==2) {
                if ($aproval3==2) {
                    if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                        $btn .= '<a href="'.url('/lembur/edit/'.$id_karyawan.'/'.$tanggal.'/'.$id_karyawan_filter).'" data-tanggal="'.$tanggal.'" data-karyawan="'.$id_karyawan.'" class="text-warning mr-2"><span class="mdi mdi-pen"></span></a>';                        
                    }                    
                }else {
                    if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                        $btn .= '<a href="'.url('/lembur/edit/'.$id_karyawan.'/'.$tanggal.'/'.$id_karyawan_filter).'" data-tanggal="'.$tanggal.'" data-karyawan="'.$id_karyawan.'" class="text-warning mr-2"><span class="mdi mdi-pen"></span></a>';                        
                    }                    
                }
            }else {
                if ($aproval3==2) {
                    if (Helper::can_akses('absensi_lembur_karyawan_edit')) {
                        $btn .= '<a href="'.url('/lembur/edit/'.$id_karyawan.'/'.$tanggal.'/'.$id_karyawan_filter).'" data-tanggal="'.$tanggal.'" data-karyawan="'.$id_karyawan.'" class="text-warning mr-2"><span class="mdi mdi-pen"></span></a>';                        
                    }                    
                }
            }
        }
        return $btn;
    }
    public function persetujuan(Request $request)
    {
        $id_karyawan = $request->karyawan_id;
        $tanggal = $request->tanggal;
        $approval = $request->approval;
            $asman = MRole::where('kode_role','asman')->where('id_role',Auth::user()->id_role)->first();
            $manager = MRole::where('kode_role','manager')->where('id_role',Auth::user()->id_role)->first();
            $gm = MRole::where('kode_role','gm')->where('id_role',Auth::user()->id_role)->first();
        if ($asman) {
            TLembur::where('id_karyawan',$id_karyawan)
            ->where('tanggal',$tanggal)
            ->update([
                'approval'=>1,
                'approve_by' => Auth::user()->id_user,
                'approve_date' => date('Y-m-d H:i:s'),
            ]);
            $this->notif($id_karyawan,$tanggal,"manager");
        }elseif ($manager) {
            TLembur::where('id_karyawan',$id_karyawan)
            ->where('tanggal',$tanggal)
            ->update([
                'approval2'=>1,
                'approve2_by' => Auth::user()->id_user,
                'approve2_date' => date('Y-m-d H:i:s'),
            ]);
            $this->notif($id_karyawan,$tanggal,"gm");
        }elseif ($gm) {
            TLembur::where('id_karyawan',$id_karyawan)
            ->where('tanggal',$tanggal)
            ->update([
                'approval3'=>1,
                'approve3_by' => Auth::user()->id_user,
                'approve3_date' => date('Y-m-d H:i:s'),
            ]);
        }
        $tahun = Carbon::createFromFormat('Y-m-d',$tanggal)->format('Y');
        $bulan = Carbon::createFromFormat('Y-m-d',$tanggal)->format('m');
        Session::flash('bulan', $bulan); 
        Session::flash('tahun', $tahun); 
        Session::flash('id_karyawan_filter', $request->filter); 
        return response()->json(['success'=>'update successfully.']);
    }

    public function getIndexLembur($jam_ke, $id_shift, $tanggal_shift, $id_karyawan)
    {
        $nama_hari = Carbon::parse($tanggal_shift)->format('D');
        $id_grup_karyawan = $this->getMKaryawan($id_karyawan);
        $result = 0;
        foreach($this->mTarifLembur as $key){
            if($jam_ke >= $key->jam_ke){ // jam_ke = 9 >= $key->jam_ke = 10
                
                if($id_shift != 1){ // bukan hari libur
                    $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->rate_hari_kerja];
                }else{ // hari libur
                    $hari_kerja = $this->getMGrupKaryawan($id_grup_karyawan);
                    if($hari_kerja == 1){
                        if($nama_hari == 'Sat'){   //jika libur di hari sabtu
                            $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->index_hari_libur_pendek];
                        }else{
                            $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->rate_hari_libur_1];
                        }
                    }elseif($hari_kerja == 2){
                        if($nama_hari == 'Sat'){   //jika libur di hari sabtu
                            $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->index_hari_libur_pendek];
                        }else{
                            $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->rate_hari_libur_2];
                        }
                    }
                }   
            }else{
                return $result;
            }
            
            
        }
        return $result;
    }
    public function getMKaryawan($id_karyawan)
    {
        foreach($this->MKaryawan as $key){
            if($key->id_karyawan == $id_karyawan){
                return $key->id_grup_karyawan;
            }
        }
        return null;
    }
    public function getMGrupKaryawan($id_grup_karyawan)
    {
        foreach($this->MGrupKaryawan as $key){
            if($key->id_grup_karyawan == $id_grup_karyawan){
                return $key->hari_kerja;
            }
        }
        return null;
    }
    public function approval1($app,$no){
        if ($no==1) {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval Atasan Langsung : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }elseif ($no==2) {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval Manager : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }else {
            if($app == 0){
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></div>';
            }elseif($app == 1){
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-success">'.Lang::get('umum.setuju').'</span></div>';
            }else{
                $html = '<div style="white-space:nowrap;">Approval GM : <span class="text-danger">'.Lang::get('umum.tolak').'</span></div>';
            }
        }
        return $html;
    }
    public function notif($id_karyawan,$tanggal,$role){
        $ref_notif = RefTemplateNotif::where('kode','approval_lembur')->where('deleted',1)->first();
        $rol = MRole::where('kode_role',$role)->first();
        $kar = MKaryawan::where('id_karyawan',$id_karyawan)->first();
        $ala = TLembur::where('id_karyawan',$id_karyawan)->where('tanggal',$tanggal)->where('deleted',1)->first();
        $isi = $ref_notif->isi;
        $isi = str_replace("{nama_karyawan}",$kar->nama_karyawan,$isi);
        $isi = str_replace("{tanggal_lembur}",$tanggal,$isi);
        $isi = str_replace("{alasan}",$ala->alasan_lembur,$isi);
        
        // $cekrole = MRole::where('id_role',$value->id_role)->first();
        if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {            
            // $departemen = MKaryawan::select('id_departemen')->where('id_karyawan',$request->id_karyawan)->first();
            $user = MKaryawan::join('m_users','m_users.id_karyawan','=','m_karyawan.id_karyawan')                                
                            ->where('m_karyawan.id_departemen',$kar->id_departemen)
                            ->where('m_users.id_role',$rol->id_role)
                            ->get(); 
        }else{
            // $userA = User::where('id_role',$value->id_role)->where('deleted',1)->get();                    
            $user = User::where('id_role',$rol->id_role)->where('deleted',1)->get();
        }
            foreach ($user as $values) {                
                $not = new Notif;
                $not->id_user = $values->id_user;
                $not->judul = $ref_notif->judul;
                $not->url = "absensi/over-time";
                $not->isi = $isi;
                $not->is_read = 0;
                $not->deleted = 1;
                $not->save();
    
                $new = User::find($values->id_user);                    
                $new->new_notif = $values->new_notif + 1;
                $new->update();

                // Mail::to($values->email)->send(new Email_notif($values->name,$ref_notif->judul,$isi,"absensi/over-time"));
            }
        
    }

}
