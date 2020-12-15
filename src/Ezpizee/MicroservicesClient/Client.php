<?php

namespace Ezpizee\MicroservicesClient;

use Unirest\Request;

class Client
{
  /**
   * @var Config
   */
  private $config;
  private $schema;
  private $host;
  private $method;
  private $methods = ['get' => 'GET', 'post' => 'POST', 'delete' => 'DELETE', 'patch' => 'PATCH'];
  private $headers = [];
  private $body;
  private $isFormData = false;

  public function __construct(string $schema, string $host, Config $config)
  {
    $this->config = $config;
    $this->schema = $schema;
    $this->host = $host;
    $this->setHeaderBase();
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
    $this->body = $body;
    $this->isFormData = true;
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

  private function request(string $url): Response
  {
    $this->requestToken();
    $response = new Response([]);

    switch ($this->method) {
      case $this->methods['get']:
        $unirestRequest = Request::get($url, $this->headers);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
      case $this->methods['post']:
        if ($this->isFormData) $this->body = Request\Body::multipart($this->body);
        $unirestRequest = Request::post($url, $this->headers, $this->body);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
      case $this->methods['delete']:
        $unirestRequest = Request::delete($url, $this->headers, $this->body);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
      case $this->methods['put']:
        $unirestRequest = Request::delete($url, $this->headers, $this->body);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
      case $this->methods['patch']:
        $unirestRequest = Request::patch($url, $this->headers, $this->body);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
    }

    return $response;
  }

  private function url(string $uri): string
  {
    return $this->schema . $this->host . $uri;
  }

  private function requestToken(): void
  {
    if (!isset($_COOKIE['token'])) {
      $tokenUri = $this->config->get('token_uri');
      $response = Request::post($this->url($tokenUri), $this->headers, null, $this->config->get('client_id'), $this->config->get('client_secret'));
      if (
        isset($response->body->data)
        && isset($response->body->data->AuthorizationBearerToken)
        && isset($response->body->data->expire_in)
      ) {
        setcookie('token', $response->body->data->AuthorizationBearerToken, time() + ($response->body->data->expire_in - (10 * 60 * 1000)), "/");
        $this->addHeader('Authorization', 'Bearer ' . $response->body->data->AuthorizationBearerToken);
      }
    } else {
      $this->addHeader('Authorization', 'Bearer ' . $_COOKIE['token']);
    }
  }

  private function setHeaderBase(): void
  {
    $this->addHeader('App-Name', 'My Electron App Demo');
  }
}
