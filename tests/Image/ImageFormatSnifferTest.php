<?php

declare(strict_types=1);

namespace Marko\Media\Tests\Image;

use Marko\Media\Exceptions\ImageFormatException;
use Marko\Media\Exceptions\MediaException;
use Marko\Media\Image\ImageFormatSniffer;

function writeSnifferFixture(
    string $bytes,
): string {
    $path = sys_get_temp_dir() . '/' . uniqid('marko_sniff_', true);
    file_put_contents($path, $bytes);

    return $path;
}

describe('ImageFormatSniffer', function (): void {
    it('detects raster formats from their magic bytes rather than the file extension', function (
        string $bytes,
        string $expected,
    ): void {
        $path = writeSnifferFixture($bytes);

        try {
            expect((new ImageFormatSniffer())->sniff($path))->toBe($expected);
        } finally {
            unlink($path);
        }
    })->with([
        'JPEG' => ["\xFF\xD8\xFF\xE0\x00\x10JFIF\x00", 'JPEG'],
        'PNG' => ["\x89PNG\r\n\x1A\n\x00\x00\x00\x0DIHDR", 'PNG'],
        'GIF87a' => ['GIF87a' . str_repeat("\x00", 8), 'GIF'],
        'GIF89a' => ['GIF89a' . str_repeat("\x00", 8), 'GIF'],
        'WEBP' => ["RIFF\x24\x00\x00\x00WEBPVP8 ", 'WEBP'],
        'AVIF' => ["\x00\x00\x00\x1Cftypavif\x00\x00\x00\x00avifmif1miaf", 'AVIF'],
        'AVIF via compatible brand' => ["\x00\x00\x00\x18ftypmif1\x00\x00\x00\x00avifmiaf", 'AVIF'],
        'HEIC' => ["\x00\x00\x00\x18ftypheic\x00\x00\x00\x00mif1heic", 'HEIC'],
        'TIFF little-endian' => ["II*\x00\x08\x00\x00\x00", 'TIFF'],
        'TIFF big-endian' => ["MM\x00*\x00\x00\x00\x08", 'TIFF'],
        'BMP' => ["BM\x46\x00\x00\x00\x00\x00\x00\x00\x36\x00\x00\x00", 'BMP'],
    ]);

    it('refuses to identify SVG, MVG, PostScript and other non-raster content', function (string $bytes): void {
        $path = writeSnifferFixture($bytes);

        try {
            expect(fn () => (new ImageFormatSniffer())->sniff($path))
                ->toThrow(ImageFormatException::class, 'not a recognised raster image');
        } finally {
            unlink($path);
        }
    })->with([
        'SVG' => ['<svg xmlns="http://www.w3.org/2000/svg"><image xlink:href="https://internal/"/></svg>'],
        'MVG' => ["push graphic-context\nviewbox 0 0 640 480\nimage over 0,0 0,0 'https://internal/'\n"],
        'PostScript' => ["%!PS-Adobe-3.0 EPSF-3.0\n"],
        'PDF' => ["%PDF-1.7\n"],
        'MSL' => ['<?xml version="1.0"?><image><read filename="/etc/passwd"/></image>'],
        'ftyp box with an unknown brand' => ["\x00\x00\x00\x14ftypisom\x00\x00\x00\x00mp41"],
        'empty file' => [''],
    ]);

    it('rejects paths carrying an ImageMagick coder prefix or a PHP stream wrapper', function (string $path): void {
        expect(fn () => (new ImageFormatSniffer())->sniff($path))
            ->toThrow(ImageFormatException::class, 'coder prefix or stream wrapper');
    })->with([
        'msl coder' => ['msl:/tmp/x'],
        'url coder' => ['url:http://internal/x.png'],
        'uppercase coder' => ['PNG:/tmp/x.png'],
        'text coder' => ['text:/etc/passwd'],
        'http wrapper' => ['http://internal/x.png'],
        'phar wrapper' => ['phar:///tmp/x.phar/x.png'],
        'data wrapper' => ['data:image/png;base64,AAAA'],
    ]);

    it('rejects a path that does not point to a readable regular file', function (): void {
        $missing = sys_get_temp_dir() . '/' . uniqid('marko_missing_', true) . '.png';

        expect(fn () => (new ImageFormatSniffer())->sniff($missing))
            ->toThrow(ImageFormatException::class, 'not a readable file')
            ->and(fn () => (new ImageFormatSniffer())->sniff(sys_get_temp_dir()))
            ->toThrow(ImageFormatException::class, 'not a readable file');
    });

    it('rejects empty paths and paths containing NUL bytes', function (string $path): void {
        expect(fn () => (new ImageFormatSniffer())->sniff($path))
            ->toThrow(ImageFormatException::class, 'Unsafe image path');
    })->with([
        'empty' => [''],
        'NUL byte' => ["/tmp/x.png\0.svg"],
    ]);

    it('throws exceptions that are MediaExceptions with context and a suggestion', function (): void {
        $exception = ImageFormatException::unrecognisedFormat('/tmp/x.svg');

        expect($exception)->toBeInstanceOf(MediaException::class)
            ->and($exception->getContext())->toContain('/tmp/x.svg')
            ->and($exception->getSuggestion())->not->toBeEmpty();
    });
});
