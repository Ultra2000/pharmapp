<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    protected $roles;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Supprimer le champ roles des données principales
        if (isset($data['roles'])) {
            $this->roles = $data['roles'];
            unset($data['roles']);
        }
        
        // Si le mot de passe est vide, on ne le modifie pas
        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Synchroniser les rôles après la sauvegarde
        if (isset($this->roles)) {
            $this->record->syncRoles($this->roles);
        }
    }
}
