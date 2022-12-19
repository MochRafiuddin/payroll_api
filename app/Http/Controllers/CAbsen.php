<?php

namespace App\Http\Controllers;

use App\Imports\ImportPriviewAbsensi;
use App\Models\MKaryawan;
use App\Models\MShift;
use App\Models\MShiftGrup;
use App\Models\MShiftKaryawan;
use App\Models\MTarifLembur;
use App\Models\RefTipeAbsensi;
use App\Models\TAbsensi;
use App\Models\LogAbsensi;
use App\Models\TReportAbsensi;
use App\Models\TReportAbsensiDetail;
use App\Models\TLembur;
use App\Models\MGrupKaryawan;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use DateInterval;
use DatePeriod;
use DateTime;
use Auth;
use Illuminate\Support\Facades\Response;
use Excel;
use Facade\Ignition\Tabs\Tab;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CAbsen extends Controller
{
    use Helper;
    public $mShiftKaryawan = null;
    public $mTarifLembur = null;
    public $mShiftGrup = null;
    public $mGrupKaryawan = null;
    public $mKaryawan = null;
    public function index()
    {
        $karyawan = MKaryawan::withDeleted()->get();
        return view('absen.index')->with('title','Absen')->with('karyawan',$karyawan);
    }
    public function datatable($tanggal,$karyawan)
    {
        //  $query = DB::select("select a.*,b.nama_shift from t_absensi a, m_shift b where a.id_shift = b.id_shift and a.deleted = 1 and a.tanggal between order by a.tanggal asc");
         $query = DB::table('t_absensi')->select('t_absensi.*','m_shift.nama_shift','m_karyawan.nama_karyawan','m_karyawan.nik')
                                ->join('m_karyawan','m_karyawan.id_karyawan','=','t_absensi.id_karyawan','left')
                                ->join('m_shift','m_shift.id_shift','=','t_absensi.id_shift','left')
                                ->where('tanggal',date('Y-m-d',strtotime($tanggal)));
                                if($karyawan != 0){
                                    $query = $query->where('t_absensi.id_karyawan',$karyawan);
                                }
                                $query = $query->orderBy('tanggal','asc');
        //  dd($query->get());
        return DataTables::of($query)
            ->addIndexColumn()
            ->toJson();
    }
    public function import()
    {
        $karyawan = MKaryawan::select('m_karyawan.*','m_shift.*')
                        ->join('m_shift','m_shift.id_shift','=','m_karyawan.id_shift','left')
                        ->where('m_karyawan.deleted',1)
                        ->orderBy('m_karyawan.nama_karyawan','asc')
                        ->get();
        $mShiftKaryawan = MShiftKaryawan::withDeleted()->get();
        $mShift = MShift::withDeleted()->get();
        $title_page = "Import Absensi";
        return view('absen.form_import')
            ->with('titlePage',$title_page)
            ->with('title','Absen')
            ->with('karyawan',$karyawan)
            ->with('shift',$mShift)
            ->with('shift_karyawan',$mShiftKaryawan);
    }
    public function priview_import(Request $request)
    {
        // validasi
        // dd(date('Y-m-d',strtotime("6/02/2022 03:00:00 PM")));
        $validator = Validator::make($request->all(),[
            'file_excel' => 'required|mimes:csv,xls,xlsx'
        ]);
        if ($validator->fails()) {
            return response()->json(['status'=>false,'msg'=>"File Excel belum di pilih"]);
        }
        // menangkap file excel
        $file = $request->file('file_excel');

        $import = new ImportPriviewAbsensi();

        Excel::import($import,$file);
        $dataExcel = $import->getData();
        // dd($dataExcel);

        foreach($dataExcel as $key){
            $result = $this->cekKodeFingerPrint($key['kode']);
            if(!$result){
                // dd($result." ".$key['kode']);
                return response()->json(['status'=>false,'msg'=>"Kode Finger Print -> <b>".$key['kode']."</b> tidak di temukan"]);
            }
        }
        // dd($dataExcel);
        return response()->json(['status'=>true,'data'=>$dataExcel]);
    }
    public function get_log_absensi(Request $request)
    {
        $dataExcel = LogAbsensi::from('log_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b',DB::raw('0'),'=','b.id_tipe_absensi')
                        ->select('a.*',DB::raw('0 as id_tipe_absensi'),'b.nama_tipe_absensi');
        if ($request->data == 0) {            
            $dataExcel = $dataExcel->where('a.imported','0');
        }
        if ($request->karyawan != 0) {
          $dataExcel = $dataExcel->where('a.id_karyawan',$request->karyawan);
        }

        $arr_id_karyawan = $dataExcel->pluck('a.id_karyawan')->toArray();
        
        $data_absensi = TAbsensi::from('t_absensi as a')
        ->leftJoin('m_karyawan as b','a.id_karyawan','b.id_karyawan')
        ->leftJoin('ref_tipe_absensi as c','a.id_tipe_absensi','=','c.id_tipe_absensi')
        ->select(DB::raw('0 as id,b.kode_fingerprint as kode,a.id_karyawan as id_karyawan,b.nik,b.nama_karyawan,0 as id_shift,a.tanggal as tanggal_shift,null as jam_masuk_shift,null as jam_keluar_shift,null as waktu_masuk,null as waktu_keluar,0 as imported,a.id_tipe_absensi,c.nama_tipe_absensi'))
        ->whereIn('a.id_karyawan',array_unique($arr_id_karyawan))
        ->where('a.id_tipe_absensi','<>','1')->where('a.deleted','1');

        if ($request->start_date && $request->end_date) {
            $start_date = Carbon::CreateFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
            $end_date = Carbon::CreateFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
            $dataExcel->whereBetween('a.tanggal_shift',[$start_date,$end_date]);
        }

        if ($request->start_date && $request->end_date) {
            $data_absensi->whereBetween('a.tanggal',[$start_date,$end_date]);
        }

        $dataExcel->union($data_absensi);
        $dataExcel = $dataExcel->orderBy('id_karyawan','asc')->orderBy('tanggal_shift','asc')->get()->toArray();
        // dd($data_absensi->get());
        foreach($dataExcel as $key){
            $result = $this->cekKodeFingerPrint($key['kode']);
            if(!$result){
                // dd($result." ".$key['kode']);
                return response()->json(['status'=>false,'msg'=>"Kode Finger Print -> <b>".$key['kode']."</b> tidak di temukan"]);
            }
        }
        // dd($dataExcel);
        return response()->json(['status'=>true,'data'=>$dataExcel]);
    }
    public function add_log_absensi(Request $request)
    {
        $this->mKaryawan = mKaryawan::withDeleted()->get();
        $this->mShiftKaryawan = MShiftKaryawan::from('m_shift_karyawan as a')
                            ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                            ->where('id_karyawan',$request->id_karyawan)
                            ->where('a.deleted','1')
                            ->get(['a.id_karyawan','a.id_shift','a.tanggal','b.jam_masuk','b.jam_keluar','b.nama_shift']);
        $waktu_masuk = Carbon::CreateFromFormat('d/m/Y H:i', $request->waktu_masuk)->format('Y-m-d H:i:s');
        $waktu_keluar = Carbon::CreateFromFormat('d/m/Y H:i', $request->waktu_keluar)->format('Y-m-d H:i:s');

        $arr_insert = [
            'kode' => $this->getKaryawan($request->id_karyawan)->kode_fingerprint,
            'id_karyawan' => $request->id_karyawan,
            'nik' => $this->getKaryawan($request->id_karyawan)->nik,
            'nama_karyawan' => $this->getKaryawan($request->id_karyawan)->nama_karyawan,
            'id_shift' => $request->id_karyawan,
            'tanggal_shift' => Carbon::CreateFromFormat('d-m-Y', $request->tanggal_shift)->format('Y-m-d'),
            'jam_masuk_shift' => $this->getMsiftKaryawan($request->id_karyawan, $request->tanggal_shift)->jam_masuk ?? $waktu_masuk,
            'jam_keluar_shift' => $this->getMsiftKaryawan($request->id_karyawan, $request->tanggal_shift)->jam_keluar ?? $waktu_keluar,
            'waktu_masuk' => $waktu_masuk,
            'waktu_keluar' => $waktu_keluar,
        ];
        
        LogAbsensi::insert($arr_insert);
        // dd($dataExcel);
        return redirect()->back();
    }
    public function edit_log_absensi(Request $request)
    {
        $id = $request->id;
        $dataExcel = LogAbsensi::find($id);
        $dataExcel->waktu_masuk = $dataExcel->waktu_masuk ? Carbon::createFromFormat('Y-m-d H:i:s',$dataExcel->waktu_masuk)->format('d/m/Y H:i') : '';
        $dataExcel->waktu_keluar = $dataExcel->waktu_keluar ? Carbon::createFromFormat('Y-m-d H:i:s',$dataExcel->waktu_keluar)->format('d/m/Y H:i') : '';
        $dataExcel->tanggal_shift = Carbon::createFromFormat('Y-m-d',$dataExcel->tanggal_shift)->format('d-m-Y');

        return response()->json(['status'=>true,'data'=>$dataExcel]);
    }
    public function update_log_absensi(Request $request)
    {
        $id = $request->id;
        $tanggal_shift = Carbon::createFromFormat('d-m-Y',$request->edit_tanggal_shift)->format('Y-m-d');
        $id_shift = $request->edit_id_shift;
        $waktu_masuk = Carbon::createFromFormat('d/m/Y H:i',$request->edit_waktu_masuk)->format('Y-m-d H:i:s');
        $waktu_keluar = Carbon::createFromFormat('d/m/Y H:i',$request->edit_waktu_keluar)->format('Y-m-d H:i:s');
        $data_shift = mShift::find($id_shift);

        $dataExcel = LogAbsensi::find($id)->update(['waktu_masuk'=>$waktu_masuk,'waktu_keluar'=>$waktu_keluar,'tanggal_shift'=>$tanggal_shift,'id_shift'=>$id_shift,'jam_masuk_shift'=>$data_shift->jam_masuk,'jam_keluar_shift'=>$data_shift->jam_keluar]);
        
        $log=LogAbsensi::find($id);

        $updateMShifKaryawan = MShiftKaryawan::where('id_karyawan',$log->id_karyawan)->where('tanggal',$tanggal_shift)->update(['id_shift'=>$id_shift]);

        return response()->json(['status'=>true]);
    }
    public function delete_log_absensi(Request $request)
    {
        $id = $request->id;
        LogAbsensi::where('id',$id)->delete();

        return response()->json(['status'=>true]);
    }
    public function clear_log_absensi()
    {
        $dataExcel = LogAbsensi::truncate();

        return redirect()->back();
    }
    public function cekKodeFingerPrint($value)
    {
        $mkaryawan = MKaryawan::withDeleted()->pluck('kode_fingerprint');
       
        foreach($mkaryawan as $kar){
            
            if(strval($value) == $kar){
                
                return true;
            }
        }
        return false;

    }
    public function save_import(Request $request)
    {
        $data_json = $request->json_import;
        $fix_data = LogAbsensi::whereIn('id',$data_json)->get();
        $data_json = $fix_data;

        $this->mShiftKaryawan = MShiftKaryawan::withDeleted()->get(['id_karyawan','id_shift','tanggal']);
        $this->mShift = MShift::withDeleted()->pluck('id_shift');
        $this->mShiftGrup = MShiftGrup::withDeleted()->get();
        $this->mGrupKaryawan = mGrupKaryawan::withDeleted()->get();
        $this->mKaryawan = mKaryawan::withDeleted()->get();
        $this->mTarifLembur = MTarifLembur::withDeleted()->get();
        $arr_ins_t_absensi = [];
        $arr_t_lembur_ins=[];
        $arr_id_log_absensi=[];
        foreach($data_json as $key){
            TAbsensi::where('id_karyawan',$key['id_karyawan'])->where('tanggal',$key['tanggal_shift'])->delete();
            TLembur::where('id_karyawan',$key['id_karyawan'])->where('tanggal',$key['tanggal_shift'])->update(['deleted'=>0]);
            // TLembur::where('id_karyawan',$key['id_karyawan'])->where('tanggal',$key['tanggal_shift'])->delete();
            $jam_masuk = $key['jam_masuk_shift'] ?? Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->format('H:i:s');
            $jam_keluar = $key['jam_keluar_shift'] ?? Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_keluar'])->format('H:i:s');

            $jam_masuk_from_m_shift = Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->format('Y-m-d').' '.$jam_masuk;
            if ($key['id_shift'] == 4) { // jika shift malam, tanggal shift masuk dan keluar harinya beda.
                $jam_keluar_from_m_shift = Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->addDays(1)->format('Y-m-d').' '.$jam_keluar;
            }else{
                $jam_keluar_from_m_shift = Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->format('Y-m-d').' '.$jam_keluar;
            }
            $waktu_absen_masuk = Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->format('Y-m-d H:i:s');
            $waktu_absen_keluar =  Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_keluar'])->format('Y-m-d H:i:s');
            // dd($jam_masuk_from_m_shift,$jam_keluar_from_m_shift);
            $total_jam_kerja = Carbon::createFromFormat("Y-m-d H:i:s", $jam_masuk_from_m_shift)->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $jam_keluar_from_m_shift));
            // $tanggal_masuk_shift = date("Y-m-d H:i:s",strtotime($jam_masuk_from_m_shift));
            $tanggal_masuk_shift = $jam_masuk_from_m_shift;
            // dd($jam_masuk_from_m_shift.' - '.$jam_keluar_from_m_shift);
            // dd($waktu_absen_masuk.' - '.$waktu_absen_keluar);
            // $tanggal_keluar_shift = date("Y-m-d H:i:s",strtotime($jam_keluar_from_m_shift));
            $tanggal_keluar_shift = $jam_keluar_from_m_shift;
            $total_jam_kerja = 0;
            $total_jam_karyawan_bekerja = 0;
            $menit_lembur = 0;
            $hitung_terlembat = 0;
            $hitung_early_leave = 0;

            // if($tanggal_masuk == $tanggal_keluar){
            //     $total_jam_kerja =  $this->getMinutes(strtotime($jam_keluar_from_m_shift)) - $this->getMinutes(strtotime($jam_masuk_from_m_shift));
            //     // dd($jam_masuk_from_m_shift." ".$jam_keluar_from_m_shift);
            // }else{
            //     $total_jam_kerja =  $this->getMinutes(strtotime("2020-01-02 ".$jam_keluar_from_m_shift)) - $this->getMinutes(strtotime("2020-01-01 ".$jam_masuk_from_m_shift));
            // }
            
            // $total_jam_karyawan_bekerja = $this->getMinutes(strtotime($waktu_absen_keluar)) - $this->getMinutes(strtotime($waktu_absen_masuk));
            $total_jam_karyawan_bekerja = Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_masuk'])->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $key['waktu_keluar']));
        // dd($total_jam_kerja,$total_jam_karyawan_bekerja);
            // $total_jam_karyawan_bekerja = $this->getMinutes(strtotime('2022-06-12 1:00:00 AM')) - $this->getMinutes(strtotime('2022-06-11 11:00:00 PM'));
            // dd($total_jam_karyawan_bekerja);
            
            
            $tanggal_masuk = $waktu_absen_masuk;
            $jam_masuk = $jam_masuk_from_m_shift;
            $jam_karyawan_masuk = $waktu_absen_masuk;

            $tanggal_keluar = $waktu_absen_keluar;
            $jam_keluar = $jam_keluar_from_m_shift;
            $jam_karyawan_keluar = $waktu_absen_keluar;

            // $hitung_terlembat = strtotime($jam_masuk_from_m_shift) - strtotime($jam_masuk);
            if ($jam_masuk < $jam_karyawan_masuk) {
                $hitung_terlembat = Carbon::createFromFormat("Y-m-d H:i:s", $jam_masuk)->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $jam_karyawan_masuk));
            }

            if ($jam_keluar > $jam_karyawan_keluar) {
                $hitung_early_leave = Carbon::createFromFormat("Y-m-d H:i:s", $jam_keluar)->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $jam_karyawan_keluar));
            }
            // dd($key['jam_masuk_shift'].' - '.date('H:i:s',strtotime($waktu_absen_masuk)));
            // $menit_lembur = abs($total_jam_karyawan_bekerja) - abs($total_jam_kerja);

            if (!$key['jam_masuk_shift'] && !$key['jam_keluar_shift']) {    //jika lembur pada hari libur
                $menit_lembur += $total_jam_karyawan_bekerja;
            }else{  //jika lembur pada hari kerja
                if ($tanggal_masuk < $tanggal_masuk_shift) {
                    $menit_lembur += Carbon::createFromFormat("Y-m-d H:i:s", $tanggal_masuk_shift)->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $tanggal_masuk));
                }
                // dd($menit_lembur);
                if ($tanggal_keluar > $tanggal_keluar_shift) {
                    $menit_lembur += Carbon::createFromFormat("Y-m-d H:i:s", $tanggal_keluar)->diffInMinutes(Carbon::createFromFormat("Y-m-d H:i:s", $tanggal_keluar_shift));
                }
            }
            // dd('masuk '.$menit_lembur);
           
            $shiftKaryawan = $this->getMsiftKaryawan($key['id_karyawan'],$key['tanggal_shift']);

           $arr_jam_lembur = [];

           $length = floor($menit_lembur / 60);
           $mod = (($menit_lembur % 60) / 60) >= 0.5 ? 0.5 : 0;
           // dd($length.' - '.$mod.' - '.$menit_lembur);
           $jam_ke = 0;
           if ($length > 0) {
               for ($i=0; $i < $length; $i++) { 
                   $arr_jam_lembur[] = ['jam_ke'=>$i+1, 'jumlah_jam'=>$jam_ke+1];
               }
               if ($mod) {
                   $arr_jam_lembur[] = ['jam_ke'=> intval($length+1), 'jumlah_jam'=>$mod];
               }
           }
           // dd($arr_jam_lembur);

           if (count($arr_jam_lembur) > 0) {
                if (count($arr_jam_lembur) > 0) {
                    for ($i=0; $i < count($arr_jam_lembur); $i++) { 
                        $indexKerja = 0;
                        if(!is_null($shiftKaryawan)){
                            if($shiftKaryawan->id_shift != 1){ // hari kerja
                                // cek ke m_tarif_lembur, jika jam == 1 jam maka pakai rate_hari_kerja 
                                //jika jam > 1 && jam <= 8 maka rate_hari_kerja 
                                //jika jam > 8 jam maka maka rate_hari_kerja 
                                $indexKerja = $this->getIndexLembur($arr_jam_lembur[$i]['jam_ke'],$shiftKaryawan->id_shift,$key['tanggal_shift'],$shiftKaryawan->id_karyawan);
                                $arr_t_lembur_ins[] = [
                                    'id_karyawan'=>$key['id_karyawan'],
                                    'id_tarif_lembur'=>$indexKerja['id_tarif_lembur'],
                                    'index_tarif'=>$indexKerja['rate'],
                                    'tipe_hari'=>0,
                                    'tanggal'=>$key['tanggal_shift'],
                                    'jumlah_jam'=>$arr_jam_lembur[$i]['jumlah_jam'],
                                    'approval'=>0,
                                    'approve_by'=>0,
                                    'created_by'=>Auth::user()->id_user,
                                    'updated_by'=>0,
                                ];
                            }else{  // hari libur
                                // cek ke m_tarif_lembur, jika jam == 1 jam maka pakai rate_hari_kerja 
                                //jika jam > 1 && jam <= 8 maka rate_hari_kerja 
                                //jika jam > 8 jam maka maka rate_hari_kerja 
                                $indexKerja = $this->getIndexLembur($arr_jam_lembur[$i]['jam_ke'],$shiftKaryawan->id_shift,$key['tanggal_shift'],$shiftKaryawan->id_karyawan);
                                $arr_t_lembur_ins[] = [
                                    'id_karyawan'=>$key['id_karyawan'],
                                    'id_tarif_lembur'=>$indexKerja['id_tarif_lembur'],
                                    'index_tarif'=>$indexKerja['rate'],
                                    'tipe_hari'=>1,
                                    'tanggal'=>$key['tanggal_shift'],
                                    'jumlah_jam'=>$arr_jam_lembur[$i]['jumlah_jam'],
                                    'approval'=>0,
                                    'approve_by'=>0,
                                    'created_by'=>Auth::user()->id_user,
                                    'updated_by'=>0,
                                ];
                            }
                        }      
                    }
                }
           }

            $arr_ins_t_absensi[] = [
                'id_karyawan' => $key['id_karyawan'],
                'tanggal' => $key['tanggal_shift'],
                'tanggal_masuk' => $tanggal_masuk,
                'tanggal_keluar' => $tanggal_keluar,
                'id_shift' => $key['id_shift'],
                'jam_masuk_shift' => $jam_masuk,
                'jam_keluar_shift' => $jam_keluar,
                'menit_lembur' => $menit_lembur,
                'menit_terlambat' => $hitung_terlembat,
                'menit_early_leave' => $hitung_early_leave,
            ];
            $arr_id_log_absensi[] = $key['id'];
            
        }
        
        TAbsensi::insert($arr_ins_t_absensi);
        if (count($arr_t_lembur_ins) > 0) {
            // dd($arr_t_lembur_ins);
            TLembur::insert($arr_t_lembur_ins);
        }

        LogAbsensi::whereIn('id',$arr_id_log_absensi)->update(['imported'=>'1']);
        
        return response()->json(['status'=>true,'msg'=>"Sukses Import Absensi"]);
        
    }
    public function getMsiftKaryawan($id_karyawan,$tanggal)
    {   

        foreach($this->mShiftKaryawan as $key){
            
            if($key->id_karyawan == $id_karyawan && $key->tanggal == $tanggal){
                // dd("Done");
                return $key;
            }
        }
        return null;
    }
    public function getIndexLembur($jam_ke, $id_shift, $tanggal_shift, $id_karyawan)
    {
        $nama_hari = Carbon::parse($tanggal_shift)->format('D');
        $id_grup_karyawan = $this->getMKaryawan($id_karyawan);
        $jabatan = $this->getKaryawan($id_karyawan)->id_jabatan;
        $result = 0;
        foreach($this->mTarifLembur as $key){
            if($jam_ke >= $key->jam_ke){ // jam_ke = 9 >= $key->jam_ke = 10
                
                if($id_shift != 1){ // bukan hari libur
                    $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->rate_hari_kerja];
                }else{ // hari libur
                    $hari_kerja = $this->getMGrupKaryawan($id_grup_karyawan);
                    if($hari_kerja == 1){
                        // if($nama_hari == 'Sat'){   //jika libur di hari sabtu
                            // $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->index_hari_libur_pendek];
                        // }else{
                            if ($jabatan == 2 || $jabatan == 5 || $jabatan == 3) {
                                $result = ['id_tarif_lembur'=>0,'rate'=>0];                                
                            }else {                                
                                $result = ['id_tarif_lembur'=>$key->id_tarif_lembur,'rate'=>$key->rate_hari_libur_1];
                            }
                        // }
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
    public function getMGrupKaryawan($id_grup_karyawan)
    {
        foreach($this->mGrupKaryawan as $key){
            if($key->id_grup_karyawan == $id_grup_karyawan){
                return $key->hari_kerja;
            }
        }
        return null;
    }
    public function getMKaryawan($id_karyawan)
    {
        foreach($this->mKaryawan as $key){
            if($key->id_karyawan == $id_karyawan){
                return $key->id_grup_karyawan;
            }
        }
        return null;
    }
    public function getKaryawan($id_karyawan)
    {
        foreach($this->mKaryawan as $key){
            if($key->id_karyawan == $id_karyawan){
                return $key;
            }
        }
        return null;
    }
    public function download_format_import()
    {
        $file= public_path(). "/download/format import absensi.xlsx";

        return Response::download($file);
    }
    public function cron_mount_before($bulan=null, $tahun=null){
        $now = date("Y-m-d");

        if ($bulan == null) {
            $bulan=date('m', strtotime($now." -1 month"));
        }
        if ($tahun == null) {            
            $tahun=date('Y', strtotime($now." -1 month"));
        }
        // echo $bulan."-".$tahun;
        $this->cron($bulan,$tahun);
    }
    public function cron($bulan=null, $tahun=null)
    {
        $years = $tahun ?? date('Y');
        $mounth = $bulan ?? date('m');
        $day = date('d');
        // dd($mounth.$years);

        if ($bulan) {
            $endDate = Carbon::CreateFromDate($years.'-'.$mounth)->endOfMonth()->format('Y-m-d');
            $startDate = Carbon::CreateFromDate($years.'-'.$mounth)->startOfMonth()->format('Y-m-d');
        }else{
            $endDate = Carbon::CreateFromDate($years.'-'.$mounth.'-'.$day)->subDays(1)->format('Y-m-d');
            $startDate = Carbon::CreateFromDate($years.'-'.$mounth)->startOfMonth()->format('Y-m-d');
            if (date('d') == '01') {
                $endDate = Carbon::CreateFromDate($years.'-'.$mounth)->subMonths(1)->endOfMonth()->format('Y-m-d');
                $startDate = Carbon::CreateFromDate($years.'-'.$mounth)->subMonths(1)->startOfMonth()->format('Y-m-d');
            }
        }
        // dd($startDate.'-'.$endDate);
        // $endDate = $request->bulan ? Carbon::CreateFromDate($years.'-'.$mounth)->endOfMonth()->format('Y-m-d') : date("Y-m-d",strtotime("-1 days"));
        // $startDate = $request->bulan ? Carbon::CreateFromDate($years.'-'.$mounth)->startOfMonth()->format('Y-m-d') : date("Y-m")."-01";
        $data_karyawan = MKaryawan::withDeleted()->get();
        $data_tipe_absensi = RefTipeAbsensi::withDeleted()->get();
        $date_all_in_mounth = [];
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime(Carbon::Parse($endDate)->addDays(1)->format('Y-m-d'))
        );
        foreach($period as $date){
            array_push($date_all_in_mounth,$date->format('Y-m-d'));
        }
        
        $tReportAbsensi = TReportAbsensi::where('bulan',$mounth)->where('tahun',$years);
        $arr_idReportAbsensi = $tReportAbsensi->pluck('id_report_absensi')->toArray();
        $tReportAbsensi->delete();
        TReportAbsensiDetail::whereIn('id_report_absensi',$arr_idReportAbsensi)->delete();


        $arr_ins_t_report_absensi_det = [];
        foreach($data_karyawan as $det_karyawan){

            foreach($data_tipe_absensi as $det_tipe){

                // $data = select id, tanggal from t_absensi where deleted = 1 and id_karyawan = $id_karyawan and tanggal between $start_date and $end_date and id_tipe_absensi = $det_tipe->id_tipe_absensi order by tanggal asc;
                $data = TAbsensi::withDeleted()->where('id_karyawan',$det_karyawan->id_karyawan)
                    ->whereBetween('tanggal',[$startDate,$endDate])
                    ->where('id_tipe_absensi',$det_tipe->id_tipe_absensi)
                    ->orderBy("tanggal",'asc')
                    ->get(['tanggal']);
                $jumlah_hari = count($data);
                //hitung tidak masuk
                if($det_tipe->id_tipe_absensi == 2){
                    $tanggalAbsensi = TAbsensi::withDeleted()->where('id_karyawan',$det_karyawan->id_karyawan)
                    ->whereBetween('tanggal',[$startDate,$endDate])
                    ->orderBy("tanggal",'asc')
                    ->pluck('tanggal');

                    $tidakmasuk = array_diff($date_all_in_mounth,$tanggalAbsensi->toArray());
                    
                    // dd($tidakmasuk);
                    $jumlah_hari = count($tidakmasuk);
                   
                }
                //hitung lembur
                if($det_tipe->kode_tipe_absensi == 'lembur'){
                    $tanggalLembur = $this->tanggalLembur($det_karyawan->id_karyawan,$startDate,$endDate);
                    $jumlah_hari = count($tanggalLembur);
                }
                //hitung terlambat
                if($det_tipe->kode_tipe_absensi == 'terlambat'){
                    $tanggalTerlambat = $this->tanggalTerlambat($det_karyawan->id_karyawan,$startDate,$endDate);
                    $jumlah_hari = count($tanggalTerlambat);
                }


                DB::table("t_report_absensi")->insert([
                    'id_karyawan' => $det_karyawan->id_karyawan,
                    'id_tipe_absensi' => $det_tipe->id_tipe_absensi,
                    'jumlah_hari' => $jumlah_hari,
                    'bulan' => $mounth,
                    'tahun' => $years,
                    'created_by' => 0,
                    'updated_by' => 0,
                    'updated_date' => date("Y-m-d H:i:s"),
                ]);
                $idReportBeforeInsert = DB::getPdo()->lastInsertId();
                
                if ($det_tipe->id_tipe_absensi == 2) {
                    foreach($tidakmasuk as $dat){
                        // $mReportAbsensiDet = new TReportAbsensiDetail;
                        // $mReportAbsensiDet->id_report_absensi = $mReportAbsensi->id_report_absensi;
                        // $mReportAbsensiDet->tanggal = $dat->tanggal;
                        // $mReportAbsensiDet->save();
                        $arr_ins_t_report_absensi_det[] = [
                            'id_report_absensi' => $idReportBeforeInsert,
                            'tanggal' => $dat,
                            'created_by' => 0,
                            'updated_by' => 0,
                            'updated_date' => date("Y-m-d H:i:s"),
                            'total_menit' => 0,
                        ];
                        // DB::table('t_report_absensi_det')->insert([
                        //     'id_report_absensi' => $idReportBeforeInsert,
                        //     'tanggal' => $dat,
                        //     'created_by' => 0,
                        //     'updated_by' => 0,
                        //     'updated_date' => date("Y-m-d H:i:s"),
                        // ]);
                    }
                }elseif($det_tipe->kode_tipe_absensi == 'lembur'){

                    foreach($tanggalLembur as $dat){
                        $arr_ins_t_report_absensi_det[] = [
                            'id_report_absensi' => $idReportBeforeInsert,
                            'tanggal' => $dat->tanggal,
                            'created_by' => 0,
                            'updated_by' => 0,
                            'updated_date' => date("Y-m-d H:i:s"),
                            'total_menit' => $dat->jumlah_menit,
                        ];
                    }

                }elseif($det_tipe->kode_tipe_absensi == 'terlambat'){

                    foreach($tanggalTerlambat as $dat){
                        $arr_ins_t_report_absensi_det[] = [
                            'id_report_absensi' => $idReportBeforeInsert,
                            'tanggal' => $dat->tanggal,
                            'created_by' => 0,
                            'updated_by' => 0,
                            'updated_date' => date("Y-m-d H:i:s"),
                            'total_menit' => $dat->jumlah_menit,
                        ];
                    }

                }else{

                    foreach($data as $dat){
                        $arr_ins_t_report_absensi_det[] = [
                            'id_report_absensi' => $idReportBeforeInsert,
                            'tanggal' => $dat->tanggal,
                            'created_by' => 0,
                            'updated_by' => 0,
                            'updated_date' => date("Y-m-d H:i:s"),
                            'total_menit' => 0,
                        ];
                    }

                }

            }
                    // dd($tanggalLembur,$tanggalTerlambat);
        // dd($arr_ins_t_report_absensi_det);

        }
        
        DB::table('t_report_absensi_det')->insert($arr_ins_t_report_absensi_det);

        return response()->json(['status'=>true,'msg'=>'Berhasil menghitung absensi']);
    }

    public function tanggalLembur($id_karyawan,$startDate,$endDate)
    {
        $data = TLembur::withDeleted()->where('id_karyawan',$id_karyawan)->where([['approval','1'],['approval2','1'],['approval3','1']])
                ->whereBetween('tanggal',[$startDate,$endDate])
                ->orderBy("tanggal",'asc')
                ->select('tanggal',DB::raw("SUM(jumlah_jam) as jumlah_jam"),DB::raw("SUM(jumlah_jam * 60) as jumlah_menit"))
                ->groupBy('tanggal')
                ->get();
                // dd($tanggalLembur);
        return $data;
    }
    public function tanggalTerlambat($id_karyawan,$startDate,$endDate)
    {
        $data = TAbsensi::from('t_absensi as a')
                    ->where('a.id_karyawan',$id_karyawan)
                    ->where('a.menit_terlambat','>','0')
                    ->where('a.deleted','1')
                    ->whereBetween('a.tanggal',[$startDate,$endDate])
                    ->orderBy("a.tanggal",'asc')
                    ->select('a.tanggal','a.menit_terlambat as jumlah_menit')
                    ->groupBy('a.tanggal')
                    ->get();
                    // dd($tanggalTerlambat);
        return $data;
    }
    
}
