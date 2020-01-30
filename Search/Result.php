<?php

namespace Ekyna\Component\Resource\Search;

/**
 * Class Result
 * @package Ekyna\Bundle\CmsBundle\Search\Wide
 * @author  ekyna
 */
class Result
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $score;


    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return Result
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the route.
     *
     * @return string
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Sets the route.
     *
     * @param string $route
     *
     * @return Result
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters.
     *
     * @param array $parameters
     *
     * @return Result
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Returns the icon.
     *
     * @return string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Sets the icon.
     *
     * @param string $icon
     *
     * @return Result
     */
    public function setIcon(string $icon = null): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return Result
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the score.
     *
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * Sets the score.
     *
     * @param float $score
     *
     * @return Result
     */
    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }
}
