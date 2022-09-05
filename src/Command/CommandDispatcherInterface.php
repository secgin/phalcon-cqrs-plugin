<?php

namespace YG\Phalcon\Command;


interface CommandDispatcherInterface
{
    public function dispatch(AbstractCommand $command): CommandResult;

    /**
     * @param string $commandClass        Komut class adı namespace ile birlikte
     * @param string $commandHandlerClass Komut işletyicisi class adı namespace ile birlikte
     */
    public function register(string $commandClass, string $commandHandlerClass): void;

    /**
     * Birden fazla komut işleyicisini kaydetmek için dizi alır.
     *
     * Dizi örneği:
     * [
     *  CreateUser => CreateUserCommandHandler,
     *  UpdateRole => UpdateRuleCommandHandler,
     * ...
     * ]
     *
     * @param array $handlers
     */
    public function registerFromArray(array $handlers): void;
}