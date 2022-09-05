<?php

namespace YG\Phalcon\Cqrs\Command;

use Error;
use Exception;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use ReflectionClass;
use YG\Phalcon\Cqrs\Command\Db\AbstractCreateDbCommand;
use YG\Phalcon\Cqrs\Command\Db\AbstractDeleteDbCommand;
use YG\Phalcon\Cqrs\Command\Db\AbstractUpdateDbCommand;
use YG\Phalcon\Cqrs\Command\Db\Handler\CreateDbCommandHandler;
use YG\Phalcon\Cqrs\Command\Db\Handler\DeleteDbCommandHandler;
use YG\Phalcon\Cqrs\Command\Db\Handler\UpdateDbCommandHandler;

final class CommandDispatcher extends Injectable implements CommandDispatcherInterface, EventsAwareInterface
{
    private array
        $handlers = [],
        $handlerClasses = [];

    private ?string $commandHandlerNamespace = null;

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
            {
                $this->eventsManager->fire(
                    'commandDispatcher:afterDispatch',
                    $this,
                    [
                        'command' => $command,
                        'result' => $result->getData()
                    ]);
            }

            return $result;
        }
        catch (Exception|Error $ex)
        {
            return CommandResult::fail('İşlem sırasında bir hata oluştu.');
        }
    }

    public function register(string $commandClass, string $commandHandlerClass): void
    {
        $this->handlerClasses[$commandClass] = $commandHandlerClass;
    }

    public function registerFromArray(array $handlers): void
    {
        $this->handlerClasses = array_merge($this->handlerClasses, $handlers);
    }

    /**
     * Komut işleyicisinin otomatik yüklenmesi için gerekli namespace eki.
     * Komut işleyicisi register metodotları ile kayıt edilirse gerek duyulmaz.
     */
    public function setNamespace(string $commandHandlerNamespace)
    {
        $this->commandHandlerNamespace = $commandHandlerNamespace;
    }


    private function getCommandHandler(AbstractCommand $command)
    {
        $commandClass = get_class($command);

        if (array_key_exists($commandClass, $this->handlers))
            return $this->handlers[$commandClass];

        if (array_key_exists($commandClass, $this->handlerClasses))
        {
            $commandHandlerClass = $this->handlerClasses[$commandClass];
            $this->handlers[$commandClass] = new $commandHandlerClass;
            return $this->handlers[$commandClass];
        }

        $annotations = $this->annotations->get($commandClass);
        $classAnnotations = $annotations->getClassAnnotations();
        if ($classAnnotations and $classAnnotations->has('Handler'))
        {
            $commandHandlerClass = $classAnnotations->get('Handler')->getArgument(0);

            if (class_exists($commandHandlerClass))
            {
                $this->handlers[$commandClass] = new $commandHandlerClass;
                return $this->handlers[$commandClass];
            }
        }

        if ($this->commandHandlerNamespace != null)
        {
            $reflection = new ReflectionClass($command);
            $commandClassShortName = $reflection->getShortName();
            $commandHandlerClass = $this->commandHandlerNamespace . '\\' . $commandClassShortName . 'CommandHandler';

            if (class_exists($commandHandlerClass))
            {
                $this->handlers[$commandClass] = new $commandHandlerClass;
                return $this->handlers[$commandClass];
            }
        }

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