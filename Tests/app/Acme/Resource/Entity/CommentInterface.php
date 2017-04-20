<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\ResourceInterface;

interface CommentInterface extends ResourceInterface
{
    public function getPost(): ?PostInterface;

    public function setPost(PostInterface $post): CommentInterface;

    public function getMessage(): ?string;

    public function setMessage(string $message): CommentInterface;
}