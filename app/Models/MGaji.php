<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MGaji extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $table = "m_gaji";
    protected $primaryKey = 'id_gaji';
    protected $fillable = [
        'deleted',
    ];
     public function jenis_gaji()
    {
        return $this->hasOne(RefJenisGaji::class,'id_jenis_gaji');
    }
    public static function withDeleted()
    {
        return self::where('deleted',1);
    }
    public static function updateDeleted($id)
    {
        return self::find($id)->update(['deleted'=>0]);
    }
   
}
