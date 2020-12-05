# brewchan
a lightweight, efficient, and PHP-based  [imageboard](https://en.wikipedia.org/wiki/imageboard)
#

**so, you've got a database.** use [MySQL](https://mysql.com), [PostgreSQL](https://www.postgresql.org) or [SQLite](https://sqlite.org) for a setup that's quick and able to handle high traffic.                
**or maybe you don't.** just store posts as text files for a portable setup that can run on pretty much every PHP host.

**not looking for an imageboard script, you say?** brewchan is able to allow new threads without uploading an image, or even completely disable images

## features

- GIF, JPG, PNG, SWF, MP4, and WebM upload
- YouTube, Vimeo, and SoundCloud embedding
- CAPTCHA
	- a simpler, self-hosted use is included
	- [ReCAPTCHA](https://www.google.com/recaptcha/about/) is supported but **[definitely not recommended](https://nearcyan.com/you-probably-dont-need-recaptcha/)**
- reference links `>>###`
- delete posts via password
- report posts
- block keywords
- management panel
	- admins and moderators use separate passwords
		- mods can only sticky threads, lock threads, delete posts, and approve posts when needed; see ``BREWCHAN_REQMOD``
		- ban posters across all boards
		- post using raw HTML

## install

1) verify the following are installed:
	- [PHP 5.5+](https://php.net)
	- [GD Image Processing Library](https://php.net/gd)
		- this library is usually already installed by default
		- if you plan on disabling image uploads, and use brewchan as a text board only, this library is optional

2) `cd` to the directory you want to install brewchan to
3) run the following:
	- `git clone https://github.com/derelictpillows/brewchan ./`
4) run the following as well:
	- `mv settings.default.php settings.php`
5) configure `settings.php`
	- when setting ``BREWCHAN_DBMODE`` to ``flatfile``, note that all post, report, and ban data are exposed as the database will be made of regular text files
	- when setting ``BREWCHAN_DBMODE`` to ``pdo``, note that only MySQL and PostgreSQL database drivers have been tested at the moment; theoretically it will work with any applicable driver, but this is not guaranteed
	- to require moderation before displaying posts:
		- set ``BREWCHAN_REQMOD`` to ``files`` to require moderation for posts with files attached
		- set ``BREWCHAN_REQMOD`` to ``all`` to require moderation for **all** posts
	- to allow video uploads:
		- ensure your webhost is running a version of GNU/Linux
		- install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/)
		- to remove the play icon from .swf and .webm thumbnails, delete or rename ``video_overlay.png``
6) `chmod` write permissions to these directories:
	- ./ (the directory containing brewchan)
	- ./src/
	- ./thumb/
	- ./res/
	- ./inc/database/flatfile/ (only if you use the ``flatfile`` database mode)
7) navigate your browser to ``imgboard.php`` and the following will happen:
	- the database structure will be created
	- directories will be verified to be writable
	- the board index will be written to ``BREWCHAN_INDEX``	

## moderation

1) log in to the management panel by clicking **[Manage]**
2) on the board, tick the checkbox next to the offending post
3) scroll to the bottom of the page
4) click delete with the password field blank
	- from this page you are able to delete the post and/or ban the author
(posting while logged in while show a special tag next to your name showing you are either a mod/admin)

## migrate

brewchan comes with a database migration tool

while the migration is in progress, users will not be able to create (or delete) posts

1) edit ``settings.php``
	- set ``BREWCHAN_DBMIGRATE`` to the desired ``BREWCHAN_DBMODE`` after the migration
	- configure all settings related to the desired ``BREWCHAN_DBMODE``
2) open the management panel
3) click **Migrate Database**
4) click **Start the migration**
5) if the migration was successful:
	- edit ``settings.php``
		- set ``BREWCHAN_DBMODE`` to the mode previously specified as ``BREWCHAN_DBMIGRATE``
		- set ``BREWCHAN_DBMIGRATE`` to a blank string (``''``)
	- click **Rebuild All** and ensure the board still looks the way it should

## support

1) make sure you are running the latest version of brewchan
2) review the [open issues](https://github.com/derelictpillows/brewchan/issues)
3) open a [new issue](https://github.com/derelictpillows/brewchan/issues/new)

## contribute 

1) fork brewchan
2) commit code changes to your forked repo
3) submit a pull request describing your modifications
