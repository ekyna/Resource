<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Event;

use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;

/**
 * Class ResourceMessage
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class ResourceMessage implements TranslatableInterface
{
    public const TYPE_INFO    = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR   = 'danger';

    private string $type;
    private string $message;
    private array $parameters = [];
    private ?string $domain = null;


    /**
     * Creates a new resource message.
     *
     * @param string $content
     * @param string $type
     *
     * @return ResourceMessage
     */
    public static function create(string $content, string $type = self::TYPE_INFO): ResourceMessage
    {
        return new self($content, $type);
    }

    /**
     * Validates the type.
     *
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    public static function validateType(string $type): void
    {
        if (in_array($type, [self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_WARNING, self::TYPE_ERROR], true)) {
            return;
        }

        throw new InvalidArgumentException("Invalid resource message type '$type'.");
    }

    /**
     * Constructor.
     *
     * @param string $content
     * @param string $type
     */
    public function __construct(string $content, string $type)
    {
        self::validateType($type);

        $this->message = $content;
        $this->type = $type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return ResourceMessage
     */
    public function setType(string $type): ResourceMessage
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the message.
     *
     * @param string $message
     *
     * @return ResourceMessage
     */
    public function setMessage(string $message): ResourceMessage
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the translation domain.
     *
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Sets the translation domain.
     *
     * @param string|null $domain
     *
     * @return ResourceMessage
     */
    public function setDomain(?string $domain): ResourceMessage
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Adds the message parameter.
     *
     * @param string $key
     * @param string $value
     *
     * @return ResourceMessage
     */
    public function addParameter(string $key, string $value): ResourceMessage
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Sets the message parameters.
     *
     * @param array $parameters
     *
     * @return ResourceMessage
     */
    public function setParameters(array $parameters = []): ResourceMessage
    {
        $this->parameters = [];

        foreach ($parameters as $key => $value) {
            $this->addParameter($key, $value);
        }

        return $this;
    }

    /**
     * Returns the message parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans($this->message, $this->parameters, $this->domain);
    }
}
