<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper\File;

use Ekyna\Component\Resource\Helper\FileHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function pathinfo;

use const PATHINFO_FILENAME;

/**
 * Class File
 * @package Ekyna\Component\Resource\Helper\File
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFile
{
    /**
     * @deprecated Use constructor
     * @TODO       Remove
     */
    public static function create(string $name, array $options = []): static
    {
        return new static(pathinfo($name, PATHINFO_FILENAME), $options);
    }

    /**
     * @return array{extension: string, mime_type: string}
     */
    abstract protected static function configure(): array;

    public function __construct(
        protected readonly string $name,
        protected readonly array  $options = []
    ) {
        $this->open();
    }

    public function __destruct()
    {
        $this->close();
    }

    protected function open(): void
    {
    }

    /**
     * Sets the columns headers.
     *
     * ```
     * $xls->setHeaders([
     *     'Reference',
     *     'Designation',
     * ]);
     * ```
     *
     * @param array $headers
     *
     * @return void
     */
    abstract public function setHeaders(array $headers): void;

    /**
     * Adds the row.
     *
     * ```
     *  $xls->addRow([
     *      '12345',
     *      'Some product',
     *  ]);
     *  ```
     *
     * @param array<int, string> $row
     *
     * @return void
     */
    abstract public function addRow(array $row): void;

    public function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     * Closes the file and return its path.
     *
     * @return string
     */
    abstract public function close(): string;

    public function download(array $options = []): BinaryFileResponse
    {
        $path = $this->close();

        $options['file_name'] ??= $this->getFilename();
        $options['mime_type'] ??= $this->getMimeType();

        return FileHelper::buildResponse($path, $options);
    }

    public function getFilename(): string
    {
        return $this->name . '.' . static::configure()['extension'];
    }

    public function getMimeType(): string
    {
        return static::configure()['mime_type'];
    }
}
