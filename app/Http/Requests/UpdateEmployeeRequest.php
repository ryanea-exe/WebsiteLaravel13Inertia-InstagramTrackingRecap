<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('employees')->ignore($this->route('employee'))],
            'name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'instagram_user_id' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('employees')->ignore($this->route('employee'))],
            'instagram_username' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
