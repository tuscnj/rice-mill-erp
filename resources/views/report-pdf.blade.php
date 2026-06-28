<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ str_replace('_', ' ', $reportType) }} Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { width: 100%; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { max-height: 50px; margin-right: 15px; }
        .company-name { font-size: 20px; font-weight: bold; color: #111; text-transform: uppercase; margin: 0; }
        .report-title { text-align: right; }
        .report-title h2 { margin: 0; font-size: 18px; color: #2563eb; text-transform: uppercase; }
        .report-title p { margin: 2px 0 0 0; font-size: 10px; color: #555; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; }
        .t-account { border: 1px solid #222; margin-top: 10px; }
        .t-account th { background-color: #f1f5f9; border-bottom: 1px solid #222; font-size: 10px; text-transform: uppercase; }
        .t-account .border-right { border-right: 1px solid #222; }
        .inner-table td { padding: 4px 6px; border: none; font-size: 10px; }
        .inner-table .bold { font-weight: bold; }
        .inner-table .total-row { border-top: 2px solid #222; font-weight: bold; font-size: 12px; background: #f8fafc; }
        
        .list-table { margin-top: 20px; }
        .list-table th { background: #1e293b; color: white; border: 1px solid #1e293b; }
        .list-table td { border-bottom: 1px solid #e2e8f0; }
        .list-table .grand-total { background: #f1f5f9; font-weight: bold; font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    @php
        $logoData = '';
        if($setting->logo_path && file_exists(public_path($setting->logo_path))) {
            $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
            $data = file_get_contents(public_path($setting->logo_path));
            $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp

    <table class="header">
        <tr>
            <td width="60%" valign="top">
                <table>
                    <tr>
                        @if($logoData)
                            <td width="1%"><img src="{{ $logoData }}" class="logo"></td>
                        @endif
                        <td>
                            <p class="company-name">{{ $setting->company_name }}</p>
                            <p style="margin:2px 0;font-size:10px;">{{ $setting->address }}</p>
                            <p style="margin:2px 0;font-size:10px;">{{ $setting->phone }} | {{ $setting->email }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="40%" class="report-title" valign="top">
                <h2>{{ str_replace('_', ' ', $reportType) }} Report</h2>
                <p>{{ $displayStartDate }} to {{ $displayEndDate }}</p>
            </td>
        </tr>
    </table>

    @if($reportType === 'Profit_Loss')
        {{-- T-ACCOUNT LAYOUT FOR P&L --}}
        <table class="t-account">
            <tr>
                <th width="50%" class="border-right">Dr. (Trading)</th>
                <th width="50%">Cr. (Trading)</th>
            </tr>
            <tr>
                <td valign="top" class="border-right" style="padding:0;">
                    <table class="inner-table">
                        <tr><td class="bold">Opening Stock</td><td class="text-right bold">{{ number_format($totalOpeningStock, 2) }}</td></tr>
                        <tr><td class="bold">Direct Expenses</td><td class="text-right bold">{{ number_format($totalPurchases, 2) }}</td></tr>
                        @foreach($purchases as $p)
                            @if($p->total > 0)
                            <tr><td style="padding-left:15px;color:#555;">{{ $p->name }}</td><td class="text-right text-muted">{{ number_format($p->total, 2) }}</td></tr>
                            @endif
                        @endforeach
                        @if($grossProfit > 0)
                            <tr><td class="bold" style="color:blue;padding-top:15px;">Gross Profit c/o</td><td class="text-right bold" style="color:blue;padding-top:15px;">{{ number_format($grossProfit, 2) }}</td></tr>
                        @endif
                    </table>
                </td>
                <td valign="top" style="padding:0;">
                    <table class="inner-table">
                        <tr><td class="bold">Direct Incomes</td><td class="text-right bold">{{ number_format($totalSales, 2) }}</td></tr>
                        @foreach($sales as $s)
                            @if($s->total > 0)
                            <tr><td style="padding-left:15px;color:#555;">{{ $s->name }}</td><td class="text-right text-muted">{{ number_format($s->total, 2) }}</td></tr>
                            @endif
                        @endforeach
                        <tr><td class="bold" style="padding-top:15px;">Closing Stock</td><td class="text-right bold" style="padding-top:15px;">{{ number_format($totalClosingStock, 2) }}</td></tr>
                        @if($grossLoss > 0)
                            <tr><td class="bold" style="color:red;padding-top:15px;">Gross Loss c/o</td><td class="text-right bold" style="color:red;padding-top:15px;">{{ number_format($grossLoss, 2) }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:0;" class="border-right"><table class="inner-table"><tr><td class="total-row">Total</td><td class="text-right total-row">{{ number_format($tradingTotal, 2) }}</td></tr></table></td>
                <td style="padding:0;"><table class="inner-table"><tr><td class="total-row">Total</td><td class="text-right total-row">{{ number_format($tradingTotal, 2) }}</td></tr></table></td>
            </tr>
        </table>

        <table class="t-account">
            <tr>
                <th width="50%" class="border-right">Dr. (P&L)</th>
                <th width="50%">Cr. (P&L)</th>
            </tr>
            <tr>
                <td valign="top" class="border-right" style="padding:0;">
                    <table class="inner-table">
                        @if($grossLoss > 0)
                            <tr><td class="bold" style="color:red;padding-bottom:10px;">Gross Loss b/f</td><td class="text-right bold" style="color:red;padding-bottom:10px;">{{ number_format($grossLoss, 2) }}</td></tr>
                        @endif
                        <tr><td class="bold">Indirect Expenses</td><td class="text-right bold">{{ number_format($totalIndirectExpenses, 2) }}</td></tr>
                        @foreach($indirectExpenses as $exp)
                            <tr><td style="padding-left:15px;color:#555;">{{ $exp->name }}</td><td class="text-right text-muted">{{ number_format($exp->total, 2) }}</td></tr>
                        @endforeach
                        @if($netProfit > 0)
                            <tr><td class="bold" style="color:green;padding-top:20px;font-size:14px;">Nett Profit</td><td class="text-right bold" style="color:green;padding-top:20px;font-size:14px;">{{ number_format($netProfit, 2) }}</td></tr>
                        @endif
                    </table>
                </td>
                <td valign="top" style="padding:0;">
                    <table class="inner-table">
                        @if($grossProfit > 0)
                            <tr><td class="bold" style="color:blue;padding-bottom:10px;">Gross Profit b/f</td><td class="text-right bold" style="color:blue;padding-bottom:10px;">{{ number_format($grossProfit, 2) }}</td></tr>
                        @endif
                        @if($totalIndirectIncomes > 0)
                            <tr><td class="bold">Indirect Incomes</td><td class="text-right bold">{{ number_format($totalIndirectIncomes, 2) }}</td></tr>
                            @foreach($indirectIncomes as $inc)
                                <tr><td style="padding-left:15px;color:#555;">{{ $inc->name }}</td><td class="text-right text-muted">{{ number_format($inc->total, 2) }}</td></tr>
                            @endforeach
                        @endif
                        @if($netLoss > 0)
                            <tr><td class="bold" style="color:red;padding-top:20px;font-size:14px;">Nett Loss</td><td class="text-right bold" style="color:red;padding-top:20px;font-size:14px;">{{ number_format($netLoss, 2) }}</td></tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:0;" class="border-right"><table class="inner-table"><tr><td class="total-row">Total</td><td class="text-right total-row">{{ number_format($plTotal, 2) }}</td></tr></table></td>
                <td style="padding:0;"><table class="inner-table"><tr><td class="total-row">Total</td><td class="text-right total-row">{{ number_format($plTotal, 2) }}</td></tr></table></td>
            </tr>
        </table>

    @elseif($reportType === 'Production')
        <table class="list-table">
            <thead>
                <tr>
                    <th class="text-center" width="25%">Paddy Crushed</th>
                    <th class="text-center" width="25%">Rice Produced</th>
                    <th class="text-center" width="25%">Byproducts</th>
                    <th class="text-center" width="25%" style="background:#16a34a;">Yield Ratio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center bold">{{ number_format($productionStats['raw'], 2) }} KG</td>
                    <td class="text-center bold">{{ number_format($productionStats['rice'], 2) }} KG</td>
                    <td class="text-center bold">{{ number_format($productionStats['byproduct'], 2) }} KG</td>
                    <td class="text-center bold" style="font-size:16px;">{{ number_format($productionStats['yield'], 2) }}%</td>
                </tr>
            </tbody>
        </table>
        
        <h3 style="margin-top:30px; border-bottom:1px solid #ccc; padding-bottom:5px;">Production Log</h3>
        <table class="list-table" style="margin-top:10px;">
            <thead>
                <tr>
                    <th>Date / Ref</th>
                    <th>Raw Material Used</th>
                    <th>Finished Goods Gained</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vouchers as $v)
                <tr>
                    <td>
                        <strong>{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</strong><br>
                        <span style="color:#555;font-size:9px;">{{ $v->reference_number ?: '#VCH-'.$v->id }}</span>
                    </td>
                    <td>
                        @foreach($v->inventoryMovements as $m)
                            @if($m->movement_type == 'Out' && $m->item && $m->item->category == 'Raw Material')
                                <div>- {{ number_format($m->quantity, 2) }} KG ({{ $m->item->name }})</div>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        @foreach($v->inventoryMovements as $m)
                            @if($m->movement_type == 'In' && $m->item && $m->item->category == 'Finished Goods')
                                <div>+ {{ number_format($m->quantity, 2) }} KG ({{ $m->item->name }})</div>
                            @endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <table class="list-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Voucher / Ref</th>
                    <th width="40%">Notes / Details</th>
                    <th class="text-right">Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vouchers as $v)
                <tr>
                    <td><strong>{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</strong></td>
                    <td style="color:#555;font-size:10px;">#VCH-{{ $v->id }}<br>{{ $v->reference_number }}</td>
                    <td>{{ $v->notes ?: '-' }}</td>
                    <td class="text-right"><strong>{{ number_format($v->display_amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="3" class="text-right">GRAND TOTAL</td>
                    <td class="text-right" style="color:blue;">৳{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

</body>
</html>