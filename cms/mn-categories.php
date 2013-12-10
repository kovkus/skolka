<?php
  include './stuff/inc/mn-start.php';
  $auth = user_auth('2');
  $admin_tmpl['cats_main'] = true;


  $categories = load_basic_data('categories');
  if (file_exists($file['categories_order'])) {
  	$categories_order = unserialize(file_get_contents($file['categories_order']));
  }
  else {
  	$categories_order = array();
  	$i = 1;
  	foreach ($categories as $id => $cname) {$categories_order[$i] = $id; $i++;}
  	
  	mn_put_contents($file['categories_order'], serialize($categories_order));
  }
  $posts_count = get_posts_count();



  if (isset($_REQUEST['action']) && isset($_REQUEST['id'])) {
    $var = get_values('categories', $_REQUEST['id']);
  }





  if (isset($_GET['action']) && $_GET['action'] == 'ajaxcall') {
    if (isset($_GET['cat_name']) && !empty($_GET['cat_name']) && !in_array($_GET['cat_name'], $categories)) {
      if (file_exists($file['categories'])) {

        $cats_file = file($file['categories']);
        $cats_file_lines = '';
        foreach ($cats_file as $single_line) {
          $cat_data = explode(DELIMITER, $single_line);
          if (substr($cat_data[0], 0, 2) == '<?') $auto_increment_id = trim($cat_data[1]);
          else $cats_file_lines .= $single_line;
        }

      }

      else {
        $auto_increment_id = 1;
        $cats_file_lines = '';
      }

      $cats_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $cats_file_lines;
      $cats_file_content .= $auto_increment_id . DELIMITER . $_GET['cat_name'] . DELIMITER . friendly_url($_GET['cat_name']) . "\n";

      if (mn_put_contents($file['categories'], $cats_file_content)) {
        echo '<option value="' . $auto_increment_id . '" selected="selected">' . $_GET['cat_name'] . '</option>';
        
        if (file_exists($file['categories_order'])) {
        	$order_arr = unserialize(file_get_contents($file['categories_order']));
        	array_push($order_arr, $auto_increment_id);
        	file_put_contents($file['categories_order'], serialize($order_arr));
        }
        die();
      }
      else die('Error MN#33: cannot write to mn-categories.php file!');

    }

    else die();
  }





	elseif (isset($_GET['action']) && $_GET['action'] == 'cat_order') {

		if (isset($_GET['categories-list']) && is_array($_GET['categories-list'])) {
		
			$order = array();
			foreach ($_GET['categories-list'] as $n => $cat_id) {
				if (empty($cat_id)) continue;
				else {
					$order[$n] = $cat_id;
				}
			}
		
		}

		mn_natcasesort($order);
		mn_put_contents($file['categories_order'], serialize($order));
		exit;

	}





  elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
  
    if (isset($_POST['cat_name']) && !empty($_POST['cat_name'])) {
    
      if (file_exists($file['categories'])) {
      
        $cats_file = file($file['categories']);
        $cats_file_lines = '';
        foreach ($cats_file as $single_line) {
          $cat_data = explode(DELIMITER, $single_line);
          if (substr($cat_data[0], 0, 2) == '<?') $auto_increment_id = trim($cat_data[1]);
          else $cats_file_lines .= $single_line;
        }
        
      }
      
      else $auto_increment_id = 1;
      
      $cats_file_content = SAFETY_LINE . DELIMITER . ($auto_increment_id + 1) . "\n" . $cats_file_lines;
      $cats_file_content .= $auto_increment_id . DELIMITER . $_POST['cat_name'] . DELIMITER . friendly_url($_POST['cat_name']) . "\n";
      
      if (mn_put_contents($file['categories'], $cats_file_content)) {
      	if (file_exists($file['categories_order'])) {
      		$order_arr = unserialize(file_get_contents($file['categories_order']));
      		array_push($order_arr, $auto_increment_id);
      		file_put_contents($file['categories_order'], serialize($order_arr));
      	}

        header('location: ./mn-categories.php?back=added');
        exit;
      }
      else overal_header($lang['cats_categories'], $lang['cats_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['cats_categories'], $lang['cats_msg_empty_cat_name'], 'error');
  }
  
  
  
  
  
  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && file_exists($file['categories'])) {

    if (!empty($_POST['cat_name'])) {

      $cats_file = file($file['categories']);
      $cats_file_content = '';

      foreach ($cats_file as $single_line) {
        $cat_data = explode(DELIMITER, $single_line);
        if ($cat_data[0] == $_POST['id']) $cats_file_content .= $cat_data[0] . DELIMITER . $_POST['cat_name'] . DELIMITER . friendly_url($_POST['cat_name']) . "\n";
        else $cats_file_content .= $single_line;
      }

      if (mn_put_contents($file['categories'], $cats_file_content)) {
        header('location: ./mn-categories.php?back=edited');
        exit;
      }
      else overal_header($lang['cats_categories'], $lang['cats_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['cats_categories'], $lang['cats_msg_empty_cat_name'], 'error');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && file_exists($file['categories']) && !empty($var['cat_name'])) {
    overall_header($lang['cats_edit_category'] . ' &raquo; ' . $var['cat_name'], $lang['cats_edit_category'], 'main');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && file_exists($file['categories']) && !empty($var['cat_name'])) {
    $admin_tmpl['cats_main'] = false;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && file_exists($file['categories'])) {

    $cats_file = file($file['categories']);
    $cats_file_content = '';

    foreach ($cats_file as $single_line) {
      $cat_data = explode(DELIMITER, $single_line);
      if ($cat_data[0] == $_POST['id']) continue;
      else $cats_file_content .= $single_line;
    }

    if (mn_put_contents($file['categories'], $cats_file_content)) {
    	if (file_exists($file['categories_order'])) {
    		$order_arr = unserialize(file_get_contents($file['categories_order']));
    		$new_order = array();
    		foreach ($order_arr as $n => $id) {
    			if ($id == $_POST['id']) continue;
    			else $new_order[$n] = $id;
    		}
    		file_put_contents($file['categories_order'], serialize($new_order));
    	}
    	
      	header('location: ./mn-categories.php?back=deleted');
      	exit;
    }
    else overal_header($lang['cats_categories'], $lang['cats_msg_put_contents_error'], 'error');

  }





  else {
    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['cats_categories'], $lang['cats_msg_category_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'canceled') overall_header($lang['cats_categories'], $lang['cats_msg_category_canceled'], 'info');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['cats_categories'], $lang['cats_msg_category_deleted'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['cats_categories'], $lang['cats_msg_category_edited'], 'ok');
    else overall_header($lang['cats_categories'], $lang['cats_categories'], 'main');
    $var['action'] = 'add';
  }




  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') ? 'edit' : 'add';
  
  if (isset($admin_tmpl['cats_main']) && $admin_tmpl['cats_main']) {
?>

  <div id="cat-add-edit">
    <form action="./mn-categories.php" method="post" id="category-add-edit">
      <label for="cat_name"><?php echo $lang['cats_cat_name'];?>:</label> <input type="text" class="text" name="cat_name" id="cat_name" value="<?php echo (isset($var['cat_name']) && !empty($var['cat_name'])) ? $var['cat_name'] : '';?>" />
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php if (isset($var['action']) && $var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['cat_id'] . '" />';?>
      <button type="submit" name="aaa"><img src="./stuff/img/icons/category-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['cats_' . $var['action'] . '_category'];?></button>
      <?php echo ($var['action'] == 'edit') ? ' <a href="./mn-categories.php" class="cancel">' . $lang['uni_cancel'] . '</a>' : '';?>
    </form>
  </div>


  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
  <table id="categories-list" class="tablesorter minor-list">
    <thead>
      <tr class="nodrop nodrag"><th id="minor_id" class="l num">id</th><th id="minor_name"><?php echo $lang['cats_category'];?></th><th id="minor_count" class="num"><img src="./stuff/img/icons/posts.png" alt="-" class="tooltip" title="<?php echo $lang['cats_posts_count'];?>" /></th><th></th></tr>
    </thead>
    <tbody>
      <?php
        if (!empty($categories)) {
          foreach ($categories_order as $cat_order => $cat_id) {
            $cat_posts_count = (empty($posts_count[$cat_id])) ? '<em class="trivial">0</em>' : '<a href="./mn-posts.php?c=' . $cat_id . '">' . $posts_count[$cat_id] . '</a>';
            echo '<tr id="' . $cat_id . '"><td class="c">' . $cat_id . '</td><td><a href="./mn-categories.php?action=edit&amp;id=' . $cat_id . '" class="main-link">' . $categories[$cat_id] . '</a><br />&nbsp;<span class="links hide"><a href="./mn-categories.php?action=edit&amp;id=' . $cat_id . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-categories.php?action=delete&amp;id=' . $cat_id . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td class="c">' . $cat_posts_count . '</td><td class="dragHandle"></td></tr>';
            
            unset($categories[$cat_id]);
          }
        }
        $uncategorized_posts_count = (empty($posts_count['-1'])) ? '<em class="trivial">0</em>' : '<a href="./mn-posts.php?c=-1">' . $posts_count['-1'] . '</a>';
        echo '<tr class="nodrop nodrag"><td class="c trivial">-1</td><td><span class="trivial">' . $lang['cats_uncategorized'] . '</span><br />&nbsp;</td><td class="c">' . $uncategorized_posts_count . '</td><td></td></tr>';
      ?>
    </tbody>
  </table>
  
  <p class="cleaner">&nbsp;</p>

<?php
  }
  else {
?>

  <form action="./mn-categories.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['cats_q_really_delete'];?>: <strong><?php echo $var['cat_name'];?></strong>?<br />
      <?php
        if ($posts_count[$var['cat_id']] == 1) $msg_num = 1;
        elseif ($posts_count[$var['cat_id']] > 1 && $posts_count[$var['cat_id']] < 5) $msg_num = 2;
        elseif ($posts_count[$var['cat_id']] > 4) $msg_num = 3;
        else $msg_num = 0;
        echo '<em>' . str_replace('%n%', '<strong>' . $posts_count[$var['cat_id']] . '</strong>', $lang['cats_msg_posts_number_' . $msg_num]) . '</em>';
      ?>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['cat_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['cats_delete_category'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  overall_footer();
?>