<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class BarcodeScanner extends Component
{
    public $isScanning = false;
    public $scannedBarcode = '';
    public $product = null;
    public $error = '';

    protected $listeners = ['barcodeScanned'];

    public function startScanning()
    {
        $this->isScanning = true;
        $this->error = '';
        $this->product = null;
    }

    public function stopScanning()
    {
        $this->isScanning = false;
    }

    public function barcodeScanned($barcode)
    {
        $this->scannedBarcode = $barcode;
        $this->isScanning = false;
        
        $this->product = Product::where('barcode', $barcode)->first();
        
        if (!$this->product) {
            $this->error = "Produit non trouvé pour le code-barres: {$barcode}";
        } else {
            $this->error = '';
            // Émettre un événement pour informer le parent
            $this->dispatch('productScanned', $this->product->id);
        }
    }

    public function addToCart()
    {
        if ($this->product) {
            $this->dispatch('productScanned', $this->product->id);
            $this->reset(['product', 'scannedBarcode']);
        }
    }

    public function render()
    {
        return view('livewire.barcode-scanner');
    }
}
