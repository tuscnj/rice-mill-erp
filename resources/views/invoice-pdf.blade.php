<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #VCH-{{ $voucher->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #222; margin: 0; padding: 0; }
        .top-accent { height: 8px; background-color: {{ str_contains($voucher->voucher_type, 'Return') ? '#f97316' : '#2563eb' }}; width: 100%; position: absolute; top: 0; left: 0; }
        
        .info-header { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 12px; }
        .logo { max-height: 60px; max-width: 150px; margin-right: 15px; }
        .company-name { font-size: 22px; font-weight: 900; color: #0f172a; text-transform: uppercase; margin: 0; }
        .company-info { font-size: 11px; color: #64748b; margin: 4px 0 0 0; }
        .party-name { margin: 0; font-size: 20px; color: #0f172a; font-weight: 900; }
        .party-type { font-size: 9px; font-weight: bold; color: #2563eb; background: #dbeafe; padding: 3px 6px; border-radius: 4px; display: inline-block; margin: 6px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;}
        .contact-info { margin: 2px 0 0 0; font-size: 11px; color: #475569; }

        .middle-row { width: 100%; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin-bottom: 25px; display: table; table-layout: fixed; }
        .middle-row td { padding: 12px; text-align: center; border-right: 1px solid #e2e8f0; }
        .middle-row td:last-child { border-right: none; }
        .meta-label { font-size: 9px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 3px; letter-spacing: 0.5px;}
        .meta-value { font-size: 14px; font-weight: 900; color: #0f172a; margin: 0; }
        .meta-type { font-size: 16px; font-weight: 900; text-transform: uppercase; margin: 0; color: #2563eb; }
        .meta-type.return { color: #ea580c; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background-color: #1e293b; color: white; padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .text-right { text-align: right !important; }
        .items-table td { border-bottom: 1px solid #e2e8f0; padding: 12px; vertical-align: top; font-size: 12px; color: #334155; }
        
        .totals-container { width: 100%; display: table; margin-top: 15px;}
        .notes { display: table-cell; width: 50%; vertical-align: top; padding-right: 30px; }
        .notes h4 { margin: 0 0 8px 0; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        .notes p { margin: 0; background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 11px; color: #475569; font-style: italic; }
        
        .totals { display: table-cell; width: 50%; vertical-align: top; }
        .totals table { width: 100%; border-collapse: collapse; background: #fff; }
        .totals td { padding: 10px 15px; border: none; border-bottom: 1px solid #e2e8f0; font-size: 13px;}
        .totals-label { color: #64748b; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;}
        .totals-value { font-weight: bold; color: #0f172a; font-size: 14px;}
        .totals .grand-total td { background-color: #0f172a; color: white; font-weight: 900; border: none; font-size: 16px;}
        
        .dr-cr-badge { background: #e2e8f0; color: #475569; font-size: 9px; padding: 2px 4px; border-radius: 3px; margin-left: 4px; vertical-align: middle;}
        
        .footer { width: 100%; margin-top: 80px; display: table; }
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

    <table style="width: 100%; margin-top: 30px; margin-bottom: 25px; table-layout: fixed;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                <div class="info-header">Billed From:</div>
                <table style="margin: 0; padding: 0; border: none; width: 100%;">
                    <tr>
                        @if($logoData)
                            <td width="1%" style="padding: 0; padding-right: 15px; border: none; vertical-align: top;"><img src="{{ $logoData }}" class="logo"></td>
                        @endif
                        <td style="padding: 0; border: none; vertical-align: top;">
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

            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="info-header">Billed To:</div>
                <h2 class="party-name">{{ $party ? $party->name : 'Walk-in / General' }}</h2>
                @if($party)
                    <div class="party-type">{{ $party->group_type }}</div>
                    @if($party->mobile_number)<p class="contact-info"><strong>Phone:</strong> {{ $party->mobile_number }}</p>@endif
                    @if($party->address)<p class="contact-info"><strong>Address:</strong> {{ $party->address }}</p>@endif
                @endif
            </td>
        </tr>
    </table>

    <table class="middle-row">
        <tr>
            <td>
                <div class="meta-label">Invoice Type</div>
                <div class="meta-type {{ str_contains($voucher->voucher_type, 'Return') ? 'return' : '' }}">{{ $voucher->voucher_type }}</div>
            </td>
            <td>
                <div class="meta-label">Invoice No.</div>
                <div class="meta-value">#VCH-{{ $voucher->id }}</div>
            </td>
            <td>
                <div class="meta-label">Date</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') }}</div>
            </td>
            @if($voucher->reference_number)
            <td>
                <div class="meta-label">Reference</div>
                <div class="meta-value">{{ $voucher->reference_number }}</div>
            </td>
            @endif
        </tr>
    </table>

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
    <p style="text-align: center; color: #94a3b8; font-style: italic; background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px;">
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
                    <td style="padding: 15px; border-radius: 4px 0 0 4px;">NET BALANCE:</td>
                    <td class="text-right" style="padding: 15px; border-radius: 0 4px 4px 0;">
                        {{ number_format(abs($currentBalanceRaw), 2) }} <span style="font-size: 11px; color: #cbd5e1; margin-left: 4px;">{{ $currentBalanceRaw >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                @else
                <tr class="grand-total">
                    <td style="padding: 15px; border-radius: 4px 0 0 4px;">TOTAL:</td>
                    <td class="text-right" style="padding: 15px; border-radius: 0 4px 4px 0;">৳ {{ number_format($totalAmount, 2) }}</td>
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