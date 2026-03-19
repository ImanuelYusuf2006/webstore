<?php

namespace App\Rules;

use App\Services\PaymentMethodQueryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidPaymentMethodHash implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = app(PaymentMethodQueryService::class);

        $found = $query->getPaymentMethodByHash($value);

        if(! $found){
            $fail("Payment Method Not Valid");
        }
    }
}
