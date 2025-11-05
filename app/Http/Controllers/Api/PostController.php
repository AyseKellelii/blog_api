<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    // posts?tag=laravel veya ?search=php url i için
    public function index(Request $request)
    {
        // Tüm postları user, category ve tags ile birlikte çek
        $query = Post::with(['user:id,name', 'category:id,name', 'tags'])->latest();

        //  Arama filtresi title veya content
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        //  Tarih aralığı filtresi (created_at)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        //  Tag filtresi
        if ($request->filled('tag')) {
            $query->withAnyTags([$request->tag]);
        }

        //  Sayfalama  10 kayıt / sayfa başına
        $posts = $query->paginate(10);

        //  JSON standartlarına uygun response
        return response()->json([
            'meta' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'filters' => [
                    'search' => $request->search ?? null,
                    'tag' => $request->tag ?? null,
                    'start_date' => $request->start_date ?? null,
                    'end_date' => $request->end_date ?? null
                ],
            ],
            'links' => [
                'self' => $request->fullUrl(),
                'next' => $posts->nextPageUrl(),
                'prev' => $posts->previousPageUrl(),
            ],
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function store(StorePostRequest $request)
    {
        $category = $request->user()
            ->categories()
            ->where('name', $request->category_name)
            ->first();

        if (!$category) {
            return response()->json([
                'errors' => [
                    'status' => 404,
                    'title' => 'Bu kategori size ait değil veya bulunamadı.'
                ]
            ], 404);
        }

        $post = $request->user()->posts()->create([
            'category_id' => $category->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->input('content')
        ]);

        if ($request->has('tags')) {
            $post->attachTags($request->tags);
        }

        if ($request->hasFile('file')) {
            $post->addMediaFromRequest('file')->toMediaCollection('uploads');
        }

        //gerçekleşen aktiviteyi veritabanına kaydetme
        activity()->performedOn($post)->causedBy($request->user())->log('Yeni gönderi oluşturuldu.');

        return response()->json([
            'data' => $post->load('category', 'tags'),
            'message' => 'Gönderi başarıyla oluşturuldu.'
        ], 201);
    }


    public function show(Request $request, Post $post)
    {
        // Sadece kendi postunu tam detaylı görebilir
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'errors' => ['status' => 403, 'title' => 'Bu gönderiye erişim izniniz yok.']
            ], 403);
        }

        return response()->json(['data' => $post->load('category', 'tags')], 200);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'errors' => ['status' => 403, 'title' => 'Bu gönderiye erişim izniniz yok.']
            ], 403);
        }

        // Kategori adından id'yi bul
        $category = Category::where('name', $request->category_name)->first();

        if (!$category) {
            return response()->json([
                'errors' => [
                    'status' => 404,
                    'title' => 'Girilen kategori bulunamadı.'
                ]
            ], 404);
        }

        $post->update([
            'category_id' => $category->id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->input('content'),
        ]);

        if ($request->has('tags')) {
            $post->syncTags($request->tags);
        }

        if ($request->hasFile('file')) {
            $post->clearMediaCollection('uploads');
            $post->addMediaFromRequest('file')->toMediaCollection('uploads');
        }

        //gerçekleşen aktiviteyi veritabanına kaydetme
        activity()->performedOn($post)->causedBy($request->user())->log('Gönderi güncellendi.');

        return response()->json([
            'data' => $post->load('category', 'tags'),
            'message' => 'Gönderi başarıyla güncellendi.'
        ], 200);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'errors' => ['status' => 403, 'title' => 'Bu gönderiye erişim izniniz yok.']
            ], 403);
        }

        $post->clearMediaCollection('uploads');
        $post->delete();

        activity()->performedOn($post)->causedBy($request->user())->log('Gönderi silindi.');

        return response()->json(['meta' => ['message' => 'Gönderi silindi.']], 200);
    }

    public function postsByUser(User $user)
    {
        $posts = $user->posts()
            ->with(['category:id,name', 'tags'])
            ->latest()
            ->get();

        return response()->json([
            'meta' => [
                'user' => $user->name,
                'count' => $posts->count()
            ],
            'data' => $posts
        ], 200);
    }

}
