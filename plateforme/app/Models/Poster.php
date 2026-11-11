<?php

namespace App\Models;

use Database\Factories\PosterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A poster (flyer, event visual, business ad…) shared by a member on their profile
 * and shown for a few days in the "Affiches" stories on the home screen.
 */
#[Fillable(['caption'])]
class Poster extends Model
{
    /** @use HasFactory<PosterFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Posters still shown in the stories.
     *
     * @param  Builder<Poster>  $query
     */
    public function scopeInStories(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subDays(config('tegv.story_days')));
    }

    /**
     * @return Attribute<string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('media')->url($this->image_path));
    }
}
