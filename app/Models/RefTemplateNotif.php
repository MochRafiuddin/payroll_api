<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefTemplateNotif extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "ref_template_notif";
    protected $primaryKey = 'id_template_notif';
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    protected $fillable = [
        'deleted',
    ];
}
