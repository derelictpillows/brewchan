<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

// Post functions
function uniquePosts() {
	global $link;
	$row = mysqli_fetch_row(mysqli_query($link, "SELECT COUNT(DISTINCT(`ip`)) FROM " . BREWCHAN_DBPOSTS));
	return $row[0];
}

function postByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBPOSTS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			return $post;
		}
	}
}

function threadExistsByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . BREWCHAN_DBPOSTS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . BREWCHAN_DBPOSTS . "` (`parent`, `timestamp`, `bumped`, `ip`, `name`, `tripcode`, `email`, `nameblock`, `subject`, `message`, `password`, `file`, `file_hex`, `file_original`, `file_size`, `file_size_formatted`, `image_width`, `image_height`, `thumb`, `thumb_width`, `thumb_height`, `moderated`) VALUES (" . $post['parent'] . ", " . time() . ", " . time() . ", '" . hashData($_SERVER['REMOTE_ADDR']) . "', '" . mysqli_real_escape_string($link, $post['name']) . "', '" . mysqli_real_escape_string($link, $post['tripcode']) . "',	'" . mysqli_real_escape_string($link, $post['email']) . "',	'" . mysqli_real_escape_string($link, $post['nameblock']) . "', '" . mysqli_real_escape_string($link, $post['subject']) . "', '" . mysqli_real_escape_string($link, $post['message']) . "', '" . mysqli_real_escape_string($link, $post['password']) . "', '" . $post['file'] . "', '" . $post['file_hex'] . "', '" . mysqli_real_escape_string($link, $post['file_original']) . "', " . $post['file_size'] . ", '" . $post['file_size_formatted'] . "', " . $post['image_width'] . ", " . $post['image_height'] . ", '" . $post['thumb'] . "', " . $post['thumb_width'] . ", " . $post['thumb_height'] . ", " . $post['moderated'] . ")");
	return mysqli_insert_id($link);
}

function approvePostByID($id) {
	global $link;
	mysqli_query($link, "UPDATE `" . BREWCHAN_DBPOSTS . "` SET `moderated` = 1 WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThreadByID($id) {
	global $link;
	mysqli_query($link, "UPDATE `" . BREWCHAN_DBPOSTS . "` SET `bumped` = " . time() . " WHERE `id` = " . $id . " LIMIT 1");
}

function stickyThreadByID($id, $setsticky) {
	global $link;
	mysqli_query($link, "UPDATE `" . BREWCHAN_DBPOSTS . "` SET `stickied` = '" . mysqli_real_escape_string($link, $setsticky) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function lockThreadByID($id, $setlock) {
	global $link;
	mysqli_query($link, "UPDATE `" . BREWCHAN_DBPOSTS . "` SET `locked` = '" . mysqli_real_escape_string($link, $setlock) . "' WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . BREWCHAN_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function allThreads() {
	global $link;
	$threads = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1 ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysqli_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link, "SELECT COUNT(*) FROM `" . BREWCHAN_DBPOSTS . "` WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function postsInThreadByID($id, $moderated_only = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBPOSTS . "` WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" . ($moderated_only ? " AND `moderated` = 1" : "") . " ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
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
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT `id`, `parent` FROM `" . BREWCHAN_DBPOSTS . "` WHERE `file_hex` = '" . mysqli_real_escape_string($link, $hex) . "' AND `moderated` = 1 LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function latestPosts($moderated = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBPOSTS . "` WHERE `moderated` = " . ($moderated ? '1' : '0') . " ORDER BY `timestamp` DESC LIMIT 10");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function deletePostByID($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBPOSTS . "` WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

function trimThreads() {
	global $link;
	if (BREWCHAN_MAXTHREADS > 0) {
		$result = mysqli_query($link, "SELECT `id` FROM `" . BREWCHAN_DBPOSTS . "` WHERE `parent` = 0 AND `moderated` = 1 ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . BREWCHAN_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysqli_fetch_assoc($result)) {
				deletePost($post['id']);
			}
		}
	}
}

function lastPostByIP() {
	global $link;
	$replies = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBPOSTS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR']) . "' OR `ip` = '" . mysqli_real_escape_string($link, $_SERVER['REMOTE_ADDR']) . "' ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysqli_fetch_assoc($replies)) {
			return $post;
		}
	}
}

// Ban functions
function banByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBBANS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBBANS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function allBans() {
	global $link;
	$bans = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBBANS . "` ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . BREWCHAN_DBBANS . "` (`ip`, `timestamp`, `expire`, `reason`) VALUES ('" . mysqli_real_escape_string($link, hashData($ban['ip'])) . "', '" . time() . "', '" . mysqli_real_escape_string($link, $ban['expire']) . "', '" . mysqli_real_escape_string($link, $ban['reason']) . "')");
	return mysqli_insert_id($link);
}

function clearExpiredBans() {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBBANS . "` WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBBANS . "` WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

function deleteBanByID($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBBANS . "` WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

// Report functions
function reportByIP($post, $ip) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "' AND (`ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "') LIMIT 1");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			return $report;
		}
	}
}

function reportsByPost($post) {
	global $link;
	$reports = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "'");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function allReports() {
	global $link;
	$reports = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBREPORTS . "` ORDER BY `post` ASC");
	if ($result) {
		while ($report = mysqli_fetch_assoc($result)) {
			$reports[] = $report;
		}
	}
	return $reports;
}

function insertReport($report) {
	global $link;
	mysqli_query($link, "INSERT INTO `" . BREWCHAN_DBREPORTS . "` (`ip`, `post`) VALUES ('" . mysqli_real_escape_string($link, hashData($report['ip'])) . "', '" . mysqli_real_escape_string($link, $report['post']) . "')");
}

function deleteReportsByPost($post) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBREPORTS . "` WHERE `post` = '" . mysqli_real_escape_string($link, $post) . "'");
}

function deleteReportsByIP($ip) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBREPORTS . "` WHERE `ip` = '" . mysqli_real_escape_string($link, $ip) . "' OR `ip` = '" . mysqli_real_escape_string($link, hashData($ip)) . "'");
}

// Keyword functions
function keywordByID($id) {
	global $link;
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBKEYWORDS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			return $keyword;
		}
	}
	return array();
}

function keywordByText($text) {
	global $link;
	$text = strtolower($text);
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBKEYWORDS . "` WHERE `text` = '" . mysqli_real_escape_string($link, $text) . "'");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			return $keyword;
		}
	}
	return array();
}

function allKeywords() {
	global $link;
	$keywords = array();
	$result = mysqli_query($link, "SELECT * FROM `" . BREWCHAN_DBKEYWORDS . "` ORDER BY `text` ASC");
	if ($result) {
		while ($keyword = mysqli_fetch_assoc($result)) {
			$keywords[] = $keyword;
		}
	}
	return $keywords;
}

function insertKeyword($keyword) {
	global $link;
	$keyword['text'] = strtolower($keyword['text']);
	mysqli_query($link, "INSERT INTO `" . BREWCHAN_DBKEYWORDS . "` (`text`, `action`) VALUES ('" . mysqli_real_escape_string($link, $keyword['text']) . "', '" . mysqli_real_escape_string($link, $keyword['action']) . "')");
}

function deleteKeyword($id) {
	global $link;
	mysqli_query($link, "DELETE FROM `" . BREWCHAN_DBKEYWORDS . "` WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "'");
}

// Utility functions
function mysqli_result($res, $row, $field = 0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}
