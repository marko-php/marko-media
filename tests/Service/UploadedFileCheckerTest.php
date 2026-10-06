<?php

declare(strict_types=1);

namespace Marko\Media\Tests\Service;

use Marko\Media\Contracts\UploadedFileCheckerInterface;
use Marko\Media\Service\UploadedFileChecker;

it('implements UploadedFileCheckerInterface', function (): void {
    expect(new UploadedFileChecker())->toBeInstanceOf(UploadedFileCheckerInterface::class);
});

it('rejects a local file that PHP upload handling did not receive under a web SAPI', function (string $sapi): void {
    $path = tempnam(sys_get_temp_dir(), 'marko_media_checker_');
    file_put_contents($path, 'not an upload');

    expect(new UploadedFileChecker(sapi: $sapi)->isTrusted($path))->toBeFalse();

    @unlink($path);
})->with(['fpm-fcgi', 'apache2handler', 'cli-server', 'cgi-fcgi', 'frankenphp']);

it('trusts any path under a command-line SAPI', function (string $sapi): void {
    $path = tempnam(sys_get_temp_dir(), 'marko_media_checker_');

    expect(new UploadedFileChecker(sapi: $sapi)->isTrusted($path))->toBeTrue();

    @unlink($path);
})->with(['cli', 'phpdbg', 'embed']);

it('falls back to the running SAPI when none is given', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'marko_media_checker_');

    // The suite runs under the CLI SAPI, where every path is trusted.
    expect(PHP_SAPI)->toBe('cli')
        ->and(new UploadedFileChecker()->isTrusted($path))->toBeTrue();

    @unlink($path);
});
