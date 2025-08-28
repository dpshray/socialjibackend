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
            'requirements' => ['required'],
            'features' => ['required'],
            'tag_id' => ['nullable', 'array'],
            'tag_id.*' => ['sometimes', 'integer', Rule::exists('tags', 'id')],
            'image' => ['nullable', 'image',  'mimes: jpeg,png,jpg', 'max:5125'],
            'status' => ['required', 'boolean'],
            'pricing_tier_id' => ['required', 'array'],
            'pricing_tier_id.*' => ['sometimes', Rule::exists('pricing_tiers', 'id')],
            'price' => ['required', 'array'],
            'price.*' => ['sometimes', 'numeric', 'max:99999999'],
            'delivery_time' => ['required', 'array'],
            'delivery_time.*' => ['required', 'date_format:Y-m-d'] , #2025-08-30
            'tier_description' => ['required', 'array'],
            'tier_description.*' => ['sometimes', 'string', 'max:1000'],
            'currency_id' => ['required', 'array'],
            'currency_id.*' => ['sometimes', 'string', Rule::exists('currencies', 'id')],
            'tier_requirement' => ['nullable', 'array'],
            'tier_requirement.*' => ['sometimes', 'string', 'max:1000'],
            'published_at' => ['sometimes','date']
        ];
    }
}
