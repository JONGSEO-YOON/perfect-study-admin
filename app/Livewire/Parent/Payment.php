<?php

namespace App\Livewire\Parent;

use App\Models\Payment as PaymentModel;
use App\Models\Student;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Payment extends Component
{
    public $payments = [];
    public $startDate;
    public $endDate;

    public function mount()
    {
        // 기본 날짜 설정: 오늘로부터 한 달 이전까지
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subMonth()->format('Y-m-d');

        $this->loadPayments();
    }

    public function updatedStartDate()
    {
        $this->loadPayments();
    }

    public function updatedEndDate()
    {
        $this->loadPayments();
    }

    public function loadPayments()
    {
        $phone = session('parent_phone');

        if (!$phone || !$this->startDate || !$this->endDate) {
            $this->payments = [];
            return;
        }

        $academyId = session('parent_academy_id');
        $studentsQuery = Student::where(function ($q) use ($phone) {
            $q->where('phone_father', $phone)
              ->orWhere('phone_mother', $phone);
        });
        if ($academyId) {
            $studentsQuery->where('academy_id', $academyId);
        }
        $students = $studentsQuery->get();

        $studentIds = $students->pluck('id');

        $this->payments = PaymentModel::whereIn('student_id', $studentIds)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->with(['student'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Layout('layouts.parent')]
    public function render()
    {
        return view('livewire.parent.payment');
    }
}
