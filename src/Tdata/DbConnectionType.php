<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Tdata;

use Throwable;

enum DbConnectionType : int {
	case dbictAuto = 0;
	case dbictHttpAuto = 1;
	case dbictHttpProxy = 2;
	case dbictTcpProxy = 3;
	case dbictProxiesListOld = 4;
	case dbictProxiesList = 5;

	static public function fromId(int $id) : ? self {
		try {
			return self::from($id);
		} catch(Throwable $e){
			return null;
		}
	}
	public function toId() : int {
		return $this->value;
	}
}

?>