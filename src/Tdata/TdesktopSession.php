<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Tdata;

use Tak\SessionBridge\Session;

use Tak\SessionBridge\AbstractSessions;

use Tak\SessionBridge\Tdata\StorageSettingsScheme as Storage;

use Tak\SessionBridge\Tdata\DbConnectionType as ProxyMode;

use Tak\Liveproto\Utils\Binary;

use Tak\Liveproto\Utils\Helper;

use Tak\Liveproto\Crypto\Aes;

use Tak\Liveproto\Enums\Endianness;

use Stringable;

use JsonSerializable;

# https://github.com/telegramdesktop/tdesktop/blob/02084be58399076c530de734b9d723f036652f50/Telegram/SourceFiles/storage/details/storage_settings_scheme.cpp #
# https://github.com/telegramdesktop/tdesktop/blob/6e8ac603993bb80cd0d35b1e3fac0cbdd824dea5/Telegram/SourceFiles/core/core_settings.h #
final class TdesktopSession extends AbstractSessions {
	public array $sessions = array();
	public array $extracted = array();

	public const IP_ADDRESSES = array(
		'149.154.175.50',
		'149.154.167.51',
		'149.154.175.100',
		'149.154.167.91',
		'91.108.56.180'
	);
	public const SUPPORTED_VERSIONS = [666];

	public function __construct(string $path,? string $passcode = null,string $session_key = 'data'){
		$path = realpath($path);
		if($path === false){
			throw new \InvalidArgumentException('Directory '.$path.' not found');
		}
		if(basename($path) !== 'tdata'){
			$newpath = $path.DIRECTORY_SEPARATOR.'tdata';
			if(is_dir($newpath)){
				$path = $newpath;
			}
		}
		if(is_null($passcode)){
			$files = preg_grep('/^(?:password|pass(?:word|code)?|2fa|2-?fa)[\w\-.]*\.txt$/i',scandir($path));
			foreach($files as $file){
				if(is_file($file) and filesize($file) > 0){
					$passcode = $path.DIRECTORY_SEPARATOR.$file;
					break;
				}
			}
		}
		if(is_string($passcode) and is_file($passcode) and filesize($passcode) > 0){
			$passcode = @file_get_contents($passcode) ?: null;
		}
		$md5 = self::md5($session_key);
		$stream = self::open($path.DIRECTORY_SEPARATOR.$md5.DIRECTORY_SEPARATOR.'map');
		$salt = self::read_bytes($stream)->read();
		if(empty($salt)){
			$stream = self::open($path.DIRECTORY_SEPARATOR.'key_'.$session_key);
			$salt = self::read_bytes($stream)->read();
			assert(strlen($salt) === 32,new \LengthException('The length of salt should be 32',strlen($salt)));
			$encryptedKey = self::read_bytes($stream);
			$encrypted = self::read_bytes($stream);
			$hash = hash('sha512',$salt.strval($passcode).$salt,true);
			$passKey = hash_pbkdf2('sha512',$hash,$salt,empty($passcode) ? 1 : 100000,256,true);
			$decryptedKey = self::read_bytes(self::decrypt($encryptedKey,$passKey))->read();
			$decrypted = self::read_bytes(self::decrypt($encrypted,$decryptedKey));
			$count = self::read_int($decrypted);
			for($i = 0;$i < $count;$i++){
				$number = self::read_int($decrypted);
				if($number >= 0){
					$md5 = self::md5($session_key.strval($number > 0 ? chr(35).strval(++$number) : null));
				}
			}
		} else {
			$encryptedKey = self::read_bytes($stream);
			$passKey = hash_pbkdf2('sha1',strval($passcode),$salt,empty($passcode) ? 4 : 4000,256,true);
			$decryptedKey = self::read_bytes(self::decrypt($encryptedKey,$passKey))->read();
		}
		$stream = self::open($path.DIRECTORY_SEPARATOR.$md5);
		$tdata = self::read_bytes($stream);
		$info = self::decrypt($tdata,$decryptedKey);
		$size = intval($info->tellLength() - $info->tellPosition());
		$length = $info->readInt();
		$padding = intval($size - $length);
		if($padding < 0){
			throw new \LengthException('The length of the T-data buffer is not valid',$padding);
		}
		$this->readSettings($info,$padding);
	}
	public function readSettings(Binary $binary,int $padding = 0) : void {
		while(intval($remaining = $binary->tellLength() - $binary->tellPosition()) > $padding){
			$blockId = self::read_int($binary);
			$dbi = Storage::fromId($blockId);
			switch($dbi){
				case Storage::dbiDcOptionOldOld:
					$dc_id = self::read_int($binary); // dcId //
					$host = self::read_bytes($binary); // host //
					$ip = self::read_bytes($binary); // ip //
					$port = self::read_int($binary); // port //
					$this->extracted[$dbi] []= compact('dc_id','host','ip','port');
					break;

				case Storage::dbiDcOptionOld:
					$dc_id = self::read_int($binary); // dcIdWithShift //
					$flags = self::read_int($binary); // flags //
					$ip = self::read_bytes($binary); // ip //
					$port = self::read_int($binary); // port //
					$this->extracted[$dbi] []= compact('dc_id','flags','ip','port');
					break;

				case Storage::dbiDcOptionsOld:
					$stream = self::read_bytes($binary); // serialized //
					$minusVersion = self::read_int($stream);
					$version = boolval($minusVersion < 0) ? abs($minusVersion) : 0;
					$count = $version > 0 ? self::read_int($stream) : $minusVersion;
					for($i = 0; $i < $count; $i++){
						$dc_id = self::read_int($stream); // id //
						$flags = self::read_int($stream); // flags //
						$port = self::read_int($stream); // port //
						$secret = $version > 0 ? self::read_bytes($stream) : null;
						$this->extracted[$dbi] []= compact('dc_id','flags','port','secret');
					}
					break;

				case Storage::dbiApplicationSettings:
					self::read_bytes($binary); // serialized //
					break;

				case Storage::dbiChatSizeMaxOld:
					self::read_int($binary); // maxSize //
					break;

				case Storage::dbiSavedGifsLimitOld:
					self::read_int($binary); // limit //
					break;

				case Storage::dbiStickersRecentLimitOld:
					self::read_int($binary); // limit //
					break;

				case Storage::dbiStickersFavedLimitOld:
					self::read_int($binary); // limit //
					break;

				case Storage::dbiMegagroupSizeMaxOld:
					self::read_int($binary); // maxSize //
					break;

				case Storage::dbiUser:
					$user_id = self::read_int($binary); // userId //
					$dc_id = self::read_int($binary); // dcId //
					$this->extracted[$dbi] []= compact('user_id','dc_id');
					break;

				case Storage::dbiKey:
					$dc_id = self::read_int($binary); // dcId //
					/*
					 * $binary->read(0x100);
					 * or
					 * self::read_bytes($binary);
					 * ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
					 */
					$auth_key = $binary->read(0x100); // authKey //
					$this->sessions []= new Session(dc_id : $dc_id,ip : self::IP_ADDRESSES[$dc_id - 1],port : 443,auth_key : $auth_key);
					break;

				/*
				 * https://github.com/telegramdesktop/tdesktop/blob/02084be58399076c530de734b9d723f036652f50/Telegram/SourceFiles/main/main_account.cpp#L294
				 */
				case Storage::dbiMtpAuthorization:
					$stream = self::read_bytes($binary); // serialized //
					self::read_long($stream); // wideTag //
					self::read_long($stream); // userId //
					self::read_int($stream); // mainDcId //
					$count = self::read_int($stream);
					for($i = 0; $i < $count; $i++){
						$dc_id = self::read_int($stream); // dcId //
						$auth_key = $stream->read(0x100); // authKey //
						if($dc_id >= 1 and $dc_id <= 5){
							$this->sessions []= new Session(dc_id : $dc_id,ip : self::IP_ADDRESSES[$dc_id - 1],port : 443,auth_key : $auth_key);
						}
					}
					break;

				case Storage::dbiAutoStart:
				case Storage::dbiStartMinimized:
				case Storage::dbiSendToMenu:
				case Storage::dbiUseExternalVideoPlayerOld:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiCacheSettingsOld:
					self::read_long($binary); // size //
					self::read_int($binary); // time //
					break;

				case Storage::dbiCacheSettings:
					self::read_long($binary); // size //
					self::read_int($binary); // time //
					self::read_long($binary); // sizeBig //
					self::read_int($binary); // timeBig //
					break;

				case Storage::dbiPowerSaving:
					self::read_int($binary); // settings //
					break;

				case Storage::dbiSoundFlashBounceNotifyOld:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiAutoDownloadOld:
					self::read_bool($binary); // photo //
					self::read_bool($binary); // audio //
					self::read_bool($binary); // gif //
					break;

				case Storage::dbiAutoPlayOld:
					self::read_bool($binary); // gif //
					break;

				case Storage::dbiDialogsModeOld:
					self::read_bool($binary); // enabled //
					// mode //
					match(self::read_int($binary)){
						// 0 => 'All', //
						1 => 'Important',
						default => 'All'
					};
					break;

				case Storage::dbiDialogsFiltersOld:
				case Storage::dbiModerateModeOld:
					self::read_bool($binary); // enabled //
					break;

				case Storage::dbiIncludeMutedOld:
				case Storage::dbiShowingSavedGifsOld:
				case Storage::dbiDesktopNotifyOld:
				case Storage::dbiNativeNotificationsOld:
				case Storage::dbiWindowsNotificationsOld:
				case Storage::dbiLastSeenWarningSeenOld:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiNotificationsCountOld:
				case Storage::dbiDialogsWidthRatioOld:
					self::read_int($binary); // v //
					break;

				case Storage::dbiNotificationsCornerOld:
					// corner //
					match(self::read_int($binary)){
						0 => 'TopLeft',
						1 => 'TopRight',
						2 => 'BottomRight',
						3 => 'BottomLeft',
						default => 'Unknown'
					};
					break;

				case Storage::dbiSessionSettings:
					$stream = self::read_bytes($binary); // serialized //
					break;

				case Storage::dbiWorkModeOld:
					// mode //
					match(self::read_int($binary)){
						0 => 'WindowAndTray',
						1 => 'TrayOnly',
						2 => 'WindowOnly',
						default => 'Unknown'
					};
					break;

				case Storage::dbiTxtDomainStringOldOld:
				case Storage::dbiTxtDomainStringOld:
					self::read_bytes($binary); // v //
					break;

				case Storage::dbiConnectionTypeOldOld:
					$v = self::read_int($binary); // v //
					$pMode = ProxyMode::fromId($v);
					if($pMode === ProxyMode::dbictHttpProxy or $pMode === ProxyMode::dbictTcpProxy){
						$host = self::read_bytes($binary); // host //
						$port = self::read_int($binary); // port //
						$user = self::read_bytes($binary); // user //
						$password = self::read_bytes($binary); // password //
						$this->extracted[$dbi] []= compact('host','port','user','password');
					}
					break;

				case Storage::dbiConnectionTypeOld:
					$connectionType = self::read_int($binary); // connectionType //
					$pMode = ProxyMode::fromId($connectionType);
					$readProxy = function() : void {
						$proxy_type = self::read_int($binary); // proxyType //
						$host = self::read_bytes($binary); // host //
						$port = self::read_int($binary); // port //
						$user = self::read_bytes($binary); // user //
						$password = self::read_bytes($binary); // password //
						$this->extracted[$dbi] []= compact('proxy_type','host','port','user','password');
					};
					if($pMode === ProxyMode::dbictProxiesListOld or $pMode === ProxyMode::dbictProxiesList){
						$count = self::read_int($binary);
						$index = self::read_int($binary);
						$settings = 0;
						$calls = false;
						if($pMode === ProxyMode::dbictProxiesList){
							$settings = self::read_int($binary);
							$calls = self::read_bool($binary);
						} elseif(abs($index) > $count){
							$calls = true;
							$index -= intval($index > 0 ? $count : - $count);
						}
						for($i = 0; $i < $count; $i++){
							$readProxy();
						}
					} else {
						$readProxy();
					}
					break;

				case Storage::dbiThemeKeyOld:
					self::read_long($binary); // key //
					break;

				case Storage::dbiThemeKey:
					self::read_long($binary); // keyDay //
					self::read_long($binary); // keyNight //
					self::read_bool($binary); // nightMode //
					break;

				case Storage::dbiBackgroundKey:
					self::read_long($binary); // keyDay //
					self::read_long($binary); // keyNight //
					break;

				case Storage::dbiLangPackKey:
					self::read_long($binary); // langPackKey //
					break;

				case Storage::dbiLanguagesKey:
					self::read_long($binary); // languagesKey //
					break;

				case Storage::dbiTryIPv6Old:
				case Storage::dbiSeenTrayTooltip:
				case Storage::dbiAutoUpdate:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiLastUpdateCheck:
				case Storage::dbiScaleOld:
				case Storage::dbiScalePercent:
					self::read_int($binary); // v //
					break;

				case Storage::dbiLangOld: // deprecated //
					self::read_int($binary); // v //
					break;

				case Storage::dbiLangFileOld: // deprecated //
					self::read_bytes($binary); // v //
					break;

				case Storage::dbiWindowPositionOld:
					self::read_int($binary); // x //
					self::read_int($binary); // y //
					self::read_int($binary); // w //
					self::read_int($binary); // h //
					self::read_int($binary); // moncrc //
					self::read_int($binary); // maximized //
					break;

				case Storage::dbiLoggedPhoneNumberOld: // deprecated //
					self::read_bytes($binary); // v //
					break;

				case Storage::dbiMutePeerOld: // deprecated //
					self::read_long($binary); // peerId //
					break;

				case Storage::dbiMutedPeersOld: // deprecated //
					$count = self::read_int($binary); // count //
					for($i = 0; $i < $count; $i++){
						self::read_long($binary); // peerId //
					}
					break;

				case Storage::dbiSendKeyOld:
					// sendKey //
					match(self::read_int($binary)){
						0 => 'Enter',
						1 => 'CtrlEnter',
						default => 'Unknown'
					};
					break;

				case Storage::dbiCatsAndDogsOld: // deprecated //
					self::read_int($binary); // v //
					break;

				case Storage::dbiTileBackgroundOld:
					self::read_int($binary); // v //
					break;

				case Storage::dbiTileBackground:
					self::read_bool($binary); // tileDay //
					self::read_bool($binary); // tileNight //
					break;

				case Storage::dbiAdaptiveForWideOld:
				case Storage::dbiReplaceEmojiOld:
				case Storage::dbiSuggestEmojiOld:
				case Storage::dbiSuggestStickersByEmojiOld:
				case Storage::dbiDefaultAttach:
				case Storage::dbiAskDownloadPathOld:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiAutoLockOld:
					self::read_int($binary); // seconds //
					break;

				case Storage::dbiNotifyViewOld:
					// notifyView //
					match(self::read_int($binary)){
						0 => 'ShowPreview',
						1 => 'ShowName',
						2 => 'ShowNothing',
						default => 'Unknown'
					};

				case Storage::dbiDownloadPathOldOld:
					self::read_bytes($binary); // v //
					break;

				case Storage::dbiDownloadPathOld:
					self::read_bytes($binary); // v //
					self::read_bytes($binary); // bookmark //
					break;

				case Storage::dbiCompressPastedImageOld:
					self::read_bool($binary); // v //
					break;

				case Storage::dbiEmojiTabOld: // deprecated //
					self::read_int($binary); // v //
					break;

				case Storage::dbiRecentEmojiOldOldOld:
					self::read_int($binary); // int32 //
					self::read_short($binary); // short //
				break;

				case Storage::dbiRecentEmojiOldOld:
					self::read_long($binary); // int64 //
					self::read_short($binary); // short //
				break;

				case Storage::dbiRecentEmojiOld:
					$count = self::read_int($binary); // count //
					for($i = 0; $i < $count; $i++){
						self::read_bytes($binary); // string //
						self::read_short($binary); // short //
					}
					break;

				case Storage::dbiRecentStickers:
					self::read_long($binary); // int64 //
					self::read_short($binary); // short //
					break;

				case Storage::dbiEmojiVariantsOldOld:
					self::read_int($binary); // int32 //
					self::read_long($binary); // int64 //
				break;

				case Storage::dbiEmojiVariantsOld:
					$count = self::read_int($binary); // count //
					for($i = 0; $i < $count; $i++){
						self::read_bytes($binary); // string //
						self::read_int($binary); // int32 //
					}
				break;

				case Storage::dbiHiddenPinnedMessagesOld:
					$count = self::read_int($binary); // count //
					for($i = 0; $i < $count; $i++){
						self::read_long($binary); // peerId //
						self::read_int($binary); // msgId //
					}
					break;

				case Storage::dbiDialogLastPath:
					self::read_bytes($binary); // path //
					break;

				case Storage::dbiSongVolumeOld:
				case Storage::dbiVideoVolumeOld:
				case Storage::dbiPlaybackSpeedOld:
					self::read_int($binary); // v //
					break;

				case Storage::dbiCallSettingsOld:
					self::read_bytes($binary); // serialized //
					break;

				case Storage::dbiFallbackProductionConfig:
					self::read_bytes($binary); // serialized //
					break;

				default:
					error_log('Unknown blockId : '.$blockId.' , Remaining bytes : '.$remaining);
					break 2;
			}
		}
	}
	static public function md5(string $data) : string {
		return strtoupper(substr(preg_replace_callback('/(..)/',fn(array $m) : string => strrev($m[1]),md5($data)),0,16));
	}
	static public function read_short(Binary $binary) : ? int {
		return Helper::unpack(format : 's',string : $binary->read(2),byteorder : Endianness::BIG);
	}
	static public function read_int(Binary $binary) : ? int {
		return Helper::unpack(format : 'l',string : $binary->read(4),byteorder : Endianness::BIG);
	}
	static public function read_long(Binary $binary) : ? int {
		return Helper::unpack(format : 'q',string : $binary->read(8),byteorder : Endianness::BIG);
	}
	static public function read_bool(Binary $binary) : bool {
		return boolval(self::read_int($binary) === 1);
	}
	static public function read_bytes(Binary $binary) : Binary {
		$length = self::read_int($binary);
		$reader = new Binary();
		if($length > 0){
			$reader->write($binary->read($length));
		}
		return $reader;
	}
	static public function decrypt(Binary $reader,string $auth_key) : Binary {
		$msgKey = $reader->read(16);
		list($key,$iv) = Helper::aesCalculate($auth_key,$msgKey,false);
		$cipher = $reader->read();
		$plain = Aes::decrypt($cipher,$key,$iv);
		$ourKey = sha1($plain,true);
		$ourKey = substr($ourKey,0,16);
		if($msgKey !== $ourKey){
			throw new \ParseError('The message key is invalid ( The passcode may be incorrect or not entered )');
		}
		$reader->write($plain);
		return $reader;
	}
	static public function open(string $file) : Binary {
		foreach(['safe'=>'s','simple'=>'0','backup'=>'1'] as $type => $ext){
			$path = $file.$ext;
			if(is_file($path) and ($content = @file_get_contents($path))){
				$binary = new Binary();
				$binary->write($content);
				$header = $binary->read(4);
				if($header === 'TDF$'){
					$bytes = $binary->read(4);
					$binary->undo();
					$version = $binary->readInt();
					$length = intval($binary->tellLength() - $binary->tellPosition() - 16);
					$binary->writeInt($length);
					$data = $binary->read($length);
					$hash = $binary->read(16);
					if(hash_equals(md5($data.$binary->read(4).$bytes.$header,true),$hash) === false){
						throw new \ParseError('MD5 is wrong ( '.bin2hex($hash).')');
					}
					$binary->write($data);
					return $binary;
				}
			}
		}
		throw new \OutOfBoundsException('The required session data file was not found : '.$file);
	}
	static public function import(Session $session,? string $path = null) : string {
		throw new \BadMethodCallException('Converting to Tdata is not yet supported');
		return $path;
	}
}

?>