<?php

declare(strict_types=1);

use Marko\Media\Contracts\UploadedFileCheckerInterface;
use Marko\Media\Service\UploadedFileChecker;

return [
    'bindings' => [
        UploadedFileCheckerInterface::class => UploadedFileChecker::class,
    ],
];
