<?php

declare(strict_types = 1);

namespace Tak\SessionBridge;

use Traversable;

use ArrayIterator;

use JsonSerializable;

use IteratorAggregate;

abstract class AbstractSessions implements JsonSerializable , IteratorAggregate {
	public array $sessions = array();

	abstract static public function import(Session $session) : string;

	public function jsonSerialize() : array {
		return array_map(static fn(Session $session) : array => $session->jsonSerialize(),$this->sessions);
	}
	public function getIterator() : Traversable {
		return new ArrayIterator($this->sessions);
	}
}

?>