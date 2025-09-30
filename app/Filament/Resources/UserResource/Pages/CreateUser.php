<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected $roles;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Supprimer le champ roles des données principales
        if (isset($data['roles'])) {
            $this->roles = $data['roles'];
            unset($data['roles']);
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Assigner les rôles après la création
        if (isset($this->roles)) {
            $this->record->assignRole($this->roles);
        }
    }
}
