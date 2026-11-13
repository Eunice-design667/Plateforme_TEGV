<?php

namespace App\Models;

use App\Enums\ChurchRole;
use App\Enums\LeaderStatus;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name', 'email', 'password',
    'first_name', 'last_name', 'primary_profile', 'secondary_profiles', 'years_experience',
    'company', 'job_title', 'birth_day', 'birth_month', 'church_roles', 'other_roles',
    'whatsapp', 'contact_email', 'is_tegv_christian',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'secondary_profiles' => 'array',
            'church_roles' => 'array',
            'other_roles' => 'array',
            'is_tegv_christian' => 'boolean',
            'is_admin' => 'boolean',
            'profile_completed_at' => 'datetime',
            'leader_status' => LeaderStatus::class,
            'leader_requested_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'leader_validated_at' => 'datetime',
            'years_experience' => 'integer',
            'birth_day' => 'integer',
            'birth_month' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function leaderValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_validated_by');
    }

    /**
     * @return HasMany<BirthdayMessage, $this>
     */
    public function birthdayMessages(): HasMany
    {
        return $this->hasMany(BirthdayMessage::class);
    }

    /**
     * @return HasMany<Poster, $this>
     */
    public function posters(): HasMany
    {
        return $this->hasMany(Poster::class);
    }

    /**
     * @return HasMany<Announcement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Members who finished the second registration step.
     *
     * @param  Builder<User>  $query
     */
    public function scopeWithCompletedProfile(Builder $query): void
    {
        // Accounts waiting for deletion are hidden from the directory and the birthdays.
        $query->whereNotNull('profile_completed_at')->whereNull('deletion_requested_at');
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeBornOn(Builder $query, int $day, int $month): void
    {
        $query->where('birth_day', $day)->where('birth_month', $month);
    }

    /**
     * Members whose birthday is celebrated on the given day. Members born on
     * 29 February celebrate on 28 February when the year is not a leap year.
     *
     * @param  Builder<User>  $query
     */
    public function scopeCelebratingOn(Builder $query, CarbonInterface $day): void
    {
        $query->where(function (Builder $query) use ($day): void {
            $query->bornOn($day->day, $day->month);

            if ($day->month === 2 && $day->day === 28 && ! $day->isLeapYear()) {
                $query->orWhere(fn (Builder $query) => $query->bornOn(29, 2));
            }
        });
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function isPendingDeletion(): bool
    {
        return $this->deletion_requested_at !== null;
    }

    /**
     * Day when a requested deletion becomes final.
     */
    public function deletionDate(): ?CarbonInterface
    {
        return $this->deletion_requested_at?->copy()->addDays(config('tegv.account_deletion_days'));
    }

    /**
     * Pastor and deacon rights stay inactive until an administrator approves them.
     */
    public function isLeader(): bool
    {
        return $this->leader_status === LeaderStatus::Approved && $this->declaresLeaderRole();
    }

    public function declaresLeaderRole(): bool
    {
        return $this->hasChurchRole(ChurchRole::Pasteur) || $this->hasChurchRole(ChurchRole::Diacre);
    }

    public function hasChurchRole(ChurchRole $role): bool
    {
        return in_array($role->value, $this->church_roles ?? [], true);
    }

    /**
     * Readable list of roles, with the free-text "Autre" roles in place of "Autre".
     *
     * @return list<string>
     */
    public function roleLabels(): array
    {
        $labels = collect($this->church_roles ?? [])
            ->map(fn (string $role) => ChurchRole::tryFrom($role))
            ->filter(fn (?ChurchRole $role) => $role !== null && $role !== ChurchRole::Autre)
            ->map(fn (ChurchRole $role) => $role->label());

        return $labels->merge($this->other_roles ?? [])->values()->all();
    }

    public function canSeeAnnouncements(): bool
    {
        return $this->is_tegv_christian || $this->isLeader();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->first_name} {$this->last_name}") ?: $this->email);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function initials(): Attribute
    {
        return Attribute::get(fn () => mb_strtoupper(
            mb_substr($this->first_name ?? $this->email, 0, 1).mb_substr($this->last_name ?? '', 0, 1)
        ));
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo_path ? Storage::disk('media')->url($this->photo_path) : null);
    }

    /**
     * Share of the profile that is filled in, used to rank directory results.
     */
    public function completeness(): float
    {
        $fields = ['photo_path', 'primary_profile', 'years_experience', 'company', 'job_title', 'whatsapp', 'contact_email'];
        $filled = collect($fields)->filter(fn (string $field) => filled($this->{$field}))->count();

        return $filled / count($fields);
    }
}
