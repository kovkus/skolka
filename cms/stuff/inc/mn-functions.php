<?php

/***************************************************************************************************
 ****** Basic MN functions *************************************************************************
 **************************************************************************************************/



//
// ----- MNews version of time() function ----------------------------------------------------------
//
function mn_time() {
  global $conf;
  return time() + ($conf['time_adj'] * 3600);
}





//
// ----- MNews version of file_put_contents() function ---------------------------------------------
//
function mn_put_contents($f, $c) {
  if (file_put_contents($f, $c, LOCK_EX)) {
    @mkdir($f, 0777);
    @chmod($f, 0777);
    return true;
  }
  elseif (file_put_contents($f, $c)) {
    @mkdir($f, 0777);
    @chmod($f, 0777);
    return true;
  }
  else return false;
}





//
// ----- MNews version of chmod() function ---------------------------------------------------------
//
function mn_chmod($path) {
  if (is_writeable($path)) return true;
  elseif (!is_dir($path)) {
    $oldumask = umask(0);
    @chmod($path, 0777);
    umask($oldumask);
  }
  else return false;
}





//
// ----- MNews version of mkdir() function ---------------------------------------------------------
//
function mn_mkdir($path) {
  if (!is_dir($path)) {

    $oldumask = umask(0);
    @mkdir($path, 0777);
    umask($oldumask);

    if (is_writable($path)) return true;
    else return false;
  }

  else return false;
}





//
// ----- MNews version of natcasesort() function ---------------------------------------------------
//
function mn_natcasesort($array) {
  $original_keys_arr = array();
  $original_values_arr = array();
  $clean_values_arr = array();

  $i = 0;
  $search  = array('Â','À','Á','Ä','Ã','Č','Ď','Ê','È','É','Ë','Ě','Î','Í','Ì','Ï','Ĺ','Ľ','Ň','Ô','Õ','Ò','Ó','Ö','Ŕ','Ř','Š','Ť','Û','Ù','Ú','Ü','Ů','Ý','Ž','â','à','á','ä','ã','č','ď','ê','è','é','ë','ě','î','í','ì','ï','ĺ','ľ','ň','ô','õ','ò','ó','ö','ŕ','ř','š','ť','û','ù','ú','ü','ů','ý','ž');
  $replace = array('A','A','A','A','A','C','D','E','E','E','E','E','I','I','I','I','L','L','N','O','O','O','O','O','R','R','S','T','U','U','U','U','U','Y','Z','a','a','a','a','a','c','d','e','e','e','e','e','i','i','i','i','l','l','n','o','o','o','o','o','r','r','s','t','u','u','u','u','u','y','z');

  foreach ($array as $key => $value) {
    $original_keys_arr[$i] = $key;
    $original_values_arr[$i] = $value;
    $clean_values_arr[$i] = str_replace($search, $replace, $value);
    $i++;
  }

  natcasesort($clean_values_arr);

  $result_arr = array();

  foreach ($clean_values_arr as $key => $value) {
    $original_key = $original_keys_arr[$key];
    $original_value = $original_values_arr[$key];
    $result_arr[$original_key] = $original_value;
  }

  return $result_arr;
}





//
// ----- Words declension --------------------- ----------------------------------------------------
//
function mn_declension($num, $text_1, $text_2_4, $text_5) {
  return (abs($num) == 1 ? $text_1 : ($num == 0 || abs($num) >= 5 ? $text_5 : $text_2_4));
}





//
// ----- Sort multidimensional Array by Value -----------------------------------------------------
//
function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}









/***************************************************************************************************
 ****** Load, Get, ... Extract data functions ******************************************************
 **************************************************************************************************/



//
// ----- Get values for each database --------------------------------------------------------------
//
function get_values($f, $id = 0, $switcher = true) {

  global $file;

  if ($switcher) {
    if (file_exists(MN_ROOT . $file[$f])) {
      $f_content = file(MN_ROOT . $file[$f]);
      array_shift($f_content);
      foreach ($f_content as $f_line) {
        $f_data = explode(DELIMITER, $f_line);
        if ($f_data[0] == $id) $values = explode(DELIMITER, $f_line);
        else continue;
      }
    }
    else return false;
  }
  else $values = explode(DELIMITER, $id);


  switch ($f) {
    case 'categories':
    	$keys = array ('cat_id', 'cat_name', 'friendly_url');
    break;

    case 'comments':
    	$keys = array ('comment_id', 'timestamp', 'post_id', 'status', 'author_id', 'author_name', 'author_email', 'author_www', '-empty1-', 'xfields', '-empty2-', 'ip_address', 'host', 'user_agent', 'comment_text');
    break;
    
    case 'files':
    	$keys = array ('file_id', 'filename', 'ext', 'filesize', 'timestamp', 'dir', 'img_width', 'img_height', 'uploader_id', 'galleries', 'folder', '-empty4-', '-empty5-', '-empty6-', '-empty7-', 'xfields', 'title', 'description');
    break;
    
    case 'galleries':
    	$keys = array ('gallery_id', 'gallery_name', 'friendly_url');
    break;

    case 'groups':
    	$keys = array ('group_id', 'group_name', 'friendly_url', 'permissions');
    break;

    case 'posts':
  	 $keys = array ('timestamp', 'post_id', 'title', 'friendly_url', 'author', 'cat', 'status', 'tags');
    break;

    case 'tags':
    	$keys = array ('tag_id', 'tag_name', 'friendly_url');
    break;

    case 'templates':
    	$keys = array ('tmpl_id', 'tmpl_name', 'tmpl_group', 'tmpl_type', '');
    break;

    case 'templates_groups':
    	$keys = array ('tg_id', 'tg_name');
    break;

    case 'users':
    	$keys = array ('user_id', 'username', 'pass', 'email', 'group', 'status', 'key', 'last_login', 'last_ip', 'registered', 'registered_ip', 'public_email', '-empty2-', '-empty3-', '-empty4-', '-empty5-', '-empty6-', '-empty7-', '-empty8-', 'avatar', 'nickname', 'gender', 'birthdate', 'location', 'www', 'icq', 'msn', 'skype', 'jabber', '-empty10-', 'xfields', 'other1', 'other2', 'about');
    break;

    default:
    	return false;
    break;
  }


  if (count($values) == count($keys)) {
    return array_combine($keys, $values);
  }
  else echo ' error MN#69: ' . count($keys) . ' vs. ' . count($values); //   ]:-> error #69
}





//
// ----- Load basic data for selected section to array ---------------------------------------------
//
function load_complex_data($required_file, $id = '') {
  global $file;
  if (file_exists(MN_ROOT . $file[$required_file])) {
    $output = array();
    $selected_file = file(MN_ROOT . $file[$required_file]);
    array_shift($selected_file);
    foreach ($selected_file as $single_line) {
      $file_data = explode(DELIMITER, $single_line);
      if (!empty($id) && $file_data[0] != $id) continue;
      elseif (empty($single_line)) continue;
      else {
        $output[$file_data[0]] = array(
          'user_id' => $file_data[0],
          'username' => $file_data[1],
          'pass' => $file_data[2],
          'email' => $file_data[3],
          'group' => $file_data[4],
          'status' => $file_data[5],
          'key' => $file_data[6],
          'last_login' => $file_data[7],
          'last_ip' => $file_data[8],
          'registered' => $file_data[9],
          'registered_ip' => $file_data[10],
          'public_email' => $file_data[11],
          'avatar' => $file_data[19],
          'nickname' => $file_data[20],
          'gender' => $file_data[21],
          'birthdate' => $file_data[22],
          'location' => $file_data[23],
          'www' => $file_data[24],
          'icq' => $file_data[25],
          'msn' => $file_data[26],
          'skype' => $file_data[27],
          'jabber' => $file_data[28],
          'xfields' => $file_data[30],
          'other1' => $file_data[31],
          'other2' => $file_data[32],
          'about' => $file_data[33]
        );
      }
    }
    return $output;
  }
  else return null;
}





//
// ----- Load basic data for selected section to array ---------------------------------------------
//
function load_basic_data($required_file, $id = '') {
  global $file;
  if (file_exists(MN_ROOT . $file[$required_file])) {
    $output = array();
    $selected_file = file(MN_ROOT . $file[$required_file]);
    array_shift($selected_file);
    foreach ($selected_file as $single_line) {
      $file_data = explode(DELIMITER, $single_line);
      if (!empty($id) && $file_data[0] != $id) continue;
      elseif (empty($single_line)) continue;
      else $output[$file_data[0]] = trim($file_data[1]);
    }
    return mn_natcasesort($output);
  }
  else return null;
}





//
// ----- Load array data for posts -----------------------------------------------------------------
//
function get_post_data($id, $switcher = true) {
  global $dir;
  $file = ($switcher) ? 'post_' . $id . '.php' : $id;
  if (file_exists(MN_ROOT . $dir['posts'] . $file)) {
    $file_content = file_get_contents(MN_ROOT . $dir['posts'] . $file);
    $file_data = explode(DELIMITER, $file_content);
    $result = array(
      'id' => trim($file_data[1]),
      'timestamp' => "$file_data[2]",
      'date_day' => date('d', $file_data[2]),
      'date_month' => date('m', $file_data[2]),
      'date_year' => date('Y', $file_data[2]),
      'date_hour' => date('H', $file_data[2]),
      'date_min' => date('i', $file_data[2]),
      'title' => $file_data[3],
      'friendly_url' => $file_data[4],
      'author' => $file_data[5],
      'cat' => $file_data[6],
      'status' => $file_data[7],
      'comments' => $file_data[8],
      'views' => $file_data[9],
      'tags' => $file_data[10],
      'image' => $file_data[11],
      'xfields' => trim($file_data[14]),
      'short_story' => trim($file_data[15]),
      'full_story' => trim($file_data[16]),
      'action' => @$_REQUEST['action'],
    );
    return $result;
  }
  else return false;
}





//
// ----- Load array data for pages -----------------------------------------------------------------
//
function get_page_data($id, $switcher = true) {
  global $dir;
  $p_file = ($switcher) ? 'page_' . trim($id) . '.php' : $id;
  if (file_exists(MN_ROOT . $dir['pages'] . $p_file)) {
    $p_content = file_get_contents(MN_ROOT . $dir['pages'] . $p_file);
    $p_data = explode(DELIMITER, $p_content);
    $result = array(
      'id' => $p_data[1],
      'timestamp' => $p_data[2],
      'title' => $p_data[3],
      'friendly_url' => $p_data[4],
      'author' => $p_data[5],
      'visible' => $p_data[6],
      'order' => $p_data[7],
      'pass' => $p_data[8],
      'xfields' => $p_data[10],
      'text' => $p_data[11]
    );
    return $result;
  }
  else return false;
}





//
// ----- Get posts count ---------------------------------------------------------------------------
//
function get_posts_count($what = 'cat') {
  global $file;

  if (file_exists(MN_ROOT . $file['posts'])) {
    if ($what == 'users') $post_var = 'author';
    elseif ($what == 'tags') $post_var = 'tags';
    else $post_var = 'cat';

    $posts_file = file(MN_ROOT . $file['posts']);
    array_shift($posts_file);
    $output = array();

    foreach ($posts_file as $post_line) {
      $post = get_values('posts', $post_line, false);
      if ($post['status'] <= 2 && $post_var == 'tags') {
        $tags = explode(',', trim($post['tags']));
        foreach ($tags as $tag) {$output[] = $tag;}
      }
      elseif ($post['status'] <= 2) $output[] = $post[$post_var];
      else continue;
    }

    return array_count_values($output);
  }

  else return '0';
}





//
// ----- Get files count ---------------------------------------------------------------------------
//
function get_files_count($what = 'galleries') {
  global $file;

  if (file_exists(MN_ROOT . $file['files'])) {

    $fFile = file(MN_ROOT . $file['files']);
    array_shift($fFile);
    $output = array();

    foreach ($fFile as $file_line) {
      $f = get_values('files', $file_line, false);

      if ($what == 'galleries' && !empty($f['galleries'])) {
        $fGalleries = explode(',', trim($f['galleries']));
        foreach ($fGalleries as $fGallery) {$output[] = $fGallery;}
      }
      elseif ($what == 'folders' && !empty($f['folder'])) {
      	$output[] = $f['folder'];
      }
      else continue;
    }

    return array_count_values($output);
  }

  else return array();
}





//
// ----- Get comments count ------------------------------------------------------------------------
//
function get_comments_count($id) {
  global $dir;

  if (file_exists(MN_ROOT . $dir['comments'] . 'comments_' . $id . '.php')) {
    $comments_file = file(MN_ROOT . $dir['comments'] . 'comments_' . $id . '.php');
    $c_num = 0;
    foreach ($comments_file as $single_line) {
      $c_data = explode(DELIMITER, $single_line);
      if (isset($c_data[3]) && ($c_data[3] == 1 || $c_data[3] == 3)) $c_num++;
      else continue;
    }
  }
  else $c_num = 0;

  return $c_num;
}





//
// ----- Get unique timestamps ---------------------------------------------------------------------
//
function get_unique_timestamps() {
  global $file;

  if (file_exists(MN_ROOT . $file['posts'])) {
    $p_file = file(MN_ROOT . $file['posts']);
    $timestamps = array();

    array_shift($p_file);
    $p_file = mn_natcasesort($p_file);
    $p_file = array_reverse($p_file, true);

    foreach ($p_file as $p_line) {
      $p = get_values('posts', $p_line, false);

      if ($p['status'] != '1' || $p['timestamp'] > mn_time()) continue;
      else $timestamps[$p['timestamp']] = date('Y-m', $p['timestamp']);
    }

    ksort($timestamps);
    $timestamps = array_unique($timestamps);

    return $timestamps;
  }
  else return NULL;
}





//
// ----- Get file size ------------------------ ----------------------------------------------------
//
function get_file_size($file, $round = 0, $s = true) {
  $size = ($s) ? filesize($file) : $file;
  $sizes = array('B', 'kB', 'MB', 'GB', 'TB');
  for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
  return round($size, $round) . ' ' . $sizes[$i];
}





//
// ----- Get latest version number -----------------------------------------------------------------
//
function get_latest_version() {
  $errmsg = false;
  $fp = @fsockopen('remote.mnewscms.com', 80, $errno, $errstr, 30);
  if (!$fp) return '';
  else {
    $out = "GET /version.txt HTTP/1.1\r\nHost: remote.mnewscms.com\r\nConnection: Close\r\n\r\n";

    fwrite($fp, $out);
    while (!feof($fp)) {
      $latest_version_number = fgets($fp, 128);
    }
    fclose($fp);
    return trim($latest_version_number);
  }
}





//
// ----- Get post slugs ----------------------------------------------------------------------------
//
function get_post_slugs() {
  global $file;

  $posts_file = file(MN_ROOT . $file['posts']);
  array_shift($posts_file);
  $arr = array();

  foreach ($posts_file as $p_line) {
    $post = get_values('posts', $p_line, false);
    $arr[$post['post_id']] = $post['friendly_url'];
  }
  
  return $arr;

}





//
// ----- Get unserialized data (folders, xfields) -------------------------------------------------
//
function get_unserialized_array($what = 'folders') {

	global $file;

	if (file_exists(MN_ROOT . $file[$what])) {
		$data_file = file_get_contents(MN_ROOT . $file[$what]);
		$data_file = str_ireplace(DIE_LINE, '', $data_file);
		$arr = (!empty($data_file)) ? unserialize($data_file) : array();
	}
	else $arr = array();

	return $arr;

}





//
// ----- Simple & really, REALLY stupid function to assign variables to ($var) array :) ------------
//
function assign_post_vars($title, $author, $cat, $short_story, $full_story, $date, $date_day, $date_month, $date_year, $date_hour, $date_min, $status, $comments, $action, $id, $counter, $xfields = array()) {
  $result = array(
    'title' => $title, 'author' => $author, 'cat' => $cat, 'short_story' => $short_story, 'full_story' => $full_story, 'date' => $date, 'date_day' => $date_day, 'date_month' => $date_month, 'date_year' => $date_year, 'date_hour' => $date_hour, 'date_min' => $date_min, 'status' => $status, 'comments' => $comments, 'action' => $action, 'id' => $id, 'counter' => $counter, 'xfields' => $xfields
  );
  return $result;
}





//
// ----- Get info from HTTP_USER_AGENT string -----------------------------------------------------
//
function get_useragent_info($u_agent) { 

	global $lang;
    $bname = $lang['uni_unknown'];
    $platform = $lang['uni_unknown'];
    $version= '';

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) $platform = 'linux';
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) $platform = 'mac';
    elseif (preg_match('/windows|win32/i', $u_agent)) $platform = 'windows';
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i', $u_agent)) { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i', $u_agent)) { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i', $u_agent)) { 
        $bname = 'Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) $version= $matches['version'][0];
        else $version= $matches['version'][1];
    }
    else $version= $matches['version'][0];
    
    // check if we have a number
    if ($version == null || $version == '') {$version = '?';}
    
    
	// get OS name
    $oses = array (
    	'iPhone' => '/(iPhone)/',
    	'Windows 3.11' => '/Win16/',
    	'Windows 95' => '/(Windows 95)|(Win95)|(Windows_95)/',
    	'Windows 98' => '/(Windows 98)|(Win98)/',
    	'Windows 2000' => '/(Windows NT 5.0)|(Windows 2000)/',
    	'Windows XP' => '/(Windows NT 5.1)|(Windows XP)/',
    	'Windows 2003' => '/(Windows NT 5.2)/',
    	'Windows Vista' => '/(Windows NT 6.0)|(Windows Vista)/',
    	'Windows 7' => '/(Windows NT 6.1)|(Windows 7)/',
    	'Windows 8' => '/(Windows NT 6.2)|(Windows 8)/',
    	'Windows NT 4.0' => '/(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)/',
    	'Windows ME' => '/Windows ME/',
    	'Open BSD'=>'/OpenBSD/',
    	'Sun OS'=>'/SunOS/',
    	'Linux'=>'/(Linux)|(X11)/',
    	'Mac OS X'=>'/(Mac_PowerPC)|(Macintosh)|(Mac OS)/',
    	'Safari' => '/(Safari)/',
    	'QNX'=>'/QNX/',
    	'BeOS'=>'/BeOS/',
    	'OS/2'=>'/OS/2/',
    	'Search Bot'=>'/(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)/'
    );
    
    $os = $lang['uni_unknown'];
    foreach($oses as $key => $pattern) {
    	if(preg_match($pattern, $u_agent)) {
    		$os = $key;
    		break;
    	}
    }
    
    // return array list
    return array(
        'user_agent' 	  => $u_agent,
        'browser'   	  => $bname . ' ' . $version,
        'browser_icon'    => 'browser-' . strtolower($ub) . '.png',
        'browser_name'    => $bname,
        'browser_short'   => strtolower($ub),
        'browser_version' => $version,
        'platform'  	  => $platform,
        'pattern'   	  => $pattern,
        'os'			  => $os,
    );
}





//
// ----- Show virtual file folders in select drop-down  --------------------------------------------
//
function show_folders($folder_id = 0, $level = 0, $sel_folder = 0) {
	global $folders, $folders_sorted, $parents;

    foreach ($folders_sorted as $fId => $f_name) {
    	if ($folders[$fId]['parent_id'] == $folder_id) {
    	
    		$sel = ($fId == $sel_folder) ? ' selected="selected"' : '';
    		echo '<option value="' . $fId . '"' . $sel . '>';
    		for($i=0;$i<$level;$i++) echo '-- ';
    		echo $f_name . '</option>';
    		
    		if (isset($parents[$fId])) show_folders($fId, $level+1, $sel_folder);
    	
    	}
    	else continue;
    }
                  
}










/**************************************************************************************************
 ****** Doublecheck, Triplecheck... Check functions ***********************************************
 **************************************************************************************************/



//
// ----- Check text --------------------------------------------------------------------------------
//
function check_text($str, $s = false, $c = false) {
  $str = strip_slashes($str);
  $str = trim($str);
  if ($s) $str = htmlspecialchars($str);
  if ($c) {
    $str = str_replace('\r', '', $str);
    $str = str_replace('\n', '[BR] ', $str);
  }
  $str = str_replace(array('||', DELIMITER, '<?', '?'.'>', "\r", "\n"), array('OR', DELIMITER_REPLACE, '&lt;?', '?&gt;', '', ' '), $str);
  return $str;
}






//
// ----- Check e-mail address ----------------------------------------------------------------------
// ----- Source: http://www.phpit.net/code/valid-email/ --------------------------------------------
//
function check_email($email) {
  $isValid = true;
  $atIndex = strrpos($email, '@');
  if (is_bool($atIndex) && !$atIndex) {
     $isValid = false;
  }
  else {
    $domain = substr($email, $atIndex+1);
    $local = substr($email, 0, $atIndex);
    $localLen = strlen($local);
    $domainLen = strlen($domain);
    if ($localLen < 1 || $localLen > 64) {
      // local part length exceeded
      $isValid = false;
    }
    elseif ($domainLen < 1 || $domainLen > 255) {
      // domain part length exceeded
      $isValid = false;
    }
    elseif ($local[0] == '.' || $local[$localLen-1] == '.') {
      // local part starts or ends with '.'
      $isValid = false;
    }
    elseif (preg_match('/\\.\\./', $local)) {
      // local part has two consecutive dots
      $isValid = false;
    }
    elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
      // character not valid in domain part
      $isValid = false;
    }
    elseif (preg_match('/\\.\\./', $domain)) {
      // domain part has two consecutive dots
      $isValid = false;
    }
    elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
      // character not valid in local part unless
      // local part is quoted
      if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
        $isValid = false;
      }
    }
    /* small problems with this, when DNS site is down
    if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
      // domain not found in DNS
      $isValid = false;
    }*/
  }
  return ($isValid) ? $email : false;
}





//
// ----- Check url ---------------------------------------------------------------------------------
//
function check_url($url) {
  $url = trim($url);
  if (empty($url) || ($url == 'http://')) return false;
  else {
    $url = str_replace(DELIMITER, '', $url);
    $find = htmlspecialchars($url);
    $http = substr_count($find, 'http://');
    if ($http == 1) return $find;
    else return 'http://' . $find;
  }
}





//
// ----- Check installation status -----------------------------------------------------------------
//
function check_install() {
  global $file;
  if (file_exists('./install.php') && !file_exists(MN_ROOT . $file['users'])) {
    session_destroy();
    header('location: ./install.php');
    exit;
  }
  elseif (file_exists('./install.php') && file_exists(MN_ROOT . $file['users'])) {
    header('location: ./mn-login.php?install-file');
    exit;
  }
  else return true;
}





//
// ----- Check CHMOD on dirs and files -------------------------------------------------------------
//
function chmod_check() {
  if (is_writeable('./data/') && is_writeable('./data/comments/') && is_writeable('./data/databases/') && is_writeable('./data/files/') && is_writeable('./data/files/avatars/') && is_writeable('./data/files/backups/') && is_writeable('./data/files/images/') && is_writeable('./data/files/images/_thumbs/') && is_writeable('./data/files/others/') && is_writeable('./data/pages/') && is_writeable('./data/posts/') && is_writeable('./data/templates/')) {
    return true;
  }
  else {
    return false;
  }
}





//
// ----- Check if file is image --------------------------------------------------------------------
//
function is_image($file) {
  global $ext;
  $file_ext = get_ext($file);

  if (in_array($file_ext, $ext['images'])) {
    return true;
  }
  else {
    return false;
  }
}





//
// ----- Get file extentions -----------------------------------------------------------------------
//
function get_ext($file) {
  if (isset($file) && !empty($file)) {
    $temp_arr = explode('.', $file);
    $file_ext = strtolower($temp_arr[count($temp_arr)-1]);
    if (!empty($file_ext)) return $file_ext;
    else return false;
  }
  else return false;
}





function pathinfo_utf($path) {
  if (strpos($path, '/') !== false) $basename = end(explode('/', $path));
  elseif (strpos($path, '\\') !== false) $basename = end(explode('\\', $path));
  else $basename = $path;

  if (strpos($basename, '.') !== false) {
  	$hv = explode('.', $path);
    $extension = end($hv);
    $filename = substr($basename, 0, strlen($basename) - strlen($extension) - 1);
  }
  else {
    $extension = '';
    $filename = $basename;
  }

  return array(
    'basename' => $basename,
    'extension' => $extension,
    'filename' => $filename
  );
}





//
// ----- Clean file name ---------------------------------------------------------------------------
//
function clean_filename($filename) {
  $filename = preg_replace('/^\W+|\W+$/', '', $filename); // remove all non-alphanumeric chars at begin & end of string
  $filename = preg_replace('/\s+/', '-', $filename); // compress internal whitespace and replace with -
  return strtolower(preg_replace('/\W-/', '', $filename)); // remove all non-alphanumeric chars except _ and -
}





 //   Oh boy :)   \\
function add_slashes($str) {
  return (get_magic_quotes_gpc() ? $str : addslashes($str));
}


 //   Oh boy :)   \\
function strip_slashes($str) {
  return (get_magic_quotes_gpc() ? stripslashes($str) : $str);
}










/* *************************************************************************************************
 ****** SAFETY functions ***************************************************************************
 **************************************************************************************************/

//
// ----- Check right Mnews install folder ----------------------------------------------------------
//
function check_hash() {
  if (isset($_SESSION['mn_check_hash']) && $_SESSION['mn_check_hash'] == md5(__FILE__)) return true;
  else return false;
}





//
// ----- User session check ------------------------------------------------------------------------
//
function user_session() {
  global $file, $conf;

  if (isset($_SESSION['mn_logged']) && $_SESSION['mn_logged'] == true && isset($_SESSION['mn_check_hash']) && $_SESSION['mn_check_hash'] == md5(__FILE__)) {
    if (isset($_SESSION['mn_user_time']) && ($_SESSION['mn_user_time'] + (MAX_LOGGED_TIME * 60)) >= time()) {
      $_SESSION['mn_user_time'] = time();
      setcookie('mn_logged', true, time()+60*60*(MAX_LOGGED_TIME-5), '/', $_SERVER['SERVER_NAME']);
      return true;
    }
    elseif (isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_user_hash']) && ($conf['users_perm_login'])) {
      permanent_login();
    }
    else {
      @session_destroy();
      header('Location: ./mn-login.php?back=auto-loggedout');
      exit;
    }
  }

  // permanent login
  elseif (isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_user_hash']) && ($conf['users_perm_login'])) {
    permanent_login();
  }

  else {
    @session_destroy();
    header('Location: ./mn-login.php');
    exit;
  }
}





//
// ----- Login function ----------------------------------------------------------------------------
//
function do_login($u_name, $u_pass, $u_pl) {

  global $conf, $file;

  $u_file = file($file['users']);
  $u_lines = ''; $mn_user_hash = '';

  foreach ($u_file as $single_line) {

    $u_data = explode(DELIMITER, $single_line);

    if ($u_name == $u_data[1] && sha1($u_pass) == trim($u_data[2])) {

      if ($u_data[5] == '1') {

        define('mn_login', true);

        $auth_data = get_values('groups', $u_data[4]);
        $time = time();


        session_regenerate_id();
        $_SESSION['mn_logged'] = true;
        $_SESSION['mn_check_hash'] = md5(__FILE__);
        $_SESSION['mn_token'] = rand(1, 1e9);
        $_SESSION['mn_last_login'] = $u_data[7];
        $_SESSION['mn_registered'] = $u_data[9];
        $_SESSION['mn_user_name'] = $u_name;
        $_SESSION['mn_user_id'] = $u_data[0];
        $_SESSION['mn_user_auth'] = $auth_data['permissions'];
        $_SESSION['mn_user_time'] = $time;
        unset($_SESSION['login_error']);


        setcookie('mn_logged', true, time()+60*60*(MAX_LOGGED_TIME-5), '/', $_SERVER['SERVER_NAME']);
        setcookie('mn_user_name', $u_name, time()+60*60*24*14, '/', $_SERVER['SERVER_NAME']);

        if ($u_pl == 'true') {
          $mn_user_hash = sha1($_SERVER['HTTP_USER_AGENT'] . sha1($u_pass) . $time . $_SERVER['REMOTE_ADDR']);
          setcookie('mn_user_hash', $mn_user_hash, time()+60*60*24*14, '/', $_SERVER['SERVER_NAME']);
        }

        $u_lines .= $u_data[0] . DELIMITER . $u_data[1] . DELIMITER . $u_data[2] . DELIMITER . $u_data[3] . DELIMITER . $u_data[4] . DELIMITER . $u_data[5] . DELIMITER . $mn_user_hash . DELIMITER . $time . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . $u_data[9] . DELIMITER . $u_data[10] . DELIMITER . $u_data[11] . DELIMITER . $u_data[12] . DELIMITER . $u_data[13] . DELIMITER . $u_data[14] . DELIMITER . $u_data[15] . DELIMITER . $u_data[16] . DELIMITER . $u_data[17] . DELIMITER . $u_data[18] . DELIMITER . $u_data[19] . DELIMITER . $u_data[20] . DELIMITER . $u_data[21] . DELIMITER . $u_data[22] . DELIMITER . $u_data[23] . DELIMITER . $u_data[24] . DELIMITER . $u_data[25] . DELIMITER . $u_data[26] . DELIMITER . $u_data[27] . DELIMITER . trim($u_data[28]) . DELIMITER . $u_data[29] . DELIMITER . $u_data[30] . DELIMITER . $u_data[31] . DELIMITER . $u_data[32] . DELIMITER . trim($u_data[33]) . "\n";
      }

      else {
        define('mn_login', false);
        $status_error = $u_data[5];
      }

    }
    else $u_lines .= $single_line;
  }



  if ( mn_login && $_SESSION['mn_logged'] ) {
    if (mn_put_contents($file['users'], $u_lines)) {
      return true;
    }
    else {
      setcookie('mn_user_hash', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
      return true;
    }
  }

  elseif (empty($status_error)) {
    $_SESSION['login_error'] = $status_error;
    return false;
  }
  else {
    unset($_SESSION['login_error']);
    return false;
  }

}





//
// ----- Login via cookies - name & hash -----------------------------------------------------------
//
function permanent_login() {
  global $conf, $file;
  $users_file = file($file['users']);
  $u_lines = ''; $mn_user_hash = '';
  $do_login = false;

  foreach ($users_file as $single_line) {

    $user_data = explode(DELIMITER, $single_line);

    if ($_COOKIE['mn_user_name'] == $user_data[1] && $_COOKIE['mn_user_hash'] == $user_data[6]) {

      if ($_COOKIE['mn_user_hash'] == sha1($_SERVER['HTTP_USER_AGENT'] . $user_data[2] . $user_data[7] . $_SERVER['REMOTE_ADDR'])) {
        $do_login = true;

        $auth_data = get_values('groups', $user_data[4]);
        $time = time()-10;

        session_regenerate_id();
        $_SESSION['mn_logged'] = true;
        $_SESSION['mn_check_hash'] = md5(__FILE__);
        $_SESSION['mn_token'] = rand(1, 1e9);
        $_SESSION['mn_last_login'] = $user_data[7];
        $_SESSION['mn_registered'] = $user_data[9];
        $_SESSION['mn_user_name'] = $user_data[1];
        $_SESSION['mn_user_id'] = $user_data[0];
        $_SESSION['mn_user_auth'] = $auth_data['permissions'];
        $_SESSION['mn_user_time'] = $time;

        setcookie('mn_logged', true, time()+60*60*MAX_LOGGED_TIME, '/', $_SERVER['SERVER_NAME']);
        setcookie('mn_user_name', $user_data[1], time()+60*60*24*14, '/', $_SERVER['SERVER_NAME']);

        $mn_user_hash = sha1($_SERVER['HTTP_USER_AGENT'] . $user_data[2] . $time . $_SERVER['REMOTE_ADDR']);
        setcookie('mn_user_hash', $mn_user_hash, time()+60*60*24*14, '/', $_SERVER['SERVER_NAME']);

        $u_lines .= $user_data[0] . DELIMITER . $user_data[1] . DELIMITER . $user_data[2] . DELIMITER . $user_data[3] . DELIMITER . $user_data[4] . DELIMITER . $user_data[5] . DELIMITER . $mn_user_hash . DELIMITER . $time . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . $user_data[9] . DELIMITER . $user_data[10] . DELIMITER . $user_data[11] . DELIMITER . $user_data[12] . DELIMITER . $user_data[13] . DELIMITER . $user_data[14] . DELIMITER . $user_data[15] . DELIMITER . $user_data[16] . DELIMITER . $user_data[17] . DELIMITER . $user_data[18] . DELIMITER . $user_data[19] . DELIMITER . $user_data[20] . DELIMITER . $user_data[21] . DELIMITER . $user_data[22] . DELIMITER . $user_data[23] . DELIMITER . $user_data[24] . DELIMITER . $user_data[25] . DELIMITER . $user_data[26] . DELIMITER . $user_data[27] . DELIMITER . $user_data[28] . DELIMITER . $user_data[29] . DELIMITER . $user_data[30] . DELIMITER . $user_data[31] . DELIMITER . $user_data[32] . DELIMITER . trim($user_data[33]) . "\n";
      }
      else $u_lines .= $single_line;

    }
    else $u_lines .= $single_line;
  }

  if ($do_login == true && $_SESSION['mn_logged'] && mn_put_contents($file['users'], $u_lines)) {
    return true;
  }
  else  {
    @session_destroy();
    setcookie('mn_user_hash', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
    header('location: ./mn-login.php');
    exit;
  }
}





//
// ----- User authorization to particular admin section --------------------------------------------
//
function user_auth($section, $a = false) {
  if (($a) && $_SESSION['mn_user_auth'][$section] == 1) return $_SESSION['mn_user_auth'][$section];
  elseif ((!$a) && ($_SESSION['mn_user_auth'][$section] > 0)) return $_SESSION['mn_user_auth'][$section];
  elseif ($a) return false;
  else {
    header('location: ./?access-denied');
    exit;
  }
}





//
// ----- Check IP ban ------------------------------------------------------------------------------
//
function check_ip_ban($ip_to_match, $ip_array) {
  if (is_array($ip_array)) {
    foreach ($ip_array as $ip) {
      // first test if there is a match, then test if the match starts at the beginning
      if (strstr($ip_to_match, $ip) && strpos($ip_to_match, $ip)===0) {
        return true;
      }
    }
  }
  return false;
}










/* ************************************************************************************************
 ****** Other functions ***************************************************************************
 **************************************************************************************************/



//
// ----- Generate custom MN url --------------------------------------------------------------------
//
function generate_url($url_in) {
  $url_part = explode('?', $url_in);
  $url_temp = (empty($url_part[1])) ? $url_in . '?' : $url_in . '&';

  $url_search = array(
    'mn_a=' . @$_GET['mn_a'],
    'mn_action=' . @$_GET['mn_action'],
    'mn_archive=' . @$_GET['mn_archive'],
    'mn_cat=' . @$_GET['mn_cat'],
    'mn_gallery=' . @$_GET['mn_gallery'],
    'mn_key=' . @$_GET['mn_key'],
    'mn_msg=' . @$_GET['mn_msg'],
    'mn_page=' . @$_GET['mn_page'],
    'mn_p=' . @$_GET['mn_p'],
    'mn_post=' . @$_GET['mn_post'],
    'mn_tag=' . @$_GET['mn_tag'],
    'mn_q=' . urlencode(@$_GET['mn_q']),
    'mn_user=' . @$_GET['mn_user'],
    '&amp;',
    '&&&',
    '&&',
    '?&',
    '&',
  );
  $url_replace = array('', '', '', '', '', '', '', '', '', '', '', '', '', '&', '&', '&', '?', '&amp;');

  return str_replace($url_search, $url_replace, $url_temp);
}





//
// ----- Encoding function - adjust the output generated by mn-show.php ----------------------------
//
function encoding($str) {
  global $conf, $encodings;

  if ($conf['web_encoding'] != 'utf-8' && array_key_exists($conf['web_encoding'], $encodings) && !defined('IN_MNews')) {
    $str =  iconv('utf-8', $conf['web_encoding'], $str);
  }
  if ($conf['web_format'] == 'html' && !defined('IN_MNews')) {
    $str = str_replace(' />', '>', $str);
  }

  return $str;
}




//
// ----- Random password generator -----------------------------------------------------------------
//
function PasswordGenerator($length) {
  $vowels = array('a','e','i','o','u','y','A','E','I','O','U','Y');
  $cons = array('b','c','d','g','h','j','k','l','m','n','p','r','s','t','u','v','w','tr','cr','br','fr','th','dr','ch','ph','wr','st','sp','sw','pr','sl','cl');
  $num_vowels = count($vowels);
  $num_cons = count($cons);
  $password = '';
  for($i = 0; $i < $length; $i++)
    $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
  return substr($password, 0, $length);
}





//
// ----- Friendly looking url from title -----------------------------------------------------------
//
function friendly_url($str) {

  $search  = array(' ',':','.',',','!','?','&','(',')','=','Â','À','Á','Ä','Ã','Č','Ď','Ê','Ě','È','É','Ë','Î','Í','Ì','Ï','Ĺ','Ľ','Ň','Ô','Õ','Ò','Ó','Ö','Ŕ','Ř','Š','Ť','Û','Ù','Ú','Ü','Ů','Ý','Ž','â','à','á','ä','ã','č','ď','ê','ě','è','é','ë','î','í','ì','ï','ĺ','ľ','ň','ô','õ','ò','ó','ö','ŕ','ř','š','ť','û','ù','ú','ü','ů','ý','ž');
  $replace = array('-','-','-','-','-','-','-','-','-','-','A','A','A','A','A','C','D','E','E','E','E','E','I','I','I','I','L','L','N','O','O','O','O','O','R','R','S','T','U','U','U','U','U','Y','Z','a','a','a','a','a','c','d','e','e','e','e','e','i','i','i','i','l','l','n','o','o','o','o','o','r','r','s','t','u','u','u','u','u','y','z');
  $str = str_replace($search, $replace, $str);
  
  $str = strtolower($str);
  $str = preg_replace('~[^-a-z0-9_]+~', '', $str);
  while (strstr($str, '--')) $str = str_replace('--', '-', $str);
  $str = trim($str, '-');

  return $str;
}




//
// ----- Select language ---------------------------------------------------------------------------
//
function select_lang() {
  global $conf;

  if (isset($_SESSION['mn_lang']) && file_exists(MN_ROOT . 'stuff/lang/lang_' . $_SESSION['mn_lang'] . '.php')) {
    $sel_lang = $_SESSION['mn_lang'];
  }

  elseif (isset($conf['lang']) && isset($_COOKIE['mn_lang']) && ($_COOKIE['mn_lang'] != $conf['lang']) && file_exists(MN_ROOT . 'stuff/lang/lang_' . $_COOKIE['mn_lang'] . '.php')) {
    $sel_lang = $_COOKIE['mn_lang'];
    @setcookie('mn_lang', $sel_lang, time()+60*60*24*31, '/', $_SERVER['SERVER_NAME']);
  }

  elseif (isset($conf['lang']) && !empty($conf['lang']) && file_exists(MN_ROOT . 'stuff/lang/lang_' . $conf['lang'] . '.php')) {
    $sel_lang = $conf['lang'];
  }

  else {
    $sel_lang = DEFAULT_LANG;
  }

  return $sel_lang;
}






//
// ----- Crop Image --------------------------------------------------------------------------------
//
function crop_image($img, $newfilename) {

  //Get Image size info
  list($original_width, $original_height, $img_type) = getimagesize($img);

  switch ($img_type) {
    case 1: $original_image_gd = imagecreatefromgif($img); break;
    case 2: $original_image_gd = imagecreatefromjpeg($img);  break;
    case 3: $original_image_gd = imagecreatefrompng($img); break;
    default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
  }


  if ($original_width > $original_height) {
    $crop_height = $original_height;
    $crop_width = $original_height;
  }
  else {
    $crop_height = $original_width;
    $crop_width = $original_width;
  }


  $cropped_image_gd = imagecreatetruecolor($crop_width, $crop_height);
  $wm = $original_width/$crop_width;
  $hm = $original_height/$crop_height;
  $h_height = $crop_height/2;
  $w_height = $crop_width/2;

  if($original_width > $original_height ) {
    $adjusted_width =$original_width / $hm;
    $half_width = $adjusted_width / 2;
    $int_width = $half_width - $w_height;

    imagecopyresampled($cropped_image_gd ,$original_image_gd ,-$int_width,0,0,0, $adjusted_width, $crop_height, $original_width , $original_height );
  }
  elseif(($original_width < $original_height ) || ($original_width == $original_height )) {
    $adjusted_height = $original_height / $wm;
    $half_height = $adjusted_height / 2;
    $int_height = $half_height - $h_height;

    imagecopyresampled($cropped_image_gd , $original_image_gd ,0,-$int_height,0,0, $crop_width, $adjusted_height, $original_width , $original_height );
  }
  else {
    imagecopyresampled($cropped_image_gd , $original_image_gd ,0,0,0,0, $crop_width, $crop_height, $original_width , $original_height );
  }


  //Generate the file, and rename it to $newfilename
  switch ($img_type) {
    case 1: imagegif($cropped_image_gd, $newfilename); break;
    case 2: imagejpeg($cropped_image_gd, $newfilename, 100); break;
    case 3: imagepng($cropped_image_gd, $newfilename); break;
    default: trigger_error('Failed resize image!', E_USER_WARNING); break;
  }

  return $newfilename;
}





//
// ----- Resize Image ------------------------------------------------------------------------------
//
function resize_img($img, $max_size, $newfilename) {

  //Check if GD extension is loaded
  if (!extension_loaded('gd') && !extension_loaded('gd2')) {
    trigger_error('GD is not loaded ', E_USER_WARNING);
    return false;
  }

  //Get Image size info
  list($width_orig, $height_orig, $image_type) = getimagesize($img);


  @ini_set('memory_limit', '64M'); // I have to try to set the limit, even most likely, it's shared hosting the system is on :)

  switch ($image_type) {
    case 1: $im = imagecreatefromgif($img); break;
    case 2: $im = imagecreatefromjpeg($img);  break;
    case 3: $im = imagecreatefrompng($img); break;
    default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
  }

  if ($width_orig > $height_orig) {
    $thumb_width = $max_size;
    $aspect_ratio = (float) $height_orig / $width_orig;
    $thumb_height = round($thumb_width * $aspect_ratio);
  }
  else {
    $thumb_height = $max_size;
    $aspect_ratio = (float) $width_orig / $height_orig;
    $thumb_width = round($thumb_height * $aspect_ratio);
  }

  $newImg = imagecreatetruecolor($thumb_width, $thumb_height);

  /* Check if this image is PNG or GIF, then set if Transparent*/
  if(($image_type == 1) OR ($image_type==3)) {
    imagealphablending($newImg, false);
    imagesavealpha($newImg,true);
    $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
    imagefilledrectangle($newImg, 0, 0, $thumb_width, $thumb_height, $transparent);
  }
  imagecopyresampled($newImg, $im, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);

  //Generate the file, and rename it to $newfilename
  switch ($image_type) {
    case 1: imagegif($newImg, $newfilename); break;
    case 2: imagejpeg($newImg, $newfilename, 100); break;
    case 3: imagepng($newImg, $newfilename); break;
    default: trigger_error('Failed resize image!', E_USER_WARNING); break;
  }

  return $newfilename;
}




if (substr(phpversion(), 0, 1) >= 5) {
//
// ----- Backup "data" directory -------------------------------------------------------------------
// ----- author: Alphard ---------------------------------------------------------------------------
// ----- source: http://diskuse.jakpsatweb.cz/index.php?action=vthread&forum=9&topic=2111#4 --------
//
class backup {

  protected $zip;
  public $zip_name;
  public $root;

  public function __construct ($root = '.', $zip_name = 'backup.zip') {
    $this -> root = $root;
    $this -> zip_name = $zip_name;
    $this -> zip = new ZipArchive();
    $this -> zip -> open ($this -> zip_name, ZIPARCHIVE::CREATE);
    self::read_dir ();
    self::save ();
  }

  public function read_dir ($path = '') {
    $bdir = scandir ($this -> root . $path);

    foreach ($bdir as $file) {
      if ($file == '.' || $file == '..') continue;
      if (is_dir ($this -> root . $path . '/' . $file)) {
        $this -> zip -> addEmptyDir ($path . '/' . $file);
        if ($file != 'backups') self::read_dir ($path . '/' . $file);
      }
      else {
        $this -> zip -> addFile ($this -> root . $path . '/' . $file, $path . '/' . $file);
      }
    }
  }
  public function save () {
    $this -> zip -> close ();
  }
}
}




//
// ----- Simple RSS reader for MNews Dashboard -----------------------------------------------------
//
function mn_rss_reader($limit = 5) {
  if (@$xml = simplexml_load_file('http://feeds.feedburner.com/mnews-cms')) {
    $i = 0; $output = '';
    foreach ($xml->channel->item as $item) {
      if ($i == $limit) break;
      $output .= '<tr><td class="rss-item"><strong class="title"><a href="' . $item->link . '">' . $item->title . '</a></strong>' . $item->description . '</td></tr>';
      $i++;
    }
  }

  if (empty($output)) {
    global $lang;
    $output = '<tr><td class="c"><em>' . $lang['index_msg_no_rss'] . '</em></td></tr>';
  }

  return $output;
}





function mn_gallery($gallery_id) {

  global $lang, $file, $dir, $mn_tmpl, $mn_url;

  $galleries = load_basic_data('galleries');

  if (is_numeric($gallery_id) && array_key_exists($gallery_id, $galleries)) {

    $f_file = file(MN_ROOT . $file['files']);
    $gallery_files = array();

    foreach ($f_file as $f_line) {
      $f_data = explode('|', $f_line);
      $f_gals = (!empty($f_data[9])) ? explode(',', trim($f_data[9])) : array();

      if (in_array($gallery_id, $f_gals)) $gallery_files[] = $f_line;
      else continue;
    }


    if (is_array($gallery_files) && !empty($gallery_files)) {
      $gallery_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_19.html')) ? $mn_tmpl . '_19' : DEFAULT_TMPL . '_19';
      $gallery_result = '';

      foreach ($gallery_files as $gal_line) {
        $gallery_result .= gallery_tmpl($gal_line, $gallery_tmpl, $mn_url, $gallery_id);
      }

      return $gallery_result;
    }
    else return $lang['web_msg_no_files_in_gallery'];

  }

  else return str_ireplace('%id%', '<b>'.$gallery_id.'</b>', $lang['web_msg_no_gallery']);

}









/***************************************************************************************************
 ****** Special COMMENT functions ******************************************************************
 **************************************************************************************************/



//
// ----- Comment format [BB tags] ------------------------------------------------------------------
//
function comment_format ($str) {
  global $conf;

  $links_target = ($conf['comments_links_target']) ? ' target="_blank"' : '';
  $links_nofollow = ($conf['comments_links_nofollow']) ? ' rel="nofollow"' : '';
  if ($conf['comments_links_auto']) $str = do_clickable($str);

  $bb_search = array(
    '#\[code\](.*?)\[\/code\]#ie',
    '/\[br\]/is',
    '/\[b\](.*)\[\/b\]/isU',
    '/\[i\](.*)\[\/i\]/isU',
    '/\[u\](.*)\[\/u\]/isU',
    '/\[s\](.*)\[\/s\]/isU',
    '/\[url\=(.*?)\](.*?)\[\/url\]/is',
    '/\[url\](.*?)\[\/url\]/is',
    '/\[email\=(.*?)\](.*?)\[\/email\]/is',
    '/\[email\](.*?)\[\/email\]/is',
    '/\[color\=(.*?)\](.*?)\[\/color\]/is',
    '/\[quote\](.*?)\[\/quote\]/is',
  );
  $bb_replace = array(
    'mn_highlight_string(\'$1\')',
    '<br />',
    '<strong>$1</strong>',
    '<em>$1</em>',
    '<span style="text-decoration: underline;">$1</span>',
    '<span style="text-decoration: line-through;">$1</span>',
    '<a href="$1"' . $links_target . $links_nofollow . '>$2</a>',
    '<a href="$1"' . $links_target . $links_nofollow . '>$1</a>',
    '<a href="mailto:$1">$2</a>',
    '<a href="mailto:$1">$1</a>',
    '<span style="color: $1;">$2</span>',
    '<blockquote>$1</blockquote>',
  );
  $bb_no_replace = array(
    'str_replace(\'\', \'\', \'$1\')',
    '<br />',
    '$1',
    '$1',
    '$1',
    '$1',
    '$2: $1',
    '$1',
    '$2: $1',
    '$1',
    '$2',
    '$1',
  );

  $smiles_search = array(
    '/:-?\[/is',
    '/:beer:/is',
    '/8-?\)/is',
    '/[^ehopt][;:],-?\(/is',
    '/\]:-?[\)D]/is',
    '/:fuck:/is',
    '/[^ehopt];-?D+/is',
    '/:jokingly:/is',
    '/:-?\*/is',
    '/:-?D+/is',
    '/:love:/is',
    '/:ninja:/is',
    '/:no:/is',
    '/:pirate:/is',
    '/:-?\(/is',
    '/:-?\)/is',
    '/:stop:/is',
    '/[:8]-?O/is',
    '/[^ehopt][;:]-?P/is',
    '/:-\//is',
    '/:-?\?/is',
    '/:whistle:/is',
    '/[^ehopt];-?\)/is',
    '/:yes:/is',
    '/:zzz:/is',
  );
  $smiles_replace = array(
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-angry.gif" width="16" height="16" alt=":-[" title="Angry" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-beer.gif" width="16" height="16" alt="beer" title="Beer" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-cool.gif" width="16" height="16" alt="8-)" title="Cool" />',
    ' <img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-cry.gif" width="16" height="16" alt=";,(" title="Crying" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-evil.gif" width="16" height="16" alt="evil" title="Evil" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-fuck.gif" width="16" height="16" alt="fuck" title="Fuck" />',
    ' <img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-grin.gif" width="16" height="16" alt=";-D" title="Grin" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-jokingly.gif" width="16" height="16" alt="jokingly" title="Jokingly" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-kiss.gif" width="16" height="16" alt=":-*" title="Kiss" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-lol.gif" width="16" height="16" alt=":-D" title="Laughing out loud" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-love.gif" width="16" height="16" alt="love" title="In love" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-ninja.gif" width="16" height="16" alt="ninja" title="Ninja" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-no.gif" width="16" height="16" alt="no!" title="No!" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-pirate.gif" width="16" height="16" alt="pirate" title="Pirate" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-sad.gif" width="16" height="16" alt=":-(" title="Sad" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-smile.gif" width="16" height="16" alt=":-)" title="Smile" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-stop.gif" width="16" height="16" alt="stop" title="Stop" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-surprised.gif" width="16" height="16" alt=":-O" title="Surprised" />',
    ' <img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-tongue.gif" width="16" height="16" alt=":-P" title="Tongue out" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-undecided.gif" width="16" height="16" alt=":-/" title="Undecided" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-what.gif" width="16" height="16" alt=":-?" title="What?" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-whistle.gif" width="16" height="16" alt="whistle" title="Whistle" />',
    ' <img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-wink.gif" width="16" height="16" alt=";-)" title="Wink" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-yes.gif" width="16" height="16" alt="yes" title="Yes" />',
    '<img src="' . $conf['admin_url'] . '/stuff/img/smiles/smiley-zzz.gif" width="16" height="16" alt="zzz" title="Zzzz" />',
  );

  if ($conf['comments_bb']) {$str = preg_replace($bb_search, $bb_replace, $str);}
  else {$str = preg_replace($bb_search, $bb_no_replace, $str);}
  if ($conf['comments_smiles']) {$str = preg_replace($smiles_search, $smiles_replace, $str);}

  return strip_slashes($str);
}





//
// ----- Highlight source code string in [code] tag ------------------------------------------------
//
function mn_highlight_string ($str) {
  $str = htmlspecialchars_decode($str);
  $str = preg_replace('/\[br\]/is', "\n", $str);
  $str = preg_replace('/\<br[ \/]?\>/is', "\n", $str);
  return highlight_string($str, true);
}





//
// ----- Check comment text ------------------------------------------------------------------------
//
function check_comment_text($str) {
  global $conf;
  
  $str = strip_slashes($str);
  $str = trim($str);
  $str = htmlspecialchars($str);
  $str = str_replace(array('||', DELIMITER, "\r", "\n"), array('OR', DELIMITER_REPLACE, '', ' [BR]'), $str);

  return $str;
}





/**
 * Make hyperlinks clickable
 *
 * @copyright Copyright (C) 2008 PunBB, partially based on code copyright (C) 2008 FluxBB.org
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package PunBB
 */
function do_clickable($text) {
	$text = ' ' . $text;

	$text = preg_replace('#(?<=[\s\]\)])(<)?(\[)?(\()?([\'"]?)(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^\s\[]*[^\s.,?!\[;:-]?)?)\4(?(3)(\)))(?(2)(\]))(?(1)(>))(?![^\s]*\[/(?:url|img)\])#ie', 'stripslashes(\'$1$2$3$4\').handle_url_tag(\'$5://$6\', \'$5://$6\', true).stripslashes(\'$4$10$11$12\')', $text);
	$text = preg_replace('#(?<=[\s\]\)])(<)?(\[)?(\()?([\'"]?)(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^\s\[]*[^\s.,?!\[;:-])?)\4(?(3)(\)))(?(2)(\]))(?(1)(>))(?![^\s]*\[/(?:url|img)\])#ie', 'stripslashes(\'$1$2$3$4\').handle_url_tag(\'$5.$6\', \'$5.$6\', true).stripslashes(\'$4$10$11$12\')', $text);

	return substr($text, 1);
}


function handle_url_tag($url, $link = '') {
  global $conf;
  $links_target = ($conf['comments_links_target']) ? ' target="_blank"' : '';
  $links_nofollow = ($conf['comments_links_nofollow']) ? ' rel="nofollow"' : '';

	$full_url = str_replace(array(' ', '\'', '`', '"'), array('%20', '', '', ''), $url);
	if (strpos($url, 'www.') === 0)			// If it starts with www, we add http://
		$full_url = 'http://' . $full_url;
	elseif (strpos($url, 'ftp.') === 0)	// Else if it starts with ftp, we add ftp://
		$full_url = 'ftp://' . $full_url;
	elseif (!preg_match('#^([a-z0-9]{3,6})://#', $url)) 	// Else if it doesn't start with abcdef://, we add http://
		$full_url = 'http://' . $full_url;

	return '<a href="' . $full_url . '"' . $links_target . $links_nofollow . '>' . $link . '</a>';
}










/***************************************************************************************************
 ****** Frontend TEMPLATE functions ****************************************************************
 **************************************************************************************************/



//
// ----- COMMON DATE/TIME TEMPLATE -----------------------------------------------------------------
//
function common_tmpl($timestamp, $content) {

  global $conf, $lang;

  $tmpl_values_search = array(
    '{DATE}',
    '{DATE_DAY}',
    '{DATE_DAY_ABBR}',
    '{DATE_MONTH}',
    '{DATE_MONTH_ABBR}',
    '{DATE_MONTH_NAME}',
    '{DATE_US}',
    '{DATE_YEAR}',
    '{PUBDATE}',
    '{TIME}',
    '{TIME_AMPM}',
    '{TIME_HOUR}',
    '{TIME_HOUR_US}',
    '{TIME_MIN}',
    '{TIME_NET}',
    '{TIME_US}',
    '{TIMESTAMP}',
    '{URL_ADMIN}',
    '{URL_FILES}',
    '{URL_ICONS}',
    '{URL_IMAGES}',
    '{%MN_URL%}',
  );

  $tmpl_values_replace = array(
    date('d.m.Y', $timestamp),
    date('j', $timestamp),
    date('D', $timestamp),
    date('n', $timestamp),
    date('M', $timestamp),
    $lang['month'][date('n', $timestamp)],
    date('m/d/y', $timestamp),
    date('Y', $timestamp),
    date('D, d M Y H:i:s', $timestamp) . ' GMT',
    date('H:i', $timestamp),
    date('a', $timestamp),
    date('H', $timestamp),
    date('h', $timestamp),
    date('i', $timestamp),
    date('B', $timestamp),
    date('h:i a', $timestamp),
    $timestamp,
    $conf['admin_url'],
    $conf['admin_url'] . '/data/files',
    $conf['admin_url'] . '/stuff/img/icons',
    $conf['admin_url'] . '/data/files/images',
    $conf['admin_url'],
  );


  $tmpl_result = str_ireplace($tmpl_values_search, $tmpl_values_replace, $content);
  return $tmpl_result;
}





//
// ----- xFIELDS TEMPLATE -------------------------------------------------------------------------
//
function xfields_tmpl($section, $section_xfields, $result) {

	global $file;

	if (file_exists(MN_ROOT . $file['xfields'])) {
	
		$xfields = get_unserialized_array('xfields');
		$xfields_search = array();
		$xfields_replace = array();
		$item_xfields = unserialize($section_xfields);
	
		foreach ($xfields as $xVar => $x) {
			if ($x['section'] != $section) continue;
			else {
				$xfields_search[] = '{x' . strtoupper($xVar) . '}';
				$xfields_replace[] = (isset($item_xfields[$xVar]) && !empty($item_xfields[$xVar])) ? $item_xfields[$xVar] : '';
				
				$xfields_search[] = '{xx' . strtoupper($xVar) . '}';
				if ($xfields[$xVar]['type'] == 'select' && is_array($xfields[$xVar]['options']) && array_key_exists($item_xfields[$xVar], $xfields[$xVar]['options'])) {
					$xfields_replace[] = $xfields[$xVar]['options'][$item_xfields[$xVar]];
				}
				else $xfields_replace[] = '';
			}
		}
		
		$result = str_ireplace($xfields_search, $xfields_replace, $result);
	
	}
	
	return $result;

}





//
// ----- POSTS TEMPLATE ----------------------------------------------------------------------------
//
function posts_tmpl($id, $template, $url) {

  global $conf, $dir, $file, $lang, $mn_categories, $mn_tags, $mn_mode, $mn_users;

  if (file_exists(MN_ROOT . $dir['posts'] . 'post_' . $id . '.php') && file_exists(MN_ROOT . $dir['templates'] . $template . '.html')) {

    $p = get_post_data($id);
    $tmpl_file = file_get_contents(MN_ROOT . $dir['templates'] . $template . '.html');



    $comments_count = get_comments_count($id);
    $comments = ($conf['comments']) ? '<a href="' . $url . 'mn_post=' . $id . '#mn-comments">' . $lang['web_comments'] . ' (' . $comments_count . ')</a>' : '';

    if (!empty($p['full_story'])) {
      $tmpl_result = preg_replace('/\[VAR-LINK\](.*?)\[\/VAR-LINK\]/is', '<a href="' . $url . 'mn_post=' . $id . '">$1</a>', $tmpl_file);
      $link = '<a href="' . $url . 'mn_post=' . $id . '">' . $lang['web_post_link'] . '</a>';
    }
    else {
      $tmpl_result = preg_replace('/\[VAR-LINK\](.*?)\[\/VAR-LINK\]/is', '', $tmpl_file);
      $link = '';
    }

    if (!empty($p['author']) && !empty($mn_users[$p['author']])) {
      $author = '<a href="' . $url . 'mn_user=' . $p['author'] . '">' . $mn_users[$p['author']] . '</a>';
      $author_name = $mn_users[$p['author']];
    }
    else {
      $author = '<em class="mn-trivial">' . $lang['web_anonym'] . '</em>';
      $author_name = '<em class="mn-trivial">' . $lang['web_anonym'] . '</em>';
    }

    if (!empty($p['cat']) && !empty($mn_categories[$p['cat']])) {
      $category = '<a href="' . $url . 'mn_cat=' . $p['cat'] . '">' . $mn_categories[$p['cat']] . '</a>';
      $category_id = $p['cat'];
      $category_name = $mn_categories[$p['cat']];
    }
    else {
      $category = '<em class="mn-trivial">' . $lang['web_uncategorized'] . '</em>';
      $category_id = '-1';
      $category_name = '<em class="mn-trivial">' . $lang['web_uncategorized'] . '</em>';
    }

    if (isset($_GET['mn_q']) && $mn_mode != 'rss' && (!preg_match('/[\'$&\/()=%*\"#~|+{}<>]/i', $_GET['mn_q'])) && (strlen($_GET['mn_q']) > 2)) {

      $q_search = '/(>[^<]*)('. $_GET['mn_q'] .')/is';
      $q_replace = '\\1<span class="mn-highlight">\\2</span>';

      $p['title'] = preg_replace('/'. $_GET['mn_q'] .'/is', '<span class="mn-highlight">\\0</span>', $p['title']);
      $p['short_story'] = preg_replace($q_search, $q_replace, $p['short_story']);
      $p['full_story'] = preg_replace($q_search, $q_replace, $p['full_story']);
    }

    if (!empty($p['tags']) && !empty($mn_tags)) {
      $p['tags-array'] = explode(',', $p['tags']);
      $tags = ''; $tag_names = '';
      foreach ($p['tags-array'] as $tag_id) {
        if (empty($mn_tags[$tag_id])) continue;
        else {
          $tags .= '<a href="' . $url . 'mn_tag=' . $tag_id . '">' . $mn_tags[$tag_id] . '</a>, ';
          $tag_names .= $mn_tags[$tag_id] . ', ';
        }
      }
      $tags = substr($tags, 0, -2);
      $tag_names = substr($tag_names, 0, -2);
    }
    else {
      $tags = '';
      $tag_names = '';
    }


    $p_img = explode(';', $p['image']);
    if (!empty($p_img[0]) && !empty($p_img[1]) && !empty($p_img[2])) {
      $image_filename = $p_img[0];
      $image_url = $conf['admin_url'] . '/' . $dir['images'] . $image_filename;
      $image_width = $p_img[1];
      $image_height = $p_img[2];
      $image = '<img src="' . $image_url . '" width="' . $image_width . '" height="' . $image_height . '" alt="' . $p['title'] . '" class="mn-post-image" />';
    }
    else {
      $image = $image_url = $image_filename = $image_width = $image_height = '';
    }




    if ($mn_mode == 'rss') {
      $title = htmlspecialchars($p['title'], ENT_QUOTES);
      $link = $url . 'mn_post=' . $id;
      $p['title'] = htmlspecialchars($p['title'], ENT_QUOTES);
      $short_story = htmlspecialchars($p['short_story'], ENT_QUOTES);
      $p['full_story'] = htmlspecialchars($p['full_story'], ENT_QUOTES);
    }
    elseif (substr($template, -2, 2) == '10') {
      $title = $p['title'];
      $short_story = $p['short_story'] . $p['full_story'];
    }
    else {
      $title = '<a href="' . $url . 'mn_post=' . $id . '">' . $p['title'] . '</a>';
      $short_story = $p['short_story'];
      if ($conf['admin_icons'] && isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_logged'])) $title .= ' <a href="' . $conf['admin_url'] . '/mn-posts.php?action=edit&amp;id=' . $id . '"><img src="' . $conf['admin_url'] . '/stuff/img/icons/edit-gray.png" alt="edit" /></a> <a href="' . $conf['admin_url'] . '/mn-posts.php?action=delete&amp;id=' . $id . '&amp;nofancy"><img src="' . $conf['admin_url'] . '/stuff/img/icons/cross-gray.png" alt="delete" /></a>';
    }



    $tmpl_values_search = array(
      '{AUTHOR}',
      '{AUTHOR_ID}',
      '{AUTHOR_NAME}',
      '{CATEGORY}',
      '{CATEGORY_ID}',
      '{CATEGORY_NAME}',
      '{COMMENTS}',
      '{COMMENTS_COUNT}',
      '{FRIENDLY_URL}',
      '{IMAGE}',
      '{IMAGE_FILENAME}',
      '{IMAGE_URL}',
      '{IMAGE_WIDTH}',
      '{IMAGE_HEIGHT}',
      '{LINK}',
      '{POST_ID}',
      '{POST_URL}',
      '{TAGS}',
      '{TAG_NAMES}',
      '{TEXT}',
      '{TEXT_FULL}',
      '{TEXT_LONG}',
      '{TEXT_PEREX}',
      '{TITLE}',
      '{TITLE_PLAIN}',
      '{VIEWS}',
      '{VIEWS_COUNT}',
    );

    $tmpl_values_replace = array(
      $author,
      $p['author'],
      $author_name,
      $category,
      $category_id,
      $category_name,
      $comments,
      $comments_count,
      $p['friendly_url'],
      $image,
      $image_filename,
      $image_url,
      $image_width,
      $image_height,
      $link,
      $id,
      $url . 'mn_post=' . $id,
      $tags,
      $tag_names,
      $short_story,
      $p['short_story'] . $p['full_story'],
      $p['full_story'],
      $p['short_story'],
      $title,
      $p['title'],
      $lang['web_viewed'] . ': ' . $p['views'] . '&times;',
      $p['views'],
    );


    $result = str_ireplace($tmpl_values_search, $tmpl_values_replace, $tmpl_result);
    $result = ($mn_mode != 'rss') ? preg_replace('/\[PERM-LINK\](.*?)\[\/PERM-LINK\]/is', '<a href="' . $url . 'mn_post=' . $id . '">$1</a>', $result) : preg_replace('/\[PERM-LINK\](.*?)\[\/PERM-LINK\]/is', '$1', $result);
    $result = preg_replace('/\[LINK\](.*?)\[\/LINK\]/is', '<a href="' . $url . 'mn_post=' . $id . '">$1</a>', $result);
    $result = preg_replace('#\[mn_gallery=(.*?)\]#ie', 'mn_gallery(\'$1\')', $result);

    $result = common_tmpl($p['timestamp'], $result);
    $result = xfields_tmpl('posts', $p['xfields'], $result);


    return $result;
  }

  else return '<p>' . $lang['web_msg_no_post_or_tmpl'] . '</p>';
}





//
// ----- PAGE TEMPLATE -----------------------------------------------------------------------------
//
function page_tmpl($id, $template, $url) {
  global $dir, $file, $conf, $mn_users;
  $p = get_page_data($id);
  $tmpl_file = (file_exists(MN_ROOT . $dir['templates'] . $template . '.html')) ? file_get_contents(MN_ROOT . $dir['templates'] . $template . '.html') : file_get_contents(MN_ROOT . $dir['templates'] . DEFAULT_TMPL . '_11.html');

  $tmpl_search = array(
    '{AUTHOR}',
    '{FRIENDLY_URL}',
    '{PAGE_ID}',
    '{TEXT}',
    '{TITLE}',
  );

  $tmpl_replace = array(
    '<a href="' . $url . 'mn_user=' . $p['author'] . '">' . $mn_users[$p['author']] . '</a>',
    $p['friendly_url'],
    $p['id'],
    $p['text'],
    $p['title'],
  );


  $result = str_ireplace($tmpl_search, $tmpl_replace, $tmpl_file);
  $result = preg_replace('#\[mn_gallery=(.*?)\]#ie', 'mn_gallery(\'$1\')', $result);

  $result = common_tmpl($p['timestamp'], $result);
  $result = xfields_tmpl('pages', $p['xfields'], $result);


  return $result;

}





//
// ----- USER'S PROFILE TEMPLATE -------------------------------------------------------------------
//
function user_tmpl($id, $template, $url) {

  global $dir, $file, $conf, $lang;
  $tmpl_file = (file_exists(MN_ROOT . $dir['templates'] . $template . '.html')) ? file_get_contents(MN_ROOT . $dir['templates'] . $template . '.html') : file_get_contents(MN_ROOT . $dir['templates'] . DEFAULT_TMPL . '_13.html');

  $u = get_values('users', $id);
  $g = load_basic_data('groups', $u['group']);

  if (!empty($u['birthdate'])) {
    $b_items = explode('-', $u['birthdate']);
    $bday = $b_items[2] . '.' . $b_items[1] . '.' . $b_items[0];
  }
  else {
    $bday = '';
  }

  $gender = (!empty($u['gender'])) ? $lang['users_gender_' . $u['gender']] : '';

  $posts_count = get_posts_count('users');
  $user_posts_count = (empty($posts_count[$u['user_id']])) ? '0' : $posts_count[$u['user_id']];
  
  if (isset($u['avatar']) && !empty($u['avatar'])) {
    list($avatar_file, $avatar_ext, $avatar_width, $avatar_height) = explode(';', $u['avatar']);
    $avatar = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext . '" class="mn-avatar" alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_standard'] . '" height="' . @$conf['users_avatar_standard'] . '" />';
    $avatar_small = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '-small.' . $avatar_ext . '" class="mn-avatar-small"  alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_small'] . '" height="' . @$conf['users_avatar_small'] . '" />';
    $avatar_mini = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '-mini.' . $avatar_ext . '" class="mn-avatar-mini"  alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_mini'] . '" height="' . @$conf['users_avatar_mini'] . '" />';
  }
  else {
    $avatar = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar.jpg" class="mn-avatar mn-avatar-anonymous" alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_standard'] . '" height="' . @$conf['users_avatar_standard'] . '" />';
    $avatar_small = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar-small.jpg" class="mn-avatar-small mn-avatar-anonymous" alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_small'] . '" height="' . @$conf['users_avatar_small'] . '" />';
    $avatar_mini = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar-mini.jpg" class="mn-avatar-mini mn-avatar-anonymous" alt="' . $u['username'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_mini'] . '" height="' . @$conf['users_avatar_mini'] . '" />';
  }

  $tmpl_search = array(
    '{BIRTHDAY}',
    '{ABOUT}',
    '{AVATAR}',
    '{AVATAR_SMALL}',
    '{AVATAR_MINI}',
    '{EMAIL}',
    '{GENDER}',
    '{GROUP}',
    '{ICQ}',
    '{JABBER}',
    '{LOCATION}',
    '{MSN}',
    '{NICKNAME}',
    '{OTHER1}',
    '{OTHER2}',
    '{POSTS_COUNT}',
    '{SKYPE}',
    '{USER_ID}',
    '{USERNAME}',
    '{WWW}',
  );

  $tmpl_replace = array(
    $bday,
    $u['about'],
    $avatar,
    $avatar_small,
    $avatar_mini,
    $u['email'],
    $gender,
    $g[$u['group']],
    $u['icq'],
    $u['jabber'],
    $u['location'],
    $u['msn'],
    $u['nickname'],
    $u['other1'],
    $u['other2'],
    $user_posts_count,
    $u['skype'],
    $u['user_id'],
    $u['username'],
    $u['www'],
  );

  $result = str_ireplace($tmpl_search, $tmpl_replace, $tmpl_file);

  $result = common_tmpl('0', $result);
  $result = xfields_tmpl('users', $u['xfields'], $result);
  
  
  return $result;
}





//
// ----- COMMENTS TEMPLATE -------------------------------------------------------------------------
//
function comment_tmpl($template, $url, $num) {
  global $comment, $dir, $conf, $lang, $mn_comm_users;

  $tmpl_file = (file_exists(MN_ROOT . $dir['templates'] . $template . '.html')) ? file_get_contents(MN_ROOT . $dir['templates'] . $template . '.html') : file_get_contents(MN_ROOT . $dir['templates'] . DEFAULT_TMPL . '_12.html');

  $target = ($conf['comments_links_target']) ? ' target="_blank"' : '';
  $rel = ($conf['comments_links_nofollow']) ? ' rel="nofollow"' : '';

  $author = (!empty($comment['author_id'])) ? '<a href="' . $url . 'mn_user=' . $comment['author_id'] . '">' . $comment['author_name'] . '</a>' : $comment['author_name'];
  $text = ($comment['status'] == 3) ? '<em>*** ' . $lang['web_msg_comment_hidden'] . ' ***</em>' : comment_format($comment['comment_text']);
  $email = (!empty($comment['author_email'])) ? '<a href="mailto:' . $comment['author_email'] . '">$1</a>' : '';
  $www = (!empty($comment['author_www'])) ? '<a href="' . $comment['author_www'] . '"' . $target . $rel . '>$1</a>' : '';
  
  if (isset($mn_comm_users[$comment['author_id']]['avatar']) && !empty($mn_comm_users[$comment['author_id']]['avatar'])) {
    list($avatar_file, $avatar_ext, $avatar_width, $avatar_height) = explode(';', $mn_comm_users[$comment['author_id']]['avatar']);
    $avatar = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext . '" class="mn-avatar"  alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_standard'] . '" height="' . @$conf['users_avatar_standard'] . '" />';
    $avatar_small = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '-small.' . $avatar_ext . '" class="mn-avatar-small"  alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_small'] . '" height="' . @$conf['users_avatar_small'] . '" />';
    $avatar_mini = '<img src="' . $conf['admin_url'] . '/' . $dir['avatars'] . $avatar_file . '-mini.' . $avatar_ext . '" class="mn-avatar-mini"  alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_mini'] . '" height="' . @$conf['users_avatar_mini'] . '" />';
  }
  else {
    $avatar = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar.jpg" class="mn-avatar mn-avatar-anonymous" alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_standard'] . '" height="' . @$conf['users_avatar_standard'] . '" />';
    $avatar_small = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar-small.jpg" class="mn-avatar-small mn-avatar-anonymous" alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_small'] . '" height="' . @$conf['users_avatar_small'] . '" />';
    $avatar_mini = '<img src="' . $conf['admin_url'] . '/stuff/img/default-avatar-mini.jpg" class="mn-avatar-mini mn-avatar-anonymous" alt="' . $comment['author_name'] . ' ' . $lang['users_avatar']. '" width="' . @$conf['users_avatar_mini'] . '" height="' . @$conf['users_avatar_mini'] . '" />';
  }

  $ua_info = get_useragent_info($comment['user_agent']);

  $tmpl_search = array(
    '{AUTHOR}',
    '{AUTHOR_NAME}',
    '{AVATAR}',
    '{AVATAR_SMALL}',
    '{AVATAR_MINI}',
    '{BROWSER}',
    '{BROWSER_ICON}',
    '{COMMENT_ID}',
    '{COMMENT_NUM}',
    '{IP_ADDRESS}',
    '{OS}',
    '{PLATFORM}',
    '{TEXT}',
  );

  $tmpl_replace = array(
    $author,
    $comment['author_name'],
    $avatar,
    $avatar_small,
    $avatar_mini,
    $ua_info['browser'],
    '<img src="' . $conf['admin_url'] . '/stuff/img/icons/' . $ua_info['browser_icon'] . '" class="mn-browser-icon" alt="' . $ua_info['browser'] . '" title="' . $ua_info['browser'] . '" width="16" height="16" />',
    $comment['comment_id'],
    $num,
    $comment['ip_address'],
    $ua_info['os'],
    $ua_info['platform'],
    $text,
  );


  $tmpl_preg_search = array(
    '/\[EMAIL\](.*?)\[\/EMAIL\]/is',
    '/\[WWW\](.*?)\[\/WWW\]/is',
  );
  $tmpl_preg_replace = array(
    $email,
    $www,
  );


  $result = str_ireplace($tmpl_search, $tmpl_replace, $tmpl_file);
  $result = preg_replace($tmpl_preg_search, $tmpl_preg_replace, $result);

  $result = common_tmpl($comment['timestamp'], $result);
  $result = xfields_tmpl('comments', $comment['xfields'], $result);
  
  
  return $result;

}





//
// ----- GALLERY TEMPLATE --------------------------------------------------------------------------
//
function gallery_tmpl($line, $template, $url, $gallery_id) {
  global $dir, $conf, $default_template;

  $g = get_values('files', $line, false);
  $tmpl_file = (file_exists(MN_ROOT . $dir['templates'] . $template . '.html')) ? file_get_contents(MN_ROOT . $dir['templates'] . $template . '.html') : $default_template[19];
  
  $image_url = $conf['admin_url'] . '/' . $dir['images'] . $g['filename'] . '.' . $g['ext'];
  $thumb_url = (file_exists(MN_ROOT . $dir['thumbs'] . '_' . $g['filename'] . '.' . $g['ext'])) ? $conf['admin_url'] . '/' . $dir['thumbs'] . '_' . $g['filename'] . '.' . $g['ext'] : $image_url;

  $tmpl_search = array(
    '{IMAGE}',
    '{IMAGE_DESCRIPTION}',
    '{IMAGE_EXT}',
    '{IMAGE_FILE}',
    '{IMAGE_FILENAME}',
    '{IMAGE_FILESIZE}',
    '{IMAGE_HEIGHT}',
    '{IMAGE_TITLE}',
    '{IMAGE_URL}',
    '{IMAGE_WIDTH}',
    '{GALLERY_ID}',
    '{THUMBNAIL}',
    '{THUMBNAIL_URL}',
  );

  $tmpl_replace = array(
    '<img src="' . $image_url . '" alt="" width="' . $g['img_width'] . '" height="' . $g['img_height'] . '" />',
    $g['description'],
    $g['ext'],
    $g['filename'] . '.' . $g['ext'],
    $g['filename'],
    get_file_size($g['filesize'], 2, false),
    $g['img_height'],
    $g['title'],
    $image_url,
    $g['img_width'],
    $gallery_id,
    '<img src="' . $thumb_url . '" alt="" />',
    $thumb_url,
  );


  $result = str_ireplace($tmpl_search, $tmpl_replace, $tmpl_file);
  $result = common_tmpl($g['timestamp'], $result);
  return $result;

}










/***************************************************************************************************
 ****** Admin TEMPLATE functions *******************************************************************
 **************************************************************************************************/



//
// ----- OVERALL HEADER ----------------------------------------------------------------------------
//
function overall_header($title, $info_text = '', $info = '', $wysiwyg = false, $additional_text = '') {
  global $lang, $languages, $conf, $lng;

  $info_images = array('ok' => 'ok', 'main' => 'blank', 'info' => 'information', 'error' => 'exclamation', 'warning' => 'warning');
  $info_message = (!empty($info) && !empty($info_text)) ? '<div class="i-' . $info . '"><img src="./stuff/img/icons/' . $info_images[$info] . '.png" alt="" width="16" height="16" />&nbsp;' . $info_text . '</div>' : '<div class="info-empty">&nbsp;</div>';
  $info_message .= $additional_text;
  if ($wysiwyg && @$_GET['wysiwyg'] != 'off' && $conf['admin_wysiwyg'] != false) {
    $wysiwyg_setup = file_get_contents(MN_ROOT . 'stuff/inc/tmpl/wysiwyg-true.php');
    $wysiwyg_lang = (file_exists(MN_ROOT . 'stuff/etc/tinymce/langs/' . $lng . '.js')) ? $lng : 'en';
    $wysiwyg_content = str_ireplace('{LANG}', $wysiwyg_lang, $wysiwyg_setup);
  }
  else $wysiwyg_content = '';

  $theme = (isset($conf['admin_theme']) && !empty($conf['admin_theme']) && is_dir(MN_ROOT . DIR_THEMES . $conf['admin_theme'])) ? $conf['admin_theme'] : 'bluedee';

  $menu = ''; $i = 0;
  if ($_SESSION['mn_user_auth'][1] > 0) {$menu .= '<li><a href="./mn-posts.php"><img src="./stuff/img/icons/posts.png" alt="" /> <span>' . $lang['posts_posts'] . '</span></a></li>'; $i++;}
  if ($_SESSION['mn_user_auth'][3] > 0) {$menu .= '<li><a href="./mn-comments.php"><img src="./stuff/img/icons/comments-gray.png" alt="" /> <span>' . $lang['comm_comments'] . '</span></a></li>'; $i++;}
  if ($_SESSION['mn_user_auth'][4] > 0) {$menu .= '<li><a href="./mn-pages.php"><img src="./stuff/img/icons/pages-gray.png" alt="" /> <span>' . $lang['pages_pages'] . '</span></a></li>'; $i++;}
  if ($_SESSION['mn_user_auth'][5] > 0) {$menu .= '<li><a href="./mn-files.php"><img src="./stuff/img/icons/folders-gray.png" alt="" /> <span>' . $lang['files_files'] . '</span></a></li>'; $i++;}
  if ($_SESSION['mn_user_auth'][6] > 0) {$menu .= '<li><a href="./mn-users.php"><img src="./stuff/img/icons/group-gray.png" alt="" /> <span>' . $lang['users_users'] . '</span></a></li>'; $i++;}
  if (substr($_SESSION['mn_user_auth'], 8, 5) != '000000') {$menu .= '<li><a href="./mn-tools.php"><img src="./stuff/img/icons/tools-gray.png" alt="" /> <span>' . $lang['tools_tools'] . '</span></a></li>'; $i++;}
  if ($i < 6 && !empty($menu)) $menu .= '<li><a href="./mn-profile.php"><img src="./stuff/img/icons/user-gray.png" alt="" /> <span>' . $lang['uni_profile'] . '</span></a></li>';

  if (!empty($menu)) $menu = '<ul class="menu">' . $menu . '</ul>';
  
  $lang_select = '<ul id="lang-select" class="hide">';
  foreach ($languages as $lang_abbr => $lang_name) {
    if (!file_exists(MN_ROOT . 'stuff/lang/lang_' . $lang_abbr . '.php')) continue;
    elseif ($lang_abbr == $lng) {$lang_select .= '<li><span class="act"><img src="./stuff/lang/lang_' . $lang_abbr . '-gray.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></span></li>';}
    else {$lang_select .= '<li><a href="./mn-login.php?l=' . $lang_abbr . '" class="round"><img src="./stuff/lang/lang_' . $lang_abbr . '.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></a></li>';}
  }
  $lang_select .= '</ul>';
  
  $header_subtitle = (isset($conf['web_title_header']) && $conf['web_title_header'] && !empty($conf['web_title'])) ? $conf['web_title'] : '';
  $meta_tags = '<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="author" content="all: Milan Nemčík, mailto: milan@nemcik.sk" />
  <link rel="stylesheet" type="text/css" href="./stuff/etc/style.php" media="all" />
  <link rel="shortcut icon" href="./stuff/img/favicon.png" />
  <script type="text/javascript" src="./stuff/etc/jquery-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-scripts.js"></script>';


  $header_tmpl_search = array(
    '{INFO_MESSAGE}',
    '{LANG_ABBR}',
    '{LANG_SELECT_BAR}',
    '{LOGOUT}',
    '{MENU}',
    '{META_TAGS}',
    '{PROFILE}',
    '{SUBTITLE}',
    '{THEME}',
    '{TITLE}',
    '{USERNAME}',
    '{WEB}',
    '{WYSIWYG}',
  );
  $header_tmpl_replace = array(
    $info_message,
    $lng,
    $lang_select . '<img src="./stuff/lang/lang_' . $lng . '-gray.gif" alt="" id="lang-img" class="tooltip" title="Change language" />',
    '<a href="./mn-login.php?action=logout">' . $lang['uni_logout'] . '</a>',
    $menu,
    $meta_tags,
    '<a href="./mn-profile.php">' . $lang['uni_profile'] . '</a>',
	$header_subtitle,
    $theme,
    $title,
    $_SESSION['mn_user_name'],
    '<a href="../">web</a>',
    $wysiwyg_content,
  );


  $header_tmpl = file_get_contents(MN_ROOT . DIR_THEMES . $theme . '/overall-header.html');
  $header_tmpl_result = str_ireplace($header_tmpl_search, $header_tmpl_replace, $header_tmpl);
  echo $header_tmpl_result;
}





//
// ----- INSTALL HEADER -----------------------------------------------------------------------------
//
function install_header($title, $info_text = '', $info = '', $additional_text = '') {
  global $lang, $languages, $conf, $lng;

  $info_images = array('ok' => 'ok', 'main' => 'blank', 'info' => 'information', 'error' => 'exclamation', 'warning' => 'warning');
  $info_message = (!empty($info) && !empty($info_text)) ? '<div class="i-' . $info . '"><img src="./stuff/img/icons/' . $info_images[$info] . '.png" alt="' . $info_images[$info] . '" />&nbsp;' . $info_text . '</div>' : '<div class="info-empty">&nbsp;</div>';
  $info_message .= $additional_text;

  $switch_button = (!isset($_GET['update'])) ? $lang['install_installation'] . ' / <a href="./install.php?update">' . $lang['install_update'] . '</a>' : '<a href="./install.php?install">' . $lang['install_installation'] . '</a> / ' . $lang['install_update'];
  $theme = (isset($conf['admin_theme']) && !empty($conf['admin_theme']) && is_dir(MN_ROOT . DIR_THEMES . $conf['admin_theme'])) ? $conf['admin_theme'] : 'bluedee';
  
  $lang_select = '<ul id="lang-select" class="hide">';
  foreach ($languages as $lang_abbr => $lang_name) {
    if ($lang_abbr == $lng) {$lang_select .= '<li><span class="act"><img src="./stuff/lang/lang_' . $lang_abbr . '-gray.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></span></li>';}
    else $lang_select .= '<li><a href="./mn-login.php?l=' . $lang_abbr . '" class="round"><img src="./stuff/lang/lang_' . $lang_abbr . '.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></a></li>';
  }
  $lang_select .= '</ul>';
  
  $meta_tags = '<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="author" content="all: Milan Nemčík, mailto: milan@nemcik.sk" />
  <link rel="stylesheet" type="text/css" href="./stuff/etc/style.php?install" media="all" />
  <link rel="shortcut icon" href="./stuff/img/favicon.png" />
  <script type="text/javascript" src="./stuff/etc/jquery-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-scripts.js"></script>';

  $install_tmpl_search = array(
    '{INFO_MESSAGE}',
    '{LANG_ABBR}',
    '{LANG_SELECT_BAR}',
    '{META_TAGS}',
    '{SWITCH_BUTTON}',
    '{THEME}',
    '{TITLE}',
  );
  $install_tmpl_replace = array(
    $info_message,
    $lng,
    $lang_select . '<img src="./stuff/lang/lang_' . $lng . '-gray.gif" alt="" id="lang-img" class="tooltip" title="Change language" />',
    $meta_tags,
    $switch_button,
    $theme,
    $title,
  );


  $install_tmpl = file_get_contents(MN_ROOT . DIR_THEMES . $theme . '/install-header.html');
  $install_tmpl_result = str_ireplace($install_tmpl_search, $install_tmpl_replace, $install_tmpl);
  echo $install_tmpl_result;
}





//
// ----- OVERALL FOOTER ----------------------------------------------------------------------------
//
function overall_footer() {
  global $conf;

  $dev = (DEBUG == true  || isset($_GET['debug'])) ? ' <span class="help">dev</a>' : '';
  $footer_tmpl_search = array(
    '{COPYRIGHT}',
  );
  $footer_tmpl_replace = array(
    'Powered by <a href="http://mnewscms.com/">MNews ' . MN_VERSION . $dev . '</a> &copy; 2008&ndash;' . date('Y'),
  );

  $theme = (isset($conf['admin_theme']) && !empty($conf['admin_theme']) && is_dir(MN_ROOT . DIR_THEMES . $conf['admin_theme'])) ? $conf['admin_theme'] : 'bluedee';

  $footer_tmpl = file_get_contents(MN_ROOT . DIR_THEMES . $theme . '/overall-footer.html');
  $footer_tmpl_result = str_ireplace($footer_tmpl_search, $footer_tmpl_replace, $footer_tmpl);
  echo $footer_tmpl_result;
}





//
// ----- LOGIN SCREEN ------------------------------------------------------------------------------
//
function login_screen($title, $info_text = '', $info = '') {
  global $conf, $lang, $languages, $lng, $var;

  $info_image = array('error' => 'cross', 'info' => 'information', 'main' => 'blank', 'ok' => 'tick', 'warning' => 'exclamation');
  $info_message = (empty($info_text)) ? '<div class="login-msg">&nbsp;</div>' : '<div class="login-msg msg-' . $info . '"><img src="./stuff/img/icons/' . $info_image[$info] . '.png" alt="-" /> ' . $info_text . '</div>';

  $delimiter = (isset($conf['users_registration']) && $conf['users_registration']) ? ' | ' : '';
  if (isset($_COOKIE['mn_user_name']) && preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_COOKIE['mn_user_name'])) $reg_link = '<a href="./mn-login.php?action=logout&amp;redir=referer">' . $lang['login_delete_cookies'] . '</a>';
  elseif (isset($conf['users_registration']) && $conf['users_registration']) $reg_link = '<a href="./mn-login.php?action=register">' . $lang['login_register'] . '</a>';
  else $reg_link = '';
  $lost_pass_link = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'lost-pass') ? '<a href="./mn-login.php">' . $lang['login_log_in'] . '</a>' : '<a href="./mn-login.php?action=lost-pass">' . $lang['login_lost_pass'] . '</a>';

  if (isset($_POST['user_login']) && preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['user_login'])) {$user_login_value = $_POST['user_login'];}
  elseif (isset($_COOKIE['mn_user_name']) && preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_COOKIE['mn_user_name'])) {$user_login_value = $_COOKIE['mn_user_name'];}
  else {$user_login_value = '';}

  if ($var['hide_form']) {
    $reg_link = ''; $lost_pass_link = ''; $checkbox = ''; $action = ''; $button = '';
    if (isset($_GET['install-file'])) {
      $inputs = '<p class="c">' . $lang['login_text_install_file1'] . '</p>';
      $inputs .= '<p class="c">' . $lang['login_text_install_file2'] . ' <a href="./"><img src="./stuff/img/icons/refresh-gray.png" alt="" /></a></p>';
    }
  }
  elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'lost-pass') {
    $inputs =  '<div id="login-lostpass"><label for="user_login">' . $lang['login_user_login'] . '</label> <input type="text" name="user_login" id="user_login" class="text" autocomplete="off" /> <label for="user_mail">' . $lang['login_user_email'] . '</label> <input type="text" name="user_mail" id="user_mail" class="text" autocomplete="off" /></div>';
    $button = '<input type="hidden" name="action" value="lost-pass" /><button type="submit"><img src="./stuff/img/icons/email-go.png" alt="-" /> ' . $lang['login_send_pass'] . '</button>';
    $checkbox = '';
    $action = 'lost-pass';
  }
  elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'register' && ($conf['users_registration'])) {
    $inputs =  '<div id="login-register">';
    $inputs .= '<label for="username">' . $lang['login_user_login_short'] . '</label> <input type="text" name="username" id="username" class="text" value="' . $var['username'] . '" autocomplete="off" /> <img src="./stuff/img/icons/help.png" class="tooltip" alt="?" width="16" height="16" title="' . $lang['users_help_username'] . '" /><br />';
    $inputs .= '<label for="email">' . $lang['login_user_email'] . '</label> <input type="text" name="email" id="email" class="text" value="' . $var['email'] . '" autocomplete="off" /> <img src="./stuff/img/icons/help.png" class="tooltip" alt="?" width="16" height="16" title="' . $lang['users_help_email'] . '" /><br />';
    $inputs .= '<label for="pass1">' . $lang['login_user_password'] . '</label> <input type="password" name="pass1" id="pass1" class="text" autocomplete="off" /> <img src="./stuff/img/icons/help.png" class="tooltip" alt="?" width="16" height="16" title="' . $lang['users_help_pass1'] . '" /><br />';
    $inputs .= '<label for="pass2">' . $lang['login_user_password_verify'] . '</label> <input type="password" name="pass2" id="pass2" class="text" autocomplete="off" /> <img src="./stuff/img/icons/help.png" class="tooltip" alt="?" width="16" height="16" title="' . $lang['users_help_pass2'] . '" />';
    $inputs .= '</div>';
    $button = '<span id="spam-span">' . str_replace('%n%', '<strong>' . $lang['num'][$conf['comments_antispam']] . '</strong>', encoding($lang['comm_antispam'])) . ': <input type="text" name="robot" id="spam-input" style="width:20px;" value="" /><br /></span>';
    $button .= '<input type="hidden" name="action" value="register" /><button type="submit"><img src="./stuff/img/icons/user-go.png" alt="-" /> ' . $lang['login_register'] . '</button>';
    $button .= '<script type="text/javascript">document.getElementById(\'spam-input\').value=\'' . $conf['comments_antispam'] . '\'; document.getElementById(\'spam-span\').style.display = \'none\';</script>';
    $checkbox = '';
    $action = 'register';
    $reg_link = '<a href="./mn-login.php">' . $lang['login_log_in'] . '</a>';
  }
  else {
    $inputs =  '<div id="login-login"><label for="user_login" id="label_login">' . $lang['login_user_login'] . '</label> <input type="text" name="user_login" id="user_login" class="text" value="' . $user_login_value . '" autocomplete="off" /> <label for="user_pass">' . $lang['login_user_password'] . '</label> <input type="password" name="user_pass" id="user_pass" class="text" autocomplete="off" /></div>';
    $button = '<input type="hidden" name="action" value="login" /><button type="submit"><img src="./stuff/img/icons/key-go.png" alt="&raquo;" /> ' . $lang['login_log_in'] . '</button>';
    $checkbox = ($conf['users_perm_login']) ? '<p class="pl"><input type="checkbox" class="checkbox" name="perm_login" id="perm_login" value="true" ' . ((isset($_POST['perm_login']) && $_POST['perm_login'] == 'true') ? ' checked="checked"' : '') . ' /> <label for="perm_login" class="perm-login">' . $lang['login_permanent_login'] . '</label></p>' : '<p class="pl">&nbsp;</p>';
    $action = 'login';
  }

  $theme = (isset($conf['admin_theme']) && !empty($conf['admin_theme']) && is_dir(MN_ROOT . DIR_THEMES . $conf['admin_theme'])) ? $conf['admin_theme'] : 'bluedee';
  
  $lang_select = '<ul id="lang-select" class="hide">';
  foreach ($languages as $lang_abbr => $lang_name) {
    if (!file_exists(MN_ROOT . 'stuff/lang/lang_' . $lang_abbr . '.php')) continue;
    elseif ($lang_abbr == $lng) {$lang_select .= '<li><span class="act"><img src="./stuff/lang/lang_' . $lang_abbr . '-gray.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></span></li>';}
    else {$lang_select .= '<li><a href="./mn-login.php?l=' . $lang_abbr . '" class="round"><img src="./stuff/lang/lang_' . $lang_abbr . '.gif" alt="' . $lang_abbr . '" /> <span>' . $lang_name . '</span></a></li>';}
  }
  $lang_select .= '</ul>';


  $meta_tags = '<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="author" content="all: Milan Nemčík, mailto: milan@nemcik.sk" />
  <link rel="stylesheet" type="text/css" href="./stuff/etc/style.php" media="all" />
  <link rel="shortcut icon" href="./stuff/img/favicon.png" />
  <script type="text/javascript" src="./stuff/etc/jquery-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-scripts.js"></script>';

  $login_screen_search = array(
    '{ACTION}',
    '{CHECKBOX}',
    '{COPYRIGHT}',
    '{DELIMITER}',
    '{INFO_MESSAGE}',
    '{INPUTS}',
    '{LANG_ABBR}',
    '{LANG_SELECT_BAR}',
    '{LINK_BACK_TO_WEB}',
    '{LINK_LOST_PASS}',
    '{LINK_REGISTER}',
    '{META_TAGS}',
    '{BUTTON}',
    '{THEME}',
    '{TITLE}',
    '{WEB_TITLE}',
  );

  $login_screen_replace = array(
    $action,
    $checkbox,
    '&copy; <a href="http://mnewscms.com/">MNews</a> 2008&ndash;' . date('Y') . ' | mineo <a href="http://mineodesign.sk/">webdesign</a>',
    $delimiter,
    $info_message,
    $inputs,
    $lng,
    $lang_select . '<img src="./stuff/lang/lang_' . $lng . '-gray.gif" alt="" id="lang-img" class="tooltip" title="Change language" />',
    '<a href="../">&laquo; ' . $lang['login_back_to_web'] . '</a>',
    $lost_pass_link,
    $reg_link,
    $meta_tags,
    $button,
    $theme,
    $title,
    $conf['web_title'],
  );

  $login_screen_tmpl = file_get_contents(MN_ROOT . DIR_THEMES . $theme . '/login-screen.html');
  $login_screen_tmpl_result = str_ireplace($login_screen_search, $login_screen_replace, $login_screen_tmpl);
  echo $login_screen_tmpl_result;
}










/***************************************************************************************************
 ****** Compatibility functions ********************************************************************
 **************************************************************************************************/

if (!function_exists('str_ireplace')) {
  function str_ireplace($search, $replace, $string) {
    return str_replace($search, $replace, $string);
  }
}

if (!function_exists('mb_strlen')) {
  function mb_strlen($str) {
    return strlen($str);
  }
}

if (!function_exists('mb_substr')) {
  function mb_substr($str, $s = 0, $l = 1, $e = 'utf-8') {
    return substr($str, $s, $l);
  }
}

if (!function_exists('mb_strtolower')) {
  function mb_strtolower($str, $n) {
    return strtolower($str);
  }
}

if (!function_exists('file_put_contents')) {
  function file_put_contents($filename, $data, $lock) {
    $f = @fopen($filename, 'w');
    if (!$f) {
      return false;
    }
    else {
      $bytes = fwrite($f, $data);
      fclose($f);
      return $bytes;
    }
  }
}

if (!function_exists('file_get_contents')) {
  function file_get_contents($filename) {
    $fhandle = fopen($filename, 'r');
    $fcontents = fread($fhandle, filesize($filename));
    fclose($fhandle);

    return $fcontents;
  }
}

if (!function_exists('checkdnsrr')) {
  function checkdnsrr($arg1, $arg2) {
    return true;
  }
}



  ##### This was a triumph. ########################################################################
  ##### http://youtu.be/NCt2nZF2nLk ################################################################

?>