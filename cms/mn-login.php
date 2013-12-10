<?php

  if (substr(phpversion(), 0, 1) < 5) {
    header('location: ./');
    exit;
  }
  if (isset($_GET['debug'])) error_reporting(E_ALL);
  else error_reporting(0);
  
  
  
  define('MN_ROOT', './');
  session_start();
  
  include './stuff/inc/mn-definitions.php';
  @include './data/databases/config.php';
  include './stuff/inc/mn-functions.php';
  $lng = select_lang();
  include './stuff/lang/lang_' . $lng . '.php';
  
  if (file_exists($file['banned_ips'])) include_once $file['banned_ips'];
  else $banned_ips = array();





  if (isset($_GET['l'])) {
    if (!empty($languages[$_GET['l']])) {
      $_SESSION['mn_lang'] = $_GET['l'];
      setcookie('mn_lang', $_GET['l'], time()+60*60*24*31, '/', $_SERVER['SERVER_NAME']);
    }
    else {
      $_SESSION['mn_lang'] = DEFAULT_LANG;
      setcookie('mn_lang', DEFAULT_LANG, time()+60*60*24*31, '/', $_SERVER['SERVER_NAME']);
    }

    if (isset($_SERVER['HTTP_REFERER']) && !isset($_GET['mn'])) $redirect = $_SERVER['HTTP_REFERER'];
    elseif (file_exists('./install.php')) $redirect = './install.php';
    else $redirect = './';
    
    header ('location: ' . $redirect);
    exit;
  }


  
  
  
  elseif (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    $url_data = explode('/', $conf['admin_url']);
    setcookie('mn_user_hash', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
    setcookie('mn_logged', '', time()-3600, '/', $_SERVER['SERVER_NAME']);

    if (isset($_GET['redir']) && $_GET['redir'] == 'referer') {
      setcookie('mn_user_name', '', time()-3600, '/', $_SERVER['SERVER_NAME']);
      header('location: ' . $_SERVER['HTTP_REFERER'] . '#mn-comment-form');
      exit;
    }
    else {
      header('location: ./mn-login.php?back=loggedout');
      exit;
    }

  }





  elseif (isset($_GET['install-file'])) {
    if (!file_exists('./install.php')) {
      header('location: ./');
      exit;
    }
    else {
      $var['hide_form'] = true;
      login_screen($lang['login_login'], $lang['login_msg_install_file'], 'warning');
    }
  }





  elseif (file_exists('./install.php')) {
    if (file_exists(MN_ROOT . $file['users'])) {
      header('location: ./mn-login.php?install-file');
      exit;
    }
    else {
      header('location: ./install.php');
      exit;
    }
  }





  elseif (isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) {
    header('location: ./');
    exit;
  }





  elseif (check_ip_ban($_SERVER['REMOTE_ADDR'], $banned_ips)) {
    $var['hide_form'] = true;
    login_screen($lang['login_login'], $lang['login_msg_blocked_ip'], 'warning');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'lost-pass') {
    login_screen($lang['login_send_new_pass'], $lang['login_send_new_pass'], 'main');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'register' && ($conf['users_registration'])) {
    login_screen($lang['login_registration'], $lang['login_registration'], 'main');
  }





  elseif (isset($_GET['back']) && $_GET['back'] == 'regdone' && ($conf['users_registration'])) {
    login_screen($lang['login_registration'], $lang['login_msg_regdone'], 'ok');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'register' && ($conf['users_registration'])) {
    if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
      if ($_POST['robot'] === trim($conf['comments_antispam'])) {
        if ((mb_strlen($_POST['username']) > 1) && (mb_strlen($_POST['pass1']) > 5)) {
          if (preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['username'])) {
            if (stripos($_POST['username'], 'admin') === false) {
              if (check_email($_POST['email'])) {
                if ($_POST['pass1'] === $_POST['pass2']) {

                  $u_file = file($file['users']);
                  $u_lines = ''; $action['add_user'] = true;

                  foreach ($u_file as $single_line) {
                    $u_data = explode(DELIMITER, $single_line);

                    if (substr($u_data[0], 0, 2) == '<?') $u_id = trim($u_data[1]);
                    elseif (trim(strtolower($_POST['username'])) == trim(strtolower($u_data[1])) || (trim(strtolower($_POST['email'])) == trim(strtolower($u_data[3])))) $action['add_user'] = false;
                    else $u_lines .= $single_line;

                  }

                  if ($action['add_user'] === true) {
                    $u_content = SAFETY_LINE . DELIMITER . ($u_id + 1) . "\n";
                    $u_content .= $u_lines;
                    $u_content .= $u_id . DELIMITER . $_POST['username'] . DELIMITER . sha1($_POST['pass1']) . DELIMITER . $_POST['email'] . DELIMITER . $conf['users_default_group'] . DELIMITER . '1' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . mn_time() . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . '0' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . "\n";

                    if (mn_put_contents($file['users'], $u_content)) {
                      if (isset($_POST['redir'])) {
                        header('location: ' . $_POST['redir']);
                        exit;
                      }
                      else {
                        header('location: ./mn-login.php?back=regdone');
                        exit;
                      }
                    }
                    else $error_msg = $lang['users_msg_put_contents_error'];

                  }
                  else $error_msg = $lang['users_msg_already_exists_short'];

                }
                else $error_msg = $lang['users_msg_passwords_not_same'];
              }
              else $error_msg = $lang['users_msg_email_check'];
            }
            else $error_msg = $lang['users_msg_forbidden_string'];
          }
          else $error_msg = $lang['users_msg_forbidden_chars'];
        }
        else $error_msg = $lang['users_msg_values_length_short'];
      }
      else $error_msg = $lang['users_msg_wrong_antispam_num'];
    }
    else $error_msg = $lang['users_msg_empty_values'];
    
    $var = array(
      'username' => $_POST['username'],
      'email' => $_POST['email']
    );
    login_screen($lang['login_registration'], $error_msg, 'error');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'login') {
    if (preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['user_login']) && !empty($_POST['user_login']) && !empty($_POST['user_pass'])) {


      do_login($_POST['user_login'], $_POST['user_pass'], $_POST['perm_login']);
      
      if ($_SESSION['mn_logged']) {
        if (isset($_POST['redir'])) {
          header('location: ' . $_POST['redir']);
          exit;
        }
        else {
          header('location: ./');
          exit;
        }
      }
      elseif (isset($_SESSION['login_error'])) login_screen($lang['login_login'], $lang['login_msg_status_' . $_SESSION['login_error']], 'warning');
      else login_screen($lang['login_login'], $lang['login_msg_login_error'], 'error');

    }
    else login_screen($lang['login_login'], $lang['login_msg_login_error'], 'error');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'lost-pass') {

    $u_file = file($file['users']);
    $u_lines = ''; $m_name = ''; $m_email = ''; $m_pass = '';
    $continue = false;



    foreach ($u_file as $single_line) {

      $u_data = explode(DELIMITER, $single_line);

      if ($_POST['user_login'] == $u_data[1] && $_POST['user_mail'] == $u_data[3]) {
        if ($u_data[5] == '1') {
          $continue = true;
          $m_name = $u_data[1];
          $m_email = $u_data[3];
          $m_pass = PasswordGenerator(7) . rand(1, 99);

          $u_lines .= $u_data[0] . DELIMITER . $u_data[1] . DELIMITER . sha1($m_pass) . DELIMITER . $u_data[3] . DELIMITER . $u_data[4] . DELIMITER . $u_data[5] . DELIMITER . $u_data[6] . DELIMITER . $u_data[7] . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . $u_data[9] . DELIMITER . $u_data[10] . DELIMITER . $u_data[11] . DELIMITER . $u_data[12] . DELIMITER . $u_data[13] . DELIMITER . $u_data[14] . DELIMITER . $u_data[15] . DELIMITER . $u_data[16] . DELIMITER . $u_data[17] . DELIMITER . $u_data[18] . DELIMITER . $u_data[19] . DELIMITER . $u_data[20] . DELIMITER . $u_data[21] . DELIMITER . $u_data[22] . DELIMITER . $u_data[23] . DELIMITER . $u_data[24] . DELIMITER . $u_data[25] . DELIMITER . $u_data[26] . DELIMITER . $u_data[27] . DELIMITER . trim($u_data[28]) . DELIMITER . $u_data[29] . DELIMITER . $u_data[30] . DELIMITER . $u_data[31] . DELIMITER . $u_data[32] . DELIMITER . trim($u_data[33]) . "\n";
        }
        else {
          $continue = false;
          $status_error = $u_data[5];
        }
      }
      else $u_lines .= $single_line;
    }



    if ( $continue ) {
    
      if(@mail($m_email, $lang['login_lost_pass_mail_subject'], str_replace('%link%', $conf['admin_url'] . '/', $lang['login_lost_pass_mail_text']) . ' ' . $m_pass, "From: robot@mnewscms.com") && mn_put_contents($file['users'], $u_lines)) {
         header('location: ./mn-login.php?back=pass-sent');
         exit;
      }

      else {
        login_screen($lang['login_msg_pass_not_sent'], $lang['login_msg_pass_not_sent'], 'error');
      }
      
    }

    else {
      login_screen($lang['login_lost_pass_wrong_values'], $lang['login_lost_pass_wrong_values'], 'error');
    }
  }





  elseif (isset($_GET['back']) && $_GET['back'] == 'loggedout') {
    login_screen($lang['login_login'], $lang['login_msg_logged_out'], 'info');
  }
  elseif (isset($_GET['back']) && $_GET['back'] == 'auto-loggedout') {
    login_screen($lang['login_login'], $lang['login_msg_auto_logged_out'], 'info');
  }
  elseif (isset($_GET['back']) && $_GET['back'] == 'pass-sent') {
    login_screen($lang['login_msg_pass_sent'], $lang['login_msg_pass_sent'], 'ok');
  }





  else {
    login_screen($lang['login_login']);
  }

?>