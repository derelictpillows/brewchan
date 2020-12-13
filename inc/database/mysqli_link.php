<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

if (!function_exists('mysqli_connect')) {
	fancyDie("MySQL library is not installed");
}

$link = @mysqli_connect(BREWCHAN_DBHOST, BREWCHAN_DBUSERNAME, BREWCHAN_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " . ((is_object($link)) ? mysqli_error($link) : (($link_error = mysqli_connect_error()) ? $link_error : '(unknown error)')));
}
$db_selected = @mysqli_query($link, "USE " . BREWCHAN_DBNAME);
if (!$db_selected) {
	fancyDie("Could not select database: " . ((is_object($link)) ? mysqli_error($link) : (($link_error = mysqli_connect_error()) ? $link_error : '(unknown error')));
}
mysqli_query($link, "SET NAMES 'utf8'");

// Create the posts table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . BREWCHAN_DBPOSTS . "'")) == 0) {
	mysqli_query($link, $posts_sql);
}

// Create the bans table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . BREWCHAN_DBBANS . "'")) == 0) {
	mysqli_query($link, $bans_sql);
}

// Create the reports table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . BREWCHAN_DBREPORTS . "'")) == 0) {
	mysqli_query($link, $reports_sql);
}

// Create the keywords table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . BREWCHAN_DBKEYWORDS . "'")) == 0) {
	mysqli_query($link, $keywords_sql);
}

if (mysqli_num_rows(mysqli_query($link, "SHOW COLUMNS FROM `" . BREWCHAN_DBPOSTS . "` LIKE 'stickied'")) == 0) {
	mysqli_query($link, "ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ADD COLUMN stickied TINYINT(1) NOT NULL DEFAULT '0'");
}

if (mysqli_num_rows(mysqli_query($link, "SHOW COLUMNS FROM `" . BREWCHAN_DBPOSTS . "` LIKE 'locked'")) == 0) {
	mysqli_query($link, "ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ADD COLUMN locked TINYINT(1) NOT NULL DEFAULT '0'");
}

mysqli_query($link, "ALTER TABLE `" . BREWCHAN_DBPOSTS . "` MODIFY tripcode VARCHAR(24) NOT NULL DEFAULT ''");

mysqli_query($link, "ALTER TABLE `" . BREWCHAN_DBPOSTS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
mysqli_query($link, "ALTER TABLE `" . BREWCHAN_DBBANS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");

if (function_exists('insertPost')) {
	function migratePost($post) {
		global $link;
		mysqli_query($link, "INSERT INTO `" . BREWCHAN_DBPOSTS . "` (`id`, `parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`, `moderated`, `stickied`, `locked`) VALUES (" . $post['id'] . ", " . $post['parent'] . ", " . $post['timestamp'] . ", " . $post['bumped'] . ", '" . mysqli_real_escape_string($link, $post['ip']) . "', '" . mysqli_real_escape_string($link, $post['name']) . "', '" . mysqli_real_escape_string($link, $post['tripcode']) . "',      '" . mysqli_real_escape_string($link, $post['email']) . "',     '" . mysqli_real_escape_string($link, $post['nameblock']) . "', '" . mysqli_real_escape_string($link, $post['subject']) . "', '" . mysqli_real_escape_string($link, $post['message']) . "', '" . mysqli_real_escape_string($link, $post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysqli_real_escape_string($link, $post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ", " . $post['stickied'] . ", " . $post['locked'] . ")");
	}

	function migrateBan($ban) {
		global $link;
		sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (" . mysqli_real_escape_string($link, $ban['id']) . "', '" . mysqli_real_escape_string($link, $ban['ip']) . "', '" . mysqli_real_escape_string($link, $ban['timestamp']) . "', '" . mysqli_real_escape_string($link, $ban['expire']) . "', '" . mysqli_real_escape_string($link, $ban['reason']) . "')");
	}

	function migrateReport($report) {
		global $link;
		sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBREPORTS . " (id, ip, post) VALUES ('" . mysqli_real_escape_string($link, $report['id']) . "', '" . mysqli_real_escape_string($link, $report['ip']) . "', '" . mysqli_real_escape_string($link, $report['post']) . "')");
	}

	function migrateKeyword($keyword) {
		global $link;
		sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBKEYWORDS . " (id, text, action) VALUES ('" . mysqli_real_escape_string($link, $keyword['id']) . "', '" . mysqli_real_escape_string($link, $keyword['text']) . "', '" . mysqli_real_escape_string($link, $keyword['action']) . "')");
	}
}
