<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private string $disk = 'public';

    // =========================================================
    // STORE PRODUCT THUMBNAIL
    // =========================================================

    public function storeThumbnail(?UploadedFile $file, string $folder = 'products'): ?string
    {
        if (! $file) {
            return null;
        }

        $filename = $this->generateFilename($file);

        return $file->storeAs(
            $folder . '/thumbnails',
            $filename,
            $this->disk
        );
    }

    // =========================================================
    // STORE MULTIPLE PRODUCT IMAGES
    // =========================================================

    public function storeMultiple(array|UploadedFile|null $files, string $folder = 'products'): array
    {
        // 🔥 Normalize input (THIS FIXES YOUR ISSUE)
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (empty($files)) {
            return [];
        }

        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->storeAs(
                    $folder . '/gallery',
                    $this->generateFilename($file),
                    $this->disk
                );
            }
        }

        return $paths;
    }

    // =========================================================
    // STORE CATEGORY IMAGE
    // =========================================================

    public function storeCategoryImage(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $filename = $this->generateFilename($file);

        return $file->storeAs('categories', $filename, $this->disk);
    }

    // =========================================================
    // STORE USER AVATAR
    // =========================================================

    public function storeAvatar(?UploadedFile $file, int $userId): ?string
    {
        if (! $file) {
            return null;
        }

        $filename = "avatar-{$userId}." . $file->getClientOriginalExtension();

        return $file->storeAs('avatars', $filename, $this->disk);
    }

    // =========================================================
    // DELETE IMAGE
    // =========================================================

    public function delete(?string $path): void
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
    }

    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $random    = Str::random(16);

        return "img-{$random}.{$extension}";
    }

    // =========================================================
    // GET PUBLIC URL
    // =========================================================

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/' . $path);
    }
}