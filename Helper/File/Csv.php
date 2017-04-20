<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper\File;

use Ekyna\Component\Resource\Exception\RuntimeException;

use function fputcsv;

/**
 * Class Csv
 * @package Ekyna\Component\Resource\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Csv extends File
{
    protected static string $extension = 'csv';
    protected static string $mimeType = 'text/csv';

    private string $separator = ',';
    private string $enclosure = '"';

    public function addRows(array $rows): void
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function addRow(array $row): void
    {
        if (false !== fputcsv($this->getHandle(), $row, $this->separator, $this->enclosure)) {
            return;
        }

        throw new RuntimeException("Failed to write into '{$this->getPath()}' file.");
    }
}
