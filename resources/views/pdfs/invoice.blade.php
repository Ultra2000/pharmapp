<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
        }
        .ticket {
            width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .info {
            margin-bottom: 20px;
        }
        .items {
            width: 100%;
            margin-bottom: 20px;
        }
        .items td {
            padding: 3px 0;
        }
        .total-line {
            border-top: 1px solid #000;
            margin-top: 10px;
            padding-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .spacer {
            margin: 10px 0;
            border-top: 1px dashed #000;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h2 style="margin: 0;">{{ $pharmacy->name }}</h2>
            <p style="margin: 5px 0;">{{ $pharmacy->address }}</p>
            <p style="margin: 5px 0;">{{ $pharmacy->zip }} {{ $pharmacy->city }}</p>
            <p style="margin: 5px 0;">Tel: {{ $pharmacy->phone }}</p>
            <p style="margin: 5px 0;">Email: {{ $pharmacy->email }}</p>
            <p style="margin: 5px 0;">SIRET: {{ $pharmacy->siret }}</p>
            <p style="margin: 5px 0;">Licence: {{ $pharmacy->license_number }}</p>
        </div>

        <div class="info">
            <p style="margin: 2px 0;">Date: {{ $sale->date->format('d/m/Y H:i') }}</p>
            <p style="margin: 2px 0;">Facture N°: {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p style="margin: 2px 0;">Vendeur: {{ $sale->user->name }}</p>
        </div>

        <div class="spacer"></div>

        <table class="items">
            <tr>
                <td colspan="4" class="bold">DÉTAIL DES ARTICLES</td>
            </tr>
            @foreach($sale->items as $item)
            <tr>
                <td colspan="4">{{ $item->product->name }}</td>
            </tr>
            <tr>
                <td>{{ $item->quantity }} x</td>
                <td>{{ number_format($item->unit_price, 2, ',', ' ') }}€</td>
                <td>=</td>
                <td class="text-right">{{ number_format($item->total_price, 2, ',', ' ') }}€</td>
            </tr>
            @endforeach
        </table>

        <div class="spacer"></div>

        <table style="width: 100%">
            <tr class="total-line bold">
                <td style="width: 60%">TOTAL TTC</td>
                <td class="text-right">{{ number_format($sale->total_amount, 2, ',', ' ') }}€</td>
            </tr>
            @if($sale->prescription_file)
            <tr>
                <td colspan="2" class="text-center" style="padding-top: 10px;">
                    ***Ordonnance jointe***
                </td>
            </tr>
            @endif
        </table>

        <div class="spacer"></div>

        <div class="qr-code">
            <img src="data:image/png;base64,{{ base64_encode(QrCode::format('png')->size(100)->generate(
                json_encode([
                    'id' => $sale->id,
                    'date' => $sale->date->format('Y-m-d H:i:s'),
                    'total' => $sale->total_amount,
                    'items' => $sale->items_count
                ])
            )) }}">
        </div>

        <div class="footer">
            @if($pharmacy->invoice_footer)
                <p style="margin: 5px 0;">{{ $pharmacy->invoice_footer }}</p>
            @endif
            @if($pharmacy->vat_number)
                <p style="margin: 5px 0;">N° TVA: {{ $pharmacy->vat_number }}</p>
            @endif
            <p style="margin: 5px 0;">{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
