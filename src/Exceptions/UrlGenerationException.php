<?php

declare(strict_types=1);

namespace Marko\Media\Exceptions;

class UrlGenerationException extends MediaException
{
    public static function diskNotPublic(
        string $disk,
        string $path,
    ): self {
        return new self(
            message: "Cannot generate a public URL for media on disk '$disk': the disk is not public",
            context: "Media path '$path' is stored on disk '$disk', which is not configured with 'public' => true",
            suggestion: "Store media on a public disk (set 'disk' in config/media.php, e.g. 'public'), or serve private media through a controller that calls MediaManagerInterface::retrieve()",
        );
    }

    public static function diskHasNoUrl(
        string $disk,
        string $path,
    ): self {
        return new self(
            message: "Cannot generate a public URL for media on disk '$disk': the disk has no 'url' configured",
            context: "Media path '$path' is stored on disk '$disk', which has no 'url' key in config/filesystem.php",
            suggestion: "Add a 'url' key (e.g. '/storage' or 'https://cdn.example.com') to the '$disk' disk in config/filesystem.php",
        );
    }
}
