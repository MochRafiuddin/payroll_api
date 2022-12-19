<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TIzinDetail extends Model
{
    use HasFactory;    
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "t_izin_detail";
}
