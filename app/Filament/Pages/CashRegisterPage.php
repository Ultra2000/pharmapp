<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\Reactive;

class CashRegisterPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = '💰 Caisse';
    protected static ?string $navigationGroup = 'Ventes';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.cash-register';

    public ?array $data = [];
    public Collection $cart;
    public float $subtotal = 0.0;
    public float $tax = 0.0;
    public float $total = 0.0;
    public float $discount = 0.0;
    public float $amountPaid = 0.0;
    public float $change = 0.0;

    protected $listeners = ['productScanned'];

    public function mount(): void
    {
        $this->cart = collect();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('barcode')
                            ->label('🔍 Scanner Code-barres')
                            ->placeholder('Scannez ou tapez le code-barres...')
                            ->autofocus()
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('scan')
                                    ->icon('heroicon-m-camera')
                                    ->action('addProductByBarcode')
                            ),
                        
                        Select::make('product_search')
                            ->label('🔍 Recherche Produit')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => 
                                Product::where('name', 'like', "%{$search}%")
                                    ->orWhere('dci', 'like', "%{$search}%")
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->name)
                            ->afterStateUpdated(fn ($state) => $this->addProductById($state)),
                    ]),
            ])
            ->statePath('data');
    }

    public function addProductByBarcode(): void
    {
        $barcode = $this->data['barcode'] ?? '';
        
        if (empty($barcode)) {
            Notification::make()
                ->title('Erreur')
                ->body('Veuillez scanner ou saisir un code-barres.')
                ->danger()
                ->send();
            return;
        }

        $product = Product::where('barcode', $barcode)->first();
        
        if (!$product) {
            Notification::make()
                ->title('Produit non trouvé')
                ->body("Aucun produit trouvé avec le code-barres: {$barcode}")
                ->warning()
                ->send();
            return;
        }

        $this->addProductToCart($product);
        $this->data['barcode'] = ''; // Reset du champ
    }

    public function addProductById($productId): void
    {
        if (!$productId) return;
        
        $product = Product::find($productId);
        if ($product) {
            $this->addProductToCart($product);
        }
    }

    public function addProductToCart(Product $product): void
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

    public function removeFromCart($productId): void
    {
        $this->cart = $this->cart->reject(fn ($item) => $item['id'] === $productId);
        $this->calculateTotals();
    }

    public function updateQuantity($productId, $quantity): void
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

    public function calculateTotals(): void
    {
        $this->subtotal = $this->cart->sum('total');
        $this->tax = $this->subtotal * 0.20; // TVA 20%
        $this->total = $this->subtotal + $this->tax - $this->discount;
        
        // Calcul du reliquat : positif = monnaie à rendre, négatif = montant manquant
        $this->change = $this->amountPaid - $this->total;
    }

    public function processSale(): void
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
            'total_amount' => $this->total,
            'tax_amount' => $this->tax,
            'discount_amount' => $this->discount,
            'payment_method' => 'cash', // À étendre avec d'autres méthodes
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

    public function resetCart(): void
    {
        $this->cart = collect();
        $this->subtotal = 0.0;
        $this->tax = 0.0;
        $this->total = 0.0;
        $this->discount = 0.0;
        $this->amountPaid = 0.0;
        $this->change = 0.0;
        $this->data = [];
    }

    public function startBarcodeScanner(): void
    {
        // Méthode pour intégrer un scanner de code-barres réel
        // Pour l'instant, on utilise JavaScript côté client
        $this->dispatch('start-barcode-scanner');
    }

    public function productScanned($productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->addProductToCart($product);
        }
    }

    public function setAmountPaid($amount): void
    {
        $this->amountPaid = floatval($amount);
        $this->calculateTotals();
        
        // Force un refresh de la vue
        $this->dispatch('$refresh');
    }

    public function updatedAmountPaid(): void
    {
        $this->calculateTotals();
    }

    public function quickSetAmount($amount): void
    {
        // Méthode spécifique pour les raccourcis
        $this->amountPaid = floatval($amount);
        $this->calculateTotals();
        
        Notification::make()
            ->title('Montant défini')
            ->body("Montant reçu: " . number_format($amount, 2) . "€")
            ->success()
            ->duration(1000)
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newSale')
                ->label('🔄 Nouvelle Vente')
                ->action('resetCart')
                ->color('success'),
        ];
    }
}
