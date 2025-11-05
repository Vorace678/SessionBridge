<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\MadelineProto;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use danog\MadelineProto\SessionPaths;

use danog\MadelineProto\Magic;

final class StringSession extends AbstractSessions {
	public array $sessions = array();

	public const IP_ADDRESSES = array(
		'149.154.175.50',
		'149.154.167.51',
		'149.154.175.100',
		'149.154.167.91',
		'91.108.56.180'
	);

	public function __construct(string $session){
		Magic::$can_getcwd = true;
		Magic::$can_use_igbinary = true;
		$sessionPath = new SessionPaths(session : $session);
		$session = $sessionPath->unserialize();
		$reflection = new \ReflectionClass($session);
		$property = $reflection->getProperty('API');
		$API = $property->getValue($session);
		foreach($API->datacenter->getDataCenterConnections() as $dc_id => $connection){
			if(is_int($dc_id)){
				$port = boolval($dc_id > 10000) ? 80 : 443;
				$dc_id %= 10000;
				if($dc_id >= 1 and $dc_id <= 5){
					$reflection = new \ReflectionClass($connection->auth);
					$property = $reflection->getProperty('authKey');
					$auth_key = $property->getValue($connection->auth);
					$this->sessions []= new Session(dc_id : $dc_id,ip : self::IP_ADDRESSES[$dc_id - 1],port : $port,auth_key : $auth_key);
				}
			}
		}
	}
	static public function import(Session $session,? string $path = null) : string {
		throw new \BadMethodCallException('Converting to Madeline is not yet supported');
		return $path;
	}
}

?>