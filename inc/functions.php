<?php
if (!defined('BREWCHAN_BOARD')) {
	die('');
}

if (!function_exists('array_column')) {
	function array_column($array, $column_name) {
		return array_map(function ($element) use ($column_name) {
			return $element[$column_name];
		}, $array);
	}
}

function hashData($data) {
	global $bcrypt_salt;
	if (substr($data, 0, 4) == '$2y$') {
		return $data;
	}
	return crypt($data, $bcrypt_salt);
}

function cleanString($string) {
	$search = array("&", "<", ">");
	$replace = array("&amp;", "&lt;", "&gt;");

	return str_replace($search, $replace, $string);
}

function plural($count, $singular, $plural) {
	if ($plural == 's') {
		$plural = $singular . $plural;
	}
	return ($count == 1 ? $singular : $plural);
}

function threadUpdated($id) {
	rebuildThread($id);
	rebuildIndexes();
}

function newPost($parent = BREWCHAN_NEWTHREAD) {
	return array('parent' => $parent,
		'timestamp' => '0',
		'bumped' => '0',
		'ip' => '',
		'name' => '',
		'tripcode' => '',
		'email' => '',
		'nameblock' => '',
		'subject' => '',
		'message' => '',
		'password' => '',
		'file' => '',
		'file_hex' => '',
		'file_original' => '',
		'file_size' => '0',
		'file_size_formatted' => '',
		'image_width' => '0',
		'image_height' => '0',
		'thumb' => '',
		'thumb_width' => '0',
		'thumb_height' => '0',
		'stickied' => '0',
		'locked' => '0',
		'moderated' => '1');
}

function convertBytes($number) {
	$len = strlen($number);
	if ($len < 4) {
		return sprintf("%dB", $number);
	} elseif ($len <= 6) {
		return sprintf("%0.2fKB", $number / 1024);
	} elseif ($len <= 9) {
		return sprintf("%0.2fMB", $number / 1024 / 1024);
	}

	return sprintf("%0.2fGB", $number / 1024 / 1024 / 1024);
}

function nameAndTripcode($name) {
	if (preg_match("/(#|!)(.*)/", $name, $regs)) {
		$cap = $regs[2];
		$cap_full = '#' . $regs[2];

		if (function_exists('mb_convert_encoding')) {
			$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
			if ($recoded_cap != '') {
				$cap = $recoded_cap;
			}
		}

		if (strpos($name, '#') === false) {
			$cap_delimiter = '!';
		} elseif (strpos($name, '!') === false) {
			$cap_delimiter = '#';
		} else {
			$cap_delimiter = (strpos($name, '#') < strpos($name, '!')) ? '#' : '!';
		}

		if (preg_match("/(.*)(" . $cap_delimiter . ")(.*)/", $cap, $regs_secure)) {
			$cap = $regs_secure[1];
			$cap_secure = $regs_secure[3];
			$is_secure_trip = true;
		} else {
			$is_secure_trip = false;
		}

		$tripcode = "";
		if ($cap != "") { // Copied from Futabally
			$cap = strtr($cap, "&amp;", "&");
			$cap = strtr($cap, "&#44;", ", ");
			$salt = substr($cap . "H.", 1, 2);
			$salt = preg_replace("/[^\.-z]/", ".", $salt);
			$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
			$tripcode = substr(crypt($cap, $salt), -10);
		}

		if ($is_secure_trip) {
			if ($cap != "") {
				$tripcode .= "!";
			}

			$tripcode .= "!" . substr(md5($cap_secure . BREWCHAN_TRIPSEED), 2, 10);
		}

		return array(preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $name), $tripcode);
	}

	return array($name, "");
}

function nameBlock($name, $tripcode, $email, $timestamp, $rawposttext) {
	$output = '<span class="postername">';
	$output .= ($name == '' && $tripcode == '') ? __('Anonymous') : $name;

	if ($tripcode != '') {
		$output .= '</span><span class="postertrip">!' . $tripcode;
	}

	$output .= '</span>';

	if ($email != '' && strtolower($email) != 'noko') {
		$output = '<a href="mailto:' . $email . '">' . $output . '</a>';
	}

	return $output . $rawposttext . ' ' . strftime(BREWCHAN_DATEFMT, $timestamp);
}

function writePage($filename, $contents) {
	$tempfile = tempnam('res/', BREWCHAN_BOARD . 'tmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}

	chmod($filename, 0664); /* it was created 0600 */
}

function fixLinksInRes($html) {
	$search = array(' href="css/', ' src="js/', ' href="src/', ' href="thumb/', ' href="res/', ' href="imgboard.php', ' href="catalog.html', ' href="favicon.ico', 'src="thumb/', 'src="inc/', 'src="sticky.png', 'src="lock.png', ' action="imgboard.php', ' action="catalog.html');
	$replace = array(' href="../css/', ' src="../js/', ' href="../src/', ' href="../thumb/', ' href="../res/', ' href="../imgboard.php', ' href="../catalog.html', ' href="../favicon.ico', 'src="../thumb/', 'src="../inc/', 'src="../sticky.png', 'src="../lock.png', ' action="../imgboard.php', ' action="../catalog.html');

	return str_replace($search, $replace, $html);
}

function _postLink($matches) {
	$post = postByID($matches[1]);
	if ($post) {
		return '<a href="res/' . ($post['parent'] == BREWCHAN_NEWTHREAD ? $post['id'] : $post['parent']) . '.html#' . $matches[1] . '">' . $matches[0] . '</a>';
	}
	return $matches[0];
}

function postLink($message) {
	return preg_replace_callback('/&gt;&gt;([0-9]+)/', '_postLink', $message);
}

function _finishWordBreak($matches) {
	return '<a' . $matches[1] . 'href="' . str_replace(BREWCHAN_WORDBREAK_IDENTIFIER, '', $matches[2]) . '"' . $matches[3] . '>' . str_replace(BREWCHAN_WORDBREAK_IDENTIFIER, '<br>', $matches[4]) . '</a>';
}

function finishWordBreak($message) {
	return str_replace(BREWCHAN_WORDBREAK_IDENTIFIER, '<br>', preg_replace_callback('/<a(.*?)href="([^"]*?)"(.*?)>(.*?)<\/a>/', '_finishWordBreak', $message));
}

function colorQuote($message) {
	if (substr($message, -1, 1) != "\n") {
		$message .= "\n";
	}
	return preg_replace('/^(&gt;[^\>](.*))\n/m', '<span class="unkfunc">\\1</span>' . "\n", $message);
}

function deletePostImages($post) {
	if (!isEmbed($post['file_hex']) && $post['file'] != '') {
		@unlink('src/' . $post['file']);
	}
	if ($post['thumb'] != '') {
		@unlink('thumb/' . $post['thumb']);
	}
}

function deletePost($id) {
	$id = intval($id);

	$posts = postsInThreadByID($id, false);
	$op = array();
	foreach ($posts as $post) {
		if ($post['parent'] == BREWCHAN_NEWTHREAD) {
			$op = $post;
			continue;
		}

		deletePostImages($post);
		deleteReportsByPost($post['id']);
		deletePostByID($post['id']);
	}
	if (!empty($op)) {
		deletePostImages($op);
		deleteReportsByPost($op['id']);
		deletePostByID($op['id']);
	}

	@unlink('res/' . $id . '.html');
}

function checkCAPTCHA($mode) {
	if ($mode === 'recaptcha') {
		require_once 'inc/recaptcha/autoload.php';

		$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
		$failed_captcha = true;

		$recaptcha = new \ReCaptcha\ReCaptcha(BREWCHAN_RECAPTCHA_SECRET);
		$resp = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);
		if ($resp->isSuccess()) {
			$failed_captcha = false;
		}

		if ($failed_captcha) {
			$captcha_error = 'Failed CAPTCHA.';
			$error_reason = '';

			if (count($resp->getErrorCodes()) == 1) {
				$error_codes = $resp->getErrorCodes();
				$error_reason = $error_codes[0];
			}

			if ($error_reason == 'missing-input-response') {
				$captcha_error .= ' Please click the checkbox labeled "I\'m not a robot".';
			} else {
				$captcha_error .= ' Reason:';
				foreach ($resp->getErrorCodes() as $error) {
					$captcha_error .= '<br>' . $error;
				}
			}
			fancyDie($captcha_error);
		}
	} else if ($mode) { // Simple CAPTCHA
		$captcha = isset($_POST['captcha']) ? strtolower(trim($_POST['captcha'])) : '';
		$captcha_solution = isset($_SESSION['brewchancaptcha']) ? strtolower(trim($_SESSION['brewchancaptcha'])) : '';

		if ($captcha == '') {
			fancyDie(__('Please enter the CAPTCHA text.'));
		} else if ($captcha != $captcha_solution) {
			fancyDie(__('Incorrect CAPTCHA text entered.  Please try again.<br>Click the image to retrieve a new CAPTCHA.'));
		}
	}
}

function checkBanned() {
	$ban = banByIP($_SERVER['REMOTE_ADDR']);
	if ($ban) {
		if ($ban['expire'] == 0 || $ban['expire'] > time()) {
			$expire = ($ban['expire'] > 0) ? ('<br>This ban will expire ' . strftime(BREWCHAN_DATEFMT, $ban['expire'])) : '<br>This ban is permanent and will not expire.';
			$reason = ($ban['reason'] == '') ? '' : ('<br>Reason: ' . $ban['reason']);
			fancyDie('Your IP address ' . $_SERVER['REMOTE_ADDR'] . ' has been banned from posting on this image board.  ' . $expire . $reason);
		} else {
			clearExpiredBans();
		}
	}
}

function checkKeywords($text) {
	$keywords = allKeywords();
	foreach ($keywords as $keyword) {
		if (stripos($text, $keyword['text']) !== false) {
			return $keyword;
		}
	}
	return array();
}

function checkFlood() {
	if (BREWCHAN_DELAY > 0) {
		$lastpost = lastPostByIP();
		if ($lastpost) {
			if ((time() - $lastpost['timestamp']) < BREWCHAN_DELAY) {
				fancyDie("Please wait a moment before posting again.  You will be able to make another post in " . (BREWCHAN_DELAY - (time() - $lastpost['timestamp'])) . " " . plural(BREWCHAN_DELAY - (time() - $lastpost['timestamp']), "second", "seconds") . ".");
			}
		}
	}
}

function checkMessageSize() {
	if (strlen($_POST["message"]) > 8000) {
		fancyDie(sprintf(__('Please shorten your message, or post it in multiple parts. Your message is %1$d characters long, and the maximum allowed is %2$d.'), strlen($_POST["message"]), 8000));
	}
}

function manageCheckLogIn() {
	$loggedin = false;
	$isadmin = false;
	if (isset($_POST['managepassword'])) {
		checkCAPTCHA(BREWCHAN_MANAGECAPTCHA);

		if ($_POST['managepassword'] === BREWCHAN_ADMINPASS) {
			$_SESSION['brewchan'] = hashData(BREWCHAN_ADMINPASS);
		} elseif (BREWCHAN_MODPASS != '' && $_POST['managepassword'] === BREWCHAN_MODPASS) {
			$_SESSION['brewchan'] = hashData(BREWCHAN_MODPASS);
		} else {
			fancyDie(__('Invalid password.'));
		}
	}

	if (isset($_SESSION['brewchan'])) {
		if ($_SESSION['brewchan'] === hashData(BREWCHAN_ADMINPASS)) {
			$loggedin = true;
			$isadmin = true;
		} elseif (BREWCHAN_MODPASS != '' && $_SESSION['brewchan'] === hashData(BREWCHAN_MODPASS)) {
			$loggedin = true;
		}
	}

	return array($loggedin, $isadmin);
}

function setParent() {
	if (isset($_POST["parent"])) {
		if ($_POST["parent"] != BREWCHAN_NEWTHREAD) {
			if (!threadExistsByID($_POST['parent'])) {
				fancyDie(__('Invalid parent thread ID supplied, unable to create post.'));
			}

			return $_POST["parent"];
		}
	}

	return BREWCHAN_NEWTHREAD;
}

function isRawPost() {
	if (isset($_POST['rawpost'])) {
		list($loggedin, $isadmin) = manageCheckLogIn();
		if ($loggedin) {
			return true;
		}
	}

	return false;
}

function validateFileUpload() {
	switch ($_FILES['file']['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_FORM_SIZE:
			fancyDie(sprintf(__('That file is larger than %s.'), BREWCHAN_MAXKBDESC));
			break;
		case UPLOAD_ERR_INI_SIZE:
			fancyDie(sprintf(__('The uploaded file exceeds the upload_max_filesize directive (%s) in php.ini.'), ini_get('upload_max_filesize')));
			break;
		case UPLOAD_ERR_PARTIAL:
			fancyDie(__('The uploaded file was only partially uploaded.'));
			break;
		case UPLOAD_ERR_NO_FILE:
			fancyDie(__('No file was uploaded.'));
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			fancyDie(__('Missing a temporary folder.'));
			break;
		case UPLOAD_ERR_CANT_WRITE:
			fancyDie(__('Failed to write file to disk'));
			break;
		default:
			fancyDie(__('Unable to save the uploaded file.'));
	}
}

function checkDuplicateFile($hex) {
	$hexmatches = postsByHex($hex);
	if (count($hexmatches) > 0) {
		foreach ($hexmatches as $hexmatch) {
			fancyDie(sprintf(__('Duplicate file uploaded. That file has already been posted <a href="%s">here</a>.'), 'res/' . (($hexmatch['parent'] == BREWCHAN_NEWTHREAD) ? $hexmatch['id'] : $hexmatch['parent']) . '.html#' . $hexmatch['id']));
		}
	}
}

function thumbnailDimensions($post) {
	if ($post['parent'] == BREWCHAN_NEWTHREAD) {
		$max_width = BREWCHAN_MAXWOP;
		$max_height = BREWCHAN_MAXHOP;
	} else {
		$max_width = BREWCHAN_MAXW;
		$max_height = BREWCHAN_MAXH;
	}
	return ($post['image_width'] > $max_width || $post['image_height'] > $max_height) ? array($max_width, $max_height) : array($post['image_width'], $post['image_height']);
}

function createThumbnail($file_location, $thumb_location, $new_w, $new_h) {
	if (BREWCHAN_THUMBNAIL == 'gd') {
		$system = explode(".", $thumb_location);
		$system = array_reverse($system);
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img = imagecreatefromjpeg($file_location);
		} else if (preg_match("/png/", $system[0])) {
			$src_img = imagecreatefrompng($file_location);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img = imagecreatefromgif($file_location);
		} else {
			return false;
		}

		if (!$src_img) {
			fancyDie(__('Unable to read the uploaded file while creating its thumbnail. A common cause for this is an incorrect extension when the file is actually of a different type.'));
		}

		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		$percent = ($old_x > $old_y) ? ($new_w / $old_x) : ($new_h / $old_y);
		$thumb_w = round($old_x * $percent);
		$thumb_h = round($old_y * $percent);

		$dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
		if (preg_match("/png/", $system[0]) && imagepng($src_img, $thumb_location)) {
			imagealphablending($dst_img, false);
			imagesavealpha($dst_img, true);

			$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 0);
			imagefilledrectangle($dst_img, 0, 0, $thumb_w, $thumb_h, $color);
			imagecolortransparent($dst_img, $color);

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		} else {
			fastimagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		}

		if (preg_match("/png/", $system[0])) {
			if (!imagepng($dst_img, $thumb_location)) {
				return false;
			}
		} else if (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $thumb_location, 70)) {
				return false;
			}
		} else if (preg_match("/gif/", $system[0])) {
			if (!imagegif($dst_img, $thumb_location)) {
				return false;
			}
		}

		imagedestroy($dst_img);
		imagedestroy($src_img);
	} else { // ImageMagick
		$discard = '';

		$exit_status = 1;
		exec("convert -version", $discard, $exit_status);
		if ($exit_status != 0) {
			fancyDie('ImageMagick is not installed, or the convert command is not in the server\'s $PATH.<br>Install ImageMagick, or set BREWCHAN_THUMBNAIL to \'gd\'.');
		}

		$exit_status = 1;
		exec("convert $file_location -auto-orient -thumbnail '" . $new_w . "x" . $new_h . "' -coalesce -layers OptimizeFrame -depth 4 -type palettealpha $thumb_location", $discard, $exit_status);

		if ($exit_status != 0) {
			return false;
		}
	}

	return true;
}

function fastimagecopyresampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
	// Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
	if (empty($src_image) || empty($dst_image)) {
		return false;
	}

	if ($quality <= 1) {
		$temp = imagecreatetruecolor($dst_w + 1, $dst_h + 1);

		imagecopyresized($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
		imagecopyresized($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor($tmp_w + 1, $tmp_h + 1);

		imagecopyresized($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
		imagecopyresampled($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		imagedestroy($temp);
	} else {
		imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

	return true;
}

function addVideoOverlay($thumb_location) {
	if (!file_exists('video_overlay.png')) {
		return;
	}

	if (BREWCHAN_THUMBNAIL == 'gd') {
		if (substr($thumb_location, -4) == ".jpg") {
			$thumbnail = imagecreatefromjpeg($thumb_location);
		} else {
			$thumbnail = imagecreatefrompng($thumb_location);
		}
		list($width, $height, $type, $attr) = getimagesize($thumb_location);

		$overlay_play = imagecreatefrompng('video_overlay.png');
		imagealphablending($overlay_play, false);
		imagesavealpha($overlay_play, true);
		list($overlay_width, $overlay_height, $overlay_type, $overlay_attr) = getimagesize('video_overlay.png');

		if (substr($thumb_location, -4) == ".png") {
			imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
			imagealphablending($thumbnail, true);
			imagesavealpha($thumbnail, true);
		}

		imagecopy($thumbnail, $overlay_play, ($width / 2) - ($overlay_width / 2), ($height / 2) - ($overlay_height / 2), 0, 0, $overlay_width, $overlay_height);

		if (substr($thumb_location, -4) == ".jpg") {
			imagejpeg($thumbnail, $thumb_location);
		} else {
			imagepng($thumbnail, $thumb_location);
		}
	} else { // imagemagick
		$discard = '';
		$exit_status = 1;
		exec("convert $thumb_location video_overlay.png -gravity center -composite -quality 75 $thumb_location", $discard, $exit_status);
	}
}

function strallpos($haystack, $needle, $offset = 0) {
	$result = array();
	for ($i = $offset; $i < strlen($haystack); $i++) {
		$pos = strpos($haystack, $needle, $i);
		if ($pos !== False) {
			$offset = $pos;
			if ($offset >= $i) {
				$i = $offset;
				$result[] = $offset;
			}
		}
	}
	return $result;
}

function url_get_contents($url) {
	if (!function_exists('curl_init')) {
		return file_get_contents($url);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);

	return $output;
}

function isEmbed($file_hex) {
	global $brewchan_embeds;
	return in_array($file_hex, array_keys($brewchan_embeds));
}

function getEmbed($url) {
	global $brewchan_embeds;
	foreach ($brewchan_embeds as $service => $service_url) {
		$service_url = str_ireplace("BREWCHANEMBED", urlencode($url), $service_url);
		$result = json_decode(url_get_contents($service_url), true);
		if (!empty($result)) {
			return array($service, $result);
		}
	}

	return array('', array());
}

function attachFile($post, $filepath, $filename, $uploaded) {
	global $brewchan_uploads;

	if (!is_file($filepath) || !is_readable($filepath)) {
		@unlink($filepath);
		fancyDie(__('File transfer failure. Please retry the submission.'));
	}

	$filesize = filesize($filepath);
	if (BREWCHAN_MAXKB > 0 && $filesize > (BREWCHAN_MAXKB * 1024)) {
		@unlink($filepath);
		fancyDie(sprintf(__('That file is larger than %s.'), BREWCHAN_MAXKBDESC));
	}

	$post['file_original'] = trim(htmlentities(substr($filename, 0, 50), ENT_QUOTES));
	$post['file_hex'] = md5_file($filepath);
	$post['file_size'] = $filesize;
	$post['file_size_formatted'] = convertBytes($post['file_size']);

	checkDuplicateFile($post['file_hex']);

	$file_mime_split = explode(' ', trim(mime_content_type($filepath)));
	if (count($file_mime_split) > 0) {
		$file_mime = strtolower(array_pop($file_mime_split));
	} else {
		if (!@getimagesize($filepath)) {
			@unlink($filepath);
			fancyDie(__('Failed to read the MIME type and size of the uploaded file. Please retry the submission.'));
		}
		$file_mime = mime_content_type($filepath);
	}
	if (empty($file_mime) || !isset($brewchan_uploads[$file_mime])) {
		fancyDie(supportedFileTypes());
	}

	$file_name = time() . substr(microtime(), 2, 3);
	$post['file'] = $file_name . '.' . $brewchan_uploads[$file_mime][0];

	$file_location = 'src/' . $post['file'];
	if ($uploaded) {
		if (!move_uploaded_file($filepath, $file_location)) {
			fancyDie(__('Could not copy uploaded file.'));
		}
	} else {
		if (!rename($filepath, $file_location)) {
			@unlink($filepath);
			fancyDie(__('Could not copy uploaded file.'));
		}
	}

	if (filesize($file_location) != $filesize) {
		@unlink($file_location);
		fancyDie(__('File transfer failure. Please go back and try again.'));
	}

	if ($file_mime == 'audio/webm' || $file_mime == 'video/webm' || $file_mime == 'audio/mp4' || $file_mime == 'video/mp4') {
		$post['image_width'] = max(0, intval(shell_exec('mediainfo --Inform="Video;%Width%" ' . $file_location)));
		$post['image_height'] = max(0, intval(shell_exec('mediainfo --Inform="Video;%Height%" ' . $file_location)));

		if ($post['image_width'] > 0 && $post['image_height'] > 0) {
			list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post);
			$post['thumb'] = $file_name . 's.jpg';
			shell_exec("ffmpegthumbnailer -s " . max($thumb_maxwidth, $thumb_maxheight) . " -i $file_location -o thumb/{$post['thumb']}");

			$thumb_info = getimagesize('thumb/' . $post['thumb']);
			$post['thumb_width'] = $thumb_info[0];
			$post['thumb_height'] = $thumb_info[1];

			if ($post['thumb_width'] <= 0 || $post['thumb_height'] <= 0) {
				@unlink($file_location);
				@unlink('thumb/' . $post['thumb']);
				fancyDie(__('Sorry, your video appears to be corrupt.'));
			}

			addVideoOverlay('thumb/' . $post['thumb']);
		}

		$duration = intval(shell_exec('mediainfo --Inform="General;%Duration%" ' . $file_location));
		if ($duration > 0) {
			$mins = floor(round($duration / 1000) / 60);
			$secs = str_pad(floor(round($duration / 1000) % 60), 2, '0', STR_PAD_LEFT);

			$post['file_original'] = "$mins:$secs" . ($post['file_original'] != '' ? (', ' . $post['file_original']) : '');
		}
	} else if (in_array($file_mime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'application/x-shockwave-flash'))) {
		$file_info = getimagesize($file_location);

		$post['image_width'] = $file_info[0];
		$post['image_height'] = $file_info[1];
	}

	if (isset($brewchan_uploads[$file_mime][1])) {
		$thumbfile_split = explode('.', $brewchan_uploads[$file_mime][1]);
		$post['thumb'] = $file_name . 's.' . array_pop($thumbfile_split);
		if (!copy($brewchan_uploads[$file_mime][1], 'thumb/' . $post['thumb'])) {
			@unlink($file_location);
			fancyDie(__('Could not create thumbnail.'));
		}
		if ($file_mime == 'application/x-shockwave-flash') {
			addVideoOverlay('thumb/' . $post['thumb']);
		}
	} else if (in_array($file_mime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
		$post['thumb'] = $file_name . 's.' . $brewchan_uploads[$file_mime][0];
		list($thumb_maxwidth, $thumb_maxheight) = thumbnailDimensions($post);

		if (!createThumbnail($file_location, 'thumb/' . $post['thumb'], $thumb_maxwidth, $thumb_maxheight)) {
			@unlink($file_location);
			fancyDie(__('Could not create thumbnail.'));
		}
	}

	if ($post['thumb'] != '') {
		$thumb_info = getimagesize('thumb/' . $post['thumb']);
		$post['thumb_width'] = $thumb_info[0];
		$post['thumb_height'] = $thumb_info[1];
	}

	return $post;
}

function installedViaGit() {
	return is_dir('.git');
}
