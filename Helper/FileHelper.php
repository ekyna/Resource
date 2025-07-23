<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Helper;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class FileHelper
 * @package Ekyna\Component\Resource\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FileHelper
{
    public static function buildResponse(string $path, array $options = []): BinaryFileResponse
    {
        $options = array_replace([
            'file_name' => null,
            'inline'    => false,
            'mime_type' => null,
        ], $options);

        $options['file_name'] ??= pathinfo($path, PATHINFO_BASENAME);

        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $disposition = $response->headers->makeDisposition(
            $options['inline'] ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $options['file_name']
        );

        $response->headers->set('Content-Disposition', $disposition);

        if (!empty($options['mime_type'])) {
            $response->headers->set('Content-Type', $options['mime_type']);
        }

        return $response;
    }
}
