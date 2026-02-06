<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We will use controller authorization
    }

    public function rules(): array
    {
        $roleId = $this->route('role') ? $this->route('role')->id : null;
        $teamId = auth()->user()->team_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId)->where(function ($query) use ($teamId) {
                    return $query->where('guard_name', 'team')
                        ->where('team_id', $teamId);
                }),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
