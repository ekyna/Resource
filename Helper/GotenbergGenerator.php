<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Decimal\Decimal;
use DOMDocument;
use Ekyna\Component\Resource\Exception\PdfException;
use Exception;
use GuzzleHttp\Client;
use Throwable;

use function array_replace;
use function fopen;
use function parse_url;
use function pathinfo;
use function preg_replace;
use function sleep;

/**
 * Class GotenbergGenerator
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GotenbergGenerator implements PdfGeneratorInterface
{
    public const NAME = 'gotenberg';

    public function __construct(
        private readonly string $endpoint,
        private readonly string $projectDir,
        private readonly int    $retry = 3
    ) {
    }

    public function generateFromUrl(string $url, array $options = []): string
    {
        return $this->generate('html', [
            [
                'name'     => 'url',
                'contents' => $url,
            ],
        ], $options);
    }

    /**
     * @throws PdfException
     */
    public function generateFromHtml(string $html, array $options = []): string
    {
        $prepared = $this->prepareHtmlForGotenberg($html);

        $multipart = [
            [
                'name'     => 'files',
                'contents' => $prepared['html'],
                'filename' => 'index.html',
            ],
        ];

        foreach ($prepared['files'] as $fileName => $realPath) {
            if (!file_exists($realPath)) {
                throw new PdfException(sprintf('File "%s" does not exist', $realPath));
            }

            $multipart[] = [
                'name'     => 'files',
                'contents' => fopen($realPath, 'r'),
                'filename' => $fileName,
            ];
        }

        return $this->generate('html', $multipart, $options);
    }

    private function generate(string $method, array $multipart, array $parameters): string
    {
        $parameters = array_replace([
            'landscape'         => false,
            'printBackground'   => true,
            //'displayHeaderFooter' => false,
            'preferCSSPageSize' => false,
            //'unit'                => 'mm',
            'marginTop'         => '0.236',
            'marginBottom'      => '0.236',
            'marginLeft'        => '0.236',
            'marginRight'       => '0.236',
            'paperWidth'        => '8.267',
            'paperHeight'       => '11.692',
            //'headerTemplate'      => '',
            //'footerTemplate'      => '',
            'scale'             => '1.0',
        ], $parameters);

        if (isset($parameters['unit'])) {
            $ratio = match ($parameters['unit']) {
                'mm' => new Decimal((string)(1 / 25.4)),
            };

            foreach (
                [
                    'marginTop',
                    'marginBottom',
                    'marginLeft',
                    'marginRight',
                    'paperWidth',
                    'paperHeight',
                ] as $key
            ) {
                if (!isset($parameters[$key]) || 0 == $parameters[$key]) {
                    continue;
                }

                $parameters[$key] = (new Decimal((string)$parameters[$key]))->mul($ratio)->toFixed(3);
            }
        }

        array_push(
            $multipart,
            ...array_map(
                fn(string $key, mixed $value) => ['name' => $key, 'contents' => $value],
                array_keys($parameters),
                array_values($parameters)
            )
        );

        $client = new Client();

        for ($i = 1; $i <= $this->retry; $i++) {
            try {
                $response = $client->request('POST', $this->endpoint . 'forms/chromium/convert/' . $method, [
                    'multipart' => $multipart,
                ]);

                if (200 !== $response->getStatusCode()) {
                    throw new Exception();
                }

                return $response->getBody()->getContents();
            } catch (Throwable $e) {
                if (3 === $i) {
                    throw new PdfException('Failed to generate PDF.', 0, $e);
                }
            }

            sleep(1);
        }

        throw new PdfException('Failed to generate PDF.');
    }

    private function prepareHtmlForGotenberg(string $html): array
    {
        $dom = new DOMDocument();
        // On charge le HTML en ignorant les erreurs de syntaxe
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $files = [];
        $counter = 0;

        // TODO Refactor

        $publicDir = $this->projectDir . '/public';

        $links = $dom->getElementsByTagName('link');
        foreach ($links as $link) {
            if ('stylesheet' !== $link->getAttribute('rel')) {
                continue;
            }

            $parsed = parse_url($link->getAttribute('href'));

            $cleanPath = preg_replace('~^/v[0-9]{3}~', '', $parsed['path']);
            $pathInfo = pathinfo($cleanPath);

            // On génère un nom unique pour Gotenberg
            $newName = "file_" . $counter . "." . $pathInfo['extension'];

            // On stocke le chemin RÉEL sur le disque pour Guzzle
            // On suppose que $publicPath est le chemin vers votre dossier /public
            $files[$newName] = $publicDir . $cleanPath;

            // On met à jour le HTML
            $link->setAttribute('href', $newName);

            $counter++;
        }

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $image) {
            $source = $image->getAttribute('src');

            if (preg_match('~^data:image/(png|jpe?g|gif|webp|svg);base64,~', $source)) {
                continue;
            }

            $parsed = parse_url($source);
            $cleanPath = preg_replace('~^/v[0-9]{3}~', '', $parsed['path']);
            $pathInfo = pathinfo($cleanPath);

            // On génère un nom unique pour Gotenberg
            $newName = "file_" . $counter . "." . $pathInfo['extension'];

            // On stocke le chemin RÉEL sur le disque pour Guzzle
            // On suppose que $publicPath est le chemin vers votre dossier /public
            $files[$newName] = $publicDir . $cleanPath;

            // On met à jour le HTML
            $image->setAttribute('src', $newName);

            $counter++;
        }

        return [
            'html'  => $dom->saveHTML(),
            'files' => $files
        ];
    }
}
