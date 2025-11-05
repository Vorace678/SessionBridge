<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Pyrogram;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use PDO;

final class SQLiteSession extends AbstractSessions {
	public array $sessions = array();

	public const IP_ADDRESSES = array(
		'149.154.175.50',
		'149.154.167.51',
		'149.154.175.100',
		'149.154.167.91',
		'91.108.56.180'
	);
	public const SUPPORTED_VERSIONS = [3,4,5,6,7];

	public function __construct(string $path){
		$connection = new PDO('sqlite:'.$path);
		$connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$stmt = $connection->query('SELECT number FROM version LIMIT 1');
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if(in_array($row['number'],self::SUPPORTED_VERSIONS)){
				$stmt = $connection->query('SELECT dc_id , test_mode , auth_key FROM sessions LIMIT 1');
				if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$this->sessions []= new Session(dc_id : $row['dc_id'],ip : self::IP_ADDRESSES[$row['dc_id'] - 1],port : boolval($row['test_mode']) ? 80 : 443,auth_key : $row['auth_key']);
				} else {
					throw new \RuntimeException('No session row found in sessions table');
				}
			} else {
				throw new \InvalidArgumentException('This Pyrogram session sqlite is not yet supported');
			}
		} else {
			throw new \RuntimeException('No number row found in version table');
		}
	}
	static public function import(Session $session,? string $path = null) : string {
		throw new \BadMethodCallException('Converting to Pyrogram is not yet supported');
		return $path;
	}
}

?>