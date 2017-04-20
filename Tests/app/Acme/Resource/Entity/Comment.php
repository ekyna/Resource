<?php

namespace Acme\Resource\Entity;

class Comment implements CommentInterface
{
    /** @var int */
    private $id;

    /** @var PostInterface */
    private $post;

    /** @var string */
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPost(): ?PostInterface
    {
        return $this->post;
    }

    public function setPost(PostInterface $post): CommentInterface
    {
        $this->post = $post;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): CommentInterface
    {
        $this->message = $message;

        return $this;
    }
}
