<?php

  include_once './stuff/inc/mn-start.php';

  $auth = user_auth('1');
  $admin_tmpl['form_add_posts'] = true;
  $admin_tmpl['form_posts_list'] = false;
  $admin_tmpl['form_post_delete'] = false;

  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') ? 'add' : 'edit';
  if (!isset($_COOKIE["TinyMCE_full_story_size"])) {setcookie("TinyMCE_full_story_size", "cw=710&ch=350", time()+60*60*24*365);}
  
  if (isset($_GET['i-ok'])) {$info['text'] = $lang['posts_msg_' . str_replace('-', '_', $_GET['i-ok'])]; $info['style'] = 'ok';}
  elseif (isset($_GET['i-error'])) {$info['text'] = $lang['posts_msg_' . str_replace('-', '_', $_GET['i-error'])]; $info['style'] = 'error';}
  elseif (isset($_GET['i-info'])) {$info['text'] = $lang['posts_msg_' . str_replace('-', '_', $_GET['i-info'])]; $info['style'] = 'info';}







  # before adding news we need form to display
  if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $var = assign_post_vars('', $_SESSION['mn_user_id'], '', '', '', '', date('d'), date('m'), date('Y'), date('H'), date('i'), '1', '1', 'add', '', '0', array());
    overall_header($lang['posts_add_new_post'], $lang['posts_add_new_post'], 'main', true);
  }



  # let's add some news :)
  elseif (isset($_POST['action']) && $_POST['action'] == 'add') {

    # check if title and short story is not empty
    if (!empty($_POST['title']) && !empty($_POST['short_story'])) {

      # find out timestamp and status of new article
      $timestamp = ($_POST['date'] == 'specific') ? mktime($_POST["date_hour"], $_POST["date_min"], 0, $_POST["date_month"], $_POST["date_day"], $_POST["date_year"]) : mn_time();
      $status = ($auth == '3' && $_POST['status'] == '1') ? 4 : $_POST['status'];
      if ($status == '3' && $_POST['date'] != 'specific') $timestamp = '9999999999';

      $post_tags = (!empty($_POST['tags'])) ? implode(',', $_POST['tags']) : '';

      # we need to find out new ID, which will be assign to this article (an = active news)
      $id = trim(file_get_contents($file['id_posts']));
      
      # create friendly url
      $post_slugs = get_post_slugs();
      if (in_array(friendly_url($_POST['title']), $post_slugs)) {
        $i = 2;
        $post_friendly_url = friendly_url($_POST['title']);
        while (in_array($post_friendly_url, $post_slugs) && $i < 100) {
          $post_friendly_url = friendly_url($_POST['title']) . '-' . $i;
          $i++;
        }
      }
      else {
        $post_friendly_url = friendly_url($_POST['title']);
      }
      
      # short story & full story
      $short_story = str_replace($conf['admin_url'], '{%MN_URL%}', $_POST['short_story']);
      $full_story = str_replace($conf['admin_url'], '{%MN_URL%}', $_POST['full_story']);


      if (isset($conf['posts_image']) && $conf['posts_image'] && isset($_FILES['image']['name']) && is_image($_FILES['image']['name'])) {

        $source_file = pathinfo_utf($_FILES['image']['name']);
        $clean_file_ext = strtolower($source_file['extension']);
        $clean_file = 'mn_post_' . $id . '.' . $clean_file_ext;
        $target_file = './' . $dir['images'] . $clean_file;

        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        if (isset($conf['posts_image_size']) && is_numeric($conf['posts_image_size']) && $conf['posts_image_size'] > 0) {
          resize_img($target_file, $conf['posts_image_size'], $target_file);
        }
    	  list($img_width, $img_height) = getimagesize($target_file);

        $post_image = $clean_file . ';' . $img_width . ';' . $img_height . ';' . filesize($target_file) . ';';

      }

      else {
        $post_image = '';
      }
      


      if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
      
      	$xfields = get_unserialized_array('xfields');
      	$post_xfields = array();
      	foreach ($xfields as $xVar => $x) {
      		if ($x['section'] != 'posts') continue;
      		else {
      			$post_xfields[$xVar] = check_text($_POST['x' . $xVar], true);
      		}
      	}
      	
      	$xfields_serialized = serialize($post_xfields);
      
      }
      else $xfields_serialized = '';

      
      # let's make content of main news file
      $p_content = SAFETY_LINE . DELIMITER . "\n" . $id . DELIMITER . $timestamp . DELIMITER . check_text($_POST['title']) . DELIMITER . $post_friendly_url . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $_POST['cat'] . DELIMITER . $status . DELIMITER . $_POST['comments'] . DELIMITER . '0' . DELIMITER . $post_tags . DELIMITER . $post_image . DELIMITER . '' . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . "\n" . check_text($short_story) . DELIMITER . "\n" . check_text($full_story);
      
      $posts_content = (file_exists(MN_ROOT . $file['posts'])) ? file_get_contents($file['posts']) : SAFETY_LINE . "\n";
      $posts_content .= $timestamp . DELIMITER . $id . DELIMITER . check_text($_POST['title']) . DELIMITER . $post_friendly_url . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $_POST['cat'] . DELIMITER . $status . DELIMITER . $post_tags . "\n";

      # write contents into files
      mn_put_contents($file['id_posts'], $id + 1);
      mn_put_contents($file['posts'], $posts_content);
      mn_put_contents($dir['posts'] . 'post_' . $id . '.php', $p_content);

      # display message about (un)successful addition
      if (file_exists($dir['posts'] . 'post_' . $id . '.php') && (file_get_contents($file['id_posts']) == ($id + 1)) && (file_get_contents($file['posts']) == $posts_content)) {
        if ($status == '2') {
          header('location: ./mn-posts.php?i-ok=added-as-private');
          exit;
        }
        elseif ($status == '3') {
          header('location: ./mn-posts.php?i-ok=added-as-draft');
          exit;
        }
        elseif ($status == '4') {
          header('location: ./mn-posts.php?i-ok=added-as-unapproved');
          exit;
        }
        else {
          header('location: ./mn-posts.php?i-ok=added');
          exit;
        }
      }
      else {
        header('location: ./mn-posts.php?action=add&i-error=error');
        exit;
      }
    }

    # if title, or news body is empty, MNews will show error message
    else {
      $var = assign_post_vars($_POST['title'], $_SESSION['mn_user_id'], $_POST['cat'], $_POST['short_story'], $_POST['full_story'], $_POST['date'], date('d'), date('m'), date('Y'), date('H'), date('i'), $_POST['status'], $_POST['comments'], 'add', '', '0');
      $var['tags_array'] = (!empty($_POST['tags'])) ? $_POST['tags'] : array();
      overall_header($lang['posts_add_new_post'], $lang['posts_msg_empty_values'], 'error', true);
    }

  }






  # let's now edit news, first we need  :)
  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    if (file_exists($dir['posts'] . 'post_' . $_GET['id'] . '.php')) {
      $var = get_post_data($_GET['id']);
      $var['tags_array'] = explode(',', $var['tags']);
      $var['xfields_array'] = unserialize($var['xfields']);
      if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
        header('location: ./?access-denied');
        exit;
      }
      else {
        if (isset($info['text']) && !empty($info['text'])) overall_header($lang['posts_posts'] . ' &raquo; ' . $var['title'], $info['text'], $info['style'], true);
        else overall_header($lang['posts_edit_post'] . ' &raquo; ' . $var['title'], $lang['posts_edit_post'], 'main', true);
      }
    }
  }



  # edit post
  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id'])) {

    # check if title and short story is not empty
    if (!empty($_POST['title']) && !empty($_POST['short_story'])) {
      $var = get_post_data($_POST['id']);
      if (($auth != 1 && ($var['author'] != $_SESSION['mn_user_id'] || $_POST['author'] != $_SESSION['mn_user_id'] || isset($_POST['approve']))) || (!file_exists($dir['posts'] . 'post_' . $_POST['id'] . '.php'))) {
        header('location: ./?access-denied');
        exit;
      }
      elseif ($_POST['date'] == 'specific') {$timestamp = mktime($_POST["date_hour"], $_POST["date_min"], 0, $_POST["date_month"], $_POST["date_day"], $_POST["date_year"]);}
      elseif ($var['timestamp'] == '9999999999') {$timestamp = mn_time();}
      else {$timestamp = $var['timestamp'];}
      
      if ($auth == 1 && (isset($_POST['approve']) || isset($_POST['publish']))) $status = 1;
      elseif ($auth == 1 && isset($_POST['reject'])) $status = 5;
      elseif ($auth > 1 && $var['status'] > 3 && $_POST['status'] < 4) $status = $var['status'];
      else $status = $_POST['status'];
      
      if ($status == '3' && $_POST['date'] != 'specific' && $var['timestamp'] == '9999999999') $timestamp = '9999999999';
      
      $author = ($auth == 1) ? $_POST['author'] : $var['author'];
      
      //$_POST['tags'] = sort($_POST['tags']);
      $post_tags = (!empty($_POST['tags'])) ? implode(',', $_POST['tags']) : '';


      $posts_file = file($file['posts']);
      $p_lines = '';
      foreach ($posts_file as $single_line) {
        $p_data = explode(DELIMITER, $single_line);
        
        if (isset($p_data[1]) && $p_data[1] == $_POST['id']) {
          $p_lines .= $timestamp . DELIMITER . $_POST['id'] . DELIMITER . check_text($_POST['title']) . DELIMITER  . friendly_url($_POST['title']) . DELIMITER . $author . DELIMITER . $_POST['cat'] . DELIMITER . $status . DELIMITER . $post_tags . "\n";
        }
        else $p_lines .= $single_line;
      }
      

      $short_story = str_replace($conf['admin_url'], '{%MN_URL%}', $_POST['short_story']);
      $full_story = str_replace($conf['admin_url'], '{%MN_URL%}', $_POST['full_story']);


      if (isset($conf['posts_image']) && $conf['posts_image'] && isset($_FILES['image']['name']) && is_image($_FILES['image']['name'])) {

        $source_file = pathinfo_utf($_FILES['image']['name']);
        $clean_file_ext = strtolower($source_file['extension']);
        $clean_file = 'mn_post_' . $_POST['id'] . '.' . $clean_file_ext;
        $target_file = './' . $dir['images'] . $clean_file;

        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        if (isset($conf['posts_image_size']) && is_numeric($conf['posts_image_size']) && $conf['posts_image_size'] > 0) {
          resize_img($target_file, $conf['posts_image_size'], $target_file);
        }
    	  list($img_width, $img_height) = getimagesize($target_file);

        $post_image = $clean_file . ';' . $img_width . ';' . $img_height . ';' . filesize($target_file) . ';';

      }

      else {
        $post_image = $var['image'];
      }



	  // xFields
      if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
      
      	$xfields = get_unserialized_array('xfields');
      	$post_xfields = array();
      	foreach ($xfields as $xVar => $x) {
      		if ($x['section'] != 'posts') continue;
      		else {
      			$post_xfields[$xVar] = check_text($_POST['x' . $xVar], true);
      		}
      	}
      	
      	$xfields_serialized = serialize($post_xfields);
      
      }
      else $xfields_serialized = '';



      $p_content = SAFETY_LINE . "\n" . DELIMITER . $_POST['id'] . DELIMITER . $timestamp . DELIMITER . check_text($_POST['title']) . DELIMITER . friendly_url($_POST['title']) . DELIMITER . $author . DELIMITER . $_POST['cat'] . DELIMITER . $status . DELIMITER . $_POST['comments'] . DELIMITER . $var['views'] . DELIMITER . $post_tags . DELIMITER . $post_image . DELIMITER . '' . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . "\n" . check_text($short_story) . DELIMITER . "\n" . check_text($full_story);
      
      mn_put_contents($dir['posts'] . 'post_' . $_POST['id'] . '.php', $p_content);
      mn_put_contents($file['posts'], $p_lines);
      
      if (isset($_POST['comments-delete']) && $_POST['comments-delete'] == 'yes' && file_exists($dir['comments'] . 'comments_' . $_POST['id'] . '.php')) {
        unlink($dir['comments'] . 'comments_' . $_POST['id'] . '.php');
      }
      
      # display message about (un)successful edition
      if (file_exists($dir['posts'] . 'post_' . $_POST['id'] . '.php') && (file_get_contents($dir['posts'] . 'post_' . $_POST['id'] . '.php') == $p_content) && (file_get_contents($file['posts']) == $p_lines)) {
        if ($var['status'] == 4 && $status == 1) $back = 'post-approved';
        elseif ($var['status'] != 4 && $status == 5) $back = 'post-rejected';
        elseif ($status == 3) $back = 'post-saved';
        elseif ($status == 4) $back = 'post-unapproved';
        else $back = 'post-edited';

        header('location: ./mn-posts.php?action=edit&id=' . $_POST['id'] . '&i-ok=' . $back);
        exit;
      }
      else {
        header('location: ./mn-posts.php?action=edit&id=' . $_POST['id'] . '&i-error=error');
        exit;
      }
    }
    
    # if title, or news body is empty, MNews will generate error message
    else {
      $var = assign_post_vars($_POST['title'], $_POST['author'], $_POST['cat'], $_POST['short_story'], $_POST['full_story'], $_POST['date'], $_POST['date_day'], $_POST['date_month'], $_POST['date_year'], $_POST['date_hour'], $_POST['date_minute'], $_POST['status'], $_POST['comments'], 'edit', $_POST['id'], '');
      $var['tags_array'] = (!empty($_POST['tags'])) ? $_POST['tags'] : array();
      overall_header($lang['posts_edit_post'] . ' &raquo; ' . $var['title'], $lang['posts_msg_empty_values'], 'error', true);
    }

  }





  # [!] vyskúšať
  elseif (isset($_POST['action']) && $_POST['action'] == 'approve') {
    $var = get_post_data($_POST['id']);
    if ($var['status'] == 4 && $auth == 1) {
      $post_content = SAFETY_LINE . "\n" . DELIMITER . $var['id'] . DELIMITER . $var['timestamp'] . DELIMITER . $var['title'] . DELIMITER . $var['friendly_url'] . DELIMITER . $var['author'] . DELIMITER . $var['cat'] . DELIMITER . '1' . DELIMITER . $var['comments'] . DELIMITER . $var['views'] . DELIMITER . $var['tags'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $var['xfields'] . DELIMITER . "\n" . $var['short_story'] . DELIMITER . "\n" . $var['full_story'];
      mn_put_contents($dir['posts'] . 'post_' . $_POST['id'] . '.php', $post_content);
      
      $posts_file = file($file['posts']);
      $p_lines = '';
      foreach ($posts_file as $single_line) {
        $p_data = explode(DELIMITER, $single_line);

        if ($p_data[1] == $_POST['id']) {
          $p_lines .= $p_data[0] . DELIMITER . $p_data[1] . DELIMITER . $p_data[2] . DELIMITER  . $p_data[3] . DELIMITER . $p_data[4] . DELIMITER . $p_data[5] . DELIMITER . '1' . DELIMITER . trim($p_data[7]) . "\n";
        }
        else $p_lines .= $single_line;
      }
      mn_put_contents($file['posts'], $p_lines);
    }
    header('location: ./mn-posts.php');
    exit;
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $admin_tmpl['form_add_posts'] = false;

    if (isset($_GET['id']) && file_exists($dir['posts'] . 'post_' . $_GET['id'] . '.php')) {
      $var = get_post_data($_GET['id']);
      if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
        header('location: ./?access-denied');
        exit;
      }
      $admin_tmpl['form_post_delete'] = true;
      
      if (isset($_GET['nofancy'])) overall_header($lang['posts_delete_post'], $lang['posts_delete_post'], 'main');
    }
    
    
    else {
      header('location: ./mn-posts.php');
      exit;
    }
  }




  
  elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {

    if (isset($_POST['id']) && file_exists($dir['posts'] . 'post_' . $_POST['id'] . '.php')) {
      $var = get_post_data($_POST['id']);
      
      if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
        header('location: ./?access-denied');
        exit;
      }
      
      else {
        unlink($dir['posts'] . 'post_' . $_POST['id'] . '.php');
        @unlink($dir['comments'] . 'comments_' . $_POST['id'] . '.php');
        $post_img = explode(';', $var['image']);
        if (isset($post_img[0]) && !empty($post_img[0])) @unlink($dir['images'] . $post_img[0]);
        
        $posts_file = file($file['posts']);
        $p_lines = '';
        foreach ($posts_file as $single_line) {
          $p_data = explode(DELIMITER, $single_line);
          if (isset($p_data[1]) && $p_data[1] == $_POST['id']) continue;
          else $p_lines .= $single_line;
        }
        mn_put_contents($file['posts'], $p_lines);


        if (!file_exists($dir['posts'] . 'post_' . $_POST['id'] . '.php')) {
          header('location: ./mn-posts.php?i-ok=post-deleted');
          exit;
        }
        else {
          header('location: ./mn-posts.php?i-error=error');
          exit;
        }
      }
    }

    else {
      header('location: ./mn-posts.php?i-error=error');
      exit;
    }
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'show-post' && file_exists($dir['posts'] . 'post_' . $_GET['id'] . '.php')) {
    
    $var = get_post_data($_GET['id']);
    
    $story = preg_replace('#\[mn_gallery=(.*?)\]#ie', 'mn_gallery(\'$1\')', $var['short_story'] . $var['full_story']);
    $story = str_ireplace('{%MN_URL%}', $conf['admin_url'], $story);
    
    echo '<div class="show-post"><h2><a href="./mn-posts.php?action=edit&amp;id=' . $var['id'] . '">' . $var['title'] . '</a></h2>' . $story . '</div>';
    die();
  }





  else {
    $admin_tmpl['form_add_posts'] = false;
    $admin_tmpl['form_posts_list'] = true;
    
    
    if (file_exists(MN_ROOT . $file['posts'])) {
      # read posts file
      $p_file = file(MN_ROOT . $file['posts']);
      $posts_result = ''; $timestamps_all = array();

      array_shift($p_file);
      $p_file = mn_natcasesort($p_file);
      $p_file = array_reverse($p_file, true);

      $categories = load_basic_data('categories');
      $users = load_basic_data('users');


      # put posts to arrays - one array for IDs, one for timestamps
      foreach ($p_file as $p_line) {
        $temp_var = get_values('posts', $p_line, false);
        if ($auth != 1 && $temp_var['author'] != $_SESSION['mn_user_id']) continue;
        
        $temp_var['tags_array'] = (!empty($temp_var['tags'])) ? explode(',', trim($temp_var['tags'])) : array();

        $timestamps_all[$temp_var['timestamp']] = date('Y-m', $temp_var['timestamp']);
        if (isset($_GET['c']) && !empty($_GET['c']) && $temp_var['cat'] != $_GET['c']) continue;
        if (isset($_GET['d']) && !empty($_GET['d']) && date('Y-m', $temp_var['timestamp']) != $_GET['d']) continue;
        if (isset($_GET['a']) && !empty($_GET['a']) && $temp_var['author'] != $_GET['a']) continue;
        if (isset($_GET['s']) && !empty($_GET['s']) && $temp_var['status'] != $_GET['s']) continue;
        if (isset($_GET['t']) && !empty($_GET['t']) && !in_array($_GET['t'], $temp_var['tags_array'])) continue;
        if (isset($_GET['q']) && (strlen($_GET['q']) > 2) && !preg_match('/[!?\'$&\/()=%*:;,.@\"#~|_+{}<>]/i', $_GET['q'])) {
          $post_content = file_get_contents(MN_ROOT . $dir['posts'] . 'post_' . $temp_var['post_id'] . '.php');
          if (stripos($post_content, $_GET['q']) === false) continue;
        }


        

        $var = get_post_data($temp_var['post_id']);

        $approve_button = ($auth == 1 && $var['status'] == 4) ? ' | <form action="./mn-posts.php" method="post" id="approve_' . $var['id'] . '"><span class="simurl" onclick="$(\'form:#approve_' . $var['id'] . '\').submit();">' . $lang['uni_approve'] . '</span><input type="hidden" name="id" value="' . $var['id'] . '" /><input type="hidden" name="action" value="approve" /></form>' : '';
        $comments_count = get_comments_count($var['id']);
        $comments_s = ($var['comments'] == 1 && $conf['comments']) ? '' : ' s';
        $comments = ($comments_count > 0) ? '<!-- ' . $comments_count . ' --><a href="./mn-comments.php?p=' . $var['id'] . '" class="comment-link' . $comments_s . '">' . $comments_count . '</a>' : '<!-- 0 --><span class="trivial' . $comments_s . '">0</span>';
        $author = (!empty($users[$var['author']])) ? $users[$var['author']] : '<!-- anonym --><span class="trivial">' . $lang['posts_author_anonym'] . '</span>';
        $status = (($var['timestamp'] > mn_time()) && ($var['status'] == 1 || $var['status'] == 2)) ? '<span class="status_6">' . $lang['posts_future_post'] . '</span>' : '<span class="status_' . $var['status'] . '">' . $lang['posts_status_name_' . $var['status']] . '</span>';
        $datetime = (($var['timestamp'] == 9999999999) || empty($var['timestamp'])) ? '<span class="trivial">-</span>' : date('d.m.Y', $var['timestamp']) . '<br /><span class="trivial">' . date('H:i', $var['timestamp']) . '</span>';
        $var['title'] = (mb_strlen($var['title']) > 38) ? '<span title="' . $var['title'] . '">' . mb_substr($var['title'], 0, 36, 'utf-8') . '&hellip;</span>' : $var['title'];

        if ($var['cat'] == '-1') $category_name = '<!-- uncategorized --><span class="trivial">' . $lang['cats_uncategorized'] . '</span>';
        elseif (empty($categories[$var['cat']])) $category_name = '<!-- unknown --><span class="trivial">' . $lang['cats_unknown_category'] . '</span>';
        else $category_name = $categories[$var['cat']];

        if (isset($conf['web_url']) && !empty($conf['web_url']) && substr_count($conf['web_url'], 'http://') == 1) {
          $show_web = ' (<a href="' . generate_url($conf['web_url']) . 'mn_post=' . $var['id'] . '" title="' . $lang['posts_show_post_web'] . '">&raquo;</a>)';
        }
        else {
          $show_web = '';
        }

        $posts_result .= '<tr>
          <td class="cell-post">
            <!-- ' . $var['friendly_url'] . ' -->
            <a href="./mn-posts.php?action=edit&amp;id=' . $var['id'] . '" class="main-link">' . $var['title'] . '</a><br />
            &nbsp;<span class="links hide"><a href="./mn-posts.php?action=edit&amp;id=' . $var['id'] . '">' . $lang['uni_edit'] . '</a> | <a class="fancy" href="./mn-posts.php?action=delete&amp;id=' . $var['id'] . '">' . $lang['uni_delete'] . '</a> | <a href="./mn-posts.php?action=show-post&amp;id=' . $var['id'] . '" class="fancy">' . $lang['uni_show'] . '</a>' . $show_web . $approve_button . '</span>
          </td>
          <td>' . $author . '</td>
          <td>' . $category_name . '</td>
          <td>' . $status . '</td>
          <td class="c">' . $comments . '</td>
          <td><!-- ' . $var['timestamp'] . ' -->' . $datetime . '</td>
        </tr>';


      }
    
      if (!empty($timestamps_all)) {
        ksort($timestamps_all);
        $posts_timestamps = array_unique($timestamps_all);
      }
    }


    if  (isset($info['text']) && !empty($info['text'])) overall_header($lang['posts_posts'], $info['text'], $info['style']);
    else overall_header($lang['posts_posts'], $lang['posts_posts'], 'main');
  }





  # show template for news add/edit
  if (isset($admin_tmpl['form_add_posts']) && $admin_tmpl['form_add_posts']) {
?>

  

  <form action="./mn-posts.php" method="post" enctype="multipart/form-data" id="posts-add-edit" class="p-form">
    <fieldset>
      <table id="table-posts-add-edit">
        <tr>
          <td class="labels"><label for="title"><img src="./stuff/img/icons/title.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_title'];?><span class="required">*</span></label></td>
          <td class="inputs"><input type="text" name="title" id="title" class="text" tabindex="1" value="<?php echo $var['title'];?>" /></td>
        </tr>
        <?php
          if (file_exists($file['categories'])) {
            $categories = load_basic_data('categories');

            if (!empty($categories)) {
              echo '<tr><td><label for="cat"><img src="./stuff/img/icons/category.png" alt="-" width="16" height="16" /> ' . $lang['posts_cat'] . '</label></td><td>';
              echo '<select name="cat" id="cat" tabindex="2" class="custom long"><option value="-1">----- ' . $lang['posts_select_category'] . ' -----</option>';
              foreach ($categories as $cat_id => $cat_name) {
                $sel = ($cat_id == $var['cat']) ? ' selected="selected"' : '';
                echo '<option value="' . $cat_id . '"' . $sel . '>' . $cat_name . '</option>';
              }
              echo '</select>';
              if (user_auth(2, true) == 1) {
                echo ' <img src="./stuff/img/icons/add-gray.png" alt="-" width="16" height="16" id="post-img-add-cat" class="imgurl tooltip" title="' . $lang['cats_add_category'] . '" />';
                echo '<div id="post-add-cat" class="hide round"><span id="post-add-cat-hide" class="simurl fr tooltip" title="' . $lang['uni_cancel'] . '">x</span>' . $lang['cats_cat_name'] . ':<br /><input type="text" class="text" name="add_cat" id="add_cat" /> <input type="button" id="catajax" class="submit" value="' . $lang['uni_add'] . '" /></div>';
              }
              echo '</td></tr>';
            }
          }

          if (isset($conf['posts_image']) && $conf['posts_image']) {
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($var['image'])) {
              $post_img = explode(';', $var['image']);
              $actuall_image = (file_exists($dir['images'] . $post_img[0])) ? ' <span class="trivial">(<a href="./' . $dir['images'] . $post_img[0] . '?' . time() . '" class="fancy">' . $post_img[0] . '</a>)</span>' : '';
            }
            else $actuall_image = '';
            echo '<tr>
              <td><label for="image"><img src="./stuff/img/icons/image.png" alt="-" width="16" height="16" /> ' . $lang['posts_image'] . '</label></td>
              <td><input type="file" class="file" name="image" id="image" />' . $actuall_image . '<!-- <img src="./stuff/img/icons/folder-browse.png" alt="-" width="16" height="16" class="tooltip" title="' . $lang['files_browse_files'] . '" />--></td></tr>';
          }
        ?>
      </table>
      
	  <?php if (isset($conf['posts_image']) && $conf['posts_image']) echo '<div id="post-image"></div>'; ?>




      <p class="ta-description">
      	<?php if (($conf['admin_wysiwyg'])) { ?>
      	<span class="wysiwyg-toggle">
      		<span class="fancy mce-add-image short_story tooltip" rel="short_story" title="<?php echo $lang['posts_insert_image_file'];?>"><img src="./stuff/img/icons/image-add.png" alt="-" width="16" height="16" /> <span class="simurl"><?php echo $lang['posts_insert_upload'];?></span></span>
      		<!--<span class="fancy mce-add-gallery tooltip" rel="short_story" title="<?php echo $lang['posts_insert_gallery'];?>"><img src="./stuff/img/icons/images-add.png" alt="-" width="16" height="16" /></span>-->
      		<span class="mce-wysiwyg" rel="short_story"><img src="./stuff/img/icons/html.png" alt="html" class="tooltip" title="<?php echo $lang['posts_wysiwyg_toggle'];?>" /></span>
      	</span>
      	<?php } ?>
      	<label for="short_story"><img src="./stuff/img/icons/short-story.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_short_story'];?><span class="required">*</span></label>
      </p>
      <div class="ta-wrap">
        <?php if (!$conf['admin_wysiwyg']) {$textarea_id = 'short_story'; include "./stuff/inc/tmpl/wysiwyg-false.php";}?>
        <textarea name="short_story" id="short_story"<?php echo ($conf['admin_wysiwyg']) ? ' class="tinymce"' : ''?> tabindex="3" rows="5" cols="60"><?php echo check_text(str_ireplace('{%MN_URL%}', $conf['admin_url'], $var['short_story']));?></textarea>
      </div>



      <div id="full-story-wrap" class="ta-wrap<?php echo (trim($var['full_story']) == '') ? ' hide' : '';?>">
        <p class="ta-description">
        	<?php if ($conf['admin_wysiwyg']) { ?>
        	<span class="wysiwyg-toggle w2">
        		<span class="fancy mce-add-image full_story tooltip" rel="full_story" title="<?php echo $lang['posts_insert_image_file'];?>"><img src="./stuff/img/icons/image-add.png" alt="-" width="16" height="16" /> <span class="simurl"><?php echo $lang['posts_insert_upload'];?></span></span>
        		<span class="mce-wysiwyg" rel="full_story"><img src="./stuff/img/icons/html.png" alt="html" class="tooltip" title="<?php echo $lang['posts_wysiwyg_toggle'];?>" /></span>
        	</span>
        	<?php } ?>
        	<label for="full_story"><img src="./stuff/img/icons/full-story.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_full_story'];?></label>
        </p>
        <?php if (!$conf['admin_wysiwyg']) {$textarea_id = 'full_story'; include "./stuff/inc/tmpl/wysiwyg-false.php";}?>
        <textarea name="full_story" id="full_story"<?php echo ($conf['admin_wysiwyg']) ? ' class="tinymce"' : ''?> rows="10" cols="100"><?php echo check_text(str_ireplace('{%MN_URL%}', $conf['admin_url'], $var['full_story']));?></textarea>
      </div>




	<?php
	
		if (file_exists(MN_ROOT . $file['xfields'])) {
		
			$xfields = get_unserialized_array('xfields');
			$xfields_rows = '';
			foreach ($xfields as $xVar => $x) {
				if ($x['section'] != 'posts') continue;
				else {
					$thisVar = (isset($_POST['x' . $xVar])) ? check_text($_POST['x' . $xVar], true, false, 'xf') : @$var['xfields_array'][$xVar];
					if (isset($x['type']) && $x['type'] == 'select') {
						$xField = '<select name="x' . $xVar . '" id="x' . $xVar . '" class="long">';
						foreach ($x['options'] as $oKey => $oValue) {
							$sel = ($thisVar == $oKey) ? ' selected="selected"' : '';
							$xField .= '<option value="' . $oKey . '"' . $sel . '>' . $oValue . '</option>';
						}
						$xField .= '</select>';
					}
					else {
						$xField = '<input type="text" name="x' . $xVar . '" id="x' . $xVar . '" value="' . $thisVar . '" class="text" />';
					}
					$xfields_rows .= '<tr><td class="r"><label for="x' . $xVar . '">' . $x['name'] . ':</label></td><td>' . $xField . '</td></tr>';
				}
			}
		
		}
		
		if (!empty($xfields_rows)) {
		
			echo '<div id="xfields"><div class="hide round" id="xfields-in"><strong>' . $lang['xfields_xfields'] . '</strong><table>';
			echo $xfields_rows;
			echo '</table><input type="hidden" name="x_fields" value="true" /></div></div>';
		
		}

	?>


      <div id="tags"><div class="hide round" id="tags-in">
        <strong><?php echo $lang['tags_tags'];?></strong>
        <?php
          echo '<ul class="tags">';
          if (file_exists($file['tags'])) {
            $tags = load_basic_data('tags');
            if (!empty($tags)) {
              foreach ($tags as $tag_id => $tag_name) {
                $checked = (!empty($var['tags_array']) && in_array($tag_id, $var['tags_array'])) ? ' checked="checked"' : '';
                $tag_name = (strlen($tag_name) > 30) ? substr($tag_name, 0, 20) . '&hellip;' : $tag_name;
                echo '<li><label><input type="checkbox" name="tags[]" class="input-tag" id="tag-' . $tag_id . '" value="' . $tag_id . '"' . $checked . '> ' . $tag_name . '</label></li>';
              }
            }
          }
          echo '</ul>';
        ?>
        
        <?php if (user_auth('2', true) == 1) { ?>
        <div id="tag-add">
          <?php if (!empty($tags)) echo '<div class="fr"><label><input type="checkbox" id="select-all"> ' . $lang['posts_select_all'] . '</label></div>';?>
          <?php echo '<input type="text" name="new_tag" id="new_tag" value="" /> <input type="button" id="tagajax" class="submit" value="' . $lang['uni_add'] . '" />';?>
        &nbsp;</div>
        <?php } ?>
      </div></div>


      <div id="settings"><div class="hide round" id="settings-in">
      	<strong><?php echo $lang['posts_settings'];?></strong>
        <table>
          <?php if ($var['action'] == 'edit') { ?>
          <tr>
            <td><img src="./stuff/img/icons/user.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_author'];?></td>
            <td>
              <?php
                $users = load_basic_data('users');
                if ($auth == 1) {
                  echo '<select name="author" class="long">';
                  foreach ($users as $user_id => $user_name) {
                    $sel = ($user_id == $var['author']) ? ' selected="selected"' : '';
                    echo '<option value="' . $user_id . '"' . $sel . '>' . $user_name . '</option>';
                  }
                  echo '</select>';
                }
                else echo '<em>' . $users[$var['author']] . '</em><input type="hidden" name="author" value="' . $var['author'] . '" />';
              ?>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td class="labels"><img src="./stuff/img/icons/date.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_date'];?></td>
            <td class="inputs">
              <ul>
                <li>
                  <input type="radio" class="radio" name="date" id="date1" value="<?php echo ($var['action'] == 'add') ? 'now' : 'constant';?>"<?php if (!isset($_POST['date']) || $_POST['date'] == 'now' || $_POST['date'] == 'constant' || $var['timestamp'] == '9999999999') echo " checked='checked'";?> />
                  <label for="date1"><?php if ($var['action'] == 'add' || $var['timestamp'] == '9999999999') echo '<label for="date1"><em>' . $lang['posts_date_now'] . '</em></label>'; else echo '<label for="date1"><em>' . $var['date_day'] . '.' . $var['date_month'] . '.' . $var['date_year'] . ' - ' . $var['date_hour'] . ':' . $var['date_min'] . '</em></label>';?></label>
                </li>

                <li>
                  <input type="radio" class="radio" name="date" id="date2" value="specific"<?php if (isset($var["date"]) && $var["date"] == "specific") echo " checked='checked'";?> />
                  <select name="date_day" id="date_day" title="<?php echo $lang['posts_date_day'];?>" onchange="$('#date2').attr('checked', 'checked');"><?php for ($i=1;$i<=31;$i++) {$sel = $var["date_day"]==$i ? ' selected="selected"' : ""; $j = $i<10 ? "0".$i : $i; echo "<option value='$j' id='date_day_$j'$sel>$j</option>";} echo "</select>";?>.<select name="date_month" title="<?php echo $lang['posts_date_month'];?>" onchange="$('#date2').attr('checked', 'checked');"><?php for ($i=1;$i<=12;$i++) {$sel = $var["date_month"]==$i ? ' selected="selected"' : ""; $j = $i<10 ? "0".$i : $i; echo "<option value='$j' id='date_month_$j'$sel>" . $lang['month'][$i] . "</option>";} echo "</select>";?>.<select name="date_year" title="<?php echo $lang['posts_date_year'];?>" onchange="$('#date2').attr('checked', 'checked');"><?php for ($i=2006;$i<=(date("Y")+2);$i++) {$sel = $var["date_year"]==$i ? ' selected="selected"' : ""; echo "<option value='$i' id='date_year_$i'$sel>$i</option>";} echo "</select>";?> -
                  <select name="date_hour" title="<?php echo $lang['posts_date_hour'];?>" onchange="$('#date2').attr('checked', 'checked');"><?php for ($i=0;$i<=23;$i++) {$sel = $var["date_hour"]==$i ? ' selected="selected"' : ""; $j = $i<10 ? "0".$i : $i; echo "<option value='$j' id='date_hour_$j'$sel>$j</option>";} echo "</select>";?>:<select name="date_min" title="<?php echo $lang['posts_date_minute'];?>" onchange="$('#date2').attr('checked', 'checked');"><?php for ($i=0;$i<=59;$i++) {$sel = $var["date_min"]==$i ? ' selected="selected"' : ""; $j = $i<10 ? "0".$i : $i; echo "<option value='$j' id='date_min_$j'$sel>$j</option>";} echo "</select>";?>
                  <?php if (isset($var['action']) && $var['action'] == 'edit') {?><img src="./stuff/img/icons/date-go.png" alt="[#]" class="imgurl tooltip" title="<?php echo $lang['posts_data_current'];?>" onclick="document.getElementById('date2').checked=true; document.getElementById('date_day_<?php echo date('d');?>').selected=true; document.getElementById('date_month_<?php echo date('m');?>').selected=true; document.getElementById('date_year_<?php echo date('Y');?>').selected=true; document.getElementById('date_hour_<?php echo date('H');?>').selected=true;document.getElementById('date_min_<?php echo date('i');?>').selected=true;" /><?php } ?>
                  <!--<span class="help">(DD.MM.YYYY - HH:MM)</span>-->
                </li>
              </ul>
            </td>
          </tr>
          <tr>
            <td class="labels"><img src="./stuff/img/icons/status.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_status'];?></td>
            <td class="inputs">
              <ul>
                <li>
                  <input type="radio" class="radio" name="status" id="status1" value="1"<?php if ($auth > 1 && $var['status'] > 3) echo ' disabled'; elseif ($var['status'] == '1') echo ' checked="checked"';?> />
                  <label for="status1"><?php echo $lang['posts_status_form_1']; if ($auth == 3) echo ' <span class="help">(' . $lang['posts_status_form_1_help'] . ')</span>';?></label>
                </li>
                
                <li>
                  <input type="radio" class="radio" name="status" id="status2" value="2"<?php if ($auth > 1 && $var['status'] > 3) echo ' disabled'; elseif ($var['status'] == '2') echo ' checked="checked"';?> />
                  <label for="status2"><?php echo $lang['posts_status_form_2'] . ' <span class="help">(' . $lang['posts_status_form_2_help'] . ')</span>';?></label>
                </li>

                <li>
                  <input type="radio" class="radio" name="status" id="status3" value="3"<?php if ($auth > 1 && $var['status'] > 3) echo ' disabled'; elseif ($var['status'] == '3') echo ' checked="checked"';?> />
                  <label for="status3"><?php echo $lang['posts_status_form_3'];?></label>
                </li>
                
                <?php if ($var['status'] == 4) { ?>
                <li>
                  <input type="radio" class="radio" name="status" id="status4" value="4" checked="checked" />
                  <label for="status4"><?php echo $lang['posts_status_form_4'];?></label>
                </li>
                <?php } ?>
                
                <?php if ($var['status'] == 5 && $auth > 1) { ?>
                <li>
                  <input type="radio" class="radio" name="status" id="status4" value="4" checked="checked" />
                  <label for="status4"><?php echo $lang['posts_status_form_4_alt'];?></label>
                </li>
                <?php } ?>
                
                <?php if ($var['action'] == 'edit' && ($auth == 1 || $var['status'] == 5)) { ?>
                <li>
                  <input type="radio" class="radio" name="status" id="status5" value="5"<?php if ($var['status'] == '5') echo ' checked="checked"';?> />
                  <label for="status5"><?php echo $lang['posts_status_form_5'];?></label>
                </li>
                <?php } ?>
              </ul>
            </td>
          </tr>
          <tr>
            <td><img src="./stuff/img/icons/comments.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_comments'];?></td>
            <td>
              <ul>
                <li><input type="radio" class="radio" name="comments" id="comments1" value="1"<?php if ($var['comments'] == '1') echo ' checked="checked"';?> /> <label for="comments1"><?php echo $lang['posts_comments_option1'];?></label></li>
                <li><input type="radio" class="radio" name="comments" id="comments0" value="0"<?php if ($var['comments'] == '0') echo ' checked="checked"';?> /> <label for="comments0"><?php echo $lang['posts_comments_option0'];?></label></li>
                <?php if ($var["action"] == "edit" && file_exists($dir['comments'] . 'comments_' . $var['id'] . '.php')) { ?>
                  <li><input type="radio" class="radio" name="comments" id="comments2" value="2"<?php if ($var['comments'] == '2') echo ' checked="checked"';?> /> <label for="comments2"><?php echo $lang['posts_comments_option2'];?></label></li>
                  <li class="special-li"><input type="checkbox" class="checkbox" name="comments-delete" id="comments-delete" value="yes" /> <label for="comments-delete"><?php echo $lang['posts_comments_option_delete'];?></label></li>
                <?php } ?>
              </ul>
            </td>
          </tr>
        </table>
      </div></div>
      
      <div class="toggles">
        <p class="fr r">
          <?php if (!empty($xfields_rows)) echo '<span class="simurl toggle" rel="xfields-in"><img src="./stuff/img/icons/textfield.png" alt="-" width="16" height="16" /> ' . $lang['xfields_xfields'] . '</span> | ';?>
          <span class="simurl" id="p-tags-viewer"><img src="./stuff/img/icons/tags.png" alt="-" width="16" height="16" /> <?php echo $lang['tags_tags'];?></span> |
          <span class="simurl toggle" rel="settings-in"><img src="./stuff/img/icons/settings.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_settings'];?></span>
        </p>

        <?php if (empty($var['full_story'])) { ?>
        <p class="fl l">
          <span class="simurl toggle750" rel="full-story-wrap"><img src="./stuff/img/icons/full-story-toggle.png" alt="-" width="16" height="16" /> <?php echo $lang['posts_toggle_full_story'];?></span>
        </p>
        <?php } ?>
      </div>
      
      <p class="c cb">
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php
        if ($var['action'] != 'add') {
          echo '<input type="hidden" name="id" value="' . $var['id'] . '" />';
        }
        
        if (empty($categories)) {
          echo '<input type="hidden" name="cat" value="-1" />';
        }


        if ($var['action'] == 'edit' && $var['status'] == 4 && $auth == 1) {
          echo '<button type="submit" name="approve" class="submit"><img src="./stuff/img/icons/tick.png" alt="" width="16" height="16" /> ' . $lang['posts_submit_approve'] . '</button>';
          echo '<button type="submit" name="reject" class="submit"><img src="./stuff/img/icons/cross.png" alt="" width="16" height="16" /> ' . $lang['posts_submit_reject'] . '</button>';
        }
        elseif ($var['action'] == 'edit') {
          if ($var['status'] == 3) {
            echo '<button type="submit" name="publish" class="submit"><img src="./stuff/img/icons/tick.png" alt="" width="16" height="16" /> ' . $lang['posts_submit_publish'] . '</button>';
          }
          echo '<button type="submit" name="save" class="submit"><img src="./stuff/img/icons/save.png" alt="" width="16" height="16" /> ' . $lang['posts_submit_save'] . '</button>';
        }
        else {
          echo '<button type="submit" name="add" class="submit"><img src="./stuff/img/icons/add.png" alt="" width="16" height="16" /> ' . $lang['posts_add_post'] . '</button>';
        }
      ?>
      </p>
    </fieldset>

  </form>


<?php
  }
  
  if (isset($admin_tmpl['form_post_delete']) && $admin_tmpl['form_post_delete']) {
?>

  <form action="./mn-posts.php" method="post" id="post-delete" class="item-delete">
    <fieldset>
      <?php echo $lang['posts_q_really_delete'];?>: <strong><?php echo $var['title'];?></strong>?
      <div class="preview"><?php echo str_ireplace('{%MN_URL%}', $conf['admin_url'], $var['short_story']);?></div>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['id'];?>" /><br />
        <button type="submit" name="submit"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['posts_delete_post'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  if (isset($_GET['nofancy'])) overall_footer();
  die();
  }


  if (isset($admin_tmpl['form_posts_list']) && $admin_tmpl['form_posts_list']) {

    echo '<div class="simbutton fl"><a href="./mn-posts.php?action=add"><img src="./stuff/img/icons/add.png" alt="" width="16" height="16" /> ' . $lang['posts_add_new_post'] . '</a></div>';
    echo '<div class="rel-links">';
    if (user_auth('2', true)) {
      echo '<a href="./mn-categories.php" class="custom"><img src="./stuff/img/icons/categories.png" alt="" width="16" height="16" /> ' . $lang['cats_categories'] . '</a>';
      if (file_exists(MN_ROOT . $file['tags'])) echo ' | <a href="./mn-tags.php" class="custom"><img src="./stuff/img/icons/tags.png" alt="" width="16" height="16" /> ' . $lang['tags_tags'] . '</a>';
    }
    if (empty($_GET['c']) && empty($_GET['t']) && empty($_GET['a']) && empty($_GET['d']) && empty($_GET['s']) && empty($_GET['q']) && empty($posts_result)) echo '';
    elseif (empty($_GET['c']) && empty($_GET['t']) && empty($_GET['a']) && empty($_GET['d']) && empty($_GET['s']) && empty($_GET['q'])) echo ' | <span class="simurl" id="filter-viewer"> <img src="./stuff/img/icons/view-settings.png" alt="" width="16" height="16" /> ' . $lang['posts_filter_settings'] . '</span>';
    else echo ' | <a href="./mn-posts.php" class="custom"><img src="./stuff/img/icons/view-settings-cancel.png" alt="" width="16" height="16" /> ' . $lang['posts_filter_cancel'] . '</a>';
    echo '</div>';

    $class = ((empty($_GET['c']) && empty($_GET['t']) && empty($_GET['a']) && empty($_GET['d']) && empty($_GET['s']) && empty($_GET['q'])) || isset($_GET['approve'])) ? ' hide' : '';
    echo '<p class="cleaner">&nbsp;</p><form action="./mn-posts.php" method="get" class="filter' . $class . '">';

    if (file_exists(MN_ROOT . $file['categories'])) {
      echo '<select name="c">';
      echo '<option value="" class="description">--- ' . $lang['posts_all_categories'] . ' ---</option>';
      $categories = load_basic_data('categories');
      if (!empty($categories)) {
        foreach ($categories as $cat_id => $cat_name) {
          $sel = (isset($_GET['c']) && $cat_id == $_GET['c']) ? ' selected="selected" class="selected"' : '';
          echo '<option value="' . $cat_id . '"' . $sel . '>' . $cat_name . '</option>';
        }
      }
      $sel = (isset($_GET['c']) && $_GET['c'] == '-1') ? ' selected="selected" class="selected"' : '';
      echo '<option value="-1"' . $sel . '>' . $lang['cats_uncategorized'] . '</option>';
      echo '</select> ';
    }
    
    
    
    if (file_exists(MN_ROOT . $file['tags'])) {
      $tags = load_basic_data('tags');
      if (!empty($tags)) {
        echo '<select name="t">';
        echo '<option value="" class="description">--- ' . $lang['posts_all_tags'] . ' ---</option>';
        if (!empty($tags)) {
          foreach ($tags as $tag_id => $tag_name) {
            $sel = (isset($_GET['t']) && $tag_id == $_GET['t']) ? ' selected="selected" class="selected"' : '';
            echo '<option value="' . $tag_id . '"' . $sel . '>' . $tag_name . '</option>';
          }
        }
        echo '</select> ';
      }
    }


    if ($auth == 1) {
      $posts_counts = get_posts_count('users');
      
      echo '<select name="a">';
      echo '<option value="" class="description">--- ' . $lang['posts_all_authors'] . ' ---</option>';
      $users = load_basic_data('users');
      if (!empty($users)) {
        foreach ($users as $user_id => $user_name) {
          $sel = (isset($_GET['a']) && $user_id == $_GET['a']) ? ' selected="selected" class="selected"' : '';
          
          if (empty($posts_counts[$user_id])) continue;
          else echo '<option value="' . $user_id . '"' . $sel . '>' . $user_name . '</option>';
        }
      }
      echo '</select> ';
    }


    echo '<select name="d">';
    echo '<option value="" class="description">--- ' . $lang['posts_all_dates'] . ' ---</option>';
    foreach ($posts_timestamps as $key => $value) {
      $sel = (isset($_GET['d']) && $value == $_GET['d']) ? ' selected="selected" class="selected"' : '';
      
      if ($key == '9999999999') continue;
      else echo '<option value="' . $value . '"' . $sel . '>' . $lang['month'][date('n', $key)] . ' ' . date('Y', $key) . '</option>';
    }
    echo '</select> ';


    echo '<select name="s">';
    echo '<option value="" class="description">--- ' . $lang['posts_status_all'] . ' ---</option>';
    for ($i=1;$i<=5;$i++) {
      $sel = (isset($_GET['s']) && $i == $_GET['s']) ? ' selected="selected" class="selected"' : '';
      echo '<option value="' . $i . '"' . $sel . '>' . $lang['posts_status_' . $i] . '</option>';
    }
    echo '</select> ';


    echo '<input type="submit" class="submit" value="' . $lang['posts_filter'] . '" />';
    echo '<div id="search"><input type="text" name="q" id="q" value="' . htmlspecialchars(@$_GET['q'], ENT_QUOTES) . '" /> <input type="submit" class="submit" value="' . $lang['uni_search'] . '" /></div>';
    echo '</form>';

    if (isset($posts_result) && !empty($posts_result)) {
?>

  <table id="posts-list" class="tablesorter">
  <thead><tr><th id="cell-post"><?php echo $lang['posts_post'];?></th><th id="cell-author"><?php echo $lang['posts_author'];?></th><th id="cell-cat"><?php echo $lang['posts_cat'];?></th><th id="cell-status"><?php echo $lang['posts_status'];?></th><th id="cell-comments" class="num"><img src="./stuff/img/icons/comments-gray.png" alt="C" class="tooltip" title="<?php echo $lang['posts_comments'];?>" /></th><th id="cell-date" class="date"><?php echo $lang['posts_date'];?></th></tr></thead>
  
  <tbody><?php echo $posts_result;?></tbody>
  </table>

  <div id="pager" class="pager<?php if (count($timestamps_all) <= 10) echo ' hide'; ?>">
    <form action="./mn-posts.php">
      <select class="pagesize fr"><option value="5">5</option><option selected="selected" value="10">10</option><option value="20">20</option><option value="30">30</option><option value="50">50</option><option value="<?php echo count($timestamps_all);?>"><?php echo $lang['posts_all_posts'];?></option></select>
      <img src="./stuff/img/icons/control-first.png" class="first" alt="&laquo;" title="<?php echo $lang['posts_page_first'];?>" /> <img src="./stuff/img/icons/control-prev.png" class="prev" alt="&lt;" title="<?php echo $lang['posts_page_prev'];?>" />
      <input type="text" class="pagedisplay" />
      <img src="./stuff/img/icons/control-next.png" class="next" alt="&gt;" title="<?php echo $lang['posts_page_next'];?>" /> <img src="./stuff/img/icons/control-last.png" class="last" alt="&raquo;" title="<?php echo $lang['posts_page_last'];?>" />
    </form>
  </div>
  
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-pager.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false}) .tablesorterPager({container: $("#pager")});});</script>
  
<?php
    }
    elseif (empty($_GET['a']) && empty($_GET['t']) && empty($_GET['d']) && empty($_GET['c']) && empty($_GET['s']) && empty($_GET['q'])) echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['posts_msg_no_posts'] . '</p>';
    else echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['posts_msg_no_posts_criteria'] . '</p>';
  }


  overall_footer();
?>