<?php

namespace App\Traits;

use App\Models\MSetting;
use App\Models\MTarifPph;
use App\Models\TGajiKaryawanPeriode;
use App\Models\TGajiKaryawanPeriodeDet;
use App\Models\TGajiKaryawanPeriodeLembur;
use App\Models\TTotalGajiPeriode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

trait HitungPenggajian
{
    public function hitung()
    {
        $id_periode = Session::get('id_periode');//dari session

        $data_karyawan = DB::select("select a.id_karyawan,a.status_npwp,a.id_status_kawin,a.tanggal_masuk,a.nama_karyawan,b.nilai_ptkp,a.metode_pph21, a.tanggal_akhir_kontrak, c.hari_kerja
            from m_karyawan a, m_status_kawin b, m_grup_karyawan c
            where a.id_status_kawin = b.id_status_kawin and a.id_grup_karyawan = c.id_grup_karyawan and a.deleted = 1 and a.set_gaji = 1");

        $data_periode = DB::select("select id_periode, bulan, tahun from m_periode where id_periode = {$id_periode}");

        $data_setting = MSetting::withDeleted()->select(['kode','nilai'])->get();
       
        $tanggal_gajian = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),$this->get_setting_by_kode($data_setting, 'tanggal_gajian'))->format('Y-m-d');
       
        $data_tarif_pph = MTarifPph::withDeleted()->orderBy('batas_bawah','asc')->get()->toArray();
        
        $start_id_gaji_karyawan_periode = TGajiKaryawanPeriode::orderBy('id','desc')->where('deleted','1')->first();
        if ($start_id_gaji_karyawan_periode) {
            $start_id_gaji_karyawan_periode=$start_id_gaji_karyawan_periode->id+1;
        }else{
            $start_id_gaji_karyawan_periode=1;
        }

        $arr_id_karyawan = [];
        $arr_ins_t_gaji_karyawan_periode = [];
        $arr_ins_t_gaji_karyawan_periode_det = [];
        $arr_ins_t_gaji_karyawan_periode_lembur = [];
        $total_nominal = 0;

        foreach ($data_karyawan as $key) {
            $arr_id_karyawan[] = $key->id_karyawan;

            $data_gaji = DB::select("select a.id_periode,a.id_karyawan,a.id_gaji,a.nominal, b.id_jenis_gaji, b.periode_hitung, b.nama_gaji
                from map_gaji_karyawan_periode a, m_gaji b 
                where a.id_gaji = b.id_gaji and a.deleted = 1 and a.id_periode = {$id_periode} and a.id_karyawan = {$key->id_karyawan}");
            // dd($id_periode);

            $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
            $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');
            $hari_masuk_kerja = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->where('b.id_karyawan',$key->id_karyawan)
                                // ->where('b.bulan',Session::get('periode_bulan'))
                                // ->where('b.tahun',Session::get('periode_tahun'))
                                ->whereBetween('a.tanggal',[$tanggal_start,$tanggal_end])
                                ->where('id_tipe_absensi','1')
                                ->count();
            $hari_tidak_masuk_kerja = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->select('a.tanggal','b.id_tipe_absensi')
                                ->where('b.id_karyawan',$key->id_karyawan)
                                // ->where('b.bulan',Session::get('periode_bulan'))
                                // ->where('b.tahun',Session::get('periode_tahun'))
                                ->whereBetween('a.tanggal',[$tanggal_start,$tanggal_end])
                                ->where('b.id_tipe_absensi','2')
                                ->count();
            $hari_izin = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->where('b.id_karyawan',$key->id_karyawan)
                                // ->where('b.bulan',Session::get('periode_bulan'))
                                // ->where('b.tahun',Session::get('periode_tahun'))
                                ->where('id_tipe_absensi','2')
                                ->whereBetween('a.tanggal',[$tanggal_start,$tanggal_end])
                                ->whereNotIn('id_tipe_absensi',['1','2','3'])
                                ->count();
            $hari_lembur_holiday = DB::table('t_lembur as a')
                                ->select('a.tanggal')
                                ->where('a.id_karyawan',$key->id_karyawan)->where('a.approval','1')->where('tipe_hari','1')
                                ->whereBetween('a.tanggal',[$tanggal_start,$tanggal_end])
                                ->where('a.deleted','1')
                                ->groupBy('a.tanggal')
                                ->get()->count();
            $hari_kerja_shift = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->select('a.tanggal','b.id_tipe_absensi')
                                ->where('b.id_karyawan',$key->id_karyawan)
                                ->where('b.bulan',Session::get('periode_bulan'))
                                ->where('b.tahun',Session::get('periode_tahun'))
                                // ->whereBetween('a.tanggal',[$tanggal_start,$tanggal_end])
                                ->where('b.id_tipe_absensi','!=','3')
                                ->count();

            $gaji_pokok = $this->get_gaji_pokok($data_gaji);

            $gaji_pokok_tunjangan_tetap = $gaji_pokok + $this->get_tunjangan_tetap($data_gaji);
            $tunjangan_tetap = $this->get_tunjangan_tetap($data_gaji);

            // $total_gaji_tunjangan = $gaji_pokok + $this->get_tunjangan($data_gaji,$hari_masuk_kerja);
            $total_gaji_tunjangan = 0;
            $tunjangan_tidak_tetap = $this->get_tunjangan($data_gaji,$hari_masuk_kerja);

            $persen_jkm_perusahaan = doubleval($this->get_setting_by_kode($data_setting,"jkm_perusahaan"));
            $persen_jkk_perusahaan = doubleval($this->get_setting_by_kode($data_setting,"jkk_perusahaan"));
            $persen_jht_perusahaan = doubleval($this->get_setting_by_kode($data_setting,"jht_perusahaan"));
            $persen_jkn_perusahaan = doubleval($this->get_setting_by_kode($data_setting,"jkn_perusahaan"));
            $persen_jpn_perusahaan = doubleval($this->get_setting_by_kode($data_setting,"jpn_perusahaan"));

            $persen_jht_karyawan = doubleval($this->get_setting_by_kode($data_setting,"jht_karyawan"));
            $persen_jkn_karyawan = doubleval($this->get_setting_by_kode($data_setting,"jkn_karyawan"));
            $persen_jpn_karyawan = doubleval($this->get_setting_by_kode($data_setting,"jpn_karyawan"));

            $maks_jpn = doubleval($this->get_setting_by_kode($data_setting,"maks_jpn"));//rupiah
            $maks_jkn = doubleval($this->get_setting_by_kode($data_setting,"maks_jkn"));//rupiah
            $maks_biaya_jabatan = doubleval($this->get_setting_by_kode($data_setting,"maks_biaya_jabatan"));//rupiah

            $jkm_perusahaan = $persen_jkm_perusahaan * $gaji_pokok_tunjangan_tetap / 100;
            $jkk_perusahaan = $persen_jkk_perusahaan * $gaji_pokok_tunjangan_tetap / 100;
            $jht_perusahaan = $persen_jht_perusahaan * $gaji_pokok_tunjangan_tetap / 100;
            $jkn_perusahaan = $persen_jkn_perusahaan * (($gaji_pokok_tunjangan_tetap > $maks_jkn) ? $maks_jkn : $gaji_pokok_tunjangan_tetap) / 100;
            $jpn_perusahaan = $persen_jpn_perusahaan * (($gaji_pokok_tunjangan_tetap > $maks_jpn) ? $maks_jpn : $gaji_pokok_tunjangan_tetap) / 100;

            $jht_karyawan = $persen_jht_karyawan * $gaji_pokok_tunjangan_tetap / 100;
            $jkn_karyawan = $persen_jkn_karyawan * (($gaji_pokok_tunjangan_tetap > $maks_jkn) ? $maks_jkn : $gaji_pokok_tunjangan_tetap) / 100;
            $jpn_karyawan = $persen_jpn_karyawan * (($gaji_pokok_tunjangan_tetap > $maks_jpn) ? $maks_jpn : $gaji_pokok_tunjangan_tetap) / 100;

            $periode_tgl20 = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),20)->format('Y-m-d');
            if ($key->tanggal_masuk >= $periode_tgl20) {
                $jht_karyawan = 0;
                $jkn_karyawan = 0;
                $jpn_karyawan = 0;
            }

            
            $gaji_per_jam = $gaji_pokok / 173;
            $nominal_lembur = 1000000;//select sum(index_tarif * jumlah_jam * $gaji_per_jam) from t_lembur where id_karyawan and approval = 1 and tanggal sesuai periode gaji. misalkan periode gaji == mei 2022, maka yang diselect dari 11 april 2022 s/d 10 mei 2022
            // $data_lembur = DB::table('t_lembur')->select('*')->where('id_karyawan',$key->id_karyawan)->where('approval','1')->whereMonth('tanggal',Session::get('periode_bulan'))->whereYear('tanggal',Session::get('periode_tahun'))->get();
            $data_lembur = DB::table('t_lembur')->select('*')->where('id_karyawan',$key->id_karyawan)->where([['approval','1'],['approval2','1'],['approval3','1']])->whereBetween('tanggal',[$tanggal_start,$tanggal_end])->where('deleted','1')->get();
            // $hitung_lembur = DB::table('t_lembur')->select(DB::raw("sum(index_tarif * jumlah_jam * $gaji_per_jam) as nominal_lembur"))->where('id_karyawan',$key->id_karyawan)->where('approval','1')->whereMonth('tanggal',Session::get('periode_bulan'))->whereYear('tanggal',Session::get('periode_tahun'))->get();
            $hitung_lembur = DB::table('t_lembur')->select(DB::raw("sum(index_tarif * jumlah_jam * $gaji_per_jam) as nominal_lembur"))->where('id_karyawan',$key->id_karyawan)->where([['approval','1'],['approval2','1'],['approval3','1']])->whereBetween('tanggal',[$tanggal_start,$tanggal_end])->where('deleted','1')->get();
            $nominal_lembur = ceil($hitung_lembur[0]->nominal_lembur) ?? 0;

            $param_denda_early_leave = [
                'id_periode' => $id_periode,
                'tanggal_start' => $tanggal_start,
                'tanggal_end' => $tanggal_end,
                'gaji_pokok' => $gaji_pokok,
                'gaji_per_jam' => $gaji_per_jam,
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
            ];
            $param_denda_terlambat = [
                'id_periode' => $id_periode,
                'tanggal_start' => $tanggal_start,
                'tanggal_end' => $tanggal_end,
                'gaji_pokok' => $gaji_pokok,
                'gaji_per_jam' => $gaji_per_jam,
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
            ];
            $param_denda_tidak_masuk = [
                'id_periode' => $id_periode,
                'tanggal_start' => $tanggal_start,
                'tanggal_end' => $tanggal_end,
                'gaji_pokok' => $gaji_pokok,
                'jumlah_hari' => $hari_tidak_masuk_kerja,
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
            ];
            $param_hutang_asuransi = [
                'id_periode' => $id_periode,
                'periode_bulan' => Session::get('periode_bulan'),
                'periode_tahun' => Session::get('periode_tahun'),
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
            ];
            $param_cut_off = [
                'id_periode' => $id_periode,
                'periode_bulan' => Session::get('periode_bulan'),
                'periode_tahun' => Session::get('periode_tahun'),
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
                'tanggal_akhir_kontrak' => $key->tanggal_akhir_kontrak,
                'tanggal_gajian' => $tanggal_gajian,
                'hari_kerja' => $key->hari_kerja,
                'gaji_pokok' => $gaji_pokok,
            ];
            $param_karyawan_baru = [
                'id_periode' => $id_periode,
                'periode_bulan' => Session::get('periode_bulan'),
                'periode_tahun' => Session::get('periode_tahun'),
                't_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
                'tanggal_akhir_kontrak' => $key->tanggal_akhir_kontrak,
                'tanggal_gajian' => $tanggal_gajian,
                'hari_kerja' => $key->hari_kerja,
                'gaji_pokok' => $gaji_pokok,
                'tanggal_masuk' => $key->tanggal_masuk,
            ];
            $all_gaji = $this->get_all_gaji($data_gaji, $nominal_lembur, $hari_masuk_kerja, $key->id_karyawan, $param_denda_terlambat,$param_denda_early_leave,$param_denda_tidak_masuk,$param_hutang_asuransi,$param_cut_off,$param_karyawan_baru,$hari_lembur_holiday);
            $bruto = $all_gaji + $jkk_perusahaan + $jkm_perusahaan + $jkn_perusahaan;
            


            $persen_biaya_jabatan = doubleval($this->get_setting_by_kode($data_setting,"biaya_jabatan"));
            $persen_non_npwp = doubleval($this->get_setting_by_kode($data_setting,"tarif_non_npwp"));

            $biaya_jabatan = (($persen_biaya_jabatan * $bruto) / 100) > $maks_biaya_jabatan ? $maks_biaya_jabatan : (($persen_biaya_jabatan * $bruto) / 100);

            $total_pengurang = $jht_karyawan + $jpn_karyawan;

            $netto = $bruto - $biaya_jabatan - $total_pengurang;

            $netto_setahun = $netto * 12;

            $ptkp = $key->nilai_ptkp;
            $pkp = $netto_setahun - $ptkp;

            $pph21_setahun = 0;
            $add_non_npwp = 0;
            $metode_pph21 = $key->metode_pph21;
            $pph21_nett = 0;
            $pph21_sebulan = 0;
            $gaji_bersih = 0;

            // dd([
            //         'all_gaji' => $all_gaji,
            //         'gaji_pokok' => $gaji_pokok,
            //         'tunjangan_tetap' => $tunjangan_tetap,
            //         'tunjangan_tidak_tetap' => $tunjangan_tidak_tetap,
            //         'jkk_perusahaan' => $jkk_perusahaan,
            //         'persen_jkk_perusahaan' => $persen_jkk_perusahaan,
            //         'jkm_perusahaan' => $jkm_perusahaan,
            //         'persen_jkm_perusahaan' => $persen_jkm_perusahaan,
            //         'jht_perusahaan' => $jht_perusahaan,
            //         'persen_jht_perusahaan' => $persen_jht_perusahaan,
            //         'jpn_perusahaan' => $jpn_perusahaan,
            //         'persen_jpn_perusahaan' => $persen_jpn_perusahaan,
            //         'biaya_jabatan' => $biaya_jabatan,
            //         'bruto' => $bruto,
            //     ]);

            if($pkp <= 0){
                $pkp = 0;
            }else{

                $pkp_backup = $pkp;
                $pph21_setahun = $this->hitung_pph21($data_tarif_pph,$pkp);
                if($key->status_npwp == 0){
                    $add_non_npwp = ($pph21_setahun * $persen_non_npwp / 100) - $pph21_setahun;
                }
                $pph21_nett = $pph21_setahun + $add_non_npwp;
                $pph21_sebulan = $pph21_nett / 12;
            }

            $gaji_bersih = $all_gaji - $jht_karyawan - $jpn_karyawan - $jkn_karyawan - $pph21_sebulan;
            $total_nominal += $gaji_bersih;
            // dd([
            //         'netto_sebulan' => $netto,
            //         'netto_setahun' => $netto_setahun,
            //         'pkp' => $pkp,
            //         'pph21_sebulan' => $pph21_sebulan,
            //         'pph21_setahun' => $pph21_setahun,
            //         'gaji_bersih' => $gaji_bersih,
            //     ]);

            // $tGajiKaryawanPeriode = new TGajiKaryawanPeriode;
            // $tGajiKaryawanPeriode->id_karyawan = $key->id_karyawan;
            // $tGajiKaryawanPeriode->id_periode = $id_periode;
            // $tGajiKaryawanPeriode->status_npwp = $key->status_npwp;
            // $tGajiKaryawanPeriode->id_status_kawin = $key->id_status_kawin;

            // $tGajiKaryawanPeriode->hari_hadir = 0;
            // $tGajiKaryawanPeriode->hari_tidak_hadir = 0;
            // $tGajiKaryawanPeriode->hari_terlambat  = 0;
            // $tGajiKaryawanPeriode->lama_kerja  = 0;
            // $tGajiKaryawanPeriode->lembur  = 0;
            // $tGajiKaryawanPeriode->metode_pph21  = $key->metode_pph21;
            // $tGajiKaryawanPeriode->total_gaji_tunjangan  = 0;
            // $tGajiKaryawanPeriode->total_potongan  = 0;

            // $tGajiKaryawanPeriode->jkm_perusahaan  = $jkm_perusahaan;
            // $tGajiKaryawanPeriode->jkk_perusahaan  = $jkk_perusahaan;
            // $tGajiKaryawanPeriode->jht_perusahaan  = $jht_perusahaan;
            // $tGajiKaryawanPeriode->jkn_perusahaan = $jkn_perusahaan;
            // $tGajiKaryawanPeriode->jpn_perusahaan = $jpn_perusahaan;
            // $tGajiKaryawanPeriode->jht_karyawan = $jht_karyawan;
            // $tGajiKaryawanPeriode->jkn_karyawan = $jkn_karyawan;
            // $tGajiKaryawanPeriode->jpn_karyawan = $jpn_karyawan;
            // $tGajiKaryawanPeriode->bruto = $bruto;
            // $tGajiKaryawanPeriode->biaya_jabatan  = $biaya_jabatan ;
            // $tGajiKaryawanPeriode->total_pengurang  = $total_pengurang ;
            // $tGajiKaryawanPeriode->netto  = $netto ;
            // $tGajiKaryawanPeriode->netto_setahun  = $netto_setahun ;
            // $tGajiKaryawanPeriode->ptkp  = $ptkp ;
            // $tGajiKaryawanPeriode->pkp  = $pkp ;
            // $tGajiKaryawanPeriode->pph21_setahun  = $pph21_setahun ;
            // $tGajiKaryawanPeriode->add_non_npwp  = $add_non_npwp ;
            // $tGajiKaryawanPeriode->pph21_nett  = $pph21_nett ;
            // $tGajiKaryawanPeriode->pph21_sebulan  = $pph21_sebulan ;
            // $tGajiKaryawanPeriode->gaji_bersih  = $gaji_bersih ;    
            // $tGajiKaryawanPeriode->save();

            $arr_ins_t_gaji_karyawan_periode[] = [
                'id' => $start_id_gaji_karyawan_periode,
                'id_karyawan' => $key->id_karyawan,
                'id_periode' => $id_periode,
                'status_npwp' => $key->status_npwp,
                'id_status_kawin' => $key->id_status_kawin,
                'hari_hadir' => $hari_masuk_kerja,
                'hari_izin' => $hari_izin,
                'hari_lembur_holiday' => $hari_lembur_holiday,
                'hari_tidak_hadir' => $hari_tidak_masuk_kerja,
                'hari_terlambat'  => 0,
                'lama_kerja'  => 0,
                'lembur'  => 0,
                'metode_pph21'  => $key->metode_pph21,
                'total_gaji_tunjangan'  => $total_gaji_tunjangan,
                'total_potongan'  => 0,
                'jkm_perusahaan'  => $jkm_perusahaan,
                'jkk_perusahaan'  => $jkk_perusahaan,
                'jht_perusahaan'  => $jht_perusahaan,
                'jkn_perusahaan' => $jkn_perusahaan,
                'jpn_perusahaan' => $jpn_perusahaan,
                'jht_karyawan' => $jht_karyawan,
                'jkn_karyawan' => $jkn_karyawan,
                'jpn_karyawan' => $jpn_karyawan,
                'lembur' => $nominal_lembur,
                'bruto' => $bruto,
                'biaya_jabatan'  => $biaya_jabatan,
                'total_pengurang'  => $total_pengurang,
                'netto'  => $netto,
                'netto_setahun'  => $netto_setahun,
                'ptkp'  => $ptkp,
                'pkp'  => $pkp,
                'pph21_setahun'  => $pph21_setahun,
                'add_non_npwp'  => $add_non_npwp,
                'pph21_nett'  => $pph21_nett,
                'pph21_sebulan'  => $pph21_sebulan,
                'gaji_bersih'  => $gaji_bersih,
                'hari_kerja_shift'  => $hari_kerja_shift,
                'created_by'  => Auth::user()->id_user,
                'updated_by'  => Auth::user()->id_user,
            ];
            // dd($arr_ins_t_gaji_karyawan_periode);
            foreach ($data_gaji as $value) {
                $nominal = $value->nominal;
                if ($value->id_jenis_gaji == 4) {
                    $nominal = $value->nominal*(-1);
                }
                if ($value->periode_hitung == 2) {
                    $nominal = $value->nominal * $hari_masuk_kerja;
                }
                if ($value->periode_hitung == 3) {
                    $nominal = $value->nominal * $hari_lembur_holiday;
                }
                $arr_ins_t_gaji_karyawan_periode_det[] = [
                    'id_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
                    'id_karyawan' => $value->id_karyawan,
                    'id_gaji' => $value->id_gaji,
                    'nama_gaji' => $value->nama_gaji,
                    'nominal' => $nominal,
                    'created_by'  => Auth::user()->id_user,
                    'updated_by'  => Auth::user()->id_user,
                ];
            }
            if (count($data_lembur) > 0) {
                foreach ($data_lembur as $value) {
                    $arr_ins_t_gaji_karyawan_periode_lembur[] = [
                        'id_gaji_karyawan_periode' => $start_id_gaji_karyawan_periode,
                        'id_tarif_lembur' => $value->id_tarif_lembur,
                        'tanggal' => $value->tanggal,
                        'index_tarif' => $value->index_tarif,
                        'jumlah_jam' => $value->jumlah_jam,
                        'tipe_hari' => $value->tipe_hari,
                        'created_by'  => Auth::user()->id_user,
                        'updated_by'  => Auth::user()->id_user,
                        'created_date'  => date('Y-m-d H:i:s'),
                        'updated_date'  => date('Y-m-d H:i:s'),
                        'deleted'  => '1',
                    ];
                }
            }
            $start_id_gaji_karyawan_periode++;
        }

        $arr_id_gaji_karyawan_periode = TGajiKaryawanPeriode::whereIn('id_karyawan',$arr_id_karyawan)->where('id_periode',$id_periode)->pluck('id')->toArray();        

        TGajiKaryawanPeriode::whereIn('id_karyawan',$arr_id_karyawan)->where('id_periode',$id_periode)->update(['deleted'=>0]);
        TGajiKaryawanPeriode::insert($arr_ins_t_gaji_karyawan_periode);

        TGajiKaryawanPeriodeDet::whereIn('id_gaji_karyawan_periode',$arr_id_gaji_karyawan_periode)->update(['deleted'=>0]);
        TGajiKaryawanPeriodeDet::insert($arr_ins_t_gaji_karyawan_periode_det);

        TGajiKaryawanPeriodeLembur::whereIn('id_gaji_karyawan_periode',$arr_id_gaji_karyawan_periode)->update(['deleted'=>0]);
        TGajiKaryawanPeriodeLembur::insert($arr_ins_t_gaji_karyawan_periode_lembur);

        $id_gaji_kar = TGajiKaryawanPeriode::where("id_periode",$id_periode)->where('deleted',1)->pluck('id')->toArray();        
        $gaji_kar = TGajiKaryawanPeriode::where("id_periode",$id_periode)->where('deleted',1);        
        $total_gaji_pokok = TGajiKaryawanPeriodeDet::whereIn('id_gaji_karyawan_periode',$id_gaji_kar)
                ->where('id_gaji',1)
                ->where('deleted',1)->sum('nominal');
        $tunjangan_kar = TGajiKaryawanPeriodeDet::join('m_gaji','m_gaji.id_gaji','=','t_gaji_karyawan_periode_det.id_gaji')
                ->whereIn('t_gaji_karyawan_periode_det.id_gaji_karyawan_periode',$id_gaji_kar)
                ->whereIn('m_gaji.id_jenis_gaji',[2,3,5])
                ->where('t_gaji_karyawan_periode_det.deleted',1)->sum('nominal');
        $deduction = TGajiKaryawanPeriodeDet::whereIn('id_gaji_karyawan_periode',$id_gaji_kar)
                ->where('id_gaji',0)
                ->where('deleted',1)->sum('nominal');
        // dd(str_replace("-","",$deduction));
        // dd($deduction);

        DB::table('t_total_gaji_periode')->where('id_periode',$id_periode)->update(['deleted'=>0]);
        TTotalGajiPeriode::insert([
                'id_periode' => $id_periode,
                'nominal' => $total_nominal,
                'gaji_pokok' => $total_gaji_pokok,
                'lembur' => $gaji_kar->sum('lembur'),
                'tunjangan' => $tunjangan_kar,
                'jht_karyawan' => $gaji_kar->sum('jht_karyawan'),
                'jpn_karyawan' => $gaji_kar->sum('jpn_karyawan'),
                'jkn_karyawan' => $gaji_kar->sum('jkn_karyawan'),
                'pph21' => $gaji_kar->sum('pph21_sebulan'),
                'deduction' => $deduction,
                'approval' => 0,
                'approval2' => 0,
                'approval3' => 0,
                'approval4' => 0,
                'approve_by' => 0,
                'approve2_by' => 0,
                'approve3_by' => 0,
                'approve4_by' => 0,
                'approve_date' => date('Y-m-d H:i:s'),
                'created_date' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id_user,
                'updated_date' => date('Y-m-d H:i:s'),
                'updated_by' => Auth::user()->id_user,
                'deleted' => 1,
            ]);

    }
    
///////////////
function get_all_gaji($array, $nominal_lembur, $hari_masuk_kerja, $id_karyawan, $param_denda_terlambat,$param_denda_early_leave, $param_denda_tidak_masuk,$param_hutang_asuransi,$param_cut_off,$param_karyawan_baru,$hari_lembur_holiday){

    $total = 0;
    foreach ($array as $key) {
        if($key->id_jenis_gaji != 4){//selain adjustment minus
            if($key->periode_hitung == 1){//per bulan
                $total += $key->nominal;
            }else if($key->periode_hitung == 2){// per hari
                $total += (ceil($key->nominal) * $hari_masuk_kerja);
            }else if($key->periode_hitung == 3){// per hari lebur holiday
                $total += (ceil($key->nominal) * $hari_lembur_holiday);
            }
        }else if($key->id_jenis_gaji == 4){
            if($key->periode_hitung == 1){//per bulan
                $total -= $key->nominal;
            }else if($key->periode_hitung == 2){// per hari
                $total -= (ceil($key->nominal) * $hari_masuk_kerja);
            }else if($key->periode_hitung == 3){// per hari lebur holiday
                $total += (ceil($key->nominal) * $hari_lembur_holiday);
            }
        }
    }

    $total += $nominal_lembur;
    $total -= $this->hitung_denda_terlambat($id_karyawan,$param_denda_terlambat);
    $total -= $this->hitung_denda_early_leave($id_karyawan,$param_denda_early_leave);
    $total -= $this->hitung_denda_tidak_masuk($id_karyawan,$param_denda_tidak_masuk);
    $total -= $this->hitung_hutang_asuransi_ekses($id_karyawan,$param_hutang_asuransi);
    
    if ($param_cut_off['tanggal_akhir_kontrak'] != null) {        
        $periode_gaji = Carbon::createFromDate($param_cut_off['periode_tahun'],$param_cut_off['periode_bulan'])->format('Y-m');
        $periode_cut_off = Carbon::parse($param_cut_off['tanggal_akhir_kontrak'])->format('Y-m');
        if ($param_cut_off['tanggal_akhir_kontrak'] < $param_cut_off['tanggal_gajian'] && $periode_cut_off == $periode_gaji) {
            $total -= $this->hitung_cut_off($id_karyawan,$param_cut_off);
        }
    }
    
    if ($param_karyawan_baru['tanggal_masuk'] != null) {        
        $tgl1 = Carbon::createFromDate($param_karyawan_baru['periode_tahun'],$param_cut_off['periode_bulan'],1)->format('Y-m-d');
        $periode_masuk = Carbon::parse($param_karyawan_baru['tanggal_masuk'])->format('Y-m');
        if ($param_karyawan_baru['tanggal_masuk'] > $tgl1 && $periode_gaji == $periode_masuk) {
            $total -= $this->potongan_gaji_karyawan_baru($id_karyawan,$param_karyawan_baru);
        }
    }

    return $total;
}

function hitung_denda_terlambat($id_karyawan, $param_denda_terlambat){//param disesuaikan
    // $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
    // $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');

    $data_terlambat = DB::table('t_absensi')->where('id_karyawan',$id_karyawan)->whereBetween('tanggal',[$param_denda_terlambat['tanggal_start'],$param_denda_terlambat['tanggal_end']])->where('deleted','1')->pluck('menit_terlambat')->toArray();
    //. misalkan periode gaji == mei 2022, maka yang diselect dari 11 april 2022 s/d 10 mei 2022

    $total_menit = 0;
    $total_jam = 0;
    $denda_terlambat = 0;

    foreach($data_terlambat as $value){
        if ($value >= 30) {
            $total_menit = $value;
            $total_jam = floor($value / 30)/2; 

            $denda_terlambat += round($param_denda_terlambat['gaji_per_jam'])*$total_jam;
        }
    }

    DB::table('t_gaji_karyawan_periode_det')->insert([
            'id_gaji_karyawan_periode' => $param_denda_terlambat['t_gaji_karyawan_periode'],
            'id_karyawan' => $id_karyawan,
            'id_gaji' => 0,
            'nama_gaji' => 'Denda Terlambat',
            'nominal' => $denda_terlambat * (-1),
            'created_by' => Auth::user()->id_user,
            'updated_by' => Auth::user()->id_user,
        ]);

    //cara hitung nya sama spti hitung lembur. menit_terlambat di convert ke jam dan di bulatkan keatas per 0.5, lalu di kali gaji_per_jam
    return $denda_terlambat;
}

function hitung_denda_early_leave($id_karyawan, $param_denda_early_leave){//param disesuaikan
    // $tanggal_start = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),11)->subMonths(1)->format('Y-m-d');
    // $tanggal_end = Carbon::createFromDate(Session::get('periode_tahun'),Session::get('periode_bulan'),10)->format('Y-m-d');

    $data_early_leave = DB::table('t_absensi')->where('id_karyawan',$id_karyawan)->whereBetween('tanggal',[$param_denda_early_leave['tanggal_start'],$param_denda_early_leave['tanggal_end']])->where('deleted','1')->pluck('menit_early_leave')->toArray();
    //. misalkan periode gaji == mei 2022, maka yang diselect dari 11 april 2022 s/d 10 mei 2022

    $total_menit = 0;
    $total_jam = 0;
    $denda_early_leave = 0;

    foreach($data_early_leave as $value){
        if ($value >= 30) {
            $total_menit = $value;
            $total_jam = floor($value / 30)/2; 

            $denda_early_leave += round($param_denda_early_leave['gaji_per_jam'])*$total_jam;
        }
    }

    DB::table('t_gaji_karyawan_periode_det')->insert([
            'id_gaji_karyawan_periode' => $param_denda_early_leave['t_gaji_karyawan_periode'],
            'id_karyawan' => $id_karyawan,
            'id_gaji' => 0,
            'nama_gaji' => 'Denda Early Leave',
            'nominal' => $denda_early_leave * (-1),
            'created_by' => Auth::user()->id_user,
            'updated_by' => Auth::user()->id_user,
        ]);

    //cara hitung nya sama spti hitung lembur. menit_terlambat di convert ke jam dan di bulatkan keatas per 0.5, lalu di kali gaji_per_jam
    return $denda_early_leave;
}

function hitung_denda_tidak_masuk($id_karyawan,$param_denda_tidak_masuk){
    $gaji_per_hari = $param_denda_tidak_masuk['gaji_pokok'] / 22;
    $denda = $param_denda_tidak_masuk['jumlah_hari'] * $gaji_per_hari;

    DB::table('t_gaji_karyawan_periode_det')->insert([
            'id_gaji_karyawan_periode' => $param_denda_tidak_masuk['t_gaji_karyawan_periode'],
            'id_karyawan' => $id_karyawan,
            'id_gaji' => 0,
            'nama_gaji' => 'Denda Tidak Masuk',
            'nominal' => $denda * (-1),
            'created_by' => Auth::user()->id_user,
            'updated_by' => Auth::user()->id_user,
        ]);

    return $denda;
}

function hitung_hutang_asuransi_ekses($id_karyawan,$param_hutang_asuransi){
    $hutang_karyawan = 0;

    $data_asuransi_ekses = DB::table('t_asuransi_det as a')
                            ->leftJoin('t_asuransi as b','a.id_asuransi','=','b.id_asuransi')
                            ->select('*')->where('b.id_karyawan',$id_karyawan)->where('a.bulan',$param_hutang_asuransi['periode_bulan'])->where('a.tahun',$param_hutang_asuransi['periode_tahun'])->first();
    if ($data_asuransi_ekses) {
        DB::table('t_asuransi_det as a')->where('id',$data_asuransi_ekses->id)->update(['status'=>'1']);
        $hutang_bayar = DB::table('t_asuransi_det as a')->where('id_asuransi',$data_asuransi_ekses->id_asuransi)->where('status','1')->sum('nominal_hutang');
        $sisa_hutang = DB::table('t_asuransi_det as a')->where('id_asuransi',$data_asuransi_ekses->id_asuransi)->where('status','0')->sum('nominal_hutang');
        
        DB::table('t_asuransi')->where('id_asuransi',$data_asuransi_ekses->id_asuransi)->update(['hutang_bayar'=>$hutang_bayar,'sisa_hutang'=>$sisa_hutang]);
        $hutang_karyawan = $data_asuransi_ekses->nominal_hutang;
    }

    DB::table('t_gaji_karyawan_periode_det')->insert([
            'id_gaji_karyawan_periode' => $param_hutang_asuransi['t_gaji_karyawan_periode'],
            'id_karyawan' => $id_karyawan,
            'id_gaji' => 0,
            'nama_gaji' => 'Hutang Asuransi Ekses',
            'nominal' => $hutang_karyawan * (-1),
            'created_by' => Auth::user()->id_user,
            'updated_by' => Auth::user()->id_user,
        ]);

    return $hutang_karyawan;
}

function hitung_cut_off($id_karyawan,$param_cut_off){
    $hari_kerja = $param_cut_off['hari_kerja'] == '1' ? 22 : 26;
    $kerja_mulai_tgl1 = Carbon::createFromDate($param_cut_off['periode_tahun'],$param_cut_off['periode_bulan'],1)->format('Y-m-d');
    $hari_masuk_kerja_mulai_tgl1 = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->where('b.id_karyawan',$id_karyawan)->where('b.bulan',$param_cut_off['periode_bulan'])
                                ->where('b.tahun',$param_cut_off['periode_tahun'])->where('id_tipe_absensi','1')
                                ->whereBetween('a.tanggal',[$kerja_mulai_tgl1,$param_cut_off['tanggal_gajian']])
                                ->where('id_tipe_absensi','1')
                                ->count();
    $sisa_hari_kerja = $hari_kerja - $hari_masuk_kerja_mulai_tgl1;

    $potongan_gaji = 0;
    if ($sisa_hari_kerja > 0) {
        $gaji_per_hari = $param_cut_off['gaji_pokok'] / 22;
        $potongan_gaji = $gaji_per_hari * $sisa_hari_kerja;
        
        DB::table('t_gaji_karyawan_periode_det')->insert([
                'id_gaji_karyawan_periode' => $param_cut_off['t_gaji_karyawan_periode'],
                'id_karyawan' => $id_karyawan,
                'id_gaji' => 0,
                'nama_gaji' => 'Potongan Cut Off',
                'nominal' => $potongan_gaji * (-1),
                'created_by' => Auth::user()->id_user,
                'updated_by' => Auth::user()->id_user,
            ]);
    }

    return $potongan_gaji;
}

function potongan_gaji_karyawan_baru($id_karyawan,$param_karyawan_baru){
    $gaji_per_hari = $param_karyawan_baru['gaji_pokok'] / 22;
    // $kerja_mulai_tgl1 = Carbon::createFromDate($param_karyawan_baru['periode_tahun'],$param_karyawan_baru['periode_bulan'],1)->format('Y-m-d');
    $hari_masuk_kerja_mulai_tgl1 = DB::table('t_report_absensi_det as a')
                                ->leftJoin('t_report_absensi as b','a.id_report_absensi','=','b.id_report_absensi')
                                ->where('b.id_karyawan',$id_karyawan)->where('b.bulan',$param_karyawan_baru['periode_bulan'])
                                ->where('b.tahun',$param_karyawan_baru['periode_tahun'])->where('id_tipe_absensi','1')
                                ->whereBetween('a.tanggal',[$param_karyawan_baru['tanggal_masuk'],$param_karyawan_baru['tanggal_gajian']])
                                ->where('id_tipe_absensi','1')
                                ->count();
    $temp = $gaji_per_hari * $hari_masuk_kerja_mulai_tgl1;
    $potongan_gaji = $param_karyawan_baru['gaji_pokok'] - $temp;
        
    DB::table('t_gaji_karyawan_periode_det')->insert([
            'id_gaji_karyawan_periode' => $param_karyawan_baru['t_gaji_karyawan_periode'],
            'id_karyawan' => $id_karyawan,
            'id_gaji' => 0,
            'nama_gaji' => 'Potongan Gaji Karyawan Baru',
            'nominal' => $potongan_gaji * (-1),
            'created_by' => Auth::user()->id_user,
            'updated_by' => Auth::user()->id_user,
        ]);

    return $potongan_gaji;
}

function hitung_pph21($data_tarif_pph,$pkp){

	$arr_nominal_pph = array();
	$pph21_setahun = 0;

	foreach ($data_tarif_pph as $key) {

		# code...
		$pengkali = 0;

		if($pkp > 0){
			if($pkp < $key['batas_atas']){
				$pengkali = $pkp;
			}else{
				$pengkali = $key['batas_atas'];
			}

			$det['persen'] = $key['tarif'];
			$det['pengkali'] = $pengkali;

			$nominal = $key['tarif'] * $pengkali / 100;
			$pph21_setahun += $nominal;
			$det['nominal'] = $nominal;

			array_push($arr_nominal_pph, $det);

			
		}

		$pkp = $pkp - $key['batas_atas'];

	}

	return $pph21_setahun;

}

function get_gaji_pokok($array){
	$ret = 0;
	foreach ($array as $key) {
		# code...
		if($key->id_gaji == 1){
			$ret = $key->nominal;
			break;
		}
	}

	return $ret;
}

function get_tunjangan_tetap($array){
    $ret = 0;

    foreach ($array as $key) {
        # code...
        if($key->id_jenis_gaji == 2){
            $ret += $key->nominal;
        }
    }

    return $ret;
}

function get_tunjangan($array,$hari_masuk_kerja){
	$ret = 0;

	foreach ($array as $key) {
		# code...
		if($key->id_jenis_gaji != 1){
            if ($key->periode_hitung == 2) {
			     $ret += ($key->nominal*$hari_masuk_kerja);
            }else{
                $ret += $key->nominal;
            }
		}
	}

	return $ret;
}

function get_setting_by_kode($array, $kode){
	$ret = "";
	foreach ($array as $key) {
		# code...
		if($key->kode == $kode){
			$ret = $key->nilai;
			break;
		}
	}

	return $ret;
}

}