<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MShiftGrup extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = "m_shift_grup";
    protected $primaryKey = 'id_shift_grup';
    protected $fillable = [
        'deleted','id_grup_karyawan','id_shift','tanggal'
    ];
    public static function withDeleted()
    {
        return self::where('m_shift_grup.deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
    public static function deleteRow($id,$tanggal)
    {
        return self::where('id_grup_karyawan',$id)->where('tanggal',$tanggal)->delete();
    }
}
