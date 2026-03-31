<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class ExamGridSelect extends Field
{
    protected string $view = 'filament.components.forms.exam-grid-select';

    protected array $items = [];
    protected bool $multiple = false;
    protected int $cols = 2;
    protected int $maxHeight = 0;

    public function items(array $items): static
    {
        $this->items = $items;
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function cols(int $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function getCols(): int
    {
        return $this->cols;
    }

    public function maxHeight(int $height): static
    {
        $this->maxHeight = $height;
        return $this;
    }

    public function getMaxHeight(): int
    {
        return $this->maxHeight;
    }
}
