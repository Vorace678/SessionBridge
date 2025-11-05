<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Telethon;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use PDO;

final class SQLiteSession extends AbstractSessions {
	public array $sessions = array();

	public const SUPPORTED_VERSIONS = [7];

	public function __construct(string $path){
		$connection = new PDO('sqlite:'.$path);
		$connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$stmt = $connection->query('SELECT version FROM version LIMIT 1');
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			if(in_array($row['version'],self::SUPPORTED_VERSIONS)){
				$stmt = $connection->query('SELECT dc_id , server_address , port , auth_key FROM sessions');
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
					$this->sessions []= new Session(dc_id : $row['dc_id'],ip : $row['server_address'],port : $row['port'],auth_key : $row['auth_key']);
				}
				if(empty($this->sessions)){
					throw new \RuntimeException('No session row found in sessions table');
				}
			} else {
				throw new \InvalidArgumentException('This Telethon session sqlite is not yet supported');
			}
		} else {
			throw new \RuntimeException('No version row found in version table');
		}
	}
	static public function import(Session $session,? string $path = null) : string {
		throw new \BadMethodCallException('Converting to Telethon is not yet supported');
		return $path;
	}
}

?>