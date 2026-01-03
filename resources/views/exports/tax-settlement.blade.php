<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td colspan="5" style="text-align:left; height: 50px; vertical-align: middle; padding: 15px; font-weight: bold; font-size: 20px;">
            결산 및 부가세 리포트
        </td>
    </tr>
    <tr>
        <td colspan="5" style="padding: 10px; font-size: 14px;">
            조회 기간: {{ $startDate }} ~ {{ $endDate }}
        </td>
    </tr>
    <tr><td colspan="5"></td></tr>
</table>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align:center; height:30px; background: #4a90d9; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 150px;">
                항목
            </th>
            <th style="text-align:right; height:30px; background: #4a90d9; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 150px;">
                금액 (원)
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                거래 건수
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ number_format($transactionCount) }}건
            </td>
        </tr>
        <tr>
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                총 매출액 (공급대가)
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                {{ number_format($totalAmount) }}
            </td>
        </tr>
        <tr>
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                공급가액 (VAT 제외)
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ number_format($supplyAmount) }}
            </td>
        </tr>
        <tr style="background-color: #fff3cd;">
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                부가세 (VAT 10%)
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #dc3545;">
                {{ number_format($taxAmount) }}
            </td>
        </tr>
    </tbody>
</table>

<table style="width: 100%; border-collapse: collapse;">
    <tr><td colspan="5" style="padding: 20px;"></td></tr>
</table>

@if(count($monthlyBreakdown) > 0)
<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td colspan="5" style="text-align:left; height: 40px; vertical-align: middle; padding: 10px; font-weight: bold; font-size: 16px;">
            월별 상세 내역
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align:center; height:30px; background: #5a6268; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 120px;">
                기간
            </th>
            <th style="text-align:center; height:30px; background: #5a6268; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 100px;">
                거래 건수
            </th>
            <th style="text-align:right; height:30px; background: #5a6268; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 130px;">
                총 매출액
            </th>
            <th style="text-align:right; height:30px; background: #5a6268; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 130px;">
                공급가액
            </th>
            <th style="text-align:right; height:30px; background: #5a6268; color: white; font-weight:bold; vertical-align:center; padding: 12px; border: 1px solid #ddd; width: 130px;">
                부가세
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach($monthlyBreakdown as $month)
        <tr>
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ $month['display'] }}
            </td>
            <td style="text-align:center; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ number_format($month['count']) }}건
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ number_format($month['total']) }}
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd;">
                {{ number_format($month['supply']) }}
            </td>
            <td style="text-align:right; height: 26px; vertical-align:center; padding: 10px; border: 1px solid #ddd; color: #dc3545;">
                {{ number_format($month['tax']) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #e9ecef;">
            <td style="text-align:center; height: 30px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                합계
            </td>
            <td style="text-align:center; height: 30px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                {{ number_format($transactionCount) }}건
            </td>
            <td style="text-align:right; height: 30px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                {{ number_format($totalAmount) }}
            </td>
            <td style="text-align:right; height: 30px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                {{ number_format($supplyAmount) }}
            </td>
            <td style="text-align:right; height: 30px; vertical-align:center; padding: 10px; border: 1px solid #ddd; font-weight: bold; color: #dc3545;">
                {{ number_format($taxAmount) }}
            </td>
        </tr>
    </tfoot>
</table>
@endif
