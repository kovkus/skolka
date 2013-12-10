<?php
  include './stuff/inc/mn-start.php';

  $auth = user_auth('9');
  
  $t_groups = array();
  $t_groups = load_basic_data('templates_groups');
  
  // compatibility issue for MNews version 2.2.0 and lower
  if (!file_exists(MN_ROOT . $dir['templates'] . 'mn_default_19.html')) {
    mn_put_contents(MN_ROOT . $dir['templates'] . 'mn_default_19.html', $default_template[19]);
  }
  




  if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $admin_tmpl['tmpl_add_step_1'] = true;
    
    $main_types = array();
    foreach ($templates as $i) {
      if (file_exists($dir['templates'] . 'mn_default_' . $i . '.html')) continue;
      else $main_types[$i] = $lang['tmpl_tmpl_type_' . $i];
    }
              
    if (isset($_GET['back']) && $_GET['back'] == 'exists') overall_header($lang['tmpl_add_template'], $lang['tmpl_msg_tmpl_group_exists'], 'error');
    elseif (isset($_GET['back']) && $_GET['back'] == 'empty') overall_header($lang['tmpl_add_template'], $lang['tmpl_msg_tmpl_group_empty'], 'error');
    else overall_header($lang['tmpl_add_template'], $lang['tmpl_add_template'] . ' &raquo; ' . $lang['tmpl_step'] . ' 1/3', 'main');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'add' && $_POST['step'] == '1') {
  
    if ($_POST['group_mode'] == '1' && $_POST['tmpl_group'] == '0') {
      $tg_name = 'mn_default';
    }
    elseif ($_POST['group_mode'] == '1' && $_POST['tmpl_group'] != '0') {
      $tg_name = $t_groups[$_POST['tmpl_group']];
    }
    else {
      $tg_name = friendly_url($_POST['tmpl_new_group']);
      if (@in_array($tg_name, $t_groups) || $tg_name == 'mn_default') {
         header('location: ./mn-templates.php?action=add&back=exists');
         exit;
      }
    }
    
    
    if (!empty($tg_name)) {
    
      $tmpl_types = array();
      foreach ($templates as $i) {
        if (file_exists($dir['templates'] . $tg_name . '_' . $i . '.html')) continue;
        else $tmpl_types[$i] = $lang['tmpl_tmpl_type_' . $i];
      }
      
      if (empty($tmpl_types)) {
        header('location: ./mn-templates.php');
        exit;
      }
      else {
        $var['tmpl_group'] = $tg_name;
        $admin_tmpl['tmpl_add_step_2'] = true;
        overall_header($lang['tmpl_add_template'], $lang['tmpl_add_template'] . ' &raquo; ' . $lang['tmpl_step'] . ' 2/3', 'main');
      }
    }
    else {
      header('location: ./mn-templates.php?action=add&back=empty');
      exit;
    }
    
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'add' && $_POST['step'] == '2') {
  
    if ($_POST['tmpl_group'] != 'mn_default' && empty($_POST['tmpl_name'])) {
      $var['tmpl_group'] = $_POST['tmpl_group'];
      $var['tmpl_name'] = $_POST['tmpl_name'];
      $var['tmpl_type'] = $_POST['tmpl_type'];
      
      $tmpl_types = array();
      foreach ($templates as $i) {
        if (file_exists($dir['templates'] . $var['tmpl_group'] . '_' . $i . '.html')) continue;
        else $tmpl_types[$i] = $lang['tmpl_tmpl_type_' . $i];
      }
      
      $admin_tmpl['tmpl_add_step_2'] = true;
      overall_header($lang['tmpl_add_template'], $lang['tmpl_msg_empty_tmpl_name'], 'error');
    }
    
    else {
    
      if ($_POST['tmpl_group'] != 'mn_default') {

        // create new template group (if it's new group)
        if (!in_array($_POST['tmpl_group'], $t_groups) && $_POST['tmpl_group'] != 'mn_default') {
          if (file_exists($file['templates_groups'])) {
            $tg_file = file($file['templates_groups']);
            $tg_lines = '';
            foreach ($tg_file as $single_line) {
              $tg_data = explode(DELIMITER, $single_line);
              if (substr($tg_data[0], 0, 2) == '<?') $tg_auto_id = trim($tg_data[1]);
              else $tg_lines .= $single_line;
            }
            $tg_content = SAFETY_LINE . DELIMITER . ($tg_auto_id + 1) . "\n" . $tg_lines;
          }
          else {
            $tg_content = SAFETY_LINE . DELIMITER . "2\n";
            $tg_auto_id = 1;
          }
          $tg_content .= $tg_auto_id . DELIMITER . check_text($_POST['tmpl_group']) . "\n";
          mn_put_contents($file['templates_groups'], $tg_content);

          $t_group = $tg_auto_id;
        }

        else {
          foreach ($t_groups as $tg_id => $tg_name) {
            if ($tg_name == $_POST['tmpl_group']) $t_group = $tg_id;
            else continue;
          }
        }



        // create new template
        if (file_exists($file['templates'])) {
          $t_file = file($file['templates']);
          $t_lines = '';
          foreach ($t_file as $single_line) {
            $t_data = explode(DELIMITER, $single_line);
            if (substr($t_data[0], 0, 2) == '<?') $t_auto_id = trim($t_data[1]);
            else $t_lines .= $single_line;
          }

          $t_content = SAFETY_LINE . DELIMITER . ($t_auto_id + 1) . "\n" . $t_lines;
        }
        else {
          $t_content = SAFETY_LINE . DELIMITER . "22\n";
          $t_auto_id = 21;
        }

        $t_content .= $t_auto_id . DELIMITER . check_text($_POST['tmpl_name']) . DELIMITER . $t_group . DELIMITER . $_POST['tmpl_type'] . DELIMITER . "\n";

        mn_put_contents($file['templates'], $t_content);
        
      }
      
      $t_id = ($_POST['tmpl_group'] != 'mn_default') ? $t_auto_id : $_POST['tmpl_type'];


      mn_put_contents($dir['templates'] . $_POST['tmpl_group'] . '_' . $_POST['tmpl_type'] . '.html', '');
      if (file_exists($dir['templates'] . $_POST['tmpl_group'] . '_' . $_POST['tmpl_type'] . '.html')) {
        header('location: ./mn-templates.php?action=edit&id=' . $t_id . '&step=3');
        exit;
      }
      else overall_header($lang['tmpl_templates'], $lang['tmpl_msg_put_contents_error'], 'error');
      
    }

  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    if ($_GET['id'] < 21) {
      $var['tmpl_id'] = $_GET['id'];
      $var['tmpl_name'] = $lang['tmpl_tmpl_name_' . $_GET['id']];
      $var['tmpl_group'] = 'mn_default';
      $var['tmpl_type'] = $_GET['id'];
      $tmpl_file = 'mn_default_' . $_GET['id'] . '.html';
    }
    else {
      $var = get_values('templates', $_GET['id']);
      $tmpl_file = $t_groups[$var['tmpl_group']] . '_' . $var['tmpl_type'] . '.html';
    }

    if (file_exists($dir['templates'] . $tmpl_file)) {
      $var['tmpl_content'] = file_get_contents($dir['templates'] . $tmpl_file);
      $var['action'] = 'edit';
      $admin_tmpl['edit_tmpl'] = true;

      if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['tmpl_edit_template'] . ' &raquo; ' . $var['tmpl_name'] , str_replace('%tmpl%', '"<em>' . $var['tmpl_name'] . '</em>"', $lang['tmpl_msg_edited']), 'ok');
      else overall_header($lang['tmpl_edit_template'] . ' &raquo; ' . $var['tmpl_name'] , $lang['tmpl_edit_template'] . ' &raquo; ' . $var['tmpl_name'], 'info');
    }
    else {
      header('location: ./mn-templates.php');
      exit;
    }
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && is_numeric($_POST['id'])) {
    if ($_POST['id'] < 21) {
      if (mn_put_contents($dir['templates'] . 'mn_default_' . $_POST['id'] . '.html', stripslashes($_POST['tmpl']))) {
        header('location: ./mn-templates.php?action=edit&id=' . $_POST['id'] . '&back=edited');
        exit;
      }
      else overall_header($lang['tmpl_templates'], $lang['tmpl_msg_put_contents_error'], 'error');
    }
    
    else {
      if (!empty($_POST['tmpl_name'])) {
        $old = get_values('templates', $_POST['id']);
        $t_file = file($file['templates']);
        $t_content = '';

        foreach ($t_file as $single_line) {
          $t_data = explode(DELIMITER, $single_line);
          if ($t_data[0] == $_POST['id']) $t_content .= $_POST['id'] . DELIMITER . check_text($_POST['tmpl_name']) . DELIMITER . $old['tmpl_group'] . DELIMITER . $old['tmpl_type'] . DELIMITER . "\n";
          else $t_content .= $single_line;
        }
        
        if (mn_put_contents($file['templates'], $t_content) && mn_put_contents($dir['templates'] . $t_groups[$old['tmpl_group']] . '_' . $old['tmpl_type'] . '.html', stripslashes($_POST['tmpl']))) {
          header('location: ./mn-templates.php?action=edit&id=' . $_POST['id'] . '&back=edited');
          exit;
        }
        else overal_header($lang['tmpl_templates'], $lang['tmpl_msg_put_contents_error'], 'error');
        
      }
      else {
        $var = get_values('templates', $_POST['id']);
        $var['tmpl_name'] = check_text($_POST['tmpl_name']);
        $var['tmpl_content'] = stripslashes($_POST['tmpl']);
        overall_header($lang['tmpl_templates'], $lang['tmpl_msg_empty_tmpl_name'], 'error');
      }
    }
    
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && !in_array($_GET['id'], $default_templates)) {
    if ($_GET['id'] < 21) {
      $var['tmpl_name'] = $lang['tmpl_tmpl_name_' . $_GET['id']];
      $var['tmpl_id'] = $_GET['id'];
    }
    else $var = get_values('templates', $_GET['id']);
    $admin_tmpl['tmpl_delete'] = true;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) &&  !in_array($_POST['id'], $default_templates)) {
  
    if ($_POST['id'] > 20) {
      $var = get_values('templates', $_POST['id']);
      $t_file = file($file['templates']);
      $t_content = '';

      foreach ($t_file as $single_line) {
        $t_data = explode(DELIMITER, $single_line);
        if ($t_data[0] == $_POST['id']) continue;
        else $t_content .= $single_line;
      }

      if (mn_put_contents($file['templates'], $t_content) && unlink($dir['templates'] . $t_groups[$var['tmpl_group']] . '_' . $var['tmpl_type'] . '.html')) {
        header('location: ./mn-templates.php?back=deleted');
        exit;
      }
      else overal_header($lang['tmpl_templates'], $lang['tmpl_msg_put_contents_error'], 'error');
    }
    
    else {
      if (unlink($dir['templates'] . 'mn_default_' . $_POST['id'] . '.html')) {
        header('location: ./mn-templates.php?back=deleted');
        exit;
      }
      else overal_header($lang['tmpl_templates'], $lang['tmpl_msg_put_contents_error'], 'error');
    }
    
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'default') {
    foreach ($default_template as $d_num => $d_tmpl) {
      mn_put_contents($dir['templates'] . 'mn_default_' . $d_num . '.html', $d_tmpl);
    }
    header('location: ./mn-templates.php?defaults');
    exit;
  }





  else {
    $templates_result = '';

    foreach ($templates as $i) {
      if (!file_exists($dir['templates'] . 'mn_default_' . $i . '.html')) continue;
      else {
        $delete_link = (in_array($i, $default_templates)) ? '' : ' | <a href="./mn-templates.php?action=delete&amp;id=' . $i . '" class="fancy">' . $lang['uni_delete'] . '</a>';
        $templates_result .= '<tr><td><a href="./mn-templates.php?action=edit&amp;id=' . $i . '" class="main-link">' . $lang['tmpl_tmpl_name_' . $i] . '</a><br />&nbsp;<span class="links hide"><a href="./mn-templates.php?action=edit&amp;id=' . $i . '">' . $lang['uni_edit'] . '</a>' . $delete_link . '</span></td><td><span class="trivial">mn_default</span></td><td>' . $lang['tmpl_tmpl_type_' . $i] . '</td></tr>';
      }
      
    }
    
    
    if (file_exists($file['templates'])) {
      $t_file = file($file['templates']);
      array_shift($t_file);

      foreach ($t_file as $t_line) {
        $t_data = explode(DELIMITER, $t_line);
        $t_id = trim($t_data[0]);

        $tmpl_name = $t_data[1];
        $tmpl_group = $t_groups[$t_data[2]];
        $delete_link = ' | <a href="./mn-templates.php?action=delete&amp;id=' . $t_id . '" class="fancy">' . $lang['uni_delete'] . '</a>';
        $tmpl_type = $lang['tmpl_tmpl_type_' . $t_data[3]];

        $templates_result .= '<tr><td><a href="./mn-templates.php?action=edit&amp;id=' . $t_id . '" class="main-link">' . $tmpl_name . '</a><br />&nbsp;<span class="links hide"><a href="./mn-templates.php?action=edit&amp;id=' . $t_id . '">' . $lang['uni_edit'] . '</a>' . $delete_link . '</span></td><td>' . $tmpl_group . '</td><td>' . $tmpl_type . '</td></tr>';
      }
    }

    if (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['tmpl_templates'], $lang['tmpl_msg_deleted'], 'ok');
    else overall_header($lang['tmpl_templates'], $lang['tmpl_templates'], 'main');
  }
  




  if (isset($admin_tmpl['tmpl_add_step_1']) && $admin_tmpl['tmpl_add_step_1']) {
?>

  <form action="./mn-templates.php" method="post" class="tmpl-add">
    <fieldset>
      <p class="c"><?php echo $lang['tmpl_select_tmpl_group'];?></p>
      <table>
        <?php if (!empty($main_types) || !empty($t_groups)) { ?>
        <tr>
          <td><input type="radio" name="group_mode" id="group_mode1" value="1" checked="checked" /> <label for="group_mode1"><?php echo $lang['tmpl_tmpl_group_exists'];?>:</label></td>
          <td><select name="tmpl_group" id="tmpl_group1" class="custom long">
            
            <?php
              if (!empty($main_types)) echo '<option value="0">mn_default</option>';
              
              if (!empty($t_groups)) {
                foreach ($t_groups as $tg_id => $tg_name) {
                
                  $tmpl_types = array();
                  foreach ($templates as $i) {
                    if (file_exists($dir['templates'] . $tg_name . '_' . $i . '.html')) continue;
                    else $tmpl_types[$i] = $lang['tmpl_tmpl_type_' . $i];
                  }

                  if (empty($tmpl_types)) continue;
                  else echo '<option value="' . $tg_id . '">' . $tg_name . '</option>';
                }
              }
            ?>
          </select></td>
        </tr>
        <?php } ?>
        
        <tr>
          <td><input type="radio" name="group_mode" id="group_mode2" value="2"<?php if (empty($main_types) && empty($t_groups)) echo ' checked="checked"';?> /> <label for="group_mode2"><?php echo $lang['tmpl_tmpl_group_new'];?>:</label></td>
          <td><input type="text" name="tmpl_new_group" id="tmpl_group2" value="<?php echo (isset($_POST['tmpl_new_group'])) ? check_text($_POST['tmpl_new_group']) : '';?>" class="text custom" /></td>
        </tr>
      </table>
      
      <p>
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="step" value="1" />
        <button type="submit"><img src="./stuff/img/icons/template-go.png" alt="" width="16" height="16" /> <?php echo $lang['tmpl_continue'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  }
  
  elseif (isset($admin_tmpl['tmpl_add_step_2']) && $admin_tmpl['tmpl_add_step_2']) {
?>

  <form action="./mn-templates.php" method="post" class="tmpl-add">
    <fieldset>
      <table>
      
        <?php if ($var['tmpl_group'] != 'mn_default') { ?>
        <tr>
          <td><label for="tmpl_name"><?php echo $lang['tmpl_tmpl_name'];?>:</label></td>
          <td><input type="text" name="tmpl_name" id="tmpl_name" value="<?php echo (isset($_POST['tmpl_name'])) ? check_text($_POST['tmpl_name']) : '';?>" class="text custom" /></td>
        </tr>
        <?php } ?>

        <tr>
          <td><label for="tmpl_type"><?php echo $lang['tmpl_tmpl_type'];?>:</label></td>
          <td>
            <select name="tmpl_type" class="custom long">
              <?php
                foreach ($tmpl_types as $tt_id => $tt_name) {
                  $sel = ($tt_id == $var['tmpl_type']) ? ' selected="selected"' : '';
                  echo '<option value="' . $tt_id . '"' . $sel . '>' . $tt_name . '</option>';
                }
              ?>
            </select>
          </td>
        </tr>
      </table>
      
      <p>
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="step" value="2" />
        <input type="hidden" name="tmpl_group" value="<?php echo $var['tmpl_group'];?>" />
        <button type="submit"><img src="./stuff/img/icons/template-go.png" alt="" width="16" height="16" /> <?php echo $lang['tmpl_continue'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  }
  
  elseif (isset($admin_tmpl['edit_tmpl']) && $admin_tmpl['edit_tmpl']) {
?>

  <a href="./mn-templates.php" id="tmpl-link"><img src="./stuff/img/icons/template.png" alt="" /> <span><?php echo $lang['tmpl_templates'];?></span></a>


  
  <form id="tmpl-edit" action="./mn-templates.php" method="post">

    <?php if ($var['tmpl_id'] > 20) { ?>
    <fieldset id="tmpl-values">
      <label for="tmpl_name"><?php echo $lang['tmpl_tmpl_name'];?>:</label> <input type="text" name="tmpl_name" id="tmpl_name" class="text" value="<?php echo $var['tmpl_name'];?>" /><br />
    </fieldset>
    <?php } ?>


    <fieldset>

      <div class="tmpl-help round" id="tmpl-main-help">
        <?php
          $tmpl_type_id = ($var['tmpl_type'] <= 9) ? '1' : $var['tmpl_type'];
          include './stuff/inc/tmpl/tmpl-type-' . $tmpl_type_id . '.php';
        ?>
      </div>
      <textarea name="tmpl" id="tmpl" rows="10" cols="100"><?php echo str_replace('&', '&amp;', $var['tmpl_content']);?></textarea>

      <p class="c">
        <input type="hidden" name="id" value="<?php echo $var['tmpl_id'];?>" />
        <input type="hidden" name="action" value="edit" />
        <button type="submit"><img src="./stuff/img/icons/template-edit.png" alt="" /> <?php echo $lang['tmpl_edit_template'];?></button>
      </p>

      <p class="r">
      <a href="./mn-templates.php">&laquo; <?php echo $lang['tmpl_link_edit_tmpls'];?></a>
    </p>
    </fieldset>
  </form>

<?php
  }
  
  elseif (isset($admin_tmpl['tmpl_delete']) && $admin_tmpl['tmpl_delete']) {
?>

  <form action="./mn-templates.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['tmpl_q_really_delete'];?>: <strong><?php echo $var['tmpl_name'];?></strong>?<br />
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['tmpl_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['tmpl_delete_template'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  else {
?>

  <div class="simbutton fl"><a href="./mn-templates.php?action=add"><img src="./stuff/img/icons/template-add.png" alt="" width="16" height="16" /> <?php echo $lang['tmpl_add_template'];?></a></div>
  <p class="cleaner">&nbsp;</p>

  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>

  <table id="templates-list" class="tablesorter">
    <thead>
      <tr>
        <th id="tmpl_name"><?php echo $lang['tmpl_tmpl_name'];?></th>
        <th id="tmpl_group"><?php echo $lang['tmpl_tmpl_group'];?></th>
        <th id="tmpl_type"><?php echo $lang['tmpl_tmpl_type'];?></th>
    </thead>
    <tbody>
      <?php echo $templates_result;?>
    </tbody>
  </table>

<?php
  }

  overall_footer();
?>
