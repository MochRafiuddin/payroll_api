<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkedKaryawan extends Model
{
    use HasFactory;

    protected $table = "marked_karyawan";
    protected $primaryKey = 'id_marked';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
