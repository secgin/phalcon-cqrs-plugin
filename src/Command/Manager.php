<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;

class Manager implements ManagerInterface, InjectionAwareInterface, EventsAwareInterface
{
    public function notifyEvent(string $eventName, AbstractCommand $command, ResultInterface $result): void
    {
        if (isset($this->eventsManager))
            $this->eventsManager->fire('commandsManager:' . $eventName, $command, $result);
    }

    #region EventsAwareInterface
    private EventsManagerInterface $eventsManager;

    public function getEventsManager(): ?EventsManagerInterface
    {
        return $this->eventsManager ?? null;
    }

    public function setEventsManager(EventsManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }
    #endregion

    #region InjectionAwareInterface
    private DiInterface $container;

    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

    public function getDI(): DiInterface
    {
        if (!isset($this->container))
            $this->container = Di::getDefault();

        return $this->container;
    }
    #endregion
}