<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

// Post functions
function uniquePosts() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . BREWCHAN_DBPOSTS . ")"));
}

function postByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function threadExistsByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE id = '" . sqlite_escape_string($id) . "' AND parent = 0 LIMIT 1")) > 0;
}

function insertPost($post) {
	sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBPOSTS . " (parent, timestamp, bumped, ip, name, tripcode, email, nameblock, subject, message, password, file, file_hex, file_original, file_size, file_size_formatted, image_width, image_height, thumb, thumb_width, thumb_height) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . hashData($_SERVER['REMOTE_ADDR']) . "', '" . sqlite_escape_string($post['name']) . "', '" . sqlite_escape_string($post['tripcode']) . "',	'" . sqlite_escape_string($post['email']) . "',	'" . sqlite_escape_string($post['nameblock']) . "', '" . sqlite_escape_string($post['subject']) . "', '" . sqlite_escape_string($post['message']) . "', '" . sqlite_escape_string($post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . sqlite_escape_string($post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ")");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function approvePostByID($id) {
	sqlite_query($GLOBALS["db"], "UPDATE " . BREWCHAN_DBPOSTS . " SET moderated = 1 WHERE id = " . $id);
}

function bumpThreadByID($id) {
	sqlite_query($GLOBALS["db"], "UPDATE " . BREWCHAN_DBPOSTS . " SET bumped = " . time() . " WHERE id = " . $id);
}

function stickyThreadByID($id, $setsticky) {
	sqlite_query($GLOBALS["db"], "UPDATE " . BREWCHAN_DBPOSTS . " SET stickied = '" . sqlite_escape_string($setsticky) . "' WHERE id = " . $id);
}

function lockThreadByID($id, $setlock) {
	sqlite_query($GLOBALS["db"], "UPDATE " . BREWCHAN_DBPOSTS . " SET locked = '" . sqlite_escape_string($setlock) . "' WHERE id = " . $id);
}

function countThreads() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0"));
}

function allThreads() {
	$threads = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0 ORDER BY stickied DESC, bumped DESC"), SQLITE_ASSOC);
	foreach ($result as $thread) {
		$threads[] = $thread;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"], "SELECT COUNT(*) FROM " . BREWCHAN_DBPOSTS . " WHERE parent = " . $id));
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE id = " . $id . " OR parent = " . $id . " ORDER BY id ASC"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
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
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT id, parent FROM " . BREWCHAN_DBPOSTS . " WHERE file_hex = '" . sqlite_escape_string($hex) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBPOSTS . " ORDER BY timestamp DESC LIMIT 10"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBPOSTS . " WHERE id = " . sqlite_escape_string($id));
}

function trimThreads() {
	if (BREWCHAN_MAXTHREADS > 0) {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT id FROM " . BREWCHAN_DBPOSTS . " WHERE parent = 0 ORDER BY stickied DESC, bumped DESC LIMIT " . BREWCHAN_MAXTHREADS . ", 10"), SQLITE_ASSOC);
		foreach ($result as $post) {
			deletePost($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBPOSTS . " WHERE ip = '" . sqlite_escape_string($_SERVER['REMOTE_ADDR']) . "' OR ip = '" . sqlite_escape_string(hashData($_SERVER['REMOTE_ADDR'])) . "' ORDER BY id DESC LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

// Ban functions
function banByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBBANS . " WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function banByIP($ip) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBBANS . " WHERE ip = '" . sqlite_escape_string($ip) . "' OR ip = '" . sqlite_escape_string(hashData($ip)) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function allBans() {
	$bans = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBBANS . " ORDER BY timestamp DESC"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBBANS . " (ip, timestamp, expire, reason) VALUES ('" . sqlite_escape_string(hashData($ban['ip'])) . "', " . time() . ", '" . sqlite_escape_string($ban['expire']) . "', '" . sqlite_escape_string($ban['reason']) . "')");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function clearExpiredBans() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBBANS . " WHERE expire > 0 AND expire <= " . time()), SQLITE_ASSOC);
	foreach ($result as $ban) {
		sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBBANS . " WHERE id = " . $ban['id']);
	}
}

function deleteBanByID($id) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBBANS . " WHERE id = " . sqlite_escape_string($id));
}

// Report functions
function reportByIP($post, $ip) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBREPORTS . " WHERE post = '" . sqlite_escape_string($post) . "' AND (ip = '" . sqlite_escape_string($ip) . "' OR ip = '" . sqlite_escape_string(hashData($ip)) . "') LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $report) {
		return $report;
	}
}

function reportByPost($post) {
	$reports = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBREPORTS . " WHERE post = '" . sqlite_escape_string($post) . "'"), SQLITE_ASSOC);
	foreach ($result as $report) {
		$reports[] = $report;
	}
	return $reports;
}

function allReports() {
	$reports = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBREPORTS . " ORDER BY post ASC"), SQLITE_ASSOC);
	foreach ($result as $report) {
		$reports[] = $report;
	}
	return $reports;
}

function insertReport($report) {
	sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBREPORTS . " (ip, post) VALUES ('" . sqlite_escape_string(hashData($report['ip'])) . "', '" . sqlite_escape_string($report['post']) . "')");
}

function deleteReportsByPost($post) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBREPORTS . " WHERE post = " . sqlite_escape_string($post));
}

function deleteReportsByIP($ip) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBREPORTS . " WHERE ip = '" . sqlite_escape_string($ip) . "' OR ip = '" . sqlite_escape_string(hashData($ip)) . "'");
}

// Keyword functions
function keywordByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBKEYWORDS . " WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $keyword) {
		return $keyword;
	}
	return array();
}

function keywordByText($text) {
	$text = strtolower($text);
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBKEYWORDS . " WHERE text = '" . sqlite_escape_string($text) . "'"), SQLITE_ASSOC);
	foreach ($result as $keyword) {
		return $keyword;
	}
	return array();
}

function allKeywords() {
	$keywords = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"], "SELECT * FROM " . BREWCHAN_DBKEYWORDS . " ORDER BY text ASC"), SQLITE_ASSOC);
	foreach ($result as $keyword) {
		$keywords[] = $keyword;
	}
	return $keywords;
}

function insertKeyword($keyword) {
	$keyword['text'] = strtolower($keyword['text']);
	sqlite_query($GLOBALS["db"], "INSERT INTO " . BREWCHAN_DBKEYWORDS . " (text, action) VALUES ('" . sqlite_escape_string($keyword['text']) . "', '" . sqlite_escape_string($keyword['action']) . "')");
}

function deleteKeyword($id) {
	sqlite_query($GLOBALS["db"], "DELETE FROM " . BREWCHAN_DBKEYWORDS . " WHERE id = " . sqlite_escape_string($id));
}
