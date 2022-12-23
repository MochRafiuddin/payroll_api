<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use DateTime;
use App\Models\TAbsensi;
use App\Models\MKaryawan;
use App\Models\MDepartement;
use App\Models\RefTipeAbsensi;
use App\Models\TReportAbsensi;
use App\Models\TReportAbsensiDetail;
use Carbon\Carbon;

use Illuminate\Support\Facades\Validator;

class CTotalAbsensi extends Controller
{
    public function index()
    {
        $departement = MDepartement::withDeleted()->orderBy("nama_departemen")->get();
        $ref_tipe_absensi = RefTipeAbsensi::withDeleted()->get();

        return view('total_absensi.index')
                ->with('title','Total Absensi')
                ->with('departemen',$departement)
                ->with('ref_tipe_absensi',$ref_tipe_absensi);
    }

    public function datatable(Request $request)
    {
        $id_departemen = $request->departemen;
        $tahun = $request->tahun;
        $bulan = (int) $request->bulan;

        $data_karyawan = MKaryawan::withDeleted()->select("nama_karyawan","id_karyawan");
        
        if ($id_departemen || $id_departemen != "") {
            $data_karyawan->where('id_departemen_label',$id_departemen);
        }

        $data_karyawan = $data_karyawan->get();

        $pluck_id_karyawan = $data_karyawan->pluck('id_karyawan')->toArray();

        $data_absensi = TReportAbsensi::withDeleted()
                            ->whereIn("id_karyawan",$pluck_id_karyawan)
                            ->where('tahun', '=', $tahun)
                            ->where('bulan', '=', $bulan)
                            ->select("t_report_absensi.*")
                            ->orderBy("id_karyawan")
                            ->orderBy("id_tipe_absensi")
                            ->get();

        // select a.*,b.nama_shift from m_shift_grup a, m_shift b where a.id_shift = b.id_shift and a.deleted = 1 and a.id_grup_karyawan = $id_grup_karyawan

        $data = [
            'data_karyawan' => $data_karyawan,
            'data_absensi' => $data_absensi,
        ];

        $response = array("message"=>"ok","data"=>$data);
        return response()->json($response,200);
    }

    public function detail(Request $request)
    {
        $id_report_absensi = $request->id;

        $data_absensi = TReportAbsensi::select("t_report_absensi.bulan", "t_report_absensi.tahun", "a.nama_tipe_absensi", "b.nama_karyawan", "a.kode_tipe_absensi")
                            ->leftJoin("ref_tipe_absensi as a","a.id_tipe_absensi","=","t_report_absensi.id_tipe_absensi")
                            ->leftJoin("m_karyawan as b","b.id_karyawan","=","t_report_absensi.id_karyawan")
                            ->where("id_report_absensi",$id_report_absensi)
                            ->where("t_report_absensi.deleted",1)
                            ->first();

        $data_absensi_det = TReportAbsensiDetail::withDeleted()
                            ->where("id_report_absensi",$id_report_absensi)->get();

        $data = [
            'data_absensi' => $data_absensi,
            'data_absensi_det' => $data_absensi_det,
        ];

        $response = array("message"=>"ok","data"=>$data);
        return response()->json($response,200);

    }
    
}
