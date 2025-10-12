<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
     public function index(Request $request)
    {
        $filters = [
            'q'          => trim($request->get('q', '')),
            'category'   => $request->get('category'),    // category_id
            'tag'        => $request->get('tag'),         // tag_id
            'level'      => $request->get('level'),       // basic | intermediate | advanced
            'language'   => $request->get('language'),    // Myanmar | English
            'is_paid'    => $request->get('is_paid'),     // 0 | 1
            'status'     => $request->get('status'),      // on_progress | finished | published | inactive
            'price_min'  => $request->get('price_min'),
            'price_max'  => $request->get('price_max'),
            'sort'       => $request->get('sort'),        // newest | price_asc | price_desc | rating_desc
        ];

        $query = Course::query()
            ->with(['instructor:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->withRatingAvg();

        if ($filters['q']) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('mm_title', 'like', "%{$q}%")
                    ->orWhere('sub_title', 'like', "%{$q}%")
                    ->orWhere('mm_sub_title', 'like', "%{$q}%")
                    ->orWhere('course_code', 'like', "%{$q}%");
            });
        }

        if ($filters['category']) {
            $categoryId = (int)$filters['category'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($filters['tag']) {
            $tagId = (int)$filters['tag'];
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        if ($filters['level']) {
            $query->where('level', $filters['level']);
        }

        if ($filters['language']) {
            $query->where('language', $filters['language']);
        }

        if ($filters['is_paid'] !== null && $filters['is_paid'] !== '') {
            $query->where('is_paid', (int)$filters['is_paid']);
        }

        // if ($filters['status']) {
        //     $query->where('status', $filters['status']);
        // }

        if (is_numeric($filters['price_min'])) {
            $query->where('price', '>=', (float)$filters['price_min']);
        }
        if (is_numeric($filters['price_max'])) {
            $query->where('price', '<=', (float)$filters['price_max']);
        }

        // Sorting
        switch ($filters['sort']) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating_desc':
                $query->orderBy('rating_avg', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc');
                break;
        }

        $courses = $query->paginate(12)->appends($filters);

        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $tags = Tag::query()->orderBy('name')->get(['id', 'name', 'slug']);

        return Inertia::render('CourseList', [
            'courses'    => $courses,
            'categories' => $categories,
            'tags'       => $tags,
            'filters'    => $filters,
        ]);
    }
}
