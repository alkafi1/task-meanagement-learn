<?php

namespace App\Models;

use App\Traits\HasTeamScope;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasTeamScope;

    protected $fillable = [
        'name',
        'guard_name',
        'team_id',
    ];
}
