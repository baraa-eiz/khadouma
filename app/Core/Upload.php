<?php

namespace App\Core;

class Upload
{
    /**
     * Validate an uploaded file.
     *
     * @param array $file $_FILES element
     * @param int $maxSize Max size in bytes (default 5MB)
     * @param array $allowedMimes Allowed MIME types
     * @return true
     * @throws \RuntimeException
     */
    public static function validate(array $file, int $maxSize = 5242880, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp']): bool
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('معلمات ملف الرفع غير صالحة.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('لم يتم رفع أي ملف.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('تجاوز حجم الملف الحد الأقصى المسموح به.');
            default:
                throw new \RuntimeException('حدث خطأ غير معروف أثناء رفع الملف.');
        }

        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('حجم الملف كبير جداً. الحد الأقصى هو ' . ($maxSize / 1024 / 1024) . ' ميغابايت.');
        }

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime, $allowedMimes)) {
            throw new \RuntimeException('نوع الملف غير مسموح به. الأنواع المسموحة هي: ' . implode(', ', $allowedMimes));
        }

        return true;
    }

    /**
     * Convert, compress, and save an image to the public uploads folder.
     *
     * @param array $file $_FILES element
     * @param string $subFolder Target subfolder (e.g. 'avatars')
     * @param int|null $targetWidth Optional width to resize
     * @param int $quality WebP compression quality (default 80)
     * @return string Returns relative public URL path
     */
    public static function savePublicImage(array $file, string $subFolder, ?int $targetWidth = null, int $quality = 80): string
    {
        self::validate($file);

        $rootDir = dirname(dirname(__DIR__));
        $year = date('Y');
        $month = date('m');
        
        $relativeDir = 'uploads-public/' . trim($subFolder, '/') . '/' . $year . '/' . $month;
        $destDir = $rootDir . '/public/' . $relativeDir;

        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        $filename = sha1(uniqid((string)rand(), true)) . '.webp';
        $destPath = $destDir . '/' . $filename;

        // Process and save as WebP (strips EXIF automatically via GD recreation)
        self::processAndSaveImageToWebp($file['tmp_name'], $destPath, $targetWidth, $quality);

        return '/' . $relativeDir . '/' . $filename;
    }

    /**
     * Save a document securely to private storage.
     *
     * @param array $file $_FILES element
     * @param string $subFolder e.g. 'identity_proofs'
     * @return array Array with filename and relative storage path
     */
    public static function savePrivateDocument(array $file, string $subFolder): array
    {
        // Enforce pdf or images for private verification documents
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        self::validate($file, 10485760, $allowedMimes); // 10MB limit for documents

        $filename = sha1(uniqid((string)rand(), true)) . '_' . basename($file['name']);
        $relativeDir = 'secure_uploads/' . trim($subFolder, '/');
        $destPath = $relativeDir . '/' . $filename;

        // Save via storage manager
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            throw new \RuntimeException('فشل في قراءة ملف الرفع.');
        }

        if (!Storage::put($destPath, $content)) {
            throw new \RuntimeException('فشل في حفظ الملف في المجلد الآمن.');
        }

        return [
            'name' => $file['name'],
            'path' => $destPath
        ];
    }

    /**
     * Process image via GD: resize, strip EXIF metadata, and save as WebP.
     */
    private static function processAndSaveImageToWebp(string $sourcePath, string $destPath, ?int $targetWidth, int $quality): void
    {
        // Get image info
        $info = getimagesize($sourcePath);
        if (!$info) {
            throw new \RuntimeException('الملف المرفوع ليس صورة صالحة.');
        }

        $mime = $info['mime'];
        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($sourcePath);
                break;
        }

        if (!$srcImage) {
            throw new \RuntimeException('تعذر قراءة بيانات الصورة.');
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Resize if requested
        if ($targetWidth !== null && $origWidth > $targetWidth) {
            $ratio = $origHeight / $origWidth;
            $targetHeight = (int)round($targetWidth * $ratio);

            $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);
            
            // Retain transparency for PNG/WebP inputs
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);
            
            // Save WebP image
            imagewebp($dstImage, $destPath, $quality);
            imagedestroy($dstImage);
        } else {
            // Save directly to convert format and strip EXIF metadata
            imagewebp($srcImage, $destPath, $quality);
        }

        imagedestroy($srcImage);
    }
}
