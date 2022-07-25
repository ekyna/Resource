<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Ekyna\Component\Resource\Exception\PdfException;
use Exception;
use GuzzleHttp\Client;
use Throwable;

/**
 * Class PdfGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PdfGenerator
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $token,
        private readonly int $retry = 3
    ) {
    }

    /**
     * Generates a PDF form the given URL.
     *
     * @throws PdfException
     */
    public function generateFromUrl(string $url, array $options = []): string
    {
        return $this->generate(array_replace($options, ['url' => $url]));
    }

    /**
     * Generates a PDF form the given HTML.
     *
     * @throws PdfException
     */
    public function generateFromHtml(string $html, array $options = []): string
    {
        return $this->generate(array_replace($options, ['html' => $html]));
    }

    /**
     * Generates the PDF.
     *
     * @throws PdfException
     */
    private function generate(array $options): string
    {
        $options = array_replace([
            'url'                 => null,
            'html'                => null,
            'landscape'           => false,
            'printBackground'     => true,
            'displayHeaderFooter' => false,
            'preferCSSPageSize'   => false,
            'unit'                => 'mm',
            'marginTop'           => 6,
            'marginBottom'        => 6,
            'marginLeft'          => 6,
            'marginRight'         => 6,
            'paperWidth'          => 210,
            'paperHeight'         => 297,
            'headerTemplate'      => '',
            'footerTemplate'      => '',
            'scale'               => 1.0,
        ], $options);

        $client = new Client();

        for ($i = 1; $i <= $this->retry; $i++) {
            try {
                $response = $client->request('GET', $this->endpoint, [
                    'json'    => $options,
                    'timeout' => 15,
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->token,
                    ],
                ]);

                if (200 !== $response->getStatusCode()) {
                    throw new Exception();
                }

                return $response->getBody()->getContents();
            } catch (Throwable) {
                if (3 === $i) {
                    throw new PdfException('Failed to generate PDF.');
                }
            }

            sleep($i);
        }

        throw new PdfException('Failed to generate PDF.');
    }
}
