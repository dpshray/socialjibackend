<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CampaignStoreRequest extends FormRequest
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
            "title" => "required|max:255",
            "description" => "required",
            "categories" => "required",
            "eligibility" => "required",
            "requirement" => "required",
            "price" => "required|integer",
            "tag_id" => "nullable|array",
            "tag_id.*" => "nullable|exists:tags,id",
            "image" => "nullable|image"
        ];
    }
}
