<?php

namespace App\Models;

use App\Enums\AnnouncementStatus;
use App\Enums\ChurchRole;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'body'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AnnouncementStatus::class,
            'published_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', AnnouncementStatus::Published->value);
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    public function scopeProposed(Builder $query): void
    {
        $query->where('status', AnnouncementStatus::Proposed->value);
    }

    /**
     * "par Samuel Ouédraogo (pasteur)", shown under published information.
     */
    public function publisherLabel(): ?string
    {
        if ($this->validator === null) {
            return null;
        }

        $title = $this->validator->hasChurchRole(ChurchRole::Pasteur) ? 'pasteur' : 'diacre';

        return "par {$this->validator->full_name} ({$title})";
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Another leader opened it for review in the last 30 minutes.
     */
    public function isBeingReviewedByAnotherThan(User $leader): bool
    {
        return $this->claimed_by !== null
            && $this->claimed_by !== $leader->id
            && $this->claimed_at?->gt(now()->subMinutes(30));
    }
}
