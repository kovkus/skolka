<?php

  define('MN_VERSION', '2.6.1');
  define('MN_VERSION_CHECK', 3);

  define('DEBUG', false);

  define('DEFAULT_LANG', 'sk');
  define('DEFAULT_TMPL', 'mn_default');
  define('MAX_LOGGED_TIME', 180);
  define('MIN_COMMENT_LENGTH', 5);





/*
 ***************************************************************************************************
 * DO NOT EDIT ANYTHING UNDER THIS LINE (unless you know what you're doing)                        *
 ***************************************************************************************************
*/

  $languages = array(
    'cs' => 'Czech',
    #'en' => 'English',
    'sk' => 'Slovak'
  );

  $file = array(
    'banned_ips' 	   => 'data/databases/banned_ips.php',
    'categories' 	   => 'data/databases/categories.php',
    'categories_order' => 'data/databases/categories_order.php',
    'config' 	       => 'data/databases/config.php',
    'files' 		   => 'data/databases/files.php',
    'folders' 		   => 'data/databases/folders.php',
    'galleries' 	   => 'data/databases/galleries.php',
    'groups' 		   => 'data/databases/groups.php',
    'id_comments' 	   => 'data/databases/id_comments.php',
    'id_folders' 	   => 'data/databases/id_folders.php',
    'id_pages' 		   => 'data/databases/id_pages.php',
    'id_posts' 	   	   => 'data/databases/id_posts.php',
    'last_backup' 	   => 'data/databases/last_backup.php',
    'pages'			   => 'data/databases/pages.php',
    'posts' 		   => 'data/databases/posts.php',
    'tags' 			   => 'data/databases/tags.php',
    'templates' 	   => 'data/databases/templates.php',
    'templates_groups' => 'data/databases/templates_groups.php',
    'theme_config' 	   => 'data/databases/theme_config.php',
    'users' 		   => 'data/databases/users.php',
    'xfields' 		   => 'data/databases/xfields.php',
  );
  
  $required_files = array('config', 'files', 'groups', 'id_comments', 'id_pages', 'id_posts', 'users');

  $dir = array(
    'avatars'   => 'data/files/avatars/',
    'backups'   => 'data/files/backups/',
    'comments'  => 'data/comments/',
    'databases' => 'data/databases/',
    'files' 	=> 'data/files/',
    'images' 	=> 'data/files/images/',
    'media' 	=> 'data/files/media/',
    'pages' 	=> 'data/pages/',
    'others' 	=> 'data/files/others/',
    'posts' 	=> 'data/posts/',
    'templates' => 'data/templates/',
    'thumbs' 	=> 'data/files/images/_thumbs/',
  );
  
  $ext = array(
    'images' => array('bmp', 'gif', 'jpeg', 'jpg', 'png'),
    'media'  => array('3gp', 'aac', 'asf', 'asx', 'avi', 'flac', 'm3u', 'm4v', 'mid', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'wav', 'wmv', 'wmx'),
    'others' => array('7z', 'csv', 'doc', 'docx', 'pdf', 'rar', 'rtf', 'srt', 'sub', 'txt', 'xls', 'xlsx', 'zip'),
  );
  
  $ext_img = array(
  	'3gp'  => 'video',
    '7z'   => 'archive',
    'aac'  => 'audio',
    'asf'  => 'video',
    'asx'  => 'video',
    'avi'  => 'video',
    'bmp'  => 'image',
    'csv'  => 'excel',
    'doc'  => 'word',
    'docx' => 'word',
    'flac' => 'audio',
    'gif'  => 'image',
    'jpeg' => 'image',
    'jpg'  => 'image',
    'm3u'  => 'audio',
    'm4v'  => 'video',
    'mid'  => 'audio',
    'mov'  => 'video',
    'mp3'  => 'audio',
    'mp4'  => 'video',
    'mpeg' => 'video',
    'mpg'  => 'video',
    'pdf'  => 'pdf',
    'png'  => 'image',
    'rar'  => 'archive',
    'rtf'  => 'word',
    'srt'  => 'others',
    'sub'  => 'others',
    'txt'  => 'others',
    'wav'  => 'audio',
    'wmv'  => 'video',
    'wmx'  => 'video',
    'xls'  => 'excel',
    'xlsx' => 'excel',
    'zip'  => 'archive',
  );
  
  $default = array(
  	'thumb_size'			=> 150,
    'avatar_size_standard' 	=> 100,
    'avatar_size_small' 	=> 50,
    'avatar_size_mini' 		=> 20,
  );
  
  $encodings = array(
    'utf-8' 	  => 'UTF-8 (Unicode)',
    'iso-8859-1'  => 'ISO-8859-1 (Western)',
    'iso-8859-2'  => 'ISO-8859-2 (Central European)',
    'iso-8859-3'  => 'ISO-8859-3 (Southern European)',
    'iso-8859-4'  => 'ISO-8859-4 (Baltic)',
    'iso-8859-5'  => 'ISO-8859-5 (Cyrillic)',
    'iso-8859-6'  => 'ISO-8859-6 (Arabic)',
    'iso-8859-7'  => 'ISO-8859-7 (Greek)',
    'iso-8859-8'  => 'ISO-8859-8 (Hebrew)',
    'iso-8859-9'  => 'ISO-8859-9 (Turkish)',
    'iso-8859-10' => 'ISO-8859-10 (Turkish)',
    'iso-8859-11' => 'ISO-8859-11 (Thai)',
    'iso-8859-13' => 'ISO-8859-13 (Baltic + Polish)',
    'iso-8859-14' => 'ISO-8859-14 (Celtic)',
    'iso-8859-15' => 'ISO-8859-15 (Western)',
    'iso-8859-16' => 'ISO-8859-16 (Central European)',
    'cp1250' 	  => 'Windows-1250 (Central European)',
    'cp1251' 	  => 'Windows-1251 (Cyrillic)',
    'cp1252' 	  => 'Windows-1252 (Western)',
    'cp1253' 	  => 'Windows-1253 (Greek)',
    'cp1254' 	  => 'Windows-1254 (Turkish)',
    'cp1255' 	  => 'Windows-1255 (Hebrew)',
    'cp1256' 	  => 'Windows-1256 (Arabic)',
    'cp1257' 	  => 'Windows-1257 (baltic)',
    'cp1258' 	  => 'Windows-1258 (Vietnamese)',
  );



  $templates = array(1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 15, 19);
  $default_templates = array(1, 10, 11, 12, 13, 14, 15, 19);
  $default_template = array(
    1  => "<div class=\"mn-posts\" style=\"text-align: justify;\">\n  <h3>{TITLE}</h3>\n  {TEXT}\n\n  <div class=\"mn-post-info\" style=\"text-align: right;\">\n    <strong>{AUTHOR}</strong> | <em>{DATE} {TIME}</em> | {COMMENTS}\n  </div>\n  <hr />\n</div>",
    10 => "<div class=\"mn-post\" style=\"text-align: justify;\">\n  <h2>{TITLE}</h2>\n  {TEXT}\n\n  <div class=\"mn-post-info\" style=\"text-align: right;\">\n    <strong>{AUTHOR}</strong> | <em>{DATE} {TIME}</em>\n  </div>\n</div>",
    11 => "<div class=\"mn-page\">\n  <h2>{TITLE}</h2>\n  {TEXT}\n</div>",
    12 => "<div class=\"mn-comment\" id=\"c-{COMMENT_ID}\" style=\"text-align: justify;\">\n  <div class=\"mn-comment-author\">\n    [<a href=\"#mn-comment-{COMMENT_ID}\">{COMMENT_NUM}</a>] <strong>{AUTHOR}</strong> [EMAIL]e-mail[/EMAIL] [WWW]www[/WWW] <em>{DATE} {TIME}</em>\n  </div>\n\n  {TEXT}\n  <hr />\n</div>",
    13 => "<div class=\"mn-user\">\n  <h2>{USERNAME}</h2>\n  <ul>\n    <li>E-mail: {EMAIL}</li>\n    <li>ICQ: {ICQ}</li>\n    <li>Www: {WWW}</li>\n  </ul>\n</div>",
    14 => "  <item>\n    <title>{TITLE_PLAIN}</title>\n    <link>{LINK}</link>\n    <description>{TEXT}</description>\n    <pubDate>{PUBDATE}</pubDate>\n  </item>",
    15 => "<div class=\"mn-pagination\">\n  [PREVIOUS]&laquo;[/PREVIOUS]\n  | {PAGES} |\n  [NEXT]&raquo;[/NEXT]\n</div>",
    19 => "<a href=\"{IMAGE_URL}\">{THUMBNAIL}</a> ",
  );
  $default_permissions = array(1 => '111111111111111', '111111000010011', '220202000000011', '230000000000011', '300000000000001');



  define('DELIMITER', '|');
  define('DELIMITER_REPLACE', '/');
  define('DIR_THEMES', 'stuff/themes/');
  define('SAFETY_LINE', '<?php if (!defined(\'IN_MNews\')) die(\'Hi! How are u today? Having fun? :)\');?>');
  define('DIE_LINE', '<?php die();?>');
  
  
  ##### Yippee-ki-yay, motherfucker! ###########################################
  ##### http://youtu.be/OTyw6cq86kY ######################################################

?>
