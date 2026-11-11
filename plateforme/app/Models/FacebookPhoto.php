<?php

namespace App\Models;

use Database\Factories\FacebookPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A photo copied from the church's Facebook page, used on the public screens.
 */
#[Fillable(['facebook_id', 'image_path', 'caption', 'width', 'height', 'posted_at'])]
class FacebookPhoto extends Model
{
    /** @use HasFactory<FacebookPhotoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('media')->url($this->image_path));
    }
}
