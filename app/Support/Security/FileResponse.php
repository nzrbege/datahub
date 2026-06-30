<?php

namespace App\Support\Security;

use Symfony\Component\HttpFoundation\HeaderUtils;

class FileResponse
{
    public static function safeFilename(?string $filename, string $fallback = 'download'): string
    {
        $name = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $filename));
        $name = preg_replace('/[\x00-\x1F\x7F]+/', '', $name) ?? '';
        $name = trim($name, " \t\n\r\0\x0B.\\/");

        if ($name === '') {
            $name = $fallback;
        }

        return mb_substr($name, 0, 180);
    }

    public static function inlineDisposition(?string $filename, string $fallback = 'dokumen.pdf'): string
    {
        $safeFilename = self::safeFilename($filename, $fallback);
        $asciiFallback = preg_replace('/[^A-Za-z0-9._-]+/', '-', $safeFilename) ?: $fallback;

        return HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $safeFilename,
            self::safeFilename($asciiFallback, $fallback)
        );
    }
}
