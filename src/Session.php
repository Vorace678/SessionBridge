<?php

declare(strict_types = 1);

namespace Tak\SessionBridge;

use Tak\Liveproto\Crypto\AuthKey;

use Stringable;

use JsonSerializable;

readonly class Session implements Stringable , JsonSerializable {
	public AuthKey $auth_key;

	public function __construct(
		public int $dc_id,
		public string $ip,
		public int $port,
		string $auth_key,
		int $expires_at = 0
	){
		$this->auth_key = new AuthKey(gmp_strval(gmp_import($auth_key)),$expires_at);
	}
	public function jsonSerialize() : array {
		return array(
			'dc_id'=>$this->dc_id,
			'ip'=>$this->ip,
			'port'=>$this->port,
			'expires_at'=>$this->auth_key->expires_at,
			'auth_key'=>bin2hex($this->auth_key->key)
		);
	}
	public function __toString() : string {
		return $this->auth_key->key;
	}
}

?>