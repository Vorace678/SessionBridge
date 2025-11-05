<?php

declare(strict_types = 1);

namespace Tak\SessionBridge\Tdata;

use Throwable;

# https://github.com/telegramdesktop/tdesktop/blob/02084be58399076c530de734b9d723f036652f50/Telegram/SourceFiles/storage/details/storage_settings_scheme.h #
enum StorageSettingsScheme : int {
	case dbiKey = 0x00;
	case dbiUser = 0x01;
	case dbiDcOptionOldOld = 0x02;
	case dbiChatSizeMaxOld = 0x03;
	case dbiMutePeerOld = 0x04;
	case dbiSendKeyOld = 0x05;
	case dbiAutoStart = 0x06;
	case dbiStartMinimized = 0x07;
	case dbiSoundFlashBounceNotifyOld = 0x08;
	case dbiWorkModeOld = 0x09;
	case dbiSeenTrayTooltip = 0x0a;
	case dbiDesktopNotifyOld = 0x0b;
	case dbiAutoUpdate = 0x0c;
	case dbiLastUpdateCheck = 0x0d;
	case dbiWindowPositionOld = 0x0e;
	case dbiConnectionTypeOldOld = 0x0f;

	case dbiDefaultAttach = 0x11;
	case dbiCatsAndDogsOld = 0x12;
	case dbiReplaceEmojiOld = 0x13;
	case dbiAskDownloadPathOld = 0x14;
	case dbiDownloadPathOldOld = 0x15;
	case dbiScaleOld = 0x16;
	case dbiEmojiTabOld = 0x17;
	case dbiRecentEmojiOldOldOld = 0x18;
	case dbiLoggedPhoneNumberOld = 0x19;
	case dbiMutedPeersOld = 0x1a;

	case dbiNotifyViewOld = 0x1c;
	case dbiSendToMenu = 0x1d;
	case dbiCompressPastedImageOld = 0x1e;
	case dbiLangOld = 0x1f;
	case dbiLangFileOld = 0x20;
	case dbiTileBackgroundOld = 0x21;
	case dbiAutoLockOld = 0x22;
	case dbiDialogLastPath = 0x23;
	case dbiRecentEmojiOldOld = 0x24;
	case dbiEmojiVariantsOldOld = 0x25;
	case dbiRecentStickers = 0x26;
	case dbiDcOptionOld = 0x27;
	case dbiTryIPv6Old = 0x28;
	case dbiSongVolumeOld = 0x29;

	case dbiWindowsNotificationsOld = 0x30;
	case dbiIncludeMutedOld = 0x31;
	case dbiMegagroupSizeMaxOld = 0x32;
	case dbiDownloadPathOld = 0x33;
	case dbiAutoDownloadOld = 0x34;
	case dbiSavedGifsLimitOld = 0x35;
	case dbiShowingSavedGifsOld = 0x36;
	case dbiAutoPlayOld = 0x37;
	case dbiAdaptiveForWideOld = 0x38;
	case dbiHiddenPinnedMessagesOld = 0x39;
	case dbiRecentEmojiOld = 0x3a;
	case dbiEmojiVariantsOld = 0x3b;

	case dbiDialogsModeOld = 0x40;
	case dbiModerateModeOld = 0x41;
	case dbiVideoVolumeOld = 0x42;
	case dbiStickersRecentLimitOld = 0x43;
	case dbiNativeNotificationsOld = 0x44;
	case dbiNotificationsCountOld = 0x45;
	case dbiNotificationsCornerOld = 0x46;
	case dbiThemeKeyOld = 0x47;
	case dbiDialogsWidthRatioOld = 0x48;
	case dbiUseExternalVideoPlayerOld = 0x49;
	case dbiDcOptionsOld = 0x4a;
	case dbiMtpAuthorization = 0x4b;
	case dbiLastSeenWarningSeenOld = 0x4c;
	case dbiSessionSettings = 0x4d;
	case dbiLangPackKey = 0x4e;
	case dbiConnectionTypeOld = 0x4f;
	case dbiStickersFavedLimitOld = 0x50;
	case dbiSuggestStickersByEmojiOld = 0x51;
	case dbiSuggestEmojiOld = 0x52;
	case dbiTxtDomainStringOldOld = 0x53;
	case dbiThemeKey = 0x54;
	case dbiTileBackground = 0x55;
	case dbiCacheSettingsOld = 0x56;
	case dbiPowerSaving = 0x57;
	case dbiScalePercent = 0x58;
	case dbiPlaybackSpeedOld = 0x59;
	case dbiLanguagesKey = 0x5a;
	case dbiCallSettingsOld = 0x5b;
	case dbiCacheSettings = 0x5c;
	case dbiTxtDomainStringOld = 0x5d;
	case dbiApplicationSettings = 0x5e;
	case dbiDialogsFiltersOld = 0x5f;
	case dbiFallbackProductionConfig = 0x60;
	case dbiBackgroundKey = 0x61;

	case dbiEncryptedWithSalt = 333;
	case dbiEncrypted = 444;

	case dbiVersion = 666;

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