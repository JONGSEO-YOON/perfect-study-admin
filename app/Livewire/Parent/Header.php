<?php

namespace App\Livewire\Parent;

use Livewire\Component;
use App\Models\Student;

class Header extends Component
{
    public $students;

    public $studentId;

    public function mount()
    {
        $this->students = Student::whereIn('id', session('students'))
            ->with('user')
            ->get();

        $this->studentId = $this->students->first()->id;
    }

    public function changeStudent()
    {
        $this->dispatch('change-student', id: $this->studentId);
    }

    public function render()
    {
        return view('livewire.parent.header');
    }
}
