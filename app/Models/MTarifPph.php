<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MTarifPph extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;
    protected $table = "m_tarif_pph";
    protected $primaryKey = 'id_tarif_pph';
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
}
