<?php

namespace App\Services;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceService
{
    public function generateInvoice(Sale $sale, $pharmacyInfo)
    {
        $pdf = Pdf::loadView('pdfs.invoice', [
            'sale' => $sale,
            'pharmacy' => $pharmacyInfo,
        ]);

        return $pdf->download('facture-' . $sale->id . '.pdf');
    }
}
