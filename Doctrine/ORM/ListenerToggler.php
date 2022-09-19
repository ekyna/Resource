<?php

declare(strict_types=1);

namespace Ekyna\Component\Resource\Doctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;

use function array_keys;

/**
 * Class ListenerToggler
 * @package Ekyna\Component\Resource\Doctrine\ORM
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ListenerToggler
{
    /** @var array<object> */
    private array         $disabledListeners = [];
    private ?EventManager $eventManager      = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function disable(string $class): void
    {
        if (isset($this->disabledListeners[$class])) {
            return;
        }

        foreach ($this->getEventManager()->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (!$listener instanceof $class) {
                    continue;
                }

                $this->getEventManager()->removeEventListener($eventName, $listener);

                if (!isset($this->disabledListeners[$class])) {
                    $this->disabledListeners[$class] = [];
                }

                $this->disabledListeners[$class][$eventName] = $listener;
            }
        }
    }

    public function enable(string $class): void
    {
        if (!isset($this->disabledListeners[$class])) {
            return;
        }

        foreach ($this->disabledListeners[$class] as $eventName => $listener) {
            $this->getEventManager()->addEventListener($eventName, $listener);
        }

        unset($this->disabledListeners[$class]);
    }

    public function restore(): void
    {
        foreach (array_keys($this->disabledListeners) as $class) {
            $this->enable($class);
        }
    }

    private function getEventManager(): EventManager
    {
        if ($this->eventManager) {
            return $this->eventManager;
        }

        return $this->eventManager = $this->entityManager->getEventManager();
    }
}
