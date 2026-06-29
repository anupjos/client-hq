<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'sometimes',
                'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Client->value),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
        ];
    }
}
