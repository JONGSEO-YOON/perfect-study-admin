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

                return "{$areaCode}-{$middle}-{$last}";
            }
            return $state;
        });

        $this->afterStateHydrated(function ($state) {
            if (!$state) {
                $this->state(['010', '', '']);
                return;
            }

            if (is_string($state)) {
                $parts = explode('-', $state);
                $this->state([
                    $parts[0] ?? '010',
                    $parts[1] ?? '',
                    $parts[2] ?? '',
                ]);
            }
        });

        $this->default(['010', '', '']);
    }
}
