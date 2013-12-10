<?php
  include './stuff/inc/mn-start.php';
  
  $auth = user_auth('6');
  $admin_tmpl['form_users'] = true;




  
  if (isset($_GET['action']) && $_GET['action'] == 'add') {
    overall_header($lang['users_add_new_user'], $lang['users_add_new_user'], 'main');
  }
  
  
  
  
  
  elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
    if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
      if ((mb_strlen($_POST['username']) > 3) && (mb_strlen($_POST['pass1']) > 5)) {
        if (preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['username'])) {
          if (check_email($_POST['email'])) {
            if ($_POST['pass1'] === $_POST['pass2']) {
            
              $users_file = file($file['users']);
              $u_lines = ''; $add_user = true;
              
              foreach ($users_file as $single_line) {
                $u_data = explode(DELIMITER, $single_line);
                
                if (substr($u_data[0], 0, 2) == '<?') $u_id = trim($u_data[1]);
                elseif (trim(strtolower($_POST['username'])) == trim(strtolower($u_data[1])) || (trim(strtolower($_POST['email'])) == trim(strtolower($u_data[3])))) $add_user = false;
                else $u_lines .= $single_line;

              }
              
              if ($add_user === true) {
                $u_content = SAFETY_LINE . DELIMITER . ($u_id + 1) . "\n";
                $u_content .= $u_lines;
                $u_content .= $u_id . DELIMITER . $_POST['username'] . DELIMITER . sha1($_POST['pass1']) . DELIMITER . $_POST['email'] . DELIMITER . $_POST['group'] . DELIMITER . '1' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . mn_time() . DELIMITER . '-' . DELIMITER . '0' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . "\n";
                
                if (mn_put_contents($file['users'], $u_content)) {
                  header('location: ./mn-users.php?back=added');
                  exit;
                }
                else overall_header($lang['users_add_new_user'], $lang['users_msg_put_contents_error'], 'error');
                
              }
              else overall_header($lang['users_add_new_user'], $lang['users_msg_already_exists'], 'error');
            
            }
            else overall_header($lang['users_add_new_user'], $lang['users_msg_passwords_not_same'], 'error');
          }
          else overall_header($lang['users_add_new_user'], $lang['users_msg_email_check'], 'error');
        }
        else overall_header($lang['users_add_new_user'], $lang['users_msg_forbidden_chars'], 'error');
      }
      else overall_header($lang['users_add_new_user'], $lang['users_msg_values_length'], 'error');
    }
    else overall_header($lang['users_add_new_user'], $lang['users_msg_empty_values'], 'error');
    
    $var = array(
      'username' => $_POST['username'],
      'email' => $_POST['email'],
      'group' => $_POST['group']
    );
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && !empty($_GET['id'])) {
    if ($_GET['id'] == '1' && $_SESSION['mn_user_id'] != '1') {
      header('location: ./?access-denied');
      exit;
    }
    $var = get_values('users', $_GET['id']);
    $bday = explode('-', $var['birthdate']);
    $var['bday_day'] = @$bday[2];
    $var['bday_month'] = @$bday[1];
    $var['bday_year'] = @$bday[0];
    $var['last_login'] = $var['last_login'] + ($conf['time_adj'] * 3600);
    $var['registered'] = $var['registered'] + ($conf['time_adj'] * 3600);
    if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['users_edit_user'] . ' &raquo; ' . $var['username'], $lang['users_msg_user_edited'], 'ok');
    else overall_header($lang['users_edit_user'] . ' &raquo; ' . $var['username'], $lang['users_edit_user'], 'main');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && !empty($_POST['id'])) {
    if (!empty($_POST['username']) && !empty($_POST['email'])) {
      if (mb_strlen($_POST['username']) > 3) {
        if (preg_match('/^[_ a-zA-Z0-9\.\-]+$/', $_POST['username'])) {
          if (check_email($_POST['email'])) {
          
          
            if ($_POST['id'] == '1' && $_SESSION['mn_user_id'] != '1') {
              header('location: ./?access-denied');
              exit;
            }


            $old = get_values('users', $_POST['id']);


            if (empty($_POST['pass1'])) {$put['pass'] = $old['pass']; $action_continue = true;}
            elseif (!empty($_POST['pass1']) && ($_POST['pass1'] === $_POST['pass2'])) {$put['pass'] = sha1($_POST['pass1']); $action_continue = true;}
            else {$action_continue = false;}
            
            
            if ($action_continue) {

              $put['nickname'] = check_text($_POST['nickname'], true);
              $put['birthdate'] =  (empty($_POST['bday_day']) || empty($_POST['bday_month']) || empty($_POST['bday_year'])) ? '' : check_text($_POST['bday_year'] . '-' . $_POST['bday_month'] . '-' . $_POST['bday_day'], true);
              $put['location'] = check_text($_POST['location'], true);
              $put['www'] = (check_url($_POST['www'])) ? check_url($_POST['www']) : '';
              $put['icq'] = (is_numeric($_POST['icq'])) ? $_POST['icq'] : '';
              $put['msn'] = (check_email($_POST['msn'])) ? check_email($_POST['msn']) : '';
              $put['skype'] = check_text($_POST['skype'], true);
              $put['jabber'] = (check_email($_POST['jabber'])) ? $_POST['jabber'] : '';
              
              $put['avatar'] = $old['avatar'];
              $put['status'] = ($_POST['id'] == '1') ? '1' : check_text($_POST['status'], true);
              $put['group'] = ($_POST['id'] == '1') ? '1' : check_text($_POST['group'], true);
              
              $put['about'] = check_text($_POST['about'], true);
              
              $user_line = $_POST['id'] . DELIMITER . $_POST['username'] . DELIMITER . $put['pass'] . DELIMITER . $_POST['email'] . DELIMITER . $put['group'] . DELIMITER . $put['status'] . DELIMITER . $old['key'] . DELIMITER . $old['last_login'] . DELIMITER . $old['last_ip'] . DELIMITER . $old['registered'] . DELIMITER . $old['registered_ip'] . DELIMITER . $old['public_email'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . '' . DELIMITER . $put['avatar'] . DELIMITER . $put['nickname'] . DELIMITER . $_POST['gender'] . DELIMITER . $put['birthdate'] . DELIMITER . $put['location'] . DELIMITER . $put['www'] . DELIMITER . $put['icq'] . DELIMITER . $put['msn'] . DELIMITER . $put['skype'] . DELIMITER . $put['jabber'] . DELIMITER . '' . DELIMITER . $old['xfields'] . DELIMITER . $old['other1'] . DELIMITER . $old['other2'] . DELIMITER . trim($put['about']) . "\n";


              $u_file = file($file['users']);
              $u_content = ''; $action_edit_user = true;

              foreach ($u_file as $u_line) {
                $u_data = explode(DELIMITER, $u_line);

                if (!isset($u_data[2]) && !isset($u_data[3])) $auto_increment_id = trim($u_data[1]);
                elseif ((trim(strtolower($_POST['username'])) == trim(strtolower($u_data[1])) || (trim(strtolower($_POST['email'])) == trim(strtolower($u_data[3])))) && ($_POST['id'] != $u_data[0])) $action_edit_user = false;
                elseif ($_POST['id'] == $u_data[0]) $u_content .= $user_line;
                else $u_content .= $u_line;

              }

              $u_content = SAFETY_LINE . DELIMITER . $auto_increment_id . "\n" . $u_content;


              if ($action_edit_user) {
                if (mn_put_contents($file['users'], $u_content)) {
                  header('location: ./mn-users.php?action=edit&id=' . $_POST['id'] . '&back=edited');
                  exit;
                }
                else $error_message = $lang['users_msg_put_contents_error'];

              }
              else $error_message = $lang['users_msg_already_exists'];
            }
            else $error_message = $lang['users_msg_passwords_not_same'];

          }
          else $error_message = $lang['users_msg_email_check'];
        }
        else $error_message = $lang['users_msg_forbidden_chars'];
      }
      else $error_message = $lang['users_msg_values_length'];
    }
    else $error_message = $lang['users_msg_empty_values'];

    $var = array(
      'user_id' => $_POST['id'],
      'username' => $_POST['username'],
      'email' => $_POST['email'],
      'group' => $_POST['group'],
      'status' => $_POST['status'],
      'nickname' => $_POST['nickname'],
      'gender' => $_POST['gender'],
      'bday_day' => $_POST['bday_day'],
      'bday_month' => $_POST['bday_month'],
      'bday_year' => $_POST['bday_year'],
      'location' => $_POST['location'],
      'www' => $_POST['www'],
      'icq' => $_POST['icq'],
      'msn' => $_POST['msn'],
      'skype' => $_POST['skype'],
      'jabber' => $_POST['jabber'],
      'about' => check_text($_POST['about']),
    );
    overall_header($lang['users_add_new_user'] . ' &raquo; ' . $var['username'], $error_message, 'error');

  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && $_GET['id'] != 1) {
    $var = get_values('users', $_GET['id']);
    $posts_count = get_posts_count('users');
    $admin_tmpl['user_delete'] = true; $admin_tmpl['form_users'] = false;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && $_POST['id'] != 1) {

    $u_file = file($file['users']);
    $u_content = '';

    foreach ($u_file as $u_line) {
      $u_data = explode(DELIMITER, $u_line);
      if ($u_data[0] == $_POST['id']) continue;
      else $u_content .= $u_line;
    }

    if (mn_put_contents($file['users'], $u_content)) {
      header('location: ./mn-users.php?back=deleted');
      exit;
    }
    else overal_header($lang['cats_categories'], $lang['users_msg_put_contents_error'], 'error');

  }





  else {
    $u_file = file($file['users']);
    array_shift($u_file);
    $users_result = ''; $users = array();
    $groups = load_basic_data('groups');
    $posts_count = get_posts_count('users');
    
    foreach ($u_file as $single_line) {
      $temp_data = explode(DELIMITER, $single_line);
      $users[$temp_data[0]] = $temp_data[1] . DELIMITER . $temp_data[3] . DELIMITER . $temp_data[4];
    }
    
    $users = mn_natcasesort($users);
    
    foreach ($users as $user_id => $temp_data) {
      $u_data = explode(DELIMITER, $temp_data);
      if (user_auth('1', true)) $user_posts_count = (!isset($posts_count[$user_id]) || empty($posts_count[$user_id])) ? '<span class="trivial">0</span>' : '<a href="./mn-posts.php?a=' . $user_id . '">' . $posts_count[$user_id] . '</a>';
      else $user_posts_count = (!isset($posts_count[$user_id]) || empty($posts_count[$user_id])) ? '0' : '' . $posts_count[$user_id] . '';
      $delete_link = ($user_id == 1 || $user_id == $_SESSION['mn_user_id']) ? '' : ' | <a href="./mn-users.php?action=delete&amp;id=' . $user_id . '" class="fancy">' . $lang['uni_delete'] . '</a>';
      if (count($users) > 2) $star = ($user_id == $_SESSION['mn_user_id']) ? ' <img src="./stuff/img/icons/star.png" alt="" />' : '';
      else $star = '';
      $users_result .= '<tr><td><a href="./mn-users.php?action=edit&amp;id=' . $user_id . '" class="main-link">' . $u_data[0] . '</a>' . $star . '<br />&nbsp;<span class="links hide"><a href="./mn-users.php?action=edit&amp;id=' . $user_id . '">' . $lang['uni_edit'] . '</a>' . $delete_link . '</span></td><td>' . $u_data[1] . '</td><td>' . $groups[$u_data[2]] . '</td><td class="c cell-posts">' . $user_posts_count . '</td></tr>';
    }
    
    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['users_users'], $lang['users_msg_user_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['users_users'], $lang['users_msg_user_deleted'], 'ok');
    else overall_header($lang['users_users'], $lang['users_users'], 'main');
    $admin_tmpl['form_users'] = false;
  }




  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') ? 'add' : 'edit';

  if (isset($admin_tmpl['form_users']) && $admin_tmpl['form_users']) {
?>

  <form action="./mn-users.php" method="post" id="users-add-edit">
  
    <fieldset>
    <legend><?php echo $lang['users_login_info'];?></legend>
    
    <table class="user-info">
      <tr>
        <td class="labels"><label for="username"><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" /> <?php echo $lang['users_username'];?> <span class="required">*</span></label></td>
        <td class="inputs"><input type="text" name="username" id="username" class="text" value="<?php echo isset($var['username']) ? $var['username'] : '';?>" maxlength="30" /></td>
      </tr>

      <tr>
        <td class="labels"><label for="email"><img src="./stuff/img/icons/email.png" alt="" width="16" height="16" /> <?php echo $lang['users_email'];?> <span class="required">*</span></label></td>
        <td class="inputs"><input type="text" name="email" id="email" class="text" value="<?php echo isset($var['email']) ? $var['email'] : '';?>" /></td>
      </tr>

      <tr>
        <td class="labels"><label for="group"><img src="./stuff/img/icons/group.png" alt="" width="16" height="16" /> <?php echo $lang['users_group'];?> <span class="required">*</span></label></td>
        <td class="inputs"><select name="group" id="group" class="custom long">
          <?php
            $groups = load_basic_data('groups');
            foreach ($groups as $group_id => $group_name) {
              $sel = (isset($var['group']) && $group_id == $var['group']) ? ' selected="selected"' : '';
              echo '<option value="' . $group_id . '"' . $sel . '>' . $group_name . '</option>';
            }
          ?>
        </td>
      </tr>

      <tr>
        <td class="labels"><label for="pass1"><img src="./stuff/img/icons/key.png" alt="" width="16" height="16" /> <?php echo $lang['users_password']; if ($var['action'] == 'add') echo ' <span class="required">*</span>';?></label></td>
        <td class="inputs"><input type="password" name="pass1" id="pass1" class="text" autocomplete="off" value="" /></td>
      </tr>

      <tr>
        <td class="labels"><label for="pass2"><img src="./stuff/img/icons/key-go.png" alt="" width="16" height="16" /> <?php echo $lang['users_password_check']; if ($var['action'] == 'add') echo ' <span class="required">*</span>';?></label></td>
        <td class="inputs"><input type="password" name="pass2" id="pass2" class="text" autocomplete="off" value="" /></td>
      </tr>
    </table>
    
    </fieldset>


    <?php include './stuff/inc/tmpl/users-profile-info.php';?>
    
    <fieldset id="admin-info" class="hide">
      <legend><?php echo $lang['users_admin_info'];?></legend>
      
      <table class="user-info">
        <tr>
          <td class="labels"><label for="status"><img src="./stuff/img/icons/status.png" alt="" width="16" height="16" /> <?php echo $lang['users_status'];?></label></td>
          <td class="inputs">
            <select name="status">
              <option value="1"<?php if ($var['status'] == '1') echo ' selected="selected"';?>><?php echo $lang['users_status_1'];?></option>
              <option value="0"<?php if ($var['status'] == '0') echo ' selected="selected"';?>><?php echo $lang['users_status_0'];?></option>
              <?php if ($var['status'] == '2') echo '<option value="1" selected="selected">' . $lang['users_status_2'] . '</option>';?>
            </select>
          </td>
        </tr>
        <tr>
          <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/date-go.png" alt="" width="16" height="16" /> <?php echo $lang['users_last_login'];?></span></td>
          <td class="info-text"><?php echo (!empty($var['last_login'])) ? date('d.m.Y H:i', $var['last_login']) : $lang['users_last_login_never'];?></td>
        </tr>
        <tr>
          <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/ip-address.png" alt="" width="16" height="16" /> <?php echo $lang['users_last_ip_address'];?></span></td>
          <td class="info-text">
          <?php
            echo (!empty($var['last_ip'])) ? $var['last_ip'] : '-';
            if (!empty($var['last_ip']) && ($var['last_ip'] != '-')) {
              echo (in_array(trim($var['last_ip']), $banned_ips)) ? ' <img src="./stuff/img/icons/warning.png" alt="" class="tooltip" title="' . $lang['uni_banned_ip'] . '" />' : ' <a href="./mn-tools.php?action=quickban&amp;ip=' . $var['last_ip'] . '" class="fancy"><img src="./stuff/img/icons/ban.png" alt="" class="tooltip" title="' . $lang['uni_ban_ip'] . '" /></a>';
            }
          ?>
          </td>
        </tr>
        <tr>
          <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/date-registered.png" alt="" width="16" height="16" /> <?php echo $lang['users_registered'];?></span></td>
          <td class="info-text"><?php echo date('d.m.Y H:i', $var['registered']);?></td>
        </tr>
        <tr>
          <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/ip-address.png" alt="" width="16" height="16" /> <?php echo $lang['users_registered_ip_address'];?></span></td>
          <td class="info-text">
          <?php
            echo $var['registered_ip'];
            if (!empty($var['registered_ip']) && ($var['registered_ip'] != '-')) {
              echo (in_array(trim($var['registered_ip']), $banned_ips)) ? ' <img src="./stuff/img/icons/warning.png" alt="" class="tooltip" title="' . $lang['uni_banned_ip'] . '" />' : ' <a href="./mn-tools.php?action=quickban&amp;ip=' . $var['registered_ip'] . '" class="fancy"><img src="./stuff/img/icons/ban.png" alt="" class="tooltip" title="' . $lang['uni_ban_ip'] . '" /></a>';
            }
          ?>
          </td>
        </tr>
        <tr>
          <td class="labels"><span class="simlabel"><img src="./stuff/img/icons/permanent-login.png" alt="" width="16" height="16" /> <?php echo $lang['users_permanent_login'];?></span></td>
          <td class="info-text">
            <?php
              if (!empty($var['key']) && $var['status'] == '1') echo '<img src="./stuff/img/icons/tick-gray.png" alt="" width="16" height="16" />';
              else echo '<img src="./stuff/img/icons/cross-gray.png" alt="" width="16" height="16" />';
            ?>
          </td>
        </tr>
      </table>
    </fieldset>
    
    <?php if ($var['action'] == 'edit') { ?><p class="r"><span class="simurl toggle750" rel="admin-info"><img src="./stuff/img/icons/shield.png" alt="" /> <?php echo $lang['users_admin_info'];?></span> | <span class="simurl" id="personal-info-toggle"><img src="./stuff/img/icons/user-personal-info.png" alt="" /> <?php echo $lang['users_personal_and_contact_info'];?></span></p><?php } ?>
    
    <p class="c">
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <input type="hidden" name="id" value="<?php echo $var['user_id'];?>" />
      <button type="submit"><img src="./stuff/img/icons/user-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['users_' . $var['action'] . '_user'];?></button>
    </p>
  </form>
  
<?php
  }
  elseif (isset($admin_tmpl['user_delete']) && $admin_tmpl['user_delete']) {
?>

  <form action="./mn-users.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['users_q_really_delete'];?>: <strong><?php echo $var['username'];?></strong>?<br />
      <?php
        if (isset($posts_count[$var['user_id']]) && !empty($posts_count[$var['user_id']])) $u_posts_count = 0;
        else $u_posts_count = @$posts_count[$var['user_id']];

        if ($u_posts_count == 1) $msg_num = 1;
        elseif ($u_posts_count > 1 && $u_posts_count < 5) $msg_num = 2;
        elseif ($u_posts_count > 4) $msg_num = 3;
        else $msg_num = 0;
        echo '<em>' . str_replace('%n%', '<strong>' . $u_posts_count . '</strong>', $lang['users_msg_posts_number_' . $msg_num]) . '</em>';
      ?>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['user_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['users_delete_user'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }
  else {
?>

  <div class="simbutton fl"><a href="./mn-users.php?action=add"><img src="./stuff/img/icons/user-add.png" alt="" width="16" height="16" /> <?php echo $lang['users_add_user'];?></a></div>
  <div class="rel-links">
    <a href="./mn-groups.php" class="custom"><img src="./stuff/img/icons/group.png" alt="" width="16" height="16" /> <?php echo $lang['groups_groups'];?></a>
  </div>
  <p class="cleaner">&nbsp;</p>


  <table id="users-list" class="tablesorter">
  <thead><tr><th id="cell-username"><?php echo $lang['users_username'];?></th><th id="cell-email"><?php echo $lang['users_email'];?></th><th id="cell-group"><?php echo $lang['users_group'];?></th><th class="num"><img src="./stuff/img/icons/posts.png" alt="" width="16" height="16" title="<?php echo $lang['users_posts_count'];?>" class="tooltip" /></th></tr></thead>
  <tbody><?php echo $users_result;?></tbody>
  </table>
  
  <div id="pager" class="pager<?php if (count($users) <= 10) echo ' hide'; ?>">
    <form action="./mn-users.php">
      <select class="pagesize fr"><option selected="selected" value="10">10</option><option value="20">20</option><option value="30">30</option><option value="<?php echo count($posts);?>"><?php echo $lang['posts_all_posts'];?></option></select>
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
  overall_footer();
?>