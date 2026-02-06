<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;

trait HasTeamScope
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootHasTeamScope(): void
    {
        // Global Scope for Filtering
        static::addGlobalScope('team', function (Builder $builder) {
            $team = current_team();
            if ($team) {
                $builder->where($builder->getModel()->getTable() . '.team_id', $team->id);
            }
        });

        // Automatic team_id Assignment on Creation
        static::creating(function ($model) {
            if (!$model->team_id) {
                $team = current_team();
                if ($team) {
                    $model->team_id = $team->id;
                }
            }
        });
    }
}
