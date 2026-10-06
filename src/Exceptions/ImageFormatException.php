<?php

declare(strict_types=1);

namespace Marko\Media\Exceptions;

class ImageFormatException extends MediaException
{
    public static function unsafePath(
        string $path,
        string $reason,
    ): self {
        return new self(
            message: "Unsafe image path: $reason",
            context: 'Attempted to inspect image at path: ' . str_replace("\0", '\0', $path),
            suggestion: 'Pass an absolute or relative path to a local file. Image paths must not start with an '
                . 'ImageMagick coder prefix (e.g. "msl:", "url:", "PNG:") or a PHP stream wrapper (e.g. "phar://")',
        );
    }

    public static function notReadable(
        string $path,
    ): self {
        return new self(
            message: "Image path '$path' is not a readable file",
            context: "Attempted to inspect image at path: $path",
            suggestion: 'Verify the file exists, is a regular file (not a directory) and is readable by PHP',
        );
    }

    public static function unrecognisedFormat(
        string $path,
    ): self {
        return new self(
            message: "File '$path' is not a recognised raster image",
            context: "Attempted to detect the image format of: $path",
            suggestion: 'Only JPEG, PNG, GIF, WebP, AVIF, HEIC, TIFF and BMP files are detected. Vector and '
                . 'script formats such as SVG, MVG, MSL, PostScript and PDF are never passed to an image decoder',
        );
    }
}
