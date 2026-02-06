<?php

namespace App\Http\Controllers\Team;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\MemberRequest;
use App\Http\Resources\UserResource;
use App\Services\Team\MemberService;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Team;

class MemberController extends Controller
{
    protected MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    public function index(): JsonResponse
    {
        $team = current_team();
        $members = $this->memberService->getTeamMembers($team->id);
        return ApiResponse::success(200, __('messages.user_retrieved'), UserResource::collection($members));
    }

    public function store(MemberRequest $request): JsonResponse
    {
        $user = auth()->user();
        $team = current_team();

        if (!$team || $team->owner_id !== $user->id) {
            return ApiResponse::error(403, 'Only team owners can add members.');
        }

        $member = $this->memberService->addMember($team, $request->validated());
        return ApiResponse::success(201, 'Member added successfully', new UserResource($member));
    }

    public function destroy(User $member): JsonResponse
    {
        $authUser = auth()->user();
        $team = current_team();

        if (!$team || $team->owner_id !== $authUser->id) {
            return ApiResponse::error(403, 'Only team owners can remove members.');
        }

        try {
            $this->memberService->removeMember($team, $member);
            return ApiResponse::success(200, 'Member removed from team successfully');
        } catch (\Exception $e) {
            return ApiResponse::error(403, $e->getMessage());
        }
    }
}
