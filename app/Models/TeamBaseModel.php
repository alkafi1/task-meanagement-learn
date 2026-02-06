<?php

namespace App\Models;

use App\Traits\HasTeamScope;
use Illuminate\Database\Eloquent\Model;

abstract class TeamBaseModel extends Model
{
    use HasTeamScope;
}
