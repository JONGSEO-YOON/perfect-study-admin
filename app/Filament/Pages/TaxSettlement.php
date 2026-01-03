<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class TaxSettlement extends Page
{
    protected static ?string $navigationIcon = null;

    protected static ?string $navigationGroup = '결제';

    protected static ?string $navigationLabel = '결산 및 부가세';

    protected static ?string $title = '결산 및 부가세';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.tax-settlement';

    public string $periodType = 'month';
    public int $selectedYear;
    public int $selectedMonth;
    public int $selectedQuarter;
    public ?string $startDate;
    public ?string $endDate;

    public int $totalAmount = 0;
    public int $supplyAmount = 0;
    public int $taxAmount = 0;
    public int $transactionCount = 0;
    public array $monthlyBreakdown = [];

    private const VAT_RATE = 1.1;

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'root_admin';
    }

    public function mount(): void
    {
        $this->selectedYear = now()->year;
        $this->selectedMonth = now()->month;
        $this->selectedQuarter = (int) ceil(now()->month / 3);
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');

        $this->calculateSettlement();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('periodType')
                    ->label('기간 유형')
                    ->options([
                        'month' => '월별',
                        'quarter' => '분기별',
                        'custom' => '기간 직접 선택',
                    ])
                    ->default('month')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->periodType = $state;
                        $this->updateDateRange();
                        $this->calculateSettlement();
                    }),
                Select::make('selectedYear')
                    ->label('연도')
                    ->options(function () {
                        $years = [];
                        for ($year = now()->year; $year >= 2020; $year--) {
                            $years[$year] = $year . '년';
                        }
                        return $years;
                    })
                    ->default(now()->year)
                    ->reactive()
                    ->visible(fn($get) => in_array($get('periodType'), ['month', 'quarter']))
                    ->afterStateUpdated(function ($state) {
                        $this->selectedYear = (int) $state;
                        $this->updateDateRange();
                        $this->calculateSettlement();
                    }),
                Select::make('selectedMonth')
                    ->label('월')
                    ->options([
                        1 => '1월', 2 => '2월', 3 => '3월',
                        4 => '4월', 5 => '5월', 6 => '6월',
                        7 => '7월', 8 => '8월', 9 => '9월',
                        10 => '10월', 11 => '11월', 12 => '12월',
                    ])
                    ->default(now()->month)
                    ->reactive()
                    ->visible(fn($get) => $get('periodType') === 'month')
                    ->afterStateUpdated(function ($state) {
                        $this->selectedMonth = (int) $state;
                        $this->updateDateRange();
                        $this->calculateSettlement();
                    }),
                Select::make('selectedQuarter')
                    ->label('분기')
                    ->options([
                        1 => '1분기 (1~3월)',
                        2 => '2분기 (4~6월)',
                        3 => '3분기 (7~9월)',
                        4 => '4분기 (10~12월)',
                    ])
                    ->default(ceil(now()->month / 3))
                    ->reactive()
                    ->visible(fn($get) => $get('periodType') === 'quarter')
                    ->afterStateUpdated(function ($state) {
                        $this->selectedQuarter = (int) $state;
                        $this->updateDateRange();
                        $this->calculateSettlement();
                    }),
                DatePicker::make('startDate')
                    ->label('시작일')
                    ->default(now()->startOfMonth())
                    ->reactive()
                    ->visible(fn($get) => $get('periodType') === 'custom')
                    ->afterStateUpdated(function ($state) {
                        $this->startDate = $state;
                        $this->calculateSettlement();
                    }),
                DatePicker::make('endDate')
                    ->label('종료일')
                    ->default(now()->endOfMonth())
                    ->reactive()
                    ->visible(fn($get) => $get('periodType') === 'custom')
                    ->afterStateUpdated(function ($state) {
                        $this->endDate = $state;
                        $this->calculateSettlement();
                    }),
            ])
            ->columns(4);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel 내보내기')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return $this->exportToExcel();
                }),
            Action::make('refresh')
                ->label('새로고침')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->calculateSettlement();
                    Notification::make()
                        ->title('데이터가 새로고침되었습니다.')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function updateDateRange(): void
    {
        switch ($this->periodType) {
            case 'month':
                $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
                $this->startDate = $date->startOfMonth()->format('Y-m-d');
                $this->endDate = $date->copy()->endOfMonth()->format('Y-m-d');
                break;

            case 'quarter':
                $startMonth = ($this->selectedQuarter - 1) * 3 + 1;
                $startDate = Carbon::create($this->selectedYear, $startMonth, 1);
                $this->startDate = $startDate->format('Y-m-d');
                $this->endDate = $startDate->copy()->addMonths(2)->endOfMonth()->format('Y-m-d');
                break;

            case 'custom':
                break;
        }
    }

    public function calculateSettlement(): void
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $paidPayments = Payment::where('payment_status', 'paid')
            ->whereBetween('approved_at', [$start, $end])
            ->get();

        $this->transactionCount = $paidPayments->count();
        $this->totalAmount = (int) $paidPayments->sum('amount');
        $this->supplyAmount = (int) floor($this->totalAmount / self::VAT_RATE);
        $this->taxAmount = $this->totalAmount - $this->supplyAmount;

        $this->monthlyBreakdown = $paidPayments->groupBy(function ($payment) {
            return $payment->approved_at->format('Y-m');
        })->map(function ($monthPayments, $yearMonth) {
            $monthTotal = (int) $monthPayments->sum('amount');
            $monthSupply = (int) floor($monthTotal / self::VAT_RATE);
            $monthTax = $monthTotal - $monthSupply;

            return [
                'year_month' => $yearMonth,
                'display' => Carbon::parse($yearMonth . '-01')->format('Y년 m월'),
                'count' => $monthPayments->count(),
                'total' => $monthTotal,
                'supply' => $monthSupply,
                'tax' => $monthTax,
            ];
        })->sortKeys()->values()->toArray();
    }

    public function exportToExcel()
    {
        $filename = '결산_' . $this->startDate . '_' . $this->endDate . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TaxSettlementExport(
                $this->startDate,
                $this->endDate,
                $this->totalAmount,
                $this->supplyAmount,
                $this->taxAmount,
                $this->transactionCount,
                $this->monthlyBreakdown
            ),
            $filename
        );
    }

    public function getViewData(): array
    {
        return [
            'periodType' => $this->periodType,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalAmount' => $this->totalAmount,
            'supplyAmount' => $this->supplyAmount,
            'taxAmount' => $this->taxAmount,
            'transactionCount' => $this->transactionCount,
            'monthlyBreakdown' => $this->monthlyBreakdown,
        ];
    }
}
