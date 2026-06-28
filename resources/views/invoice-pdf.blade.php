<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #VCH-{{ $voucher->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .header { width: 100%; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 20px; }
        .header td { vertical-align: top; }
        .logo { max-height: 60px; max-width: 150px; margin-right: 15px; }
        .company-name { font-size: 24px; font-weight: bold; color: #111; text-transform: uppercase; margin: 0; }
        .company-info { font-size: 11px; color: #555; margin: 3px 0 0 0; }
        .invoice-title { font-size: 22px; font-weight: bold; text-transform: uppercase; margin: 0; text-align: right; color: #2563eb; }
        .invoice-title.return { color: #ea580c; }
        .invoice-meta { text-align: right; font-size: 12px; margin-top: 5px; color: #555;}
        .bill-to { margin-bottom: 20px; }
        .bill-to h4 { margin: 0 0 5px 0; font-size: 11px; color: #777; text-transform: uppercase; }
        .bill-to h2 { margin: 0; font-size: 16px; color: #222; }
        .bill-to p { margin: 2px 0 0 0; font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f1f5f9; border-top: 2px solid #222; border-bottom: 2px solid #222; padding: 10px 8px; text-align: left; font-size: 11px; text-transform: uppercase; color: #333;}
        .text-right { text-align: right; }
        td { border-bottom: 1px solid #e2e8f0; padding: 10px 8px; vertical-align: top; }
        .totals-container { width: 100%; display: table; margin-top: 10px;}
        .notes { display: table-cell; width: 55%; vertical-align: top; padding-right: 20px; }
        .notes h4 { margin: 0 0 5px 0; font-size: 11px; color: #777; text-transform: uppercase; }
        .notes p { margin: 0; background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 11px; }
        .totals { display: table-cell; width: 45%; vertical-align: top; }
        .totals table { width: 100%; border: 1px solid #e2e8f0; background: #f8fafc; }
        .totals th, .totals td { border: none; padding: 12px; }
        .totals .subtotal { border-bottom: 1px solid #e2e8f0; }
        .totals .grand-total { background-color: #1e293b; color: white; font-size: 16px; font-weight: bold; }
        .footer { width: 100%; margin-top: 80px; display: table; }
        .footer td { text-align: center; width: 50%; }
        .sig-line { border-top: 1px solid #222; width: 200px; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 11px; }
    </style>
</head>
<body>
    @php
        $partyEntry = $voucher->entries->whereIn('account.group_type', ['Sundry Debtors', 'Sundry Creditors'])->first();
        $party = $partyEntry ? $partyEntry->account : null;
        $totalAmount = $partyEntry ? $partyEntry->amount : $voucher->entries->where('entry_type', 'Debit')->sum('amount');
        
        $logoData = '';
        if($setting->logo_path && file_exists(public_path($setting->logo_path))) {
            $type = pathinfo(public_path($setting->logo_path), PATHINFO_EXTENSION);
            $data = file_get_contents(public_path($setting->logo_path));
            $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp

    <table class="header">
        <tr>
            <td width="60%">
                <table>
                    <tr>
                        @if($logoData)
                            <td width="1%"><img src="{{ $logoData }}" class="logo"></td>
                        @endif
                        <td>
                            <p class="company-name">{{ $setting->company_name }}</p>
                            @if($setting->address)<p class="company-info">{{ $setting->address }}</p>@endif
                            <p class="company-info">
                                @if($setting->phone) Ph: {{ $setting->phone }} @endif
                                @if($setting->email) | Em: {{ $setting->email }} @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="40%">
                <p class="invoice-title {{ str_contains($voucher->voucher_type, 'Return') ? 'return' : '' }}">{{ $voucher->voucher_type }}</p>
                <div class="invoice-meta">
                    <strong>#VCH-{{ $voucher->id }}</strong><br>
                    Date: {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: none; margin-bottom: 20px;">
        <tr>
            <td style="border: none; padding: 0; width: 50%;">
                <div class="bill-to">
                    <h4>Bill To:</h4>
                    <h2>{{ $party ? $party->name : 'Walk-in / General' }}</h2>
                    @if($party)<p>{{ $party->group_type }}</p>@endif
                </div>
            </td>
            <td style="border: none; padding: 0; width: 50%; text-align: right;">
                @if($voucher->reference_number)
                    <div class="bill-to">
                        <h4>Reference:</h4>
                        <p style="font-size: 14px; font-weight: bold; color: #222;">{{ $voucher->reference_number }}</p>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    @if($voucher->inventoryMovements->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voucher->inventoryMovements as $item)
            <tr>
                <td><strong>{{ $item->item->name ?? 'Unknown Item' }}</strong></td>
                <td class="text-right">{{ number_format($item->quantity, 2) }} {{ $item->item->unit ?? 'KG' }}</td>
                <td class="text-right">৳ {{ number_format($item->rate, 2) }}</td>
                <td class="text-right"><strong>৳ {{ number_format($item->quantity * $item->rate, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #777; font-style: italic; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 20px;">
        No inventory items associated with this transaction.
    </p>
    @endif

    <div class="totals-container">
        <div class="notes">
            <h4>Narration / Notes:</h4>
            <p>{{ $voucher->notes ?? 'No additional notes provided.' }}</p>
        </div>
        <div class="totals">
            <table style="margin: 0; padding: 0; border-collapse: collapse;">
                <tr class="subtotal">
                    <td style="border: none; color: #555;"><strong>Subtotal:</strong></td>
                    <td class="text-right" style="border: none;">৳ {{ number_format($totalAmount, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td style="border: none; padding: 12px; font-size: 16px;">TOTAL:</td>
                    <td class="text-right" style="border: none; padding: 12px; font-size: 16px;">৳ {{ number_format($totalAmount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="footer">
        <tr>
            <td><div class="sig-line">Customer / Supplier</div></td>
            <td><div class="sig-line">Authorized Signature<br><span style="font-weight: normal; font-size: 9px; color: #555;">{{ $setting->company_name }}</span></div></td>
        </tr>
    </table>
</body>
</html>