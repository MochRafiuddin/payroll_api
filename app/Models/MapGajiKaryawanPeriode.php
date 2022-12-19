<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapGajiKaryawanPeriode extends Model
{
    use CreatedUpdatedBy;
    use HasFactory;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = 'map_gaji_karyawan_periode';
    protected $primaryKey = 'id';
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
    public static function updateAllDeleted($id_periode)
    {
        return self::where("id_periode",$id_periode)->update(['deleted'=>0]);
    }
}
