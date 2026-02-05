<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\TeamRequest;
use App\Http\Resources\SuperAdmin\TeamResource;
use App\Models\Team;
use App\Services\SuperAdmin\TeamService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    protected TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
        $this->authorizeResource(Team::class, 'team');
    }

    /**
     * Display a listing of teams.
     */
    public function index(): JsonResponse
    {
        $teams = $this->teamService->getAllTeams();

        return ApiResponse::success(200, __('messages.user_retrieved'), TeamResource::collection($teams));
    }

    /**
     * Store a newly created team.
     */
    public function store(TeamRequest $request): JsonResponse
    {
        $team = $this->teamService->createTeam($request->validated());

        return ApiResponse::success(201, __('messages.register_success'), new TeamResource($team));
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team): JsonResponse
    {
        return ApiResponse::success(200, __('messages.user_retrieved'), new TeamResource($team));
    }

    /**
     * Update the specified team.
     */
    public function update(TeamRequest $request, Team $team): JsonResponse
    {
        $updatedTeam = $this->teamService->updateTeam($team, $request->validated());

        return ApiResponse::success(200, __('messages.profile_updated'), new TeamResource($updatedTeam));
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->teamService->deleteTeam($team);

        return ApiResponse::success(200, __('messages.logout_success'));
    }
}
