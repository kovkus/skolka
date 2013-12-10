<?php

  if (isset($_GET['debug'])) error_reporting(E_ALL);
  else error_reporting(0);
  
  if (substr(phpversion(), 0, 1) < 5) {
    header('location: ./');
    exit;
  }
  
  

  session_start();
  
  define('IN_MNews', true);
  define('MN_ROOT', './');
  
  $conf['admin_theme'] = 'bluedee';
  $dirs = array('data', 'data/comments', 'data/databases', 'data/files', 'data/files/avatars', 'data/files/backups', 'data/files/images', 'data/files/images/_thumbs', 'data/files/media', 'data/files/others', 'data/pages', 'data/posts', 'data/templates');

  include_once  './stuff/inc/mn-definitions.php';
  include_once  './stuff/inc/mn-functions.php';
  $lng = select_lang();
  include_once  './stuff/lang/lang_' . $lng .'.php';
  
  
  





  if (!isset($_SESSION['mn_lang']) && !file_exists('./' . $file['users']) && !file_exists('./' . $file['config'])) {
    $admin_tmpl['lang_select'] = true;
    install_header('Install', 'Please select your language', 'main');
  }






  elseif (isset($_GET['step']) && $_GET['step'] == 'done') {
    if (isset($_GET['update'])) install_header($lang['install_msg_update_done'], $lang['install_msg_update_done'], 'ok');
    else install_header($lang['install_msg_install_done'], $lang['install_msg_install_done'], 'ok');
    
    echo '<p class="c">' . $lang['install_text_done'] . '</p>';
  }





  elseif (file_exists('./' . $file['users']) && file_exists('./' . $file['config']) && file_exists('./' . $file['groups'])) {
    header('location: ./install.php?step=done');
    exit;
  }





  elseif (isset($_GET['step']) && $_GET['step'] == 1) {

    foreach ($dir as $single_dir) {
      if (!is_writeable($single_dir)) mn_chmod($single_dir);
    }


    if (chmod_check()) {
        header('location: ./install.php?step=2');
        exit;
    }
    
    else {
      install_header($lang['install_installation'] . ' - ' . $lang['install_msg_chmod'], $lang['install_msg_chmod'], 'info');
      $admin_tmpl['chmod_help'] = true;
    }
      
  }





  elseif (isset($_GET['step']) && $_GET['step'] == 2) {
  
    if (chmod_check()) {


      if (!file_exists(MN_ROOT . $file['config'])) {
        $url_path = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
        $url_path = preg_replace( '/\install\.php/is', '', $url_path);
        if (substr($url_path, -1) == '/') $url_path = substr($url_path, 0, -1);
        
        $antispam_num = rand(1, 20);
        $config_content = "<?php\n\n\t// Administration:\n\t\$conf['admin_url'] = '" . $url_path . "';\n\t\$conf['lang'] = '" . $_SESSION['mn_lang'] . "';\n\t\$conf['admin_theme'] = 'bluedee';\n\t\$conf['admin_wysiwyg'] = true;\n\t\$conf['admin_multiupload'] = true;\n\t\$conf['time_adj'] = '0';\n\t\$conf['admin_icons'] = false;\n\n\t// Website:\n\t\$conf['web_title'] = 'Another MNews website';\n\t\$conf['web_title_header'] = false;\n\t\$conf['web_url'] = '';\n\t\$conf['web_format'] = 'html';\n\t\$conf['web_encoding'] = 'utf-8';\n\t\$conf['web_section_titles'] = true;\n\t\$conf['web_powered_by'] = false;\n\n\t// Posts:\n\t\$conf['web_posts_count'] = 10;\n\t\$conf['web_pagination'] = true;\n\t\$conf['web_counter'] = false;\n\t\$conf['posts_image'] = false;\n\t\$conf['posts_image_size'] = 0;\n\n\t// Comments:\n\t\$conf['comments'] = '1';\n\t\$conf['comments_order'] = 'normal';\n\t\$conf['comments_approval'] = false;\n\t\$conf['comments_antispam'] = " . $antispam_num . ";\n\t\$conf['comments_antiflood'] = 30;\n\t\$conf['comments_captcha'] = false;\n\t\$conf['comments_bb'] = true;\n\t\$conf['comments_bb_buttons'] = '110010';\n\t\$conf['comments_smiles'] = true;\n\t\$conf['comments_links_auto'] = true;\n\t\$conf['comments_links_target'] = false;\n\t\$conf['comments_links_nofollow'] = true;\n\t\$conf['comments_field_email'] = true;\n\t\$conf['comments_field_www'] = true;\n\t\$conf['comments_field_preview'] = false;\n\n\t// Users:\n\t\$conf['users_registration'] = false;\n\t\$conf['users_default_group'] = 5;\n\t\$conf['users_perm_login'] = true;\n\t\$conf['users_avatar_standard'] = " . $default['avatar_size_standard'] . ";\n\t\$conf['users_avatar_small'] = " . $default['avatar_size_small'] . ";\n\t\$conf['users_avatar_mini'] = " . $default['avatar_size_mini'] . ";\n\n?" . ">";
          
        mn_put_contents(MN_ROOT . $file['config'], $config_content);
      }


      if (!file_exists(MN_ROOT . $file['files'])) mn_put_contents(MN_ROOT . $file['files'], SAFETY_LINE . DELIMITER . "1\n");
      
      
      if (!file_exists(MN_ROOT . $file['groups'])) {
        $g_content = SAFETY_LINE . DELIMITER . "6\n";
        for ($i = 1; $i <= 5;  $i++) {
          $g_content .= $i . DELIMITER . $lang['groups_default_group_' . $i] . DELIMITER . friendly_url($lang['groups_default_group_' . $i]) . DELIMITER . $default_permissions[$i] . "\n";
        }
        mn_put_contents(MN_ROOT . $file['groups'], $g_content);
      }
      
      
      if (!file_exists(MN_ROOT . $file['id_comments'])) mn_put_contents(MN_ROOT . $file['id_comments'], '1');
      if (!file_exists(MN_ROOT . $file['id_pages'])) mn_put_contents(MN_ROOT . $file['id_pages'], '1');
      if (!file_exists(MN_ROOT . $file['id_posts'])) mn_put_contents(MN_ROOT . $file['id_posts'], '1');
      
      if (!file_exists(MN_ROOT . $dir['avatars'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['avatars'] . 'index.html', 'MNews FTW! ;-)');
      if (!file_exists(MN_ROOT . $dir['backups'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['backups'] . 'index.html', 'MNews FTW! ;-)');
      if (!file_exists(MN_ROOT . $dir['images'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['images'] . 'index.html', 'MNews FTW! ;-)');
      if (!file_exists(MN_ROOT . $dir['media'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['media'] . 'index.html', 'MNews FTW! ;-)');
      if (!file_exists(MN_ROOT . $dir['others'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['others'] . 'index.html', 'MNews FTW! ;-)');
      if (!file_exists(MN_ROOT . $dir['thumbs'] . 'index.html')) mn_put_contents(MN_ROOT . $dir['thumbs'] . 'index.html', 'MNews FTW! ;-)');

      if (!file_exists(MN_ROOT . $file['templates'])) mn_put_contents(MN_ROOT . $file['templates'], SAFETY_LINE . DELIMITER . "30\n");
      
      
      foreach ($default_template as $tmpl_id => $tmpl_content) {
        if (!file_exists(MN_ROOT . $dir['templates']) . 'mn_default_' . $tmpl_id . '.html') mn_put_contents(MN_ROOT . $dir['templates'] . 'mn_default_' . $tmpl_id . '.html', $tmpl_content);
      }
      
      
      
      if (!file_exists(MN_ROOT . $file['users'])) {
        install_header($lang['install_installation'] . ' - ' . $lang['install_msg_add_user'], $lang['install_msg_add_user'], 'main');
        $admin_tmpl['add_user'] = true;
      }
      else {
        header('location: ./install.php?step=done');
        exit;
      }
      
    }
    
    else {
      header('location: ./install.php?step=1');
      exit;
    }

  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'add_user') {

    if (!file_exists(MN_ROOT . $file['users'])) {
      if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
        if ((mb_strlen($_POST['username']) > 1) && (mb_strlen($_POST['pass1']) > 5)) {
          if (preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['username'])) {
            if (check_email($_POST['email'])) {
              if ($_POST['pass1'] === $_POST['pass2']) {

                $u_content = SAFETY_LINE . DELIMITER . "2\n";
                $u_content .= '1' . DELIMITER . $_POST['username'] . DELIMITER . sha1($_POST['pass1']) . DELIMITER . $_POST['email'] . DELIMITER . '1' . DELIMITER . '1' . DELIMITER . DELIMITER . DELIMITER . DELIMITER . time() . DELIMITER . $_SERVER['REMOTE_ADDR'] . DELIMITER . '0' . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . DELIMITER . "\n";

                if (mn_put_contents($file['users'], $u_content)) {
                  header('location: ./install.php?step=done');
                  exit;
                }
                else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_put_contents_error'], 'error');

              }
              else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_passwords_not_same'], 'error');
            }
            else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_email_check'], 'error');
          }
          else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_forbidden_chars'], 'error');
        }
        else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_values_length'], 'error');
      }
      else install_header($lang['install_installation'] . ' - ' . $lang['users_add_new_user'], $lang['users_msg_empty_values'], 'error');
    }
    else {
      header('location: ./install.php?step=done');
      exit;
    }


    $admin_tmpl['add_user'] = true;
    $var = array(
      'username' => htmlspecialchars($_POST['username']),
      'email' => htmlspecialchars($_POST['email']),
    );

  }
  





  else {

    header('location: ./install.php?step=1');
    exit;

  }
  





  if (isset($admin_tmpl['chmod_help']) && $admin_tmpl['chmod_help']) {
?>

  <div class="content-text j">
    <p><?php echo $lang['install_chmod_text'] . '</p><p>' . $lang['install_chmod_text2'];?></p>
    <ul class="chmod-dirs">
      <?php
        foreach ($dirs as $dir_name => $dir_path) {
          if (!is_writable($dir_path)) echo '<li>/' . $dir_path . '/</li>';
        }
      ?>
    </ul>
    <div class="c"><div class="simbutton"><a class="cc" href="./install.php?<?php if (isset($_GET['update'])) echo 'update&amp;';?>step=2"><img src="./stuff/img/icons/tick.png" alt="" /> <?php echo $lang['uni_continue'];?></a></div></div>

    <p class="r"><strong class="simurl" id="chmod-help-toggle"><img src="./stuff/img/icons/help.png" alt="" /> <?php echo $lang['install_chmod_show_manual'];?></strong></p>
    <ol id="install-list" class="hide">
      <?php
        $i = 1;
        for ($i = 1; $i <= 6; $i++) {
          echo '<li>' . $lang['install_chmod_help_step' . $i] . '</li>';
        }
      ?>
    </ol>
  </div>

<?php
  }
  
  if (isset($admin_tmpl['add_user']) && $admin_tmpl['add_user']) {
?>

  <form action="./install.php" method="post" id="users-add-edit" class="install-form">

    <fieldset>
    <legend><?php echo $lang['users_login_info'];?></legend>

    <table class="user-info">
      <tr>
        <td class="labels"><label for="username"><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" /> <?php echo $lang['users_username'];?></label></td>
        <td class="inputs"><input type="text" name="username" id="username" class="text" value="<?php echo (isset($var['username'])) ? $var['username'] : '';?>" maxlength="30" /></td>
      </tr>
      <tr><td colspan="2" class="help"><?php echo $lang['users_help_username'];?></td></tr>

      <tr>
        <td class="labels"><label for="email"><img src="./stuff/img/icons/email.png" alt="" width="16" height="16" /> <?php echo $lang['users_email'];?></label></td>
        <td class="inputs"><input type="text" name="email" id="email" class="text" value="<?php echo (isset($var['email'])) ? $var['email'] : '';?>" /></td>
      </tr>
      <tr><td colspan="2" class="help"><?php echo $lang['users_help_email'];?></td></tr>

      <tr>
        <td class="labels"><label for="pass1"><img src="./stuff/img/icons/key.png" alt="" width="16" height="16" /> <?php echo $lang['users_password']; if (isset($var['action']) && $var['action'] == 'add') echo ' <span class="required">*</span>';?></label></td>
        <td class="inputs"><input type="password" name="pass1" id="pass1" class="text" value="" /></td>
      </tr>
      <tr><td colspan="2" class="help"><?php echo $lang['users_help_pass1'];?></td></tr>

      <tr>
        <td class="labels"><label for="pass2"><img src="./stuff/img/icons/key-go.png" alt="" width="16" height="16" /> <?php echo $lang['users_password_check']; if (isset($var['action']) && $var['action'] == 'add') echo ' <span class="required">*</span>';?></label></td>
        <td class="inputs"><input type="password" name="pass2" id="pass2" class="text" value="" /></td>
      </tr>
      <tr><td colspan="2" class="help"><?php echo $lang['users_help_pass2'];?></td></tr>
    </table>

    </fieldset>


    <p class="c">
      <input type="hidden" name="action" value="add_user" />
      <button type="submit"><img src="./stuff/img/icons/user-add.png" alt="" /> <?php echo $lang['users_add_user'];?></button>
    </p>
  </form>

<?php
  }

  elseif (isset($admin_tmpl['lang_select']) && $admin_tmpl['lang_select']) {
    echo '<ul id="install-lang">';
    foreach ($languages as $lang_abbr => $lang_name) {
      echo '<li><a href="./mn-login.php?l=' . $lang_abbr . '"><img src="./stuff/lang/lang_' . $lang_abbr . '.gif" alt="' . $lang_abbr . '" /> ' . $lang_name . '</a></li>';
    }
    echo '</ul>';
  }

  overall_footer();
?>