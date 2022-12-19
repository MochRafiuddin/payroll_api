<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAbsensi extends Model
{
    protected $table = "log_absensi";
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;
}
