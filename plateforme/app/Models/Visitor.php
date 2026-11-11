<?php

namespace App\Models;

use App\Enums\ThanksStatus;
use Database\Factories\VisitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['first_name', 'last_name', 'origin_church', 'has_no_church', 'whatsapp', 'visited_on', 'device_hash'])]
class Visitor extends Model
{
    /** @use HasFactory<VisitorFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_no_church' => 'boolean',
            'visited_on' => 'date',
            'thanks_status' => ThanksStatus::class,
            'thanks_sent_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Visitor>  $query
     */
    public function scopeAwaitingThanks(Builder $query): void
    {
        $query->where('thanks_status', ThanksStatus::Pending->value);
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
