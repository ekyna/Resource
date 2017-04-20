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
    private string $endpoint;
    private string $token;
    private int    $retry;

    public function __construct(string $endpoint, string $token, int $retry = 3)
    {
        $this->endpoint = $endpoint;
        $this->token = $token;
        $this->retry = $retry;
    }

    /**
     * Generates a PDF form the given URL.
     *
     * @param string $url
     * @param array  $options
     *
     * @return string
     *
     * @throws PdfException
     */
    public function generateFromUrl(string $url, array $options = []): string
    {
        return $this->generate(array_replace($options, ['headers' => []], ['url' => $url]));
    }

    /**
     * Generates a PDF form the given HTML.
     *
     * @param string $html
     * @param array  $options
     *
     * @return string
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
     * @param array $options
     *
     * @return string
     *
     * @throws PdfException
     */
    private function generate(array $options): string
    {
        $options = array_replace_recursive([
            'orientation' => 'portrait',
            'format'      => 'A4',
            'paper'       => [
                'width'  => null,
                'height' => null,
                'unit'   => 'in',
            ],
            'margins'     => [
                'top'    => 6,
                'right'  => 6,
                'bottom' => 6,
                'left'   => 6,
                'unit'   => 'mm',
            ],
            'header'      => null,
            'footer'      => null,
        ], $options);

        $client = new Client();

        for ($i = 1; $i <= $this->retry; $i++) {
            try {
                $response = $client->request('GET', $this->endpoint, [
                    'json'    => $options,
                    'timeout' => 30,
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->token,
                    ],
                ]);

                if (200 !== $response->getStatusCode()) {
                    throw new Exception();
                }

                return $response->getBody()->getContents();
            } catch (Throwable $e) {
                if (3 == $i) {
                    throw new PdfException('Failed to generate PDF.');
                }
            }

            sleep($i);
        }

        throw new PdfException('Failed to generate PDF.');
    }
}
