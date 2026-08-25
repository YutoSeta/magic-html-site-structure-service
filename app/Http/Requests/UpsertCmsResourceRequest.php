<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

final class UpsertCmsResourceRequest extends ContractRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contract_version' => ['required', 'in:1.0'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'schema' => ['present', 'array'],
            'value' => ['present'],
            'media_refs' => ['sometimes', 'array', 'max:1000'],
            'media_refs.*' => ['required', 'string', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]{0,99}$/', 'distinct:strict'],
        ];
    }

    /** @return array<int,callable(Validator):void> */
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                $document = json_decode($this->getContent());
                if (! is_object($document?->schema ?? null)) {
                    $validator->errors()->add('schema', 'The schema must be a JSON object.');
                }
                $schema = $this->input('schema');
                $value = $this->input('value');
                if (is_array($schema) && strlen((string) json_encode($schema)) > 50000) {
                    $validator->errors()->add('schema', 'The schema may not exceed 50 KB.');
                }
                if (strlen((string) json_encode($value)) > 1000000) {
                    $validator->errors()->add('value', 'The encoded value may not exceed 1 MB.');
                }
            },
        ];
    }
}
