<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\LiveProto;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use Tak\Liveproto\Utils\Settings;

use Tak\Liveproto\Database\Session as LPSession;

final class SQLiteSession extends AbstractSessions {
	public array $sessions = array();

	public function __construct(string $name,Settings $settings = new Settings){
		$lpSession = new LPSession(name : $name,mode : 'SQLite',settings : $settings);
		$content = $lpSession->load();
		$auth_key = strval($content->auth_key->key ?? null);
		if(empty($auth_key) === false){
			$this->sessions []= new Session(dc_id : $content->dc,ip : $content->ip,port : $content->port,auth_key : $auth_key);
		} else {
			throw new \InvalidArgumentException('This LiveProto session has no any auth key');
		}
	}
	static public function import(Session $session,? string $name = null,Settings $settings = new Settings) : string {
		$name = is_null($name) ? 'LP_'.hash('crc32b',serialize($session)) : $name;
		$lpSession = new LPSession(name : $name,mode : 'SQLite',settings : $settings);
		$content = $lpSession->load();
		$content->dc = $session->dc_id;
		$content->ip = $session->ip;
		$content->port = $session->port;
		$content->expires_at = $session->auth_key->expires_at;
		$content->auth_key = $session->auth_key;
		$lpSession->save();
		return $name;
	}
}

?>