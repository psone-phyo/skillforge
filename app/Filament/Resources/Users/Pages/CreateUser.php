<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
        {
        if ($data['is_verified']){
            $data['email_verified_at'] = now()->format('Y-m-d H:i:s');
        };
        return $data;
        }

    protected function afterCreate(): void
    {
        $user = $this->record;

        $role = $this->form->getState()['role'] ?? null;

        if ($role) {
            $user->assignRole($role);
        }
    }
}
