<?php

namespace App\Models;

use Database\Factories\AbsenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['first_name', 'last_name', 'reason', 'starts_on', 'ends_on', 'is_tegv_christian'])]
class Absence extends Model
{
    /** @use HasFactory<AbsenceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_tegv_christian' => 'boolean',
            'handled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function markHandledBy(?User $leader): void
    {
        $this->handled_at = $leader ? now() : null;
        $this->handled_by = $leader?->id;
        $this->save();
    }
}
