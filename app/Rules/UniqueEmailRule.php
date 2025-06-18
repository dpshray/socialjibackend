<?php

namespace App\Rules;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueEmailRule implements ValidationRule
{
    use ResponseTrait;
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $row = DB::table('users')->where('email', $value)->first();
        if ($row) {
            if ($row->deleted_at) {
                $fail('your account has been deactivated. please login to activate your account.');
            }
            $fail('email has already been taken.');
        }
    }
}
