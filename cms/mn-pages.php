<?php
  include './stuff/inc/mn-start.php';
  $auth = user_auth('4');

  if (!isset($_COOKIE["TinyMCE_text_size"])) {setcookie("TinyMCE_text_size", "cw=810&ch=300", time()+60*60*24*365);}
  if (!file_exists('./' . $file['pages'])) {

  	$p_dir = dir($dir['pages']);
  	$pages = array();

  	while ($p_file = $p_dir->read()) {
  	  if (!is_file($dir['pages'] . $p_file)) continue;
  	  else {
  	    $var = get_page_data($p_file, false);
  	    if(isset($var['author']) && !empty($var['author'])) $pages[$var['id']] = array('id' => $var['id'], 'timestamp' => $var['timestamp'], 'title' => $var['title'], 'friendly_url' => $var['friendly_url'], 'author' => $var['author'], 'parent_id' => 0, 'order' => $var['order']);
  	    else continue;
  	  }
  	}

  	mn_put_contents($file['pages'], DIE_LINE . serialize($pages));

  }





  if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $var['title'] = '';
    $var['text'] = '';
    $var['xfields_array'] = array();
    overall_header($lang['pages_add_new_page'], $lang['pages_add_new_page'], 'main', true);
    $admin_tmpl['form'] = true;
  }

  
  
  
  
  elseif (isset($_POST['action']) && $_POST['action'] == 'add') {
    if (!empty($_POST['title']) && !empty($_POST['text'])) {
    
      $p_pass = ($_POST['pass-conf'] && !empty($_POST['pass'])) ? sha1($_POST['pass']) : '';
      $p_order = ($_POST['visible'] == '1') ? $_POST['order'] : '0';
      $p_id = trim(file_get_contents($file['id_pages']));
      
      
      // xFields
      if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
      
      	$xfields = get_unserialized_array('xfields');
      	$post_xfields = array();
      	foreach ($xfields as $xVar => $x) {
      		if ($x['section'] != 'pages') continue;
      		else {
      			$post_xfields[$xVar] = check_text($_POST['x' . $xVar], true, 'xf');
      		}
      	}
      	
      	$xfields_serialized = serialize($post_xfields);
      
      }
      else $xfields_serialized = '';
      
      
      $p_content = SAFETY_LINE . "\n" . DELIMITER . $p_id . DELIMITER . mn_time() . DELIMITER . check_text($_POST['title']) . DELIMITER . friendly_url($_POST['title']) . DELIMITER . $_SESSION['mn_user_id'] . DELIMITER . $_POST['visible'] . DELIMITER . $p_order . DELIMITER . $p_pass . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . "\n" . check_text($_POST['text']);
      
      if (mn_put_contents($dir['pages'] . 'page_' . $p_id . '.php', $p_content)) {
        mn_put_contents($file['id_pages'], $p_id + 1);
        header('location: ./mn-pages.php?back=added');
        exit;
      }
      else overall_header($lang['pages_add_new_page'], $lang['pages_msg_put_contents_error'], 'error', true);

    }
    else {
      $var['title'] = check_text($_POST['title']);
      $var['text'] = check_text($_POST['text']);
      $var['visible'] = $_POST['visible'];
      overall_header($lang['pages_add_new_page'], $lang['pages_msg_empty_values'], 'error', true);
      $admin_tmpl['form'] = true;
    }
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && file_exists($dir['pages'] . 'page_' . $_GET['id'] . '.php')) {
    $var = get_page_data($_GET['id']);
    $var['xfields_array'] = unserialize($var['xfields']);
    if (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['pages_pages'], $lang['pages_msg_page_edited'], 'ok', true);
    else overall_header($lang['pages_edit_page'] . ' &raquo; ' . $var['title'], $lang['pages_edit_page'] . ' &raquo; ' . $var['title'], 'main', true);
    $admin_tmpl['form'] = true;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && file_exists($dir['pages'] . 'page_' . $_POST['id'] . '.php')) {

    if (!empty($_POST['title']) && !empty($_POST['text'])) {
      $var = get_page_data($_POST['id']);
      
      if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
        header('location: ./?access-denied');
        exit;
      }

      if (isset($_POST['pass-conf']) && $_POST['pass-conf'] == '1') {
        $p_pass = (!empty($_POST['pass'])) ? sha1($_POST['pass']) : $var['pass'];
      }
      else {
        $p_pass = '';
      }
      $p_order = ($_POST['visible'] == '1') ? $_POST['order'] : '0';
      
      
      // xFields
      if (isset($_POST['x_fields']) && file_exists(MN_ROOT . $file['xfields'])) {
      
      	$xfields = get_unserialized_array('xfields');
      	$post_xfields = array();
      	foreach ($xfields as $xVar => $x) {
      		if ($x['section'] != 'pages') continue;
      		else {
      			$post_xfields[$xVar] = check_text($_POST['x' . $xVar], true, 'xf');
      		}
      	}
      	
      	$xfields_serialized = serialize($post_xfields);
      
      }
      else $xfields_serialized = '';
      

      $p_content = SAFETY_LINE . "\n" . DELIMITER . $_POST['id'] . DELIMITER . mn_time() . DELIMITER . check_text($_POST['title']) . DELIMITER . friendly_url($_POST['title']) . DELIMITER . $var['author'] . DELIMITER . $_POST['visible'] . DELIMITER . $p_order . DELIMITER . $p_pass . DELIMITER . '' . DELIMITER . $xfields_serialized . DELIMITER . "\n" . check_text($_POST['text']);

      if (mn_put_contents($dir['pages'] . 'page_' . $_POST['id'] . '.php', $p_content)) {
        header('location: ./mn-pages.php?action=edit&id=' . $_POST['id'] . '&back=edited');
        exit;
      }
      else overall_header($lang['pages_edit_page'] . ' &raquo; ' . $var['title'], $lang['pages_msg_put_contents_error'], 'error', true);

    }

    else {
      $var['title'] = check_text($_POST['title']);
      $var['text'] = check_text($_POST['text']);
      $var['id'] = check_text($_POST['id']);
      overall_header($lang['pages_edit_page'] . ' &raquo; ' . $var['title'], $lang['pages_msg_empty_values'], 'error', true);
      $admin_tmpl['form'] = true;
    }

  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && file_exists($dir['pages'] . 'page_' . $_GET['id'] . '.php')) {
    $var = get_page_data($_GET['id']);
    if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
      header('location: ./?access-denied');
      exit;
    }
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && file_exists($dir['pages'] . 'page_' . $_POST['id'] . '.php')) {
    $var = get_page_data($_POST['id']);

    if ($auth != 1 && $var['author'] != $_SESSION['mn_user_id']) {
      header('location: ./?access-denied');
      exit;
    }

    else {
      unlink($dir['pages'] . 'page_' . $_POST['id'] . '.php');
      if (!file_exists($dir['pages'] . 'page_' . $_POST['id'] . '.php')) {
        header('location: ./mn-pages.php?back=deleted');
        exit;
      }
      else {
        header('location: ./mn-pages.php?back=error');
        exit;
      }
    }
  }





  else {
    $p_dir = dir($dir['pages']);
    $pages = array(); $p_timestamps = array(); $p_result = '';

    while ($p_file = $p_dir->read()) {
      if (!is_file($dir['pages'] . $p_file)) continue;
      else {
        $temp_var = get_page_data($p_file, false);

        if ($auth != 1 && $temp_var['author'] != $_SESSION['mn_user_id']) continue;
        else {
          if (isset($_GET['a']) && !empty($_GET['a']) && $temp_var['author'] != $_GET['a']) continue;
          if (isset($_GET['m']) && !empty($_GET['m']) && $_GET['m'] == '1' && $temp_var['visible'] != '1') continue;
          if (isset($_GET['m']) && !empty($_GET['m']) && $_GET['m'] == '2' && $temp_var['visible'] != '0') continue;
          if (isset($_GET['d']) && !empty($_GET['d']) && date('Y-m', $temp_var['timestamp']) != $_GET['d']) continue;

          if (isset($_GET['action']) && $_GET['action'] == 'reorder') {
            if ($temp_var['visible'] == '1') $pages[$temp_var['id']] = $temp_var['order'];
            else continue;
          }
          else {
            $pages[$temp_var['id']] = $temp_var['friendly_url'];
            $p_timestamps[$temp_var['timestamp']] = date('Y-m', $temp_var['timestamp']);
          }
        }
      }
    }
    
    if (!empty($pages)) {
      $pages = mn_natcasesort($pages);
      $p_timestamps = array_unique($p_timestamps);
      $author = load_basic_data('users');
      $pages_result = '';

      foreach ($pages as $key => $value) {
        $var = get_page_data($key);
        
        if (isset($_GET['action']) && $_GET['action'] == 'reorder') {
          $pages_result .= '<li id="item_' . $var['id'] . '">' . $var['title'] . '</li>';
        }
        else {
          $pass_img = (!empty($var['pass'])) ? '<img src="./stuff/img/icons/key-gray.png" alt="" width="16" height="16" class="tooltip" title="' . $lang['pages_protected_page'] . '" />' : '';
          $status_img = ($var['visible'] == 1) ? '<img src="./stuff/img/icons/tick-gray.png" alt="" width="16" height="16" class="tooltip" title="' . $lang['uni_yes'] . '" />' : '<img src="./stuff/img/icons/cross-gray.png" alt="" width="16" height="16" class="tooltip" title="' . $lang['uni_no'] . '" />';
          $order_num = ($var['order'] > 0) ? $var['order'] : '';
          
          $page_author = (empty($author[$var['author']])) ? '<em class="trivial">' . $lang['uni_anonym'] . ' ' . $var['author'] . '</em>' : $author[$var['author']];

          $pages_result .= '<tr><td class="c">' . $var['id'] . '</td><td><a href="./mn-pages.php?action=edit&amp;id=' . $var['id'] . '" class="main-link">' . $var['title'] . '</a> ' . $pass_img . '<br />&nbsp;<span class="links hide"><a href="./mn-pages.php?action=edit&amp;id=' . $var['id'] . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-pages.php?action=delete&amp;id=' . $var['id'] . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td>' . $page_author . '</td><td class="c">' . $status_img . '</td><td class="c">' . $order_num . '</td><td>' . date('d.m.Y', $var['timestamp']) . '<br />' . date('H:i', $var['timestamp']) . '</td></tr>';
        }
      }
    }


    $admin_tmpl['list'] = true;

    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['pages_pages'], $lang['pages_msg_page_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['pages_pages'], $lang['pages_msg_page_deleted'], 'ok');
    else overall_header($lang['pages_pages'], $lang['pages_pages'], 'main');
  }




  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') ? 'add' : 'edit';
  if (isset($admin_tmpl['form']) && $admin_tmpl['form']) {
?>

  <form action="./mn-pages.php" method="post" id="pages-add-edit" class="p-form">
    <fieldset>

      <p class="l">
        <label for="title" id="for-title"><img src="./stuff/img/icons/title.png" alt="" width="16" height="16" /> <?php echo $lang['pages_title'];?> <span class="required">*</span></label> <input type="text" name="title" id="title" class="text" autocomplete="off" value="<?php echo $var['title'];?>" maxlength="65" />
      </p>


      <p class="ta-description">
      	<?php if ($conf['admin_wysiwyg']) { ?>
      	<span class="wysiwyg-toggle w2">
      		<span class="fancy mce-add-image full_story tooltip" rel="text" title="<?php echo $lang['posts_insert_image_file'];?>"><img src="./stuff/img/icons/image-add.png" alt="-" width="16" height="16" /> <span class="simurl"><?php echo $lang['posts_insert_upload'];?></span></span>
      		<span class="mce-wysiwyg" rel="text"><img src="./stuff/img/icons/html.png" alt="html" class="tooltip" title="<?php echo $lang['posts_wysiwyg_toggle'];?>" /></span>
      	</span>
      	<?php } ?>
      	<label for="text"><img src="./stuff/img/icons/page-text.png" alt="-" width="16" height="16" /> <?php echo $lang['pages_text'];?> <span class="required">*</span></label>
      </p>
      <div class="ta-wrap">
        <?php if (!$conf['admin_wysiwyg']) {$textarea_id = 'text'; include "./stuff/inc/tmpl/wysiwyg-false.php";}?>
        <textarea name="text" id="text" class="tinymce" tabindex="3" rows="5" cols="60"><?php echo check_text($var['text']);?></textarea>
      </div>
      
      <?php
      	
      		if (file_exists(MN_ROOT . $file['xfields'])) {
      		
      			$xfields = get_unserialized_array('xfields');
      			$xfields_rows = '';
      			foreach ($xfields as $xVar => $x) {
      				if ($x['section'] != 'pages') continue;
      				else {
      				
      					$thisVar = (isset($_POST['x' . $xVar])) ? check_text($_POST['x' . $xVar], true, false, 'xf') : @$var['xfields_array'][$xVar];
      					if (isset($x['type']) && $x['type'] == 'select') {
      						$xField = '<select name="x' . $xVar . '" id="x' . $xVar . '" class="long">';
      						foreach ($x['options'] as $oKey => $oValue) {
      							$sel = ($thisVar == $oKey) ? ' selected="selected"' : '';
      							$xField .= '<option value="' . $oKey . '"' . $sel . '>' . $oValue . '</option>';
      						}
      						$xField .= '</select>';
      					}
      					else {
      						$xField = '<input type="text" name="x' . $xVar . '" id="x' . $xVar . '" value="' . $thisVar . '" class="text" />';
      					}
      				
      					$xfields_rows .= '<tr><td class="r"><label for="x' . $x['var'] . '">' . $x['name'] . ':</label></td><td>' . $xField . '</td></tr>';
      				}
      			}
      		
      		}
      		
      		if (!empty($xfields_rows)) {
      		
      			echo '<div id="xfields"><div class="hide round" id="xfields-in"><strong>' . $lang['xfields_xfields'] . '</strong><table>';
      			echo $xfields_rows;
      			echo '</table><input type="hidden" name="x_fields" value="true" /></div></div>';
      		
      		}
      
      	?>
      
      <div id="settings"><div class="hide round" id="settings-in">
        <table class="page-info">
        <tr>
          <td class="labels"><label for="title"><img src="./stuff/img/icons/visible.png" alt="" width="16" height="16" /> <?php echo $lang['pages_show_in_menu'];?></label></td>
          <td class="inputs">
            <ul>
            <li><input type="radio" name="visible" id="visible1" value="1"<?php if (isset($var['visible']) && $var['visible'] == '1') echo ' checked="checked"';?> />&nbsp;<label for="visible1"><?php echo $lang['uni_yes'];?></label></li>
            <li><input type="radio" name="visible" id="visible2" value="0"<?php if (!isset($var['visible']) || empty($var['visible']) || ($var['visible'] == '0')) echo ' checked="checked"';?> />&nbsp;<label for="visible2"><?php echo $lang['uni_no'];?></label></li>
            </ul>
          </td>
        </tr>
        <tr>
          <td><label for="title"><img src="./stuff/img/icons/order.png" alt="" width="16" height="16" /> <?php echo $lang['pages_order'];?></label></td>
          <td><select name="order"><option value="0">--</option><?php for ($i=1;$i<=20;$i++) {$sel = ($var['order'] == $i) ? ' selected="selected"' : ''; echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';}?></select></td>
        </tr>
        <tr>
          <td class="labels"><label for="title"><img src="./stuff/img/icons/key.png" alt="" width="16" height="16" /> <?php echo $lang['pages_password_required'];?></label></td>
          <td class="inputs">
            <ul>
            <li id="page-pass-off"><input type="radio" name="pass-conf" id="pass-conf1" value="0"<?php if (empty($var['pass'])) echo ' checked="checked"';?> />&nbsp;<label for="pass-conf1"><?php echo $lang['uni_no'];?></label></li>
            <li id="page-pass-on"><input type="radio" name="pass-conf" id="pass-conf2" value="1"<?php if (!empty($var['pass'])) echo ' checked="checked"';?> />&nbsp;<label for="pass-conf2"><?php echo $lang['uni_yes'];?><?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($var['pass'])) echo ' (<span class="simurl">' . $lang['pages_change_pass'] . '</span>)';?></label></li>
            <li id="page-pass" class="hide"><label for="pass"><?php echo $lang['pages_pass'];?>:</label>&nbsp;<input type="password" name="pass" id="pass" class="medium" /></li>
            </ul>
          </td>
        </tr>
        </table>
      </div></div>
      
      <div id="info"><div id="info-in" class="hide round">
      
        <?php
          if (!empty($var['pass'])) {
            $page_key = substr(md5(substr($var['pass'], 1, 22) . $var['id'] . date('d.m.Y')), 6, 9);
            echo '<strong><img src="./stuff/img/icons/key-gray.png" alt="" width="16" height="16" /> ' . $lang['pages_info_key'] . ':</strong> <span class="example">' . $page_key . '</span>';
            echo '<p class="j">' . $lang['pages_info_key_text'] . '</p><p class="example">http://' . $_SERVER['SERVER_NAME'] . '/?mn_page=' . $var['id'] . '&amp;mn_key=' . $page_key . '</p>';
          }
        ?>

        <strong><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" /> <?php echo $lang['pages_info_pageview'];?>:</strong>
        <?php
          echo '<p class="j">' . str_ireplace('%id%', $var['id'], $lang['pages_info_pageview_text1']) . '</p>';
          echo '<p class="example">http://' . $_SERVER['SERVER_NAME'] . '/?mn_page=' . $var['id'] . '</p>';
          echo '<p>' . $lang['pages_info_pageview_text2'] . '</p>';
        ?>
        <textarea class="integration" readonly="readonly">&lt;?php
  $mn_page = <?php echo $var['id'];?>;
  include './mnews/mn-show.php';
?&gt;</textarea>
      </div></div>
      
      <p class="r">
      	<?php if (!empty($xfields_rows)) echo '<span class="simurl toggle" rel="xfields-in"><img src="./stuff/img/icons/textfield.png" alt="-" width="16" height="16" /> ' . $lang['xfields_xfields'] . '</span> | ';?>
        <?php if ($var['action'] == 'edit') { ?><span class="simurl toggle750" rel="info-in"><img src="./stuff/img/icons/information.png" alt="-" width="16" height="16" /> <?php echo $lang['pages_info'];?></span> |<?php } ?>
        <span class="simurl toggle750" rel="settings-in"><img src="./stuff/img/icons/settings.png" alt="-" width="16" height="16" /> <?php echo $lang['pages_settings'];?></span>
      </p>
      
      <p class="c">
        <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
        <?php if ($var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['id'] . '" />';?>
        <button type="submit" name="<?php echo $var['action'];?>"><img src="./stuff/img/icons/page-<?php echo $var['action'];?>.png" alt="" width="16" height="16" /> <?php echo $lang['pages_' . $var['action'] . '_page'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  }

  elseif (isset($admin_tmpl['list']) && $admin_tmpl['list']) {
?>

  <div class="simbutton fl"><a href="./mn-pages.php?action=add"><img src="./stuff/img/icons/page-add.png" alt="" width="16" height="16" /> <?php echo $lang['pages_add_page'];?></a></div>
  
  <?php
    if (!empty($pages_result)) {
  ?>
  <div class="rel-links">
    <!--<a href="./mn-pages.php?action=reorder&amp;iframe" class="simurl fancy"><img src="./stuff/img/icons/order.png" alt="" /> <?php echo $lang['pages_reorder_pages'];?></a> |-->
    <?php echo (empty($_GET['a']) && empty($_GET['m']) && empty($_GET['d'])) ? '<span class="simurl" id="filter-viewer"> <img src="./stuff/img/icons/view-settings.png" alt="" width="16" height="16" /> ' . $lang['pages_filter_settings'] . '</span>' : '<a href="./mn-pages.php" class="custom"><img src="./stuff/img/icons/view-settings-cancel.png" alt="" width="16" height="16" /> ' . $lang['pages_filter_cancel'] . '</a>';?>
  </div>
  
  <?php $class = (empty($_GET['a']) && empty($_GET['m']) && empty($_GET['d'])) ? ' hide' : '';?>
  <p class="cleaner">&nbsp;</p>

  <form action="./mn-pages.php" method="get" class="filter<?php echo $class;?>">
    <select name="a">
      <option value="" class="description">--- <?php echo $lang['pages_all_users'];?> ---</option>
      <?php
        $users = load_basic_data('users');
        if (!empty($users)) {
          foreach ($users as $user_id => $user_name) {
            $sel = (isset($_GET['a']) && $user_id == $_GET['a']) ? ' selected="selected" class="selected"' : '';
            echo '<option value="' . $user_id . '"' . $sel . '>' . $user_name . '</option>';
          }
        }
      ?>
    </select>
    
    <select name="m">
      <option value="" class="description">--- <?php echo $lang['pages_all_menu_status'];?> ---</option>
      <option value="1"<?php if (isset($_GET['m']) && $_GET['m'] == '1') echo ' selected="selected"';?>><?php echo $lang['pages_menu_visible'];?></option>
      <option value="2"<?php if (isset($_GET['m']) && $_GET['m'] == '2') echo ' selected="selected"';?>><?php echo $lang['pages_menu_invisible'];?></option>
    </select>
    
    <select name="d">
    <option value="" class="description">--- <?php echo $lang['pages_all_dates'];?> ---</option>
    <?php
      foreach ($p_timestamps as $key => $value) {
        $sel = (isset($_GET['d']) && $value == $_GET['d']) ? ' selected="selected" class="selected"' : '';
        echo '<option value="' . $value . '"' . $sel . '>' . $lang['month'][date('n', $key)] . ' ' . date('Y', $key) . '</option>';
      }
    ?>
    </select>
    
    <input type="submit" class="submit" value="<?php echo $lang['pages_filter'];?>" />
  
  </form>

  <table id="pages-list" class="tablesorter">
  <thead><tr><th id="cell-id" class="l num">id</th><th id="cell-page"><?php echo $lang['pages_page'];?></th><th id="cell-author"><?php echo $lang['pages_author'];?></th><th id="cell-visible"><img src="./stuff/img/icons/eye-gray.png" alt="" class="tooltip" title="<?php echo $lang['pages_visible'];?>" /></th><th id="cell-order"><img src="./stuff/img/icons/sitemap.png" alt="" class="tooltip" title="<?php echo $lang['pages_order'];?>" /></th><th id="cell-date" class="date"><?php echo $lang['pages_date'];?></th></tr></thead>
  <tbody><?php echo $pages_result;?></tbody>
  </table>
  
  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
  
  <?php
    }
    else echo '<p class="no-values cb"><img src="./stuff/img/icons/information.png" alt="(i)" /> ' . $lang['pages_msg_no_pages'] . '</p>';

  }
  
  elseif (isset($_GET['action']) && $_GET['action'] == 'reorder') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="sk" xml:lang="sk">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="./stuff/etc/main.css" media="all" />
  <link rel="stylesheet" type="text/css" href="./stuff/themes/<?php echo $conf['admin_theme'];?>/style.css" media="all" />
  <script type="text/javascript" src="./stuff/etc/jquery-min.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-disable-text-select-pack.js"></script>
  <script type="text/javascript" src="./stuff/etc/jquery-listreorder.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      var options = {
    		itemHoverClass : 'reorder_hover',
    		dragTargetClass : 'reorder_drag',
    		dropTargetClass : 'reorder_drop',
    		useDefaultDragHandle : false
    	};

    	var lists = $('#reorder_list').ListReorder(options);
    	
    	lists.bind('listorderchanged', function(evt, jqList, listOrder) {
    		var str = jqList.attr('id') + " order changed: \n"
    				+ "\tcurrent -- original\n";
    		for (var i = 0; i < listOrder.length; i++)
    			str += "\t" + i + " -- " + listOrder[i] + "\n";

    		$('#example2out').html(str);
    	});
    });
  </script>

  <title>MNews | Stránky</title>
</head>

<body id="reorder_page">

  <div class="i-main"><?php echo $lang['pages_reorder_pages'];?></div>
  
  <ul id="reorder_list" class="lists">
    <?php echo $pages_result;?>
  </ul>
  
  <pre id="example2out">Event Handler Output</pre>
  
  <p class="help"><?php echo $lang['pages_reorder_pages_help'];?></p>
  
</body>
</html>

<?php
die();
}
  else {
?>

  <form action="./mn-pages.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['pages_q_really_delete'];?>: <strong><?php echo $var['title'];?></strong>?
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['pages_delete_page'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }


  overall_footer();
?>