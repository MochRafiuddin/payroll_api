<?php

namespace App\Http\Controllers;

use App\Models\LogAbsensi;
use App\Models\LogFingerprintUser;
use App\Models\LogFingerprint;
use App\Models\MSetting;
use App\Models\MKaryawan;
use App\Models\MShiftKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

class CCron extends Controller
{
    public function get_user_info(){
        $data_karyawan = DB::connection('adms')->table('userinfo')        
        ->select('userid','badgenumber','name')->get();
        
        LogFingerprintUser::query()->delete();;

        // LogFingerprintUser::create($data_karyawan);
        // $dataUser = [];
        foreach ($data_karyawan as $key) {
            DB::table("log_fingerprint_user")->insert([
                'userid' => $key->userid,
                'badgenumber' => $key->badgenumber,
                'name' => $key->name,
            ]);
        }
        // dd($dataUser);
        return response()->json(['status'=>true,'msg'=>'Berhasil menyimpan data fingerprint user']);
    }
    public function get_chechinout(){
        $last_id = $this->get_last_id();
        $data_adms = DB::connection('adms')->table('checkinout')        
        ->select('userid','checktime','checktype')
        ->where('checktime','>',$last_id)
        ->orderBy('checktime', 'ASC')->limit(100)->get()->toArray();

        $index = array_key_last($data_adms);
        // dd($data_adms[$index]->checktime);
        MSetting::where('kode','last_id_adms')->update(['nilai' => $data_adms[$index]->checktime]);

        // LogFingerprint::insert($data_adms);
        foreach ($data_adms as $key) {
            DB::table("log_fingerprint")->insert([
                'userid' => $key->userid,
                'checktime' => $key->checktime,
                'checktype' => $key->checktype,
            ]);
        }

        return response()->json(['status'=>true,'msg'=>'Berhasil menyimpan data fingerprint']);
    }
    public function get_fingerprint(Request $request){
        $data_json = $request->json_import;

        $this->data = [];
        $mKaryawan = MKaryawan::withDeleted()->get();
        
        // $last_id = $this->get_last_id();
        // $data_adms = DB::connection('adms')->table('checkinout')
        // ->join('userinfo','userinfo.userid','=','checkinout.userid')
        // ->select('id','userinfo.badgenumber as kode','checktime as tanggal',DB::raw("IF(checktype = 0, 'c/masuk', 'c/keluar') as status"))
        // ->where('checktime','>',$last_id)
        // ->orderBy('checktime', 'ASC')->limit(100)->get();
        LogFingerprint::whereIn("id",$data_json)->update(['submitted' => 1]);
        $data_adms = DB::table('log_fingerprint')
        ->join('log_fingerprint_user','log_fingerprint_user.userid','=','log_fingerprint.userid')
        ->select('id','log_fingerprint_user.badgenumber as kode','checktime as tanggal',DB::raw("IF(checktype = 0, 'c/masuk', 'c/keluar') as status"))
        ->whereIn('id',$data_json)
        ->orderBy('checktime', 'ASC')->get();
        // dd($data_adms);
        
        $arr_id_karyawan = [];
        foreach ($data_adms as $key => $row) {
            if($row != null){
                $data_karyawan = $this->getMKaryawan($mKaryawan, (int)$row->kode);
                if ($data_karyawan != null) {                    
                    $arr_id_karyawan[] = $data_karyawan->id_karyawan;
                }
            }
        }
        // dd($arr_id_karyawan);


        $arr_tanggal_shift = $this->getPluckDataLogOnly($arr_id_karyawan)->pluck('tanggal_shift')->toArray();

        $this->data = $this->getDataLogOnly($arr_id_karyawan);
        $this->mShiftKaryawan = MShiftKaryawan::from('m_shift_karyawan as a')
                            ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                            ->whereIn('id_karyawan',array_unique($arr_id_karyawan))
                            ->where('a.deleted','1')
                            ->get(['a.id_karyawan','a.id_shift','a.tanggal','b.jam_masuk','b.jam_keluar','b.nama_shift']);

        // $arr_tanggal = [];
        $id_skip = [];
        $nama_skip =[];
        $last_id_adms = 0;
        foreach($data_adms as $row){

            if($row != null){
                $data_karyawan = $this->getMKaryawan($mKaryawan, (int)$row->kode);
                if ($data_karyawan != null) {
                    
                $tanggal = Carbon::createFromFormat("Y-m-d H:i:s", $row->tanggal)->format('Y-m-d');
                $tanggal_kemarin = Carbon::createFromFormat("Y-m-d H:i:s", $row->tanggal)->subDays(1)->format('Y-m-d');
                $tanggal_waktu = Carbon::createFromFormat("Y-m-d H:i:s", $row->tanggal)->format('Y-m-d H:i:s');
                $data_shift = $this->getMsiftKaryawan($data_karyawan->id_karyawan, $tanggal);
                $status = $row->status;
                // $arr_tanggal[] = $tanggal;
                if ($data_shift == null) {
                    array_push($id_skip,$row->id);
                    array_push($nama_skip,'- '.$data_karyawan->nama_karyawan.'<br>');
                    continue;
                }
                $array = [
                            'id' => null,
                            'kode' => (int)$row->kode,
                            'id_karyawan' => $data_karyawan->id_karyawan,
                            'nik' => $data_karyawan->nik,
                            'nama_karyawan' => $data_karyawan->nama_karyawan,
                            'id_shift' => $data_shift->id_shift,
                            'tanggal_shift' => $tanggal,
                            'jam_masuk_shift' => $data_shift->jam_masuk ?? null,
                            'jam_keluar_shift' => $data_shift->jam_keluar ?? null,
                            'waktu_masuk' => ($status == 'c/masuk' ? $tanggal_waktu : null),
                            'waktu_keluar' => ($status == 'c/keluar' ? $tanggal_waktu : null),
                            'waktu_keluar' => ($status == 'c/keluar' ? $tanggal_waktu : null),
                            'imported' => 0,
                            'created_date' => date('Y-m-d H:i:s'),
                            'status' => strtolower($row->status),
                        ];

                // if (count($this->data) == 0) {
                //     array_push($this->data,$array);
                // }else{
        // dd($array);

                    $cek_tgl_kemarin = $this->cekTanggalKemarin($array,$tanggal_kemarin,$status);
                    if ($cek_tgl_kemarin >= 0) {                        
                        if ($this->data[$cek_tgl_kemarin]['waktu_masuk'] != "" && $this->data[$cek_tgl_kemarin]['waktu_keluar'] != "") {
                            $cek_tgl_sekarang = $this->cekTanggalSekarang($array,$status);
                            if ($cek_tgl_sekarang >= 0) {

                                if ($this->data[$cek_tgl_sekarang]['waktu_masuk'] != "" && $this->data[$cek_tgl_sekarang]['waktu_keluar'] != "") {
                                    // do nothing
                                }else{
                                    if ($status == 'c/masuk') {
                                        if ($this->data[$cek_tgl_sekarang]['waktu_masuk'] == "") {
                                            $this->data[$cek_tgl_sekarang]['waktu_masuk'] = $array['waktu_masuk'];
                                        }
                                    } elseif ($status == 'c/keluar') {
                                        if ($this->data[$cek_tgl_sekarang]['waktu_keluar'] == "") {
                                            $this->data[$cek_tgl_sekarang]['waktu_keluar'] = $array['waktu_keluar'];
                                        }
                                    }
                                }

                            }else{
                                if ($status == 'c/masuk') {
                                    array_push($this->data,$array);
                                }else{
                                    // do nothing
                                }
                            }
                        }else{
                            if ($status == $this->data[$cek_tgl_kemarin]['status']) {
                                array_push($this->data,$array);
                            }else{
                                if ($status == 'c/masuk') {
                                    if ($this->data[$cek_tgl_kemarin]['waktu_masuk'] == "") {
                                        $this->data[$cek_tgl_kemarin]['waktu_masuk'] = $array['waktu_masuk'];
                                    }
                                } elseif ($status == 'c/keluar') {
                                    if ($this->data[$cek_tgl_kemarin]['waktu_keluar'] == "" ) {
                                        $cek_tgl_sekarang = $this->cekTanggalSekarang($array,$status);
                                        if ($cek_tgl_sekarang >= 0) {
                                            if ($status == 'c/masuk') {
                                                if ($this->data[$cek_tgl_sekarang]['waktu_masuk'] == "") {
                                                    $this->data[$cek_tgl_sekarang]['waktu_masuk'] = $array['waktu_masuk'];
                                                }
                                            } elseif ($status == 'c/keluar') {
                                                if ($this->data[$cek_tgl_sekarang]['waktu_keluar'] == "") {
                                                    $this->data[$cek_tgl_sekarang]['waktu_keluar'] = $array['waktu_keluar'];
                                                }
                                            }
                                        }else{
                                            $this->data[$cek_tgl_kemarin]['waktu_keluar'] = $array['waktu_keluar'];
                                        }                          
                                    }
                                }
                            }
                        }

                    }else{

                        $cek_tgl_sekarang = $this->cekTanggalSekarang($array,$status);
                        if ($cek_tgl_sekarang >= 0) {

                            if ($this->data[$cek_tgl_sekarang]['waktu_masuk'] != "" && $this->data[$cek_tgl_sekarang]['waktu_keluar'] != "") {
                                // do nothing
                            }else{
                                if ($status == 'c/masuk') {
                                    if ($this->data[$cek_tgl_sekarang]['waktu_masuk'] == "") {
                                        $this->data[$cek_tgl_sekarang]['waktu_masuk'] = $array['waktu_masuk'];
                                    }
                                } elseif ($status == 'c/keluar') {
                                    if ($this->data[$cek_tgl_sekarang]['waktu_keluar'] == "") {
                                        $this->data[$cek_tgl_sekarang]['waktu_keluar'] = $array['waktu_keluar'];
                                    }
                                }
                            }

                        }else{
                            if ($status == 'c/masuk') {
                                array_push($this->data,$array);
                            }else{
                                // do nothing
                            }
                        }

                    }

                }

            }
        //   }
          $last_id_adms=$row->tanggal;
        }

        $tdata = [];
        foreach ($this->data as $key => $value) {
            unset($value['status']);
            $tdata[] = $value;
        }
        
        $data = $tdata;
        // if (count($data_adms)==0) {            
        //     $last_id_adms = DB::connection('adms')->table('checkinout')->select('checktime')->orderBy('checktime','desc')->first()->checktime;
        // }
        // dd($id_skip);
        // dd($data);
        LogFingerprint::whereIn("id",$id_skip)->update(['submitted' => 0]);

        $select = LogAbsensi::whereIn('id_karyawan',array_unique($arr_id_karyawan))
                ->whereIn('tanggal_shift',$arr_tanggal_shift)
                ->select(array('kode', 'id_karyawan', 'nik', 'nama_karyawan', 'id_shift', 'tanggal_shift', 'jam_masuk_shift', 'jam_keluar_shift', 'waktu_masuk', 'waktu_keluar', 'imported', 'created_date'));        
        $bindings = $select->getBindings();        
        $insertQuery = "INSERT into temp_log_absensi (kode, id_karyawan, nik, nama_karyawan, id_shift, tanggal_shift, jam_masuk_shift, jam_keluar_shift, waktu_masuk, waktu_keluar, imported, created_date)".$select->toSql();    
        DB::insert($insertQuery, $bindings);
        
        LogAbsensi::whereIn('id_karyawan',array_unique($arr_id_karyawan))->whereIn('tanggal_shift',$arr_tanggal_shift)->delete();
        LogAbsensi::insert($data);
        // MSetting::where('kode','last_id_adms')->update(['nilai' => $last_id_adms]);

        return response()->json(['status'=>true,'msg'=>'Berhasil menyimpan data fingerprint', 'msg1'=>array_unique($nama_skip)]);
    }

    public function get_last_id(){
        $last_id = MSetting::where('kode','last_id_adms')->first();
        if (!$last_id) {
            $val = '0000-00-00 00:00:00';
            MSetting::insert(['kode'=>'last_id_adms','nilai'=>$val,'ket'=>'ID terakhir pada DB ADMS','created_by'=>0,'updated_by'=>0,'created_date'=>date('Y-m-d H:i:s'),'updated_date'=>date('Y-m-d H:i:s')]);
            return $val;
        }else{
            return $last_id->nilai;
        }
    }

    public function getMKaryawan($array_karyawan,$kode)
    {   

        foreach($array_karyawan as $key){
            
            if($key->kode_fingerprint == $kode){
                return $key;
            }
        }
        return null;
    }

    public function getDataLogOnly($arr_id_karyawan)
    {   
        $arr_id_karyawan = array_unique($arr_id_karyawan);
        $data_log = LogAbsensi::from('log_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b',DB::raw('0'),'=','b.id_tipe_absensi')
                        ->select('a.*')
                        ->whereIn('a.id_karyawan',$arr_id_karyawan)
                        ->where('a.imported','0');
        $data_log = $data_log->orderBy('a.id_karyawan','asc')->orderBy('a.tanggal_shift','asc')->get()->toArray();        
        if ($data_log) {
            $array2 = array('status' => 'c/masuk'); 
            for ($i=0; $i < count($data_log) ; $i++) {             
                $dd[] = $data_log[$i] + $array2;
            }
        }else {
            $dd=[];
        }
        return $dd;
    }

    public function getPluckDataLogOnly($arr_id_karyawan)
    {   
        $arr_id_karyawan = array_unique($arr_id_karyawan);
        $data_log = LogAbsensi::from('log_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b',DB::raw('0'),'=','b.id_tipe_absensi')
                        ->select('a.*')
                        ->whereIn('a.id_karyawan',$arr_id_karyawan)
                        ->where('a.imported','0');
        $data_log = $data_log->orderBy('a.id_karyawan','asc')->orderBy('a.tanggal_shift','asc');
        return $data_log;
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

    public function cekTanggalKemarin($data,$tanggal_kemarin,$status)
    {   
        foreach($this->data as $key => $value){
        // dd($this->data[$key] , $data);
            if($this->data[$key]['tanggal_shift'] == $tanggal_kemarin && $this->data[$key]['id_karyawan'] == $data['id_karyawan']){
                return $key;
            }
        }

        return (-1);
    }

    public function cekTanggalSekarang($data,$status)
    {   
        foreach($this->data as $key => $value){
        // dd($this->data[$key] , $data);
            if($this->data[$key]['tanggal_shift'] == $data['tanggal_shift'] && $this->data[$key]['id_karyawan'] == $data['id_karyawan']){
                return $key;
            }
        }

        return (-1);
    }
}
