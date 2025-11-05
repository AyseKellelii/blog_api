<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @method authorize(string $string, Category $category)
 */
class CategoryController extends Controller
{
    use AuthorizesRequests;
    // GET /api/categories
    public function index(Request $request)
    {
        $categories = $request->user()->categories()->latest()->get();

        return response()->json([
            'data' => $categories
        ], 200);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $request->user()->categories()->create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'data' => [
                'type' => 'categories',
                'id' => $category->id,
                'attributes' => $category
            ],
            'message' => 'Kategori başarıyla oluşturuldu.'
        ], 201);
    }


    public function show(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->slug) {
            return response()->json([
                'errors' => [
                    'status' => 403,
                    'title' => 'Bu kategoriye erişim izniniz yok.'
                ]
            ], 403);
        }

        return response()->json([
            'data' => [
                'type' => 'categories',
                'id' => $category->id,
                'attributes' => $category
            ]
        ], 200);
    }


    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Kategori başarıyla güncellendi.'
        ]);
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return response()->json([
            'meta' => [
                'message' => 'Kategori silindi.'
            ]
        ], 200);
    }
}
