<?php

namespace App\Http\Requests;

final class StoreMediaRequest extends ContractRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/avif,application/pdf,video/mp4,video/webm,audio/mpeg,audio/wav,audio/ogg',
            ],
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
        ];
    }
}
