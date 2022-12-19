<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TLembur extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "t_lembur";
    protected $primaryKey = 'id_lembur';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $guarded = [];
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
