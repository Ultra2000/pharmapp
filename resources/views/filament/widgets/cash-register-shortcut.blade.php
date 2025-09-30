<x-filament-widgets::widget>
    <x-filament::section>
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-4xl">💰</span>
                        <div>
                            <h2 class="text-2xl font-bold">Interface de Caisse</h2>
                            <p class="text-green-100">Scanner codes-barres • Vente rapide • Calculs automatiques</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-4 mb-4">
                        <div class="flex items-center gap-2 text-green-100">
                            <span class="text-lg">📱</span>
                            <span class="text-sm">Scanner intégré</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-100">
                            <span class="text-lg">⚡</span>
                            <span class="text-sm">Calculs automatiques</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-100">
                            <span class="text-lg">🎯</span>
                            <span class="text-sm">Gestion stock temps réel</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-100">
                            <span class="text-lg">⌨️</span>
                            <span class="text-sm">Raccourcis clavier</span>
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <a href="{{ \App\Filament\Pages\CashRegisterPage::getUrl() }}" 
                       class="inline-flex items-center gap-2 bg-white text-green-600 px-6 py-3 rounded-lg font-semibold hover:bg-green-50 transition-colors text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                        <span class="text-2xl">🚀</span>
                        OUVRIR LA CAISSE
                    </a>
                    
                    <div class="mt-3 text-green-100 text-sm">
                        <div>💡 <strong>F1:</strong> Scanner caméra</div>
                        <div>💡 <strong>F2:</strong> Focus code-barres</div>
                        <div>💡 <strong>F12:</strong> Valider vente</div>
                    </div>
                </div>
            </div>
            
            <!-- Statistiques rapides du jour -->
            <div class="mt-6 grid grid-cols-3 gap-4 pt-4 border-t border-green-400">
                @php
                    $todaySales = \App\Models\Sale::whereDate('created_at', today())->count();
                    $todayRevenue = \App\Models\Sale::whereDate('created_at', today())->sum('total_amount');
                    $avgTicket = $todaySales > 0 ? $todayRevenue / $todaySales : 0;
                @endphp
                
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $todaySales }}</div>
                    <div class="text-green-100 text-sm">Ventes aujourd'hui</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ number_format($todayRevenue, 0) }}€</div>
                    <div class="text-green-100 text-sm">Chiffre d'affaires</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ number_format($avgTicket, 2) }}€</div>
                    <div class="text-green-100 text-sm">Ticket moyen</div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
