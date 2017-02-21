<?php

namespace Ekyna\Component\Resource\Bridge\Symfony\Locale;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestLocaleProvider
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RequestLocaleProvider implements LocaleProviderInterface, EventSubscriberInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $availableLocales;


    /**
     * Constructor.
     *
     * @param string $defaultLocale
     * @param array  $availableLocales
     */
    public function __construct($defaultLocale, array $availableLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->availableLocales = $availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // IMPORTANT to keep priority 34.
            KernelEvents::REQUEST => [['onKernelRequest', 34]],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentLocale()
    {
        if (null === $this->request) {
            return $this->getFallbackLocale();
        }
        return $this->request->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLocales()
    {
        return $this->availableLocales;
    }
}
