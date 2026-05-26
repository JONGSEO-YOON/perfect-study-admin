<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Set;

class AddressInput extends Field
{
    protected string $view = 'forms.components.address-input';

    public function getInputId(): string
    {
        return $this->getId() . '-input';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        $this->formatStateUsing(function ($state, $record) {
            return [
                'address' => $record?->address ?? '',
                'postal_code' => $record?->postal_code ?? '',
            ];
        });

        $this->afterStateUpdated(function ($state, $livewire, $set) {
            if (!is_array($state)) {
                return;
            }
            $set('address', $state['address'] ?? null);
            $set('postal_code', $state['postal_code'] ?? null);
        });
    }
}
