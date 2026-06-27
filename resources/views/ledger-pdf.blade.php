<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger Statement</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; width: 100%; display: table; }
        .header td { vertical-align: top; }
        .logo { max-height: 60px; max-width: 150px; margin-right: 15px; }
        .company-info h1 { margin: 0; font-size: 24px; color: #111; text-transform: uppercase; }
        .company-info p { margin: 2px 0; color: #555; }
        .statement-info { text-align: right; }
        .statement-info h2 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; color: #333; }
        .statement-info h3 { margin: 5px 0 0 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f4f4f4; border-bottom: 2px solid #555; padding: 8px 5px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { border-bottom: 1px solid #ddd; padding: 8px 5px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-dr { color: #059669; }
        .text-cr { color: #e11d48; }
        .inventory-box { background-color: #f9f9f9; padding: 5px; margin-top: 5px; border: 1px solid #eee; border-radius: 3px; font-size: 9px; }
        .footer { width: 100%; margin-top: 50px; display: table; }
        .footer td { text-align: center; font-size: 10px; width: 50%; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 5px; font-weight: bold; }
    </style>
</head>
<body>

    @php
        // Securely convert image to Base64 so the PDF Engine reads it instantly
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
                        <td class="company-info">
                            <h1>{{ $setting->company_name }}</h1>
                            @if($setting->address)<p>{{ $setting->address }}</p>@endif
                            <p>
                                @if($setting->phone) Ph: {{ $setting->phone }} @endif
                                @if($setting->email) | Em: {{ $setting->email }} @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="40%" class="statement-info">
                <h2>Statement of Account</h2>
                <h3>{{ $account->name }}</h3>
                <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Particulars</th>
                <th>Vch Type</th>
                <th class="text-right">Debit (Dr)</th>
                <th class="text-right">Credit (Cr)</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" class="text-center font-bold">OPENING BALANCE</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-bold">
                    {{ number_format(abs($openingBalanceRaw), 2) }} {{ $openingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}
                </td>
            </tr>

            @foreach($entries as $row)
            @php $entry = $row['entry']; @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($entry->voucher->voucher_date)->format('d-M-Y') }}</td>
                <td>
                    <div class="font-bold">
                        @if($row['particulars']->count() > 0)
                            By {{ $row['particulars']->pluck('account.name')->implode(', ') }}
                        @else
                            Self / System Adjustment
                        @endif
                    </div>
                    
                    @if($isDetailed)
                        @if($entry->voucher->reference_number || $entry->voucher->notes)
                            <div style="font-size: 9px; color: #555; margin-top: 3px;">
                                {{ $entry->voucher->reference_number ? 'Ref: '.$entry->voucher->reference_number.' | ' : '' }}
                                {{ $entry->voucher->notes ?? '' }}
                            </div>
                        @endif

                        @if($row['inventory']->count() > 0)
                            <div class="inventory-box">
                                <strong>Inventory Included:</strong><br>
                                @foreach($row['inventory'] as $inv)
                                    • {{ $inv->item->name }} | {{ number_format($inv->quantity, 2) }} {{ $inv->item->unit ?? 'KG' }} @ {{ number_format($inv->rate, 2) }}<br>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </td>
                <td>{{ $entry->voucher->voucher_type }}<br><span style="font-size: 8px; color: #888;">#VCH-{{ $entry->voucher->id }}</span></td>
                <td class="text-right font-bold text-dr">{{ $entry->entry_type == 'Debit' ? number_format($entry->amount, 2) : '' }}</td>
                <td class="text-right font-bold text-cr">{{ $entry->entry_type == 'Credit' ? number_format($entry->amount, 2) : '' }}</td>
                <td class="text-right font-bold">
                    {{ number_format(abs($row['running_balance']), 2) }} {{ $row['running_balance'] >= 0 ? 'Dr' : 'Cr' }}
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="3" class="text-center font-bold" style="background-color: #eee;">CLOSING BALANCE</td>
                <td class="text-right" style="background-color: #eee;"></td>
                <td class="text-right" style="background-color: #eee;"></td>
                <td class="text-right font-bold" style="background-color: #eee; font-size: 13px;">
                    ৳ {{ number_format(abs($closingBalanceRaw), 2) }} {{ $closingBalanceRaw >= 0 ? 'Dr' : 'Cr' }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td><div class="signature-line">Prepared By</div></td>
            <td><div class="signature-line">Authorized Signature<br><span style="font-weight: normal; font-size: 8px;">{{ $setting->company_name }}</span></div></td>
        </tr>
    </table>

</body>
</html>