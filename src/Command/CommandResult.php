<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Exception;
use Phalcon\Messages\Message;

final class CommandResult implements CommandResultInterface
{
    private bool $success;

    private ?string $id;

    private ?Exception $exception;

    /**
     * @var Message[]
     */
    private array $messages = [];

    /**
     * @param bool                  $success
     * @param string|Message[]|null $message
     * @param                       $id
     */
    private function __construct(bool $success, $message, $id)
    {
        $this->success = $success;

        if (is_string($message))
            $this->messages[] = new Message($message);
        else if (is_array($message))
            $this->messages = $message;

        $this->id = $id;
    }

    static public function success(?string $id = null): self
    {
        return new CommandResult(true, null, $id);
    }

    /**
     * @param string|string[]|null $message
     * @param Exception|null       $exception
     *
     * @return static
     */
    static public function fail($message, Exception $exception = null): self
    {
        $result = new CommandResult(false, $message, null);
        $result->exception = $exception;
        return $result;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFail(): bool
    {
        return !$this->success;
    }

    public function getMessage(): string
    {
        return join('.' . PHP_EOL, $this->messages);
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getException(): ?Exception
    {
        return $this->exception ?? null;
    }

    public function __get($name)
    {
        return property_exists($this, $name)
            ? $this->$name
            : null;
    }
}