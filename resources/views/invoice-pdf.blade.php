<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #VCH-{{ $voucher->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #222; margin: 0; padding: 0; }
        .top-accent { height: 8px; background-color: {{ str_contains($voucher->voucher_type, 'Return') ? '#f97316' : '#2563eb' }}; width: 100%; position: absolute; top: 0; left: 0; }
        .header { width: 100%; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; margin-top: 15px; }
        .header td { vertical-align: bottom; }
        .logo { max-height: 60px; max-width: 150px; margin-right: 15px; }
        .company-name { font-size: 26px; font-weight: 900; color: #0f172a; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;}
        .company-info { font-size: 11px; color: #64748b; margin: 4px 0 0 0; }
        .invoice-title { font-size: 26px; font-weight: 900; text-transform: uppercase; margin: 0; text-align: right; color: #2563eb; letter-spacing: 2px; }
        .invoice-title.return { color: #ea580c; }
        .invoice-meta { text-align: right; font-size: 14px; margin-top: 5px; color: #475569;}
        .invoice-meta-date { text-align: right; font-size: 12px; margin-top: 5px; font-weight: bold; background: #f1f5f9; display: inline-block; padding: 4px 8px; border-radius: 4px;}
        
        .bill-to-container { width: 100%; margin-bottom: 25px; display: table; }
        .bill-to-card { background-color: #f8fafc; padding: 18px; border-radius: 12px; border: 1px solid #e2e8f0; width: 50%; display: table-cell; vertical-align: top; }
        .bill-to-card h4 { margin: 0 0 8px 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;}
        .bill-to-card h2 { margin: 0; font-size: 20px; color: #0f172a; font-weight: 900;}
        .party-type { font-size: 10px; font-weight: bold; color: #2563eb; background: #dbeafe; padding: 3px 6px; border-radius: 4px; display: inline-block; margin: 6px 0 10px 0; text-transform: uppercase; }
        .contact-info { margin: 4px 0 0 0; font-size: 12px; color: #475569; }
        
        .ref-card { width: 50%; display: table-cell; text-align: right; vertical-align: top; }
        .ref-card h4 { margin: 0 0 5px 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;}
        .ref-card p { margin: 0; font-size: 16px; font-weight: bold; color: #0f172a; background: #f1f5f9; display: inline-block; padding: 4px 10px; border-radius: 4px;}

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background-color: #1e293b; color: white; padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .text-right { text-align: right !important; }
        .items-table td { border-bottom: 1px solid #e2e8f0; padding: 12px; vertical-align: top; font-size: 12px; color: #334155; }
        
        .totals-container { width: 100%; display: table; margin-top: 15px;}
        .notes { display: table-cell; width: 50%; vertical-align: top; padding-right: 30px; }
        .notes h4 { margin: 0 0 8px 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .notes p { margin: 0; background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 11px; color: #475569; font-style: italic; }
        
        .totals { display: table-cell; width: 50%; vertical-align: top; }
        .totals table { width: 100%; border-collapse: collapse; background: #fff; }
        .totals td { padding: 10px 15px; border: none; border-bottom: 1px solid #e2e8f0; font-size: 13px;}
        .totals-label { color: #64748b; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .totals-value { font-weight: bold; color: #0f172a; font-size: 14px;}
        .totals .grand-total td { background-color: #0f172a; color: white; font-weight: 900; border: none; font-size: 16px;}
        
        .dr-cr-badge { background: #e2e8f0; color: #475569; font-size: 9px; padding: 2px 4px; border-radius: 3px; margin-left: 4px; vertical-align: middle;}
        
        .footer { width: 100%; margin-top: 100px; display: table; }
        .footer td { text-align: center; width: 50%; }
        .sig-line { border-top: 1px solid #334155; width: 220px; margin: 0 auto; padding-top: 8px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #1e293b;}
    </style>
</head>
<body>
    <div class="top-accent"></div>

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
            <td width="60%">
                <table>
                    <tr>
                        @if($logoData)
                            <td width="1%" style="padding-right: 15px;"><img src="{{ $logoData }}" class="logo"></td>
                        @endif
                        <td>
                            <p class="company-name">{{ $setting->company_name }}</p>
                            @if($setting->address)<p class="company-info">{{ $setting->address }}</p>@endif
                            <p class="company-info">
                                @if($setting->phone) <strong>P:</strong> {{ $setting->phone }} @endif
                                @if($setting->email) | <strong>E:</strong> {{ $setting->email }} @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="40%" style="text-align: right;">
                <p class="invoice-title {{ str_contains($voucher->voucher_type, 'Return') ? 'return' : '' }}">{{ $voucher->voucher_type }}</p>
                <div class="invoice-meta">
                    <strong>#VCH-{{ $voucher->id }}</strong>
                </div>
                <div style="margin-top: 8px;">
                    <span class="invoice-meta-date">Date: {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="bill-to-container">
        <div class="bill-to-card">
            <h4>Bill To:</h4>
            <h2>{{ $party ? $party->name : 'Walk-in / General' }}</h2>
            @if($party)
                <div class="party-type">{{ $party->group_type }}</div>
                @if($party->mobile_number)<p class="contact-info"><strong>Phone:</strong> {{ $party->mobile_number }}</p>@endif
                @if($party->address)<p class="contact-info"><strong>Address:</strong> {{ $party->address }}</p>@endif
            @endif
        </div>
        
        <div class="ref-card">
            @if($voucher->reference_number)
                <h4>Reference:</h4>
                <p>{{ $voucher->reference_number }}</p>
            @endif
        </div>
    </div>

    @if($voucher->inventoryMovements->count() > 0)
    <table class="items-table">
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
                <td class="text-right">{{ number_format($item->quantity, 2) }} <span style="font-size: 10px; color: #94a3b8;">{{ $item->item->unit ?? 'KG' }}</span></td>
                <td class="text-right">৳ {{ number_format($item->rate, 2) }}</td>
                <td class="text-right"><strong>৳ {{ number_format($item->quantity * $item->rate, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align: center; color: #94a3b8; font-style: italic; background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px;">
        No inventory items associated with this transaction.
    </p>
    @endif

    <div class="totals-container">
        <div class="notes">
            <h4>Narration / Notes:</h4>
            <p>{{ $voucher->notes ?? 'No additional notes provided.' }}</p>
        </div>
        <div class="totals">
            <table>
                <tr>
                    <td class="totals-label">Invoice Amount:</td>
                    <td class="text-right totals-value">৳ {{ number_format($totalAmount, 2) }}</td>
                </tr>
                @if($party)
                <tr>
                    <td class="totals-label">Previous Balance:</td>
                    <td class="text-right" style="font-weight: bold; color: #475569;">
                        {{ number_format(abs($previousBalanceRaw), 2) }} <span class="dr-cr-badge">{{ $previousBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                <tr class="grand-total">
                    <td style="padding: 15px;">NET BALANCE:</td>
                    <td class="text-right" style="padding: 15px;">
                        {{ number_format(abs($currentBalanceRaw), 2) }} <span style="font-size: 11px; color: #cbd5e1; margin-left: 4px;">{{ $currentBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                @else
                <tr class="grand-total">
                    <td style="padding: 15px;">TOTAL:</td>
                    <td class="text-right" style="padding: 15px;">৳ {{ number_format($totalAmount, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <table class="footer">
        <tr>
            <td><div class="sig-line">Customer / Supplier</div></td>
            <td><div class="sig-line">Authorized Signature<br><span style="font-weight: normal; font-size: 9px; color: #94a3b8; margin-top: 4px; display: inline-block;">{{ $setting->company_name }}</span></div></td>
        </tr>
    </table>
</body>
</html>