<?php

namespace App\Http\Controllers;

use App\Models\MKaryawan;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Auth;
use Carbon\Carbon;

class CAsuransiEkses extends Controller
{
    use Helper;
    public function index()
    {
        return view('asuransi_ekses.index')->with('title','Asuransi Ekses');
    }

    public function form_tambah()
    {
        $m_karyawan = MKaryawan::withDeleted()->where('aktif',1)->get();
        $bulan_aktif = Session::get('periode_bulan');
        $tahun_aktif = Session::get('periode_tahun');
        return view('asuransi_ekses.form', compact('m_karyawan','bulan_aktif','tahun_aktif'))->with('title','Tambah Asuransi Ekses');
    }

    public function form_edit($id)
    {
        $t_asuransi = DB::table('t_asuransi')->where('id_asuransi',$id)->first();
        $m_karyawan = MKaryawan::from('m_karyawan as a')
                        ->leftJoin('map_gaji_karyawan as b','a.id_karyawan','=','b.id_karyawan')
                        ->select('a.*','b.nominal as gaji_pokok')
                        ->where('a.id_karyawan',$t_asuransi->id_karyawan)->where('b.id_gaji','1')
                        ->where('a.aktif',1)
                        ->first();
        $m_karyawan->gaji_30 = $m_karyawan->gaji_pokok*(30/100);
        $t_asuransi->hutang_karyawan = DB::table('t_asuransi_det')->where('id_asuransi',$id)->where('deleted','1')->sum('nominal_hutang');
        $bulan_aktif = Session::get('periode_bulan');
        $tahun_aktif = Session::get('periode_tahun');
        $periode_bulan = DB::table('t_asuransi_det')->where('id_asuransi',$id)->where('deleted','1')->first()->bulan ?? $bulan_aktif;
        $periode_tahun = DB::table('t_asuransi_det')->where('id_asuransi',$id)->where('deleted','1')->first()->tahun ?? $tahun_aktif;
        $t_asuransi->periode_aktif = $this->convertBulan($periode_bulan).' '.$periode_tahun;
                        // dd($m_karyawan);
        return view('asuransi_ekses.form-edit', compact('m_karyawan','bulan_aktif','tahun_aktif','t_asuransi'))->with('title','Edit Asuransi Ekses');
    }

    public function get_cicilan_asuransi(Request $request)
    {
        $id_asuransi = $request->id_asuransi;
        $t_asuransi_det = DB::table('t_asuransi_det')->where('id_asuransi',$id_asuransi)->where('deleted','1')->get();
        $no=1;
        $data=[];
        foreach ($t_asuransi_det as $value) {
            $value->no = $no;
            $value->potongan_gaji = $value->nominal_hutang;
            $value->periode_bulan = $value->bulan;
            $value->periode_tahun = $value->tahun;
            $value->sudah_bayar = $value->status;
            $no++;
            $data[] = $value;
        }
        return $data;
    }

    public function update_cicilan_asuransi(Request $request)
    {
        $id_asuransi = $request->id_asuransi;
        $data_cicilan = $request->data_cicilan;
        $arr_ins_t_asuransi_det = [];
        if ($data_cicilan) {
            foreach ($data_cicilan as $value) {
                $arr_ins_t_asuransi_det[] = [
                    'id_asuransi' => $id_asuransi,
                    'nominal_hutang' => $value['potongan_gaji'],
                    'bulan' => $value['bulan'],
                    'tahun' => $value['tahun'],
                    'status' => $value['sudah_bayar'],
                    'tgl_gaji' => Carbon::createFromDate($value['tahun'],$value['bulan'],10)->format('Y-m-d'),
                    "created_date" => date('Y-m-d H:i:s'),
                    "created_by" => Auth::user()->id_user,
                    "edited_date" => date('Y-m-d H:i:s'),
                    "edited_by" => Auth::user()->id_user,
                    "deleted" => "1",
                ];
            }
        }

        if (count($arr_ins_t_asuransi_det) > 0) {
            DB::table('t_asuransi_det')->where('id_asuransi',$id_asuransi)->update(['deleted'=>0]);
            DB::table('t_asuransi_det')->insert($arr_ins_t_asuransi_det);
        }

        return true;
    }

    public function datatable_asuransi()
    {
        $query = DB::table('t_asuransi as a')
                    ->leftJoin('m_karyawan as b','a.id_karyawan','=','b.id_karyawan')
                    ->select(
                        'a.*',
                        'b.nama_karyawan',
                        'b.nik',
                        'b.limit_asuransi',
                    )
                    ->where('a.deleted',1);

        $t_asuransi_det = DB::table('t_asuransi_det')->where('deleted','1')->get();

        return DataTables::of($query)
            ->addColumn('action', function ($row) use ($t_asuransi_det) {
                $notif = $this->getNotifDelete($t_asuransi_det,$row->id_asuransi);
                $btn = '';
                if (Helper::can_akses('penggajian_asuransi_ekses_edit')) {
                    $btn .= '<a href="'.url('gaji_karyawan/edit-asuransi/'.$row->id_asuransi).'" class="text-warning"><span class="mdi mdi-pen"></span></a>';
                }            
                if (Helper::can_akses('penggajian_asuransi_ekses_delete')) {
                    $btn .= '<a href="'.url('gaji_karyawan/delete-asuransi/'.$row->id_asuransi).'" class="text-danger delete mr-2" notif="'.$notif.'"><span class="mdi mdi-delete"></span></a>';                    
                }    
                return $btn;
            }) 
            ->editColumn('limit_asuransi', function ($row) {
                return $this->ribuan($row->limit_asuransi);
            })->editColumn('biaya_rs', function ($row) {
                return $this->ribuan($row->biaya_rs);
            })->editColumn('hutang', function ($row) {
                return $this->ribuan($row->hutang);
            })->editColumn('hutang_bayar', function ($row) {
                return $this->ribuan($row->hutang_bayar);
            })->editColumn('sisa_hutang', function ($row) {
                return $this->ribuan($row->sisa_hutang);
            })
            // ->addColumn('periode', function ($row) {
            //     $html = '';
            //     $periode_bulan = Session::get('periode_bulan');
            //     $periode_tahun = Session::get('periode_tahun');
            //     if($periode_bulan != null){
            //         $html = '<div class="badge badge-outline-success badge-pill">'.$this->convertBulan($periode_bulan).' '.$periode_tahun.'</div>';
            //     }
            //     return $html;
                
            // })    
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }

    public function getNotifDelete($data,$id_asuransi){
        $notif = "";
        $arr_periode = [];
        foreach ($data as $value) {
            if ($value->id_asuransi == $id_asuransi && $value->status == '1') {
                $arr_periode[] = $this->convertBulan($value->bulan).' '.$value->tahun;
            }
        }
        if (count($arr_periode) > 0) {
            $text_periode = implode(', ', $arr_periode);
            $notif = "Hutang karyawan pada bulan ".$text_periode." sudah masuk ke perhitungan gaji, disarankan untuk menghitung ulang gaji pada bulan ".$text_periode." jika Anda ingin menghapus data ini.";
        }
        return $notif;
    }

    public function calculate_gaji_karyawan(){
        $this->hitung();
        return redirect()->route('riwayat-gaji-index');
    }

    public function get_asuransi_ekses(Request $request){
        $id_karyawan = $request->id_karyawan;
        $biaya_rs = str_replace('.', '', $request->biaya_rs);
        $is_generate = $request->is_generate ?? 0;

        $karyawan = MKaryawan::from('m_karyawan as a')
                        ->leftJoin('map_gaji_karyawan as b','a.id_karyawan','=','b.id_karyawan')
                        ->select('a.*','b.nominal as gaji_pokok')
                        ->where('a.id_karyawan',$id_karyawan)->where('b.id_gaji','1')
                        ->first();

        $data_cicilan = [];

        if ($karyawan) {
            $karyawan->periode_aktif = $this->convertBulan(Session::get('periode_bulan')).' '.Session::get('periode_tahun');
            $karyawan->gaji_30 = $karyawan->gaji_pokok*(30/100);
            $karyawan->biaya_rs = $biaya_rs;
            $karyawan->hutang_karyawan = doubleval($biaya_rs) - doubleval($karyawan->limit_asuransi) < 0 ? 0 : abs(doubleval($biaya_rs) - doubleval($karyawan->limit_asuransi));
            $total_potongan_gaji = 0;

            $periode_bulan = Session::get('periode_bulan')-1;
            $periode_tahun = Session::get('periode_tahun');
            if ($is_generate) {
                if ($karyawan->hutang_karyawan < $karyawan->gaji_30) {
                    if ($karyawan->hutang_karyawan > 0) {
                        $data_cicilan[] = [
                            'no' => 1,
                            'potongan_gaji' => $karyawan->hutang_karyawan,
                            'periode_bulan' => intval($periode_bulan+1),
                            'periode_tahun' => intval($periode_tahun),
                            'sudah_bayar' => 'Belum',
                        ];
                        $total_potongan_gaji += $karyawan->hutang_karyawan;
                    }
                }else{
                    $n_potongan = floor($karyawan->hutang_karyawan / $karyawan->gaji_30); //cari berapa kali cicilan
                    $mod = $karyawan->hutang_karyawan % $karyawan->gaji_30; //cari sisa cicilan terakhir
                    $no=0;
                    for ($i=$no; $i < $n_potongan; $i++) {
                        if ($periode_bulan+1 > 12) { // jika index bulan lebih dari 12, masuk ke januari tahun berikutnya
                            $periode_bulan=0;
                            $periode_tahun++;
                        }
                        $data_cicilan[] = [
                            'no' => $i+1,
                            'potongan_gaji' => $karyawan->gaji_30,
                            'periode_bulan' => intval($periode_bulan+1),
                            'periode_tahun' => intval($periode_tahun),
                            'sudah_bayar' => 'Belum',
                        ];
                        $total_potongan_gaji += $karyawan->gaji_30;
                        $no++;
                        $periode_bulan++;
                    }
                    if ($mod > 0) {
                        $data_cicilan[] = [
                            'no' => $no+1,
                            'potongan_gaji' => $mod,
                            'periode_bulan' => intval($periode_bulan+1),
                            'periode_tahun' => intval($periode_tahun),
                            'sudah_bayar' => 'Belum',
                        ];
                        // $total_potongan_gaji += $mod;
                    }
                }
            }

            $karyawan->total_potongan_gaji = $total_potongan_gaji;
            $karyawan->data_cicilan = $data_cicilan;

        }else{
            return 0;
        }

        return $karyawan;
    }

    public function submit_asuransi_ekses(Request $request){
        $arr_ins_t_asuransi = [
            "id_karyawan" => $request->id_karyawan,
            "limit_asuransi" => $request->limit_asuransi,
            "biaya_rs" => $request->biaya_rs,
            "hutang" => $request->hutang,
            "hutang_bayar" => "0",
            "sisa_hutang" => $request->hutang,
            "created_date" => date('Y-m-d H:i:s'),
            "created_by" => Auth::user()->id_user,
            "edited_date" => date('Y-m-d H:i:s'),
            "edited_by" => Auth::user()->id_user,
            "deleted" => "1",
        ];

        DB::table('t_asuransi')->insert($arr_ins_t_asuransi);
        $t_asuransi = DB::table('t_asuransi')->where('created_by',Auth::user()->id_user)->orderBy('created_date','desc')->first();
        $id_periode = DB::table('m_periode')->where('bulan',Session::get('periode_bulan'))->where('tahun',Session::get('periode_tahun'))->first();
        $t_gaji_karyawan_periode = DB::table('t_gaji_karyawan_periode')->where('id_periode',$id_periode->id_periode)->where('id_karyawan',$request->id_karyawan)->where('deleted','1')->first();

        $arr_ins_t_asuransi_det = [];
        if ($request->data_cicilan) {
            foreach ($request->data_cicilan as $value) {
                $arr_ins_t_asuransi_det[] = [
                    'id_asuransi' => $t_asuransi->id_asuransi,
                    'nominal_hutang' => $value['potongan_gaji'],
                    'bulan' => $value['bulan'],
                    'tahun' => $value['tahun'],
                    'status' => '0',
                    'tgl_gaji' => Carbon::createFromDate($value['tahun'],$value['bulan'],10)->format('Y-m-d'),
                    "created_date" => date('Y-m-d H:i:s'),
                    "created_by" => Auth::user()->id_user,
                    "edited_date" => date('Y-m-d H:i:s'),
                    "edited_by" => Auth::user()->id_user,
                    "deleted" => "1",
                ];
            }
        }

        if (count($arr_ins_t_asuransi_det) > 0) {
            DB::table('t_asuransi_det')->insert($arr_ins_t_asuransi_det);
        }

        return true;
    }

    public function delete($id)
    {
        DB::table('t_asuransi')->where('id_asuransi',$id)->update(['deleted'=>0]);
        DB::table('t_asuransi_det')->where('id_asuransi',$id)->update(['deleted'=>0]);

        return redirect()->route('asuransi-ekses-index')->with('msg','Sukses Menghapus Data');

    }
}
