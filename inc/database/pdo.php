<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

// Post functions
function uniquePosts() {
	$result = pdoQuery("SELECT COUNT(DISTINCT(ip)) FROM " . BREWCHAN_DBPOSTS);
	return (int)$result->fetchColumn();
}

function postByID($id) {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE id = ?", array($id));
	if ($result) {
		return $result->fetch();
	}
}

function threadExistsByID($id) {
	$result = pdoQuery("SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE id = ? AND parent = 0 AND moderated = 1", array($id));
	return $result->fetchColumn() != 0;
}

function insertPost($post) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email,   nameblock, subject, message, password,   file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height, moderated) " .
		" VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stm->execute(array($post['parent'], $now, $now, hashData($_SERVER['REMOTE_ADDR']), $post['name'], $post['tripcode'], $post['email'],
		$post['nameblock'], $post['subject'], $post['message'], $post['password'],
		$post['file'], $post['file_hex'], $post['file_original'], $post['file_size'], $post['file_size_formatted'],
		$post['image_width'], $post['image_height'], $post['thumb'], $post['thumb_width'], $post['thumb_height'], $post['moderated']));
	return $dbh->lastInsertId();
}

function approvePostByID($id) {
	pdoQuery("UPDATE " . BREWCHAN_DBPOSTS . " SET moderated = 1 WHERE id = ?", array($id));
}

function bumpThreadByID($id) {
	$now = time();
	pdoQuery("UPDATE " . BREWCHAN_DBPOSTS . " SET bumped = ? WHERE id = ?", array($now, $id));
}

function stickyThreadByID($id, $setsticky) {
	pdoQuery("UPDATE " . BREWCHAN_DBPOSTS . " SET stickied = ? WHERE id = ?", array($setsticky, $id));
}

function lockThreadByID($id, $setlock) {
	pdoQuery("UPDATE " . BREWCHAN_DBPOSTS . " SET locked = ? WHERE id = ?", array($setlock, $id));
}

function countThreads() {
	$result = pdoQuery("SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetchColumn();
}

function allThreads() {
	$threads = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0 AND moderated = 1 ORDER BY stickied DESC, bumped DESC");
	while ($row = $results->fetch()) {
		$threads[] = $row;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	$result = pdoQuery("SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE parent = ? AND moderated = 1", array($id));
	return (int)$result->fetchColumn();
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE (id = ? OR parent = ?)" . ($moderated_only ? " AND moderated = 1" : "") . " ORDER BY id ASC", array($id, $id));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function imagesInThreadByID($id, $moderated_only = true) {
	$images = 0;
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['file'] != '') {
			$images++;
		}
	}
	return $images;
}

function postsByHex($hex) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE file_hex = ? AND moderated = 1 LIMIT 1", array($hex));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE moderated = ? ORDER BY timestamp DESC LIMIT 10", array($moderated ? '1' : '0'));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function deletePostByID($id) {
	pdoQuery("DELETE FROM " . BREWCHAN_DBPOSTS . " WHERE id = ?", array($id));
}

function trimThreads() {
	$limit = (int)BREWCHAN_MAXTHREADS;
	if ($limit > 0) {
		$results = pdoQuery("SELECT id FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0 AND moderated = 1 ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		/*
		old mysql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT $limit,100
		mysql, postgresql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT 100 OFFSET $limit
		oracle: SELECT id FROM ( SELECT id, rownum FROM $table ORDER BY bumped) WHERE rownum >= $limit
		MSSQL: WITH ts AS (SELECT ROWNUMBER() OVER (ORDER BY bumped) AS 'rownum', * FROM $table) SELECT id FROM ts WHERE rownum >= $limit
		*/
		foreach ($results as $post) {
			deletePost($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE ip = ? ORDER BY id DESC LIMIT 1", array($_SERVER['REMOTE_ADDR']));
	return $result->fetch(PDO::FETCH_ASSOC);
}

// Ban functions
function banByID($id) {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBBANS . " WHERE id = ?", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBBANS . " WHERE ip = ? OR ip = ? LIMIT 1", array($ip, hashData($ip)));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function allBans() {
	$bans = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBBANS . " ORDER BY timestamp DESC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$bans[] = $row;
	}
	return $bans;
}

function insertBan($ban) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBBANS . " (ip, timestamp, expire, reason) VALUES (?, ?, ?, ?)");
	$stm->execute(array(hashData($ban['ip']), $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function clearExpiredBans() {
	$now = time();
	pdoQuery("DELETE FROM " . BREWCHAN_DBBANS . " WHERE expire > 0 AND expire <= ?", array($now));
}

function deleteBanByID($id) {
	pdoQuery("DELETE FROM " . BREWCHAN_DBBANS . " WHERE id = ?", array($id));
}

// Report functions
function reportByIP($post, $ip) {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBREPORTS . " WHERE post = ? AND (ip = ? OR ip = ?) LIMIT 1", array($post, $ip, hashData($ip)));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function reportsByPost($post) {
	$reports = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBREPORTS . " WHERE post = ?", array($post));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $row;
	}
	return $reports;
}

function allReports() {
	$reports = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBREPORTS . " ORDER BY post ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $row;
	}
	return $reports;
}

function insertReport($report) {
	global $dbh;
	$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBREPORTS . " (ip, post) VALUES (?, ?)");
	$stm->execute(array(hashData($report['ip']), $report['post']));
}

function deleteReportsByPost($post) {
	pdoQuery("DELETE FROM " . BREWCHAN_DBREPORTS . " WHERE post = ?", array($post));
}

function deleteReportsByIP($ip) {
	pdoQuery("DELETE FROM " . BREWCHAN_DBREPORTS . " WHERE ip = ? OR ip = ?", array($ip, hashData($ip)));
}

// Keyword functions
function keywordByID($id) {
	$result = pdoQuery("SELECT * FROM " . BREWCHAN_DBKEYWORDS . " WHERE id = ? LIMIT 1", array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function keywordByText($text) {
	$text = strtolower($text);
	$keywords = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBKEYWORDS . " WHERE text = ?", array($text));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$keywords[] = $row;
	}
	return $keywords;
}

function allKeywords() {
	$keywords = array();
	$results = pdoQuery("SELECT * FROM " . BREWCHAN_DBKEYWORDS . " ORDER BY text ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$keywords[] = $row;
	}
	return $keywords;
}

function insertKeyword($keyword) {
	global $dbh;
	$keyword['text'] = strtolower($keyword['text']);
	$stm = $dbh->prepare("INSERT INTO " . BREWCHAN_DBKEYWORDS . " (text, action) VALUES (?, ?)");
	$stm->execute(array($keyword['text'], $keyword['action']));
}

function deleteKeyword($id) {
	pdoQuery("DELETE FROM " . BREWCHAN_DBKEYWORDS . " WHERE id = ?", array($id));
}
