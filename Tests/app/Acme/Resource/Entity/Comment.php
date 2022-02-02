<?php

namespace Acme\Resource\Entity;

use Ekyna\Component\Resource\Model\AbstractResource;

class Comment extends AbstractResource implements CommentInterface
{
    /** @var PostInterface */
    private $post;

    /** @var string */
    private $message;

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
