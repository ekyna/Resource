<?php

namespace Ekyna\Component\Resource\Event;

/**
 * Class ResourceMessage
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceMessage
{
    const TYPE_INFO    = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR   = 'danger';

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    /**
     * Constructor.
     *
     * @param $message
     * @param string $type
     */
    public function __construct($message, $type = self::TYPE_INFO)
    {
        $this
            ->setMessage($message)
            ->setType($type)
        ;
    }

    /**
     * Sets the message.
     *
     * @param string $message
     * @return ResourceMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     * @return ResourceMessage
     */
    public function setType($type)
    {
        self::validateType($type);
        $this->type = $type;
        return $this;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Validates the type.
     *
     * @param string $type
     * @throws \InvalidArgumentException
     */
    public static function validateType($type)
    {
        if (!in_array($type, [self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_ERROR])) {
            throw new \InvalidArgumentException('Invalid resource message type "%s".', $type);
        }
    }
}
