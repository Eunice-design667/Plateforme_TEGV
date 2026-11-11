<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded pictures as JPEG no wider/taller than a maximum size,
 * so posters and stories stay light on mobile data.
 */
class ImageResizer
{
    public static function storeJpeg(UploadedFile $file, string $directory, int $maxSide = 1600): string
    {
        if (! extension_loaded('gd')) {
            return $file->store($directory, 'media');
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($source === false) {
            return $file->store($directory, 'media');
        }

        $source = self::applyExifOrientation($source, $file);
        [$width, $height] = [imagesx($source), imagesy($source)];
        $ratio = min(1, $maxSide / max($width, $height));
        $target = imagecreatetruecolor((int) round($width * $ratio), (int) round($height * $ratio));
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        ob_start();
        imagejpeg($target, null, 82);
        $path = $directory.'/'.Str::uuid().'.jpg';
        Storage::disk('media')->put($path, (string) ob_get_clean());

        return $path;
    }

    /**
     * Phone photos are often stored sideways with a rotation flag.
     */
    private static function applyExifOrientation(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}
