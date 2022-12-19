<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\TAbsensi;
use Carbon\Carbon;

class ExportAbsensiLembur implements FromView
{
       public function __construct(string $keyword,$keyword1,$keyword2)
    {
        $this->karyawan = $keyword;
        $this->awal = $keyword1;
        $this->akhir = $keyword2;
    }
    public function view(): View
    {
        $start = Carbon::createFromFormat('m-d-Y',$this->awal)->format('Y-m-d');
        $end = Carbon::createFromFormat('m-d-Y',$this->akhir)->format('Y-m-d');
        if ($this->karyawan!=0) {
            $model = TAbsensi::where('t_absensi.deleted',1)
            ->selectRaw('m_karyawan.nama_karyawan,m_karyawan.employee_id,m_karyawan.no_bpjs,t_absensi.tanggal,m_shift.nama_shift,t_absensi.jam_keluar_shift,t_absensi.jam_masuk_shift,t_absensi.tanggal_masuk,t_absensi.tanggal_keluar,m_departemen.nama_departemen,m_karyawan.id_karyawan')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_absensi.id_karyawan','left')
            ->join('m_shift','m_shift.id_shift','=','t_absensi.id_shift','left')
            ->join('m_departemen','m_departemen.id_departemen','=','m_karyawan.id_departemen','left')
            ->where('id_tipe_absensi', 1)
            ->where('m_karyawan.id_karyawan',$this->karyawan)
            ->whereBetween('t_absensi.tanggal', [$start, $end])
            ->orderBy('t_absensi.id_karyawan')
            ->orderBy('t_absensi.tanggal')
            ->get(); 
        }else{
            $model = TAbsensi::where('t_absensi.deleted',1)
            ->selectRaw('m_karyawan.nama_karyawan,m_karyawan.employee_id,m_karyawan.no_bpjs,t_absensi.tanggal,m_shift.nama_shift,t_absensi.jam_keluar_shift,t_absensi.jam_masuk_shift,t_absensi.tanggal_masuk,t_absensi.tanggal_keluar,m_departemen.nama_departemen,m_karyawan.id_karyawan')
            ->join('m_karyawan','m_karyawan.id_karyawan','=','t_absensi.id_karyawan','left')
            ->join('m_shift','m_shift.id_shift','=','t_absensi.id_shift','left')
            ->join('m_departemen','m_departemen.id_departemen','=','m_karyawan.id_departemen','left')
            ->where('id_tipe_absensi', 1)
            ->where('m_karyawan.deleted', 1)
            ->whereBetween('t_absensi.tanggal', [$start, $end])
            ->orderBy('t_absensi.id_karyawan')
            ->orderBy('t_absensi.tanggal')
            ->get(); 
        }
        return view('absensi_lembur.export', [
            'hsl' => $model
        ]);
    }
}
