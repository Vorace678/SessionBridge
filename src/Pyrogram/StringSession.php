<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Pyrogram;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use Tak\Liveproto\Utils\Tools;

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
		$data = Tools::base64_url_decode($session);
		$unpacked = match(strlen($data)){
			// new : 1 + 4 + 1 + 256 + 8 + 1 //
			271 => @unpack('Cdc_id/Napi_id/Ctest_mode/a256auth_key/Nuser_high/Nuser_low/Cis_bot',$data),
			// old 64 : 1 + 1 + 256 + 8 + 1 //
			267 => @unpack('Cdc_id/Ctest_mode/a256auth_key/Nuser_high/Nuser_low/Cis_bot',$data),
			// old 32 : 1 + 1 + 256 + 4 + 1 //
			263 => @unpack('Cdc_id/Ctest_mode/a256auth_key/Nuser_low/Cis_bot',$data),
			default => throw new \InvalidArgumentException('This Pyrogram session string is not yet supported')
		};
		$this->sessions []= new Session(dc_id : $unpacked['dc_id'],ip : self::IP_ADDRESSES[$unpacked['dc_id'] - 1],port : boolval($unpacked['test_mode']) ? 80 : 443,auth_key : $unpacked['auth_key']);
	}
	static public function import(Session $session,int $api_id = 2040,int $user_id = 777000,bool $is_bot = false) : string {
		return Tools::base64_url_encode(pack('CNCa256PC',$session->dc_id,$api_id,intval($session->port === 80),$session->auth_key->key,$user_id,intval($is_bot)));
	}
}

?>