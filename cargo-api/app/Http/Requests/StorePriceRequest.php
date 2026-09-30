<?php

namespace App\Http\Requests;

use App\Enums\Origin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function rules(): array
    {
        // "strict" rejects numbers sent as text, like "1".
        // Company 0 is allowed because the test client sends it.
        return [
            'Company' => ['required', 'integer:strict', 'between:0,999'],
            'Price' => ['required', 'integer:strict', 'between:1,99999'],
            'Origin' => ['required', Rule::enum(Origin::class)],
            'Date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
