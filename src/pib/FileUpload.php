<?php
namespace pib;

class FileUpload
{
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    private const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
    private const UPLOAD_DIR = __DIR__ . '/../uploads/';

    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
    ];

    public static function uploadFile(array $file)
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            return false;
        }

        $maxSize = self::isVideo($mimeType) ? self::MAX_VIDEO_SIZE : self::MAX_IMAGE_SIZE;
        if ($file['size'] > $maxSize) {
            return false;
        }

        $extension = self::ALLOWED_TYPES[$mimeType];
        $filename = self::generateSecureFilename($extension);
        $uploadPath = self::UPLOAD_DIR . $filename;

        if (!file_exists(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return false;
        }

        return 'uploads/' . $filename;
    }

    private static function generateSecureFilename(string $extension): string
    {
        $hash = bin2hex(random_bytes(16));
        $timestamp = time();
        return "{$timestamp}_{$hash}.{$extension}";
    }

    public static function isImage(string $mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
    }

    public static function isVideo(string $mimeType): bool
    {
        return strpos($mimeType, 'video/') === 0;
    }

    public static function deleteFile(string $relativePath): bool
    {
        if (empty($relativePath) || strpos($relativePath, 'uploads/') !== 0) {
            return false;
        }

        $fullPath = __DIR__ . '/../' . $relativePath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    public static function getFileInfo(string $relativePath): ?array
    {
        if (empty($relativePath)) {
            return null;
        }

        $fullPath = __DIR__ . '/../' . $relativePath;

        if (!file_exists($fullPath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return [
            'path' => $relativePath,
            'mime_type' => $mimeType,
            'is_image' => self::isImage($mimeType),
            'is_video' => self::isVideo($mimeType),
            'size' => filesize($fullPath)
        ];
    }
}
