<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

final class UpsertSiteStructureRequest extends ContractRequest
{
    /** @return array<string,array<mixed>|string> */
    public function rules(): array
    {
        return [
            'contract_version' => ['required', 'in:1.0'],
            'structure' => ['required', 'array'],
        ];
    }

    /** @return array<int,callable(Validator):void> */
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                $document = json_decode($this->getContent());
                if (! is_object($document?->structure ?? null)) {
                    $validator->errors()->add('structure', 'The structure must be a JSON object.');
                }
            },
        ];
    }
}
