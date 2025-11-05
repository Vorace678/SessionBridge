<?php

declare(strict_types = 1);

namespace Tak\SessionBridge;

use Tak\SessionBridge\LiveProto;

use Tak\SessionBridge\MadelineProto;

use Tak\SessionBridge\Pyrogram;

use Tak\SessionBridge\Telethon;

use Tak\SessionBridge\Tdata;

function from_liveproto_sqlite(string $session,mixed ...$arguments) : AbstractSessions {
	return new LiveProto\SQLiteSession($session,...$arguments);
}

function to_liveproto_sqlite(Session $session,mixed ...$arguments) : string {
	return LiveProto\SQLiteSession::import($session,...$arguments);
}

function from_liveproto_string(string $session,mixed ...$arguments) : AbstractSessions {
	return new LiveProto\StringSession($session,...$arguments);
}

function to_liveproto_string(Session $session,mixed ...$arguments) : string {
	return LiveProto\StringSession::import($session,...$arguments);
}

function from_madelineproto_string(string $session,mixed ...$arguments) : AbstractSessions {
	return new MadelineProto\StringSession($session,...$arguments);
}

function to_madelineproto_string(Session $session,mixed ...$arguments) : string {
	return MadelineProto\StringSession::import($session,...$arguments);
}

function from_pyrogram_sqlite(string $session,mixed ...$arguments) : AbstractSessions {
	return new Pyrogram\SQLiteSession($session,...$arguments);
}

function to_pyrogram_sqlite(Session $session,mixed ...$arguments) : string {
	return Pyrogram\SQLiteSession::import($session,...$arguments);
}

function from_pyrogram_string(string $session,mixed ...$arguments) : AbstractSessions {
	return new Pyrogram\StringSession($session,...$arguments);
}

function to_pyrogram_string(Session $session,mixed ...$arguments) : string {
	return Pyrogram\StringSession::import($session,...$arguments);
}

function from_telethon_sqlite(string $session,mixed ...$arguments) : AbstractSessions {
	return new Telethon\SQLiteSession($session,...$arguments);
}

function to_telethon_sqlite(Session $session,mixed ...$arguments) : string {
	return Telethon\SQLiteSession::import($session,...$arguments);
}

function from_telethon_string(string $session,mixed ...$arguments) : AbstractSessions {
	return new Telethon\StringSession($session,...$arguments);
}

function to_telethon_string(Session $session,mixed ...$arguments) : string {
	return Telethon\StringSession::import($session,...$arguments);
}

function from_tdata(string $session,mixed ...$arguments) : AbstractSessions {
	return new Tdata\TdesktopSession($session,...$arguments);
}

function to_tdata(Session $session,mixed ...$arguments) : string {
	return Tdata\TdesktopSession::import($session,...$arguments);
}

?>