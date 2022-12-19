<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class TGajiKaryawanPeriode extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = "t_gaji_karyawan_periode";
    protected $fillable = [
        'deleted',
    ];
    public static function withDeleted()
    {
        return self::where('deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
    public function details()
    {
        return self::hasMany(TGajiKaryawanPeriodeDet::class, 'id_gaji_karyawan_periode','id')
                ->leftJoin('m_gaji','t_gaji_karyawan_periode_det.id_gaji','=','m_gaji.id_gaji')
                ->select('t_gaji_karyawan_periode_det.*','m_gaji.id_jenis_gaji','m_gaji.nama_gaji','t_gaji_karyawan_periode_det.nama_gaji as nama_gaji_temp','m_gaji.periode_hitung')
                ->where('t_gaji_karyawan_periode_det.deleted','1')->where('m_gaji.deleted','1')->orWhere('t_gaji_karyawan_periode_det.id_gaji','0');
    }
}
