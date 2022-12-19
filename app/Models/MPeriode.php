<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPeriode extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = "m_periode";
    protected $primaryKey = 'id_periode';
    protected $fillable = [
        'deleted','aktif'
    ];

    public static function withDeleted()
    {
        return self::where('deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
}
