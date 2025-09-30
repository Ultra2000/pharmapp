<?php

namespace App\Filament\Resources\PharmacyInfoResource\Pages;

use App\Filament\Resources\PharmacyInfoResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManagePharmacyInfos extends ManageRecords
{
    protected static string $resource = PharmacyInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Limiter à une seule entrée
        if ($this->getModel()::count() > 0 && !$this->record) {
            Notification::make()
                ->warning()
                ->title('Une seule configuration est autorisée')
                ->send();
            
            $this->halt();
        }

        return $data;
    }
}
