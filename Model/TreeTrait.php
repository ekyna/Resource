<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

/**
 * Trait TreeTrait
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait TreeTrait
{
    protected int            $left   = 0;
    protected int            $right  = 0;
    protected ?int           $root   = null;
    protected int            $level  = 0;
    protected ?TreeInterface $parent = null;
    /** @var Collection|TreeInterface[] */
    protected Collection $children;


    /**
     * Initializes this tree node.
     */
    protected function initializeNode(): void
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Sets the left bound.
     *
     * @param int $left
     *
     * @return $this|TreeInterface
     */
    public function setLeft(int $left): TreeInterface
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Returns the left bound.
     *
     * @return int
     */
    public function getLeft(): int
    {
        return $this->left;
    }

    /**
     * Sets the right bound.
     *
     * @param int $right
     *
     * @return $this|TreeInterface
     */
    public function setRight(int $right): TreeInterface
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Returns the right bound.
     *
     * @return int
     */
    public function getRight(): int
    {
        return $this->right;
    }

    /**
     * Sets the root.
     *
     * @param int|null $root
     *
     * @return $this|TreeInterface
     */
    public function setRoot(int $root = null): TreeInterface
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Returns the root.
     *
     * @return int|null
     */
    public function getRoot(): ?int
    {
        return $this->root;
    }

    /**
     * Sets the level.
     *
     * @param int $level
     *
     * @return $this|TreeInterface
     */
    public function setLevel(int $level): TreeInterface
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Returns the level.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Sets the parent tree node.
     *
     * @param TreeInterface|null $parent
     *
     * @return $this|TreeInterface
     */
    public function setParent(?TreeInterface $parent): TreeInterface
    {
        $parent && $this->supportNode($parent);

        if ($parent !== $this->parent) {
            if ($previous = $this->parent) {
                $this->parent = null;
                /** @noinspection PhpParamsInspection */
                $previous->removeChild($this);
            }

            if ($this->parent = $parent) {
                /** @noinspection PhpParamsInspection */
                $this->parent->addChild($this);
            }
        }

        return $this;
    }

    /**
     * Returns the parent tree node.
     *
     * @return static|TreeInterface
     */
    public function getParent(): ?TreeInterface
    {
        return $this->parent;
    }

    /**
     * Returns whether the tree node has the child or not.
     *
     * @param TreeInterface $child
     *
     * @return bool
     */
    public function hasChild(TreeInterface $child): bool
    {
        return $this->children->contains($child);
    }

    /**
     * Add children
     *
     * @param TreeInterface $child
     *
     * @return $this|TreeInterface
     */
    public function addChild(TreeInterface $child): TreeInterface
    {
        $this->supportNode($child);

        if (!$this->hasChild($child)) {
            $this->children->add($child);
            /** @noinspection PhpParamsInspection */
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Remove children
     *
     * @param TreeInterface $child
     *
     * @return $this|TreeInterface
     */
    public function removeChild(TreeInterface $child): TreeInterface
    {
        if ($this->hasChild($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * Has children
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return 0 < $this->children->count();
    }

    /**
     * Get children
     *
     * @return Collection|static[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * Check whether the node is of the same class.
     *
     * @param TreeInterface $node
     */
    protected function supportNode(TreeInterface $node): void
    {
        if (!$node instanceof static) {
            throw new UnexpectedTypeException($node, static::class);
        }
    }
}
