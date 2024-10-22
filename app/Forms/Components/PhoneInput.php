<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class PhoneInput extends Field
{
    protected string $view = 'forms.components.phone-input';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            if (!$state) return null;

            // Convert array to string with hyphens
            if (is_array($state)) {
                $areaCode = $state[0] ?? '';
                $middle = $state[1] ?? '';
                $last = $state[2] ?? '';

                if ($areaCode && $middle && $last) {
                    return "{$areaCode}-{$middle}-{$last}";
                }
            }
            return $state;
        });

        $this->formatStateUsing(function ($state) {
            if (!$state) return ['010', '', ''];

            // Convert string to array by splitting on hyphens
            $parts = explode('-', $state);
            return [
                $parts[0] ?? '',
                $parts[1] ?? '',
                $parts[2] ?? '',
            ];
        });

        $this->rules([
            'required',
            // 'regex:/^(010|011|016|017|018|019|02|031|032|033|041|042|043|044|051|052|053|054|055|061|062|063|064)-\d{3,4}-\d{4}$/'
        ]);
    }
}
