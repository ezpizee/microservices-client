<?php

namespace Ezpizee\MicroserviceClient;

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

  public function __construct(string $schema, string $host, Config $config)
  {
    $this->config = $config;
    $this->schema = $schema;
    $this->host = $host;
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

  private function setBaseHeaders(): void {}

  private function url(string $uri): string {return $this->schema.$this->host.$uri;}
}
