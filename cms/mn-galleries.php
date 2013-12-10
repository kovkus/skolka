<?php

  include './stuff/inc/mn-start.php';
  $admin_tmpl['galleries_main'] = true;
  $admin_tmpl['code'] = false;



  $galleries = file_exists($file['galleries']) ? load_basic_data('galleries') : array();
  $files_count = get_files_count();



  if (isset($_GET['action']) && $_GET['action'] == 'ajaxcall') {
    if (isset($_GET['gallery_name']) && !empty($_GET['gallery_name']) && !in_array($_GET['gallery_name'], $galleries)) {
      if (file_exists($file['galleries'])) {

        $galleries_file = file($file['galleries']);
        $galleries_file_lines = '';
        foreach ($galleries_file as $single_line) {
          $gallery_data = explode(DELIMITER, $single_line);
          if (substr($gallery_data[0], 0, 2) == '<?') $auto_increment_id = trim($gallery_data[1]);
          else $galleries_file_lines .= $single_line;
        }

      }

      else {
        $auto_increment_id = 1;
        $galleries_file_lines = '';
      }

      $galleries_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $galleries_file_lines;
      $galleries_file_content .= $auto_increment_id . DELIMITER . $_GET['gallery_name'] . DELIMITER . friendly_url($_GET['gallery_name']) . "\n";

      if (mn_put_contents($file['galleries'], $galleries_file_content)) {
        echo '<li><input type="checkbox" name="galleries[]" id="gallery-' . $auto_increment_id . '" class="input-gallery" value="' . $auto_increment_id . '" checked="checked" /> <label for="gallery-' . $auto_increment_id . '">' . substr($_GET['gallery_name'], 0, 20) . '</label></li>';
        die();
      }
      else echo 'Error MN#44: cannot write to mn-galleries.php file!';
      die();
    }

    else echo '';
    die();
  }





  $auth = user_auth('5');





  if (isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    $var = get_values('galleries', $_REQUEST['id']);
  }





  if (isset($_POST['action']) && $_POST['action'] == 'add') {

    if (!empty($_POST['gallery_name'])) {

      if (file_exists($file['galleries'])) {

        $galleries_file = file($file['galleries']);
        $galleries_file_lines = '';
        foreach ($galleries_file as $single_line) {
          $gallery_data = explode(DELIMITER, $single_line);
          if (substr($gallery_data[0], 0, 2) == '<?') $auto_increment_id = trim($gallery_data[1]);
          else $galleries_file_lines .= $single_line;
        }

      }

      else $auto_increment_id = 1;

      $galleries_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $galleries_file_lines;
      $galleries_file_content .= $auto_increment_id . DELIMITER . $_POST['gallery_name'] . DELIMITER . friendly_url($_POST['gallery_name']) . "\n";

      if (mn_put_contents($file['galleries'], $galleries_file_content)) {
        header('location: ./mn-galleries.php?back=added');
        exit;
      }
      else overal_header($lang['galleries_galleries'], $lang['galleries_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['galleries_galleries'], $lang['galleries_msg_empty_gallery_name'], 'error');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && file_exists($file['galleries'])) {

    if (!empty($_POST['gallery_name'])) {

      $galleries_file = file($file['galleries']);
      $galleries_file_content = '';

      foreach ($galleries_file as $single_line) {
        $gallery_data = explode(DELIMITER, $single_line);
        if ($gallery_data[0] == $_POST['id']) $galleries_file_content .= $gallery_data[0] . DELIMITER . $_POST['gallery_name'] . DELIMITER . friendly_url($_POST['gallery_name']) . "\n";
        else $galleries_file_content .= $single_line;
      }

      if (mn_put_contents($file['galleries'], $galleries_file_content)) {
        header('location: ./mn-galleries.php?back=edited');
        exit;
      }
      else overal_header($lang['galleries_galleries'], $lang['galleries_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['galleries_galleries'], $lang['galleries_msg_empty_gallery_name'], 'error');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && file_exists($file['galleries']) && !empty($var['gallery_name'])) {
    overall_header($lang['galleries_edit_gallery'] . ' &raquo; ' . $var['gallery_name'], $lang['galleries_edit_gallery'], 'main');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && file_exists($file['galleries']) && !empty($var['gallery_name'])) {
    $admin_tmpl['galleries_main'] = false;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && file_exists($file['galleries'])) {

    $galleries_file = file($file['galleries']);
    $galleries_file_content = '';

    foreach ($galleries_file as $single_line) {
      $gallery_data = explode(DELIMITER, $single_line);
      if ($gallery_data[0] == $_POST['id']) continue;
      else $galleries_file_content .= $single_line;
    }

    if (mn_put_contents($file['galleries'], $galleries_file_content)) {
      header('location: ./mn-galleries.php?back=deleted');
      exit;
    }
    else overal_header($lang['galleries_galleries'], $lang['galleries_msg_put_contents_error'], 'error');

  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'code' && isset($_GET['gal']) && is_numeric($_GET['gal'])) {
    $admin_tmpl['galleries_main'] = false;
    $admin_tmpl['code'] = true;
  }





  else {
    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['galleries_galleries'], $lang['galleries_msg_gallery_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['galleries_galleries'], $lang['galleries_msg_gallery_deleted'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['galleries_galleries'], $lang['galleries_msg_gallery_edited'], 'ok');
    else overall_header($lang['galleries_galleries'], $lang['galleries_galleries'], 'main');
    $var['action'] = 'add';
  }





  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') ? 'edit' : 'add';

  if (isset($admin_tmpl['galleries_main']) && $admin_tmpl['galleries_main']) {
?>

  <div id="gallery-add-edit">
    <form action="./mn-galleries.php" method="post" id="category-add-edit">
      <label for="gallery_name"><?php echo $lang['galleries_gallery_name'];?>:</label> <input type="text" class="text" name="gallery_name" id="gallery_name" value="<?php echo @$var['gallery_name'];?>" />
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php if (isset($var['action']) && $var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['gallery_id'] . '" />';?>
      <button type="submit" name="aaa"><img src="./stuff/img/icons/images-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['galleries_' . $var['action'] . '_gallery'];?></button>
      <?php echo ($var['action'] == 'edit') ? ' <a href="./mn-galleries.php" class="cancel">' . $lang['uni_cancel'] . '</a>' : '';?>
    </form>
  </div>


  <?php if (file_exists($file['galleries']) && !empty($galleries)) { ?>
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
  <table id="galleries-list" class="tablesorter minor-list">
    <thead>
      <tr><th id="minor_id" class="l num">id</th><th id="minor_name"><?php echo $lang['galleries_gallery'];?></th><th id="minor_count" class="num"><img src="./stuff/img/icons/images-gray.png" alt="-" class="tooltip" title="<?php echo $lang['galleries_posts_count'];?>" /></th></tr>
    </thead>
    <tbody>
      <?php
        foreach ($galleries as $gallery_id => $gallery_name) {
          $gallery_files_count = (empty($files_count[$gallery_id])) ? '<em class="trivial">0</em>' : '<a href="./mn-files.php?g=' . $gallery_id . '">' . $files_count[$gallery_id] . '</a>';
          echo '<tr><td class="c">' . $gallery_id . '</td><td><a href="./mn-galleries.php?action=edit&amp;id=' . $gallery_id . '" class="main-link">' . $gallery_name . '</a><br />&nbsp;<span class="links hide"><a href="./mn-galleries.php?action=edit&amp;id=' . $gallery_id . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-galleries.php?action=code&amp;gal=' . $gallery_id . '" class="fancy">' . $lang['uni_code'] . '</a> | <a href="./mn-galleries.php?action=delete&amp;id=' . $gallery_id . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td class="c">' . $gallery_files_count . '</td></tr>';
        }
      ?>
    </tbody>
  </table>
  <?php } ?>

  <p class="cleaner">&nbsp;</p>

<?php
  }
  elseif (isset($admin_tmpl['code']) && $admin_tmpl['code']) {
?>

  <form id="integration">
    <fieldset>
      <legend><?php echo $lang['galleries_int_code'];?></legend>
      <p class="help"><?php echo str_ireplace('%templates%', '<a href="./mn-templates.php">' . $lang['tmpl_templates'] . '</a>', $lang['galleries_int_code_help1']);?></p>
      <textarea class="integration" id="gal" readonly="readonly">&lt;?php
  $mn_mode = 'gallery';
  $mn_gallery = <?php echo $_GET['gal'];?>;
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
      <p class="help">
        <?php
          echo $lang['galleries_int_code_help2'] . ': http://' . $_SERVER['SERVER_NAME'] . '/?mn_gallery=' . $_GET['gal'];
        ?>
      </p>
  </form>

<?php
  die();
  }
  else {
?>

  <form action="./mn-galleries.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['galleries_q_really_delete'];?>: <strong><?php echo $var['gallery_name'];?></strong>?<br />
      <?php
        if (isset($files_count[$var['gallery_id']]) && $files_count[$var['gallery_id']] == 1) $msg_num = 1;
        elseif (isset($files_count[$var['gallery_id']]) && $files_count[$var['gallery_id']] > 1 && $files_count[$var['gallery_id']] < 5) $msg_num = 2;
        elseif (isset($files_count[$var['gallery_id']]) && $files_count[$var['gallery_id']] > 4) $msg_num = 3;
        else $msg_num = 0;
        echo '<em>' . str_replace('%n%', '<strong>' . @$files_count[$var['gallery_id']] . '</strong>', $lang['galleries_msg_posts_number_' . $msg_num]) . '</em>';
        echo '<em>' . $lang['galleries_msg_images_no_delete'] . '</em>';
      ?>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['gallery_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['galleries_delete_gallery'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  overall_footer();



  ##### I ? ########################################################################################
  ##### http://youtu.be/nTbL5elVXrU ################################################################

?>