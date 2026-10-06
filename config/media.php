<?php

declare(strict_types=1);

return [
    // Filesystem disk (config/filesystem.php) uploads are written to. UrlGenerator builds
    // URLs from this disk's 'url', so it must be a public disk for media to be web-reachable.
    'disk' => 'public',
    'max_file_size' => 10485760,
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'mime_extension_map' => [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
    ],
];
