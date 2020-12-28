<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\Logger;
use RuntimeException;
use Unirest\Request;

class Client
{
    const KEY_ACCESS_TOKEN = 'access_token_key';
    const KEY_CLIENT_ID = 'client_id';
    const KEY_CLIENT_SECRET = 'client_secret';
    const KEY_APP_NAME = 'app_name';
    const KEY_ENV = 'env';
    const KEY_TOKEN_URI = 'token_uri';

    const HEADER_PARAM_ACCEPT = "Accept";
    const HEADER_PARAM_CTYPE = "Content-Type";
    const HEADER_PARAM_ACCESS_TOKEN = "Authorization";
    const HEADER_PARAM_USER_AGENT = "User-Agent";
    const HEADER_VALUE_JSON = "application/json";
    const HEADER_VALUE_USER_AGENT = "Ezpizee Web/1.0";
    const HEADER_PARAM_APP_NAME = "App-Name";
    const HEADER_PARAM_APP_VERSION = "App-Version";
    const HEADER_VALUE_APP_VERSION = "0.0.1";
    const HEADER_PARAM_APP_PLATFORM = "App-Platform";
    const HEADER_VALUE_APP_PLATFORM = "Unknown";
    const HEADER_PARAM_OS_PLATFORM_VERSION = "OS-Platform-Version";
    const HEADER_VALUE_OS_PLATFORM_VERSION = "Unknown";
    const HEADER_LANGUAGE_TAG = "Language-Tag";

    private $isMultipart = false;
    private $platform = '';
    private $platformVersion = '';

    /**
     * @var Config
     */
    private $config;
    /**
     * @var string http:// or https://
     */
    private $schema;
    /**
     * @var string domain, subdomain, domain:port, or subdomain:port
     */
    private $host;
    private $method;
    private $methods = ['get' => 'GET', 'post' => 'POST', 'delete' => 'DELETE', 'patch' => 'PATCH'];
    private $headers = [];
    private $body;

    public static function verifyPeer(bool $b): void
    {
        Request::verifyPeer($b);
    }

    public static function getContentAsString(string $url, bool $ignoreSSLValidation=false): string
    {
        if ($ignoreSSLValidation) {
            self::verifyPeer(false);
        }
        return Request::get($url)->raw_body;
    }

    public function __construct(string $schema, string $host, Config $config)
    {
        if ($config->isValid()) {
            $this->config = $config;
            $this->schema = $schema;
            $this->host = $host;
        } else {
            throw new RuntimeException('Invalid microservices config', 422);
        }
    }

    public function addHeader(string $key, string $val): void
    {
        $this->headers[$key] = $val;
    }

    public function addHeaders(array $headers): void
    {
        if (!empty($headers)) {
            foreach ($headers as $key => $val) {
                if (!is_numeric($key)) {
                    $this->addHeader($key, $val);
                }
            }
        }
    }

    public function get(string $uri, array $params = []): Response
    {
        $this->method = $this->methods['get'];
        $this->body = $params;
        return $this->request($this->url($uri));
    }

    public function post(string $uri, array $body): Response
    {
        $this->method = $this->methods['post'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function put(string $uri, array $body): Response
    {
        $this->method = $this->methods['put'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function postFormData(string $uri, array $body = []): Response
    {
        $this->method = $this->methods['post'];
        $this->body = Request\Body::multipart($body);
        $this->setMultipart(true);
        return $this->request($this->url($uri));
    }

    public function delete(string $uri, array $body = []): Response
    {
        $this->method = $this->methods['delete'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function patch(string $uri, array $body = []): Response
    {
        $this->method = $this->methods['patch'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    public function getExpireIn(string $tokeyKey): string
    {
        return isset($_COOKIE[$tokeyKey . '_ei']) ? $_COOKIE[$tokeyKey . '_ei'] : '0';
    }

    public function setMultipart(bool $b): void
    {
        $this->isMultipart = $b;
    }

    public function setPlatform(string $platform): void
    {
        $this->platform = $platform;
    }

    public function setPlatformVersion(string $platformVersion): void
    {
        $this->platformVersion = $platformVersion;
    }

    protected function getHeaders(): array
    {
        return $this->headers;
    }

    protected function hasHeader(string $key): bool
    {
        return isset($this->headers[$key]);
    }

    protected function url(string $uri): string
    {
        return $this->schema . str_replace('//', '/', $this->host . ($uri && $uri[0] === '/' ? '' : '/') . $uri);
    }

    private function request(string $url): Response
    {
        $this->setBaseHeaders();

        Logger::debug('API CALL: '.$this->method.' '.$url.(isset($_SERVER['HTTP_REFERER'])?'; refererer: '.$_SERVER['HTTP_REFERER']:''));

        $response = new Response([]);

        switch ($this->method) {
            case $this->methods['get']:
                $unirestRequest = Request::get($url, $this->headers);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['post']:
                $unirestRequest = Request::post($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['delete']:
                $unirestRequest = Request::delete($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['put']:
                $unirestRequest = Request::put($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['patch']:
                $unirestRequest = Request::patch($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
        }

        return $response;
    }

    private function setBaseHeaders(): void
    {
        if (!$this->isMultipart && !$this->hasHeader(self::HEADER_PARAM_CTYPE)) {
            $this->addHeader(self::HEADER_PARAM_CTYPE, self::HEADER_VALUE_JSON);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_ACCEPT)) {
            $this->addHeader(self::HEADER_PARAM_ACCEPT, self::HEADER_VALUE_JSON);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_USER_AGENT)) {
            $this->addHeader(self::HEADER_PARAM_USER_AGENT, self::HEADER_VALUE_USER_AGENT);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_VERSION)) {
            $this->addHeader(self::HEADER_PARAM_APP_VERSION, self::HEADER_VALUE_APP_VERSION);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_PLATFORM)) {
            $this->addHeader(self::HEADER_PARAM_APP_PLATFORM, $this->platform ? $this->platform : self::HEADER_VALUE_APP_PLATFORM);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_OS_PLATFORM_VERSION)) {
            $this->addHeader(self::HEADER_PARAM_OS_PLATFORM_VERSION, $this->platformVersion ? $this->platformVersion : self::HEADER_VALUE_OS_PLATFORM_VERSION);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_NAME) && $this->getConfig(self::KEY_APP_NAME)) {
            $this->addHeader(self::HEADER_PARAM_APP_NAME, $this->getConfig(self::KEY_APP_NAME));
        }
        $this->fetchBearerToken($this->getConfig(self::KEY_ACCESS_TOKEN, 'token'));
    }

    private function fetchBearerToken(string $tokenKey): void
    {
        if (!$this->hasHeader(self::HEADER_PARAM_ACCESS_TOKEN) && $this->config->has(self::KEY_TOKEN_URI)) {
            if (!isset($_COOKIE[$tokenKey])) {
                $response = Request::post(
                    $this->url($this->getConfig(self::KEY_TOKEN_URI)),
                    $this->getHeaders(),
                    null,
                    $this->getConfig(self::KEY_CLIENT_ID),
                    $this->getConfig(self::KEY_CLIENT_SECRET)
                );
                if (isset($response->body->data)
                    && isset($response->body->data->AuthorizationBearerToken)
                    && isset($response->body->data->expire_in)) {
                    $expire = time() + ($response->body->data->expire_in - (10 * 60 * 1000));
                    setcookie($tokenKey, $response->body->data->AuthorizationBearerToken, $expire, "/");
                    setcookie($tokenKey . '_ei', $response->body->data->expire_in, $expire, "/");
                    $this->addHeader(self::HEADER_PARAM_ACCESS_TOKEN, 'Bearer ' . $response->body->data->AuthorizationBearerToken);
                }
            } else {
                $this->addHeader(self::HEADER_PARAM_ACCESS_TOKEN, 'Bearer ' . $_COOKIE[$tokenKey]);
            }
        }
    }
}
