<?php

if (!function_exists('team_slug')) {
    /**
     * Get the team slug from the request header.
     *
     * @return string|null
     */
    function team_slug(): ?string
    {
        return request()->header('team-slug');
    }
}

if (!function_exists('current_team')) {
    /**
     * Get the current team model based on the team-slug header.
     *
     * @return \App\Models\Team|null
     */
    function current_team(): ?\App\Models\Team
    {
        $slug = team_slug();
        if (!$slug) {
            return null;
        }

        return \App\Models\Team::where('slug', $slug)->first();
    }
}
