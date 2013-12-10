<?php


  $session_name = session_name();
  if (isset($_POST['action']) && $_POST['action'] == 'multiupload' && !isset($_POST[$session_name])) {
  	exit;
  }
  elseif (isset($_POST['action']) && $_POST['action'] == 'multiupload') {
  	session_id($_POST[$session_name]);
  }



  include './stuff/inc/mn-start.php';
  $auth = user_auth('5');


  $max_upload = (int)(ini_get('upload_max_filesize'));
  $max_post = (int)(ini_get('post_max_size'));
  $memory_limit = (int)(ini_get('memory_limit'));
  $max_upload_size = min($max_upload, $max_post, $memory_limit);

  
  $folders = get_unserialized_array('folders');
  

  if (!empty($folders)) {

  	$parents = $folders_sorted = array();
  	foreach ($folders as $fId => $f) {
  		if (isset($folders[$fId]['parent_id']) && $folders[$fId]['parent_id'] != 0) $parents[$folders[$fId]['parent_id']] = $fId;
  		$folders_sorted[$fId] = $f['name'];
  	}
  	$folders_sorted = mn_natcasesort($folders_sorted);	

  }





  //
  // ---  Check if files.php exists
  //
  if (!file_exists(MN_ROOT . $file['files']) || (isset($_GET['action']) && $_GET['action'] == 'index')) {

    $img_dir = dir($dir['images']);
    $current_files = array();
    
	if (isset($_GET['action']) && $_GET['action'] == 'index') {
		$f_lines = '';
		$i = 1;
		
		$f_file = file($file['files']);
		array_shift($f_file);
		
		foreach ($f_file as $single_line) {
		  $temp_data = explode(DELIMITER, $single_line);
		  $current_files[] = $temp_data[1] . '.' . $temp_data[2];
		  
		  $f_lines .= $single_line;
		  $i++;
		}
		    
	}
	else {
		$f_lines = '';
		$i = 1;
	}

    while($img_file = $img_dir->read()) {
      if (!is_file($dir['images'] . $img_file) || ($img_file == 'index.html') || substr($img_file, 0, 8) == 'mn_post_') continue;
      elseif (in_array($img_file, $current_files)) continue;
      else {
        list($img_width, $img_height) = getimagesize($dir['images'] . $img_file);
        $img_size = filesize($dir['images'] . $img_file);
        $img_time = filemtime($dir['images'] . $img_file);
        $img_info = pathinfo_utf($dir['images'] . $img_file);
        
        $thumb_size = (isset($conf['admin_thumb_size']) && is_numeric($conf['admin_thumb_size'])) ? $conf['admin_thumb_size'] : $default['thumb_size'];
        if (isset($_GET['action']) && $_GET['action'] == 'index') resize_img($dir['images'] . $img_file, $thumb_size, './' . $dir['thumbs'] . '_' . $img_file);
        
        $f_lines .= $i . DELIMITER . $img_info['filename'] . DELIMITER . $img_info['extension'] . DELIMITER . $img_size . DELIMITER . $img_time . DELIMITER . 'images' . DELIMITER . $img_width . DELIMITER . $img_height . DELIMITER . '1' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . "\n";
        $i++;
      }
    }
    
    $f_content = SAFETY_LINE . DELIMITER . $i . "\n" . $f_lines;
    mn_put_contents($file['files'], $f_content);

	header('Location: ./mn-files.php?back=indexed');
	exit;

  }




  //
  // --- Upload proccess
  //
  elseif (isset($_POST['action']) && ($_POST['action'] == 'quick-upload' || $_POST['action'] == 'multiupload') && isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {
  
	$multiupload = ($_POST['action'] == 'multiupload') ? true : false;
	$file_folder = (isset($_POST['f']) && !empty($_POST['f']) && is_numeric($_POST['f'])) ? $_POST['f'] : '';
	$file_gallery = (isset($_POST['g']) && !empty($_POST['g']) && is_numeric($_POST['g'])) ? $_POST['g'] : '';
    $file_ext = get_ext($_FILES['file']['name']);


	// if file is too big, cancel upload
	if (isset($_FILES['file']['size']) && !empty($_FILES['file']['size']) && $_FILES['file']['size'] > ($max_upload_size * 1024 * 1024)) {
	  
	  if ($multiupload) echo '0';
	  else {
	  	header('location: ./mn-files.php?back=toobig');
	  	exit;
	  }
	}



    // --- Upload image
    elseif (is_image($_FILES['file']['name'])) {

      $source_file = pathinfo_utf($_FILES['file']['name']);
      $clean_file_name = friendly_url($source_file['filename']);
      $clean_file_ext = strtolower($source_file['extension']);
      $clean_file = $clean_file_name . '.' . $clean_file_ext;
      $target_file = './' . $dir['images'] . $clean_file;

      if (file_exists($target_file)) {
        $i = 2;
        while (file_exists($target_file) && $i < 100) {
          $clean_file_name = friendly_url($source_file['filename']) . '-' . $i;
          $clean_file = $clean_file_name . '.' . $clean_file_ext;
          $target_file = './' . $dir['images'] . $clean_file;
          $i++;
        }
      }

      move_uploaded_file($_FILES['file']['tmp_name'], $target_file);
    	list($img_width, $img_height) = getimagesize($target_file);


      if (isset($img_width) && !empty($img_width) && isset($img_height) && !empty($img_height)) {

		$thumb_size = (isset($conf['admin_thumb_size']) && is_numeric($conf['admin_thumb_size'])) ? $conf['admin_thumb_size'] : $default['thumb_size'];
        #if ($img_width > $thumb_size || $img_height > $thumb_size) {
          resize_img($target_file, $thumb_size, './' . $dir['thumbs'] . '_' . $clean_file);
        #}
        
        
        $files_file = file($file['files']);
        $files_file_lines = '';
        foreach ($files_file as $single_line) {
          $file_data = explode(DELIMITER, $single_line);
          if (substr($file_data[0], 0, 2) == '<?') $auto_increment_id = trim($file_data[1]);
          else $files_file_lines .= $single_line;
        }
        
        $file_size = filesize($target_file);
        $files_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $files_file_lines;
        $files_file_content .= $auto_increment_id . DELIMITER . $clean_file_name . DELIMITER . $clean_file_ext . DELIMITER . $file_size . DELIMITER . mn_time() . DELIMITER . 'images' . DELIMITER . $img_width . DELIMITER . $img_height . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $file_gallery . DELIMITER . $file_folder . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . "\n";

        mn_put_contents($file['files'], $files_file_content);

		if ($multiupload && !isset($_GET['mce'])) {
			echo '<tr class="highlight">
        <td><input type="checkbox" name="files[]" value="' . $auto_increment_id . '" class="checkbox" checked="checked" /></td>
        <td class="cell-icon"><img src="./stuff/img/icons/file-image.png" alt="" title="' . $clean_file_ext . '" class="tooltip" width="16" height="16" /></td>
        <td><a href="' . $target_file . '" class="main-link fancyimg" rel="fancygal">' . $clean_file_name . '<span class="ext">.' . $clean_file_ext . '</span></a><br />
          &nbsp;<span class="links hide">
            <a href="./mn-files.php?action=edit&amp;id=' . $auto_increment_id . '">' . $lang['uni_edit'] . '</a> |
            <a href="./mn-files.php?action=delete&amp;id=' . $auto_increment_id . '" class="fancy">' . $lang['uni_delete'] . '</a>
          </span>
        </td>
        <td>' . get_file_size($file_size, 2, false) . '<br /><span class="trivial">' . $img_width . ' &times; ' . $img_height . '</span></td>
        <td>' . $lang['files_dir_images'] . '</td>
        <td>' . $_SESSION['mn_user_name'] . '</td>
        <td>' . date('d.m.Y') . '<br /><span class="trivial">' . date('H:i') . '</span></td>
      </tr>';
		}
		elseif ($multiupload && isset($_GET['mce'])) {
			echo '<div class="gal-item uploaded" id="gal-item' . $auto_increment_id . '"><img src="' . MN_ROOT . $dir['thumbs'] . '_' . $clean_file_name . '.' . $clean_file_ext . '" alt="" style="width: 90%; max-height: 90px;" /><p>' . $clean_file_name . '.' . $clean_file_ext . '<br /><span class="trivial">' . $img_width . ' &times; ' . $img_height . '</span></p><form><input type="hidden" name="f-id" class="f-id" value="' . $auto_increment_id . '" /><input type="hidden" name="f-filename" class="f-filename" value="' . $clean_file_name . '.' . $clean_file_ext . '" /><input type="hidden" name="f-ext" class="f-ext" value="' . $clean_file_ext . '" /><input type="hidden" name="f-url" class="f-url" value="' . $conf['admin_url'] . '/' . $dir['images'] . $clean_file_name . '.' . $clean_file_ext . '" /><input type="hidden" name="f-folder" class="f-folder" value="images" /><input type="hidden" name="f-title" class="f-title" value="" /><input type="hidden" name="f-description" class="f-description" value="" /><input type="hidden" name="f-size" class="f-size" value="' . get_file_size($file_size, 2, false) . '" /><input type="hidden" name="f-thumb" class="f-thumb" value="' . $conf['admin_url'] . '/' . $dir['thumbs'] . '_' . $clean_file_name . '.' . $clean_file_ext. '" /><input type="hidden" name="f-imgsize" class="f-imgsize" value="' . $img_width . ' &times; ' . $img_height . '" /></form></div>';
			die();
		}
		else {
        	header('location: ./mn-files.php?back=success&hl=' . $auto_increment_id);
        	exit;
        }

      }

      else {
        unlink($target_file);
        if ($multiupload) echo '0';
        else {
        	header('location: ./mn-files.php?back=wrongitype');
        	exit;
        }
      }

    }
    
    
    
    // --- Upload other filetype (document, archive, etc)
    elseif (in_array($file_ext, $ext['media']) ||  in_array($file_ext, $ext['others'])) {

      $source_file = pathinfo_utf($_FILES['file']['name']);
      $clean_file_name = friendly_url($source_file['filename']);
      $clean_file_ext = strtolower($source_file['extension']);
      $file_dir = (in_array($file_ext, $ext['media'])) ? 'media' : 'others';
      $target_file = './' . $dir[$file_dir] . $clean_file_name . '.' . $clean_file_ext;
      
      
      if (file_exists($target_file)) {
        $i = 2;
        while (file_exists($target_file) && $i < 100) {
          $clean_file_name = friendly_url($source_file['filename']) . '-' . $i;
          $clean_file = $clean_file_name . '.' . $clean_file_ext;
          $target_file = './' . $dir[$file_dir] . $clean_file;
          $i++;
        }
      }
      
      move_uploaded_file($_FILES['file']['tmp_name'], $target_file);
      
      $files_file = file($file['files']);
      $files_file_lines = '';
      foreach ($files_file as $single_line) {
        $file_data = explode(DELIMITER, $single_line);
        if (substr($file_data[0], 0, 2) == '<?') $auto_increment_id = trim($file_data[1]);
        else $files_file_lines .= $single_line;
      }
      
      $file_size = filesize($target_file);
      $files_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $files_file_lines;
      $files_file_content .= $auto_increment_id . DELIMITER . $clean_file_name . DELIMITER . $clean_file_ext . DELIMITER . $file_size . DELIMITER . mn_time() . DELIMITER . $file_dir . DELIMITER . '' . DELIMITER . '' . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $file_gallery . DELIMITER . $file_folder . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . "\n";

      mn_put_contents($file['files'], $files_file_content);
      
      if ($multiupload && !isset($_GET['mce'])) {
	      echo '<tr class="highlight">
        <td><input type="checkbox" name="files[]" value="' . $auto_increment_id . '" class="checkbox" checked="checked" /></td>
        <td class="cell-icon"><img src="./stuff/img/icons/file-' . $ext_img[strtolower($clean_file_ext)] . '.png" alt="" title="' . $clean_file_ext . '" class="tooltip" width="16" height="16" /></td>
        <td><a href="' . $target_file . '" class="main-link">' . $clean_file_name . '<span class="ext">.' . $clean_file_ext . '</span></a><br />
          &nbsp;<span class="links hide">
            <a href="./mn-files.php?action=edit&amp;id=' . $auto_increment_id . '">' . $lang['uni_edit'] . '</a> |
            <a href="./mn-files.php?action=delete&amp;id=' . $auto_increment_id . '" class="fancy">' . $lang['uni_delete'] . '</a>
          </span>
        </td>
        <td>' . get_file_size($file_size, 2, false) . '</td>
        <td>' . $lang['files_dir_' . $file_dir] . '</td>
        <td>' . $_SESSION['mn_user_name'] . '</td>
        <td>' . date('d.m.Y') . '<br /><span class="trivial">' . date('H:i') . '</span></td>
      </tr>';
      }
      elseif ($multiupload && isset($_GET['mce'])) {
      	echo '<div class="gal-item uploaded" id="gal-item' . $auto_increment_id . '"><span class="simicon f_' . $clean_file_ext . '">' . $clean_file_ext . '</span><p>' . $clean_file_name . '.' . $clean_file_ext . '</p><form><input type="hidden" name="f-id" class="f-id" value="' . $auto_increment_id . '" /><input type="hidden" name="f-filename" class="f-filename" value="' . $clean_file_name . '.' . $clean_file_ext . '" /><input type="hidden" name="f-ext" class="f-ext" value="' . $clean_file_ext . '" /><input type="hidden" name="f-url" class="f-url" value="' . $conf['admin_url'] . '/' . $dir[$file_dir] . $clean_file_name . '.' . $clean_file_ext . '" /><input type="hidden" name="f-folder" class="f-folder" value="' . $file_dir . '" /><input type="hidden" name="f-title" class="f-title" value="" /><input type="hidden" name="f-description" class="f-description" value="" /><input type="hidden" name="f-size" class="f-size" value="' . get_file_size($file_size, 2, false) . '" /></form></div>';
      	die();
      }
      else {
      	header('location: ./mn-files.php?back=success&hl=' . $auto_increment_id);
      	exit;
      }

    }
    
    

    else {
      if ($multiupload) {
      	echo '<tr><td colspan="7"><strong>' . $_FILES['file']['name'] . '</strong>:' . $lang['files_msg_wrong_filetype'] . '</td></tr>';
      }
      else {
      	header('location: ./mn-files.php?back=wrongtype');
      	exit;
      }
    }
  }




  //
  // --- Edit - show form
  //
  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    if ($auth != 1) {
      header('location: ./?access-denied');
      exit;
    }

    $var = get_values('files', $_GET['id']);
    $filename = $var['filename'] . '.' . $var['ext'];
    if ($var['dir'] == 'images') {
      $thumb_file =  (file_exists($dir['thumbs'] . '_' . $filename)) ? $dir['thumbs'] . '_' . $filename : $dir['images'] . $filename;
    }

    if (isset($_GET['back']) && $_GET['back'] == 'edited') {overall_header($lang['files_edit_file'], $lang['files_msg_file_edited'], 'ok');}
    else overall_header($lang['files_edit_file'], $lang['files_edit_file'], 'main');
    $admin_tmpl['edit_img'] = true;
  }





  //
  // --- Edit - proccess
  //
  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['file_id'])) {
    if ($auth != 1) {
      header('location: ./?access-denied');
      exit;
    }
    
    elseif (empty($_POST['filename'])) {
      $var = get_values('files', $_POST['file_id']);
      $filename = $var['filename'] . '.' . $var['ext'];
      if ($var['dir'] == 'images') {
        $thumb_file =  (file_exists($dir['thumbs'] . '_' . $filename)) ? $dir['thumbs'] . '_' . $filename : $dir['images'] . $filename;
      }
      overall_header($lang['files_edit_file'], $lang['files_msg_empty_filename'], 'error');
      $admin_tmpl['edit_img'] = true;
    }
    
    else {
    
      $var = get_values('files', $_POST['file_id']);
      $old_file = $var['filename'] . '.' . $var['ext'];
      $new_file = $_POST['filename'] . '.' . $var['ext'];

      rename(MN_ROOT . $dir[$var['dir']] . $old_file, MN_ROOT . $dir[$var['dir']] . $new_file);
      @mkdir(MN_ROOT . $dir[$var['dir']] . $new_file, 0777);
      @chmod(MN_ROOT . $dir[$var['dir']] . $new_file, 0777);
      if ($var['dir'] == 'images' && file_exists(MN_ROOT . $dir['thumbs'] . '_' . $old_file)) {
        rename(MN_ROOT . $dir['thumbs'] . '_' . $old_file, MN_ROOT . $dir['thumbs'] . '_' . $new_file);
        @mkdir(MN_ROOT . $dir['thumbs'] . '_' . $new_file, 0777);
        @chmod(MN_ROOT . $dir['thumbs'] . '_' . $new_file, 0777);
      }


      $file_galleries = ($var['dir'] == 'images' && isset($_POST['galleries']) && !empty($_POST['galleries'])) ? implode(',', $_POST['galleries']) : '';

      $file_title = check_text($_POST['title'], true);
      $file_description = str_replace(array("\r", "\n"), array('', ''), check_text($_POST['description'], true));

      $files_file = file($file['files']);
      $files_lines = '';
      foreach ($files_file as $single_line) {
        $file_data = explode(DELIMITER, $single_line);
        if ($file_data[0] == $_POST['file_id']) {
          $files_lines .= $var['file_id'] . DELIMITER . $_POST['filename'] . DELIMITER . $var['ext'] . DELIMITER . $var['filesize'] . DELIMITER . $var['timestamp'] . DELIMITER . $var['dir'] . DELIMITER . $var['img_width'] . DELIMITER . $var['img_height'] . DELIMITER . $var['uploader_id'] . DELIMITER . $file_galleries . DELIMITER . (int)$_POST['folder'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $file_title . DELIMITER . $file_description . "\n";
        }
        else $files_lines .= $single_line;
      }

      mn_put_contents($file['files'], $files_lines);
      header('location: ./mn-files.php?action=edit&id=' . $var['file_id'] . '&back=edited');
      exit;
    }

  }





  //
  // --- Bulk edit - show fieldsets
  //
  elseif (isset($_GET['action']) && $_GET['action'] == 'bulk' && isset($_GET['a']) && $_GET['a'] == 'edit' && isset($_GET['files'])) {
  
  	$files_arr = explode(',', $_GET['files']);

	$f_file = file($file['files']);
	$files_result = '';
	
	foreach ($f_file as $single_line) {
	
		$f_data = explode(DELIMITER, $single_line);
		
		if (in_array($f_data[0], $files_arr)) {
			
			if ($f_data[5] == 'images') {
				$f_img = '<a href="./data/files/images/' . $f_data[1] . '.' . $f_data[2] . '" class="fancyimg"><img src="./data/files/images/_thumbs/_' . $f_data[1] . '.' . $f_data[2] . '" alt="" width="16" height="16" class="tooltip" title="<img src=\'./data/files/images/_thumbs/_' . $f_data[1] . '.' . $f_data[2] . '\' alt=\'\' />" /></a>';
			}
			else {
				$f_img = '<img src="./stuff/img/icons/file-' . $ext_img[strtolower($f_data[2])] . '.png" alt="" width="16" height="16" />';
			}
			
			$files_result .= '<fieldset>
				<strong>' . $f_img . ' ' . $f_data[1] . '.' . $f_data[2] . '</strong><br />
				<label for="filename' . $f_data[0] . '">' . $lang['files_filename'] . ':</label> <input type="text" class="text long filename" name="filename[' . $f_data[0] . ']" id="filename' . $f_data[0] . '" value="' . $f_data[1] . '" /><span class="trivial">.' . $f_data[2] . '</span><br />
				<label for="title' . $f_data[0] . '">' . $lang['files_title'] . ':</label> <input type="text" class="text long" name="title[' . $f_data[0] . ']" id="title' . $f_data[0] . '" value="' . $f_data[16] . '" /><br />
				<label for="description' . $f_data[0] . '">' . $lang['files_description'] . ':</label> <input type="text" class="text long" name="description[' . $f_data[0] . ']" id="description' . $f_data[0] . '" value="' . $f_data[17] . '" /><br />
				<input type="hidden" name="files[]" value="' . $f_data[0] . '" />
			</fieldset>';
			
			unset($f_data);
		
		}
		else continue;
	
	}
	
	overall_header($lang['files_edit_files'], $lang['files_edit_files'], 'main');
	$admin_tmpl['bulk_edit'] = true;
  
  }





  //
  // --- Delete - show form
  //
  elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
    if ($auth != 1) {
      header('location: ./?access-denied');
      exit;
    }
    
    $var = get_values('files', $_GET['id']);
    $filename = $var['filename'] . '.' . $var['ext'];
    if ($var['dir'] == 'images') {
      $thumb_file =  (file_exists($dir['thumbs'] . '_' . $filename)) ? $dir['thumbs'] . '_' . $filename : $dir['images'] . $filename;
    }
    
    $admin_tmpl['delete_img'] = true;
  }
  
  
  
  
  
  //
  // --- Delete - proccess
  //
  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['file_id'])) {
    if ($auth != 1) {
      header('location: ./?access-denied');
      exit;
    }
    
    $var = get_values('files', $_POST['file_id']);
    $filepath = MN_ROOT . $dir[$var['dir']] . $var['filename'] . '.' . $var['ext'];
    
    if (file_exists($filepath)) {
      unlink($filepath);
      if (file_exists(MN_ROOT . $dir['thumbs'] . '_' . $var['filename'] . '.' . $var['ext'])) {
        @unlink(MN_ROOT . $dir['thumbs'] . '_' . $var['filename'] . '.' . $var['ext']);
      }

      $files_file = file($file['files']);
      $files_lines = '';
      foreach ($files_file as $single_line) {
        $file_data = explode(DELIMITER, $single_line);
        if ($file_data[0] == $_POST['file_id']) continue;
        else $files_lines .= $single_line;
      }

      mn_put_contents($file['files'], $files_lines);
    }

    header('location: ./mn-files.php?back=deleted');
    exit;
  }




  //
  // --- Bulk proccess
  //
  elseif (isset($_POST['action']) && $_POST['action'] == 'bulk' && isset($_POST['a']) && !empty($_POST['a']) && isset($_POST['files']) && !empty($_POST['files'])) {
  
  
  	if (isset($_POST['a']) && $_POST['a'] == 'doedit') {
  		$arrgs = implode(',', $_POST['files']);
  		header('Location: ./mn-files.php?action=bulk&a=edit&files=' . $arrgs);
  		exit;
  	}


    $files_file = file($file['files']);
    $files_lines = '';
    foreach ($files_file as $single_line) {
      $f_data = explode(DELIMITER, $single_line);


      // bulk delete
      if ($_POST['a'] == 'delete' && in_array($f_data[0], $_POST['files'])) {
        unlink(MN_ROOT . $dir['files'] . $f_data[5] . '/' . $f_data[1] . '.' . $f_data[2]);
        if (file_exists(MN_ROOT . $dir['thumbs'] . '_' . $f_data[1] . '.' . $f_data[2])) {
          @unlink(MN_ROOT . $dir['thumbs'] . '_' . $f_data[1] . '.' . $f_data[2]);
        }
        continue;
      }


      // bulk edit
      elseif ($_POST['a'] == 'edit' && in_array($f_data[0], $_POST['files'])) {
      	
      	$var = get_values('files', $single_line, false);
      	$id = $var['file_id'];
      	$old_file = $var['filename'] . '.' . $var['ext'];
      	$new_filename = (!empty($_POST['filename'][$id])) ? $_POST['filename'][$id] : $var['filename'];
      	$new_file = $new_filename . '.' . $var['ext'];
      	
      	if ($old_file != $new_file) {
	      	rename(MN_ROOT . $dir[$var['dir']] . $old_file, MN_ROOT . $dir[$var['dir']] . $new_file);
	      	@mkdir(MN_ROOT . $dir[$var['dir']] . $new_file, 0777);
	      	@chmod(MN_ROOT . $dir[$var['dir']] . $new_file, 0777);
	      	if ($var['dir'] == 'images' && file_exists(MN_ROOT . $dir['thumbs'] . '_' . $old_file)) {
	      	  rename(MN_ROOT . $dir['thumbs'] . '_' . $old_file, MN_ROOT . $dir['thumbs'] . '_' . $new_file);
	      	  @mkdir(MN_ROOT . $dir['thumbs'] . '_' . $new_file, 0777);
	      	  @chmod(MN_ROOT . $dir['thumbs'] . '_' . $new_file, 0777);
	      	}
      	}
      	
      	$file_title = check_text($_POST['title'][$id], true);
      	$file_description = str_replace(array("\r", "\n"), array('', ''), check_text($_POST['description'][$id], true));
      	
      	$files_lines .= $var['file_id'] . DELIMITER . $new_filename . DELIMITER . $var['ext'] . DELIMITER . $var['filesize'] . DELIMITER . $var['timestamp'] . DELIMITER . $var['dir'] . DELIMITER . $var['img_width'] . DELIMITER . $var['img_height'] . DELIMITER . $var['uploader_id'] . DELIMITER . $var['galleries'] . DELIMITER . $var['folder'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $file_title . DELIMITER . $file_description . "\n";
      	
      }

      // bulk gallery assign
      elseif (isset($_POST['a']) && in_array($f_data[0], $_POST['files'])) {
      
      	if ($_POST['a'][0] == 'g' && isset($f_data[5]) && $f_data[5] == 'images') {
	        $f_gal_arr = explode(',', $f_data[9]);
	        $f_gal_arr[] = str_replace('g', '', $_POST['a']);
	        $f_gal_arr = array_unique($f_gal_arr);
	        $file_galleries = implode(',', $f_gal_arr);
	        $file_folder = $f_data[10];
	    }
	    elseif (is_numeric($_POST['a'])) {
	    	$file_galleries = $f_data[9];
	    	$file_folder = (is_numeric($_POST['a'])) ? $_POST['a'] : $f_data[10];
	    }
	    else {
	    	$file_galleries = $f_data[9];
	    	$file_folder = $f_data[10];
	    }

        $files_lines .= $f_data[0] . DELIMITER . $f_data[1] . DELIMITER . $f_data[2] . DELIMITER . $f_data[3] . DELIMITER . $f_data[4] . DELIMITER . $f_data[5] . DELIMITER . $f_data[6] . DELIMITER . $f_data[7] . DELIMITER . $f_data[8] . DELIMITER . $file_galleries . DELIMITER . $file_folder . DELIMITER . $f_data[11] . DELIMITER . $f_data[12] . DELIMITER . $f_data[13] . DELIMITER . $f_data[14] . DELIMITER . $f_data[15] . DELIMITER . $f_data[16] . DELIMITER . trim($f_data[17]) . "\n";
      }
      else $files_lines .= $single_line;
    }


    mn_put_contents($file['files'], $files_lines);

	if ($_POST['a'] == 'delete') {
		header('Location: ./mn-files.php?back=bulk-deleted');
		exit;
	}
	else {
		header('location: ./mn-files.php?back=bulk-ok&hl=' . implode(',', $_POST['files']));
		exit;
	}

  
  }




  
  //
  // --- Else, show files list
  //
  else {


    if (isset($_GET['ajaxcall'])) {
    	echo '';
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'success') {
      overall_header($lang['files_files'], $lang['files_msg_upload_successful'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'edited') {
      overall_header($lang['files_files'], $lang['files_msg_file_edited'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'bulk-ok') {
      overall_header($lang['files_files'], $lang['files_msg_files_edited'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'bulk-deleted') {
      overall_header($lang['files_files'], $lang['files_msg_files_deleted'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') {
      overall_header($lang['files_files'], $lang['files_msg_file_deleted'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'indexed') {
      overall_header($lang['files_files'], $lang['files_msg_files_indexed'], 'ok');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'toobig') {
      overall_header($lang['files_files'], $lang['files_msg_file_too_big'] . ': ' . $max_upload_size . 'MB.', 'error');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'wrongtype') {
      overall_header($lang['files_files'], $lang['files_msg_wrong_filetype'], 'error');
    }
    elseif (isset($_GET['back']) && $_GET['back'] == 'wrongitype') {
      overall_header($lang['files_files'], $lang['files_msg_wrong_image'], 'error');
    }
    else {
      overall_header($lang['files_files'], $lang['files_files'], 'main');
    }



    $f_file = file($file['files']);
    array_shift($f_file);

    $users = load_basic_data('users');
    $files_result = ''; $files = array();
    $timestamps = array(); $uploaders = array();

    foreach ($f_file as $single_line) {
      $temp_data = explode(DELIMITER, $single_line);

      $timestamps[$temp_data[4]] = date('Y-m', $temp_data[4]);
      $uploaders[$temp_data[8]] = $users[$temp_data[8]];

      $var['galleries_array'] = explode(',', $temp_data[9]);

      if (isset($_GET['u']) && !empty($_GET['u']) && $temp_data[8] != $_GET['u']) continue;
      if (isset($_GET['e']) && !empty($_GET['e']) && $temp_data[2] != $_GET['e']) continue;
      if (isset($_GET['t']) && !empty($_GET['t']) && $temp_data[5] != $_GET['t']) continue;
      if (isset($_GET['f']) && !empty($_GET['f']) && $temp_data[10] != $_GET['f']) continue;
      if (isset($_GET['g']) && !empty($_GET['g']) && !in_array($_GET['g'], $var['galleries_array'])) continue;
      if (isset($_GET['d']) && !empty($_GET['d']) && date('Y-m', $temp_data[4]) != $_GET['d']) continue;
      else $files[$temp_data[0]] = $temp_data[4] . DELIMITER . $temp_data[1] . DELIMITER . $temp_data[2] . DELIMITER . $temp_data[0] . DELIMITER . $temp_data[3] . DELIMITER . $temp_data[5] . DELIMITER . $temp_data[8] . DELIMITER . $temp_data[6] . DELIMITER . $temp_data[7] . DELIMITER . $temp_data[16] . DELIMITER . $temp_data[17] . DELIMITER . $temp_data[10];

    }

    $files = mn_natcasesort($files);
    $files = array_reverse($files);
    $timestamps = array_unique($timestamps);
    $uploaders = array_unique($uploaders);
    $uploaders = mn_natcasesort($uploaders);




    foreach ($files as $file_id => $temp_data) {
      $f_data = explode(DELIMITER, $temp_data);

      $file_image = '<img src="./stuff/img/icons/file-' . $ext_img[strtolower($f_data[2])] . '.png" alt="" title="' . $f_data[2] . '" class="tooltip" width="16" height="16" />';
      $file_class = ($f_data[5] == 'images') ? ' class="main-link fancyimg" rel="fancygal"' : ' class="main-link"';
      $img_size = (isset($f_data[7]) && is_numeric($f_data[7])) ? '<br><span class="trivial">' . $f_data[7] . ' &times; ' . $f_data[8] . '</span>' : '';
      

      $tr_class = '';
      if (isset($_GET['hl'])) {
	      $hl_arr = explode(',', $_GET['hl']);
	      if (in_array($f_data[3], $hl_arr)) {$tr_class = ' class="highlight"';}
      }


      if (isset($_GET['ajaxcall'])) {
      
      	if ($f_data[5] == 'images') {
      		$file_img = '<img src="' . MN_ROOT . $dir['thumbs'] . '_' . $f_data[1] . '.' . $f_data[2] . '" alt="" />';
      	}
      	else {
      		$file_img = '<span class="simicon f_' . $f_data[2] . '">' . strtoupper($f_data[2]) . '</span>';
      	}
      	$file_folder = (isset($f_data[11]) && !empty($f_data[11])) ? $f_data[11] : 0;


      	$files_result .= '<div class="gal-item type-' . $f_data[5] . ' folder-' . $file_folder . '" id="gal-item' . $f_data[3] . '">' . $file_img . '<p>' . $f_data[1] . '.' . $f_data[2] . $img_size . '</p>';
      	$files_result .= '<form><input type="hidden" name="f-id" class="f-id" value="' . $f_data[3] . '" /><input type="hidden" name="f-filename" class="f-filename" value="' . $f_data[1] . '.' . $f_data[2] . '" /><input type="hidden" name="f-ext" class="f-ext" value="' . $f_data[2] . '" /><input type="hidden" name="f-url" class="f-url" value="' . $conf['admin_url'] . '/' . $dir[$f_data[5]] . $f_data[1] . '.' . $f_data[2] . '" /><input type="hidden" name="f-folder" class="f-folder" value="' . $f_data[5] . '" /><input type="hidden" name="f-title" class="f-title" value="' . $f_data[9] . '" /><input type="hidden" name="f-description" class="f-description" value="' . $f_data[10] . '" /><input type="hidden" name="f-size" class="f-size" value="' . get_file_size($f_data[4], 2, false) . '" />';
      	$files_result .= ($f_data[5] == 'images') ? '<input type="hidden" name="f-thumb" class="f-thumb" value="' . $conf['admin_url'] . '/' . $dir['thumbs'] . '_' . $f_data[1] . '.' . $f_data[2] . '" /><input type="hidden" name="f-imgsize" class="f-imgsize" value="' . $f_data[7] . ' &times; ' . $f_data[8] . '" />' : '';
      	$files_result .= '</form></div>';
      
      }
      
      else {
	      $files_result .= '<tr' . $tr_class . '>
	        <td><!-- ' . $f_data[3] . ' --><input type="checkbox" name="files[]" value="' . $f_data[3] . '" class="checkbox" /></td>
	        <td class="cell-icon"><!-- ' . $f_data[2] . ' -->' . $file_image . '</td>
	        <td><!-- ' . $f_data[1] . ' -->
	          <a href="' . MN_ROOT . $dir[$f_data[5]] . $f_data[1] . '.' . $f_data[2] . '"' . $file_class . '>' . $f_data[1] . '<span class="ext">.' . $f_data[2] . '</span></a><br />
	          &nbsp;<span class="links hide">
	            <a href="./mn-files.php?action=edit&amp;id=' . $f_data[3] . '">' . $lang['uni_edit'] . '</a> |
	            <a href="./mn-files.php?action=delete&amp;id=' . $f_data[3] . '" class="fancy">' . $lang['uni_delete'] . '</a>
	            <!-- | <input type="text" class="file-link" value="' . $conf['admin_url'] . '/' . $dir[$f_data[5]] . $f_data[1] . '.' . $f_data[2] . '" />-->
	          </span>
	        </td>
	        <td><!-- ' . $f_data[4] . ' -->' . get_file_size($f_data[4], 2, false) . $img_size . '</td>
	        <td>' . $lang['files_dir_' . $f_data[5]] . '</td>
	        <td>' . $uploaders[$f_data[6]] . '</td>
	        <td>' . date('d.m.Y', $f_data[0]) . '<br /><span class="trivial">' . date('H:i', $f_data[0]) . '</span></td>
	      </tr>';
	   }
	   
	   
    }

    
    if (isset($_GET['ajaxcall'])) {
    	$admin_tmpl['mce_gallery_list'] = true;
    }
    else {
    	$admin_tmpl['files_list'] = true;
    }

  }
  






	if (isset($admin_tmpl['mce_gallery_list']) && $admin_tmpl['mce_gallery_list']) {




		if (is_writeable($dir['images']) && is_writeable($dir['thumbs'])) {
		
			$file_exts = '';
			foreach ($ext_img as $key => $value) {
			  $file_exts .= '*.' . $key . '; ';
			}
			$file_exts = substr($file_exts, 0, -2);
			
			
			if (!isset($conf['admin_multiupload']) || $conf['admin_multiupload']) {
?>	
		
	<script type="text/javascript" src="./stuff/etc/jquery-uploadify-min.js"></script>
	<script type="text/javascript">
	 	$(document).ready( function() {
		 	$("#multiupload").uploadify({
				'swf'             : './stuff/etc/uploadify.swf',
				'uploader'        : './mn-files.php?mce=true',
		 		'buttonText'      : '<img src="./stuff/img/icons/folder-browse.png" alt="" width="16" height="16" />&nbsp;&nbsp;<?php echo $lang['files_upload_files'];?>',
		 		'fileObjName'     : 'file',
	 			'height'          : 35,
		 		'width'           : 160,
		 		'removeCompleted' : true,
		 		'removeTimeout'   : 0,
		 		'auto'            : false,
		 		'queueID'         : 'upload-queue',
		 		'fileSizeLimit'   : '<?php echo $max_upload_size;?>MB',
		 		'fileTypeExts'    : '<?php echo $file_exts;?>',
		 		'onSelect'   : function() {
		 			$('#upload-btn').show();
		 			$('#upload-info').show().html('<?php echo $lang['files_multiupload_start_info'];?>');
		 		},
		 		'onUploadStart' : function() {
		 			$('#upload-info').html('<?php echo $lang['files_multiupload_upload_progress'];?><br /><img src="./stuff/img/loading-animation.gif" alt="" width="208" height="13">');
		 		},
		 		'onUploadSuccess' : function(file, data, response) {
	 		 		$('#gallery-filter').after(data);
	 		 	},
		 		'onQueueComplete' : function() {
		 			$('#upload-btn').hide();
		 			$('#upload-info').html('<img src="./stuff/img/icons/ok.png" alt="" width="16" height="16"> <span class="complete"><?php echo $lang['files_multiupload_upload_complete'];?></span>');
		 			$('a.fancy').fancybox({'closeClick': false, 'autoSize' : false, 'width': 600, 'height': 450, 'type': 'ajax', 'afterShow': function(){$('#c_text').focus();}});
		 			$('a.fancyimg').fancybox({'closeClick': false, 'type': 'image'});
		 			$('tr#no_files').remove();
		 		},
				'formData'        : {
					'action' : 'multiupload',
					'<?php echo session_name();?>' : '<?php echo session_id();?>'
				}
	 		});

	 	});
	</script>
		 	
		 	
			<form action="./mn-files.php" method="post" enctype="multipart/form-data" id="uploadify-form">
		
		 		<input id="multiupload" type="file" name="file" />
		
		 		<p id="upload-info" class="c hide"></p> 		
		 		<div id="upload-queue" class="mce"></div>
		 	
		
		 		<div class="simbutton c hide" id="upload-btn"><a href="javascript:$('#multiupload').uploadify('upload', '*')" style="margin: 0 auto;"><img src="./stuff/img/icons/folder-go.png" alt="" width="16" height="16" /> <?php echo $lang['files_upload_files'];?></a></div>
			</form>
		
		<?php 
				}
		
		    }
		    else echo '<p class="c msg-error">' . $lang['files_msg_wrong_permissions'] . '</p>';
?>


	<div class="i-main l cb"><?php echo $lang['posts_insert_image_file'];?></div>
	<div id="gallery">
	
		<div id="gallery-filter">
			<?php echo $lang['uni_show'];?>:
				<select name="filter-type" id="filter-type">
					<option value="0">--- <?php echo $lang['files_all_types'];?> ---</option>
					<option value="images"><?php echo $lang['files_dir_images'];?></option>
					<option value="media"><?php echo $lang['files_dir_media'];?></option>
					<option value="others"><?php echo $lang['files_dir_others'];?></option>
				</select>
				
				<?php 
					if (!empty($folders)) {
						echo '<select name="filter-folder" id="filter-folder"><option value="0">--- ' . $lang['files_all_folders'] . ' ---</option>';
						
						show_folders();
		
						echo '</select>';
					} 
				?>
		</div>

		<?php echo $files_result;?>

	</div>
	
	<div id="mn-popup" class="hide round">
		
		<span class="pop-close round tooltip" title="<?php echo $lang['uni_close'];?>">x</span>
		
		<div class="i-main">
			<span class="image"><?php echo $lang['files_insert_image'];?></span>
			<span class="other"><?php echo $lang['files_insert_file'];?></span>
		</div>
		
		<form class="cb">
		
			<div class="l fl" id="preview"></div>
		
			<div class="r fr">
				<span class="image">
					<label><?php echo $lang['files_insert_as'];?>:
						<select name="insert_as" id="insert_as" class="long">
							<option value="1"><?php echo $lang['files_insert_as1'];?></option>
							<option value="2"><?php echo $lang['files_insert_as2'];?></option>
							<option value="3"><?php echo $lang['files_insert_as3'];?></option>
						</select></label><br />
				</span>
				<span class="other" id="link_text"><label><?php echo $lang['files_file_url_text'];?>: <input type="text" name="file_url_text" id="file_url_text" class="text" value="" /></label><br /></span>
				<span class="image"><label><?php echo $lang['files_file_alt'];?>: <input type="text" name="file_alt" id="file_alt" class="text" value="" /></label><br /></span>
				
				<label><?php echo $lang['files_file_title'];?>: <input type="text" name="file_title" id="file_title" class="text" value="" /></label><br />
				<label><?php echo $lang['files_file_class'];?>: <input type="text" name="file_class" id="file_class" class="text" value="" /></label><br />
				
				<span class="image">
					<label><?php echo $lang['files_image_align'];?>: <select name="file_img_align" id="file_img_align" class="short"><option value=""></option><option value="left"><?php echo $lang['uni_left'];?></option><option value="right"><?php echo $lang['uni_right'];?></option></select></label>
					<label>rel=<input type="text" name="file_rel" id="file_rel" class="text short" value="lightbox" /></label><br />
				</span>
				
				<span class="other"><br /><label><input type="checkbox" name="file_target" id="file_target" value="1" /> <?php echo $lang['files_file_target_blank'];?></label></span>

				<input type="hidden" name="file_url" id="file_url" value="" />
				<input type="hidden" name="file_thumb" id="file_thumb" value="" />
				<input type="hidden" name="file_folder" id="file_folder" value="" />
				
			</div>
			
			<p class="c cb"><button id="mce-insert"><img src="./stuff/img/icons/tick.png" alt="-" width="16" height="16" /> <?php echo $lang['uni_insert'];?></button></p>

		</table>
		</form>
	</div>



	<script type="text/javascript">
	
		$(document).ready(function() {
			
			$('#gallery-filter select').change(function () {
			
				var TypeValue = $('#gallery-filter select#filter-type option:selected').val();
				var FolderValue = ($('#gallery-filter select#filter-folder').length > 0) ? $('#gallery-filter select#filter-folder option:selected').val() : '0';
				var TypeClass = (TypeValue != '0') ? '.type-' + TypeValue : '';
				var FolderClass = (FolderValue != '0') ? '.folder-' + FolderValue : '';
				
				$('.gal-item').hide();
				$('.gal-item' + TypeClass + FolderClass).show();
			
			});
		



			$('#mn-popup form').submit(function() {
			
				var fileFolder = $('#mn-popup #file_folder').val();
				var fileURL = $('#mn-popup #file_url').val();
				var fileTit = $('#mn-popup #file_title').val();
				var fileClas = $('#mn-popup #file_class').val();
				var fileURLtext = $('#mn-popup #file_url_text').val();
				
				var fileTitle = (fileTit == '') ? '' : ' title="' + fileTit + '"';
				var fileClass = (fileClas == '') ? '' : ' class="' + fileClas + '"';
				
				
				if (fileFolder == 'images') {
					
					var fileThumb = $('#mn-popup #file_thumb').val();
					var fileAlt = $('#mn-popup #file_alt').val();
					var insertAs = $('#mn-popup #insert_as option:selected').val();
					
					if (insertAs == '2') {
						var mceContent = '<img src="' + fileURL + '" alt="' + fileAlt + '"' + fileTitle + ' />';
					}
					else if (insertAs == '3') {
						var mceContent = '<a href="' + fileURL + '"' + fileClass + ' rel="' + $('#mn-popup #file_rel').val() + '"' + fileTitle + '>' + fileURLtext + '</a>';
					}
					else {
						var mceContent = '<a href="' + fileURL + '"' + fileClass + ' rel="' + $('#mn-popup #file_rel').val() + '"><img src="' + fileThumb + '" alt="' + fileAlt + '"' + fileTitle + ' /></a>';
					}
					
				}
				
				else  {
					var fileTarget = ($('#mn-popup #file_target').is(':checked')) ? ' target="_blank"' : '';
					var mceContent = '<a href="' + fileURL + '"' + fileTitle + fileTarget + '>' + fileURLtext + '</a>';
				}
	
				<?php
					if (isset($_GET['mce_area'])) {
						echo "$('#" . $_GET['mce_area'] . "').insertAtCaret(mceContent);";
					}
					else {
						echo "tinyMCE.activeEditor.execCommand('mceInsertContent', false, mceContent);";
					}
				?>
			
				$('#mn-popup input:not(#file_rel)').val('');
				$('#mn-popup select').val('');
				$('#mn-popup').hide();
				
				$.fancybox.close(true);
			
				return false;
			});
			
			
		
		});
	</script>



<?php
	die();
	}




  if (isset($admin_tmpl['files_list']) && $admin_tmpl['files_list']) {
  
  
    if (is_writeable($dir['images']) && is_writeable($dir['thumbs'])) {

	    $file_exts = '';
	  	foreach ($ext_img as $key => $value) {
	  	  $file_exts .= '*.' . $key . '; ';
	  	}
	  	$file_exts = substr($file_exts, 0, -2);
	  	
	  	
	  	if (!isset($conf['admin_multiupload']) || $conf['admin_multiupload']) {
?>	

    <script type="text/javascript" src="./stuff/etc/jquery-uploadify-min.js"></script>
 	<script type="text/javascript">
 	 	$(document).ready( function() {
 		 	$("#multiupload").uploadify({
				'swf'             : './stuff/etc/uploadify.swf',
				'uploader'        : './mn-files.php',
 		 		'buttonText'      : '<img src="./stuff/img/icons/folder-browse.png" alt="" width="16" height="16" />&nbsp;&nbsp;<?php echo $lang['files_select_files'];?>',
 		 		'fileObjName'     : 'file',
 	 			'height'          : 35,
 		 		'width'           : 160,
 		 		'removeCompleted' : true,
 		 		'removeTimeout'   : 0,
 		 		'auto'            : false,
 		 		'queueID'         : 'upload-queue',
 		 		'fileSizeLimit'   : '<?php echo $max_upload_size;?>MB',
 		 		'fileTypeExts'    : '<?php echo $file_exts;?>',
 		 		'onSelect'   : function() {
 		 			$('#upload-btn').show();
 		 			$('#upload-info').show();
 		 			$('#upload-info .progress').hide();
 		 			$('#upload-info .start').show();
 		 		},
 		 		'onUploadStart' : function() {
 		 			$('#upload-info .progress').hide();
 		 			$('#upload-info .uploading').show();
 		 		},
 		 		'onUploadSuccess' : function(file, data, response) {
	 		 		$('#files-list tbody tr:first').before(data);
	 		 	},
 		 		'onQueueComplete' : function() {
 		 			$('#upload-btn').hide();
 		 			$('#upload-info .progress').hide();
 		 			$('#upload-info .end').show();
 		 			$('a.fancy').fancybox({'closeClick': false, 'autoSize' : false, 'width': 600, 'height': 450, 'type': 'ajax', 'afterShow': function(){$('#c_text').focus();}});
 		 			$('a.fancyimg').fancybox({'closeClick': false, 'type': 'image'});
 		 			$('tr#no_files').remove();
 		 		},
 				'formData'        : {
 					'action' : 'multiupload',
 					'<?php echo session_name();?>' : '<?php echo session_id();?>'
 				}
 	 		});
 	 	});
 	</script>
 	
 	
	<form action="./mn-files.php" method="post" enctype="multipart/form-data" id="uploadify-form">

 		<input id="multiupload" type="file" name="file" />

 		<div id="upload-info" class="c hide">
 			<div class="progress start">
 				<p><img src="./stuff/img/icons/information.png" alt="" width="16" height="16"> <?php echo $lang['files_multiupload_start_info'];?></p>
 				
 					<?php
 						/*
 						 * --- Abandoned concept, too much graphic noise during upload process
 						 * --- Use bulk actions after upload
 						 *
 						echo '<p class="l">' . $lang['files_uploaded_files'] . ':';
 						
 						if (!empty($folders)) {      
 						    echo '<select name="f">';
 						    echo '<option value="" class="description">--- ' . $lang['files_buld_add_to_folder'] . ' ---</option>';          
 						    show_folders();                             
 						    echo '</select> ';
 						}
 						
 						
 						
 						if (file_exists(MN_ROOT . $file['galleries'])) {
 						  $galleries = load_basic_data('galleries');
 						
 						  if(!empty($galleries)) {
 						    echo '<select name="g">';
 						    echo '<option value="" class="description">--- ' . $lang['files_buld_add_to_gallery'] . ' ---</option>';
 						
 						    foreach ($galleries as $gal_id => $gal_name) {
 						      $sel = (isset($_GET['g']) && $_GET['g'] == $gal_id) ? ' selected="selected"' : '';
 						      echo '<option value="' . $gal_id . '"' . $sel . '>' . $gal_name . '</option>';
 						    }
 						    echo '</select>';
 						  }
 						}
 						
 						echo '</p>';
 						*/   
 					?>

 			</div>
 			<p class="progress uploading"><?php echo $lang['files_multiupload_upload_progress'];?><br /><img src="./stuff/img/loading-animation.gif" alt="" width="208" height="13"></p>
 			<p class="progress end"><img src="./stuff/img/icons/ok.png" alt="" width="16" height="16"> <span class="complete"><?php echo $lang['files_multiupload_upload_complete'];?></span></p>
 		</div> 		
 		<div id="upload-queue"></div>
 	

 		<div class="simbutton c hide" id="upload-btn"><a href="javascript:$('#multiupload').uploadify('upload', '*')" style="margin: 0 auto;"><img src="./stuff/img/icons/folder-go.png" alt="" width="16" height="16" /> <?php echo $lang['files_upload_files'];?></a></div>
	</form>

<?php 
		} else {
?>

	<form action="./mn-files.php" method="post" enctype="multipart/form-data" id="quick-upload">
	  <input type="file" class="file" name="file" id="file" />
	  <input type="hidden" name="action" value="quick-upload" />
	  <input type="submit" class="submit" value="<?php echo $lang['uni_upload'];?>" />
	  <p><?php echo $lang['files_max_file_size'] . ' <b>' . $max_upload_size . 'MB</b>.';?></p>
	</form>

<?php 
		}

    }
    else echo '<p class="c msg-error">' . $lang['files_msg_wrong_permissions'] . '</p>';
?>


  
  <div class="rel-links">
    <a href="./mn-folders.php"><img src="./stuff/img/icons/folders.png" alt="" /> <?php echo $lang['folders_folders'];?></a> |
    <a href="./mn-galleries.php"><img src="./stuff/img/icons/images.png" alt="" /> <?php echo $lang['galleries_galleries'];?></a> |
    <?php echo (empty($_GET['u']) && empty($_GET['e']) && empty($_GET['t']) && empty($_GET['f']) && empty($_GET['g']) && empty($_GET['d'])) ? '<span class="simurl" id="filter-viewer"> <img src="./stuff/img/icons/view-settings.png" alt="" width="16" height="16" /> ' . $lang['pages_filter_settings'] . '</span>' : '<a href="./mn-files.php" class="custom"><img src="./stuff/img/icons/view-settings-cancel.png" alt="" width="16" height="16" /> ' . $lang['pages_filter_cancel'] . '</a>';?>
  </div>

  <?php $class = (empty($_GET['u']) && empty($_GET['e']) && empty($_GET['t']) && empty($_GET['f']) && empty($_GET['g']) && empty($_GET['d'])) ? ' hide' : '';?>
  <p class="cleaner">&nbsp;</p>


  <form action="./mn-files.php" method="get" class="filter<?php echo $class;?>">
    <select name="u">
      <option value="" class="description">--- <?php echo $lang['files_all_users'];?> ---</option>
      <?php
        $users = load_basic_data('users');
        if (!empty($uploaders)) {
          foreach ($uploaders as $user_id => $user_name) {
            $sel = (isset($_GET['u']) && $user_id == $_GET['u']) ? ' selected="selected" class="selected"' : '';
            echo '<option value="' . $user_id . '"' . $sel . '>' . $user_name . '</option>';
          }
        }
      ?>
    </select>
    
    <select name="e">
      <option value="" class="description">--- <?php echo $lang['files_all_extentions'];?> ---</option>
      <optgroup label="<?php echo $lang['files_dir_images'];?>">
        <?php
          foreach ($ext['images'] as $extention) {
            $sel = (isset($_GET['e']) && $_GET['e'] == $extention) ? ' selected="selected"' : '';
            echo '<option value="' . $extention . '"' . $sel . '>' . $extention . '</option>';
          }
        ?>
      </optgroup>
      <optgroup label="<?php echo $lang['files_dir_media'];?>">
        <?php
          foreach ($ext['media'] as $extention) {
            $sel = (isset($_GET['e']) && $_GET['e'] == $extention) ? ' selected="selected"' : '';
            echo '<option value="' . $extention . '"' . $sel . '>' . $extention . '</option>';
          }
        ?>
      </optgroup>
      <optgroup label="<?php echo $lang['files_dir_others'];?>">
        <?php
          foreach ($ext['others'] as $extention) {
            $sel = (isset($_GET['e']) && $_GET['e'] == $extention) ? ' selected="selected"' : '';
            echo '<option value="' . $extention . '"' . $sel . '>' . $extention . '</option>';
          }
        ?>
      </optgroup>
    </select>

    <select name="t">
      <option value="" class="description">--- <?php echo $lang['files_all_types'];?> ---</option>
      <option value="images"<?php if (isset($_GET['t']) && $_GET['t'] == 'images') echo ' selected="selected"';?>><?php echo $lang['files_dir_images'];?></option>
      <option value="media"<?php if (isset($_GET['t']) && $_GET['t'] == 'media') echo ' selected="selected"';?>><?php echo $lang['files_dir_media'];?></option>
      <option value="others"<?php if (isset($_GET['t']) && $_GET['t'] == 'others') echo ' selected="selected"';?>><?php echo $lang['files_dir_others'];?></option>
    </select>
    
    <?php
      if (!empty($folders)) {      
          echo '<select name="f">';
          echo '<option value="" class="description">--- ' . $lang['files_all_folders'] . ' ---</option>';          
          show_folders(0, 0, @$_GET['f']);                             
          echo '</select> ';
      }
      
      
      
      if (file_exists(MN_ROOT . $file['galleries'])) {
        $galleries = load_basic_data('galleries');

        if(!empty($galleries)) {
          echo '<select name="g">';
          echo '<option value="" class="description">--- ' . $lang['files_all_galleries'] . ' ---</option>';

          foreach ($galleries as $gal_id => $gal_name) {
            $sel = (isset($_GET['g']) && $_GET['g'] == $gal_id) ? ' selected="selected"' : '';
            echo '<option value="' . $gal_id . '"' . $sel . '>' . $gal_name . '</option>';
          }
          echo '</select>';
        }
      }
    ?>


	<?php if (!file_exists(MN_ROOT . $file['galleries']) || empty($folders)) { ?>
    <select name="d">
    <option value="" class="description">--- <?php echo $lang['files_all_dates'];?> ---</option>
    <?php
      foreach ($timestamps as $key => $value) {
        $sel = (isset($_GET['d']) && $value == $_GET['d']) ? ' selected="selected" class="selected"' : '';
        echo '<option value="' . $value . '"' . $sel . '>' . $lang['month'][date('n', $key)] . ' ' . date('Y', $key) . '</option>';
      }
    ?>
    </select>
    <?php } ?>

    <input type="submit" class="submit" value="<?php echo $lang['pages_filter'];?>" />

  </form>

  <form action="./mn-files.php" method="post" id="files-list-form">
    <table id="files-list" class="tablesorter">
    <thead><tr><th id="cell-checkbox" class="num" title="<?php echo $lang['files_sort_by_id'];?>" class="tooltip">&nbsp;</th><th id="cell-icon" title="<?php echo $lang['files_sort_by_ext'];?>" class="tooltip">&nbsp;</th><th id="cell-name"><?php echo $lang['files_filename'];?></th><th id="cell-size" class="num"><?php echo $lang['files_size'];?></th><th id="cell-dir"><?php echo $lang['files_type'];?></th><th id="cell-uploader"><?php echo $lang['files_uploader'];?></th><th class="date"><?php echo $lang['files_date'];?></th></tr></thead>
    <tbody>
    	<?php
    		if (empty($files_result)) {
    			echo '<tr id="no_files"><td colspan="7" class="c"><p><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['files_msg_no_files'] . '</p></td></tr>';
    		}
    		else {
	    		echo $files_result;
    		}
    	?>
    </tbody>
    </table>

    <div class="bulk-actions">
      <select name="a">
        <option>--- <?php echo $lang['uni_bulk_actions'];?> ---</option>
        
        <option value="doedit"><?php echo $lang['files_bulk_edit'];?></option>
        <option value="delete"><?php echo $lang['files_bulk_delete'];?></option>

        <?php
          if (!empty($folders)) {

          	echo '<optgroup label="' . $lang['files_buld_add_to_folder'] . ': ">';
          	show_folders();
          	echo '</optgroup>';
          
          }


          if (file_exists(MN_ROOT . $file['galleries'])) {
            $galleries = load_basic_data('galleries');
            if(!empty($galleries)) {
              echo '<optgroup label="' . $lang['files_buld_add_to_gallery'] . ': ">';
              foreach ($galleries as $gal_id => $gal_name) {
                echo '<option value="g' . $gal_id . '">' . $gal_name . '</option>';
              }
              echo '</optgroup>';
            }
          }
          
        ?>
      </select>
      <input type="hidden" name="action" value="bulk" />
      <input type="submit" class="submit" value="<?php echo $lang['uni_send'];?>" />
    </div>
  
  </form>

  <div id="pager" class="custom-pager pager<?php if (isset($files) && count($files) <= 10) echo ' hide'; ?>">
    <form action="./mn-users.php">
      <select class="pagesize fr"><option selected="selected" value="10">10</option><option value="20">20</option><option value="30">30</option><option value="<?php echo count($files);?>"><?php echo $lang['posts_all_posts'];?></option></select>
      <img src="./stuff/img/icons/control-first.png" class="first" alt="&laquo;" title="<?php echo $lang['posts_page_first'];?>" /> <img src="./stuff/img/icons/control-prev.png" class="prev" alt="&lt;" title="<?php echo $lang['posts_page_prev'];?>" />
      <input type="text" class="pagedisplay" />
      <img src="./stuff/img/icons/control-next.png" class="next" alt="&gt;" title="<?php echo $lang['posts_page_next'];?>" /> <img src="./stuff/img/icons/control-last.png" class="last" alt="&raquo;" title="<?php echo $lang['posts_page_last'];?>" />
    </form>
  </div>

  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-pager.js"></script>
  <script type="text/javascript">
  	$(function() {
  		$("table")
  			.tablesorter({widthFixed: false})
  			.tablesorterPager({container: $("#pager")});
  		$('#files-list-form').submit(function() {
  			if ($('#files-list-form select option:selected').val() == 'delete') {
		  		return confirm('<?php echo $lang['files_bulk_delete_confirm'];?>');
		  	}
		  	else {
			  	return true;
		  	}
	  	});
  	});
  </script>

<?php


  }

  if (isset($admin_tmpl['edit_img']) && $admin_tmpl['edit_img']) {
  
  	$rowspan = (!empty($folders)) ? 4 : 3;
  
?>

  <form action="./mn-files.php" method="post" id="image-edit" class="c">

    <table id="files-edit">
      <tr>
        <td rowspan="<?php echo $rowspan;?>" class="thumb"><?php echo (isset($thumb_file) && file_exists($thumb_file)) ? '<a href="./' . $dir['images'] . $filename . '" class="fancyimg"><img src="' . $thumb_file . '" alt="' . $filename . '" /></a>' : '&nbsp;';?></td>
        <td class="labels"><label for="filename" class="required"><?php echo $lang['files_filename'];?>:</label></td>
        <td class="inputs"><input type="text" class="text long" name="filename" id="filename" value="<?php echo $var['filename'];?>" /><span class="trivial">.<?php echo $var['ext'];?></span></td>
      </tr>
      <?php 
      	if (!empty($folders)) {
      		
      		echo '<tr><td class="labels"><label for="folder">' . $lang['folders_folder'] . ':</label></td><td class="inputs"><select name="folder" id="folder" class="long"><option value=""></option>';
      		show_folders(0, 0, $var['folder']); 
      		echo '</select></td></tr>';
      	
      	}
      ?>
      <tr>
        <td class="labels"><label for="title"><?php echo $lang['files_title'];?>:</label></td>
        <td class="inputs"><input type="text" class="text long" name="title" id="title" value="<?php echo $var['title'];?>" /></td>
      </tr>
      <tr>
        <td class="labels vatt"><label for="description"><?php echo $lang['files_description'];?>:</label></td>
        <td class="inputs"><textarea name="description" id="description" cols="40" rows="5"><?php echo $var['description'];?></textarea></td>
      </tr>
    </table>
    <?php
      if ($var['dir'] == 'images' && file_exists(MN_ROOT . $file['galleries'])) {
        $galleries = load_basic_data('galleries');
        $var['galleries_array'] = explode(',', $var['galleries']);

        if(!empty($galleries)) {
          echo '<div id="galleries" class="round">';
          echo '<strong>' . $lang['galleries_galleries'] . '</strong>';
          echo '<ul class="galleries">';
          
          foreach ($galleries as $gal_id => $gal_name) {
            $checked = (!empty($var['galleries']) && in_array($gal_id, $var['galleries_array'])) ? ' checked="checked"' : '';
            echo '<li><input type="checkbox" name="galleries[]" id="gal_' . $gal_id . '" value="' . $gal_id . '"' . $checked . ' /> <label for="gal_' . $gal_id . '">' . $gal_name . '</label></li>';
          }
          echo '</ul></div>';
        }
      }
    ?>
    </p>
      <input type="hidden" name="file_id" value="<?php echo $var['file_id'];?>" />
      <input type="hidden" name="action" value="edit" />
    </p>
    <p><button type="submit"><img src="./stuff/img/icons/image-edit.png" alt="" width="16" height="16" /> <?php echo $lang['uni_edit'];?></button></p>
  </form>

<?php
  }

  if (isset($admin_tmpl['bulk_edit']) && $admin_tmpl['bulk_edit']) {
  ?>
  
    <form action="./mn-files.php" method="post" id="bulk-edit" class="c">
    
    	<?php echo $files_result; ?>
    	<p class="cleaner">&nbsp;</p>
    	
    	</p>
    	  <input type="hidden" name="action" value="bulk" />
    	  <input type="hidden" name="a" value="edit" />
    	</p>
    	<p class="c"><button type="submit"><img src="./stuff/img/icons/image-edit.png" alt="" width="16" height="16" /> <?php echo $lang['uni_edit'];?></button></p>
    	
    
    </form>

<?php 
  }
  
  if (isset($admin_tmpl['delete_img']) && $admin_tmpl['delete_img']) {
?>

  <form action="./mn-files.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['files_q_really_delete'];?>: <strong><?php echo $filename;?></strong>?<br />
      <p><?php
        if (isset($thumb_file) && file_exists($thumb_file)) {
          echo '<img src="' . $thumb_file . '" alt="' . $filename . '" class="thumb" />';
        }
        else echo '<em><img src="./stuff/img/icons/file-' . $ext_img[strtolower($var['ext'])] . '.png" alt="" title="' . $var['ext'] . '" width="16" height="16" /> ' . $filename . '</em>';
      ?></p>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="file_id" value="<?php echo $var['file_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['files_delete_file'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  
  overall_footer();
?>