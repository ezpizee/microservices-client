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
  private $methods = ['get'=>'GET','post'=>'POST','delete'=>'DELETE','patch'=>'PATCH'];
  private $headers = [];

  public function __construct(string $schema, string $host, Config $config)
  {
    $this->config = $config;
    $this->schema = $schema;
    $this->host = $host;
  }

  public function addHeader(string $key, string $val): void {$this->headers[$key] = $val;}
  public function addHeaders(array $headers): void {
    if (!empty($headers)) {
      foreach ($headers as $key=>$val) {
        if (!is_numeric($key)) {
          $this->addHeader($key, $val);
        }
      }
    }
  }

  public function get(string $uri): Response {
    $this->method = $this->methods['get'];
    return $this->request($this->url($uri));
  }

  public function post(string $uri): Response {
    $this->method = $this->methods['post'];
    return $this->request($this->url($uri));
  }

  public function delete(string $uri): Response {
    $this->method = $this->methods['delete'];
    return $this->request($this->url($uri));
  }

  public function patch(string $uri): Response {
    $this->method = $this->methods['patch'];
    return $this->request($this->url($uri));
  }

  private function request(string $url): Response {
    $response = new Response([]);
    $this->setBaseHeaders();

    switch ($this->method) {
      case $this->methods['get']:
        $unirestRequest = Request::get($url);
        $response->setUnirestResponse($unirestRequest->raw_body);
        break;
      case $this->methods['post']:
        break;
      case $this->methods['delete']:
        break;
      case $this->methods['patch']:
        break;
    }

    return $response;
  }

  private function setBaseHeaders(): void {
    $ctype = $this->config->get('content-type', 'application/json');
    $this->addHeader('content-type', $ctype);
  }

  private function url(string $uri): string {return $this->schema.$this->host.$uri;}
}
