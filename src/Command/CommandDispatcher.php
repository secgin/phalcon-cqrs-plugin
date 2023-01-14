<?php

namespace YG\Phalcon\Cqrs\Command;

use Error;
use Exception;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use YG\Phalcon\Cqrs\Command\Db\AbstractCreateDbCommand;
use YG\Phalcon\Cqrs\Command\Db\AbstractDeleteDbCommand;
use YG\Phalcon\Cqrs\Command\Db\AbstractUpdateDbCommand;
use YG\Phalcon\Cqrs\Command\Db\Handler\CreateDbCommandHandler;
use YG\Phalcon\Cqrs\Command\Db\Handler\DeleteDbCommandHandler;
use YG\Phalcon\Cqrs\Command\Db\Handler\UpdateDbCommandHandler;

final class CommandDispatcher extends Injectable implements CommandDispatcherInterface, EventsAwareInterface
{
    private array $handlers = [];

    protected ?ManagerInterface $eventsManager = null;


    public function dispatch(AbstractCommand $command): CommandResult
    {
        try
        {
            $commandHandler = $this->getCommandHandler($command);

            if ($commandHandler == null)
                throw new Error('Not Found Command Handler');

            if (!method_exists($commandHandler, 'handle'))
                throw new Error('Not Found Command Handler Method');

            $result = $commandHandler->handle($command);

            if ($this->eventsManager and $result->isSuccess())
                $this->eventsManager->fire('commandDispatcher:afterDispatch', $command);

            return $result;
        }
        catch (Exception|Error $ex)
        {
            if ($this->eventsManager)
                $this->eventsManager->fire('commandDispatcher:fail', $command, $ex);

            return CommandResult::fail('İşlem sırasında hata oluştu.');
        }
    }

    private function getCommandHandler(AbstractCommand $command)
    {
        $commandClass = get_class($command);

        if (array_key_exists($commandClass, $this->handlers))
            return $this->handlers[$commandClass];

        $commandHandlerClass = str_replace('Commands\\', 'CommandHandlers\\', $commandClass) . "CommandHandler";
        if (class_exists($commandHandlerClass))
        {
            $this->handlers[$commandClass] = new $commandHandlerClass;
            return $this->handlers[$commandClass];
        }

        if (is_subclass_of($command, AbstractCreateDbCommand::class))
            return new CreateDbCommandHandler();
        elseif (is_subclass_of($command, AbstractUpdateDbCommand::class))
            return new UpdateDbCommandHandler();
        elseif (is_subclass_of($command, AbstractDeleteDbCommand::class))
            return new DeleteDbCommandHandler();

        return null;
    }

    #region EventsAwareInterface
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }
    #endregion
}