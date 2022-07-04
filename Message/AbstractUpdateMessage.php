<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Message;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class AbstractUpdateMessage
 * @package Ekyna\Component\Resource\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractUpdateMessage
{
    protected readonly int   $id;
    protected readonly array $data;

    public function __construct(
        ResourceInterface|int $resource,
        protected array       $changeSet,
    ) {
        $this->id = $resource instanceof ResourceInterface ? $resource->getId() : $resource;
        $this->data = $this->buildData($changeSet);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function hasChanged(string $property): bool
    {
        return $this->getFrom($property) !== $this->getTo($property);
    }

    public function getFrom(string $property): string|int|bool|null
    {
        return $this->data[$property][0] ?? null;
    }

    public function getTo(string $property): string|int|bool|null
    {
        return $this->data[$property][1] ?? null;
    }

    protected function buildData(array $changeSet): array
    {
        $data = [];

        foreach (static::getDefaults() as $key => $value) {
            $data[$key] = [
                $changeSet[$key][0] ?? $value,
                $changeSet[$key][1] ?? $value,
            ];
        }

        return $data;
    }

    abstract protected static function getDefaults(): array;
}
