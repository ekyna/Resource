<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Ekyna\Component\Resource\Exception\PdfException;

/**
 * Interface PdfGeneratorInterface
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PdfGeneratorInterface
{
    /**
     * Generates a PDF form the given URL.
     *
     * @throws PdfException
     */
    public function generateFromUrl(string $url, array $options = []): string;

    /**
     * Generates a PDF form the given HTML.
     *
     * @throws PdfException
     */
    public function generateFromHtml(string $html, array $options = []): string;
}
