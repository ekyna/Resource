<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Resource\Tests\Config\Cache;

use Acme\Resource\Entity\Foo;
use Ekyna\Component\Resource\Config\Cache\PhpDumper;
use Ekyna\Component\Resource\Exception\UnexpectedValueException;
use PHPUnit\Framework\TestCase;

/**
 * Class PhpDumperTest
 * @package Ekyna\Component\Resource\Tests\Config\Cache
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PhpDumperTest extends TestCase
{
    public function test_dump_withValidData(): void
    {
        $data = [
            'array'  => [
                'array'  => [
                    'array' => [],
                ],
            ],
            'scalar' => [
                'string'  => "ashal'anore",
                'integer' => 10,
                'float'   => 1.23,
                'bool'    => true,
                'null'    => null,
            ],
        ];

        $expected = "<?php return [
'array'=>['array'=>['array'=>[],],],
'scalar'=>['string'=>'ashal\'anore','integer'=>10,'float'=>1.23,'bool'=>true,'null'=>null,],
];";

        $dumper = new PhpDumper();

        self::assertEquals($expected, $dumper->dump($data));
    }

    public function test_dump_withWrongData(): void
    {
        $data = [
            'foo' => new Foo(),
        ];

        $this->expectException(UnexpectedValueException::class);

        $dumper = new PhpDumper();

        $dumper->dump($data);
    }

    public function test_dump_withWrongData2(): void
    {
        $data = [
            'bar' => [
                'bar' => new Foo(),
            ],
        ];

        $this->expectException(UnexpectedValueException::class);

        $dumper = new PhpDumper();

        $dumper->dump($data);
    }
}
