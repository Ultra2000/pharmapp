<div>
    <!-- Bouton pour ouvrir le scanner -->
    @if(!$isScanning)
        <button 
            wire:click="startScanning"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            📱 Scanner Code-barres
        </button>
    @endif

    <!-- Modal du scanner -->
    @if($isScanning)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Scanner Code-barres</h3>
                    <button 
                        wire:click="stopScanning"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Zone du scanner -->
                <div class="mb-4">
                    <div id="scanner-container" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <div id="scanner" class="w-full h-64 bg-gray-100 rounded"></div>
                        <p class="mt-2 text-sm text-gray-600">Placez le code-barres devant la caméra</p>
                    </div>
                </div>

                <!-- Saisie manuelle -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ou saisir manuellement:
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            wire:model="scannedBarcode"
                            wire:keydown.enter="barcodeScanned($event.target.value)"
                            placeholder="Code-barres..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <button 
                            wire:click="barcodeScanned(scannedBarcode)"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
                        >
                            ✓
                        </button>
                    </div>
                </div>

                @if($error)
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ $error }}
                    </div>
                @endif

                @if($product)
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 rounded">
                        <h4 class="font-semibold text-green-800">Produit trouvé!</h4>
                        <p class="text-green-700">{{ $product->name }}</p>
                        <p class="text-sm text-green-600">Prix: {{ number_format($product->sale_price, 2) }}€</p>
                        <p class="text-sm text-green-600">Stock: {{ $product->stock }} unités</p>
                        
                        <button 
                            wire:click="addToCart"
                            class="mt-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md w-full"
                        >
                            ➕ Ajouter au panier
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Script pour QuaggaJS -->
    @if($isScanning)
        <script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Quagga !== 'undefined') {
                    Quagga.init({
                        inputStream: {
                            name: "Live",
                            type: "LiveStream",
                            target: document.querySelector('#scanner'),
                            constraints: {
                                width: 480,
                                height: 320,
                                facingMode: "environment"
                            }
                        },
                        decoder: {
                            readers: [
                                "code_128_reader",
                                "ean_reader",
                                "ean_8_reader",
                                "code_39_reader",
                                "code_39_vin_reader",
                                "codabar_reader",
                                "upc_reader",
                                "upc_e_reader"
                            ]
                        }
                    }, function(err) {
                        if (err) {
                            console.error('Erreur d'initialisation du scanner:', err);
                            return;
                        }
                        Quagga.start();
                    });

                    Quagga.onDetected(function(data) {
                        if (data && data.codeResult && data.codeResult.code) {
                            const barcode = data.codeResult.code;
                            @this.call('barcodeScanned', barcode);
                            Quagga.stop();
                        }
                    });
                }
            });

            // Nettoyer le scanner quand le composant est détruit
            Livewire.on('stopScanning', () => {
                if (typeof Quagga !== 'undefined') {
                    Quagga.stop();
                }
            });
        </script>
    @endif
</div>
