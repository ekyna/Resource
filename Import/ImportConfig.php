<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Import;

use Ekyna\Component\Resource\Exception\RuntimeException;

/**
 * Class ImportConfig
 * @package Ekyna\Component\Resource\Import
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ImportConfig
{
    /** @var array<ConsumerInterface> */
    private array   $consumers = [];
    private ?string $path      = null;
    private ?int    $from      = null;
    private ?int    $to        = null;
    private string  $separator = ',';
    private string  $enclosure = '"';

    public function addConsumer(string $name, ConsumerInterface $consumer): ImportConfig
    {
        if (isset($this->consumers[$name])) {
            throw new RuntimeException("Import consumer '$name' is already registered.");
        }

        $this->consumers[$name] = $consumer;

        return $this;
    }

    public function getConsumers(): array
    {
        return $this->consumers;
    }

    /**
     * Returns the file path.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the file path.
     */
    public function setPath(?string $path): ImportConfig
    {
        $this->path = $path;

        return $this;
    }

    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function setFrom(int $from = null): ImportConfig
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): ?int
    {
        return $this->to;
    }

    public function setTo(int $to = null): ImportConfig
    {
        $this->to = $to;

        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setSeparator(string $separator): ImportConfig
    {
        $this->separator = $separator;

        return $this;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    public function setEnclosure(string $enclosure): ImportConfig
    {
        $this->enclosure = $enclosure;

        return $this;
    }
}
