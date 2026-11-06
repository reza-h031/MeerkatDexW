<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_media_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'avatar_media_id',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarAttribute(): ?string
    {
        $media = $this->avatar_media;

        if (!$media) {
            return null;
        }

        return Storage::disk($media->disk)->url($media->path);
    }

    public function avatar_media(): BelongsTo
    {
        return $this->belongsTo(
            MyMedia::class,
            'avatar_media_id',
            'id'
        );
    }
	public function savedGames(): BelongsToMany
{
    return $this->belongsToMany(
        Game::class,
        'saved_games',
        'user_id',
        'game_id'
    )->withTimestamps();
}

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
