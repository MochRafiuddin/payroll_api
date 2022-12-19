<?php

namespace App\Models;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapRoleAction extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $table = "map_role_action";
    protected $primaryKey = 'id';
    
    
}
