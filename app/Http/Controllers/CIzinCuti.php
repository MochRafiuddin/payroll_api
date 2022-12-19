<?php

namespace App\Http\Controllers;

use App\Models\MKaryawan;
use App\Models\MDepartement;
use App\Models\MRole;
use App\Models\RefTipeAbsensi;
use App\Models\TAbsensi;
use App\Models\TIzinnCuti;
use App\Models\TIzinDetail;
use App\Models\MapRoleJabatan;
use App\Models\MapApprIzin;
use App\Models\MapAnggota;
use App\Models\User;
use App\Models\RefTemplateNotif;
use App\Models\Notif;
use Illuminate\Http\Request;
use App\Traits\Helper;
use Mail;
use App\Mail\Email_notif;

use DataTables;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Lang;
use Session;
use Illuminate\Support\Facades\Validator;

use Auth;

class CIzinCuti extends Controller
{
    use Helper;
    public $validate = [
            'id_tipe_absensi' => 'required', 
            'id_karyawan' => 'required', 
            'tanggal_mulai' => 'required', 
            'tanggal_selesai' => 'required', 
            'alasan' => 'required', 
        ];
    public function index()
    {
        $bulan = Session::get('bulan');
        $tahun = Session::get('tahun');
        $id_karyawan_filter = Session::get('id_karyawan_filter');
        $id_departement_filter = Session::get('id_departement_filter');
        $karyawan = MKaryawan::withDeleted()->orderBy("nama_karyawan")->get();
        $departement = MDepartement::withDeleted()->orderBy("nama_departemen")->get();
        return view('izin-cuti.index',compact('karyawan'))
                ->with('departemen',$departement)
                ->with('bulan',$bulan)
                ->with('tahun',$tahun)
                ->with('id_karyawan_filter',$id_karyawan_filter)
                ->with('id_departement_filter',$id_departement_filter)
                ->with('title','Izin atau Cuti');
    }
    public function create($title_page = 'Tambah')
    {
        $tipeAbsensi = RefTipeAbsensi::withDeleted()->where('is_show','=',1)->get();
        // if (Auth::user()->id_role==1) {
        //     $roleJ = MapRoleJabatan::where('id_role',1)->get();
        //     $jabatan = [];
        //     foreach ($roleJ as $key) {
        //         $jabatan[]=$key->id_jabatan;
        //     }
        //     $karyawan = MKaryawan::withDeleted()->whereIn('id_jabatan', $jabatan)->get();
        // }elseif (Auth::user()->id_role==2) {
        //     $roleJ = MapRoleJabatan::where('id_role',2)->get();
        //     $jabatan = [];
        //     foreach ($roleJ as $key) {
        //         $jabatan[]=$key->id_jabatan;
        //     }
        //     $karyawan = MKaryawan::withDeleted()->whereIn('id_jabatan', $jabatan)->get();
        // }else {            
        //     $karyawan = MKaryawan::withDeleted()->where('id_karyawan',Auth::user()->id_karyawan)->get();
        // }
        $anggota = MapAnggota::select('id_karyawan')->where('id_submitter',Auth::user()->id_karyawan);
        if (Auth::user()->id_role == 1) {
            $karyawan = MKaryawan::withDeleted()->get();
        }else{            
            $karyawan = MKaryawan::where('id_karyawan',Auth::user()->id_karyawan)
                ->orWhereIn('id_karyawan', $anggota)
                ->get();
        }
        // dd($karyawan);
        $url = url('izin-cuti/create-save');
        return view('izin-cuti.form', compact('karyawan'))
            ->with('data',null)
            ->with('tipe_absensi',$tipeAbsensi)
            ->with('filterkar',null)
            ->with('id_departement_filter',null)
            ->with('title','Izin atau Cuti')
            ->with('titlePage',Lang::get('umum.tambah'))
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mCuti = new TIzinnCuti;
        $this->credentials($request,$mCuti);
        $mCuti->save();

        $period = new DatePeriod(
            new DateTime($request->tanggal_mulai),
            new DateInterval('P1D'),
            new DateTime($request->tanggal_selesai.' +1 days')
        );
            
        foreach($period as $key){
            $absensi = new TIzinDetail;
            $absensi->id_karyawan = $request->id_karyawan;
            $absensi->tanggal = $key->format('Y-m-d');            
            $absensi->id_izin = $mCuti->id_izin;
            $absensi->save();
        }

        $urutan = MRole::withDeleted()->where('id_role',Auth::user()->id_role)->first();
        $list_approval = MRole::withDeleted()
            ->where('urutan_approval_cuti','>=',$urutan->urutan_approval_cuti)
            ->groupBy('urutan_approval_cuti')
            ->orderBy('urutan_approval_cuti','ASC')
            ->get();
        foreach ($list_approval as $key => $value) {
            if ($key==0) {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 1;
                $appr->approve2_by = Auth::user()->id_user;
                $appr->approve_date = date('Y-m-d H:i:s');
                $appr->save();
            }elseif ($key==1) {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 0;
                $appr->approve2_by = 0;                
                $appr->save();
                $cekrole = MRole::where('id_role',$value->id_role)->first();
                if ($cekrole->kode_role == "asman" || $cekrole->kode_role == "manager") {
                    
                    $departemen = MKaryawan::select('id_departemen')->where('id_karyawan',$request->id_karyawan)->first();
                    $userA = MKaryawan::join('m_users','m_users.id_karyawan','=','m_karyawan.id_karyawan')                                
                                    ->where('m_karyawan.id_departemen',$departemen->id_departemen)
                                    ->where('m_users.id_role',$value->id_role)
                                    ->get(); 
                }else{
                    $userA = User::where('id_role',$value->id_role)->where('deleted',1)->get();                    
                }                   
                    $ref_notif = RefTemplateNotif::where('kode','approval_izin')->where('deleted',1)->first();
                    // dd($userA);
                    foreach ($userA as $values) {                    
                        $abs = RefTipeAbsensi::where('id_tipe_absensi',$request->id_tipe_absensi)->first();
                        $kar = MKaryawan::where('id_karyawan',$request->id_karyawan)->first();
                        $isi = $ref_notif->isi;
                        $isi = str_replace("{nama_karyawan}",$kar->nama_karyawan,$isi);
                        $isi = str_replace("{tipe_absensi}",$abs->nama_tipe_absensi,$isi);
                        $isi = str_replace("{tanggal_mulai}",$request->tanggal_mulai,$isi);
                        $isi = str_replace("{tanggal_selesai}",$request->tanggal_selesai,$isi);
                        $isi = str_replace("{alasan}",$request->alasan,$isi);
                        
                        $not = new Notif;
                        $not->id_user = $values->id_user;
                        $not->judul = $ref_notif->judul;
                        $not->url = "absensi/izin-cuti";
                        $not->isi = $isi;
                        $not->is_read = 0;
                        $not->deleted = 1;
                        $not->save();
    
                        $new = User::find($values->id_user);                    
                        $new->new_notif = $values->new_notif + 1;
                        $new->update();
    
                        // Mail::to($values->email)->send(new Email_notif($values->name,$ref_notif->judul,$isi,"absensi/izin-cuti"));
                    }
                
            }else {
                $appr = new MapApprIzin;
                $appr->id_izin = $mCuti->id_izin;
                $appr->id_role = $value->id_role;
                $appr->urutan = $value->urutan_approval_cuti;
                $appr->approval = 0;
                $appr->approve2_by = 0;                
                $appr->save();
            }
        }
        if (count($list_approval)==1) {

            $Cuti = TIzinnCuti::find($mCuti->id_izin);

            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->whereBetween('tanggal',[$Cuti->tanggal_mulai,$Cuti->tanggal_selesai])->update(['deleted' => 0]);
            
            // TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_tipe_absensi',$Cuti->id_tipe_absensi)->delete();
            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_izin',$Cuti->id_izin)->delete();
            
            
            $period = new DatePeriod(
                new DateTime($Cuti->tanggal_mulai),
                new DateInterval('P1D'),
                new DateTime($Cuti->tanggal_selesai.' +1 days')
            );
            
            foreach($period as $key){
                $absensi = new TAbsensi;
                $absensi->id_karyawan = $Cuti->id_karyawan;
                $absensi->tanggal = $key->format('Y-m-d');
                $absensi->id_tipe_absensi = $Cuti->id_tipe_absensi;
                $absensi->id_izin = $Cuti->id_izin;
                $absensi->save();
            }   
        }
        // $this->exAbsensi($mCuti);
        return redirect()->route('izin-cuti-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id,$filterkar,$id_departement_filter)
    {
        // dd(TIzinnCuti::find($id));
        $tipeAbsensi = RefTipeAbsensi::withDeleted()->where('is_show','=',1)->get();
        // if (Auth::user()->id_role==1) {
        //     $roleJ = MapRoleJabatan::where('id_role',1)->get();
        //     $jabatan = [];
        //     foreach ($roleJ as $key) {
        //         $jabatan[]=$key->id_jabatan;
        //     }
        //     $karyawan = MKaryawan::withDeleted()->whereIn('id_jabatan', $jabatan)->get();
        // }elseif (Auth::user()->id_role==2) {
        //     $roleJ = MapRoleJabatan::where('id_role',2)->get();
        //     $jabatan = [];
        //     foreach ($roleJ as $key) {
        //         $jabatan[]=$key->id_jabatan;
        //     }
        //     $karyawan = MKaryawan::withDeleted()->whereIn('id_jabatan', $jabatan)->get();
        // }else {            
        //     $karyawan = MKaryawan::withDeleted()->where('id_karyawan',Auth::user()->id_karyawan)->get();
        // }
        $anggota = MapAnggota::select('id_karyawan')->where('id_submitter',Auth::user()->id_karyawan);
        if (Auth::user()->id_role == 1) {
            $karyawan = MKaryawan::withDeleted()->get();
        }else{            
            $karyawan = MKaryawan::where('id_karyawan',Auth::user()->id_karyawan)
                ->orWhereIn('id_karyawan', $anggota)
                ->get();
        }
        return view('izin-cuti.form', compact('karyawan'))
            ->with('data',TIzinnCuti::find($id))
            ->with('title','Izin atau Cuti')
            ->with('titlePage','Edit')            
            ->with('tipe_absensi',$tipeAbsensi)
            ->with('filterkar',$filterkar)
            ->with('id_departement_filter',$id_departement_filter)
            ->with('url',url('izin-cuti/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),$this->validate);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mCuti = TIzinnCuti::find($id);
        $this->credentials($request,$mCuti);
        $mCuti->update();

        $map_appr = MapApprIzin::where('id_izin',$id)->get();
        foreach ($map_appr as $key => $value) {
            if ($key!=0) {
                $appr = MapApprIzin::where('id_izin',$id)->where('id_role',$value->id_role)->first();
                $appr->approval = 0;
                $appr->approve2_by = 0;
                $appr->approve_date = null;
                $appr->save(); 
            }
        }

        $period = new DatePeriod(
            new DateTime($request->tanggal_mulai),
            new DateInterval('P1D'),
            new DateTime($request->tanggal_selesai.' +1 days')
        );
        TIzinDetail::where('id_izin',)->delete();    
        foreach($period as $key){
            $absensi = new TIzinDetail;
            $absensi->id_karyawan = $request->id_karyawan;
            $absensi->tanggal = $key->format('Y-m-d');            
            $absensi->id_izin = $mCuti->id_izin;
            $absensi->save();
        }

        $tahun = Carbon::createFromFormat('d-m-Y',$request->tanggal_mulai)->format('Y');
        $bulan = Carbon::createFromFormat('d-m-Y',$request->tanggal_mulai)->format('m');

        Session::flash('bulan', $bulan); 
        Session::flash('tahun', $tahun); 
        Session::flash('id_karyawan_filter', $request->id_karyawan_filter);
        Session::flash('id_departement_filter', $request->id_departement_filter);

        return redirect()->route('izin-cuti-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {   $mCuti = TIzinnCuti::findOrFail($id);
        TAbsensi::where('id_karyawan',$mCuti->id_karyawan)->where('id_tipe_absensi',$mCuti->id_tipe_absensi)->where('id_izin',$mCuti->id_izin)->delete();
        $mCuti->destroyed($id);
        TIzinDetail::where('id_izin',)->delete();    
        return redirect()->route('izin-cuti-index')->with('msg','Sukses Menambahkan Data');

    }
    public function credentials(Request $request,$mCuti)
    {
        $id_karyawan = $request->id_karyawan;
        $id_tipe_absensi = $request->id_tipe_absensi;

        $mCuti->id_tipe_absensi = $id_tipe_absensi;
        $mCuti->id_karyawan = $id_karyawan;
        $mCuti->tanggal_mulai = date('Y-m-d',strtotime($request->tanggal_mulai));
        $mCuti->tanggal_selesai = date('Y-m-d',strtotime($request->tanggal_selesai));
        $mCuti->alasan = $request->alasan;
        $mCuti->approval = 0;
        $mCuti->approve_by = 0;
        $mCuti->created_by = 0;
        $mCuti->updated_by = 0;
        
    }
    public function exAbsensi($mCuti,$id_izin = false)
    {
        //ke t_absensi
        if($id_izin){
            TAbsensi::where('id_karyawan',$mCuti->id_karyawan)->where('id_tipe_absensi',$mCuti->id_tipe_absensi)->where('id_izin',$mCuti->id_izin)->delete();
        }else{
            TAbsensi::where('id_karyawan',$mCuti->id_karyawan)->where('id_tipe_absensi',$mCuti->id_tipe_absensi)->delete();
        }
        $period = new DatePeriod(
            new DateTime($mCuti->tanggal_mulai),
            new DateInterval('P1D'),
            new DateTime($mCuti->tanggal_selesai.' +1 days')
        );
        foreach($period as $key){
            $absensi = new TAbsensi;
            $absensi->id_karyawan = $mCuti->id_karyawan;
            $absensi->tanggal = $key->format('Y-m-d');
            $absensi->id_tipe_absensi = $mCuti->id_tipe_absensi;
            $absensi->id_izin = $mCuti->id_izin;
            $absensi->save();
        }
    }
    public function datatable()
    {
        $start_date = (!empty($_GET["start_date"])) ? ($_GET["start_date"]) : ('');
        $end_date = (!empty($_GET["end_date"])) ? ($_GET["end_date"]) : ('');
        $departement = (!empty($_GET["departement"])) ? ($_GET["departement"]) : 0;
        $filterkar = (!empty($_GET["filterkar"])) ? ($_GET["filterkar"]) : 0;        
        $departemen = MKaryawan::find(Auth::user()->id_karyawan);
        $rol = MRole::find(Auth::user()->id_role);
        // dd($departement."--".$filterkar);
        if($start_date && $end_date){
            $model = TIzinnCuti::select("t_izin.*",'m_karyawan.nama_karyawan','m_karyawan.id_departemen','ref_tipe_absensi.nama_tipe_absensi')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_izin.id_karyawan','left')
            ->join('ref_tipe_absensi','ref_tipe_absensi.id_tipe_absensi','=','t_izin.id_tipe_absensi','left')
            ->where('t_izin.deleted',1)
            ->whereMonth('t_izin.tanggal_mulai',$start_date)
            ->whereYear('t_izin.tanggal_mulai',$end_date);
            if ($rol->kode_role == "leader") {
                $anggota = MapAnggota::where('id_submitter',Auth::user()->id_karyawan)->pluck('id_karyawan')->toArray();
                $sendiri = Auth::user()->id_karyawan;
                array_push($anggota, $sendiri);                
                $model = $model->whereIn('m_karyawan.id_karyawan',$anggota);
            }
            if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {                
                $model = $model->where('m_karyawan.id_departemen',$departemen->id_departemen);
            }
            if ($departement) {
                $model = $model->where('m_karyawan.id_departemen',$departement);
            }
            if ($filterkar) {
                $model = $model->where('m_karyawan.id_karyawan',$filterkar);                
            }
        }else{
            $model = TIzinnCuti::select("t_izin.*",'m_karyawan.nama_karyawan','ref_tipe_absensi.nama_tipe_absensi')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_izin.id_karyawan','left')
            ->join('ref_tipe_absensi','ref_tipe_absensi.id_tipe_absensi','=','t_izin.id_tipe_absensi','left')
            ->where('t_izin.deleted',1);
            if ($rol->kode_role == "leader") {
                $anggota = MapAnggota::where('id_submitter',Auth::user()->id_karyawan)->pluck('id_karyawan')->toArray();
                $sendiri = Auth::user()->id_karyawan;
                array_push($anggota, $sendiri);
                $model = $model->whereIn('m_karyawan.id_karyawan',$anggota);
            }
            if ($rol->kode_role == "asman" || $rol->kode_role == "manager") {                
                $model = $model->where('m_karyawan.id_departemen',$departemen->id_departemen);
            }
            if ($departement != 1) {
                $model = $model->where('m_karyawan.id_departemen',$departement);
            }
            if ($filterkar) {
                $model = $model->where('m_karyawan.id_karyawan',$filterkar);                
            }
        }
        
        // dd($model->get());
        return DataTables::of($model)
            ->addColumn('action', function ($row) use ($filterkar,$departement) {
                return $this->cek_action($row->id_izin,Auth::user()->id_role,$row->id_karyawan,$filterkar,$departement);
            })
            ->addColumn('approval',function ($row) {
                return $this->cek_appr($row->id_izin);
            })
            ->editColumn('tanggal_mulai',function ($row) {
                $html = $this->convertDate($row->tanggal_mulai,true,false);
                return $html;
            })
            ->editColumn('tanggal_selesai',function ($row) {
                $html = $this->convertDate($row->tanggal_selesai,true,false);
                return $html;
            })
            ->rawColumns(['action','approval'])
            ->addIndexColumn()
            ->toJson();
    }
    public function get_karyawan()
    {
        $q = (isset($_GET['q'])) ? $_GET['q'] : "";
        $karyawan = MKaryawan::withDeleted()->where('nama_karyawan','like','%'.$q.'%')->limit(10)->get(['id_karyawan','nama_karyawan']);
        return response()->json(['status'=>true,'data'=>$karyawan]);
    }
    public function karyawanBydepartemen()
    {
        $departement = (!empty($_GET["departement"])) ? ($_GET["departement"]) : ('');
        // dd($departement);
        $karyawan = MKaryawan::withDeleted()->where('id_departemen',$departement)->get();        
        $data ="<option value='' selected disabled>Pilih Karyawan</option><option value='0'>Semua</option>";
        foreach ($karyawan as $key) {
            $data .="<option value='".$key->id_karyawan."' >".$key->nama_karyawan."</option>";
        }
        return response()->json(['status'=>true,'data'=>$data]);
    }
    public function persetujuan(Request $request)
    {
        $id = $request->id;
        $id_role = $request->id_role;
        $id_karyawan = $request->id_karyawan;
        $konfrim = $request->konfrim;
        $user = User::where('id_karyawan',$request->id_karyawan)->where('deleted',1)->first();
        $rol_appro = MRole::find($id_role);
        if ($user) {            
            $rol = MRole::find($user->id_role);
            $rol = $rol->kode_role;
        }else{
            $rol = "tidak_ada_role";
        }
        
        $urut=MapApprIzin::where('id_izin',$id)->orderBy('urutan', 'desc')->first();
        if ($rol != 'manager' && $rol_appro->kode_role == "gm") {
            $appr = MapApprIzin::where('id_izin',$id)->where('approval',0)->update(['approval'=>$konfrim,'approve2_by'=>Auth::user()->id_user,'approve_date'=>date('Y-m-d H:i:s')]);
            // $appr->approval = $konfrim;
            // $appr->approve2_by = Auth::user()->id_user;
            // $appr->approve_date = date('Y-m-d H:i:s');
            // $appr->save();   

            $Cuti = TIzinnCuti::find($id);

            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->whereBetween('tanggal',[$Cuti->tanggal_mulai,$Cuti->tanggal_selesai])->update(['deleted' => 0]);
            
            // TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_tipe_absensi',$Cuti->id_tipe_absensi)->delete();
            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_izin',$Cuti->id_izin)->delete();
            
            
            $period = new DatePeriod(
                new DateTime($Cuti->tanggal_mulai),
                new DateInterval('P1D'),
                new DateTime($Cuti->tanggal_selesai.' +1 days')
            );
            
            foreach($period as $key){
                $absensi = new TAbsensi;
                $absensi->id_karyawan = $Cuti->id_karyawan;
                $absensi->tanggal = $key->format('Y-m-d');
                $absensi->id_tipe_absensi = $Cuti->id_tipe_absensi;
                $absensi->id_izin = $Cuti->id_izin;
                $absensi->save();
            }
        }elseif ($urut->id_role == $id_role && $konfrim == 1) {

            $appr = MapApprIzin::where('id_izin',$id)->where('id_role',$id_role)->first();
            $appr->approval = $konfrim;
            $appr->approve2_by = Auth::user()->id_user;
            $appr->approve_date = date('Y-m-d H:i:s');
            $appr->save();   

            $Cuti = TIzinnCuti::find($id);

            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->whereBetween('tanggal',[$Cuti->tanggal_mulai,$Cuti->tanggal_selesai])->update(['deleted' => 0]);
            
            // TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_tipe_absensi',$Cuti->id_tipe_absensi)->delete();
            TAbsensi::where('id_karyawan',$Cuti->id_karyawan)->where('id_izin',$Cuti->id_izin)->delete();
            
            
            $period = new DatePeriod(
                new DateTime($Cuti->tanggal_mulai),
                new DateInterval('P1D'),
                new DateTime($Cuti->tanggal_selesai.' +1 days')
            );
            
            foreach($period as $key){
                $absensi = new TAbsensi;
                $absensi->id_karyawan = $Cuti->id_karyawan;
                $absensi->tanggal = $key->format('Y-m-d');
                $absensi->id_tipe_absensi = $Cuti->id_tipe_absensi;
                $absensi->id_izin = $Cuti->id_izin;
                $absensi->save();
            }
        }else{
            $appr = MapApprIzin::where('id_izin',$id)->where('id_role',$id_role)->first();
            $appr->approval = $konfrim;
            $appr->approve2_by = Auth::user()->id_user;
            $appr->approve_date = date('Y-m-d H:i:s');
            $appr->save();   

            $urut_role=MRole::where('id_role',$id_role)->first();
            $urut = $urut_role->urutan_approval_cuti+1;
            $role = MRole::where('urutan_approval_cuti',$urut)->first();
            $cekrole = MRole::where('id_role',$role->id_role)->first();
            if ($cekrole->kode_role == "asman" || $cekrole->kode_role == "manager") {                    
                $departemen = MKaryawan::select('id_departemen')->where('id_karyawan',$request->id_karyawan)->first();
                $user = MKaryawan::join('m_users','m_users.id_karyawan','=','m_karyawan.id_karyawan')                                
                                ->where('m_karyawan.id_departemen',$departemen->id_departemen)
                                ->where('m_users.id_role',$role->id_role)
                                ->get(); 
            }else{
                // $userA = User::where('id_role',$value->id_role)->where('deleted',1)->get();                    
                $user = User::where('id_role',$role->id_role)->where('deleted',1)->get();
            }
            $ref_notif = RefTemplateNotif::where('kode','approval_izin')->where('deleted',1)->first();
            $izin = TIzinnCuti::where('id_izin',$id)->first();
                foreach ($user as $values) {
                    $abs = RefTipeAbsensi::where('id_tipe_absensi',$izin->id_tipe_absensi)->first();
                    $kar = MKaryawan::where('id_karyawan',$izin->id_karyawan)->first();
                    $isi = $ref_notif->isi;
                    $isi = str_replace("{nama_karyawan}",$kar->nama_karyawan,$isi);
                    $isi = str_replace("{tipe_absensi}",$abs->nama_tipe_absensi,$isi);
                    $isi = str_replace("{tanggal_mulai}",$izin->tanggal_mulai,$isi);
                    $isi = str_replace("{tanggal_selesai}",$izin->tanggal_selesai,$isi);
                    $isi = str_replace("{alasan}",$izin->alasan,$isi);
                    
                    $not = new Notif;
                    $not->id_user = $values->id_user;
                    $not->judul = $ref_notif->judul;
                    $not->url = "absensi/izin-cuti";
                    $not->isi = $isi;
                    $not->is_read = 0;
                    $not->deleted = 1;
                    $not->save();

                    $new = User::find($values->id_user);                    
                    $new->new_notif = $values->new_notif + 1;
                    $new->update();
                    
                    // Mail::to($values->email)->send(new Email_notif($values->name,$ref_notif->judul,$isi,"absensi/izin-cuti"));
                }
        }        
        return response()->json(['success'=>'update successfully.']);
    }

    public function get_tipe_absensi($id)
    {   
        $tipe = RefTipeAbsensi::find($id);
        if ($tipe->tipe_batas_waktu==1) {
            $now = date("d-m-Y");
            $beda = $tipe->batas_waktu;
            $data['awal']= date('d-m-Y', strtotime($now.' -'.$beda.' Weekday'));
            $data['akhir']= date('d-m-Y', strtotime($now.' -1 Weekday'));
        }else {
            $now = date("d-m-Y");
            $beda = $tipe->batas_waktu+1;
            $data['awal']= date('d-m-Y', strtotime($now.' +'.$beda.' day'));
            $data['akhir']= date('d-m-Y', strtotime($now.' +'.$beda.' day'));
        }

        return response()->json($data);
    }

    public function cek_appr($id)
    {   
        $html="";
        $map=MapApprIzin::where('id_izin',$id)->where('urutan','!=',0)->get();
        foreach ($map as $key) {
            $role = MRole::where('id_role',$key->id_role)->first();
            if ($key->approval==0) {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-warning">'.Lang::get('umum.menunggu').'</span></p>';
            }elseif ($key->approval==1) {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-success">'.Lang::get('umum.setuju').'</span></p>';
            }else {
                $html .='<p style="white-space:nowrap;">'.$role->nama_role.' : <span class="text-danger">'.Lang::get('umum.tolak').'</span></p>';
            }
        }
        return $html;
    }
    public function cek_action($id,$id1,$id2,$filterkar,$departement)
    {   
        $html="";
        $map=MapApprIzin::where('id_izin',$id)->where('id_role',$id1)->first();
        $tolak=MapApprIzin::where('id_izin',$id)->where('approval','=',2)->get()->count();
        if ($tolak!=0) {
            if (Helper::can_akses('absensi_izincuti_delete')) {
                $html .= '<a href="'.url('izin-cuti/delete/'.$id).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
            }
            if (Helper::can_akses('absensi_izincuti_edit')) {                
                $html .= '<a href="'.url('izin-cuti/show/'.$id.'/'.$filterkar.'/'.$departement).'" class="text-danger mr-2"><span class="mdi mdi-pen"></span></a>';
            }
        }else{
            if ($map) {
                $urut=MapApprIzin::where('id_izin',$id)->where('approval',1)->orderBy('urutan', 'desc')->first();
                $urut_role=MRole::where('id_role',$id1)->first();
                if ($urut_role->urutan_approval_cuti == $urut->urutan+1 && $map->approval==0) {
                    $html .= '<a href="'.url('izin-cuti/delete/'.$id).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                    $html .= '<a href="'.url('izin-cuti/show/'.$id.'/'.$filterkar.'/'.$departement).'" class="text-danger mr-2"><span class="mdi mdi-pen"></span></a>';
                    $html .= '<a href="javascript:void(0)" data-toggle="modal" data-id="'.$id.'" data-role="'.$id1.'" data-karyawan="'.$id2.'" data-original-title="Edit" class="text-success editPost"><span class="mdi mdi-check"></span></a>';
                }else {
                    $cekAtasan=MapApprIzin::where('id_izin',$id)->where('urutan','>',0);
                    // dd($cekAtasan->where('approval',1)->count().'-'.$cekAtasan->where('approval',2)->count());
                    if ($cekAtasan->where('approval',1)->count() > 0 && $cekAtasan->where('approval',2)->count() <= 0) {
                        $html='';
                    }else {                        
                        if (Helper::can_akses('absensi_izincuti_delete')) {
                            $html .= '<a href="'.url('izin-cuti/delete/'.$id).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                        }
                        if (Helper::can_akses('absensi_izincuti_edit')) {                
                            $html .= '<a href="'.url('izin-cuti/show/'.$id.'/'.$filterkar.'/'.$departement).'" class="text-danger mr-2"><span class="mdi mdi-pen"></span></a>';
                        }
                    }
                }
            }
        }
        return $html;
    }

    public function get_jumlah_izin($id_karyawan)
    {   
        $kar = MKaryawan::find($id_karyawan);        
        $izin = TIzinDetail::where('id_karyawan',$id_karyawan)->whereYear('tanggal',date('Y'))->count();
        $total = $kar->max_izin - $izin;
        return response()->json($total);
    }
}
