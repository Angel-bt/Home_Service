<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;

final class ServiceAssetCleaner
{
    private const THUMBNAIL_DIRECTORY = 'images/services/thumbnails';
    private const IMAGE_DIRECTORY = 'images/services';

    /**
     * Remove the image files belonging to a service from the public directory.
     */
    public function deleteFiles(Service $service): void
    {
        $this->deleteFile(self::THUMBNAIL_DIRECTORY, $service->thumbnail);
        $this->deleteFile(self::IMAGE_DIRECTORY, $service->image);
    }

    /**
     * Remove a single image file from a directory inside the public folder.
     *
     * Uses absolute paths so the operation is independent of the current
     * working directory, and silently ignores files that no longer exist.
     */
    public function deleteFile(string $directory, ?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = public_path($directory . '/' . $filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
