<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

define('BREWCHAN_NEWTHREAD', '0');
define('BREWCHAN_INDEXPAGE', false);
define('BREWCHAN_RESPAGE', true);
define('BREWCHAN_WORDBREAK_IDENTIFIER', '@!@BREWCHAN_WORDBREAK@!@');

// The following are provided for backward compatibility and should not be relied upon
// Copy new settings from settings.default.php to settings.php
if (!defined('BREWCHAN_LOCALE')) {
	define('BREWCHAN_LOCALE', '');
}
if (!defined('BREWCHAN_INDEX')) {
	define('BREWCHAN_INDEX', 'index.html');
}
if (!defined('BREWCHAN_MAXREPLIES')) {
	define('BREWCHAN_MAXREPLIES', 0);
}
if (!defined('BREWCHAN_MAXWOP')) {
	define('BREWCHAN_MAXWOP', BREWCHAN_MAXW);
}
if (!defined('BREWCHAN_MAXHOP')) {
	define('BREWCHAN_MAXHOP', BREWCHAN_MAXH);
}
if (!defined('BREWCHAN_THUMBNAIL')) {
	define('BREWCHAN_THUMBNAIL', 'gd');
}
if (!defined('BREWCHAN_UPLOADVIAURL')) {
	define('BREWCHAN_UPLOADVIAURL', false);
}
if (!defined('BREWCHAN_NOFILEOK')) {
	define('BREWCHAN_NOFILEOK', false);
}
if (!defined('BREWCHAN_CAPTCHA')) {
	define('BREWCHAN_CAPTCHA', '');
}
if (!defined('BREWCHAN_MANAGECAPTCHA')) {
	define('BREWCHAN_MANAGECAPTCHA', '');
}
if (!defined('BREWCHAN_REPORT')) {
	define('BREWCHAN_REPORT', '');
}
if (!defined('BREWCHAN_REQMOD')) {
	define('BREWCHAN_REQMOD', '');
}
if (!defined('BREWCHAN_ALWAYSNOKO')) {
	define('BREWCHAN_ALWAYSNOKO', false);
}
if (!defined('BREWCHAN_WORDBREAK')) {
	define('BREWCHAN_WORDBREAK', 0);
}
if (!defined('BREWCHAN_TIMEZONE')) {
	define('BREWCHAN_TIMEZONE', '');
}
if (!defined('BREWCHAN_CATALOG')) {
	define('BREWCHAN_CATALOG', true);
}
if (!defined('BREWCHAN_JSON')) {
	define('BREWCHAN_JSON', true);
}
if (!defined('BREWCHAN_DATEFMT')) {
	define('BREWCHAN_DATEFMT', '%g/%m/%d(%a)%H:%M:%S');
}
if (!defined('BREWCHAN_DBMIGRATE')) {
	define('BREWCHAN_DBMIGRATE', false);
}
if (!defined('BREWCHAN_DBREPORTS')) {
	define('BREWCHAN_DBREPORTS', BREWCHAN_BOARD . '_reports');
}
if (!defined('BREWCHAN_DBPORT')) {
	define('BREWCHAN_DBPORT', 3306);
}
if (!defined('BREWCHAN_DBDRIVER')) {
	define('BREWCHAN_DBDRIVER', 'pdo');
}
if (!defined('BREWCHAN_DBDSN')) {
	define('BREWCHAN_DBDSN', '');
}
if (!defined('BREWCHAN_DBPATH')) {
	define('BREWCHAN_DBPATH', 'BREWCHAN.db');
}
if (!isset($BREWCHAN_hidefieldsop)) {
	$BREWCHAN_hidefieldsop = array();
}
if (!isset($BREWCHAN_hidefields)) {
	$BREWCHAN_hidefields = array();
}
if (!isset($BREWCHAN_uploads)) {
	$BREWCHAN_uploads = array();
	if (defined('BREWCHAN_PIC') && BREWCHAN_PIC) {
		$BREWCHAN_uploads['image/jpeg'] = array('jpg');
		$BREWCHAN_uploads['image/pjpeg'] = array('jpg');
		$BREWCHAN_uploads['image/png'] = array('png');
		$BREWCHAN_uploads['image/gif'] = array('gif');
	}
	if (defined('BREWCHAN_SWF') && BREWCHAN_SWF) {
		$BREWCHAN_uploads['application/x-shockwave-flash'] = array('swf', 'swf_thumbnail.png');
	}
	if (defined('BREWCHAN_WEBM') && BREWCHAN_WEBM) {
		$BREWCHAN_uploads['video/webm'] = array('webm');
		$BREWCHAN_uploads['audio/webm'] = array('webm');
	}
}
if (!isset($BREWCHAN_embeds)) {
	$BREWCHAN_embeds = array();
	if (defined('BREWCHAN_EMBED') && BREWCHAN_EMBED) {
		$BREWCHAN_embeds['SoundCloud'] = 'https://soundcloud.com/oembed?format=json&url=BREWCHANEMBED';
		$BREWCHAN_embeds['Vimeo'] = 'https://vimeo.com/api/oembed.json?url=BREWCHANEMBED';
		$BREWCHAN_embeds['YouTube'] = 'https://www.youtube.com/oembed?url=BREWCHANEMBED&format=json';
	}
}
if (!isset($BREWCHAN_capcodes)) {
	$BREWCHAN_capcodes = array(array('Admin', 'red'), array('Mod', 'purple'));
}
