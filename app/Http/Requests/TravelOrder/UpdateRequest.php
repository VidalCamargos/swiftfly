<?php

namespace App\Http\Requests\TravelOrder;

use App\Enums\TravelOrder\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(Status::possibleUpdateStatus()),
                Rule::prohibitedIf(fn () => $this->route('travelOrder')->status->isApproved()),
            ],
        ];
    }
}
