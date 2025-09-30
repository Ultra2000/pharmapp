<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Panneau de gauche: Scanner et recherche -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Scanner et recherche -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🔍</span>
                    <span class="font-semibold">Scanner & Recherche</span>
                </div>
            </x-slot>

            <div class="space-y-4">
                <!-- Code-barres -->
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model="barcode"
                            wire:keydown.enter="addProductByBarcode"
                            placeholder="Scannez ou tapez le code-barres..."
                            autofocus
                        />
                    </x-filament::input.wrapper>
                </div>
                
                <!-- Recherche produit -->
                <div>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model.live="productSearch"
                            placeholder="Recherche produit par nom..."
                        />
                    </x-filament::input.wrapper>
                    
                    @if(!empty($productSearch))
                        <div class="mt-2 max-h-40 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                            @foreach(App\Models\Product::where('name', 'like', "%{$productSearch}%")->orWhere('dci', 'like', "%{$productSearch}%")->limit(10)->get() as $product)
                                <button 
                                    wire:click="addProductById({{ $product->id }})"
                                    class="w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-700 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $product->dci }} - {{ number_format($product->sale_price, 2) }}€</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Scanner intégré -->
                <div class="border-t pt-4">
                    @livewire('barcode-scanner')
                </div>
                
                <!-- Bouton ajouter -->
                <div class="flex gap-2">
                    <x-filament::button 
                        wire:click="addProductByBarcode"
                        color="success"
                        icon="heroicon-m-plus"
                        size="lg"
                        class="flex-1"
                    >
                        ➕ Ajouter au Panier
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <!-- Panier -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛒</span>
                        <span class="font-semibold">Panier ({{ $cart->count() }} articles)</span>
                    </div>
                    @if($cart->isNotEmpty())
                        <x-filament::button 
                            wire:click="resetCart"
                            color="danger"
                            size="sm"
                            icon="heroicon-m-trash"
                        >
                            Vider
                        </x-filament::button>
                    @endif
                </div>
            </x-slot>

            @if($cart->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <div class="text-6xl mb-4">🛒</div>
                    <p class="text-lg">Panier vide</p>
                    <p class="text-sm">Scannez un produit pour commencer</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($cart as $index => $item)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $item['name'] }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Code: {{ $item['barcode'] }} | Stock: {{ $item['stock'] }}
                                </p>
                                <p class="text-lg font-bold text-green-600">
                                    {{ number_format($item['price'], 2) }}€ × {{ $item['quantity'] }}
                                    = {{ number_format($item['total'], 2) }}€
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <!-- Contrôles quantité -->
                                <div class="flex items-center bg-white dark:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-600">
                                    <button 
                                        wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                        class="px-3 py-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        <span class="text-lg">−</span>
                                    </button>
                                    
                                    <span class="px-4 py-1 font-semibold min-w-[3rem] text-center text-gray-900 dark:text-gray-100">
                                        {{ $item['quantity'] }}
                                    </span>
                                    
                                    <button 
                                        wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                        class="px-3 py-1 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                                        @if($item['quantity'] >= $item['stock']) disabled @endif
                                    >
                                        <span class="text-lg">+</span>
                                    </button>
                                </div>
                                
                                <!-- Bouton supprimer -->
                                <x-filament::button 
                                    wire:click="removeFromCart({{ $item['id'] }})"
                                    color="danger"
                                    size="sm"
                                    icon="heroicon-m-trash"
                                >
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>

    <!-- Panneau de droite: Totaux et paiement -->
    <div class="space-y-6">
        <!-- Totaux -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="text-xl">💰</span>
                    <span class="font-semibold">Totaux</span>
                </div>
            </x-slot>

            <div class="space-y-4">
                <div class="flex justify-between text-lg">
                    <span>Sous-total:</span>
                    <span class="font-semibold">{{ number_format($subtotal, 2) }}€</span>
                </div>
                
                <div class="flex justify-between text-lg">
                    <span>TVA (20%):</span>
                    <span class="font-semibold">{{ number_format($tax, 2) }}€</span>
                </div>
                
                @if($discount > 0)
                    <div class="flex justify-between text-lg text-green-600">
                        <span>Remise:</span>
                        <span class="font-semibold">-{{ number_format($discount, 2) }}€</span>
                    </div>
                @endif
                
                <hr class="border-gray-300 dark:border-gray-600">
                
                <div class="flex justify-between text-2xl font-bold text-green-600">
                    <span>TOTAL:</span>
                    <span>{{ number_format($total, 2) }}€</span>
                </div>
            </div>
        </x-filament::section>

        <!-- Paiement -->
        @if($cart->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">💳</span>
                        <span class="font-semibold">Paiement</span>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <!-- Montant reçu -->
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                            💰 Montant reçu:
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                step="0.01" 
                                wire:model.live="amountPaid"
                                id="amountPaidInput"
                                class="w-full px-4 py-4 text-2xl text-center border-2 rounded-lg focus:ring-4 focus:ring-green-500/50 focus:border-green-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 font-bold shadow-sm"
                                placeholder="0.00"
                                autocomplete="off"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 text-xl">€</span>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 text-center">
                            Tapez le montant reçu du client
                        </div>
                    </div>

                    <!-- Raccourcis montants -->
                    <div class="grid grid-cols-3 gap-2">
                        @php
                            $suggestions = [
                                ceil($total), 
                                ceil($total / 10) * 10, 
                                ceil($total / 20) * 20,
                                ceil($total / 50) * 50
                            ];
                            $suggestions = array_unique($suggestions);
                            sort($suggestions);
                        @endphp
                        
                        @foreach($suggestions as $amount)
                            <x-filament::button 
                                wire:click="quickSetAmount({{ $amount }})"
                                color="primary"
                                size="sm"
                            >
                                {{ $amount }}€
                            </x-filament::button>
                        @endforeach
                    </div>

                    @if($amountPaid > 0)
                        <div class="space-y-3">
                            @if($change >= 0)
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                    <div class="flex justify-between items-center text-lg font-semibold text-green-600">
                                        <span class="flex items-center gap-2">
                                            <span class="text-xl">💰</span>
                                            Monnaie à rendre:
                                        </span>
                                        <span>{{ number_format($change, 2) }}€</span>
                                    </div>
                                </div>
                            @else
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex justify-between items-center text-lg font-semibold text-red-600">
                                        <span class="flex items-center gap-2">
                                            <span class="text-xl">⚠️</span>
                                            Montant manquant:
                                        </span>
                                        <span>{{ number_format(abs($change), 2) }}€</span>
                                    </div>
                                    <div class="mt-2 text-sm text-red-500">
                                        Ajoutez {{ number_format(abs($change), 2) }}€ pour compléter le paiement
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Bouton valider -->
                    @php
                        $canValidate = $change >= 0 && !$cart->isEmpty();
                        $buttonText = $cart->isEmpty() ? '❌ PANIER VIDE' : 
                                     ($change < 0 ? '⚠️ PAIEMENT INSUFFISANT' : '✅ VALIDER LA VENTE');
                        $buttonColor = $canValidate ? 'success' : 'danger';
                    @endphp
                    
                    <x-filament::button 
                        wire:click="processSale"
                        :color="$buttonColor"
                        size="lg"
                        class="w-full"
                        :disabled="!$canValidate"
                    >
                        <span class="text-lg">{{ $buttonText }}</span>
                    </x-filament::button>
                    
                    @if(!$cart->isEmpty() && $change < 0)
                        <div class="text-center text-sm text-red-600 mt-2">
                            Il manque {{ number_format(abs($change), 2) }}€ pour valider cette vente
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif

        <!-- Raccourcis clavier -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="text-xl">⌨️</span>
                    <span class="font-semibold">Raccourcis</span>
                </div>
            </x-slot>

            <div class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
                <div><x-filament::badge color="gray" size="sm">F1</x-filament::badge> Scanner caméra</div>
                <div><x-filament::badge color="gray" size="sm">F2</x-filament::badge> Focus code-barres</div>
                <div><x-filament::badge color="gray" size="sm">F3</x-filament::badge> Focus montant</div>
                <div><x-filament::badge color="gray" size="sm">F12</x-filament::badge> Valider vente</div>
                <div><x-filament::badge color="gray" size="sm">Esc</x-filament::badge> Nouvelle vente</div>
            </div>
        </x-filament::section>
    </div>
</div>

<!-- Scripts pour raccourcis clavier -->
<script>
    // Raccourcis clavier
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            // Scanner caméra
        } else if (e.key === 'F2') {
            e.preventDefault();
            document.querySelector('input[wire\\:model="barcode"]')?.focus();
        } else if (e.key === 'F3') {
            e.preventDefault();
            document.getElementById('amountPaidInput')?.focus();
        } else if (e.key === 'F12') {
            e.preventDefault();
            @this.call('processSale');
        } else if (e.key === 'Escape') {
            e.preventDefault();
            @this.call('resetCart');
        }
    });

    // Auto-sélection du texte dans le champ montant
    document.addEventListener('click', function(e) {
        if (e.target.id === 'amountPaidInput') {
            e.target.select();
        }
    });
</script>
