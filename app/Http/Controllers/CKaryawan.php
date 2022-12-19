<?php

namespace App\Http\Controllers;

use App\Models\MAgama;
use App\Models\MBank;
use App\Models\MDepartement;
use App\Models\MJabatan;
use App\Models\MKaryawan;
use App\Models\MGrupKaryawan;
use App\Models\MShift;
use App\Models\MShiftKaryawan;
use App\Models\MStatusKaryawan;
use App\Models\MStatusKawin;
use App\Models\MShiftGrup;
use App\Models\MSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DataTables;
use DB;
use App\Traits\Helper;
use Session;
use Carbon\Carbon;

class CKaryawan extends Controller
{
    use Helper;

    public $validator =  [
            'nik' => 'required',
            'id_departemen' => 'required',
            'id_bank' => 'required',
            'nama_karyawan' => 'required',
            'no_rekening' => 'required',
            'jk' => 'required',
            'id_jabatan' => 'required',
            'nama_rekening' => 'required',
            'tanggal_lahir' => 'required',
            'id_status_karyawan' => 'required',
            'tipe_gajian' => 'required',
            'id_agama' => 'required',
            'tanggal_masuk' => 'required',
            'id_status_kawin' => 'required',
            'tanggal_akhir_kontrak' => 'required',
            'metode_pph21' => 'required',
            'alamat' => 'required',
            'status_npwp' => 'required',
            'status_bjps_kes' => 'required',
            'no_telp' => 'required',
            'email' => 'required',
            'kode_fingerprint' => 'required',
            'aktif' => 'required',
            'limit_asuransi' => 'required',
            'id_grup_karyawan' => 'required',
            'employee_id' => 'required',
            'no_bpjs' => 'required',
    ];
    public function index()
    {
        $departement = MDepartement::withDeleted()->orderBy("nama_departemen")->get();
        return view('karyawan.index')
            ->with('departemen',$departement)
            ->with('title','Karyawan');
    }
    public function create($title_page = 'Tambah')
    {
        $bank = MBank::withDeleted()->get();
        $departement = MDepartement::withDeleted()->get();
        $jabatan = MJabatan::withDeleted()->get();
        $status_karyawan = MStatusKaryawan::withDeleted()->get();
        $status_kawin = MStatusKawin::withDeleted()->get();
        $agama = MAgama::withDeleted()->get();
        $shift = MShift::withDeleted()->get();
        $grup_karyawan = MGrupKaryawan::withDeleted()->get();
        $cuti = MSetting::where('kode','max_cuti_setahun')->first();
        $url = url('karyawan/create-save');
        return view('karyawan.form')
            ->with('data',null)
            ->with('bank',$bank)
            ->with('cuti',$cuti)
            ->with('departement',$departement)
            ->with('jabatan',$jabatan)
            ->with('status_karyawan',$status_karyawan)
            ->with('status_kawin',$status_kawin)
            ->with('agama',$agama)
            ->with('shift',$shift)
            ->with('grup_karyawan',$grup_karyawan)
            ->with('title','Karyawan')
            ->with('id_karyawan','')
            ->with('id_grup_karyawan','')
            ->with('titlePage',$title_page)
            ->with('url',$url);
    }
    public function create_save(Request $request)
    {
        if($request->status_npwp == 1){
            $this->validator['no_npwp'] = 'required';
        }
        $validator = Validator::make($request->all(),$this->validator);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());
        }

        $mJabatan = new MKaryawan;
        $this->credentials($mJabatan,$request);
        $mJabatan->save();

        $user = auth()->user();

        $start_date = Carbon::createFromFormat('d/m/Y',substr($request->daterange, 0,10))->format('Y-m-d');
        $end_date = Carbon::createFromFormat('d/m/Y',substr($request->daterange, 13,10))->format('Y-m-d');
        $select_grup = MShiftGrup::select(DB::raw("'".$mJabatan->id_karyawan."','".$user->id_user."'"),'id_shift','tanggal')
                        ->where('m_shift_grup.deleted', 1)
                        ->whereBetween('m_shift_grup.tanggal', [$start_date,$end_date])
                        ->where('m_shift_grup.id_grup_karyawan', $request->id_grup_karyawan);
            
        $rows_grup_inserted = MShiftKaryawan::insertUsing(['id_karyawan','created_by','id_shift', 'tanggal'], $select_grup);


        return redirect()->route('karyawan-index')->with('msg','Sukses Menambahkan Data');
    }
    
    public function show($id,$id1)
    {
        // dd(MAgama::find($id));
        $karyawan = MKaryawan::find($id);
        $nama_grup_karyawan = MGrupKaryawan::find($karyawan->id_grup_karyawan)->nama_grup;
        $bank = MBank::withDeleted()->get();
        $departement = MDepartement::withDeleted()->get();
        $jabatan = MJabatan::withDeleted()->get();
        $status_karyawan = MStatusKaryawan::withDeleted()->get();
        $status_kawin = MStatusKawin::withDeleted()->get();
        $agama = MAgama::withDeleted()->get();
        $shift = MShift::withDeleted()->get();
        $grup_karyawan = MGrupKaryawan::withDeleted()->get();
        return view('karyawan.form_edit')
            ->with('data',MKaryawan::find($id))
            ->with('filter',$id1)
            ->with('bank',$bank)
            ->with('departement',$departement)
            ->with('jabatan',$jabatan)
            ->with('status_karyawan',$status_karyawan)
            ->with('status_kawin',$status_kawin)
            ->with('agama',$agama)
            ->with('shift',$shift)
            ->with('grup_karyawan',$grup_karyawan)
            ->with('title','Departement')
            ->with('titlePage','Edit')
            ->with('id_karyawan',$id)
            ->with('id_grup_karyawan',['id_grup_karyawan'=>$karyawan->id_grup_karyawan,'nama_grup'=>$nama_grup_karyawan])
            ->with('url',url('karyawan/show-save/'.$id));
    }
    public function show_save($id, Request $request)
    {
        
        if($request->status_npwp == 1){
            $this->validator['no_npwp'] = 'required';
        }
            $this->validator['nik'] = [
                "required",
                Rule::unique('m_karyawan', 'nik')->ignore($id,'id_karyawan')->where(function ($query) {
                    $query->where('deleted',1);
                }),
            ];
            
            $this->validator['kode_fingerprint'] = [
                "required",
                Rule::unique('m_karyawan', 'kode_fingerprint')->ignore($id,'id_karyawan')->where(function ($query) {
                    $query->where('deleted',1);
                }),
            ];
        $validator = Validator::make($request->all(),$this->validator);
        if ($validator->fails()) {
             redirect()->back()
                        ->withInput($request->all())
                        ->withErrors($validator->errors());            
            return response()->json(['status'=>true,'error'=>'error','msg'=>'Sukses Mengubah Data']);
        }

        $user = auth()->user();

        $mJabatan = MKaryawan::find($id);
        $id_grup_lama = $mJabatan->id_grup_karyawan;
        $this->credentials($mJabatan,$request);
        $mJabatan->update();

        if ($id_grup_lama != $request->id_grup_karyawan) {
        // dd($id_grup_lama,$request->id_grup_karyawan);

            $start_date = Carbon::parse(substr($request->daterange, 0,10))->format('Y-m-d');
            $end_date = Carbon::parse(substr($request->daterange, 13,10))->format('Y-m-d');
            MShiftKaryawan::where("id_karyawan",$id)->whereBetween('tanggal',[$start_date,$end_date])->delete();

            $select_grup = MShiftGrup::select(DB::raw("'".$id."','".$user->id_user."'"),'id_shift','tanggal')
                            ->where('m_shift_grup.deleted', 1)
                            ->whereBetween('m_shift_grup.tanggal', [$start_date,$end_date])
                            ->where('m_shift_grup.id_grup_karyawan', $request->id_grup_karyawan);

                
            $rows_grup_inserted = MShiftKaryawan::insertUsing(['id_karyawan','created_by','id_shift', 'tanggal'], $select_grup);
            
        }
        Session::flash('state', $request->filter); 
        return response()->json(['status'=>true,'error'=>'berhasil','msg'=>'Sukses Mengubah Data']);

    }

    public function cek_shift_karyawan(Request $request)
    {
        $id_karyawan = $request->id_karyawan;
        $shift = MShiftKaryawan::where("id_karyawan",$id_karyawan)->where('deleted','1')->count();

        if ($shift) {
            return 1;
        }

        return 0;

    }

    public function cek_shift(Request $request)
    {
        $id_grup_karyawan = $request->id_grup_karyawan;
        $shift = MShiftGrup::where("id_grup_karyawan",$id_grup_karyawan)->where('deleted','1')->count();

        if ($shift) {
            return 1;
        }

        return 0;

    }

    public function delete($id)
    {
        MKaryawan::updateDeleted($id);
        return redirect()->route('karyawan-index')->with('msg','Sukses Menambahkan Data');

    }
    public function datatable()
    {
        $departement = (!empty($_GET["departement"])) ? ($_GET["departement"]) : (0);

        $model = MKaryawan::withDeleted()->select("m_karyawan.*","m_grup_karyawan.nama_grup as nama_grup_karyawan","m_departemen.nama_departemen as nama_departemen")
                ->leftJoin('m_grup_karyawan', 'm_grup_karyawan.id_grup_karyawan', '=', 'm_karyawan.id_grup_karyawan')
                ->leftJoin('m_departemen', 'm_departemen.id_departemen', '=', 'm_karyawan.id_departemen');
        if ($departement != 0) {
            $model = $model->where('m_karyawan.id_departemen',$departement);
        }

        return DataTables::of($model)
            ->addColumn('action', function ($model) use ($departement) {
                $btn = '';
                if (Helper::can_akses('master_karyawan_delete')) {
                    $btn .= '<a href="'.url('karyawan/delete/'.$model->id_karyawan).'" class="text-primary delete mr-2"><span class="mdi mdi-delete"></span></a>';
                }
                if (Helper::can_akses('master_karyawan_edit')) {
                    $btn .= '<a href="'.url('karyawan/show/'.$model->id_karyawan.'/'.$departement).'" class="text-danger"><span class="mdi mdi-pen"></span></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
    }
    public function credentials($mJabatan,$request)
    {
        $mJabatan->nik = $request->nik;
        $mJabatan->id_departemen = $request->id_departemen;
        $mJabatan->id_bank = $request->id_bank;
        $mJabatan->nama_karyawan = $request->nama_karyawan;
        $mJabatan->no_rekening = $request->no_rekening;
        $mJabatan->jk = $request->jk;
        $mJabatan->id_jabatan = $request->id_jabatan;
        $mJabatan->nama_rekening = $request->nama_rekening;
        $mJabatan->tanggal_lahir = date('Y-m-d',strtotime($request->tanggal_lahir));
        $mJabatan->id_status_karyawan = $request->id_status_karyawan;
        $mJabatan->tipe_gajian = $request->tipe_gajian;
        $mJabatan->id_agama = $request->id_agama;
        $mJabatan->tanggal_masuk = date('Y-m-d',strtotime($request->tanggal_masuk));
        $mJabatan->id_status_kawin = $request->id_status_kawin;
        $mJabatan->tanggal_akhir_kontrak = date('Y-m-d',strtotime($request->tanggal_akhir_kontrak));
        $mJabatan->metode_pph21 = $request->metode_pph21;
        $mJabatan->alamat = $request->alamat;
        $mJabatan->status_npwp = $request->status_npwp;
        $mJabatan->no_npwp = $request->no_npwp;
        $mJabatan->status_bjps_kes = $request->status_bjps_kes;
        $mJabatan->no_telp = $request->no_telp;
        $mJabatan->email = $request->email;
        $mJabatan->kode_fingerprint = $request->kode_fingerprint;
        $mJabatan->aktif = $request->aktif;
        $mJabatan->limit_asuransi = str_replace(".","",$request->limit_asuransi);
        $mJabatan->id_grup_karyawan = $request->id_grup_karyawan;
        $mJabatan->employee_id = $request->employee_id;
        $mJabatan->no_bpjs = $request->no_bpjs;
        $mJabatan->max_izin = $request->max_izin;
    }

}
