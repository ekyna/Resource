<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper\File;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XlsWriter;

/**
 * Class Xls
 * @package Ekyna\Component\Resource\Helper\File
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Xls extends AbstractFile
{
    public const EXTENSION = 'xls';
    public const MIME_TYPE = 'application/vnd.ms-excel';

    public const HEADER_BACKGROUND = 'FFE3E3E3';

    public const STYLE_CENTER = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
        ],
    ];

    public const STYLE_BACKGROUND = [
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => self::HEADER_BACKGROUND,
            ],
        ],
    ];

    public const STYLE_BORDER_BOTTOM = [
        'borders' => [
            'bottom' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    public const STYLE_BOLD = [
        'font' => [
            'bold' => true,
        ],
    ];

    public const STYLE_BORDER_LEFT = [
        'borders' => [
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    private Spreadsheet $spreadsheet;
    private int         $row = 0;

    protected static function configure(): array
    {
        return [
            'extension' => self::EXTENSION,
            'mime_type' => self::MIME_TYPE,
        ];
    }

    protected function open(): void
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function setHeaders(array $headers): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $this->row++;

        $col = 0;
        foreach ($headers as $header) {
            $cell = $sheet->getCell([++$col, $this->row]);
            $cell->setValue($header);
            $cell->getStyle()->applyFromArray(
                self::STYLE_BOLD + self::STYLE_BACKGROUND + self::STYLE_BORDER_BOTTOM
            );
        }
    }

    /**
     * Sets the columns widths.
     *
     * ```
     * $xls->setHeaders([
     *     16,
     *     48,
     * ]);
     * ```
     *
     * @param array $widths
     *
     * @return void
     */
    public function setColumnsWidths(array $widths): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $col = 0;
        foreach ($widths as $width) {
            $sheet->getColumnDimensionByColumn(++$col)->setWidth($width, 'mm');
        }
    }

    public function addRow(array $row): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $this->row++;

        $col = 1;
        foreach ($row as $value) {
            $sheet->getCell([$col, $this->row])->setValue($value);
            $col++;
        }
    }

    public function close(): string
    {
        $path = tempnam(sys_get_temp_dir(), $this->name);

        $writer = new XlsWriter($this->spreadsheet);
        $writer->save($path);

        return $path;
    }
}
