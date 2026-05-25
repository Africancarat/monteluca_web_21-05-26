<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageHelper
{
    /**
     * Resolve items.photo / thumbnail (filename, site path, or absolute URL) for use in img src.
     * Always builds a URL for the current host (never returns a stale absolute URL from the DB).
     */
    public static function storageImageUrl(?string $path, ?string $placeholder = null): string
    {
        $placeholder ??= url('/core/public/storage/images/placeholder.png');

        if ($path === null || trim($path) === '') {
            return $placeholder;
        }

        $filename = self::storageImageBasename($path);
        if ($filename === '') {
            return $placeholder;
        }

        $filename = self::resolveExistingStorageFilename($filename);

        return url('/core/public/storage/images/' . $filename);
    }

    /**
     * Extract the images/ basename from a filename, relative path, or legacy absolute URL.
     */
    public static function storageImageBasename(string $path): string
    {
        $p = self::normalizeStorageImagePath(trim($path));

        if (preg_match('/^https?:\/\//i', $p)) {
            $p = basename(parse_url($p, PHP_URL_PATH) ?: '') ?: $p;
        }

        $p = preg_replace('#^/?core/public/storage/images/#i', '', $p);
        $p = preg_replace('#^/?storage/images/#i', '', $p);

        return ltrim(str_replace('\\', '/', $p), '/');
    }

    /**
     * When the DB omits an extension, pick the first matching file on disk.
     */
    public static function resolveExistingStorageFilename(string $filename): string
    {
        $dir = public_path('storage/images');
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;

        if (is_file($fullPath)) {
            return $filename;
        }

        if (pathinfo($filename, PATHINFO_EXTENSION) !== '') {
            return $filename;
        }

        foreach (['.png', '.jpg', '.jpeg', '.webp', '.gif'] as $ext) {
            $candidate = $filename . $ext;
            if (is_file($dir . DIRECTORY_SEPARATOR . $candidate)) {
                return $candidate;
            }
        }

        return $filename;
    }

    /**
     * Filesystem slug for a diamond shape label (e.g. "Round" → "round").
     */
    public static function diamondShapeSlug(?string $shapeLabel): string
    {
        if ($shapeLabel === null || trim($shapeLabel) === '') {
            return '';
        }

        return strtolower(trim($shapeLabel));
    }

    /**
     * URL for storage/images/{shape}.svg when the file exists; null otherwise.
     */
    public static function diamondShapeIconUrl(?string $shapeLabel): ?string
    {
        $slug = self::diamondShapeSlug($shapeLabel);
        if ($slug === '') {
            return null;
        }

        $file = $slug . '.svg';
        if (! is_file(public_path('storage/images/' . $file))) {
            return null;
        }

        return self::storageImageUrl($file);
    }

    /**
     * Repair legacy double-prefixed values:
     * /core/public/storage/images/http://host/.../file.jpg
     */
    public static function normalizeStorageImagePath(string $path): string
    {
        $prev = null;
        while ($path !== $prev) {
            $prev = $path;
            if (preg_match('#/storage/images/(https?://.+)$#i', $path, $m)) {
                $path = $m[1];
                continue;
            }
            if (preg_match('#(?:^|/storage/images/)(https?://.+)#i', $path, $m)) {
                $path = $m[1];
            }
        }

        return $path;
    }

    /**
     * Original uploaded basename (no path traversal). Keeps client name as-is aside from safety trimming.
     */
    public static function originalClientFilename(UploadedFile $file): string
    {
        $basename = basename(str_replace('\\', '/', (string) $file->getClientOriginalName()));
        $basename = str_replace("\0", '', $basename);

        if ($basename === '' || $basename === '.' || $basename === '..') {
            $ext = ltrim((string) $file->getClientOriginalExtension(), '.');
            $basename = $ext !== '' ? 'upload.' . $ext : 'upload.bin';
        }

        return $basename;
    }

    /**
     * Thumbnail filename paired with a main product image (e.g. ring.png → ring-thumb.png).
     */
    public static function thumbnailFilename(string $photoFilename): string
    {
        $info = pathinfo($photoFilename);
        $base = $info['filename'] ?? $photoFilename;
        $ext = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';

        if (str_ends_with(strtolower($base), '-thumb')) {
            return $photoFilename;
        }

        return $base . '-thumb' . $ext;
    }

    /**
     * If $directory/$filename already exists, use name-2.ext, name-3.ext, … (never random prefixes).
     *
     * @throws RuntimeException when more than 999 name collisions occur
     */
    public static function resolveAvailableFilename(string $directory, string $filename): string
    {
        $directory = trim($directory, '/');
        $relative = $directory . '/' . $filename;

        if (! Storage::exists($relative)) {
            return $filename;
        }

        $info = pathinfo($filename);
        $base = $info['filename'] ?? 'upload';
        $ext = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';

        for ($i = 2; $i <= 999; $i++) {
            $candidate = $base . '-' . $i . $ext;
            if (! Storage::exists($directory . '/' . $candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("Could not store upload: too many files named like {$filename} in {$directory}.");
    }

    /**
     * Basename from a URL or path (for CSV remote downloads).
     */
    public static function filenameFromUrlOrPath(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'download.jpg';
        }

        $path = parse_url($value, PHP_URL_PATH);
        if (is_string($path) && $path !== '') {
            $base = basename($path);
            if ($base !== '' && $base !== '.' && $base !== '..') {
                return $base;
            }
        }

        return basename(str_replace('\\', '/', $value)) ?: 'download.jpg';
    }

    public static function handleUploadedImage($file, $path, $delete = null)
    {
        if (! $file) {
            return null;
        }

        if ($delete) {
            Storage::delete($path . '/' . $delete);
        }

        if (! $file instanceof UploadedFile) {
            return null;
        }

        $name = self::resolveAvailableFilename($path, self::originalClientFilename($file));
        Storage::putFileAs($path, $file, $name);

        return $name;
    }

    public static function uploadSummernoteImage($file, $path)
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $path = trim((string) $path, '/');
        $name = self::resolveAvailableFilename($path, self::originalClientFilename($file));
        Storage::putFileAs($path, $file, $name);

        return $name;
    }

    public static function ItemhandleUploadedImage($file, $path, $delete = null)
    {
        if (! $file) {
            return null;
        }

        if ($delete) {
            Storage::delete($path . '/' . $delete);
        }

        $photoName = self::resolveAvailableFilename($path, self::originalClientFilename($file));
        $thumbnailName = self::resolveAvailableFilename($path, self::thumbnailFilename($photoName));

        Storage::putFileAs($path, $file, $photoName);

        $image = \Image::make($file)->resize(230, 230);
        Storage::put($path . '/' . $thumbnailName, (string) $image->encode());

        return [$photoName, $thumbnailName];
    }

    public static function handleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {
        $name = self::resolveAvailableFilename($path, self::originalClientFilename($file));

        Storage::putFileAs($path, $file, $name);

        if (! empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }

        return $name;
    }

    public static function ItemhandleUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    {
        $photoName = self::resolveAvailableFilename($path, self::originalClientFilename($file));
        $thumbnailName = self::resolveAvailableFilename($path, self::thumbnailFilename($photoName));

        $image = \Image::make($file)->resize(230, 230);
        Storage::put($path . '/' . $thumbnailName, (string) $image->encode());

        Storage::putFileAs($path, $file, $photoName);

        if (! empty($data['thumbnail'])) {
            Storage::delete($delete_path . '/' . $data['thumbnail']);
        }

        if (! empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }

        return [$photoName, $thumbnailName];
    }

    public static function handleDeletedImage($data, $field, $delete_path)
    {
        if (! empty($data[$field])) {
            Storage::delete($delete_path . '/' . $data[$field]);
        }
    }
}
