<?php

namespace App\Http\Requests\TravelOrder;

use App\Enums\TravelOrder\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter.status' => ['sometimes', 'string', Rule::in(Status::values())],
            'filter.destination' => ['sometimes', 'string', 'max:255'],
            'filter.departure_start_date' => ['sometimes', 'date_format:Y-m-d H:i:s'],
            'filter.departure_end_date' => ['sometimes', 'date_format:Y-m-d H:i:s'],
            'filter.return_start_date' => ['sometimes', 'date_format:Y-m-d H:i:s'],
            'filter.end_date' => ['sometimes', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
