<?php
  include './stuff/inc/mn-start.php';
  $auth = user_auth('14');


  if (isset($_GET['id']) && user_auth('6', true) == '1') {$uid = $_GET['id'];}
  else {$uid = $_SESSION['mn_user_id'];}

  
  if (isset($_POST['action']) && $_POST['action'] == 'doit:)') {
    if (check_email($_POST['email'])) {
      $old = get_values('users', $_SESSION['mn_user_id']);

      // no pass change, no e-mail change
      if (empty($_POST['pass1']) && $old['email'] == $_POST['email']) {
        $put['pass'] = $old['pass'];
        $put['email'] = $old['email'];
        $action['continue'] = true;
      }
      
      // password and also e-mail change
      elseif (!empty($_POST['pass1']) && $old['email'] != $_POST['email'] && ($_POST['pass1'] === $_POST['pass2'])) {
        if ($old['pass'] == sha1($_POST['pass0']) && $_POST['pass1'] === $_POST['pass2']) {
          $put['pass'] = sha1($_POST['pass1']);
          $put['email'] = $_POST['email'];
          $action['continue'] = true;
        }
        else {
          $action['continue'] = false;
          $error_message = $lang['users_msg_wrong_old_password'];
        }
      }
      
      // no password change, but e-mail will change
      elseif (empty($_POST['pass1']) && $old['email'] != $_POST['email']) {
        if ($old['pass'] == sha1($_POST['pass0'])) {
          $put['pass'] = $old['pass'];
          $put['email'] = $_POST['email'];
          $action['continue'] = true;
        }
        else {
          $action['continue'] = false;
          $error_message = $lang['users_msg_wrong_old_password'];
        }
      }
      
      // no e-mail change, but password will change
      elseif (!empty($_POST['pass1']) && $old['email'] == $_POST['email'] && ($_POST['pass1'] === $_POST['pass2'])) {
        if ($old['pass'] == sha1($_POST['pass0'])) {
          $put['pass'] = sha1($_POST['pass1']);
          $put['email'] = $old['email'];
          $action['continue'] = true;
        }
        else {
          $action['continue'] = false;
          $error_message = $lang['users_msg_wrong_old_password'];
        }
      }
      
      // else ...
      else {
        $action['continue'] = false;
        $error_message = $lang['users_msg_passwords_not_same'];
      }
      
      
      if ($action['continue']) {
        
        $put['nickname'] = check_text($_POST['nickname'], true);
        $put['birthdate'] =  (empty($_POST['bday_day']) || empty($_POST['bday_month']) || empty($_POST['bday_year'])) ? '' : check_text($_POST['bday_year'] . '-' . $_POST['bday_month'] . '-' . $_POST['bday_day'], true);
        $put['location'] = check_text($_POST['location'], true);
        $put['www'] = (check_url($_POST['www'])) ? check_url($_POST['www']) : '';
        $put['icq'] = (is_numeric($_POST['icq'])) ? $_POST['icq'] : '';
        $put['msn'] = (check_email($_POST['msn'])) ? $_POST['msn'] : '';
        $put['skype'] = check_text($_POST['skype'], true);
        $put['jabber'] = (check_email($_POST['jabber'])) ? $_POST['jabber'] : '';
        $put['public_email'] = (isset($_POST['public_email'])) ? '1' : '0';
        $put['about'] = check_text($_POST['about'], true);
        
        
        // xFields
        if (file_exists(MN_ROOT . $file['xfields'])) {
        
        	$xfields = get_unserialized_array('xfields');
        	$user_xfields = array();
        	foreach ($xfields as $xVar => $x) {
        		if ($x['section'] != 'users') continue;
        		else {
        			$user_xfields[$xVar] = check_text($_POST['x' . $xVar], true, 'xf');
        		}
        	}
        	
        	$xfields_serialized = serialize($user_xfields);
        
        }
        else $xfields_serialized = '';
        
        
        
        $user_line = $old['user_id'] . DELIMITER . $old['username'] . DELIMITER . $put['pass'] . DELIMITER . $put['email'] . DELIMITER . $old['group'] . DELIMITER . $old['status'] . DELIMITER . $old['key'] . DELIMITER . $old['last_login'] . DELIMITER . $old['last_ip'] . DELIMITER . $old['registered'] . DELIMITER . $old['registered_ip'] . DELIMITER . $put['public_email'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $old['avatar'] . DELIMITER . $put['nickname'] . DELIMITER . check_text($_POST['gender'], true) . DELIMITER . $put['birthdate'] . DELIMITER . $put['location'] . DELIMITER . $put['www'] . DELIMITER . $put['icq'] . DELIMITER . $put['msn'] . DELIMITER . $put['skype'] . DELIMITER . $put['jabber'] . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . $old['other1'] . DELIMITER . $old['other2'] . DELIMITER . trim($put['about']) . "\n";
        
        
        
        $u_file = file($file['users']);
        $u_content = '';

        foreach ($u_file as $u_line) {
          $u_data = explode(DELIMITER, $u_line);
          if ($_SESSION['mn_user_id'] == $u_data[0]) $u_content .= $user_line;
          else $u_content .= $u_line;
        }
        
        if (mn_put_contents($file['users'], $u_content)) {
          if (isset($_POST['redir'])) {
            header('location: ' . $_POST['redir']);
            exit;
          }
          else {
            header('location: ./mn-profile.php?back=edited');
            exit;
          }
        }
        else $error_message = $lang['users_msg_put_contents_error'];
      }
    }
    else $error_message = $lang['users_msg_email_check'];
    
    $var = array(
      'username' => $_SESSION['mn_user_name'],
      'email' => $_POST['email'],
      'public_email' => (isset($_POST['public_email'])) ? '1' : '0',
      'nickname' => check_text($_POST['nickname'], true),
      'gender' => check_text($_POST['gender'], true),
      'bday_day' => check_text($_POST['bday_day'], true),
      'bday_month' => check_text($_POST['bday_month'], true),
      'bday_year' => check_text($_POST['bday_year'], true),
      'location' => check_text($_POST['location'], true),
      'www' => check_text($_POST['www'], true),
      'icq' => check_text($_POST['icq'], true),
      'msn' => check_text($_POST['msn'], true),
      'skype' => check_text($_POST['skype'], true),
      'jabber' => check_text($_POST['jabber'], true),
      'about' => check_text($_POST['about'], true),
    );
    overall_header($lang['users_profile'], $error_message, 'error');
  }





  // ad = avatar delete
  elseif (isset($_GET['dt']) && $_GET['dt'] == $_SESSION['upload_token']) {

    $var = get_values('users', $uid);
    list($avatar_file, $avatar_ext, $avatar_width, $avatar_height) = explode(';', $var['avatar']);
    
    if (file_exists('./' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext)) {
      unlink('./' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext);
      unlink('./' . $dir['avatars'] . $avatar_file . '-small.' . $avatar_ext);
      unlink('./' . $dir['avatars'] . $avatar_file . '-mini.' . $avatar_ext);
    }
    
    $user_line = $var['user_id'] . DELIMITER . $var['username'] . DELIMITER . $var['pass'] . DELIMITER . $var['email'] . DELIMITER . $var['group'] . DELIMITER . $var['status'] . DELIMITER . $var['key'] . DELIMITER . $var['last_login'] . DELIMITER . $var['last_ip'] . DELIMITER . $var['registered'] . DELIMITER . $var['registered_ip'] . DELIMITER . $var['public_email'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $var['nickname'] . DELIMITER . $var['gender'] . DELIMITER . $var['birthdate'] . DELIMITER . $var['location'] . DELIMITER . $var['www'] . DELIMITER . $var['icq'] . DELIMITER . $var['msn'] . DELIMITER . $var['skype'] . DELIMITER . $var['jabber'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . $var['other1'] . DELIMITER . $var['other2'] . DELIMITER . trim($var['about']) . "\n";
    
    $u_file = file($file['users']);
    $u_content = '';

    foreach ($u_file as $u_line) {
      $u_data = explode(DELIMITER, $u_line);
      if ($uid == $u_data[0]) $u_content .= $user_line;
      else $u_content .= $u_line;
    }
    
    mn_put_contents($file['users'], $u_content);

    header('Location: ./mn-profile.php?a&id=' . $uid);
    exit;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'upload') {
  
    if (is_image($_FILES['avatar']['name'])) {
    
      $var = get_values('users', $uid);
      
      $source_file = pathinfo($_FILES['avatar']['name']);
      $avatar_name =  $uid . '-' . strtolower($var['username']);
      $avatar_ext = strtolower($source_file['extension']);
      $avatar_file = './' . $dir['avatars'] . $avatar_name . '-source.' . $avatar_ext;
      
      move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_file);
      list($avatar_width, $avatar_height) = getimagesize($avatar_file);
      
      if (isset($avatar_width) && !empty($avatar_width) && isset($avatar_height) && !empty($avatar_height)) {
      
        if (isset($var['avatar']) && !empty($var['avatar'])) {
          list($old_avatar_file, $old_avatar_ext, $old_avatar_width, $old_avatar_height) = explode(';', $var['avatar']);
          if (file_exists('./' . $dir['avatars'] . $old_avatar_file . '.' . $old_avatar_ext)) {
            unlink('./' . $dir['avatars'] . $old_avatar_file . '.' . $old_avatar_ext);
            unlink('./' . $dir['avatars'] . $old_avatar_file . '-small.' . $old_avatar_ext);
            unlink('./' . $dir['avatars'] . $old_avatar_file . '-mini.' . $old_avatar_ext);
          }
        }
        
        
        $standard_size = (isset($conf['users_avatar_standard']) && is_numeric($conf['users_avatar_standard'])) ? $conf['users_avatar_standard'] : $default['avatar_size_standard'];
        $small_size = (isset($conf['users_avatar_small']) && is_numeric($conf['users_avatar_small'])) ? $conf['users_avatar_small'] : $default['avatar_size_small'];
        $mini_size = (isset($conf['users_avatar_mini']) && is_numeric($conf['users_avatar_mini'])) ? $conf['users_avatar_mini'] : $default['avatar_size_mini'];

        crop_image($avatar_file, $avatar_file);
        resize_img($avatar_file, $standard_size, './' . $dir['avatars'] . $avatar_name . '.' . $avatar_ext);
        resize_img($avatar_file, $small_size,    './' . $dir['avatars'] . $avatar_name . '-small.' . $avatar_ext);
        resize_img($avatar_file, $mini_size,     './' . $dir['avatars'] . $avatar_name . '-mini.' . $avatar_ext);
        unlink($avatar_file);
        
        list($avatar_width, $avatar_height) = getimagesize('./' . $dir['avatars'] . $avatar_name . '.' . $avatar_ext);
        
        
        $avatar_info = $avatar_name . ';' . $avatar_ext . ';' . $avatar_width . ';' . $avatar_height;
        $user_line = $var['user_id'] . DELIMITER . $var['username'] . DELIMITER . $var['pass'] . DELIMITER . $var['email'] . DELIMITER . $var['group'] . DELIMITER . $var['status'] . DELIMITER . $var['key'] . DELIMITER . $var['last_login'] . DELIMITER . $var['last_ip'] . DELIMITER . $var['registered'] . DELIMITER . $var['registered_ip'] . DELIMITER . $var['public_email'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $avatar_info . DELIMITER . $var['nickname'] . DELIMITER . $var['gender'] . DELIMITER . $var['birthdate'] . DELIMITER . $var['location'] . DELIMITER . $var['www'] . DELIMITER . $var['icq'] . DELIMITER . $var['msn'] . DELIMITER . $var['skype'] . DELIMITER . $var['jabber'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . $var['other1'] . DELIMITER . $var['other2'] . DELIMITER . trim($var['about']) . "\n";
        $u_file = file($file['users']);
        $u_content = '';
        foreach ($u_file as $u_line) {
          $u_data = explode(DELIMITER, $u_line);
          if ($uid == $u_data[0]) $u_content .= $user_line;
          else $u_content .= $u_line;
        }
        mn_put_contents($file['users'], $u_content);

        header('location: ./mn-profile.php?a&id=' . $uid);
        exit;

      }

      else {
        unlink($avatar_file);
        header('location: ./mn-profile.php?a&id=' . $uid);
        exit;
      }
    
    }
    
    else {
      header('location: ./mn-profile.php?a&id=' . $uid);
      exit;
    }
  
  }





  // upload avatar form
  elseif (isset($_GET['a'])) {

    $_SESSION['upload_token'] = md5(uniqid(mt_rand(), true));

    $var = get_values('users', $uid);

    if (isset($var['avatar']) && !empty($var['avatar'])) {
      list($avatar_file, $avatar_ext, $avatar_width, $avatar_height) = explode(';', $var['avatar']);
      
      if (file_exists('./' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext)) {
        $user_avatar = '<img src="./' . $dir['avatars'] . $avatar_file . '.' . $avatar_ext . '?' . time() . '" alt="avatar" width="' . $avatar_width . '" height="' . $avatar_height . '">';
        $a_del = true;
      }
      else {
        $user_avatar = '<img src="./stuff/img/default-avatar.jpg" alt="avatar" width="100" height="100">';
        $a_del = false;
      }
    }
    else {
      $user_avatar = '<img src="./stuff/img/default-avatar.jpg" alt="avatar" width="100" height="100">';
      $a_del = false;
    }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="./stuff/etc/style.php" media="all">
    <title><?php echo $lang['users_avatar'];?></title>
  </head>
  <body id="avatar">
    <form action="./mn-profile.php<?php echo (isset($_GET['id'])) ? '?id=' . $_GET['id'] : '';?>" method="post" enctype="multipart/form-data">
      <?php echo $user_avatar;?>
      <div id="avatar-controls">
        <span class="simurl" onclick="document.getElementById('avatar-info').style.display = 'block'; document.getElementById('avatar-controls').style.display = 'none'; return false;"><?php echo $lang['users_change_image'];?></span>
        <?php
          if ($a_del) {
            $part_url = (isset($_GET['id'])) ? '&amp;id=' . $_GET['id'] : '';
            echo '<br><a href="./mn-profile.php?dt=' . $_SESSION['upload_token'] . $part_url . '">' . $lang['users_delete_image'] . '</a>';
          }
        ?>
      </div>
      <div id="avatar-info" class="hide">
        <input type="file" name="avatar" id="avatar" size="15"><br>
        <input type="hidden" name="action" value="upload">
        <input type="hidden" name="upload_token" value="<?php echo $_SESSION['upload_token'];?>">
        <input type="submit" class="submit" value="<?php echo $lang['uni_upload'];?>">
        <span class="help"><?php echo $lang['users_avatar_help'];?>
        <span class="simurl" onclick="document.getElementById('avatar-info').style.display = 'none'; document.getElementById('avatar-controls').style.display = 'block'; return false;">(<?php echo mb_strtolower($lang['uni_cancel'], 'UTF-8');?>)</span></span>
      </div>
    </form>
  </body>
</html>

<?php

    die();
  }





  else {
    $var = get_values('users', $_SESSION['mn_user_id']);
    $bday = explode('-', $var['birthdate']);
    $var['bday_day'] = @$bday[2];
    $var['bday_month'] = @$bday[1];
    $var['bday_year'] = @$bday[0];

    if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['users_profile'], $lang['users_msg_profile_edited'], 'ok');
    else overall_header($lang['users_profile'], $lang['users_profile'] . ': ' . $_SESSION['mn_user_name'], 'main');
  }
?>

  <form action="./mn-profile.php" method="post" id="profile-edit" class="profile-form">
  
    <fieldset>
    <legend><?php echo $lang['users_login_info'];?></legend>
    
    <table class="user-info">
      <tr>
        <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" /> <?php echo $lang['users_username'];?></span></td>
        <td class="inputs"><input type="text" name="username" id="username" class="text disabled" value="<?php echo $var['username'];?>" maxlength="30" disabled /></td>
      </tr>

      <tr>
        <td class="labels"><label for="email"><img src="./stuff/img/icons/email.png" alt="" width="16" height="16" /> <?php echo $lang['users_email'];?></label></td>
        <td class="inputs"><input type="text" name="email" id="email" class="text" value="<?php echo $var['email'];?>" />&nbsp<input type="checkbox" name="public_email"<?php echo (isset($var['public_email']) && $var['public_email'] == 1) ? ' checked="checked"' : '';?> class="tooltip" title="<?php echo $lang['users_public_email_help'];?>" /></td>
      </tr>

      <tr>
        <td class="labels"><label for="pass1"><img src="./stuff/img/icons/key.png" alt="" width="16" height="16" /> <?php echo $lang['users_password_new'];?></label></td>
        <td class="inputs"><input type="password" name="pass1" id="pass1" class="text" autocomplete="off" value="" /></td>
      </tr>

      <tr id="tr-pass2"<?php echo (isset($_POST['pass1']) && !empty($_POST['pass1'])) ? '' : ' class="hide"';?>>
        <td class="labels"><label for="pass2"><img src="./stuff/img/icons/key-go.png" alt="" width="16" height="16" /> <?php echo $lang['users_password_check'];?></label></td>
        <td class="inputs"><input type="password" name="pass2" id="pass2" class="text" autocomplete="off" value="" /></td>
      </tr>

    </table>


    <div class="l round<?php echo (isset($error_message) && ($error_message == $lang['users_msg_wrong_old_password'])) ? '' : ' hide';?>" id="profile-old-pass">
      <label for="pass0"><img src="./stuff/img/icons/key-gray.png" alt="" width="16" height="16" /> <?php echo $lang['users_password_old'];?>:</label>
      <input type="password" name="pass0" id="pass0" class="text" autocomplete="off" value="" /><br />
      <span class="help"><?php echo $lang['users_profile_edit_help'];?></span>
    </div>


    </fieldset>
    

    
    <?php include './stuff/inc/tmpl/users-profile-info.php';?>
    
    <p class="c">
      <input type="hidden" name="action" value="doit:)" />
      <button type="submit"><img src="./stuff/img/icons/user-edit.png" alt="" /> <?php echo $lang['users_edit_profile'];?></button>
    </p>
  </form>

<?php
  overall_footer();
?>