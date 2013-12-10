<?php
  include './stuff/inc/mn-start.php';
  $auth = user_auth('7');





  if (isset($_GET['action']) && $_GET['action'] == 'add') {
    overall_header($lang['groups_add_new_group'], $lang['groups_add_new_group'], 'main');
    $var['permissions'] = '220200000000011';
    $admin_tmpl['groups_form'] = true;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
    $permissions = $_POST['dashboard'] . $_POST['posts'] . $_POST['cats'] . $_POST['comments'] . $_POST['pages'] . $_POST['uploads'] . $_POST['users'] . $_POST['groups'] . $_POST['config'] . $_POST['templates'] . $_POST['ipban'] . $_POST['backup'] . $_POST['integration'] . $_POST['help'] . $_POST['profile'];
    
    if (strlen($permissions) == 15 && !empty($_POST['group_name'])) {
      $g_file = file($file['groups']);
      $g_lines = '';
      foreach ($g_file as $single_line) {
        $g_data = explode(DELIMITER, $single_line);
        if (substr($g_data[0], 0, 2) == '<?') $auto_increment_id = trim($g_data[1]);
        else $g_lines .= $single_line;
      }
      
      $g_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $g_lines;
      $g_content .= $auto_increment_id . DELIMITER . check_text($_POST['group_name']) . DELIMITER . friendly_url($_POST['group_name']) . DELIMITER . $permissions . "\n";
      
      if (mn_put_contents($file['groups'], $g_content)) {
        header('location: ./mn-groups.php?back=added');
        exit;
      }
      else overall_header($lang['groups_groups'], $lang['groups_msg_put_contents_error'], 'error');
    }
    else {
      overall_header($lang['groups_add_new_group'], $lang['groups_msg_empty_values'], 'error');
      $var['permissions'] = $permissions;
      $var['group_name'] = check_text($_POST['group_name']);
      $admin_tmpl['groups_form'] = true;
    }
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && $_GET['id'] != 1 && file_exists($file['groups'])) {
    $var = get_values('groups', $_GET['id']);
    overall_header($lang['groups_groups'], $lang['groups_groups'], 'main');
    $admin_tmpl['groups_form'] = true;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && $_POST['id'] != 1 && file_exists($file['groups'])) {
    $permissions = ($_POST['id'] == 1) ? '111111111111111' : $_POST['dashboard'] . $_POST['posts'] . $_POST['cats'] . $_POST['comments'] . $_POST['pages'] . $_POST['uploads'] . $_POST['users'] . $_POST['groups'] . $_POST['config'] . $_POST['templates'] . $_POST['ipban'] . $_POST['backup'] . $_POST['integration'] . $_POST['help'] . $_POST['profile'];

    if (strlen($permissions) == 15 && !empty($_POST['group_name'])) {
      $g_file = file($file['groups']); $g_content = '';

      foreach ($g_file as $single_line) {
        $g_data = explode(DELIMITER, $single_line);
        if ($g_data[0] == $_POST['id']) $g_content .= $_POST['id'] . DELIMITER . check_text($_POST['group_name']) . DELIMITER . friendly_url($_POST['group_name']) . DELIMITER . $permissions . "\n";
        else $g_content .= $single_line;
      }

      if (mn_put_contents($file['groups'], $g_content)) {
        header('location: ./mn-groups.php?back=edited');
        exit;
      }
      else overal_header($lang['groups_groups'], $lang['groups_msg_put_contents_error'], 'error');
    }
    else {
      overall_header($lang['groups_groups'], $lang['groups_msg_empty_values'], 'error');
      $var['permissions'] = $permissions;
      $var['group_name'] = check_text($_POST['group_name']);
      $admin_tmpl['groups_form'] = true;
    }
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && file_exists($file['groups'])) {
    $groups = load_basic_data('groups');
    $var = get_values('groups', $_GET['id']);
    $admin_tmpl['group_delete'] = true;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && file_exists($file['groups'])) {

    $g_file = file($file['groups']);
    $g_content = '';

    foreach ($g_file as $g_line) {
      $g_data = explode(DELIMITER, $g_line);
      if ($g_data[0] == $_POST['id'] && $_POST['id'] != 1) continue;
      else $g_content .= $g_line;
    }
    
    $u_file = file($file['users']);
    $u_content = '';
    foreach ($u_file as $u_line) {
      $u_data = explode(DELIMITER, $u_line);
      if (isset($u_data[4]) && $u_data[4] == $_POST['id']) $u_content .= $u_data[0] . DELIMITER . $u_data[1] . DELIMITER . $u_data[2] . DELIMITER . $u_data[3] . DELIMITER . trim($_POST['group_alt']) . DELIMITER . $u_data[5] . DELIMITER . $u_data[6] . DELIMITER . $u_data[7] . DELIMITER . $u_data[8] . DELIMITER . $u_data[9] . DELIMITER . $u_data[10] . DELIMITER . $u_data[11] . DELIMITER . $u_data[12] . DELIMITER . $u_data[13] . DELIMITER . $u_data[14] . DELIMITER . $u_data[15] . DELIMITER . $u_data[16] . DELIMITER . $u_data[17] . DELIMITER . $u_data[18] . DELIMITER . $u_data[19] . DELIMITER . $u_data[20] . DELIMITER . $u_data[21] . DELIMITER . $u_data[22] . DELIMITER . $u_data[23] . DELIMITER . $u_data[24] . DELIMITER . $u_data[25] . DELIMITER . $u_data[26] . DELIMITER . $u_data[27] . DELIMITER . trim($u_data[28]) . DELIMITER . $u_data[29] . DELIMITER . $u_data[30] . DELIMITER . $u_data[31] . DELIMITER . $u_data[32] . DELIMITER . trim($u_data[33]) . "\n";
      else $u_content .= $u_line;
    }

    if (mn_put_contents($file['groups'], $g_content) && mn_put_contents($file['users'], $u_content)) {
      header('location: ./mn-groups.php?back=deleted');
      exit;
    }
    else overal_header($lang['groups_groups'], $lang['groups_msg_put_contents_error'], 'error');

  }





  else {
    $g_file = file($file['groups']);
    array_shift($g_file);
    $groups_result = '';
    
    foreach ($g_file as $g_line) {
      $g_data = explode(DELIMITER, $g_line);
      $group_id = trim($g_data[0]);
      
      if ($group_id == 1) {
      	$groups_result .= '<tr><td><span class="main-link tooltip" title="' . $lang['groups_msg_not_editable'] . '">' . $lang['groups_default_group_1'] . '</span><br />&nbsp;</td>';
      }
      else {
      	$groups_result .= '<tr><td><a href="./mn-groups.php?action=edit&amp;id=' . $group_id . '" class="main-link">' . $g_data[1] . '</a><br />&nbsp;<span class="links hide"><a href="./mn-groups.php?action=edit&amp;id=' . $group_id . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-groups.php?action=delete&amp;id=' . $group_id . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td>';
      }


      for ($i = 0; $i <= 14; $i++) {
        $groups_result .= '<td class="c"><img src="./stuff/img/icons/permission-' . $g_data[3][$i] . '.png" alt="" width="16" height="16" class="tooltip" title="' . $lang['groups_perms_section_' . $i] . ' - ' . $lang['groups_perms_' . $g_data[3][$i]] . '" /></td>';
      }
      $groups_result .= '</tr>';
    }


    if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['groups_groups'], $lang['groups_msg_edited'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['groups_groups'], $lang['groups_msg_deleted'], 'ok');
    else overall_header($lang['groups_groups'], $lang['groups_groups'], 'main');
    
    $admin_tmpl['groups_list'] = true;
  }




  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') ? 'add' : 'edit';
  if (isset($admin_tmpl['groups_form']) && $admin_tmpl['groups_form']) {
?>

  <form action="./mn-groups.php" method="post" id="group-add-edit">
    <fieldset id="group-name"><label for="group_name"><?php echo $lang['groups_group_name'];?>:</label> <input type="text" class="text" name="group_name" id="group_name" value="<?php echo (isset($var['group_name'])) ? $var['group_name'] : '';?>" /></fieldset>
    
    
    <?php if ($_GET['action'] == 'add' || (isset($var['group_id']) && $var['group_id'] != 1)) { ?>
    <fieldset>
    <legend><img src="./stuff/img/icons/dashboard.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_0'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="dashboard" id="dashboard1" value="1"<?php if ($var['permissions'][0] == '1') echo ' checked="checked"';?> /> <label for="dashboard1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="dashboard" id="dashboard2" value="2"<?php if ($var['permissions'][0] == '2') echo ' checked="checked"';?> /> <label for="dashboard2"><?php echo $lang['groups_perms_2'];?></label></li>
        <li><input type="radio" name="dashboard" id="dashboard3" value="3"<?php if ($var['permissions'][0] == '3') echo ' checked="checked"';?> /> <label for="dashboard3"><?php echo $lang['groups_perms_3_dashboard'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/posts.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_1'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="posts" id="posts1" value="1"<?php if ($var['permissions'][1] == '1') echo ' checked="checked"';?> /> <label for="posts1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="posts" id="posts2" value="2"<?php if ($var['permissions'][1] == '2') echo ' checked="checked"';?> /> <label for="posts2"><?php echo $lang['groups_perms_2'];?></label></li>
        <li><input type="radio" name="posts" id="posts3" value="3"<?php if ($var['permissions'][1] == '3') echo ' checked="checked"';?> /> <label for="posts3"><?php echo $lang['groups_perms_3'];?></label></li>
        <li><input type="radio" name="posts" id="posts0" value="0"<?php if ($var['permissions'][1] == '0') echo ' checked="checked"';?> /> <label for="posts0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/tag.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_2'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="cats" id="cats1" value="1"<?php if ($var['permissions'][2] == '1') echo ' checked="checked"';?> /> <label for="cats1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="cats" id="cats0" value="0"<?php if ($var['permissions'][2] == '0') echo ' checked="checked"';?> /> <label for="cats0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>

    <fieldset>
    <legend><img src="./stuff/img/icons/comments.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_3'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="comments" id="comments1" value="1"<?php if ($var['permissions'][3] == '1') echo ' checked="checked"';?> /> <label for="comments1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="comments" id="comments2" value="2"<?php if ($var['permissions'][3] == '2') echo ' checked="checked"';?> /> <label for="comments2"><?php echo $lang['groups_perms_2'];?></label></li>
        <li><input type="radio" name="comments" id="comments0" value="0"<?php if ($var['permissions'][3] == '0') echo ' checked="checked"';?> /> <label for="comments0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/pages.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_4'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="pages" id="pages1" value="1"<?php if ($var['permissions'][4] == '1') echo ' checked="checked"';?> /> <label for="pages1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="pages" id="pages2" value="2"<?php if ($var['permissions'][4] == '2') echo ' checked="checked"';?> /> <label for="pages2"><?php echo $lang['groups_perms_2'];?></label></li>
        <li><input type="radio" name="pages" id="pages0" value="0"<?php if ($var['permissions'][4] == '0') echo ' checked="checked"';?> /> <label for="pages0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/folders.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_5'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="uploads" id="uploads1" value="1"<?php if ($var['permissions'][5] == '1') echo ' checked="checked"';?> /> <label for="uploads1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="uploads" id="uploads2" value="2"<?php if ($var['permissions'][5] == '2') echo ' checked="checked"';?> /> <label for="uploads2"><?php echo $lang['groups_perms_2'];?></label></li>
        <li><input type="radio" name="uploads" id="uploads0" value="0"<?php if ($var['permissions'][5] == '0') echo ' checked="checked"';?> /> <label for="uploads0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_6'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="users" id="users1" value="1"<?php if ($var['permissions'][6] == '1') echo ' checked="checked"';?> /> <label for="users1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="users" id="users0" value="0"<?php if ($var['permissions'][6] == '0') echo ' checked="checked"';?> /> <label for="users0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/group.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_7'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="groups" id="groups1" value="1"<?php if ($var['permissions'][7] == '1') echo ' checked="checked"';?> /> <label for="groups1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="groups" id="groups0" value="0"<?php if ($var['permissions'][7] == '0') echo ' checked="checked"';?> /> <label for="groups0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/config.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_8'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="config" id="config1" value="1"<?php if ($var['permissions'][8] == '1') echo ' checked="checked"';?> /> <label for="config1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="config" id="config0" value="0"<?php if ($var['permissions'][8] == '0') echo ' checked="checked"';?> /> <label for="config0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/template.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_9'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="templates" id="templates1" value="1"<?php if ($var['permissions'][9] == '1') echo ' checked="checked"';?> /> <label for="templates1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="templates" id="templates0" value="0"<?php if ($var['permissions'][9] == '0') echo ' checked="checked"';?> /> <label for="templates0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/blocked-ip.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_10'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="ipban" id="ipban1" value="1"<?php if ($var['permissions'][10] == '1') echo ' checked="checked"';?> /> <label for="ipban1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="ipban" id="ipban0" value="0"<?php if ($var['permissions'][10] == '0') echo ' checked="checked"';?> /> <label for="ipban0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/backup.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_11'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="backup" id="backup1" value="1"<?php if ($var['permissions'][11] == '1') echo ' checked="checked"';?> /> <label for="backup1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="backup" id="backup0" value="0"<?php if ($var['permissions'][11] == '0') echo ' checked="checked"';?> /> <label for="backup0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_12'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="integration" id="integration1" value="1"<?php if ($var['permissions'][12] == '1') echo ' checked="checked"';?> /> <label for="integration1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="integration" id="integration0" value="0"<?php if ($var['permissions'][12] == '0') echo ' checked="checked"';?> /> <label for="integration0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/faq.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_13'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="help" id="help1" value="1"<?php if ($var['permissions'][13] == '1') echo ' checked="checked"';?> /> <label for="help1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="help" id="help0" value="0"<?php if ($var['permissions'][13] == '0') echo ' checked="checked"';?> /> <label for="help0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    
    <fieldset>
    <legend><img src="./stuff/img/icons/user-personal-info.png" alt="" width="16" height="16" /> <?php echo $lang['groups_perms_section_14'];?></legend>
      <ul class="permissions-list">
        <li><input type="radio" name="profile" id="profile1" value="1"<?php if ($var['permissions'][14] == '1') echo ' checked="checked"';?> /> <label for="profile1"><?php echo $lang['groups_perms_1'];?></label></li>
        <li><input type="radio" name="profile" id="profile0" value="0"<?php if ($var['permissions'][14] == '0') echo ' checked="checked"';?> /> <label for="profile0"><?php echo $lang['groups_perms_0'];?></label></li>
      </ul>
    </fieldset>
    <?php } ?>
    
    
    
    <p>
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php if (isset($var['action']) && $var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['group_id'] . '" />';?>
      <button type="submit"><img src="./stuff/img/icons/group-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['groups_' . $var['action'] . '_group'];?></button>
    </p>
  </form>


<?php
  }
  
  elseif (isset($admin_tmpl['group_delete']) && $admin_tmpl['group_delete']) {
?>

  <form action="./mn-groups.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['groups_q_really_delete'];?>: <strong><?php echo $var['group_name'];?></strong>?<br />
      <p>
        <?php echo $lang['groups_move_users_to_group'];?>:
        <select name="group_alt">
        <?php
          foreach ($groups as $group_id => $group_name) {
            $sel = ($group_id > 1 && $group_id != $var['group_id'] && empty($sels)) ? ' selected="selected"' : '';
            $sels .= $sel;

            if ($group_id == $var['group_id']) continue;
            else echo '<option value="' . $group_id . '"' . $sel . '>' . $group_name . '</option>';
          }
        ?>
        </select>
      </p>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['group_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['groups_delete_group'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  else {
?>

  <div class="simbutton "><a href="./mn-groups.php?action=add"><img src="./stuff/img/icons/group-add.png" alt="" width="16" height="16" /> <?php echo $lang['groups_add_group'];?></a></div>
  <p class="cleaner">&nbsp;</p>
  
  <table id="groups-list" class="tablesorter">
    <thead>
      <tr>
        <th id="group_name"><?php echo $lang['groups_group'];?></th>
        <th class="permissions"><img src="./stuff/img/icons/dashboard.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_0'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/posts.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_1'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/tag.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_2'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/comments.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_3'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/pages.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_4'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/folders.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_5'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_6'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/group.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_7'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/config.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_8'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/template.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_9'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/blocked-ip.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_10'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/backup.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_11'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_12'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/faq.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_13'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/user-personal-info.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_14'];?>" /></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th id="group_name"><?php echo $lang['groups_group'];?></th>
        <th class="permissions"><img src="./stuff/img/icons/dashboard.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_0'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/posts.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_1'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/tag.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_2'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/comments.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_3'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/pages.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_4'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/folders.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_5'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/user.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_6'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/group.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_7'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/config.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_8'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/template.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_9'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/blocked-ip.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_10'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/backup.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_11'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_12'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/faq.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_13'];?>" /></th>
        <th class="permissions"><img src="./stuff/img/icons/user-personal-info.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['groups_perms_section_14'];?>" /></th>
      </tr>
    </tfoot>
    <tbody>
      <?php
        echo $groups_result;
      ?>
    </tbody>
  </table>
  

  <ul id="permissions-help" class="round">
    <li><img src="./stuff/img/icons/permission-1.png" alt="" width="16" height="16" /> <?php echo '<strong>' . $lang['groups_perms_1'] . '</strong> - ' . $lang['groups_perms_help_1'];?></li>
    <li><img src="./stuff/img/icons/permission-2.png" alt="" width="16" height="16" /> <?php echo '<strong>' . $lang['groups_perms_2'] . '</strong> - ' . $lang['groups_perms_help_2'];?></li>
    <li><img src="./stuff/img/icons/permission-3.png" alt="" width="16" height="16" /> <?php echo '<strong>' . $lang['groups_perms_3'] . '</strong> - ' . str_replace('#1', '<img src="./stuff/img/icons/permission-2.png" alt="" width="16" height="16" />', $lang['groups_perms_help_3']);?></li>
    <li><img src="./stuff/img/icons/permission-0.png" alt="" width="16" height="16" /> <?php echo '<strong>' . $lang['groups_perms_0'] . '</strong> - ' . $lang['groups_perms_help_0'];?></li>
  </ul>

<?php
  }
  overall_footer();
?>