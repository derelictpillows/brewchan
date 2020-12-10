<?php
/*
brewchan
https://github.com/derelictpillows/brewchan

Support:
https://github.com/derelictpillows/brewchan/issues

see README for instructions on configuring, moderating and upgrading your board.

set BREWCHAN_DBMODE to a MySQL-related mode if it's available.  by default it's set to flatfile, which can be very slow.
*/

// internationalization
define('BREWCHAN_LOCALE', '');          // locale  (see README for instructions)

// administrator/moderator credentials
define('BREWCHAN_ADMINPASS', '');       // administrators have full access to the board
define('BREWCHAN_MODPASS', '');         // moderators only have access to delete (and moderate if TINYIB_REQMOD is set) posts  <'' to disable>

// board description and behavior
//   WARNING: Enabling ReCAPTCHA will cause all visitors to be tracked by Google.  See https://nearcyan.com/you-probably-dont-need-recaptcha/
define('BREWCHAN_BOARD', 'b');          // identifier for this board using only letters and numbers
define('BREWCHAN_BOARDDESC', 'brewchan'); // displayed at the top of every page
define('BREWCHAN_ALWAYSNOKO', true);   // redirect to thread after posting
define('BREWCHAN_CAPTCHA', '');         // reduce spam by requiring users to pass a CAPTCHA when posting: simple / recaptcha  (click Rebuild All in the management panel after enabling)  <'' to disable>
define('BREWCHAN_MANAGECAPTCHA', '');   // improve security by requiring users to pass a CAPTCHA when logging in to the management panel: simple / recaptcha  <'' to disable>
define('BREWCHAN_REPORT', true);       // allow users to report posts
define('BREWCHAN_REQMOD', '');          // require moderation before displaying posts: files / all  ['' to disable]

// board appearance
define('BREWCHAN_INDEX', 'index.html'); // index file
define('BREWCHAN_LOGO', '');            // logo HTML
define('BREWCHAN_THREADSPERPAGE', 12);  // amount of threads shown per index page
define('BREWCHAN_PREVIEWREPLIES', 3);   // amount of replies previewed on index pages
define('BREWCHAN_TRUNCATE', 15);        // messages are truncated to this many lines on board index pages  <0 to disable>
define('BREWCHAN_WORDBREAK', 0);       // words longer than this many characters will be broken apart  <0 to disable>
define('BREWCHAN_TIMEZONE', 'America/New_York');     // see https://secure.php.net/manual/en/timezones.php - e.g. America/Los_Angeles
define('BREWCHAN_CATALOG', true);       // generate catalog page
define('BREWCHAN_JSON', true);          // generate JSON files
define('BREWCHAN_DATEFMT', '%g/%m/%d(%a)%H:%M:%S'); // date and time format  (see php.net/strftime)
$brewchan_hidefieldsop = array('email', 'embed');       // fields to hide when creating a new thread - e.g. array('name', 'email', 'subject', 'message', 'file', 'embed', 'password')
$brewchan_hidefields = array('email', 'embed');         // fields to hide when replying
$brewchan_capcodes = array(array('Admin', 'red'), array('Mod', 'purple')); // administrator and moderator capcode label and color

// post control
define('BREWCHAN_DELAY', 10);           // Delay (in seconds) between posts from the same IP address to help control flooding  <0 to disable>
define('BREWCHAN_MAXTHREADS', 100);     // Oldest threads are discarded when the thread count passes this limit  <0 to disable>
define('BREWCHAN_MAXREPLIES', 0);       // Maximum replies before a thread stops bumping  <0 to disable>

// upload types
//   empty array to disable
//   format: MIME type => (extension, optional thumbnail)
//   video uploads require mediainfo and ffmpegthumbnailer, see README for instructions
$brewchan_uploads = array('image/jpeg'                  => array('jpg'),
                        'image/pjpeg'                   => array('jpg'),
                        'image/png'                     => array('png'),
                        'image/gif'                     => array('gif');
                        'application/x-shockwave-flash' => array('swf', 'swf_thumbnail.png');
                        'audio/aac'                     => array('aac');
                        'audio/flac'                    => array('flac');
                        'audio/ogg'                     => array('ogg');
                        'audio/opus'                    => array('opus');
                        'audio/mp3'                     => array('mp3');
                        'audio/mpeg'                    => array('mp3');
                        'audio/mp4'                     => array('mp4');
                        'audio/wav'                     => array('wav');
                        'audio/webm'                    => array('webm');
                        'video/mp4'                     => array('mp4'); 
                        'video/webm'                    => array('webm'));

// oEmbed APIs borrowed from tinyib
//   empty array to disable
$tinyib_embeds = array('SoundCloud' => 'https://soundcloud.com/oembed?format=json&url=TINYIBEMBED',
                       'Vimeo'      => 'https://vimeo.com/api/oembed.json?url=TINYIBEMBED',
                       'YouTube'    => 'https://www.youtube.com/oembed?url=TINYIBEMBED&format=json');

// file control
define('BREWCHAN_MAXKB', 0);         // maximum file size in kilobytes  [0 to disable]
define('BREWCHAN_MAXKBDESC', 'unlimited');   // human-readable description of the maximum file size
define('BREWCHAN_THUMBNAIL', 'gd');     // thumbnail method to use: gd / imagemagick  (see README for instructions)
define('BREWCHAN_UPLOADVIAURL', false); // allow files to be uploaded via URL
define('BREWCHAN_NOFILEOK', false);     // allow the creation of new threads without uploading a file

// thumbnail size - new thread
define('BREWCHAN_MAXWOP', 250);         // width
define('BREWCHAN_MAXHOP', 250);         // height

// thumbnail size - reply
define('BREWCHAN_MAXW', 250);           // width
define('BREWCHAN_MAXH', 250);           // height

// tripcode seed - Must not change once set!
define('BREWCHAN_TRIPSEED', '');        // enter some random text  (used when generating secure tripcodes, hashing passwords and hashing IP addresses)

// CAPTCHA
//   the following only apply when BREW_CAPTCHA is set to recaptcha
//   for API keys visit https://www.google.com/recaptcha
define('BREWCHAN_RECAPTCHA_SITE', '');  // Site key
define('BREWCHAN_RECAPTCHA_SECRET', '');// Secret key

// database
//   recommended database modes from best to worst:
//     pdo, mysqli, mysql, sqlite3, sqlite (deprecated), flatfile (only useful if you need portability or lack any kind of database)
define('BREWCHAN_DBMODE', 'flatfile');     // Mode
define('BREWCHAN_DBMIGRATE', false);       // Enable database migration tool  (see README for instructions)
define('BREWCHAN_DBBANS', 'bans');         // Bans table name (use the same table across boards for global bans)
define('BREWCHAN_DBKEYWORDS', 'keywords'); // Keywords table name (use the same table across boards for global keywords)
define('BREWCHAN_DBPOSTS', BREWCHAN_BOARD . '_posts');     // Posts table name
define('BREWCHAN_DBREPORTS', BREWCHAN_BOARD . '_reports'); // Reports table name

// database configuration - MySQL / pgSQL
//   the following only apply when BREWCHAN_DBMODE is set to mysql, mysqli or pdo with default (blank) BREWCHAN_DBDSN
define('BREWCHAN_DBHOST', 'localhost'); // Hostname
define('BREWCHAN_DBPORT', 3306);        // Port  (set to 0 if you are using a UNIX socket as the host)
define('BREWCHAN_DBUSERNAME', '');      // Username
define('BREWCHAN_DBPASSWORD', '');      // Password
define('BREWCHAN_DBNAME', '');          // Database

// database configuration - SQLite / SQLite3
//   the following only apply when BREWCHAN_DBMODE is set to sqlite or sqlite3
define('BREWCHAN_DBPATH', 'brew.db');  // SQLite DB path relative to inc/

// database configuration - PDO
//   the following only apply when BREWCHAN_DBMODE is set to pdo  (see README for instructions)
define('BREWCHAN_DBDRIVER', 'mysql');   // PDO driver to use (mysql / pgsql / sqlite / etc.)
define('BREWCHAN_DBDSN', '');           // enter a custom DSN to override all of the connection/driver settings above  (see README for instructions)
//                                         when changing this, you should still set BREWCHAN_DBDRIVER appropriately.
//                                         if you're using PDO with a MySQL or pgSQL database, you should leave this blank.
