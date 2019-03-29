<?php

namespace Ekyna\Component\Resource\Bridge\Symfony\Locale;

use Ekyna\Component\Resource\Locale\LocaleProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestLocaleProvider
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RequestLocaleProvider extends LocaleProvider implements EventSubscriberInterface
{
    /**
     * @var Request
     */
    private $request;


    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getCurrentLocale()
    {
        if ($this->currentLocale) {
            return $this->currentLocale;
        }

        if ($this->request) {
            return $this->currentLocale = $this->request->getLocale();
        }

        return $this->getFallbackLocale();
    }
}
