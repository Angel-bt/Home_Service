<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Public images (services, categories, provider photos, slides), wherever they live.
 *
 * Locally they are files under public/images. On a host without a permanent disk
 * (Vercel) FILESYSTEM_DISK points at object storage instead, while the images shipped
 * with the code keep being served from public/images.
 */
final class Media
{
    public static function url(string $directory, ?string $file): string
    {
        $path = $directory . '/' . $file;

        if (! self::onObjectStorage() || is_file(public_path('images/' . $path))) {
            return asset('images/' . $path);
        }

        return self::disk()->url($path);
    }

    /**
     * Saves an upload in $directory under $name, explicitly on the media disk:
     * without it, Livewire would use its own temporary uploads disk.
     */
    public static function store(UploadedFile $file, string $directory, string $name): void
    {
        $file->storeAs($directory, $name, config('filesystems.default'));
    }

    /**
     * Files that no longer exist are ignored, and so are the ones shipped with the code.
     */
    public static function delete(string $directory, ?string $file): void
    {
        if ($file === null || $file === '') {
            return;
        }

        $path = $directory . '/' . $file;

        if (! self::onObjectStorage()) {
            $absolute = public_path('images/' . $path);

            if (is_file($absolute)) {
                unlink($absolute);
            }

            return;
        }

        if (! is_file(public_path('images/' . $path))) {
            self::disk()->delete($path);
        }
    }

    private static function onObjectStorage(): bool
    {
        return config('filesystems.disks.' . config('filesystems.default') . '.driver') === 's3';
    }

    private static function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk(config('filesystems.default'));
    }
}
