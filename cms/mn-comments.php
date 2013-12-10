<?php

  if (isset($_POST['action']) && $_POST['action'] == 'add-comment') {

    session_start();
    
    define('IN_MNews', true);
    define('MN_ROOT', './');
    define('MN_LOGGED', false);

    @include_once './data/databases/config.php';
    include_once  './stuff/inc/mn-functions.php';
    $lng = select_lang();
    include_once  './stuff/lang/lang_' . $lng .'.php';
    include_once  './stuff/inc/mn-definitions.php';

    if (file_exists($file['banned_ips'])) include_once $file['banned_ips'];
    else $banned_ips = array();
    
    
    
    $mn_users = load_basic_data('users');
    $post = get_post_data($_POST['post_id']);
    $mn_redir = (isset($_POST['redir']) && !empty($_POST['redir'])) ? $_POST['redir'] : str_replace('&mn_msg=c_added', '', $_SERVER['HTTP_REFERER']);
    $conf['comments_antiflood'] = (isset($conf['comments_antiflood']) && is_numeric($conf['comments_antiflood'])) ? $conf['comments_antiflood'] : '30';

    if (isset($_SESSION['mn_logged']) && $_SESSION['mn_logged'] && !check_hash()) {
      session_destroy();
      $url_data = explode('/', $conf['admin_url']);
      setcookie('mn_user_hash', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
      setcookie('mn_logged', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
      header('location: ' . $mn_redir . '#mn-comment-form');
      exit;
    }
    elseif (isset($_SESSION['mn_logged']) && !$_SESSION['mn_logged'] && isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_user_hash']) && ($conf['users_perm_login'])) permanent_login();
    elseif ((in_array(@$_POST['comment_author'], $mn_users)) || (isset($_POST['comment_pass']) && !empty($_POST['comment_pass']))) do_login($_POST['comment_author'], $_POST['comment_pass'], false);
    
    
    
    if ($post['comments'] == '1' && ($conf['comments'] === true || $conf['comments'] >= 1) && !check_ip_ban($_SERVER['REMOTE_ADDR'], $banned_ips)) {
    
    
      // Check for correct captcha code
      if ((!isset($_SESSION['mn_logged']) || !$_SESSION['mn_logged']) && isset($conf['comments_captcha']) && ($conf['comments_captcha'])) {
        require_once('./stuff/inc/recaptchalib.php');
        $captcha = recaptcha_check_answer ('6LfnaQoAAAAAAPi1X1HiWwEWBnCmJ7jLUc5biRpE', $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
      }
      
      
    
      if (isset($_POST['preview']) && isset($_POST['comment_text']) && !empty($_POST['comment_text'])) {
        $preview = true;
      }
    
      elseif ((!isset($_SESSION['mn_logged']) || !$_SESSION['mn_logged']) && in_array($_POST['comment_author'], $mn_users)) $error_msg = $lang['comm_msg_password'];
      
      elseif (isset($_SESSION['mn_comm_time']) && ($_SESSION['mn_comm_time'] + $conf['comments_antiflood']) > time()) $error_msg = $lang['comm_msg_flood'];
      
      elseif ((!isset($_SESSION['mn_logged']) || !$_SESSION['mn_logged']) && isset($conf['comments_captcha']) && ($conf['comments_captcha']) && !$captcha->is_valid) $error_msg = $lang['comm_msg_captcha'];

      else {
    
        if ((isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) || $_POST['robot'] === trim($conf['comments_antispam'])) {

          if (((isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) || !empty($_POST['comment_author'])) && !empty($_POST['comment_text'])) {

            if (strlen($_POST['comment_text']) >= MIN_COMMENT_LENGTH) {

              if ((isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) || (!preg_match('/[!?\'$&\/()=%*:;,.@\"#~|_+{}<>]/i', $_POST['comment_author']))) {

                if ((isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) || ((strlen($_POST['comment_author']) >= 3) && (strlen($_POST['comment_author']) <= 30))) {

                  if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && file_exists($dir['posts'] . 'post_' . $_POST['post_id'] . '.php')) {


                    $c_content = (file_exists($dir['comments'] . 'comments_' . $_POST['post_id'] . '.php')) ? file_get_contents($dir['comments'] . 'comments_' . $_POST['post_id'] . '.php') : SAFETY_LINE . "\n";
                    $c_id = trim(file_get_contents($file['id_comments']));
                    
                    $c_status = ((!isset($_SESSION['mn_logged']) || !$_SESSION['mn_logged']) && isset($conf['comments_approval']) && $conf['comments_approval'] == true) ? 2 : 1;

                    if (isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) {
                      $u = get_values('users', $_SESSION['mn_user_id']);
                      $c['user_name'] = $u['username'];
                      $c['user_id'] = $u['user_id'];
                      $c['user_email'] = ($u['public_email'] == 1) ? $u['email'] : '';
                      $c['user_www'] = $u['www'];
                    }
                    else {
                      $c['user_name'] = $_POST['comment_author'];
                      $c['user_id'] = '';
                      $c['user_email'] = check_email($_POST['comment_email']);
                      $c['user_www'] = check_url($_POST['comment_www']);
                    }
                    
                    // xFields
                    if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
                    
                    	$xfields = get_unserialized_array('xfields');
                    	$comm_xfields = array();
                    	foreach ($xfields as $xVar => $x) {
                    		if ($x['section'] != 'comments') continue;
                    		else {
                    			$comm_xfields[$xVar] = check_text($_POST['x' . $xVar], true);
                    		}
                    	}
                    	
                    	$xfields_serialized = serialize($comm_xfields);
                    
                    }
                    else $xfields_serialized = '';


                    $comment_line = $c_id . DELIMITER . mn_time() . DELIMITER . $_POST['post_id'] . DELIMITER . $c_status . DELIMITER . $c['user_id'] . DELIMITER . $c['user_name'] . DELIMITER . $c['user_email'] . DELIMITER . $c['user_www'] . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . '' . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . gethostbyaddr($_SERVER['REMOTE_ADDR']) . DELIMITER . $_SERVER['HTTP_USER_AGENT'] . DELIMITER . check_comment_text($_POST['comment_text']) . "\n";
                    
                    if ($conf['web_encoding'] != 'utf-8' && !isset($_POST['form'])) $comment_line = iconv($conf['web_encoding'], 'utf-8', $comment_line);
                    
                    $c_content .= $comment_line;

                    if (mn_put_contents($file['id_comments'], ($c_id + 1)) && mn_put_contents($dir['comments'] . 'comments_' . $_POST['post_id'] . '.php', $c_content)) {
                      $_SESSION['mn_comm_time'] = time();
                      $mn_msg = (!@$_SESSION['mn_logged'] && isset($conf['comments_approval']) && ($conf['comments_approval'])) ? 'c_sent#mn-comment-form' : 'c_added#c-' . $c_id;
                      
                      $mn_reddir_delim = (stripos($mn_redir, '?') === false) ? '?' : '&';
                      header('location: ' . $mn_redir . $mn_reddir_delim . 'mn_msg=' . $mn_msg);
                      exit;
                    }
                    else {
                      $error_msg = $lang['comm_msg_put_contents_error'];
                    }


                  }
                  else $error_msg = $lang['comm_msg_post_id'];

                }
                else $error_msg = $lang['comm_msg_author_name_length'];

              }
              else $error_msg = $lang['comm_msg_forbidden_author_name'];

            }
            else $error_msg = str_replace('%n%', '<strong>' . MIN_COMMENT_LENGTH . '</strong>', $lang['comm_msg_comment_length']);

          }
          else $error_msg = $lang['comm_msg_empty_values'];

        }
        else $error_msg = $lang['comm_msg_spam'];

      }

      include MN_ROOT . 'stuff/inc/tmpl/comment-form-admin.php';
      
    }
    
    else {
      header('location: ../');
      exit;
    }


  }
 
 
 
 
 
  elseif (isset($_GET['action']) && $_GET['action'] == 'ajaxcall' && isset($_GET['file'])) {
   
     include './stuff/inc/mn-start.php';
     define('MN_LOGGED', true);

   
     if (file_exists($dir['comments'] . 'comments_' . $_GET['file'] . '.php')) {
       $c_file = file($dir['comments'] . 'comments_' . $_GET['file'] . '.php');
       array_shift($c_file);
       
       echo '<div id="comments-title">' . $lang['posts_post_comments'] . '</div>';
       
       $i = 1;
       foreach ($c_file as $c_line) {
         $c_var = get_values('comments', $c_line, false);
         echo '<div class="comment" id="c-' . $c_var['comment_id'] . '"><span class="info">[<a href="#c-' . $c_var['comment_id'] . '">' . $i . '</a>] <strong>' . $c_var['author_name'] . '</strong> ' . date('d.m.Y H:i', $c_var['timestamp']);
         
         echo (user_auth('3', true)) ? '<span class="links hide"> <a href="./mn-comments.php?action=reply&amp;post=' . $c_var['post_id'] . '" class="fancy">' . $lang['comm_reply'] . '</a> | <a href="./mn-comments.php?action=edit&amp;post=' . $c_var['post_id'] . '&amp;id=' . $c_var['comment_id'] . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-comments.php?a=m&amp;s=0&amp;f=' . $c_var['post_id'] .'&amp;c=' . $c_var['comment_id'] .'&amp;t=' . $_SESSION['mn_token'] . '&amp;from=post" class="ajaxcall">' . $lang['uni_delete'] . '</a></span>' : '';
           
         echo '</span><br />' . comment_format($c_var['comment_text']) . '</div>';
         $i++;
       }
     }
     
     else echo '<div id="no-comments" class="round"><img src="./stuff/img/icons/information.png" alt="" /> ' . $lang['comm_msg_post_no_comments'] . '</div>';
   }





  else {



    include './stuff/inc/mn-start.php';
    define('MN_LOGGED', true);
    $auth = user_auth('3');





    if (isset($_GET['action']) && $_GET['action'] == 'reply' && file_exists($dir['comments'] . 'comments_' . $_GET['post'] . '.php')) {
      $admin_tmpl['comment_reply'] = true;
    }





    elseif (isset($_POST['action']) && $_POST['action'] == 'reply' && isset($_POST['c_text']) && !empty($_POST['c_text']) && file_exists($dir['comments'] . 'comments_' . $_POST['post'] . '.php')) {
      $c_content = file_get_contents($dir['comments'] . 'comments_' . $_POST['post'] . '.php');
      $c_id = trim(file_get_contents($file['id_comments']));
      $user = get_values('users', $_SESSION['mn_user_id']);

      $c_content .= $c_id . DELIMITER . mn_time() . DELIMITER . $_POST['post'] . DELIMITER . '1' . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $user['username'] . DELIMITER . $user['email'] . DELIMITER . $user['www'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . gethostbyaddr($_SERVER['REMOTE_ADDR']) . DELIMITER . $_SERVER['HTTP_USER_AGENT'] . DELIMITER . check_comment_text($_POST['c_text']) . "\n";

      if (mn_put_contents($file['id_comments'], ($c_id + 1))) {
        if (mn_put_contents($dir['comments'] . 'comments_' . $_POST['post'] . '.php', $c_content)) {
          header('location: ./mn-comments.php?back=added');
          exit;
        }
        else overall_header($lang['comm_comments'], $lang['comm_msg_put_contents_error'], 'error');
      }
      else overall_header($lang['comm_comments'], $lang['comm_msg_put_contents_error'], 'error');
    }





    elseif (isset($_GET['action']) && ($_GET['action'] == 'delete' || $_GET['action'] == 'edit') && isset($_GET['post']) && isset($_GET['id'])) {
      $c_file = file($dir['comments'] . 'comments_' . $_GET['post'] . '.php');
      array_shift($c_file);
      
      if ($auth == 2) {
        $post = get_post_data($_GET['post']);
        if ($post['author'] != $_SESSION['mn_user_id']) {
          header('location: ./?access-denied');
          exit;
        }
      }

      foreach ($c_file as $c_line) {
        $temp_var = get_values('comments', $c_line, false);
        if ($temp_var['comment_id'] == $_GET['id']) $var = $temp_var;
        else continue;
      }

      if (empty($var)) {
        header('location: ./mn-comments.php');
        exit;
      }
      elseif ($_GET['action'] == 'edit') {
        $admin_tmpl['comment_edit_form'] = true;
        $var['xfields_array'] = unserialize($var['xfields']);
        if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['comm_comments'], $lang['comm_msg_comment_edited'], 'ok');
        else overall_header($lang['comm_edit_comment'] . ' #' . $_GET['id'], $lang['comm_edit_comment'] . ' #' . $_GET['id'], 'main');
      }
      else {
        $admin_tmpl['comment_delete'] = true;
      }
    }





    elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && file_exists($dir['comments'] . 'comments_' . $_POST['post'] . '.php')) {

      if (!empty($_POST['author_name']) && !empty($_POST['comment_text'])) {
        if (strlen($_POST['comment_text']) >= MIN_COMMENT_LENGTH) {
          if ((preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['author_name'])) && (strlen($_POST['author_name']) >= 3)) {

            if ($auth == 2) {
              $post = get_post_data($_POST['post']);
              if ($post['author'] != $_SESSION['mn_user_id']) {
                header('location: ./?access-denied');
                exit;
              }
            }

            $c_file = file($dir['comments'] . 'comments_' . $_POST['post'] . '.php');
            array_shift($c_file);
            $c_content = SAFETY_LINE . "\n";
            
            // xFields
            if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
            
            	$xfields = get_unserialized_array('xfields');
            	$comm_xfields = array();
            	foreach ($xfields as $xVar => $x) {
            		if ($x['section'] != 'comments') continue;
            		else {
            			$comm_xfields[$xVar] = check_text($_POST['x' . $xVar], true);
            		}
            	}
            	
            	$xfields_serialized = serialize($comm_xfields);
            
            }
            else $xfields_serialized = '';

            foreach ($c_file as $c_line) {
              $var = get_values('comments', $c_line, false);

              if ($var['comment_id'] == $_POST['id']) $c_content .= $var['comment_id'] . DELIMITER . $var['timestamp'] . DELIMITER . $var['post_id'] . DELIMITER . $var['status'] . DELIMITER . $var['author_id'] . DELIMITER . check_text($_POST['author_name']) . DELIMITER . check_email($_POST['author_email']) . DELIMITER . check_url($_POST['author_www']) . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . '' . DELIMITER . $var['ip_address'] . DELIMITER . $var['host'] . DELIMITER . $var['user_agent'] . DELIMITER . check_comment_text($_POST['comment_text']) . "\n";
              else $c_content .= $c_line;
            }

            if (mn_put_contents($dir['comments'] . 'comments_' . $_POST['post'] . '.php', $c_content)) {
              header('location: ./mn-comments.php?action=edit&post=' . $_POST['post'] . '&id=' . $_POST['id'] . '&back=edited');
              exit;
            }
            else overall_header($lang['comm_comments'], $lang['comm_msg_put_contents_error'], 'error');

          }
          else overall_header($lang['comm_edit_comment'] . ' #' . $_POST['comment_id'], $lang['comm_msg_forbidden_author_name'], 'error');
        }
        else overall_header($lang['comm_edit_comment'] . ' #' . $_POST['comment_id'], $lang['comm_msg_too_short'], 'error');
      }
      else overall_header($lang['comm_edit_comment'] . ' #' . $_POST['comment_id'], $lang['comm_msg_empty_values'], 'error');

      $var = array(
        'author_name' => $_POST['author_name'],
        'author_email' => $_POST['author_email'],
        'author_www' => $_POST['author_www'],
        'comment_text' => $_POST['comment_text'],
      );
      $admin_tmpl['comment_edit_form'] = true;
    }





    elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && file_exists($dir['comments'] . 'comments_' . $_POST['post'] . '.php')) {
      if ($auth == 2) {
        $post = get_post_data($_POST['post']);
        if ($post['author'] != $_SESSION['mn_user_id']) {
          header('location: ./?access-denied');
          exit;
        }
      }

      $c_file = file($dir['comments'] . 'comments_' . $_POST['post'] . '.php');
      $c_content = '';

      foreach ($c_file as $c_line) {
        $c_data = explode(DELIMITER, $c_line);

        if ($c_data[0] == $_POST['id']) continue;
        else $c_content .= $c_line;
      }

      if (mn_put_contents($dir['comments'] . 'comments_' . $_POST['post'] . '.php', $c_content)) {
        header('location: ./mn-comments.php?back=deleted');
        exit;
      }
      else {
        overal_header($lang['comm_comments'], $lang['comm_msg_put_contents_error'], 'error');
      }
    }





    elseif (isset($_GET['a']) && $_GET['a'] == 'm' && isset($_GET['t']) && $_GET['t'] == $_SESSION['mn_token']) {
      if (isset($_GET['f']) && isset($_GET['c']) && file_exists($dir['comments'] . 'comments_' . (int)$_GET['f'] . '.php')) {
        $c_file = file($dir['comments'] . 'comments_' .  (int)$_GET['f'] . '.php');
        $c_content = '';
        
        $status = (isset($_GET['s']) && $_GET['s'] >= 0 && $_GET['s'] <= 5) ? $_GET['s'] : '2';

        foreach ($c_file as $c_line) {
          $c_data = explode(DELIMITER, $c_line);

          if ($_GET['s'] == 'd' && (int)$c_data[0] == (int)$_GET['c']) continue;
          elseif ((int)$c_data[0] == (int)$_GET['c']) $c_content .= $c_data[0] . DELIMITER . $c_data[1] . DELIMITER . $c_data[2] . DELIMITER . $status . DELIMITER . $c_data[4] . DELIMITER . $c_data[5] . DELIMITER . $c_data[6] . DELIMITER . $c_data[7] . DELIMITER . $c_data[8] . DELIMITER . $c_data[9] . DELIMITER . $c_data[10] . DELIMITER . $c_data[11] . DELIMITER . $c_data[12] . DELIMITER . $c_data[13] . DELIMITER . trim($c_data[14]) . "\n";
          else $c_content .= $c_line;

        }

        if (mn_put_contents($dir['comments'] . 'comments_' . (int)$_GET['f'] . '.php', $c_content)) {
          if (isset($_GET['action'])) echo 'ok';
          else {
            header('location: ./mn-comments.php?back=trash');
            exit;
          }
        }
      }
    }





    elseif (isset($_POST['action']) && $_POST['action'] == 'bulk') {
    
      if (isset($_POST['a']) && isset($_POST['comments']) && isset($_POST['posts']) && !empty($_POST['a']) && !empty($_POST['comments']) && !empty($_POST['posts'])) {
      
        $c_posts = array_unique($_POST['posts']);
        foreach ($c_posts as $c_post) {
        
          if (file_exists($dir['comments'] . 'comments_' . $c_post . '.php')) {
            $c_file = file($dir['comments'] . 'comments_' . $c_post . '.php');
            $c_content = '';

            foreach ($c_file as $c_line) {
              $c_data = explode(DELIMITER, $c_line);

              if ($_POST['a'] == 'delete' && in_array($c_data[0], $_POST['comments'])) continue;
              elseif (strlen($_POST['a']) == 7 && substr($_POST['a'], 0, 6) == 'status' && in_array($c_data[0], $_POST['comments'])) $c_content .= $c_data[0] . DELIMITER . $c_data[1] . DELIMITER . $c_data[2] . DELIMITER . substr($_POST['a'], -1) . DELIMITER . $c_data[4] . DELIMITER . $c_data[5] . DELIMITER . $c_data[6] . DELIMITER . $c_data[7] . DELIMITER . $c_data[8] . DELIMITER . $c_data[9] . DELIMITER . $c_data[10] . DELIMITER . $c_data[11] . DELIMITER . $c_data[12] . DELIMITER . $c_data[13] . DELIMITER . trim($c_data[14]) . "\n";
              else $c_content .= $c_line;

            }
            
            mn_put_contents($dir['comments'] . 'comments_' . $c_post . '.php', $c_content);
          }
          
        }
        
        if ($_POST['a'] == 'delete') {
          header('location: ./mn-comments.php?trash&back=bulk-deleted');
          exit;
        }
        elseif ($_POST['a'] == 'status1') {
          header('location: ./mn-comments.php?back=bulk-status1');
          exit;
        }
        else {
          header('location: ./mn-comments.php?back=bulk-edited');
          exit;
        }
      }
      
      
      
      else {
        header('location: ./mn-comments.php');
        exit;
      }
      
    }





    else {
      $comments = array();

      if ($auth == 2) {
        $posts_dir = dir($dir['posts']);
        $temp_posts = array();

        while ($post_file = $posts_dir->read()) {
          if (!is_file($dir['posts'] . $post_file) || $post_file == 'mn-id.php') continue;
          else {
            $temp_var = get_post_data($post_file, false);

            if ($temp_var['author'] != $_SESSION['mn_user_id']) continue;
            else {
              $temp_posts[] = $temp_var['id'];
            }
          }
        }

        foreach ($temp_posts as $post_id) {
          if (file_exists($dir['comments'] . 'comments_' . $post_id . '.php')) {
            $c_file = file($dir['comments'] . 'comments_' . $post_id . '.php');
            array_shift($c_file);
            foreach ($c_file as $c_line) {
              $comments[] .= $c_line;
            }
          }
          else continue;
        }
      }


      else {
        $comments_dir = dir($dir['comments']);

        while ($comments_file = $comments_dir->read()) {
          $f_part = explode('.', $comments_file);
          if (!is_file($dir['comments'] . $comments_file) || (isset($f_part[1]) && $f_part[1] != 'php')) continue;
          else {
            $c_file = file($dir['comments'] . $comments_file);
            array_shift($c_file);
            foreach ($c_file as $c_line) {
              $comments[] .= $c_line;
            }
          }
        }
      }


      $c_trash = 0;
      
      if (!empty($comments)) {
        $comments = mn_natcasesort($comments);
        $comments = array_reverse($comments);
        $comments_result = '';
        $c_count = 0; $c_authors = array(); $c_ips = array();

        foreach ($comments as $comments_line) {
          $var = get_values('comments', $comments_line, false);
          $post = get_post_data($var['post_id']);

          $comments_timestamps[$var['timestamp']] = date('Y-m', $var['timestamp']);
          if (isset($var['author_id']) && !empty($var['author_id'])) $c_authors[] = $var['author_id'];
          if (isset($var['ip_address']) && !empty($var['ip_address'])) $c_ips[] = $var['ip_address'];
          if (isset($var['status']) && $var['status'] == 0) $c_trash++;
          $posts[$post['id']] = $post['title'];

          if (!isset($_GET['trash']) && $var['status'] == 0) continue;
          if (isset($_GET['trash']) && $var['status'] != 0) continue;
          if (isset($_GET['d']) && !empty($_GET['d']) && date('Y-m', $var['timestamp']) != $_GET['d']) continue;
          if (isset($_GET['s']) && !empty($_GET['s']) && (int)$var['status'] != (int)$_GET['s']) continue;
          if (isset($_GET['a']) && !empty($_GET['a']) && (int)$var['author_id'] != (int)$_GET['a']) continue;
          if (isset($_GET['i']) && !empty($_GET['i']) && $var['ip_address'] != $_GET['i']) continue;
          if (isset($_GET['q']) && (strlen($_GET['q']) > 2) && (stripos($var['comment_text'], $_GET['q']) === false)) continue;
          else {
            if (isset($_GET['p']) && !empty($_GET['p']) && $post['id'] != $_GET['p']) continue;
            else {
              $ip_info = (in_array(trim($var['ip_address']), $banned_ips)) ? ' <img src="./stuff/img/icons/warning.png" alt="" class="tooltip" title="' . $lang['uni_banned_ip'] . '" />' : ' (<a href="./mn-tools.php?action=quickban&amp;ip=' . $var['ip_address'] . '" class="tooltip fancy" title="' . $lang['uni_ban_ip'] . ' ' . $var['ip_address'] . '">' . $lang['uni_ban'] . '</a>)';
              $status = ($var['status'] != 1 && $var['status'] != 0) ? '<span id="s-' . $var['comment_id'] . '" class="comment_status status_' . $var['status'] . '">' . $lang['comm_status' . $var['status']] . '<br /></span>' : '<span id="s-' . $var['comment_id'] . '" class="comment_status status_' . $var['status'] . '"></span>';
              $status .= ($var['status'] == 2) ? ' <p class="process-links hide c"><a href="./mn-comments.php?a=m&amp;s=1&amp;f=' . $post['id'] . '&amp;c=' . $var['comment_id'] . '&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status1'] . '" class="ajaxcall approve">' . $lang['comm_process_approve'] . '</a> | <a href="./mn-comments.php?a=m&amp;s=5&amp;&amp;f=' . $post['id'] . '&amp;c=' . $var['comment_id'] . '&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status5'] . '" class="ajaxcall reject">' . $lang['comm_process_reject'] . '</a></p>' : '';
              $checked = ((isset($_GET['approve']) && $var['status'] == 2) || isset($_GET['check'])) ? ' checked="checked"' : '';
              
              
              if ($var['status'] == 5) $comment_links = '<a href="./mn-comments.php?a=m&amp;s=1&amp;f=' . $post['id'] . '&amp;c=' . $var['comment_id'] . '&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status1'] . '" class="ajaxcall approve">' . $lang['comm_process_approve'] . '</a> | ';
              else $comment_links = '';
              
              $showhide = ($var['status'] == 3) ? '<a href="./mn-comments.php?a=m&amp;s=1&amp;f=' . $post['id'] . '&amp;c=' . $var['comment_id'] . '&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status1'] . '" class="ajaxcall approve showhide">' . $lang['comm_show'] . '</a>' : '<a href="./mn-comments.php?a=m&amp;s=3&amp;f=' . $var['post_id'] .'&amp;c=' . $var['comment_id'] .'&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status3'] . '" class="ajaxcall showhide">' . $lang['uni_hide'] . '</a>';
              $profile_link = (isset($var['author_id']) && is_numeric($var['author_id'])) ? ' <span class="info hide"><a href="./mn-comments.php?a=' . $var['author_id'] . '" class="tooltip" title="' . $lang['comm_filter_by_user'] . ' ' . $var['author_name'] . '">&raquo;</a></span>' : '';
              
              $comment_links .= ($var['status'] != 0) ? '<a href="./mn-comments.php?action=reply&amp;post=' . $var['post_id'] . '" class="fancy">' . $lang['comm_reply'] . '</a> | <a href="./mn-comments.php?action=edit&amp;post=' . $var['post_id'] .'&amp;id=' . $var['comment_id'] .'">' . $lang['uni_edit'] . '</a> | ' . $showhide . ' | <a href="./mn-comments.php?a=m&amp;s=0&amp;f=' . $var['post_id'] .'&amp;c=' . $var['comment_id'] .'&amp;t=' . $_SESSION['mn_token'] . '&amp;msg=' . $lang['comm_status0'] . '" class="ajaxcall trash">' . $lang['comm_trash'] . '</a>' : '<a href="./mn-comments.php?a=m&amp;s=1&amp;f=' . $var['post_id'] .'&amp;c=' . $var['comment_id'] .'&amp;t=' . $_SESSION['mn_token'] . '&amp;from=trash&amp;msg=' . $lang['comm_status1'] . '" class="restore ajaxcall">' . $lang['comm_restore'] . '</a> | <a href="./mn-comments.php?a=m&amp;s=d&amp;f=' . $var['post_id'] .'&amp;c=' . $var['comment_id'] .'&amp;t=' . $_SESSION['mn_token'] . '&amp;from=trash" class="delete ajaxcall">' . $lang['comm_delete_permanently'] . '</a>';
              
              if (isset($_GET['q']) && (strlen($_GET['q']) > 2)) {
                $var['comment_text'] = preg_replace('/'. $_GET['q'] .'/is', '<span class="mn-highlight">\\0</span>', $var['comment_text']);
              }
              $comments_result .= '<tr id="c' . $var['comment_id'] . '"><td><a href="./?p=' . $post['id'] . '#c-' . $var['comment_id'] . '">#</a> <strong>' . $var['author_name'] . '</strong>' . $profile_link . '<input type="hidden" name="posts[]" value="' . $var['post_id'] . '" /><input type="checkbox" name="comments[]" value="' . $var['comment_id'] .'" class="checkbox"' . $checked . ' /><br /><span class="trivial">' . date('d.m.Y H:i', $var['timestamp']) . '</span><br />' . $status . '<span class="info hide">IP: <a href="./mn-comments.php?i=' . $var['ip_address'] . '" class="tooltip" title="' . $lang['comm_filter_by_ip_address'] . ' ' . $var['ip_address'] . '">' . $var['ip_address'] . '</a> ' . $ip_info . '</span>&nbsp;</td><td><div class="comment-text">' . comment_format($var['comment_text']) . '</div>&nbsp;<span class="comment-links hide">' . $comment_links . '</span></td><td><a href="./?p=' . $post['id'] . '">' . $post['title'] . '</a></td></tr>';
              $c_count++;
            }
          }

        }

        $posts = mn_natcasesort($posts);
        $posts = array_unique($posts);

        ksort($comments_timestamps);
        $comments_timestamps = array_unique($comments_timestamps);
        $c_authors = array_unique($c_authors);
        $c_ips = array_unique($c_ips);
        $c_ips = mn_natcasesort($c_ips);
      }


      if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['comm_comments'], $lang['comm_msg_comment_added'], 'ok');
      elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['comm_comments'], $lang['comm_msg_comment_deleted'], 'ok');
      elseif (isset($_GET['back']) && $_GET['back'] == 'bulk-deleted') overall_header($lang['comm_comments'], $lang['comm_msg_selected_comments_deleted'], 'ok');
      elseif (isset($_GET['back']) && $_GET['back'] == 'bulk-status1') overall_header($lang['comm_comments'], $lang['comm_msg_bulk_status1'], 'ok');
      elseif (isset($_GET['back']) && $_GET['back'] == 'trash') overall_header($lang['comm_comments'], $lang['comm_msg_moved_to_trash'], 'ok');
      else overall_header($lang['comm_comments'], $lang['comm_comments'], 'main');
      $admin_tmpl['comments_list'] = true;
    }



  }
  



  
 if (isset($admin_tmpl['comment_edit_form']) && $admin_tmpl['comment_edit_form'] && MN_LOGGED) {
?>

  <form action="./mn-comments.php" method="post" id="comment-edit">
    <fieldset>
      <table>
        <tr>
          <td class="labels"><label for="author_name"><img src="./stuff/img/icons/user.png" alt="user" /> <?php echo $lang['comm_author'];?></label></td>
          <td class="inputs"><input type="text" name="author_name" class="text" id="author_name" value="<?php echo check_text($var['author_name']);?>" /></td>
        </tr>
        <tr>
          <td><label for="author_email"><img src="./stuff/img/icons/email.png" alt="e-mail" /> <?php echo $lang['comm_email'];?></label></td>
          <td><input type="text" name="author_email" class="text" id="author_email" value="<?php echo check_text($var['author_email']);?>" /></td>
        </tr>
        <tr>
          <td><label for="author_www"><img src="./stuff/img/icons/www.png" alt="www" /> <?php echo $lang['comm_www'];?></label></td>
          <td><input type="text" name="author_www" class="text" id="author_www" value="<?php echo check_text($var['author_www']);?>" /></td>
        </tr>
        
        <?php
        
        	if (file_exists(MN_ROOT . $file['xfields'])) {
        	
        		$xfields = get_unserialized_array('xfields');
        		foreach ($xfields as $xVar => $x) {
        			if ($x['section'] != 'comments') continue;
        			else {
        			
        				$thisVar = (isset($_POST['x' . $xVar])) ? check_text($_POST['x' . $xVar], true) : @$var['xfields_array'][$xVar];
        			
        				if (isset($x['type']) && $x['type'] == 'select') {
        					$xField = '<select name="x' . $xVar . '" id="x' . $xVar . '" class="mn-xfield-select">';
        					foreach ($x['options'] as $oKey => $oValue) {
        						$sel = ($thisVar == $oKey) ? ' selected="selected"' : '';
        						$xField .= '<option value="' . $oKey . '"' . $sel . '>' . $oValue . '</option>';
        					}
        					$xField .= '</select>';
        				}
        				else {
        					$xField = '<input type="text" name="x' . $xVar . '" id="mn-x' . $xVar . '" value="' . $thisVar . '" class="mn-xfield-input" />';
        				}
        				echo '<tr><td><label for="x' . $xVar . '">' . $x['name'] . ':</label></td><td>' . $xField . '</td></tr>';
        			}
        		}
        		
        		echo '<input type="hidden" name="x_fields" value="true" />';
        	
        	}
        
        ?>
        
      </table>

      <p class="ta-description"><label for="comment_text"><img src="./stuff/img/icons/comment.png" alt="comment" /> <?php echo $lang['comm_text'];?></label></p>
      <?php include './stuff/inc/tmpl/comment-form-buttons.php';?>
      <textarea name="comment_text" id="comment_text" rows="5" cols="40"><?php echo check_text($var['comment_text']);?></textarea>
    </fieldset>

      <ul id="comment-info" class="round hide">
        <?php
          if (!empty($var['ip_address']) && ($var['ip_address'] != '-')) {
            $ip_info = (in_array(trim($var['ip_address']), $banned_ips)) ? ' <img src="./stuff/img/icons/warning.png" alt="" class="tooltip" title="' . $lang['uni_banned_ip'] . '" />' : ' <a href="./mn-tools.php?action=quickban&amp;ip=' . $var['ip_address'] . '" class="fancy"><img src="./stuff/img/icons/ban.png" alt="" class="tooltip" title="' . $lang['uni_ban_ip'] . '" /></a>';
          }
          echo '<li><strong>' . $lang['uni_date'] . ':</strong> ' . date('j.n.Y H:i', ($var['timestamp'])) . '</li>';
          echo '<li><strong>' . $lang['comm_ip_address'] . ':</strong> ' . $var['ip_address'] . $ip_info . '</li>';
          echo '<li><strong>' . $lang['comm_host'] . ':</strong> ' . $var['host'] . '</li>';
          echo '<li><strong>' . $lang['comm_user_agent'] . ':</strong> ' . $var['user_agent'] . '</li>';
        ?>
      </ul>
      <p class="r"><span class="simurl toggle750" rel="comment-info"><img src="./stuff/img/icons/information.png" alt="(i)" /> <?php echo $lang['comm_info'];?></span></p>
      <p class="c">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="post" value="<?php echo $_REQUEST['post'];?>" />
        <input type="hidden" name="id" value="<?php echo $_REQUEST['id'];?>" />
        <button type="submit" name="submit"><img src="./stuff/img/icons/comment-edit.png" alt="edit" /> <?php echo $lang['comm_edit_comment'];?></button>
      </p>
  </form>

<?php
  }




  elseif (isset($admin_tmpl['comment_reply']) && $admin_tmpl['comment_reply'] && MN_LOGGED) {
?>

  <form action="./mn-comments.php" method="post" id="comment-reply">
    <p class="ta-description"><label for="comment_text"><img src="./stuff/img/icons/comment.png" alt="comment" /> <?php echo $lang['comm_text'];?>:</label></p>
    <?php include './stuff/inc/tmpl/comment-form-buttons.php';?>
    <textarea name="c_text" id="comment_text" rows="5" cols="40"></textarea>
    <p class="c">
      <input type="hidden" name="action" value="reply" />
      <input type="hidden" name="post" value="<?php echo $_GET['post'];?>" />
      <button type="submit" name="submit"><img src="./stuff/img/icons/comment-add.png" alt="" /> <?php echo $lang['comm_add_comment'];?></button>
    </p>
  </form>

<?php
  die();
  }
  
  elseif (isset($admin_tmpl['comment_delete']) && $admin_tmpl['comment_delete'] && MN_LOGGED) {
?>

  <form action="./mn-comments.php" method="post" id="comment-delete" class="item-delete">
    <fieldset>
      <?php echo $lang['comm_q_really_delete'];?> <strong><?php echo $var['author_name'];?></strong>?
      <div class="preview round"><?php echo comment_format($var['comment_text']);?></div>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['comment_id'];?>" />
        <input type="hidden" name="post" value="<?php echo $var['post_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['comm_delete_comment'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }




  if (isset($admin_tmpl['comments_list']) && $admin_tmpl['comments_list'] && MN_LOGGED) {
  
    
    echo (isset($c_count) && !empty($c_count)) ? '<span id="c-count">' . $lang['comm_filter_comments_count'] . ': <strong id="comm_count">' . $c_count . '</strong></span>' : '';
    echo '<div class="rel-links">';
    echo (isset($_GET['trash'])) ? '<a href="./mn-comments.php"><img src="./stuff/img/icons/comments.png" alt="" /> ' . $lang['comm_comments'] . '</a>' : '<a href="./mn-comments.php?trash"><img src="./stuff/img/icons/trash.png" alt="" /> ' . $lang['uni_trash'] . ' (<span id="trash_count">' . $c_trash . '</span>)</a>';
    if (!empty($comments_result) || !empty($_GET['d']) || !empty($_GET['p']) || !empty($_GET['a']) || !empty($_GET['i']) || !empty($_GET['s']) || !empty($_GET['q'])) {
      echo (empty($_GET['d']) && empty($_GET['p']) && empty($_GET['a']) && empty($_GET['i']) && empty($_GET['s']) && empty($_GET['q'])) ? ' | <span class="simurl" id="filter-viewer"> <img src="./stuff/img/icons/view-settings.png" alt="" width="16" height="16" /> ' . $lang['comm_filter_settings'] . '</span>' : ' | <a href="./mn-comments.php" class="custom"><img src="./stuff/img/icons/view-settings-cancel.png" alt="" width="16" height="16" /> ' . $lang['comm_filter_cancel'] . '</a>';
    }
    echo '</div>';
      
    $class = (empty($_GET['d']) && empty($_GET['p']) && empty($_GET['a']) && empty($_GET['i']) && empty($_GET['s']) && empty($_GET['q'])) ? ' hide' : '';
    echo '<p class="cleaner">&nbsp;</p><form action="./mn-comments.php" method="get" class="filter' . $class . '">';
    echo '<select name="d">';
    echo '<option value="" class="description">--- ' . $lang['comm_all_dates'] . ' ---</option>';
    foreach ($comments_timestamps as $key => $value) {
      $sel = (isset($_GET['d']) && $value == $_GET['d']) ? ' selected="selected" class="selected"' : '';
      echo '<option value="' . $value . '"' . $sel . '>' . $lang['month'][date('n', $key)] . ' ' . date('Y', $key) . '</option>';
    }
    echo '</select> ';
    
    echo '<select name="p">';
    echo '<option value="" class="description">--- ' . $lang['comm_all_posts'] . ' ---</option>';
    foreach ($posts as $key => $value) {
      $sel = (isset($_GET['p']) && $key == $_GET['p']) ? ' selected="selected" class="selected"' : '';
      echo '<option value="' . $key . '"' . $sel . '>' . $value . '</option>';
    }
    echo '</select> ';
    
    echo '<select name="a">';
    echo '<option value="" class="description">--- ' . $lang['comm_all_authors'] . ' ---</option>';
    $mn_users = load_basic_data('users');
    foreach ($c_authors as $ca_id) {
      $sel = (isset($_GET['a']) && $ca_id == $_GET['a']) ? ' selected="selected" class="selected"' : '';
      echo '<option value="' . $ca_id . '"' . $sel . '>' . $mn_users[$ca_id] . '</option>';
    }
    echo '</select> ';
    
    echo '<select name="i">';
    echo '<option value="" class="description">--- ' . $lang['comm_all_ip_addresses'] . ' ---</option>';
    foreach ($c_ips as $c_ip) {
      $sel = (isset($_GET['i']) && $c_ip == $_GET['i']) ? ' selected="selected" class="selected"' : '';
      echo '<option value="' . $c_ip . '"' . $sel . '>' . $c_ip . '</option>';
    }
    echo '</select> ';
    
    echo '<select name="s">';
    echo '<option value="" class="description">--- ' . $lang['comm_all_statuses'] . ' ---</option>';
      $c_statuses = array(1,2,3,5);
      foreach ($c_statuses as $c_status) {
        $sel = (isset($_GET['s']) && $c_status == $_GET['s']) ? ' selected="selected" class="selected"' : '';
        echo '<option value="'  . $c_status . '"' . $sel . '>' . $lang['comm_filter_status' . $c_status] . '</option>';
      }
    echo '</select> ';
    echo '<input type="submit" class="submit" value="' . $lang['posts_filter'] . '" />';
    echo '<div id="search"><input type="text" name="q" id="q" value="' . htmlspecialchars(@$_GET['q'], ENT_QUOTES) . '" /> <input type="submit" class="submit" value="' . $lang['uni_search'] . '" /></div>';
    echo '</form>';
    
    if (!empty($comments_result)) {
?>


  <form action="./mn-comments.php" method="post">
  
    <table id="comments-list" class="tablesorter">
      <thead>
        <tr>
        <th id="cell-author"><?php echo $lang['comm_author'];?></th>
        <th id="cell-text"><?php echo $lang['comm_text'];?></th>
        <th id="cell-post"><?php echo $lang['comm_post'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php echo $comments_result;?>
      </tbody>
    </table>
  
    <div class="bulk-actions">
      <select name="a">
        <option>--- <?php echo $lang['uni_bulk_actions'];?> ---</option>
        <?php if (isset($_GET['trash'])) { ?>
        <option value="status1"><?php echo $lang['comm_bulk_restore'];?></option>
        <option value="delete"><?php echo $lang['comm_bulk_delete_perm'];?></option>
        <?php } else { ?>
        <option value="status1"><?php echo $lang['comm_bulk_approve'];?></option>
        <option value="status5"><?php echo $lang['comm_bulk_reject'];?></option>
        <option value="status3"><?php echo $lang['comm_bulk_hide'];?></option>
        <option value="status0"><?php echo $lang['comm_bulk_move_to_trash'];?></option>
        <?php } ?>
      </select>
      <input type="hidden" name="action" value="bulk" />
      <input type="submit" class="submit" value="<?php echo $lang['uni_send'];?>" />
    </div>
  </form>
  

  <div id="pager" class="custom-pager pager<?php if (count($comments) <= 10) echo ' hide'; ?>">
    <form action="./mn-comments.php">
      <select class="pagesize fr"><option selected="selected" value="10">10</option><option value="20">20</option><option value="30">30</option><option value="<?php echo count($comments);?>"><?php echo $lang['posts_all_posts'];?></option></select>
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
    elseif (isset($_GET['trash'])) echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['comm_msg_no_comments_trash'] . '</p>';
    elseif (empty($_GET['d']) && empty($_GET['p']) && empty($_GET['s']) && empty($_GET['a']) && empty($_GET['i'])) echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['comm_msg_no_comments'] . '</p>';
    else echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['comm_msg_no_comments_criteria'] . '</p>';
  }


  if (MN_LOGGED) overall_footer();
?>