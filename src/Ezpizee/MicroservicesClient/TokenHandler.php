<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\Session;

class TokenHandler implements TokenHandlerInterface
{
    /**
     * @var Session
     */
    private $session;
    private $key = '';

    public function __construct(string $key)
    {
        $this->session = new Session();
        $this->key = $key;
    }

    public function keepToken(Token $token)
    : void
    {
        $this->session->set($this->key, $token);
    }

    public function getToken()
    : Token
    {
        if ($this->key && isset($_SESSION)) {
            $token = $this->session->get($this->key);
            if ($token instanceof Token) {
                return $token;
            }
        }
        return new Token([]);
    }

    public function setCookie(string $name, string $value = null, int $expire = 0, string $path = '/')
    {
        $this->session->setCookie($name, $value, $expire, $path);
    }
}
