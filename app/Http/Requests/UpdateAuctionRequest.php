<?php

namespace App\Http\Requests;

use App\Enums\AuctionState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'state' => ['sometimes', Rule::enum(AuctionState::class)],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The auction title is required.',
            'title.max' => 'The auction title must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 5000 characters.',
            'scheduled_at.after' => 'The scheduled date must be in the future.',
        ];
    }
}
