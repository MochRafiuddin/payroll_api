<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogFingerprintUser extends Model
{
    protected $table = "log_fingerprint_user";
    protected $primaryKey = 'userid';
    protected $guarded = [];
    public $timestamps = false;
}
