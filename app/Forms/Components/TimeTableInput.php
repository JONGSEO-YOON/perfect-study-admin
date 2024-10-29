<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class TimeTableInput extends Field
{
  protected string $view = 'forms.components.time-table-input';

  protected function setUp(): void
  {
    parent::setUp();

    $this->dehydrateStateUsing(function ($state) {
      if (!$state) return null;

      // Convert the array state to a JSON-friendly format
      $schedule = [];
      foreach ($state as $day => $times) {
        if ($times['enabled'] ?? false) {
          $schedule[$day] = [
            'start' => $times['start'] ?? null,
            'end' => $times['end'] ?? null,
          ];
        }
      }
      return $schedule;
    });

    $this->afterStateHydrated(function ($state) {
      $defaultState = [
        'mon' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'tue' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'wed' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'thu' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'fri' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'sat' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
        'sun' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      ];

      if (is_array($state)) {
        foreach ($state as $day => $times) {
          $defaultState[$day] = [
            'enabled' => true,
            'start' => $times['start'] ?? '09:00',
            'end' => $times['end'] ?? '18:00',
          ];
        }
      }

      $this->state($defaultState);
    });

    $this->default([
      'mon' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'tue' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'wed' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'thu' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'fri' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'sat' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
      'sun' => ['enabled' => false, 'start' => '09:00', 'end' => '18:00'],
    ]);
  }
}
