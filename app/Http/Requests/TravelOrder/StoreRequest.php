<?php

namespace App\Http\Requests\TravelOrder;

use App\Rules\TravelOrder\ValidateDepartureDate;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'departure_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:now'],
            'destination' => ['required', 'max:255', 'min:3'],
            'return_date' => ['sometimes', 'date_format:Y-m-d H:i:s', 'after:departure_date'],
        ];
    }

    #[Override]
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        $validated['requester_name'] = auth()->user()->name;

        return data_get($validated, $key, $default);
    }
}
