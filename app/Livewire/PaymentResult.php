<?php

namespace App\Livewire;

use App\Models\Payment;
use Livewire\Component;

class PaymentResult extends Component
{
    public $paymentId;
    public $payment;
    public $failed_message;

    public function mount($paymentId)
    {
        $this->paymentId = $paymentId;
        $this->payment = Payment::find($paymentId);

        if (!$this->payment) {
            abort(404, '결제 정보를 찾을 수 없습니다.');
        }

        $this->failed_message = request('failed_message');
    }

    public function render()
    {
        return view('livewire.payment-result');
    }
}
