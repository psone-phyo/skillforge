<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\Tag;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
    protected $tagArray = [];
    public function mutateFormDataBeforeCreate(array $data): array
    {

        if (isset($data['tags'])){
            foreach ($data['tags'] as $key => $tag) {
                $this->tagArray[] = Tag::firstOrCreate([
                    'name' => $tag,
                    'slug' => make_slug($tag),
                ])->id;
            }
        }

        $data['course_code'] = strtoupper(Str::random(6)) . mt_rand(10, 99);
        $data['slug'] = Str::slug($data['title']);
        $data['instructor_id'] = Auth::user()->id;
        return $data;
    }

    public function afterCreate(): void
    {
        $this->record->tags()->attach($this->tagArray);
    }
}
