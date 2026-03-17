<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class Settlement extends Page
{
    protected static ?string $navigationIcon = null;

    protected static ?string $navigationGroup = '결제';

    protected static ?string $navigationLabel = '결산 및 부가세';

    protected static ?string $title = '결산 및 부가세';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.settlement';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public array $transactions = [];
    public array $summary = [
        'total_amount' => 0,
        'total_count' => 0,
        'card_amount' => 0,
        'card_count' => 0,
        'transfer_amount' => 0,
        'transfer_count' => 0,
        'cancel_amount' => 0,
        'cancel_count' => 0,
        'vat' => 0,
        'supply_amount' => 0,
    ];
    public bool $isLoading = false;
    public ?string $errorMessage = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['root_admin', 'admin']);
    }

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->fetchTransactions();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('시작일')
                            ->required()
                            ->reactive(),
                        DatePicker::make('endDate')
                            ->label('종료일')
                            ->required()
                            ->reactive(),
                    ]),
            ]);
    }

    public function search()
    {
        $this->fetchTransactions();
    }

    protected function fetchTransactions()
    {
        $this->isLoading = true;
        $this->errorMessage = null;
        $this->transactions = [];
        $this->summary = [
            'total_amount' => 0,
            'total_count' => 0,
            'card_amount' => 0,
            'card_count' => 0,
            'transfer_amount' => 0,
            'transfer_count' => 0,
            'cancel_amount' => 0,
            'cancel_count' => 0,
            'vat' => 0,
            'supply_amount' => 0,
        ];

        try {
            $secretKey = config('services.toss.secret_key');
            $startDateTime = $this->startDate . 'T00:00:00';
            $endDateTime = $this->endDate . 'T23:59:59';

            $allTransactions = [];
            $lastCursor = null;
            $hasMore = true;

            // 페이지네이션으로 전체 거래 내역 조회
            while ($hasMore) {
                $queryParams = [
                    'startDate' => $startDateTime,
                    'endDate' => $endDateTime,
                    'limit' => 100,
                ];

                if ($lastCursor) {
                    $queryParams['startingAfter'] = $lastCursor;
                }

                $response = Http::withBasicAuth($secretKey, '')
                    ->get('https://api.tosspayments.com/v1/transactions', $queryParams);

                if ($response->failed()) {
                    $error = $response->json();
                    $this->errorMessage = $error['message'] ?? '토스 API 조회에 실패했습니다.';
                    $this->isLoading = false;
                    return;
                }

                $data = $response->json();

                if (empty($data) || !is_array($data)) {
                    $hasMore = false;
                } else {
                    $allTransactions = array_merge($allTransactions, $data);
                    if (count($data) < 100) {
                        $hasMore = false;
                    } else {
                        $lastCursor = end($data)['transactionKey'] ?? null;
                        if (!$lastCursor) $hasMore = false;
                    }
                }
            }

            // orderId로 DB Payment 매칭하여 학생 정보 추가
            $orderIds = array_filter(array_column($allTransactions, 'orderId'));
            $payments = Payment::with('student.user')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->keyBy('order_id');

            foreach ($allTransactions as &$tx) {
                $orderId = $tx['orderId'] ?? null;
                $payment = $orderId ? ($payments[$orderId] ?? null) : null;
                $tx['_student_name'] = $payment?->student?->user?->name ?? '-';
                $tx['_billing_name'] = $payment?->billing_name ?? '-';
                $tx['_billing_memo'] = $payment?->billing_memo ?? '';
                $tx['_created_at'] = $payment?->created_at?->format('Y-m-d H:i') ?? '';
            }
            unset($tx);

            $this->transactions = $allTransactions;
            $this->calculateSummary();
        } catch (\Exception $e) {
            $this->errorMessage = '조회 중 오류가 발생했습니다: ' . $e->getMessage();
        }

        $this->isLoading = false;
    }

    protected function calculateSummary()
    {
        foreach ($this->transactions as $tx) {
            $amount = $tx['amount'] ?? 0;
            $method = $tx['method'] ?? '';
            $status = $tx['status'] ?? '';

            if ($status === 'CANCELED' || $status === 'PARTIAL_CANCELED') {
                $this->summary['cancel_amount'] += abs($amount);
                $this->summary['cancel_count']++;
            } else if ($status === 'DONE') {
                $this->summary['total_amount'] += $amount;
                $this->summary['total_count']++;

                if (str_contains($method, '카드')) {
                    $this->summary['card_amount'] += $amount;
                    $this->summary['card_count']++;
                } else {
                    $this->summary['transfer_amount'] += $amount;
                    $this->summary['transfer_count']++;
                }
            }
        }

        // 부가세 계산 (총 매출의 10%)
        $netAmount = $this->summary['total_amount'] - $this->summary['cancel_amount'];
        $this->summary['supply_amount'] = (int) round($netAmount / 1.1);
        $this->summary['vat'] = $netAmount - $this->summary['supply_amount'];
    }
}
