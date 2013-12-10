<?php
  include './stuff/inc/mn-start.php';

  $auth = user_auth('5');
  $admin_tmpl['folders_main'] = true;


  $folders = get_unserialized_array('folders');
  $files_count = get_files_count('folders');





  if (isset($_POST['action']) && $_POST['action'] == 'add') {
  
    if (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
    
     
      $auto_id = (file_exists($file['id_folders'])) ? trim(file_get_contents($file['id_folders'])) : 1;
      $folder_parent = (isset($_POST['folder_parent'])) ? (int)$_POST['folder_parent'] : 0;



      $folders[$auto_id] = array(
      	'name' => check_text($_POST['folder_name'], true),
      	'parent_id' => $folder_parent,
      );


      if (mn_put_contents($file['folders'], DIE_LINE . serialize($folders))) {
      	mn_put_contents($file['id_folders'], ($auto_id+1));
        header('location: ./mn-folders.php?back=added&f=' . $auto_id);
        exit;
      }
      else overal_header($lang['folders_folders'], $lang['folders_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['folders_folders'], $lang['folders_msg_empty_folder_name'], 'error');
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && array_key_exists($_POST['id'], $folders)) {

    if (!empty($_POST['folder_name'])) {

      $folders[$_POST['id']] = array(
      	'name' => check_text($_POST['folder_name'], true),
      	'parent_id' => (int)$_POST['folder_parent']
      );

      if (mn_put_contents($file['folders'], DIE_LINE . serialize($folders))) {
        header('location: ./mn-folders.php?back=edited&f=' . $_POST['id']);
        exit;
      }
      else overal_header($lang['folders_folders'], $lang['folders_msg_put_contents_error'], 'error');

    }

    else overall_header($lang['folders_folders'], $lang['folders_msg_empty_folder_name'], 'error');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && array_key_exists($_GET['id'], $folders)) {
  	$var = array(
  		'folder_name' => $folders[$_GET['id']]['name'],
  		'folder_id' => $_GET['id'],
  		'folder_parent' => $folders[$_GET['id']]['parent_id']
  	);

    overall_header($lang['folders_edit_folder'] . ' &raquo; ' . $var['folder_name'], $lang['folders_edit_folder'], 'main');
  }





  elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && array_key_exists($_GET['id'], $folders)) {
  	$var = array(
  		'folder_name' => $folders[$_GET['id']]['name'],
  		'folder_id' => $_GET['id'],
  		'folder_parent' => $folders[$_GET['id']]['parent_id']
  	);

    $admin_tmpl['folders_main'] = false;
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && array_key_exists($_POST['id'], $folders)) {

    unset($folders[$_POST['id']]);
    
    if (mn_put_contents($file['folders'], DIE_LINE . serialize($folders))) {
      if (empty($folders)) unlink($file['folders']);
      header('location: ./mn-folders.php?back=deleted');
      exit;
    }
    else overal_header($lang['folders_folders'], $lang['folders_msg_put_contents_error'], 'error');

  }





  else {
    if (isset($_GET['back']) && $_GET['back'] == 'added') overall_header($lang['folders_folders'], $lang['folders_msg_folder_added'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'canceled') overall_header($lang['folders_folders'], $lang['folders_msg_folder_canceled'], 'info');
    elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['folders_folders'], $lang['folders_msg_folder_deleted'], 'ok');
    elseif (isset($_GET['back']) && $_GET['back'] == 'edited') overall_header($lang['folders_folders'], $lang['folders_msg_folder_edited'], 'ok');
    else overall_header($lang['folders_folders'], $lang['folders_folders'], 'main');
    $var['action'] = 'add';
  }




  $var['action'] = (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') ? 'edit' : 'add';
  
  if (isset($admin_tmpl['folders_main']) && $admin_tmpl['folders_main']) {
?>

  <div id="cat-add-edit">
    <form action="./mn-folders.php" method="post" id="category-add-edit">

      <label for="folder_name"><?php echo $lang['folders_folder_name'];?>:</label> <input type="text" class="text" name="folder_name" id="folder_name" value="<?php echo @$var['folder_name'];?>" />
      
      <?php
      	if (!empty($folders)) {
      		echo '<p><label for="folder_parent">' . $lang['folders_parent'] . ':</label><select name="folder_parent" id="folder_parent" class="long"><option value="0">--- ' . $lang['folders_select_main_folder'] . ' ---</option>';
      		
      		$parents = $folders_sorted = array();
      		foreach ($folders as $fId => $f) {
      			if (isset($folders[$fId]['parent_id']) && $folders[$fId]['parent_id'] != 0) $parents[$folders[$fId]['parent_id']] = $fId;
      			$folders_sorted[$fId] = $f['name'];
      		}
      		$folders_sorted = mn_natcasesort($folders_sorted);
      		

      		function show_folderss($folder_id = 0, $level = 0) {
      			global $folders, $folders_sorted, $parents, $var;
      		
      		    foreach ($folders_sorted as $fId => $f_name) {
      		    	if ($folders[$fId]['parent_id'] == $folder_id) {
      		    	
      		    		if (isset($var['folder_parent']) && $var['folder_parent'] == $fId) $sel = ' selected="selected"';
      		    		elseif ($fId == @$var['folder_id']) $sel = ' disabled="disabled"';
      		    		else $sel = '';

      		    		echo '<option value="' . $fId . '"' . $sel . '>';
      		    		for($i=0;$i<$level;$i++) echo '-- ';
      		    		echo $f_name . '</option>';
      		    		
      		    		if (isset($parents[$fId])) show_folderss($fId, $level+1);
      		    	
      		    	}
      		    	else continue;
      		    }
      		                  
      		}
      		
      		show_folderss();

      		echo '</select></p>';
      	}
      ?>
      
      <input type="hidden" name="action" value="<?php echo $var['action'];?>" />
      <?php if (isset($var['action']) && $var['action'] == 'edit') echo '<input type="hidden" name="id" value="' . $var['folder_id'] . '" />';?>
      <button type="submit" name="aaa"><img src="./stuff/img/icons/folder-<?php echo $var['action'];?>.png" alt="" /> <?php echo $lang['folders_' . $var['action'] . '_folder'];?></button>
      <?php echo ($var['action'] == 'edit') ? ' <a href="./mn-folders.php" class="cancel">' . $lang['uni_cancel'] . '</a>' : '';?>
    </form>
  </div>


  <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
  <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
  <table id="folders-list" class="tablesorter minor-list">
    <thead>
      <tr class="nodrop nodrag"><th id="minor_id" class="l num">id</th><th id="minor_name"><?php echo $lang['folders_folder'];?></th><th id="minor_count" class="num"><img src="./stuff/img/icons/posts.png" alt="-" class="tooltip" title="<?php echo $lang['folders_files_count'];?>" /></th></tr>
    </thead>
    <tbody>
      <?php
        if (!empty($folders)) {
          foreach ($folders_sorted as $fId => $f_name) {
          
          	$folder_files_count = (empty($files_count[$fId])) ? '<em class="trivial">0</em>' : '<a href="./mn-files.php?f=' . $fId . '">' . $files_count[$fId] . '</a>';
          	$highlight = (isset($_GET['f']) && $_GET['f'] == $fId) ? ' class="highlight"' : '';
            
            echo '<tr id="' . $fId . '"' . $highlight . '><td class="c">' . $fId . '</td><td><a href="./mn-folders.php?action=edit&amp;id=' . $fId . '" class="main-link">' . $folders[$fId]['name'] . '</a><br />&nbsp;<span class="links hide"><a href="./mn-folders.php?action=edit&amp;id=' . $fId . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-folders.php?action=delete&amp;id=' . $fId . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td class="c">' . $folder_files_count . '</td></tr>';

          }
        }
        else echo '<tr><td colspan="3"><p class="trivial c">' . $lang['folders_msg_no_folders'] . '</p></td></tr>';
      ?>
    </tbody>
  </table>
  
  <p class="cleaner">&nbsp;</p>

<?php
  }
  else {
?>

  <form action="./mn-folders.php" method="post" class="item-delete">
    <fieldset>
      <?php echo $lang['folders_q_really_delete'];?>: <strong><?php echo $var['folder_name'];?></strong>?<br />
      <?php
        if (isset($files_count[$var['folder_id']]) && $files_count[$var['folder_id']] == 1) $msg_num = 1;
        elseif (isset($files_count[$var['folder_id']]) && $files_count[$var['folder_id']] > 1 && $files_count[$var['folder_id']] < 5) $msg_num = 2;
        elseif (isset($files_count[$var['folder_id']]) && $files_count[$var['folder_id']] > 4) $msg_num = 3;
        else $msg_num = 0;
        echo '<em>' . str_replace('%n%', '<strong>' . @$files_count[$var['folder_id']] . '</strong>', $lang['folders_msg_files_number_' . $msg_num]);
        echo ($msg_num == 0) ? '' : '<br />' . $lang['folders_msg_files_no_delete'];
        echo '</em>';
      ?>
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?php echo $var['folder_id'];?>" />
        <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['folders_delete_folder'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }

  overall_footer();
?>