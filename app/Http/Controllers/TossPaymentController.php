<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\MonthlyStatement;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TossPaymentController extends Controller
{
  /**
   * 토스페이먼츠 API 엔드포인트
   */
  protected $tossApiUrl = 'https://api.tosspayments.com/v1';

  /**
   * 토스페이먼츠 시크릿 키
   */
  protected $secretKey;

  /**
   * 생성자
   */
  public function __construct()
  {
    $this->secretKey = config('services.toss.secret_key');
  }

  /**
   * 결제 성공 처리
   */
  public function handleSuccess(Request $request)
  {
    $validated = $request->validate([
      'orderId' => 'required|string|exists:payments,order_id',
      'paymentKey' => 'required|string',
      'amount' => 'required|numeric',
    ]);

    $payment = Payment::where([['order_id', $validated['orderId']], ['payment_status', '!=', 'paid']])->first();

    if (!$payment) {
      return redirect('/payment/' . $payment->id . '/result?failed_message=결제 정보를 찾을 수 없습니다.');
    }

    if ($payment->amount != $validated['amount']) {
      return redirect('/payment/' . $payment->id . '/result?failed_message=결제 금액이 일치하지 않습니다.');
    }

    try {
      // 토스 API를 통해 결제 승인 요청
      $tossResponse = $this->confirmPaymentWithToss(
        $validated['paymentKey'],
        $validated['orderId'],
        $validated['amount']
      );

      // 결제 승인 성공시 처리
      $payment->markAsSuccess(
        $validated['paymentKey'],
        json_encode(['request' => $request->all(), 'response' => $tossResponse]),
        $tossResponse['method']
      );

      return redirect('/payment/' . $payment->id . '/result');
    } catch (\Exception $e) {
      dd($e);
      // 결제 승인 실패시 처리
      $payment->markAsFailed(json_encode([
        'error' => $e->getMessage(),
        'request' => $request->all()
      ]));

      Log::error('Payment confirmation failed', [
        'order_id' => $validated['orderId'],
        'error' => $e->getMessage()
      ]);
      return redirect('/payment/' . $payment->id . '/result?failed_message=' . urlencode($e->getMessage()));
    }
  }

  /**
   * 결제 실패 처리
   */
  public function handleFailure(Request $request)
  {
    $validated = $request->validate([
      'orderId' => 'required|string|exists:toss_payments,order_id',
      'message' => 'required|string',
      'code' => 'required|string',
    ]);

    $payment = Payment::where('order_id', $validated['orderId'])->first();
    $payment->markAsFailed(json_encode($validated));

    return redirect('/payment/' . $payment->id . '/result');
  }

  /**
   * 토스페이먼츠 결제 승인 API 호출
   *
   * @param string $paymentKey
   * @param string $orderId
   * @param float $amount
   * @return array
   * @throws \Exception
   */
  protected function confirmPaymentWithToss(string $paymentKey, string $orderId, float $amount): array
  {
    try {
      // 시크릿 키 인코딩
      $encodedKey = base64_encode($this->secretKey . ':');

      // API 호출
      $response = Http::withHeaders([
        'Authorization' => 'Basic ' . $encodedKey,
        'Content-Type' => 'application/json'
      ])->post($this->tossApiUrl . '/payments/confirm', [
        'paymentKey' => $paymentKey,
        'orderId' => $orderId,
        'amount' => $amount
      ]);
      // dd($response->json());

      // 응답 확인
      if ($response->successful()) {
        return $response->json();
      }

      // 에러 응답 처리
      $errorResponse = $response->json();
      Log::error('Toss payment confirmation failed', $errorResponse);
      throw new \Exception($errorResponse['message'] ?? '결제 승인 실패');
    } catch (\Exception $e) {
      Log::error('Toss API error', ['error' => $e->getMessage()]);
      throw new \Exception('결제 승인 중 오류가 발생했습니다: ' . $e->getMessage());
    }
  }

  /**
   * 결제 취소
   *
   * @param string $paymentKey
   * @param string $cancelReason
   * @return array
   * @throws \Exception
   */
  public function cancelPayment(string $paymentKey, string $cancelReason = '고객 요청'): array
  {
    try {
      // 시크릿 키 인코딩
      $encodedKey = base64_encode($this->secretKey . ':');

      // API 호출
      $response = Http::withHeaders([
        'Authorization' => 'Basic ' . $encodedKey,
        'Content-Type' => 'application/json'
      ])->post($this->tossApiUrl . '/payments/' . $paymentKey . '/cancel', [
        'cancelReason' => $cancelReason
      ]);

      // 응답 확인
      if ($response->successful()) {
        return $response->json();
      }

      // 에러 응답 처리
      $errorResponse = $response->json();
      Log::error('Toss payment cancellation failed', $errorResponse);
      throw new \Exception($errorResponse['message'] ?? '결제 취소 실패');
    } catch (\Exception $e) {
      Log::error('Toss cancel API error', ['error' => $e->getMessage()]);
      throw new \Exception('결제 취소 중 오류가 발생했습니다: ' . $e->getMessage());
    }
  }
}
