<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = \App\Models\User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => bcrypt($data['user']['password']),
            'email_verified_at' => $data['user']['is_verified'] ? now()->format('Y-m-d H:i:s') : null,
        ]);

        $data['user_id'] = $user->id;

        unset($data['user']);

        return $data;
    }
}
