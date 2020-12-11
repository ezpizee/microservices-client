<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\EncodingUtil;
use Ezpizee\Utils\ListModel;

class Response extends ListModel
{
  public function setUnirestResponse(string $rawBody) {
    $obj = EncodingUtil::isValidJSON($rawBody) ? json_decode($rawBody, true) : [];
    $this->merge($obj);
  }
}
