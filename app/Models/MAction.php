<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAction extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "m_action";
    protected $primaryKey = 'id';    

    public static function withMenu($id)
    {
        return self::where('id_menu',$id);
    }
}
