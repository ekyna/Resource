<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Search;

/**
 * Class Result
 * @package Ekyna\Bundle\CmsBundle\Search\Wide
 * @author  ekyna
 */
final class Result
{
    private ?string $title       = null;
    private ?string $action      = null;
    private ?string $route       = null;
    private array   $parameters  = [];
    private ?string $icon        = null;
    private ?string $description = null;
    private float   $score       = 0;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getScore(): float
    {
        return $this->score;
    }
}
