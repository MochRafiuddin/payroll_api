<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionBulk extends Model
{
    use HasFactory;

    protected $table = "action_bulk";
    protected $primaryKey = 'id_action';
}
