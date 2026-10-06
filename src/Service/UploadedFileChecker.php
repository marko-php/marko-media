<?php

declare(strict_types=1);

namespace Marko\Media\Service;

use Marko\Media\Contracts\UploadedFileCheckerInterface;

/**
 * Under a web SAPI, only files written by PHP's upload handling (is_uploaded_file())
 * are trusted, so request data cannot point an UploadedFile at an arbitrary local file.
 * Command-line SAPIs have no HTTP upload to verify, so every path is trusted there:
 * the command author is responsible for choosing the source file.
 */
readonly class UploadedFileChecker implements UploadedFileCheckerInterface
{
    private const array NON_WEB_SAPIS = ['cli', 'phpdbg', 'embed'];

    public function __construct(
        private ?string $sapi = null,
    ) {}

    public function isTrusted(
        string $tmpPath,
    ): bool {
        if (in_array($this->sapi ?? PHP_SAPI, self::NON_WEB_SAPIS, true)) {
            return true;
        }

        return is_uploaded_file($tmpPath);
    }
}
