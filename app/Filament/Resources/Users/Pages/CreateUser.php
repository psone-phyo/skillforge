<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\InstructorStatus;
use App\Enums\Role;
use App\Filament\Resources\Users\UserResource;
use App\Models\Instructor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected $role;
    protected static string $resource = UserResource::class;
    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = (string) Str::uuid();
        $this->role = $data['role'];
        return $data;
    }

    public function afterCreate(): void
    {
        $this->record->assignRole($this->role);
        if ($this->role == Role::ID_TEACHER) {
            Instructor::create([
                'user_id' => $this->record->id,
                'status' => InstructorStatus::ID_APPROVED,
            ]);
        }elseif($this->role == Role::ID_STUDENT) {
            Instructor::create([
                'user_id' => $this->record->id,
            ]);
        }
    }
}
