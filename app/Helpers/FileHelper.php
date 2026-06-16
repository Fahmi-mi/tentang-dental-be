<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Upload image to storage
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder (e.g., 'doctors', 'promos', 'services')
     * @return string|null - Returns filename or null on failure
     */
    public static function uploadImage($file, string $folder): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $filename, 'public');

        return $path ? $filename : null;
    }

    /**
     * Delete image from storage
     *
     * @param string $path - Relative path from storage/app/public (e.g., 'doctors/image.jpg')
     * @return bool
     */
    public static function deleteImage(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Generate URL-friendly slug from title
     *
     * @param string $title
     * @return string
     */
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);

        if (empty($slug)) {
            $slug = Str::random(10);
        }

        return $slug;
    }

    /**
     * Format standard API response
     *
        * @template TData
     * @param bool $success
        * @param TData|null $data
     * @param string $message
        * @return array{success: bool, data?: TData, message?: string}
     */
    public static function formatResponse(bool $success, $data = null, string $message = ''): array
    {
        $response = [
            'success' => $success,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($message)) {
            $response['message'] = $message;
        }

        return $response;
    }
}