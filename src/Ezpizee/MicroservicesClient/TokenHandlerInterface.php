<?php

namespace Ezpizee\MicroservicesClient;

interface TokenHandlerInterface
{
    public function __construct(string $key);

    public function keepToken(Token $token): void;

    public function getToken(): Token;
}