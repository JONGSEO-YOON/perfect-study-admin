<?php

namespace App\Exports;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaxSettlementExport implements FromView, WithTitle
{
    protected string $startDate;
    protected string $endDate;
    protected int $totalAmount;
    protected int $supplyAmount;
    protected int $taxAmount;
    protected int $transactionCount;
    protected array $monthlyBreakdown;

    public function __construct(
        string $startDate,
        string $endDate,
        int $totalAmount,
        int $supplyAmount,
        int $taxAmount,
        int $transactionCount,
        array $monthlyBreakdown
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->totalAmount = $totalAmount;
        $this->supplyAmount = $supplyAmount;
        $this->taxAmount = $taxAmount;
        $this->transactionCount = $transactionCount;
        $this->monthlyBreakdown = $monthlyBreakdown;
    }

    public function title(): string
    {
        return '결산 및 부가세';
    }

    public function view(): View
    {
        return view('exports.tax-settlement', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalAmount' => $this->totalAmount,
            'supplyAmount' => $this->supplyAmount,
            'taxAmount' => $this->taxAmount,
            'transactionCount' => $this->transactionCount,
            'monthlyBreakdown' => $this->monthlyBreakdown,
        ]);
    }
}
