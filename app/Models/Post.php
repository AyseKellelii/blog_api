<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, HasTags;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
    ];

     //güncelleme, getirme vs id yerine slug üzerinden yapmak için
     public function getRouteKeyName()
     {
         return 'slug';
     }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

     public function registerMediaCollections(): void
     {
         $this->addMediaCollection('uploads')
             ->useDisk('public')
             ->acceptsFile(function ($file) {
                 $allowedMimeTypes = [
                     'application/pdf',
                     'application/msword',
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                     'image/png',
                     'image/jpeg',
                 ];

                 // Bazı durumlarda $file->mimeType null olabiliyor
                 $mime = $file->mimeType ?? '';

                 return in_array($mime, $allowedMimeTypes);
             });
     }


     public function getActivitylogOptions(): LogOptions
     {
         return LogOptions::defaults()
             ->useLogName('post') // log grubu ismi
             ->logAll()           // tüm alan değişikliklerini logla
             ->setDescriptionForEvent(fn(string $eventName) => "Post {$eventName} işlemi yapıldı"); // açıklama
     }
}
