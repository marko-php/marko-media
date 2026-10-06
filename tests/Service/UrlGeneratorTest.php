<?php

declare(strict_types=1);

namespace Marko\Media\Tests\Service;

use Marko\Filesystem\Config\FilesystemConfig;
use Marko\Filesystem\Exceptions\FilesystemException;
use Marko\Media\Entity\Media;
use Marko\Media\Exceptions\UrlGenerationException;
use Marko\Media\Service\UrlGenerator;
use Marko\Testing\Fake\FakeConfigRepository;

/**
 * @param array<string, array<string, mixed>>|null $disks
 */
function makeUrlGeneratorFilesystemConfig(
    ?array $disks = null,
): FilesystemConfig {
    // Mirrors the shipped config/filesystem.php disks
    $disks ??= [
        'local' => ['driver' => 'local', 'path' => 'storage', 'public' => false],
        'public' => ['driver' => 'local', 'path' => 'storage/public', 'public' => true, 'url' => '/storage'],
    ];

    return new FilesystemConfig(new FakeConfigRepository([
        'filesystem.default' => 'local',
        'filesystem.disks' => $disks,
    ]));
}

function makeMedia(
    string $path = '2024/01/abc123.jpg',
    string $disk = 'public',
): Media {
    $media = new Media();
    $media->id = 1;
    $media->filename = 'abc123.jpg';
    $media->originalFilename = 'photo.jpg';
    $media->mimeType = 'image/jpeg';
    $media->size = 1024;
    $media->disk = $disk;
    $media->path = $path;

    return $media;
}

it('generates the public URL from the url of the disk the media is stored on', function (): void {
    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig());

    expect($generator->url(makeMedia(path: '2024/01/abc123.jpg')))
        ->toBe('/storage/2024/01/abc123.jpg');
});

it('generates URL based on the media disk configuration', function (): void {
    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig([
        'public' => ['driver' => 'local', 'public' => true, 'url' => '/storage'],
        's3' => ['driver' => 's3', 'public' => true, 'url' => 'https://s3.amazonaws.com/bucket/'],
    ]));

    expect($generator->url(makeMedia(path: '2024/01/abc123.jpg', disk: 's3')))
        ->toBe('https://s3.amazonaws.com/bucket/2024/01/abc123.jpg');
});

it('refuses to generate a public URL for media stored on a private disk', function (): void {
    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig());

    expect(fn () => $generator->url(makeMedia(disk: 'local')))
        ->toThrow(UrlGenerationException::class, "disk 'local': the disk is not public");
});

it('throws when the public disk has no url configured', function (): void {
    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig([
        'public' => ['driver' => 'local', 'public' => true],
    ]));

    expect(fn () => $generator->url(makeMedia()))
        ->toThrow(UrlGenerationException::class, "has no 'url' configured");
});

it('throws when the media disk is not configured', function (): void {
    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig());

    expect(fn () => $generator->url(makeMedia(disk: 'missing')))
        ->toThrow(FilesystemException::class, "Disk 'missing' is not configured");
});

it('generates a URL that points at the shipped public disk the media manager writes to by default', function (): void {
    $mediaConfig = require dirname(__DIR__, 2) . '/config/media.php';
    $filesystemConfig = require dirname(__DIR__, 4) . '/packages/filesystem/config/filesystem.php';

    $generator = new UrlGenerator(makeUrlGeneratorFilesystemConfig($filesystemConfig['disks']));

    expect($generator->url(makeMedia(path: '2024/01/abc123.jpg', disk: $mediaConfig['disk'])))
        ->toBe('/storage/2024/01/abc123.jpg')
        ->and($filesystemConfig['disks'][$mediaConfig['disk']]['path'])->toBe('storage/public');
});
