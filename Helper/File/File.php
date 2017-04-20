<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper\File;

use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use function clearstatcache;
use function fclose;
use function fopen;
use function pathinfo;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;

use const PATHINFO_BASENAME;

/**
 * Class File
 * @package Ekyna\Component\Resource\Helper\File
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class File
{
    protected static string $extension;
    protected static string $mimeType;

    private string $name;
    private string $path;
    /** @var resource */
    private      $handle;
    private bool $opened = true;

    public static function create(string $name): Csv
    {
        // TODO PHP8 Use str_ends_with()
        if (substr($name, -strlen(static::$extension)) !== static::$extension) {
            throw new UnexpectedValueException(sprintf("File name must ends with '.%s'.", static::$extension));
        }

        if (false === $path = tempnam(sys_get_temp_dir(), $name)) {
            throw new RuntimeException("Failed to create '$name' temporary file.");
        }

        if (false === $handle = fopen($path, 'w')) {
            throw new RuntimeException("Failed to open '$path' for writing.");
        }

        return new static($name, $path, $handle);
    }

    public static function buildResponse(string $path, array $options = []): BinaryFileResponse
    {
        $options = array_replace([
            'file_name' => null,
            'inline'    => false,
            'mime_type' => 'text/plain',
        ], $options);

        $options['file_name'] ??= pathinfo($path, PATHINFO_BASENAME);

        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $disposition = $response->headers->makeDisposition(
            $options['inline'] ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $options['file_name']
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', $options['mime_type']);

        return $response;
    }

    /**
     * @param resource $handle
     */
    private function __construct(string $name, string $path, $handle)
    {
        $this->name = $name;
        $this->path = $path;
        $this->handle = $handle;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): string
    {
        if (!$this->opened) {
            return $this->path;
        }

        if (false === fclose($this->handle)) {
            throw new RuntimeException("Failed to close '$this->path' file");
        }

        $this->opened = false;

        return $this->path;
    }

    public function download(array $options = []): BinaryFileResponse {
        $path = $this->close();

        $options['file_name'] ??= $this->name;
        $options['mime_type'] ??= static::$mimeType;

        return self::buildResponse($path, $options);
    }

    protected function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return resource
     */
    protected function getHandle()
    {
        return $this->handle;
    }
}
