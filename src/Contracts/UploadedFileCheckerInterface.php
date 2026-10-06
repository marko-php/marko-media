<?php

declare(strict_types=1);

namespace Marko\Media\Contracts;

/**
 * Decides whether a temporary file path may be imported by MediaManager::upload().
 */
interface UploadedFileCheckerInterface
{
    /**
     * Whether the path is a trusted upload source: a file PHP's upload handling
     * placed there under a web SAPI, or any path when running from the command line.
     */
    public function isTrusted(
        string $tmpPath,
    ): bool;
}
