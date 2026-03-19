<?php

namespace App\Rules;

use App\Services\ShippingMethodService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidShippingHash implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $found = app(ShippingMethodService::class)->getShippingMethod($value);

        if(! $found){
            $fail('Shippinh Method Not Valid');
        }
    }
}
