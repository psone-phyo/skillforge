<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['instructor_id'] = Auth::id();
        return $data;
    }
}
