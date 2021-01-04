<?php

namespace Ezpizee\MicroservicesClient;

use JsonSerializable;

class Token implements JsonSerializable
{
    private $data = [];
    private $keys = [
        'session_id'=>'Session-Id', 'token_uuid'=>'token_uuid',
        'grant_type' => 'grant_type', 'token_param_name'=>'token_param_name',
        'expire_in' => 'expire_in',
        'roles' => 'roles', 'user' => 'user'
    ];

    public function __construct(array $data) {$this->data = $data;}

    public function getSessionId(): string {return $this->_get($this->keys['session_id'], '');}
    public function getTokenUUID(): string {return $this->_get($this->keys['token_uuid'], '');}
    public function getGrantType(): string {return $this->_get($this->keys['grant_type'], '');}
    public function getTokenParamName(): string {return $this->_get($this->keys['token_param_name'], '');}
    public function getAuthorizationBearerToken(): string {
        return $this->_get($this->_get($this->keys['token_param_name'], 'AuthorizationBearerToken'), '');
    }
    public function getExpireIn(): int {return $this->_get($this->keys['expire_in'], 0);}
    public function getRoles(): array {return $this->_get($this->keys['roles'], []);}
    public function getUser(string $key='') {
        $user = $this->_get($this->keys['user'], []);
        if (!empty($key)) {
            if (isset($user[$key])) {
                return $user[$key];
            }
        }
        return $user;
    }

    private function _get($k, $v) {return isset($this->data[$k]) ? $this->data[$k] : $v;}

    public function jsonSerialize(): array {return $this->data;}

    public function __toString(): string {return json_encode($this->jsonSerialize());}
}