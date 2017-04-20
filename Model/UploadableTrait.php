<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Model;

use Behat\Transliterator\Transliterator;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function is_null;

/**
 * Trait UploadableTrait
 * @package Ekyna\Bundle\CoreBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait UploadableTrait
{
    use TimestampableTrait;

    protected ?string $key     = null;
    protected ?File   $file    = null;
    protected int     $size    = 0;
    protected ?string $path    = null;
    protected ?string $oldPath = null;
    protected ?string $rename  = null;
    protected bool    $unlink  = false;


    /**
     * Sets the key.
     *
     * @param string|null $key
     *
     * @return $this|UploadableInterface
     */
    public function setKey(?string $key): UploadableInterface
    {
        $this->key = $key;

        if (!empty($this->key) && !$this->hasRename()) {
            if ($this->hasPath()) {
                $this->rename = pathinfo($this->path, PATHINFO_BASENAME);
            } else {
                $this->rename = pathinfo($this->key, PATHINFO_BASENAME);
            }

            $this->setUpdatedAt(new DateTime());
        }

        return $this;
    }

    /**
     * Returns whether the upload key is set.
     *
     * @return bool
     */
    public function hasKey(): bool
    {
        return !empty($this->key);
    }

    /**
     * Returns the key.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Set file
     *
     * @param File|null $file
     *
     * @return $this|UploadableInterface
     */
    public function setFile(?File $file): UploadableInterface
    {
        $this->file = $file;

        if ($file && !$this->hasRename()) {
            if ($this->hasPath()) {
                $this->rename = pathinfo($this->path, PATHINFO_BASENAME);
            } elseif ($file instanceof UploadedFile) {
                $this->rename = $file->getClientOriginalName();
            } else {
                $this->rename = $file->getBasename();
            }

            $this->setUpdatedAt(new DateTime());
        }

        return $this;
    }

    /**
     * Returns whether the file is set.
     *
     * @return bool
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * Returns the file.
     *
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * Sets the size.
     *
     * @param int $size
     *
     * @return $this|UploadableInterface
     */
    public function setSize(int $size): UploadableInterface
    {
        $this->size = intval($size);

        return $this;
    }

    /**
     * Returns the size.
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return $this|UploadableInterface
     */
    public function setPath(string $path): UploadableInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns whether the path is set.
     *
     * @return bool
     */
    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the old path.
     *
     * @param string|null $oldPath
     *
     * @return $this|UploadableInterface
     */
    public function setOldPath(?string $oldPath): UploadableInterface
    {
        $this->oldPath = $oldPath;

        return $this;
    }

    /**
     * Returns whether the old path is set.
     *
     * @return bool
     */
    public function hasOldPath(): bool
    {
        return null !== $this->oldPath;
    }

    /**
     * Returns the old path.
     *
     * @return string|null
     */
    public function getOldPath(): ?string
    {
        return $this->oldPath;
    }

    /**
     * Returns whether the uploadable should be renamed or not.
     *
     * @return bool
     */
    public function shouldBeRenamed(): bool
    {
        return $this->hasPath() && ($this->guessFilename() !== pathinfo($this->getPath(), PATHINFO_BASENAME));
    }

    /**
     * Guesses the file extension.
     *
     * @return string
     */
    public function guessExtension(): ?string
    {
        if ($this->hasFile()) {
            $extension = $this->file->guessExtension();
        } elseif ($this->hasKey()) {
            $extension = pathinfo($this->getKey(), PATHINFO_EXTENSION);
        } elseif ($this->hasPath()) {
            $extension = pathinfo($this->getPath(), PATHINFO_EXTENSION);
        } else {
            return null;
        }

        return strtolower($extension);
    }

    /**
     * Guesses the file name.
     *
     * @return string|null
     */
    public function guessFilename(): ?string
    {
        // Extension
        if (is_null($extension = $this->guessExtension())) {
            return null;
        }

        // Filename
        $filename = null;
        if ($this->hasRename()) {
            $filename = Transliterator::urlize(pathinfo($this->rename, PATHINFO_FILENAME));
        } elseif ($this->hasFile()) {
            $filename = pathinfo($this->file->getFilename(), PATHINFO_FILENAME);
        } elseif ($this->hasKey()) {
            $filename = pathinfo($this->getKey(), PATHINFO_FILENAME);
        } elseif ($this->hasPath()) {
            $filename = pathinfo($this->path, PATHINFO_FILENAME);
        } else {
            return null;
        }

        return $filename . '.' . $extension;
    }

    /**
     * Returns the filename.
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->guessFilename();
    }

    /**
     * Set rename.
     *
     * @param string $rename
     *
     * @return $this|UploadableInterface
     */
    public function setRename(string $rename): UploadableInterface
    {
        if ($rename !== $this->rename) {
            $this->updatedAt = new DateTime();
        }

        $this->rename = strtolower($rename);

        return $this;
    }

    /**
     * Returns whether the uploadable has a rename or not.
     *
     * @return bool
     */
    public function hasRename(): bool
    {
        return !empty($this->rename);
    }

    /**
     * Returns the new file name
     *
     * @return string|null
     */
    public function getRename(): ?string
    {
        return $this->hasRename() ? $this->rename : $this->guessFilename();
    }

    /**
     * Sets the whether the uploadable should be unlinked from subject.
     *
     * @param bool $unlink
     *
     * @return $this|UploadableInterface
     */
    public function setUnlink(bool $unlink): UploadableInterface
    {
        $this->unlink = $unlink;

        return $this;
    }

    /**
     * Returns whether the uploadable should be unlinked from subject.
     *
     * @return bool
     */
    public function getUnlink(): bool
    {
        return $this->unlink;
    }
}
