<?php

namespace YG\Phalcon\Cqrs\Command;

use Error;
use Exception;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Throwable;

final class Dispatcher extends Injectable implements DispatcherInterface, EventsAwareInterface
{
    private array $handlers = [];

    public function dispatch(Command $command): CommandResultInterface
    {
        if (!$this->getDI()->has(get_class($command)))
        {
            if (method_exists($command, 'handle'))
                return $command->execute();
        }

        try
        {
            $commandHandler = $this->getCommandHandler($command);

            if ($commandHandler == null)
                throw new Error('Not Found Command Handler');

            if (!method_exists($commandHandler, 'handle'))
                throw new Error('Not Found Command Handler Method');

            $this->notifyEvent('beforeExecute', $command);
            $result = $commandHandler->handle($command);
            $this->notifyEvent('afterExecute', $command, $result);
            return $result;
        }
        catch (Exception|Error|Throwable $ex)
        {
            $result = CommandResult::fail('İşlem sırasında hata oluştu.');
            $this->notifyEvent('error', $command, $ex);
            return $result;
        }
    }

    private function getCommandHandler(Command $command)
    {
        $commandClass = get_class($command);

        if (array_key_exists($commandClass, $this->handlers))
            return $this->handlers[$commandClass];

        if ($this->getDI()->has($commandClass))
        {
            $commandHandler = $this->getDI()->get($commandClass);
            $this->handlers[$commandClass] = $commandHandler;
            return $commandHandler;
        }

        $commandHandlerClassName = $this->getCommandHandlerClassName($command);
        if (class_exists($commandHandlerClassName))
        {
            $commandHandler = new $commandHandlerClassName;
            $this->handlers[$commandClass] = $commandHandler;
            return $commandHandler;
        }

        return null;
    }

    private function getCommandHandlerClassName(Command $command): ?string
    {
        $commandClass = get_class($command);

        $commandHandlerClass = str_replace('Commands\\', 'CommandHandlers\\', $commandClass) . "CommandHandler";
        if (class_exists($commandHandlerClass))
            return $commandHandlerClass;

        $arr = explode('\\', $commandClass);
        $commandName = array_pop($arr);

        array_pop($arr);
        $namespace = join('\\', $arr);

        $commandHandlerClassName = $namespace . '\\CommandHandlers\\' . $commandName . "CommandHandler";
        if (class_exists($commandHandlerClassName))
            return $commandHandlerClassName;

        $commandHandlerClassName = $namespace . '\\Services\\CommandHandlers\\' . $commandName . "CommandHandler";
        if (class_exists($commandHandlerClassName))
            return $commandHandlerClassName;

        array_pop($arr);
        $namespace = join('\\', $arr);

        $commandHandlerClassName = $namespace . '\\CommandHandlers\\' . $commandName . "CommandHandler";
        if (class_exists($commandHandlerClassName))
            return $commandHandlerClassName;

        $commandHandlerClassName = $namespace . '\\Services\\CommandHandlers\\' . $commandName . "CommandHandler";
        if (class_exists($commandHandlerClassName))
            return $commandHandlerClassName;

        return null;
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

    public function notifyEvent(string $eventName, Command $command, $result = null): void
    {
        if (isset($this->eventsManager))
            $this->eventsManager->fire('commandDispatcher:' . $eventName, $command, $result);
    }
}