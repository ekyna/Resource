<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface TreeInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface TreeInterface extends ResourceInterface
{
    /**
     * Sets the left bound.
     *
     * @param int $left
     *
     * @return $this|TreeInterface
     */
    public function setLeft(int $left): TreeInterface;

    /**
     * Returns the left bound.
     *
     * @return int
     */
    public function getLeft(): int;

    /**
     * Sets the right bound.
     *
     * @param int $right
     *
     * @return $this|TreeInterface
     */
    public function setRight(int $right): TreeInterface;

    /**
     * Returns the right bound.
     *
     * @return int
     */
    public function getRight(): int;

    /**
     * Sets the root.
     *
     * @param int|null $root
     *
     * @return $this|TreeInterface
     */
    public function setRoot(int $root = null): TreeInterface;

    /**
     * Returns the root.
     *
     * @return int|null
     */
    public function getRoot(): ?int;

    /**
     * Sets the level.
     *
     * @param int $level
     *
     * @return $this|TreeInterface
     */
    public function setLevel(int $level): TreeInterface;

    /**
     * Returns the level.
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * Sets the parent tree node.
     *
     * @param TreeInterface|null $parent
     *
     * @return $this|TreeInterface
     */
    public function setParent(?TreeInterface $parent): TreeInterface;

    /**
     * Returns the parent tree node.
     *
     * @return $this|TreeInterface
     */
    public function getParent(): ?TreeInterface;

    /**
     * Returns whether this tree node has the given child.
     *
     * @param TreeInterface $child
     *
     * @return bool
     */
    public function hasChild(TreeInterface $child): bool;

    /**
     * Adds the given child node.
     *
     * @param TreeInterface $child
     *
     * @return $this|TreeInterface
     */
    public function addChild(TreeInterface $child): TreeInterface;

    /**
     * Removes the given child node.
     *
     * @param TreeInterface $child
     *
     * @return $this|TreeInterface
     */
    public function removeChild(TreeInterface $child): TreeInterface;

    /**
     * Returns whether this node has children.
     *
     * @return bool
     */
    public function hasChildren(): bool;

    /**
     * Returns the children.
     *
     * @return Collection|TreeInterface[]
     */
    public function getChildren(): Collection;
}
