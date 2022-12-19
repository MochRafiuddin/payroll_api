<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MShiftKaryawan extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = "m_shift_karyawan";
    protected $primaryKey = 'id_shift_karyawan';
    protected $fillable = [
        'deleted','id_karyawan','id_shift','tanggal','jam_keluar','jam_masuk'
    ];
    public static function withDeleted()
    {
        return self::where('m_shift_karyawan.deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
}
