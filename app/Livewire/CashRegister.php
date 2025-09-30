<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Notifications\Notification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;

class CashRegister extends Component
{
    public $barcode = '';
    public $productSearch = '';
    public Collection $cart;
    public float $subtotal = 0.0;
    public float $tax = 0.0;
    public float $total = 0.0;
    public float $discount = 0.0;
    public float $amountPaid = 0.0;
    public float $change = 0.0;

    protected $listeners = ['productScanned'];

    public function mount()
    {
        $this->cart = collect();
    }

    public function addProductByBarcode()
    {
        if (empty($this->barcode)) {
            Notification::make()
                ->title('Erreur')
                ->body('Veuillez scanner ou saisir un code-barres.')
                ->danger()
                ->send();
            return;
        }

        $product = Product::where('barcode', $this->barcode)->first();
        
        if (!$product) {
            Notification::make()
                ->title('Produit non trouvé')
                ->body("Aucun produit trouvé avec le code-barres: {$this->barcode}")
                ->warning()
                ->send();
            return;
        }

        $this->addProductToCart($product);
        $this->barcode = ''; // Reset du champ
    }

    public function addProductById($productId)
    {
        if (!$productId) return;
        
        $product = Product::find($productId);
        if ($product) {
            $this->addProductToCart($product);
        }
    }

    public function addProductToCart(Product $product)
    {
        if ($product->stock <= 0) {
            Notification::make()
                ->title('Stock insuffisant')
                ->body("Le produit {$product->name} n'est plus en stock.")
                ->danger()
                ->send();
            return;
        }

        $existingItem = $this->cart->firstWhere('id', $product->id);
        
        if ($existingItem) {
            if ($existingItem['quantity'] >= $product->stock) {
                Notification::make()
                    ->title('Stock insuffisant')
                    ->body("Stock maximum atteint pour {$product->name}.")
                    ->warning()
                    ->send();
                return;
            }
            
            $this->cart = $this->cart->map(function ($item) use ($product) {
                if ($item['id'] === $product->id) {
                    $item['quantity']++;
                    $item['total'] = $item['quantity'] * $item['price'];
                }
                return $item;
            });
        } else {
            $this->cart->push([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price,
                'quantity' => 1,
                'total' => $product->sale_price,
                'barcode' => $product->barcode,
                'stock' => $product->stock,
            ]);
        }

        $this->calculateTotals();
        
        Notification::make()
            ->title('Produit ajouté')
            ->body("{$product->name} ajouté au panier.")
            ->success()
            ->send();
    }

    public function removeFromCart($productId)
    {
        $this->cart = $this->cart->reject(fn ($item) => $item['id'] === $productId);
        $this->calculateTotals();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $this->cart = $this->cart->map(function ($item) use ($productId, $quantity) {
            if ($item['id'] === $productId) {
                if ($quantity > $item['stock']) {
                    Notification::make()
                        ->title('Stock insuffisant')
                        ->body("Stock maximum: {$item['stock']}")
                        ->warning()
                        ->send();
                    return $item;
                }
                
                $item['quantity'] = $quantity;
                $item['total'] = $item['quantity'] * $item['price'];
            }
            return $item;
        });

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->cart->sum('total');
        $this->tax = $this->subtotal * 0.20; // TVA 20%
        $this->total = $this->subtotal + $this->tax - $this->discount;
        
        // Calcul du reliquat : positif = monnaie à rendre, négatif = montant manquant
        $this->change = $this->amountPaid - $this->total;
    }

    public function quickSetAmount($amount)
    {
        $this->amountPaid = floatval($amount);
        $this->calculateTotals();
        
        Notification::make()
            ->title('Montant défini')
            ->body("Montant reçu: " . number_format($amount, 2) . "€")
            ->success()
            ->duration(1000)
            ->send();
    }

    public function processSale()
    {
        if ($this->cart->isEmpty()) {
            Notification::make()
                ->title('Panier vide')
                ->body('Ajoutez des produits avant de valider la vente.')
                ->warning()
                ->send();
            return;
        }

        if ($this->amountPaid < $this->total) {
            Notification::make()
                ->title('Montant insuffisant')
                ->body('Le montant payé est insuffisant.')
                ->danger()
                ->send();
            return;
        }

        // Créer la vente
        $sale = Sale::create([
            'user_id' => auth()->id(),
            'date' => now(),
            'total_amount' => $this->total,
            'tax_amount' => $this->tax,
            'discount_amount' => $this->discount,
            'payment_method' => 'cash',
        ]);

        // Ajouter les articles de vente et mettre à jour le stock
        foreach ($this->cart as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total_price' => $item['total'],
            ]);

            // Mettre à jour le stock
            $product = Product::find($item['id']);
            $product->decrement('stock', $item['quantity']);
        }

        Notification::make()
            ->title('Vente validée')
            ->body("Vente #{$sale->id} enregistrée avec succès!")
            ->success()
            ->send();

        // Reset du panier
        $this->resetCart();
    }

    public function resetCart()
    {
        $this->cart = collect();
        $this->subtotal = 0.0;
        $this->tax = 0.0;
        $this->total = 0.0;
        $this->discount = 0.0;
        $this->amountPaid = 0.0;
        $this->change = 0.0;
        $this->barcode = '';
        $this->productSearch = '';
    }

    public function productScanned($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $this->addProductToCart($product);
        }
    }

    public function updatedAmountPaid()
    {
        $this->calculateTotals();
    }

    public function getProductSearchOptions()
    {
        if (empty($this->productSearch)) {
            return [];
        }

        return Product::where('name', 'like', "%{$this->productSearch}%")
            ->orWhere('dci', 'like', "%{$this->productSearch}%")
            ->limit(10)
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.cash-register');
    }
}
