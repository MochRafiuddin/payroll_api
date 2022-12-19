<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TReportAbsensiDetail extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "t_report_absensi_det";
    protected $primaryKey = 'id_report_absensi_det';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $fillable = [
        'deleted','id_report_absensi','tanggal'
    ];
    public static function withDeleted()
    {
        return self::where('deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
    public static function destroyed($id)
    {
        return self::find($id)->delete();
    }
}
