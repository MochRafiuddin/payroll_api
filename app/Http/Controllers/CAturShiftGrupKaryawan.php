<?php

namespace App\Http\Controllers;

use App\Models\MShiftGrup;
use App\Models\MShift;
use App\Models\MGrupKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Validation\Rule;
use Excel;
use App\Exports\FormatExcelImportShiftGrup;
use App\Imports\ImportShift;
use App\Models\MKaryawan;
use App\Models\MShiftKaryawan;
use App\Models\TAbsensi;
use App\Models\LogAbsensi;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Auth;

class CAturShiftGrupKaryawan extends Controller
{
    public function index()
    {
        $m_shift = MShift::withDeleted()->get();
        return view('atur_shift_grup_karyawan.index', compact('m_shift'))->with('title','Atur Shift Grup Karyawan');
    }

    public function set_shift(Request $request)
    {
        $id_grup_karyawan = $request->id_grup_karyawan;
        $tanggal = $request->tanggal;
        $id_shift = $request->id_shift;

        $arr_id_karyawan = MKaryawan::where('id_grup_karyawan',$id_grup_karyawan)->pluck('id_karyawan')->toArray();
        $arr_ins_shift_grup = [
            'id_grup_karyawan' => $id_grup_karyawan,
            'id_shift' => $id_shift,
            'tanggal' => $tanggal,
            'created_by' => Auth::user()->id_user,
        ];

        $arr_ins_shift_karyawan = [];
        $arr_ins_t_absensi = [];
        foreach ($arr_id_karyawan as $value) {
            $row = [
                'id_karyawan' => $value,
                'id_shift' => $id_shift,
                'tanggal' => $tanggal,
                'created_by' => Auth::user()->id_user,
            ];
            $row1 = [
                'id_karyawan' => $value,
                'id_shift' => $id_shift,
                'tanggal' => $tanggal,
                'id_tipe_absensi' => 3,
                'created_by' => Auth::user()->id_user,
            ];
            $arr_ins_shift_karyawan[] = $row;
            $arr_ins_t_absensi[] = $row1;
        }

        $next_shift = MShift::where('id_shift',$id_shift)->where('deleted','1')->first()->kode_shift;
        $prev_shift = MShiftGrup::from('m_shift_grup as a')
                            ->leftJoin('m_shift as b','a.id_shift','=','b.id_shift')
                            ->select('b.kode_shift')
                            ->where('a.id_grup_karyawan',$id_grup_karyawan)->where('a.tanggal',$tanggal)
                            ->where('a.deleted','1')
                            ->first()->kode_shift ?? '';

        if ($prev_shift == $next_shift) {
            return redirect()->back()->withInput();
        }

        MShiftGrup::where('id_grup_karyawan',$id_grup_karyawan)->where('tanggal',$tanggal)->update(['deleted'=>'0']);
        MShiftKaryawan::where('tanggal',$tanggal)->whereIn('id_karyawan',$arr_id_karyawan)->update(['deleted'=>'0']);
        MShiftGrup::insert($arr_ins_shift_grup);
        MShiftKaryawan::insert($arr_ins_shift_karyawan);

        $shift = MShift::where('id_shift',$id_shift)->where('deleted','1')->first();
        LogAbsensi::where('tanggal_shift',$tanggal)->whereIn('id_karyawan',$arr_id_karyawan)->update(['id_shift'=>$id_shift,'jam_masuk_shift'=>$shift->jam_masuk,'jam_keluar_shift'=>$shift->jam_keluar]);

        if ($prev_shift == 'libur' && $next_shift != 'libur') {
            TAbsensi::where('tanggal',$tanggal)->whereIn('id_karyawan',$arr_id_karyawan)->update(['deleted'=>'0']);
        }elseif ($prev_shift != 'libur' && $next_shift == 'libur') {
            TAbsensi::insert($arr_ins_t_absensi);
        }

        return redirect()->back()->withInput();
    }

    public function datatable(Request $request)
    {
        $month_year = $request->month_year;

        $month_year = explode("-", $month_year);

        $data_grup = MGrupKaryawan::withDeleted()->select("nama_grup","id_grup_karyawan")->get();

        $pluck_id_grup = $data_grup->pluck('id_grup_karyawan')->toArray();

        $data_shift_grup = MShiftGrup::withDeleted()
                            ->join("m_shift","m_shift.id_shift","=","m_shift_grup.id_shift")
                            ->whereIn("id_grup_karyawan",$pluck_id_grup)
                            ->whereMonth('tanggal', '=', $month_year[0])
                            ->whereYear('tanggal', '=', $month_year[1])
                            ->select("m_shift_grup.*","m_shift.nama_shift")
                            ->orderBy("id_grup_karyawan")
                            ->orderBy("tanggal")
                            ->get();

        // select a.*,b.nama_shift from m_shift_grup a, m_shift b where a.id_shift = b.id_shift and a.deleted = 1 and a.id_grup_karyawan = $id_grup_karyawan

        $data = [
            'data_grup' => $data_grup,
            'data_shift_grup' => $data_shift_grup,
        ];

        $response = array("message"=>"ok","data"=>$data);
        return response()->json($response,200);
    }

    public function format_excel()
    {
        // return Excel::download(new FormatExcelImportShiftGrup(), 'format-import-shift-grup-karyawan.xlsx');
        $file= public_path(). "/download/format import grup shift.xlsx";

        return Response::download($file);
    }

    public function create_save(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(),[
            'nama_shift' => 'required', 
            'hari_kerja' => 'required', 
            'kode_shift' => [
                "required",
                Rule::unique('m_shift', 'kode_shift')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    }),
            ],
            'jam_masuk' => 'required', 
            'jam_keluar' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mShift = new MShift;
        $mShift->nama_shift = $request->nama_shift;
        $mShift->hari_kerja = $request->hari_kerja;
        $mShift->kode_shift = $request->kode_shift;
        $mShift->jam_masuk = $request->jam_masuk;
        $mShift->jam_keluar = $request->jam_keluar;
        $mShift->save();
        return redirect()->route('atur-shift-grup-karyawan-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id)
    {
        // dd(MAgama::find($id));
        
        return view('shift.form')
            ->with('data',MShift::find($id))
            ->with('title','Shift')
            ->with('titlePage','Edit')
            ->with('url',url('shift/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_shift' => 'required', 
            'hari_kerja' => 'required', 
            'kode_shift' => [
                "required",
                Rule::unique('m_shift', 'kode_shift')
                    ->where(static function ($query) {
                        return $query->where('deleted',1);
                    })->ignore($id, 'id_shift'),
            ], 
            'jam_masuk' => 'required', 
            'jam_keluar' => 'required', 
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }
        $mShift = MShift::find($id);
        $mShift->nama_shift = $request->nama_shift;
        $mShift->hari_kerja = $request->hari_kerja;
        $mShift->kode_shift = $request->kode_shift;
        $mShift->jam_masuk = $request->jam_masuk;
        $mShift->jam_keluar = $request->jam_keluar;
        $mShift->update();
        return redirect()->route('atur-shift-grup-karyawan-index')->with('msg','Sukses Mengubah Data');

    }
    public function delete($id)
    {
        MShift::updateDeleted($id);
        return redirect()->route('atur-shift-grup-karyawan-index')->with('msg','Sukses Menambahkan Data');

    }
    public function import(Request $request)
    {

        // validasi
        $this->validate($request, [
            'file_excel' => 'required|mimes:csv,xls,xlsx'
        ]);

        // menangkap file excel
        $file = $request->file('file_excel');

        $grupKaryawan = MGrupKaryawan::withDeleted()->get();
        $mShift = MShift::withDeleted()->get();

        $import = new ImportShift($grupKaryawan,$mShift);

        Excel::import($import,$file);
        $allTanggal = $import->getAllTanggal();

        if($import->error() == 1){
            return response()->json(['status'=>true,'error'=>$import->error(),'msg_import'=>$import->message()]);
        }else{
            $dataShifKar = [];
            foreach($import->dataGrup() as $key){
                $grupKar = [];
                foreach($import->data() as $grup){
                    if($key == $grup["id_grup_karyawan"]){
                        array_push($grupKar,[
                            'id_shift' => $grup['id_shift'],
                            'tanggal' => $grup['tanggal'],
                            'created_by' => $grup['created_by'],
                            'updated_by' => $grup['updated_by'],
                            'id_grup_karyawan' => $grup["id_grup_karyawan"],
                        ]);
                    }
                }
                $mKaryawan = MKaryawan::withDeleted()->whereIn('id_grup_karyawan',$import->dataGrup());
                
                foreach($allTanggal as $det){
                    MShiftGrup::where('id_grup_karyawan',$key)->where('tanggal',$det)->delete();
                    MShiftKaryawan::whereIn('id_karyawan',$mKaryawan->pluck('id_karyawan')->toArray())->where('tanggal',$det)->delete();
                }
                // dd($grupKar);
                foreach($mKaryawan->get() as $kar){     // dobelnya disini
                    
                    foreach($grupKar as $data){
                        if ($data['id_grup_karyawan'] == $kar->id_grup_karyawan) {
                            $data['id_karyawan'] = $kar->id_karyawan;
                            unset($data['id_grup_karyawan']);
                            array_push($dataShifKar,$data);
                        }
                    }

                }
                
            }

            // dd($dataShifKar);
            MShiftGrup::insert($import->data());
            MShiftKaryawan::insert($dataShifKar);
            $mShiftKaryawan = MShiftKaryawan::whereIn('tanggal',$allTanggal)->whereIn('id_karyawan',$mKaryawan->pluck('id_karyawan')->toArray())->where('id_shift',1)->get(['id_karyawan','tanggal']);
            TAbsensi::whereIn('tanggal',$allTanggal)->whereIn('id_karyawan',$mKaryawan->pluck('id_karyawan')->toArray())->where('id_tipe_absensi',3)->delete();

            $arr_absensi = [];
            foreach($mShiftKaryawan as $shiftKar){
                array_push($arr_absensi,[
                    'id_karyawan' => $shiftKar->id_karyawan,
                    'tanggal' => $shiftKar->tanggal,
                    'id_tipe_absensi' => 3,
                ]);
            }
            TAbsensi::insert($arr_absensi);
   
            return response()->json(['status'=>true,'error'=>$import->error(),'msg_import'=>'Import Shift Grup Karyawan Sukses']);
        }
    }
}
