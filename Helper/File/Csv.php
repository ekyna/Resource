<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper\File;

use Ekyna\Component\Resource\Exception\RuntimeException;

use function fclose;
use function fopen;
use function fputcsv;
use function sys_get_temp_dir;
use function tempnam;

/**
 * Class Csv
 * @package Ekyna\Component\Resource\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Csv extends AbstractFile
{
    public const EXTENSION = 'csv';
    public const MIME_TYPE = 'text/csv';

    private string $path;
    private mixed  $handle;
    private bool   $opened = false;

    protected static function configure(): array
    {
        return [
            'extension' => self::EXTENSION,
            'mime_type' => self::MIME_TYPE,
        ];
    }

    /**
     * Constructor.
     */
    public function __construct(
        string $name,
        array  $options = []
    ) {
        parent::__construct($name, array_replace([
            'separator' => ',',
            'enclosure' => '"',
        ], $options));
    }

    protected function open(): void
    {
        if (false === $this->path = tempnam(sys_get_temp_dir(), $this->name)) {
            throw new RuntimeException("Failed to create '$this->name' temporary file.");
        }

        if (false === $this->handle = fopen($this->path, 'r+')) {
            throw new RuntimeException("Failed to open '$this->path' for writing.");
        }

        $this->opened = true;
    }

    public function setHeaders(array $headers): void
    {
        $this->addRow($headers);
    }

    public function addRow(array $row): void
    {
        if (false !== fputcsv($this->handle, $row, $this->options['separator'], $this->options['enclosure'])) {
            return;
        }

        throw new RuntimeException("Failed to write into '$this->path' file.");
    }

    public function close(): string
    {
        if (!$this->opened) {
            return $this->path;
        }

        if (false === fclose($this->handle)) {
            throw new RuntimeException("Failed to close '$this->path' file");
        }

        $this->handle = null;
        $this->opened = false;

        return $this->path;
    }
}
