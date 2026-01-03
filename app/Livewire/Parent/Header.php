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
        $parent_phone = session('parent_phone');
        $students = Student::where('phone_father', $parent_phone)
            ->orWhere('phone_mother', $parent_phone)
            ->get();
        $this->students = $students;
        $this->studentId = $students->first()->id;
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
