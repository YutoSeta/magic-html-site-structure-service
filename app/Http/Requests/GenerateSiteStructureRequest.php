<?php

namespace App\Http\Requests;

final class GenerateSiteStructureRequest extends ContractRequest
{
    /** @return array<string,array<mixed>|string> */
    public function rules(): array
    {
        return [
            'contract_version' => ['required', 'in:1.0'],
            'brief' => ['required', 'array:organization,goals,audience,tone,requirements,materials'],
            'brief.organization' => ['required', 'string', 'min:1', 'max:1000'],
            'brief.goals' => ['required', 'string', 'min:1', 'max:4000'],
            'brief.audience' => ['required', 'string', 'min:1', 'max:4000'],
            'brief.tone' => ['required', 'string', 'min:1', 'max:2000'],
            'brief.requirements' => ['sometimes', 'string', 'max:8000'],
            'brief.materials' => ['sometimes', 'array', 'max:30'],
            'brief.materials.*' => ['string', 'max:8000'],
            'locale' => ['sometimes', 'string', 'min:2', 'max:20'],
            'page_limit' => ['sometimes', 'integer', 'between:1,20'],
            'execution_profile' => ['sometimes', 'string', 'in:fast,balanced,quality'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'locale' => $this->input('locale', 'ja'),
            'page_limit' => $this->input('page_limit', 5),
            'execution_profile' => $this->input('execution_profile', 'fast'),
        ]);
    }
}
