<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PharmacyInfo;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService)
    {
    }

    public function generate(Sale $sale)
    {
        $pharmacyInfo = PharmacyInfo::first();
        
        if (!$pharmacyInfo) {
            abort(404, 'Les informations de la pharmacie ne sont pas configurées.');
        }
        
        return $this->invoiceService->generateInvoice($sale, $pharmacyInfo);
    }
}
