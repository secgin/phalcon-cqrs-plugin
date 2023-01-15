<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Exception;
use Phalcon\Messages\Message;

interface CommandResultInterface
{
    public function isSuccess(): bool;

    public function isFail(): bool;

    /**
     * @return Message[]
     */
    public function getMessages(): array;

    public function getException(): ?Exception;

    public function getId(): ?string;
}