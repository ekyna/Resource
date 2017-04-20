<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config;

use Ekyna\Component\Resource\Config\NamespaceConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class NamespaceConfigTest
 * @package Ekyna\Component\Resource\Tests\Config
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NamespaceConfigTest extends TestCase
{
    public function testBasic(): void
    {
        $config = $this->getBasicConfig();

        self::assertEquals('foo', $config->getName());
        self::assertEquals('/foo', $config->getPrefix());
        self::assertEquals('foo.label', $config->getLabel());
        self::assertNull($config->getTransDomain());
    }

    public function testAdvanced(): void
    {
        $config = $this->getAdvancedConfig();

        self::assertEquals('bar', $config->getName());
        self::assertEquals('/bar', $config->getPrefix());
        self::assertEquals('acme.bar.label', $config->getLabel());
        self::assertEquals('Acme', $config->getTransDomain());
    }

    private function getBasicConfig(): NamespaceConfig
    {
        return new NamespaceConfig('foo', [
            'prefix' => '/foo',
        ]);
    }

    private function getAdvancedConfig(): NamespaceConfig
    {
        return new NamespaceConfig('bar', [
            'prefix'       => '/bar',
            'label'        => 'acme.bar.label',
            'trans_domain' => 'Acme',
        ]);
    }
}
