<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role') ? $this->route('role')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId)->where(function ($query) {
                    $guardName = $this->team_id ? 'team' : 'super_admin';
                    return $query->where('guard_name', $guardName)
                        ->where('team_id', $this->team_id);
                }),
            ],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
