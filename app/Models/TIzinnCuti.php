<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TIzinnCuti extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "t_izin";
    protected $primaryKey = 'id_izin';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
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
    public static function destroyed($id)
    {
        return self::find($id)->delete();
    }
}
