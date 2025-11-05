<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            //'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            // Kullanıcı bilgisi
            'user' => [
                'name' => $this->user->name,
            ],

            // Kategori bilgisi
            'category' => [
                'name' => $this->category->name,
            ],

            // Tag de yalnızca isim ve sluggösterme
            'tags' => $this->tags->map(fn($tag) => [
                'name' => $tag->name['en'] ?? null,
                'slug' => $tag->slug['en'] ?? null,
            ]),
        ];
    }
}
