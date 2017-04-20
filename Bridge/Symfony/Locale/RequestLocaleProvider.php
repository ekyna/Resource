<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Bridge\Symfony\Locale;

use Ekyna\Component\Resource\Locale\LocaleProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestLocaleProvider
 * @package Ekyna\Bundle\CoreBundle\Locale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RequestLocaleProvider extends LocaleProvider implements EventSubscriberInterface
{
    private ?Request $request = null;


    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // IMPORTANT to keep priority 34.
            KernelEvents::REQUEST => [['onKernelRequest', 34]],
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $this->request = $event->getRequest();
    }

    /**
     * @inheritDoc
     */
    public function getCurrentLocale(): string
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
