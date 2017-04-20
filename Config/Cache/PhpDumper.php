<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Config\Cache;

use Ekyna\Component\Resource\Exception\UnexpectedValueException;

use function addcslashes;
use function is_array;
use function is_bool;
use function is_null;
use function is_numeric;
use function is_string;
use function sprintf;

/**
 * Class PhpDumper
 * @package Ekyna\Component\Resource\Config\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO PHP8 Type hinting
 */
class PhpDumper
{
    /**
     * Dumps the registry data.
     *
     * @param array $data
     *
     * @return string
     */
    public function dump(array $data): string
    {
        return <<<EOT
<?php return {$this->dumpData($data)};
EOT;
    }

    /**
     * Generates the configuration.
     *
     * @param array $data
     *
     * @return string
     */
    private function dumpData(array $data): string
    {
        $output = "[\n";

        foreach ($data as $key => $config) {
            if (is_array($config)) {
                $value = $this->dumpArray($config);
            } else {
                $value = $this->dumpScalar($config);
            }

            $output .= sprintf("'%s'=>%s,\n", $key, $value);
        }

        $output .= ']';

        return $output;
    }

    /**
     * Dumps the options.
     *
     * @param array $options
     *
     * @return string
     */
    private function dumpArray(array $options): string
    {
        $output = '[';

        foreach ($options as $key => $value) {
            if (!is_numeric($key)) {
                $output .= "'{$key}'=>";
            }

            if (is_array($value)) {
                $output .= $this->dumpArray($value) . ',';
            } else {
                $output .= $this->dumpScalar($value) . ',';
            }
        }

        $output .= ']';

        return $output;
    }

    /**
     * Dumps the scalar value.
     *
     * @param string|int|float|bool|null $value
     *
     * @return string
     */
    private function dumpScalar($value): string
    {
        if (is_numeric($value)) {
            return (string)$value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_string($value)) {
            return "'" . addcslashes($value, "'") . "'";
        }

        throw new UnexpectedValueException('Unexpected data.');
    }
}
