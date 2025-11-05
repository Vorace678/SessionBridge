<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Telethon;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use Tak\Liveproto\Utils\Tools;

final class StringSession extends AbstractSessions {
	public array $sessions = array();

	public const SUPPORTED_VERSIONS = [1];

	public function __construct(string $session){
		if(in_array(substr($session,0,1),self::SUPPORTED_VERSIONS)){
			$encoded = substr($session,1);
			$decoded = Tools::base64_url_decode($encoded);
			$format = sprintf('Cdc_id/a%dip/nport/a256auth_key',strlen($decoded) - intval(1 + 2 + 256));
			$unpacked = @unpack($format,$decoded);
			if($ipAddress = @inet_ntop($unpacked['ip'])){
				$this->sessions []= new Session(dc_id : $unpacked['dc_id'],ip : $ipAddress,port : $unpacked['port'],auth_key : $unpacked['auth_key']);
			} else {
				throw new \RuntimeException('Failed to find IP address');
			}
		} else {
			throw new \InvalidArgumentException('This Telethon session string is not yet supported');
		}
	}
	static public function import(Session $session) : string {
		$ipBin = @inet_pton($session->ip);
		$version = self::SUPPORTED_VERSIONS[array_key_last(self::SUPPORTED_VERSIONS)];
		$format = sprintf('Ca%dna256',strlen($ipBin));
		$b64 = Tools::base64_url_encode(pack($format,$session->dc_id,$ipBin,$session->port,$session->auth_key->key));
		return $version.str_pad($b64,intval(ceil(strlen($b64) / 4) * 4),chr(61),STR_PAD_RIGHT);
	}
}

?>