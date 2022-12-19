<?php

namespace App\Imports;

use App\Models\MGrupKaryawan;
use App\Models\MShiftGrup;
use App\Models\MKaryawan;
use App\Models\MShift;
use App\Models\MShiftKaryawan;
use App\Models\LogAbsensi;
use App\Models\TAbsensi;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use DB;

class ImportPriviewAbsensi implements ToCollection, WithStartRow {
    protected $data = [];

    public function collection(Collection $rows)
    {
        $mKaryawan = mKaryawan::withDeleted()->get();
        $arr_id_karyawan = [];
        foreach ($rows as $key => $row) {
            if($row[0] != null){
                $data_karyawan = $this->getMKaryawan($mKaryawan, $row[0]);
                if ($data_karyawan != null) {                    
                    $arr_id_karyawan[] = $data_karyawan->id_karyawan;
                }
            }
        }

        $arr_tanggal_shift = $this->getPluckDataLogOnly($arr_id_karyawan)->pluck('tanggal_shift')->toArray();

        $this->data = $this->getDataLogOnly($arr_id_karyawan);
        $this->mShiftKaryawan = MShiftKaryawan::from('m_shift_karyawan as a')
                            ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                            ->whereIn('id_karyawan',array_unique($arr_id_karyawan))
                            ->where('a.deleted','1')
                            ->get(['a.id_karyawan','a.id_shift','a.tanggal','b.jam_masuk','b.jam_keluar','b.nama_shift']);

        $arr_tanggal = [];
        foreach($rows as $row){

            if($row[0] != null){
                $data_karyawan = $this->getMKaryawan($mKaryawan, $row[0]);
                if ($data_karyawan != null) {

                $tanggal = Carbon::createFromFormat("m/d/Y h:i:s A", $row[1])->format('Y-m-d');
                $tanggal_kemarin = Carbon::createFromFormat("m/d/Y h:i:s A", $row[1])->subDays(1)->format('Y-m-d');
                $tanggal_waktu = Carbon::createFromFormat("m/d/Y h:i:s A", $row[1])->format('Y-m-d H:i:s');
                $data_shift = $this->getMsiftKaryawan($data_karyawan->id_karyawan, $tanggal);
                $status = strtolower($row[2]);
                $arr_tanggal[] = $tanggal;

                $array = [
                            'id' => null,
                            'kode' => $row[0],
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
                            'status' => strtolower($row[2]),
                        ];

                // if (count($this->data) == 0) {
                //     array_push($this->data,$array);
                // }else{
                    
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

                // }

                }
            }
        }

        $tdata = [];
        foreach ($this->data as $key => $value) {
            unset($value['status']);
            $tdata[] = $value;
        }
        
        $data = $tdata;
        LogAbsensi::whereIn('id_karyawan',array_unique($arr_id_karyawan))->whereIn('tanggal_shift',$arr_tanggal_shift)->delete();
        LogAbsensi::insert($data);
        $this->data = $this->getDataLog($arr_id_karyawan,$arr_tanggal);
    }

    public function startRow(): int
    {
        return 4;
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

    public function getMKaryawan($array_karyawan,$kode)
    {   

        foreach($array_karyawan as $key){
            
            if($key->kode_fingerprint == $kode){
                return $key;
            }
        }
        return null;
    }

    public function getDataLog($arr_id_karyawan,$arr_tanggal)
    {   
        $start_date = reset($arr_tanggal);
        $end_date = end($arr_tanggal);
        $data_log = LogAbsensi::from('log_absensi as a')
                        ->leftJoin('ref_tipe_absensi as b',DB::raw('0'),'=','b.id_tipe_absensi')
                        ->select('a.*',DB::raw('0 as id_tipe_absensi'),'b.nama_tipe_absensi')
                        ->whereBetween('a.tanggal_shift',[$start_date,$end_date])
                        ->where('a.imported','0');
        $arr_id_karyawan = array_unique($arr_id_karyawan);
        $data_absensi = TAbsensi::from('t_absensi as a')
                        ->leftJoin('m_karyawan as b','a.id_karyawan','b.id_karyawan')
                        ->leftJoin('ref_tipe_absensi as c','a.id_tipe_absensi','=','c.id_tipe_absensi')
                        ->select(DB::raw('0 as id,b.kode_fingerprint as kode,a.id_karyawan as id_karyawan,b.nik,b.nama_karyawan,0 as id_shift,a.tanggal as tanggal_shift,null as jam_masuk_shift,null as jam_keluar_shift,null as waktu_masuk,null as waktu_keluar,0 as imported,a.id_tipe_absensi,c.nama_tipe_absensi'))
                        ->whereIn('a.id_karyawan',$arr_id_karyawan)
                        ->whereBetween('a.tanggal',[$start_date,$end_date])
                        ->where('a.id_tipe_absensi','>','1')->where('a.deleted','1');
        $data_log->union($data_absensi);
        $data_log = $data_log->orderBy('id_karyawan','asc')->orderBy('tanggal_shift','asc')->get()->toArray();
        return $data_log;
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
    
    public function getData()
    {
        return $this->data;
    }

}