<?php

declare(strict_types = 1);

defined('STDOUT') || define('STDOUT',fopen('php://stdout','wb'));

function colorize(string $text,? string $fg = null,? string $bg = null,array $options = []) : string {
	$fgMap = [
		'black'=>30,
		'red'=>31,
		'green'=>32,
		'yellow'=>33,
		'blue'=>34,
		'magenta'=>35,
		'cyan'=>36,
		'white'=>37,
		'bright_black'=>90,
		'bright_red'=>91,
		'bright_green'=>92,
		'bright_yellow'=>93,
		'bright_blue'=>94,
		'bright_magenta'=>95,
		'bright_cyan'=>96,
		'bright_white'=>97
	];
	$bgMap = [
		'black'=>40,
		'red'=>41,
		'green'=>42,
		'yellow'=>43,
		'blue'=>44,
		'magenta'=>45,
		'cyan'=>46,
		'white'=>47,
		'bright_black'=>100,
		'bright_red'=>101,
		'bright_green'=>102,
		'bright_yellow'=>103,
		'bright_blue'=>104,
		'bright_magenta'=>105,
		'bright_cyan'=>106,
		'bright_white'=>107
	];
	$optsMap = [
		'reset'=>0,
		'bold'=>1,
		'dim'=>2,
		'underline'=>4,
		'blink'=>5,
		'reverse'=>7,
		'hidden'=>8,
	];
	if(cli_supports_ansi() === false):
		return $text;
	endif;
	$codes = array();
	if(empty($fg) === false and array_key_exists($fg,$fgMap)):
		$codes []= $fgMap[$fg];
	endif;
	if(empty($bg) === false and array_key_exists($bg,$bgMap)):
		$codes []= $bgMap[$bg];
	endif;
	foreach($options as $opt):
		if(array_key_exists($opt,$optsMap)):
			$codes []= $optsMap[$opt];
		endif;
	endforeach;
	if(empty($codes)):
		return $text;
	endif;
	return sprintf("\033[%sm%s\033[0m",implode(';',$codes),$text);
}

function cli_supports_ansi() : bool {
	if(function_exists('stream_isatty')):
		if(@stream_isatty(STDOUT) === false) return false;
	endif;
	if(DIRECTORY_SEPARATOR === chr(92)):
		if(function_exists('sapi_windows_vt100_support')):
			@sapi_windows_vt100_support(STDOUT,true);
		endif;
	endif;
	return true;
}

?>