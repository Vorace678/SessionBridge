<?php

require '../attr/vendor/autoload.php';

use function Tak\SessionBridge\from_liveproto_sqlite;
use function Tak\SessionBridge\from_liveproto_string;
use function Tak\SessionBridge\from_madelineproto_string;
use function Tak\SessionBridge\from_pyrogram_sqlite;
use function Tak\SessionBridge\from_pyrogram_string;
use function Tak\SessionBridge\from_telethon_sqlite;
use function Tak\SessionBridge\from_telethon_string;
use function Tak\SessionBridge\from_tdata;

use function Tak\SessionBridge\to_liveproto_sqlite;
use function Tak\SessionBridge\to_liveproto_string;
use function Tak\SessionBridge\to_pyrogram_string;
use function Tak\SessionBridge\to_telethon_string;

$sessions = from_liveproto_string('LP_17d0e58f');

foreach($sessions as $session){
	print('⟩ LiveProto SQLite : ');
	var_dump(to_liveproto_sqlite($session)); // path of SQLite3 //
	print('⟩ Pyrogram String : ');
	var_dump(to_pyrogram_string($session)); // text of String //
	print('⟩ Telethon String : ');
	var_dump(to_telethon_string($session)); // text of String //
}

echo PHP_EOL , PHP_EOL;

$sessions = from_tdata('./tdatas/34682422808/tdata');

foreach($sessions as $session){
	print('⟩ LiveProto String : ');
	var_dump(to_liveproto_string($session)); // path of String //
	print('⟩ Telethon String : ');
	var_dump(to_telethon_string($session)); // text of String //
	print('⟩ LiveProto SQLite : ');
	var_dump(to_liveproto_sqlite($session)); // path of SQLite3 //
}

echo PHP_EOL , PHP_EOL;

// Telethon String //

$x = '1AZWarzgBu1KCRriWiMCXtDXRQ-9rWKPYhWvz0me8Pj6KKO4E-ZW-cBwb3RY9hOn0-PGJFKGCLkYww61t_yZZlnMaJueEuQnyn3f4sGknP9mJ6MyxUR-YvaoM43SXmVH_cJTYAsAJhqitiGtFVMQvXuYWC8MNnj5XUWxsMcH0WDUbKwYvz9HRWozsmsXiuEITBn-2yhwm2kknTti39k2GmFxbM0y_ABhfFrtL1IkK0-CR1io0JlpZImYT5ekmjWm4kfvJZfAP0cmPbW1nLKEGTiNYRolcbY9n_zmzgqcvEfP3H5JJCT-h2eBBGMpf_D_Ufm1UOCzA5YQmZpAMQ1xnXMvpTWb_wdA';

$y = from_telethon_string($x);

$session = reset($y->sessions);

$z = to_telethon_string($session);

similar_text($x,$z,$precent);

printf('%.2f%%'.PHP_EOL,$precent); // It's not always 100%, and that's okay //

$l = to_liveproto_string($session);

echo '» LiveProto Session string file path : ' , $l;

echo PHP_EOL , PHP_EOL;

// Pyrogram String //

$x = 'AQAAVNwAa8SW50dYZ4s6ZpelBdhv3_SFE9mc4rYAu6o2uG9c9-Z1bBoYnHQZo-URUdLdwv-evF8kyDgLje4ytvy26sI8z3nfycXXY3EtjIl6SsBpCSG_RjUeE8qUKAQN5AjtqFFhmvbLAtsey4xuLBdx5yFEY8eeUf_KoBe_l_CNnGVoyhKbKmzsurgZCP5XxXAiHNVKIaFqqueUIejM_C3w75TUmJ1lLYYu8SENsklaWqq8SN84wo3ikUWYw0Gv2a2_-HCOetPcNl3IxHEhlQRC6B_CBqoDdfBpuyF8kPzhqABPzAl6xLWJX9AQCisqc_XADMW9s7EBtI8N9wzwoQvyjpoMFAAAAAGj_h5JAQ';

$y = from_pyrogram_string($x);

$session = reset($y->sessions);

$z = to_pyrogram_string($session);

similar_text($x,$z,$precent);

printf('%.2f%%'.PHP_EOL,$precent); // It's not always 100%, and that's okay //

$l = to_liveproto_string($session);

echo '» LiveProto Session string file path : ' , $l;

echo PHP_EOL , PHP_EOL;

// Telethon SQLite3 //

$load = from_telethon_sqlite('telethon-sqlite.session');

$session = reset($load->sessions);

$LP = to_liveproto_sqlite($session);

echo '» LiveProto Session sqlite3 file path : ' , $LP;

echo PHP_EOL , PHP_EOL;

// Pyrogram SQLite3 //

$load = from_pyrogram_sqlite('pyrogram-sqlite.session');

$session = reset($load->sessions);

$LP = to_liveproto_sqlite($session);

echo '» LiveProto Session sqlite3 file path : ' , $LP;

echo PHP_EOL , PHP_EOL;

?>