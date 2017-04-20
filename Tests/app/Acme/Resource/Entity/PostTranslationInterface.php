<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\TranslationInterface;

interface PostTranslationInterface extends TranslationInterface
{
    public function getTitle(): ?string;

    public function setTitle(string $title): PostTranslationInterface;
    
    public function getContent(): ?string;

    public function setContent(string $content): PostTranslationInterface;
}
