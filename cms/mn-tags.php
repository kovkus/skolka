<?php
  include './stuff/inc/mn-start.php';
  $admin_tmpl['tags_main'] = true;

  $auth = user_auth('2');


  $tags = file_exists($file['tags']) ? load_basic_data('tags') : array();
  $posts_count = file_exists($file['tags']) ? get_posts_count('tags') : array();



  if (isset($_GET['action']) && $_GET['action'] == 'ajaxcall') {
    if (isset($_GET['tag_name']) && !empty($_GET['tag_name']) && !in_array($_GET['tag_name'], $tags)) {
      if (file_exists($file['tags'])) {

        $tags_file = file($file['tags']);
        $tags_file_lines = '';
        foreach ($tags_file as $single_line) {
          $tag_data = explode(DELIMITER, $single_line);
          if (substr($tag_data[0], 0, 2) == '<?') $auto_increment_id = trim($tag_data[1]);
          else $tags_file_lines .= $single_line;
        }

      }

      else {
        $auto_increment_id = 1;
        $tags_file_lines = '';
      }

      $tags_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $tags_file_lines;
      $tags_file_content .= $auto_increment_id . DELIMITER . $_GET['tag_name'] . DELIMITER . friendly_url($_GET['tag_name']) . "\n";

      if (mn_put_contents($file['tags'], $tags_file_content)) {
        echo '<li><input type="checkbox" name="tags[]" id="tag-' . $auto_increment_id . '" class="input-tag" value="' . $auto_increment_id . '" checked="checked" /> <label for="tag-' . $auto_increment_id . '">' . substr($_GET['tag_name'], 0, 20) . '</label></li>';
        die();
      }
      else echo 'Error MN#44: cannot write to mn-tags.php file!';
      die();
    }
    
    else echo '';
    die();
  }

  
  
  
  
  
  if (isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    $var = get_values('tags', $_REQUEST['id']);
  }





  if (isset($_POST['action']) && $_POST['action'] == 'add') {

    if (!empty($_POST['tag_name'])) {

      if (file_exists($file['tags'])) {

        $tags_file = file($file['tags']);
        $tags_file_lines = '';
        foreach ($tags_file as $single_line) {
          $tag_data = explode(DELIMITER, $single_line);
          if (substr($tag_data[0], 0, 2) == '<?') $auto_increment_id = trim($tag_data[1]);
          else $tags_file_lines .= $single_line;
        }

      }

      else $auto_increment_id = 1;

      $tags_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $tags_file_lines;
      $tags_file_content .= $auto_increment_id . DELIMITER . $_POST['tag_name'] . DELIMITER . friendly_url($_POST['tag_name']) . "\n";

      if (mn_put_contents($file['tags'], $tags_file_content)) {
        header('location: ./mn-tags.php?back=added');
        exit;
      }
      else overal_header($lang['tags_tags'], $lang['tags_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['tags_tags'], $lang['tags_msg_empty_tag_name'], 'error');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && file_exists($file['tags'])) {

    if (!empty($_POST['tag_name'])) {

      $tags_file = file($file['tags']);
      $tags_file_content = '';

      foreach ($tags_file as $single_line) {
        $tag_data = explode(DELIMITER, $single_line);
        if ($tag_data[0] == $_POST['id']) $tags_file_content .= $tag_data[0] . DELIMITER . $_POST['tag_name'] . DELIMITER . friendly_url($_POST['tag_name']) . "\n";
        else $tags_file_content .= $single_line;
      }

      if (mn_put_contents($file['tags'], $tags_file_content)) {
        header('location: ./mn-tags.php?back=edited');
        exit;
      }
      else overal_header($lang['tags_tags'], $lang['tags_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['tags_tags'], $lang['tags_msg_empty_tag_name'], 'error');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && file_exists($file['tags']) && !empty($var['tag_name'])) {
    overall_header($lang['tags_edit_tag'] . ' &raquo; ' . $var['tag_name'], $lang['tags_edit_tag'], 'main');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && file_exists($file['tags']) && !empty($var['tag_name'])) {
    $admin_tmpl['tags_main'] = false;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && file_exists($file['tags'])) {

    $tags_file = file($file['tags']);
    $tags_file_content = '';

    foreach ($tags_file as $single_line) {
      $tag_data = explode(DELIMITER, $single_line);
      if ($tag_data[0] == $_POST['id']) continue;
      else $tags_file_content .= $single_line;
    }

    if (mn_put_contents($file['tags'], $tags_file_content)) {
      header('location: ./mn-tags.php?back=deleted');
      exit;
    }
    else overal_header($lang['tags_tags'], $lang['tags_msg_put_contents_error'], 'error');

  }





  else {
    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['tags_tags'], $lang['tags_msg_tag_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['tags_tags'], $lang['tags_msg_tag_deleted'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['tags_tags'], $lang['tags_msg_tag_edited'], 'ok');
    else overall_header($lang['tags_tags'], $lang['tags_tags'], 'main');
    $var['action'] = 'add';
  }





  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') ? 'edit' : 'add';

  if (isset($admin_tmpl['tags_main']) && $admin_tmpl['tags_main']) {
?>

  <div id="category-add-edit">
    <form action="./mn-tags.php" method="post" id="category-add-edit">
      <label for="tag_name"><?php echo $lang['tags_tag_name'];?>:</label> <input type="text" class="text" name="tag_name" id="tag_name" value="<?php echo @$var['tag_name'];?>" />
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php if (isset($var['action']) && $var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['tag_id'] . '" />';?>
      <button type="submit" name="aaa"><img src="./stuff/img/icons/tag-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['tags_' . $var['action'] . '_tag'];?></button>
      <?php echo ($var['action'] == 'edit') ? ' <a href="./mn-tags.php" class="cancel">' . $lang['uni_cancel'] . '</a>' : '';?>
      
    </form>
  </div>


  <?php if (file_exists($file['tags']) && !empty($tags)) { ?>
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
  <table id="tags-list" class="tablesorter minor-list">
    <thead>
      <tr><th id="minor_id" class="l num">id</th><th id="minor_name"><?php echo $lang['tags_tag'];?></th><th id="minor_count" class="num"><img src="./stuff/img/icons/posts.png" alt="-" class="tooltip" title="<?php echo $lang['tags_posts_count'];?>" /></th></tr>
    </thead>
    <tbody>
      <?php
        foreach ($tags as $tag_id => $tag_name) {
          $tag_posts_count = (empty($posts_count[$tag_id])) ? '<em class="trivial">0</em>' : '<a href="./mn-posts.php?t=' . $tag_id . '">' . $posts_count[$tag_id] . '</a>';
          echo '<tr><td class="c">' . $tag_id . '</td><td><a href="./mn-tags.php?action=edit&amp;id=' . $tag_id . '" class="main-link">' . $tag_name . '</a><br />&nbsp;<span class="links hide"><a href="./mn-tags.php?action=edit&amp;id=' . $tag_id . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-tags.php?action=delete&amp;id=' . $tag_id . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td class="c">' . $tag_posts_count . '</td></tr>';
        }
      ?>
    </tbody>
  </table>
  <?php } ?>

  <p class="cleaner">&nbsp;</p>

<?php
  }
  else {
?>

  <form action="./mn-tags.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['tags_q_really_delete'];?>: <strong><?php echo $var['tag_name'];?></strong>?<br />
      <?php
        if (isset($posts_count[$var['tag_id']]) && $posts_count[$var['tag_id']] == 1) $msg_num = 1;
        elseif (isset($posts_count[$var['tag_id']]) && $posts_count[$var['tag_id']] > 1 && $posts_count[$var['tag_id']] < 5) $msg_num = 2;
        elseif (isset($posts_count[$var['tag_id']]) && $posts_count[$var['tag_id']] > 4) $msg_num = 3;
        else $msg_num = 0;
        echo '<em>' . str_replace('%n%', '<strong>' . @$posts_count[$var['tag_id']] . '</strong>', $lang['tags_msg_posts_number_' . $msg_num]) . '</em>';
      ?>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['tag_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['tags_delete_tag'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  overall_footer();



  ##### I λ ########################################################################################
  ##### http://youtu.be/nTbL5elVXrU ################################################################

?>