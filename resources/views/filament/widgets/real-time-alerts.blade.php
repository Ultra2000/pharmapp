<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="text-lg font-semibold">🚨 Alertes en Temps Réel</span>
                @if($this->getTotalAlertsCount() > 0)
                    <x-filament::badge color="danger" size="sm">
                        {{ $this->getTotalAlertsCount() }}
                    </x-filament::badge>
                @endif
            </div>
        </x-slot>

        @php
            $criticalAlerts = $this->getCriticalAlerts();
            $highAlerts = $this->getHighPriorityAlerts();
            $allAlerts = $this->getAlerts();
            $totalAlertsCount = $this->getTotalAlertsCount();
        @endphp

        @if($totalAlertsCount == 0)
            <div class="text-center py-8">
                <div class="text-6xl mb-4">✅</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Tout va bien !
                </h3>
                <p class="text-gray-500 dark:text-gray-400">
                    Aucune alerte détectée dans le système.
                </p>
            </div>
        @else
            <!-- Alertes Critiques -->
            @if(!empty($criticalAlerts))
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 text-red-600 font-semibold mb-3">
                        <span class="text-xl">🚨</span>
                        Alertes Critiques ({{ count($criticalAlerts) }})
                    </h3>
                    <div class="space-y-3">
                        @foreach($criticalAlerts as $alert)
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="text-2xl">{{ $alert['icon'] }}</span>
                                        <div>
                                            <h4 class="font-semibold text-red-800 dark:text-red-200">
                                                {{ $alert['title'] }}
                                            </h4>
                                            <p class="text-red-700 dark:text-red-300 text-sm">
                                                {{ $alert['message'] }}
                                            </p>
                                            <p class="text-red-600 dark:text-red-400 text-xs mt-2 font-medium">
                                                👉 {{ $alert['action'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <x-filament::badge color="danger" size="sm">
                                        URGENT
                                    </x-filament::badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Alertes Haute Priorité -->
            @if(!empty($highAlerts))
                <div class="mb-6">
                    <h3 class="flex items-center gap-2 text-orange-600 font-semibold mb-3">
                        <span class="text-xl">⚠️</span>
                        Alertes Prioritaires ({{ count($highAlerts) }})
                    </h3>
                    <div class="space-y-3">
                        @foreach($highAlerts as $alert)
                            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="text-xl">{{ $alert['icon'] }}</span>
                                        <div>
                                            <h4 class="font-semibold text-orange-800 dark:text-orange-200">
                                                {{ $alert['title'] }}
                                            </h4>
                                            <p class="text-orange-700 dark:text-orange-300 text-sm">
                                                {{ $alert['message'] }}
                                            </p>
                                            <p class="text-orange-600 dark:text-orange-400 text-xs mt-2 font-medium">
                                                👉 {{ $alert['action'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <x-filament::badge color="warning" size="sm">
                                        PRIORITAIRE
                                    </x-filament::badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Résumé des autres alertes -->
            @php
                $mediumAlerts = [];
                $lowAlerts = [];
                foreach($allAlerts as $category => $alerts) {
                    foreach($alerts as $alert) {
                        if($alert['priority'] === 'medium') $mediumAlerts[] = $alert;
                        if($alert['priority'] === 'low') $lowAlerts[] = $alert;
                    }
                }
            @endphp

            @if(!empty($mediumAlerts) || !empty($lowAlerts))
                <div class="border-t pt-4">
                    <!-- Alertes Moyennes -->
                    @if(!empty($mediumAlerts))
                        <div class="mb-4">
                            <h3 class="flex items-center gap-2 text-yellow-600 font-semibold mb-3">
                                <span class="text-xl">⚡</span>
                                Alertes Moyennes ({{ count($mediumAlerts) }})
                            </h3>
                            <details class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <summary class="cursor-pointer font-medium text-yellow-800 dark:text-yellow-200">
                                    Cliquez pour voir les détails
                                </summary>
                                <div class="mt-3 space-y-2">
                                    @foreach($mediumAlerts as $alert)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span>{{ $alert['icon'] }}</span>
                                            <div>
                                                <strong>{{ $alert['title'] }}</strong><br>
                                                <span class="text-yellow-700 dark:text-yellow-300">{{ $alert['message'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif

                    <!-- Alertes Informatives -->
                    @if(!empty($lowAlerts))
                        <div class="mb-4">
                            <h3 class="flex items-center gap-2 text-blue-600 font-semibold mb-3">
                                <span class="text-xl">ℹ️</span>
                                Alertes Informatives ({{ count($lowAlerts) }})
                            </h3>
                            <details class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <summary class="cursor-pointer font-medium text-blue-800 dark:text-blue-200">
                                    Cliquez pour voir les détails
                                </summary>
                                <div class="mt-3 space-y-2">
                                    @foreach($lowAlerts as $alert)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span>{{ $alert['icon'] }}</span>
                                            <div>
                                                <strong>{{ $alert['title'] }}</strong><br>
                                                <span class="text-blue-700 dark:text-blue-300">{{ $alert['message'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        <div class="mt-4 text-xs text-gray-400 text-center">
            Dernière mise à jour : {{ now()->format('H:i:s') }} - Actualisation automatique toutes les 30s
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
