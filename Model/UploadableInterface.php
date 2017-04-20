<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface UploadableInterface
 * @package Ekyna\Component\Resource\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface UploadableInterface extends TimestampableInterface
{
    /**
     * Sets the key.
     *
     * @param string|null $key
     *
     * @return $this|UploadableInterface
     */
    public function setKey(?string $key): UploadableInterface;

    /**
     * Returns whether the upload key is set.
     *
     * @return bool
     */
    public function hasKey(): bool;

    /**
     * Returns the key.
     *
     * @return string|null
     */
    public function getKey(): ?string;

    /**
     * Set file
     *
     * @param File|null $file
     *
     * @return $this|UploadableInterface
     */
    public function setFile(?File $file): UploadableInterface;

    /**
     * Returns whether the file is set.
     *
     * @return bool
     */
    public function hasFile(): bool;

    /**
     * Returns the file.
     *
     * @return File|null
     */
    public function getFile(): ?File;

    /**
     * Sets the size.
     *
     * @param int $size
     *
     * @return $this|UploadableInterface
     */
    public function setSize(int $size): UploadableInterface;

    /**
     * Returns the size.
     *
     * @return int
     */
    public function getSize(): int;

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return $this|UploadableInterface
     */
    public function setPath(string $path): UploadableInterface;

    /**
     * Returns whether the path is set.
     *
     * @return bool
     */
    public function hasPath(): bool;

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): ?string;

    /**
     * Sets the old path.
     *
     * @param string|null $oldPath
     *
     * @return $this|UploadableInterface
     */
    public function setOldPath(?string $oldPath): UploadableInterface;

    /**
     * Returns whether the old path is set.
     *
     * @return bool
     */
    public function hasOldPath(): bool;

    /**
     * Returns the old path.
     *
     * @return string|null
     */
    public function getOldPath(): ?string;

    /**
     * Returns whether the uploadable should be renamed or not.
     *
     * @return bool
     */
    public function shouldBeRenamed(): bool;

    /**
     * Guesses the file extension.
     *
     * @return string
     */
    public function guessExtension(): ?string;

    /**
     * Guesses the file name.
     *
     * @return string|null
     */
    public function guessFilename(): ?string;

    /**
     * Returns the filename.
     *
     * @return string|null
     */
    public function getFilename(): ?string;

    /**
     * Set rename.
     *
     * @param string $rename
     *
     * @return $this|UploadableInterface
     */
    public function setRename(string $rename): UploadableInterface;

    /**
     * Returns whether the uploadable has a rename or not.
     *
     * @return bool
     */
    public function hasRename(): bool;

    /**
     * Returns the new file name
     *
     * @return string|null
     */
    public function getRename(): ?string;

    /**
     * Sets the whether the uploadable should be unlinked from subject.
     *
     * @param bool $unlink
     *
     * @return $this|UploadableInterface
     */
    public function setUnlink(bool $unlink): UploadableInterface;

    /**
     * Returns whether the uploadable should be unlinked from subject.
     *
     * @return bool
     */
    public function getUnlink(): bool;
}
