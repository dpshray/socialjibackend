<?php

namespace App\Http\Requests\Influencer\Gig;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGigRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'requirements' => ['required', 'array'],
            'requirements.*' => ['required', 'string', 'max:1000'],
            'features' => ['required', 'array'],
            'features.*' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image',  'mimes: jpeg,png,jpg', 'max:2048'],
            'status' => ['required', 'boolean'],
            'published' => ['required', 'boolean'],
            'pricing_tier' => ['nullable', 'array'],
            'pricing_tier.*' => ['sometimes', Rule::exists('pricing_tiers', 'id')],
            'price' => ['nullable', 'array'],
            'price.*' => ['sometimes', 'numeric', 'max:99999999'],
            'delivery_time' => ['nullable', 'array'],
            'delivery_time.*' => ['sometimes', 'date'],
            'tier_description' => ['nullable', 'array'],
            'tier_description.*' => ['sometimes', 'string', 'max:1000'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['sometimes', 'integer', Rule::exists('tags', 'id')],
            'tier_requirement' => ['nullable', 'array'],
            'tier_requirement.*' => ['sometimes', 'string', 'max:1000'],
        ];
    }
}
