<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config;

use Ekyna\Component\Resource\Config\PermissionConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class PermissionConfigTest
 * @package Ekyna\Component\Resource\Tests\Config
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionConfigTest extends TestCase
{
    public function testBasic(): void
    {
        $config = $this->getBasicConfig();

        self::assertEquals('foo', $config->getName());
        self::assertEquals('foo.label', $config->getLabel());
        self::assertNull($config->getTransDomain());
    }

    public function testAdvanced(): void
    {
        $config = $this->getAdvancedConfig();

        self::assertEquals('bar', $config->getName());
        self::assertEquals('bar.label', $config->getLabel());
        self::assertEquals('Acme', $config->getTransDomain());
    }

    private function getBasicConfig(): PermissionConfig
    {
        return new PermissionConfig('foo', [
            'label' => 'foo.label',
        ]);
    }

    private function getAdvancedConfig(): PermissionConfig
    {
        return new PermissionConfig('bar', [
            'label'        => 'bar.label',
            'trans_domain' => 'Acme',
        ]);
    }
}
