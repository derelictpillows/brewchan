<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

if (BREWCHAN_DBDSN == '') { // Build a default (likely MySQL) DSN
	$dsn = BREWCHAN_DBDRIVER . ":host=" . BREWCHAN_DBHOST;
	if (BREWCHAN_DBPORT > 0) {
		$dsn .= ";port=" . BREWCHAN_DBPORT;
	}
	$dsn .= ";dbname=" . BREWCHAN_DBNAME;
} else { // Use a custom DSN
	$dsn = BREWCHAN_DBDSN;
}

if (BREWCHAN_DBDRIVER === 'pgsql') {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
} else {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
}

try {
	$dbh = new PDO($dsn, BREWCHAN_DBUSERNAME, BREWCHAN_DBPASSWORD, $options);
} catch (PDOException $e) {
	fancyDie("Failed to connect to the database: " . $e->getMessage());
}

// Create the posts table if it does not exist
if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(BREWCHAN_DBPOSTS);
	$posts_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(BREWCHAN_DBPOSTS));
	$posts_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$posts_exists) {
	$dbh->exec($posts_sql);
}

// Create the bans table if it does not exist
if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(BREWCHAN_DBBANS);
	$bans_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(BREWCHAN_DBBANS));
	$bans_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$bans_exists) {
	$dbh->exec($bans_sql);
}

// Create the reports table if it does not exist
if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(BREWCHAN_DBREPORTS);
	$reports_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(BREWCHAN_DBREPORTS));
	$reports_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$reports_exists) {
	$dbh->exec($reports_sql);
}

// Create the keywords table if it does not exist
if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(BREWCHAN_DBKEYWORDS);
	$keywords_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(BREWCHAN_DBKEYWORDS));
	$keywords_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$keywords_exists) {
	$dbh->exec($keywords_sql);
}

if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . BREWCHAN_DBPOSTS . "' and column_name='moderated'";
	$moderated_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . BREWCHAN_DBPOSTS . "` LIKE 'stickied'");
	$moderated_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$moderated_exists) {
	$dbh->exec("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ADD COLUMN moderated TINYINT(1) NOT NULL DEFAULT '0'");
}
if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . BREWCHAN_DBPOSTS . "' and column_name='stickied'";
	$stickied_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . BREWCHAN_DBPOSTS . "` LIKE 'stickied'");
	$stickied_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$stickied_exists) {
	$dbh->exec("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ADD COLUMN stickied TINYINT(1) NOT NULL DEFAULT '0'");
}

if (BREWCHAN_DBDRIVER === 'pgsql') {
	$query = "SELECT column_name FROM information_schema.columns WHERE table_name='" . BREWCHAN_DBPOSTS . "' and column_name='locked'";
	$locked_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW COLUMNS FROM `" . BREWCHAN_DBPOSTS . "` LIKE 'locked'");
	$locked_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$locked_exists) {
	$dbh->exec("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ADD COLUMN locked TINYINT(1) NOT NULL DEFAULT '0'");
}

if (BREWCHAN_DBDRIVER === 'pgsql') {
	$dbh->query("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ALTER COLUMN tripcode VARCHAR(24) NOT NULL DEFAULT ''");

	$dbh->query("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` ALTER COLUMN ip VARCHAR(255) NOT NULL DEFAULT ''");
	$dbh->query("ALTER TABLE `" . BREWCHAN_DBBANS . "` ALTER COLUMN ip VARCHAR(255) NOT NULL DEFAULT ''");
} else {
	$dbh->query("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` MODIFY tripcode VARCHAR(24) NOT NULL DEFAULT ''");

	$dbh->query("ALTER TABLE `" . BREWCHAN_DBPOSTS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
	$dbh->query("ALTER TABLE `" . BREWCHAN_DBBANS . "` MODIFY ip VARCHAR(255) NOT NULL DEFAULT ''");
}

function pdoQuery($sql, $params = false) {
	global $dbh;

	if ($params) {
		$statement = $dbh->prepare($sql);
		$statement->execute($params);
	} else {
		$statement = $dbh->query($sql);
	}

	return $statement;
}

if (function_exists('insertPost')) {
	function migratePost($post) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBPOSTS . " (id, parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated, stickied, locked) " .
			" VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stm->execute(array($post['id'], $post['parent'], $post['timestamp'], $post['bumped'], $post['ip'], $post['name'], $post['tripcode'], $post['email'],
			$post['nameblock'], $post['subject'], $post['message'], $post['password'],
			$post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
			$post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'], $post['moderated'], $post['stickied'], $post['locked']));
	}

	function migrateBan($ban) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBBANS . " (id, ip, timestamp, expire, reason) VALUES (?, ?, ?, ?, ?)");
		$stm->execute(array($ban['id'], $ban['ip'], $ban['timestamp'], $ban['expire'], $ban['reason']));
	}

	function migrateReport($report) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBREPORTS . " (id, ip, post) VALUES (?, ?, ?)");
		$stm->execute(array($report['id'], $report['ip'], $report['post']));
	}

	function migrateKeyword($keyword) {
		global $dbh;
		$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBKEYWORDS . " (id, text, action) VALUES (?, ?, ?)");
		$stm->execute(array($keyword['id'], $keyword['text'], $keyword['action']));
	}
}
