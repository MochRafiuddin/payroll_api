<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CreatedUpdatedBy;

class LogSelfi extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "log_selfi";
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
}
