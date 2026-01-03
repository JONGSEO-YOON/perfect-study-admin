<?php

namespace App\Livewire;

use App\Models\Payment as PaymentModel;
use Livewire\Component;
use Illuminate\Support\Str;

class Payment extends Component
{
    public $paymentId;
    public $payment;
    public $orderId;
    public $amount;
    public $billing_name;
    public $TOSS_CLIENT_KEY;
    public $TOSS_CUSTOMER_KEY;
    public $TOSS_SECRET_KEY;

    public function mount($paymentId = null)
    {
        $this->paymentId = $paymentId;


        if ($paymentId) {
            $this->payment = PaymentModel::with(['user', 'student'])->find($paymentId);
            $this->amount = $this->payment->amount;
            $this->billing_name =  $this->payment->billing_name;
            $this->orderId =  $this->payment->order_id;
            $this->TOSS_CLIENT_KEY = config('services.toss.client_key');
            $this->TOSS_CUSTOMER_KEY = config('services.toss.customer_key');
        }
    }

    public function render()
    {
        return view('livewire.payment');
    }
}
