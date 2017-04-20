<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslation;

class PostTranslation extends AbstractTranslation implements PostTranslationInterface
{
    private $title;
    private $content;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): PostTranslationInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): PostTranslationInterface
    {
        $this->content = $content;

        return $this;
    }
}
