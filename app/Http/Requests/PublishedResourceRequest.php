<?php

namespace App\Http\Requests;

final class PublishedResourceRequest extends ContractRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
            'q' => ['sometimes', 'nullable', 'string', 'max:200'],
        ];
    }
}
