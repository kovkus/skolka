<?php
  include './stuff/inc/mn-start.php';



	# --- xFIELDs
	if (isset($_GET['action']) && $_GET['action'] == 'xfields') {

		$auth = user_auth('8');

		$xfields = get_unserialized_array('xfields');


		# --- add new xField
		if (isset($_POST['action']) && $_POST['action'] == 'xfield_add') {

			if (!in_array($_POST['field_section'], array('posts', 'comments', 'pages', 'users'))) die();
			else $xSection = $_POST['field_section'];

			$xName = check_text(trim($_POST['field_name']), true);
			$xVar = str_replace('-', '_', friendly_url(trim($_POST['field_variable'])));
			$xType = ($_POST['field_type'] == 'select' && !empty($_POST['field_options'])) ? 'select' : 'input';
			
			if (empty($xName) || empty($xVar) || array_key_exists($xVar, $xfields)) {

				$var = array(
					'name' => $xName,
					'var' => $xVar,
					'section' => $xSection,
					'type' => $xType,
					'options' => trim($_POST['field_options']),
					'action' => 'add'
				);
				
				$admin_tmpl['xfields_list'] = true;
				$xError = (array_key_exists($xVar, $xfields)) ? 'xfields_msg_variable_exists' : 'xfields_msg_empty_fields';
				overall_header($lang['xfields_xfields'], $lang[$xError], 'error');
			}
			
			else {
			
				if ($xType == 'select' && !empty($_POST['field_options'])) {
					$xOptions = array();
					$xOptions_lines = explode("\n", trim($_POST['field_options']));
					foreach ($xOptions_lines as $xLine) {
						$xO = explode('=', check_text($xLine, true));
						if (isset($xO[1])) $xOptions[$xO[0]] = $xO[1];
						else $xOptions[$xO[0]] = $xO[0];
					}
				}
				else $xOptions = '';

			
				$xfields[$xVar] = array(
					'name' => check_text($_POST['field_name'], true),
					'var' => $xVar,
					'section' => $xSection,
					'type' => $xType,
					'options' => $xOptions,
					'required' => 0
				);
		
		
				mn_put_contents($file['xfields'], '<?php die();?>' . serialize($xfields));
				
				header('Location: ./mn-tools.php?action=xfields&back=added');
				exit;
			}

		}
		
		
		# --- edit xField - show edit form
		elseif (isset($_GET['f']) && array_key_exists($_GET['f'], $xfields)) {
			$admin_tmpl['xfields_list'] = true;
			
			$f = $_GET['f'];
			
			$xOptions = '';
			if ($xfields[$f]['type'] == 'select' && !empty($xfields[$f]['options'])) {
				foreach ($xfields[$f]['options'] as $xKey => $xValue) {
					if ($xKey == $xValue) $xOptions .= $xValue . "\n";
					else $xOptions .= $xKey . '=' . $xValue . "\n";
				}
			}
			
			$var = array(
				'name' => $xfields[$f]['name'],
				'var' => $xfields[$f]['var'],
				'section' => $xfields[$f]['section'],
				'type' => $xfields[$f]['type'],
				'options' => trim($xOptions),
				'action' => 'edit'
			);
			overall_header($lang['xfields_xfields'], $lang['xfields_xfields'], 'main');
		}
		
		
		
		elseif (isset($_POST['action']) && $_POST['action'] == 'xfield_edit' && array_key_exists($_POST['xfield_var'], $xfields)) {

			$xVar = $_POST['xfield_var'];
			$xName = (!empty($_POST['field_name'])) ? check_text(trim($_POST['field_name']), true) : $xfields[$xVar]['name'];
			$xType = ($_POST['field_type'] == 'select' && !empty($_POST['field_options'])) ? 'select' : 'input';
			
			if (!in_array($_POST['field_section'], array('posts', 'comments', 'pages', 'users'))) $xSection = $xfields[$xVar]['section'];
			else $xSection = $_POST['field_section'];
			
			
			if ($xType == 'select' && !empty($_POST['field_options'])) {
				$xOptions = array();
				$xOptions_lines = explode("\n", trim($_POST['field_options']));
				foreach ($xOptions_lines as $xLine) {
					$xO = explode('=', check_text($xLine, true));
					if (isset($xO[1])) $xOptions[$xO[0]] = $xO[1];
					else $xOptions[$xO[0]] = $xO[0];
				}
			}
			else $xOptions = '';

			$xfields[$xVar] = array(
				'name' => $xName,
				'var' => $xVar,
				'section' => $xSection,
				'type' => $xType,
				'options' => $xOptions,
				'required' => 0
			);
			
			mn_put_contents($file['xfields'], '<?php die();?>' . serialize($xfields));
			
			header('Location: ./mn-tools.php?action=xfields&back=edited');
			exit;
		}
		
		
		elseif (isset($_GET['d']) && array_key_exists($_GET['d'], $xfields)) {
			$admin_tmpl['xfields_delete'] = true;
			
			$d = $_GET['d'];
			$var = array(
				'name' => $xfields[$d]['name'],
				'var' => $xfields[$d]['var'],
			);
		}
		
		
		elseif (isset($_POST['action']) && $_POST['action'] == 'xfield_delete' && array_key_exists($_POST['field'], $xfields)) {
			unset($xfields[$_POST['field']]);
			
			if (is_array($xfields) && !empty($xfields)) mn_put_contents($file['xfields'], '<?php die();?>' . serialize($xfields));
			else unlink($file['xfields']);
			
			header('Location: ./mn-tools.php?action=xfields&back=deleted');
			exit;
		}


		else {
			$admin_tmpl['xfields_list'] = true;
			$var = array(
				'name' => '',
				'var' => '',
				'section' => 'posts',
				'action' => 'add');

			if (isset($_GET['back'])) overall_header($lang['xfields_xfields'], $lang['xfields_msg_' . $_GET['back']], 'ok');
			else overall_header($lang['xfields_xfields'], $lang['xfields_xfields'], 'main');
		}
		
		
		
		$xfields_result = '';
		
		if (is_array($xfields) && !empty($xfields)) {	
				
			foreach ($xfields as $xVar => $x) {
				$xIcon = (isset($x['type']) && $x['type'] == 'select') ? '<img src="./stuff/img/icons/select.png" alt="" width="16" height="16" title="' . $lang['xfields_type_select'] . '" class="tooltip" />' : '<img src="./stuff/img/icons/textfield.png" alt="" width="16" height="16" title="' . $lang['xfields_type_input'] . '" class="tooltip" />';
				$xfields_result .= '<tr><td>' . $xIcon . '</td><td><a href="./mn-tools.php?action=xfields&amp;f=' . $x['var'] . '" class="main-link">' . $x['name'] . '</a><br />
				&nbsp;<span class="links hide"><a href="./mn-tools.php?action=xfields&amp;f=' . $x['var'] . '">' . $lang['uni_edit'] . '</a> | <a href="./mn-tools.php?action=xfields&amp;d=' . $x['var'] . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td><td><span class="trivial">{x' . strtoupper($x['var']) . '}</span></td><td>' . (($x['section'] == 'comments') ? $lang['comm_comments'] : $lang[$x['section'] . '_' . $x['section']]) . '</td></tr>';
			}		
		}

		if ($xfields_result == '') $xfields_result = '<tr><td colspan="4"><p class="c trivial">' . $lang['xfields_msg_no_fields'] . '</p></td></tr>';
	}





	




  # --- IP ban [get]
  elseif (isset($_GET['action']) && $_GET['action'] == 'ipban') {
    $auth = user_auth('10');
    $ips_result = '';
    foreach ($banned_ips as $banned_ip) {$ips_result .= $banned_ip . "\n";}
    $admin_tmpl['ip_ban'] = true;
    
    if (isset($_GET['back']) && $_GET['back'] == 'saved') overall_header($lang['ban_ban_ips'], $lang['ban_msg_banned_ips_saved'], 'ok');
    else overall_header($lang['ban_ban_ips'], $lang['ban_ban_ips'], 'main');
  }





  # --- IP ban [post]
  elseif (isset($_POST['action']) && $_POST['action'] == 'ipban') {
    $auth = user_auth('10');
    $ips = trim($_POST['ips']);
    
    if (!empty($ips)) {
      $ban_ips = explode("\n", $ips);
      
      $b_content = "<?php\n\t\$banned_ips = array(\n";
      foreach ($ban_ips as $ban_ip) {
        if (preg_match('/^[0-9\.]+$/', trim($ban_ip))) {
          $b_content .= "\t\t'" . trim($ban_ip) . "',\n";
        }
        else {
          continue;
        }
      }
      $b_content .= "\t);\n?".">";
      
      mn_put_contents($file['banned_ips'], $b_content);
    }
    else @unlink($file['banned_ips']);
    
    header('location: ./mn-tools.php?action=ipban&back=saved');
    exit;
  }





  # --- QuickBAN
  elseif (isset($_GET['action']) && $_GET['action'] == 'quickban' && isset($_GET['ip'])) {
    $auth = user_auth('10');
    if ($_GET['ip'] == $_SERVER['REMOTE_ADDR']) {
      echo '<div class="i-error"><img src="./stuff/img/icons/exclamation.png" alt="" /> ' . $lang['ban_msg_own_ip'] . '</div>';
      die();
    }
    elseif (in_array($_GET['ip'], $banned_ips)) {
      echo '<div class="i-info"><img src="./stuff/img/icons/information.png" alt="" /> ' . $lang['ban_msg_already_banned_ip'] . '</div>';
      die();
    }
    elseif (!preg_match("/^[0-9*]{1,3}\.[0-9*]{1,3}\.[0-9*]{1,3}\.[0-9*]{1,3}$/", $_GET['ip'])) {
      echo '<div class="i-info"><img src="./stuff/img/icons/information.png" alt="" /> ' . $lang['ban_msg_not_valid_ip'] . '</div>';
      die();
    }
    else {
      $b_content = "<?php\n\t\$banned_ips = array(\n";
      foreach ($banned_ips as $banned_ip) {$b_content .= "\t\t'" . $banned_ip . "',\n";}
      $b_content .= "\t\t'" . $_GET['ip'] . "',\n\t);\n?".">";

      if (mn_put_contents($file['banned_ips'], $b_content)) {
        $info_text = str_replace('%ip%', '<strong>' . $_GET['ip'] . '</strong>', $lang['ban_msg_ip_address_banned']);
        echo '<div class="i-ok"><img src="./stuff/img/icons/ok.png" alt="" /> ' . $info_text . '</div>';
        die();
      }
    }

  }





  elseif (isset($_GET['action']) && isset($_GET['subaction']) && $_GET['action'] == 'backup' && $_GET['subaction'] == 'delete') {
    $admin_tmpl['backup_delete'] = true;
  }





  elseif (isset($_POST['action']) && isset($_POST['subaction']) && $_POST['action'] == 'backup' && $_POST['subaction'] == 'delete') {
    if (isset($_POST['file']) && file_exists($dir['backups'] . $_POST['file'])) {
      if (unlink($dir['backups'] . $_POST['file'])) {
        header('location: ./mn-tools.php?action=backup&back=deleted');
        exit;
      }
      else {
        header('location: ./mn-tools.php?action=backup&back=del-error');
        exit;
      }
    }
    else {
      header('location: ./mn-tools.php?action=backup');
      exit;
    }
  }





  # --- Backup [get]
  elseif (isset($_GET['action']) && $_GET['action'] == 'backup') {
    $auth = user_auth('11');
    
    if (class_exists('ZipArchive') && is_callable(array('ZipArchive','addEmptyDir'))) {
    
    
      $backup_dir = dir($dir['backups']);
      $backup_lines = array();
      $backup_result = '';
      $backup_dir_size = '';
      
      while($backup_file = $backup_dir->read()) {
        if (is_file($backup_file) || $backup_file == 'index.html' || mb_substr($backup_file, 0, 7) != 'backup-') continue;
        else {
        
          $file_size = get_file_size($dir['backups'] . $backup_file, 2);
          $backup_dir_size += filesize($dir['backups'] . $backup_file);
          $file_timestamp = filemtime($dir['backups'] . $backup_file);
          $file_time = date('d.m.Y H:i', $file_timestamp);

          $backup_lines[] = '<tr>
            <td><a href="' . $dir['backups'] . $backup_file . '" class="main-link tooltip" title="' . $lang['backup_download_file'] . '">' . $backup_file . '</a><br />
              &nbsp;<span class="links hide"><a href="./mn-tools.php?action=backup&amp;subaction=delete&amp;file=' . $backup_file . '" class="fancy">' . $lang['uni_delete'] . '</a></span></td>
            <td class="c">' . $file_size . '</td>
            <td class="r"><!-- ' . $file_timestamp . ' -->' . $file_time . '</td></tr>';
        }
      }
      
      $backup_lines = array_reverse($backup_lines);
      foreach ($backup_lines as $line) {$backup_result .= $line;}
      

    
      if (file_exists($file['last_backup'])) {
        include MN_ROOT . $file['last_backup'];
      }
      $admin_tmpl['backup'] = true;

      if (isset($_GET['back']) && $_GET['back'] == 'done') overall_header($lang['backup_backup'], $lang['backup_msg_backup_done'], 'ok');
      elseif (isset($_GET['back']) && $_GET['back'] == 'deleted') overall_header($lang['backup_backup'], $lang['backup_msg_backup_deleted'], 'ok');
      else overall_header($lang['backup_backup'], $lang['backup_backup'], 'main');
      
    }

    else {
      overall_header($lang['backup_backup'], $lang['backup_msg_not_supported'], 'error');
      echo '<p class="disclaimer">' . $lang['backup_not_supported_text'] . '</p>';
    }
  }





  # --- Backup [post]
  elseif (isset($_POST['action']) && $_POST['action'] == 'backup') {
    $auth = user_auth('11');
    $backup_timestamp = time();
    $backup_hash = md5(rand(1, 1000) . microtime() . $_SERVER['REMOTE_ADDR']);

    if (class_exists('ZipArchive')) $backup = new backup('data/', $dir['backups'] . 'backup-' . date('Y-m-d') . '-' . substr($backup_hash, 6, 9) . '.zip');
    else $backup = false;

    if ($backup) {
      mn_put_contents($file['last_backup'], "<?php\n\t\$backup['timestamp'] = '" . $backup_timestamp . "';\n\t\$backup['hash'] = '" . $backup_hash . "';\n?" . ">");
      header('location: ./mn-tools.php?action=backup&back=done');
      exit;
    }
    else overall_header($lang['backup_backup'], $lang['backup_msg_not_supported'], 'error');
  }





  # --- Integration forms
  elseif (isset($_GET['action']) && $_GET['action'] == 'integration') {
    $auth = user_auth('12');
    overall_header($lang['int_integration'], $lang['int_integration'], 'main');
    $admin_tmpl['integration'] = true;
  }





  # --- Integration wizard
  elseif (isset($_GET['action']) && $_GET['action'] == 'wizard') {
    $auth = user_auth('12');
    overall_header($lang['wiz_wizard'], $lang['wiz_wizard'], 'main');
    $admin_tmpl['wizard'] = true;
    
    $mn_users = load_basic_data('users');
    $mn_categories = load_basic_data('categories');
    $t_groups = array();
    $t_groups = load_basic_data('templates_groups');
    
    if (file_exists(MN_ROOT . $file['posts'])) {
      $p_file = file(MN_ROOT . $file['posts']);
      array_shift($p_file);
      $authors = array();
      
      foreach ($p_file as $p_line) {
        $post = get_values('posts', $p_line, false);
        $authors[] = $post['author'];
      }
      
      $authors = array_unique($authors);
    }
    else {
      $authors = array();
    }
  }


  elseif (isset($_POST['action']) && $_POST['action'] == 'wizard') {
    $auth = user_auth('12');
    $wizard_code = '';
    
    if (!empty($_POST['count']) && is_numeric($_POST['count'])) $wizard_code .= '$mn_count = ' . $_POST['count'] . ';' . "\n  ";
    if (isset($_POST['categories']) && !empty($_POST['categories'])) {
      sort($_POST['categories']);
      $categories = trim(implode(',', $_POST['categories']), ',');
      $wizard_code .= "\$mn_cat = '" . $categories . "';\n  ";
    }
    if (isset($_POST['author']) && !empty($_POST['author']) && is_numeric($_POST['author'])) $wizard_code .= "\$mn_author = " . $_POST['author'] . ";\n  ";
    if (isset($_POST['template']) && !empty($_POST['template'])) $wizard_code .= "\$mn_tmpl = '" . $_POST['template'] . "';\n  ";
    if (isset($_POST['pagination']) && !empty($_POST['pagination'])) {
      $pagination = ($_POST['pagination'] == 'true') ? 'true' : 'false';
      $wizard_code .= "\$mn_pagination = " . $pagination . ";\n  ";
    }

    
    overall_header($lang['wiz_wizard'], $lang['wiz_wizard'], 'main');
    $admin_tmpl['wizard_done'] = true;
  }






  else {
    $admin_tmpl['tools'] = true;
    overall_header($lang['tools_tools'], $lang['tools_tools'], 'main');
  }
  
  
  
  if (isset($admin_tmpl['tools']) && $admin_tmpl['tools']) {
?>

  <div id="tools">

    <?php if ($_SESSION['mn_user_auth'][8] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-config.php"><img src="./stuff/img/icons/config.png" alt="" /> <span><?php echo $lang['tools_config'];?></span></a></h3>
      <p><?php echo $lang['tools_config_help'];?></p>
    </div>
    <?php } ?>
    
    <?php if ($_SESSION['mn_user_auth'][9] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-templates.php"><img src="./stuff/img/icons/template.png" alt="" /> <span><?php echo $lang['tools_templates'];?></span></a></h3>
      <p><?php echo $lang['tools_templates_help'];?></p>
    </div>
    <?php } ?>
    
    <?php if ($_SESSION['mn_user_auth'][8] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-tools.php?action=xfields"><img src="./stuff/img/icons/textfield.png" alt="" /> <span><?php echo $lang['tools_xfields'];?></a></span></h3>
      <p><?php echo $lang['tools_xfields_help'];?></p>
    </div>
    <?php } ?>
    
    <?php if ($_SESSION['mn_user_auth'][10] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-tools.php?action=ipban"><img src="./stuff/img/icons/blocked-ip.png" alt="" /> <span><?php echo $lang['tools_ipban'];?></a></span></h3>
      <p><?php echo $lang['tools_ipban_help'];?></p>
    </div>
    <?php } ?>
    
    <?php if ($_SESSION['mn_user_auth'][11] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-tools.php?action=backup"><img src="./stuff/img/icons/backup.png" alt="" /> <span><?php echo $lang['tools_backup'];?></a></span></h3>
      <p><?php echo $lang['tools_backup_help'];?></p>
    </div>
    <?php } ?>
    
    <?php if ($_SESSION['mn_user_auth'][12] == 1) { ?>
    <div class="tool round">
      <h3><a href="./mn-tools.php?action=integration"><img src="./stuff/img/icons/wand.png" alt="" /> <span><?php echo $lang['tools_integration'];?></a></span></h3>
      <p><?php echo $lang['tools_integration_help'] . ' <a href="./mn-tools.php?action=wizard">' . $lang['wiz_wizard'] . '</a>.';?></p>
    </div>
    <?php } ?>


    <p class="cleaner">&nbsp;</p>
  </div>

<?php
  }

  # --- xFields TMPL
  elseif (isset($admin_tmpl['xfields_list']) && $admin_tmpl['xfields_list']) {
?>


	<form action="./mn-tools.php?action=xfields" method="post" id="xfields-add-edit">
	
		<fieldset>
		
			<label class="block">
				<?php echo $lang['xfields_section'];?>:
				<select name="field_section" id="field_section" class="medium">
					<option value="posts"<?php echo (@$var['section'] == 'posts') ? ' selected="selected"' : '';?>><?php echo $lang['posts_posts'];?></option>
					<option value="comments"<?php echo (@$var['section'] == 'comments') ? ' selected="selected"' : '';?>><?php echo $lang['comm_comments'];?></option>
					<option value="pages"<?php echo (@$var['section'] == 'pages') ? ' selected="selected"' : '';?>><?php echo $lang['pages_pages'];?></option>
					<option value="users"<?php echo (@$var['section'] == 'users') ? ' selected="selected"' : '';?>><?php echo $lang['users_users'];?></option>
				</select>

			</label>
	
			<label class="block">
				<?php echo $lang['xfields_name'];?>: <img src="./stuff/img/icons/help.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['xfields_name_help'];?>" /><br />
				<input type="text" name="field_name" id="field_name" value="<?php echo @$var['name'];?>" class="text" />
			</label>
				
			<label class="block">
				<?php echo $lang['xfields_variable'];?>: <img src="./stuff/img/icons/help.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['xfields_variable_help'];?>" /><br />
				<input type="text" name="field_variable" id="field_variable" value="<?php echo @$var['var'];?>" class="text"<?php echo ($var['action'] == 'edit') ? ' disabled="disabled" title="' . $lang['xfields_msg_no_edit'] . '"' : '';?> />
			</label>
			
			<p>
				<?php echo $lang['xfields_type'];?>:
				<label class="select" id="field_type1"><input type="radio" name="field_type" value="input"<?php echo (!isset($var['type']) || $var['type'] == 'input') ? ' checked="checked"' : '';?> /> <?php echo $lang['xfields_type_input'];?></label>
				<label class="select" id="field_type2"><input type="radio" name="field_type" value="select"<?php echo (isset($var['type']) && $var['type'] == 'select') ? ' checked="checked"' : '';?> /> <?php echo $lang['xfields_type_select'];?></label>
				
				<label class="block<?php echo (isset($var['type']) && $var['type'] == 'select') ? '' : ' hide';?>" id="field_options_block">
					<?php echo $lang['xfields_options'];?>: <img src="./stuff/img/icons/help.png" alt="" width="16" height="16" class="tooltip" title="<?php echo $lang['xfields_options_help'];?>" /><br />
					<textarea name="field_options" id="field_options"><?php echo @$var['options'];?></textarea>
				</label>
			</p>			

			
			<p class="c cb">
				<input type="hidden" name="action" value="xfield_<?php echo $var['action'];?>" />
				<?php echo ($var['action'] == 'edit') ? '<input type="hidden" name="xfield_var" value="' . $var['var'] . '" />' : '';?>
				<button type="submit" name="submit" class="submit"><img src="./stuff/img/icons/<?php echo $var['action'];?>.png" alt="" width="16" height="16" /> <?php echo $lang['xfields_' . $var['action'] . '_field'];?></button>
				<?php echo ($var['action'] == 'edit') ? ' <a href="./mn-tools.php?action=xfields" class="cancel">' . $lang['uni_cancel'] . '</a>' : '';?>
			</p>
		
		</fieldset>
	
	</form>
	
	
	<script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
	<script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>
	<table id="xfields-list" class="tablesorter">
		<thead><tr><th id="xfield-icon" class="nosort"></th><th id="xfield-name"><?php echo $lang['xfields_name'];?></th><th id="xfield-var"><?php echo $lang['xfields_variable'];?></th><th id="xfield-section"><?php echo $lang['xfields_section'];?></th></tr></thead>
		<tbody><?php echo $xfields_result;?></tbody>
	</table>


	<p class="cleaner">&nbsp;</p>

<?php
  }
  
  elseif (isset($admin_tmpl['xfields_delete']) && $admin_tmpl['xfields_delete']) {
?>
  
    <form action="./mn-tools.php?action=xfields" method="post" class="item-delete">
      <fieldset>
        <?php echo $lang['xfields_q_really_delete'];?>: <strong><?php echo $var['name'];?></strong>?
        <p>
          <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
          <input type="hidden" name="action" value="xfield_delete" />
          <input type="hidden" name="field" value="<?php echo $var['var'];?>" />
          <button type="submit" name="delete"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['xfields_delete_xfield'];?></button>
        </p>
      </fieldset>
    </form>
  
<?php
  die();
  }

  elseif (isset($admin_tmpl['backup']) && $admin_tmpl['backup']) {
?>

  <form action="./mn-tools.php" method="post">
    <fieldset>
      <?php if (!isset($_GET['back'])) { ?>
      <p class="c backup-info">
        <img src="./stuff/img/icons/information.png" alt="" width="16" height="16" />
        <?php
          if (!file_exists($file['last_backup'])) echo $lang['backup_last_backup_never'];
          elseif ($backup['timestamp'] > (time() - 1*24*60*60)) echo $lang['backup_last_backup_day'];
          elseif ($backup['timestamp'] > (time() - 7*24*60*60)) echo $lang['backup_last_backup_week'];
          elseif ($backup['timestamp'] > (time() - 31*24*60*60)) echo $lang['backup_last_backup_month'];
          else echo $lang['backup_last_backup_lta'];
        ?>
        <img src="./stuff/img/icons/information.png" alt="" width="16" height="16" />
      </p>
      <?php } ?>
      <p class="c">
        <input type="hidden" name="action" value="backup" />
        <?php if (!isset($backup['timestamp']) || $backup['timestamp'] < (time() - 1*24*60*60)) { ?><button type="submit" name="save"><img src="./stuff/img/icons/backup.png" alt="" width="16" height="16" /> <?php echo $lang['backup_submit'];?></button><?php } ?>
      </p>
    </fieldset>
  </form>
  
  <?php if (isset($backup_result) && !empty($backup_result)) { ?>
  <table class="tablesorter" id="backup-table">
    <thead>
      <?php echo '<tr><th id="file_name">' . $lang['backup_file_name'] . '</th><th id="file_size" class="num">' . $lang['backup_file_size'] . '</th><th id="file_date" class="date">' . $lang['backup_file_date'] . '</th></tr>';?>
    </thead>
    <?php echo $backup_result;?>
  </table>
  
    <script type="text/javascript" src="./stuff/etc/jquery-tablesorter-min.js"></script>
    <script type="text/javascript">$(function() {$("table") .tablesorter({widthFixed: false})});</script>

  <?php
      echo '<p class="c">' . $lang['backup_dir_size'] . ': <strong>' . get_file_size($backup_dir_size, 2, false) . '</strong></p>';
    }
  ?>
  
  <p class="disclaimer">
    <?php echo '<strong>' . $lang['backup_disclaimer'] . ':</strong> ' . $lang['backup_disclaimer_text'];?>
  </p>

<?php
  }
  
  if (isset($admin_tmpl['backup_delete']) && $admin_tmpl['backup_delete']) {
?>

  <form action="./mn-tools.php" method="post" id="backup-delete" class="item-delete">
    <fieldset>
      <?php echo $lang['backup_q_really_delete'];?>: <br /><strong><?php echo $_GET['file'];?></strong>?
      <p>
        <span class="warn"><img src="./stuff/img/icons/warning.png" alt="!" /> <?php echo $lang['uni_no_go_back'];?> <img src="./stuff/img/icons/warning.png" alt="!" /></span>
        <input type="hidden" name="action" value="backup" />
        <input type="hidden" name="subaction" value="delete" />
        <input type="hidden" name="file" value="<?php echo $_GET['file'];?>" /><br />
        <button type="submit" name="submit"><img src="./stuff/img/icons/delete.png" alt="" width="16" height="16" /> <?php echo $lang['backup_delete_file'];?></button>
      </p>
    </fieldset>
  </form>

<?php
  die();
  }
  
  elseif (isset($admin_tmpl['ip_ban']) && $admin_tmpl['ip_ban']) {
?>

  <div id="ip-ban">
    <form action="./mn-tools.php" method="post" id="banned-ips">
      <p class="help"><?php echo $lang['ban_ban_ips_help'];?></p>
      <textarea name="ips"><?php echo $ips_result;?></textarea>
      <p class="c">
        <input type="hidden" name="action" value="ipban" />
        <button type="submit" name="save"><img src="./stuff/img/icons/tick.png" alt="" width="16" height="16" /> <?php echo $lang['ban_submit'];?></button>
      </p>
    </form>
  </div>

<?php
  }
  
  elseif (isset($admin_tmpl['integration']) && $admin_tmpl['integration']) {
?>

  <div class="simbutton"><a href="./mn-tools.php?action=wizard"><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" /> <?php echo $lang['wiz_generate'];?></a></div>

  <form id="integration">
  
  
    <fieldset>
      <legend><?php echo $lang['int_code_main'];?></legend>
      <p class="help"><?php echo str_ireplace('%templates%', '<a href="./mn-templates.php">' . $lang['tmpl_templates'] . '</a>', $lang['int_code_main_help']);?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
    <fieldset>
      <legend><?php echo $lang['int_code_menu'];?></legend>
      <p class="help"><?php echo $lang['int_code_menu_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'menu';
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
    <fieldset>
      <legend><?php echo $lang['int_code_pagemenu'];?></legend>
      <p class="help"><?php echo $lang['int_code_pagemenu_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'pagemenu';
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
    <fieldset>
      <legend><?php echo $lang['int_code_archive'];?></legend>
      <p class="help"><?php echo $lang['int_code_archive_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'archive';
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>


    <fieldset>
      <legend><?php echo $lang['int_code_search'];?></legend>
      <p class="help"><?php echo $lang['int_code_search_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'search';
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
    <fieldset>
      <legend><?php echo $lang['int_code_rss'];?></legend>
      <p class="help"><?php echo $lang['int_code_rss_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'rss';
  $mn_url = '/';
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
    <fieldset>
      <legend><?php echo $lang['int_code_gallery'];?></legend>
      <p class="help"><?php echo $lang['int_code_gallery_help'];?></p>
      <textarea class="integration" readonly="readonly">&lt;?php
  $mn_mode = 'gallery';
  $mn_gallery = 3;
  include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
    
  </form>
<?php
  }
  
  elseif (isset($admin_tmpl['wizard']) && $admin_tmpl['wizard']) {
?>

  <p class="help-text j round">
    <?php echo $lang['wiz_wizard_help'] . ' <a href="./mn-tools.php?action=integration">' . $lang['int_integration'] . '</a>.';?>
  </p>

  <form action="./mn-tools.php" method="post">
  
    <fieldset>

      <table class="config-edit">

        <tr><td><label for="count"><img src="./stuff/img/icons/posts.png" alt="" /> <?php echo $lang['wiz_posts_count'];?>:</label></td><td><input type="text" class="custom" name="count" id="count" size="2" value="" /></td></tr>
        <tr class="config-help"><td colspan="2"><?php echo $lang['wiz_posts_count_help'];?></td></tr>
        
        <tr>
          <td>
            <label for="categories"><img src="./stuff/img/icons/categories.png" alt="" /> <?php echo $lang['wiz_categories'];?>:</label><br />
            <span class="help"><?php echo $lang['wiz_categories_help'];?></span>
          </td>
          <td>
            <div class="sim-select round">
              <?php
                if (file_exists($file['categories']) && !empty($mn_categories)) {
                  foreach ($mn_categories as $cat_id => $cat_name) {
                    echo '<input type="checkbox" name="categories[]" id="cat_' . $cat_id . '" value="' . $cat_id . '"> <label for="cat_' . $cat_id . '" class="custom">' . $cat_name . '</label><br />';
                  }
                }
                else echo '<span class="help">' . $lang['wiz_categories_empty'] . '</span>';
              ?>
            </div>
          </td>
        </tr>
        
        <?php if (!empty($authors)) { ?>
        <tr>
          <td><label for="author"><img src="./stuff/img/icons/user.png" alt="" /> <?php echo $lang['wiz_author'];?>:</label></td>
          <td>
            <select name="author" id="author" class="custom long">
              <?php
                echo '<option value="">----- ' . $lang['wiz_author_all'] . ' -----</option>';
                foreach ($authors as $author_id) {
                  if (!in_array($author_id, $authors)) continue;
                  else echo '<option value="' . $author_id . '">' . $mn_users[$author_id] . '</option>';
                }
              ?>
            </div>
          </td>
        </tr>
        <tr class="config-help"><td colspan="2"><?php echo $lang['wiz_author_help'];?></td></tr>
        <?php } ?>
        
        <?php if (!empty($t_groups)) { ?>
        <tr>
          <td><label for="template"><img src="./stuff/img/icons/template.png" alt="" /> <?php echo $lang['wiz_template'];?>:</label></td>
          <td>
            <select name="template" id="template" class="custom long">
              <?php
                echo '<option value="">----- ' . $lang['wiz_template_default'] . ' -----</option>';
                foreach ($t_groups as $t_id => $t_name) {
                  echo '<option value="' . $t_name . '">' . $t_name . '</option>';
                }
              ?>
            </div>
          </td>
        </tr>
        <tr class="config-help"><td colspan="2"><?php echo $lang['wiz_author_help'];?></td></tr>
        <?php } ?>
        
        <tr>
          <td><label for="pagination0"><img src="./stuff/img/icons/pagination.png" alt="" /> <?php echo $lang['wiz_pagination'];?>:</label></td>
          <td>
            <input type="radio" class="radio" id="pagination1" name="pagination" value="true"> <label for="pagination1" class="custom"><?php echo $lang['uni_yes'];?></label>
            <input type="radio" class="radio secondrb" id="pagination2" name="pagination" value="false"> <label for="pagination2" class="custom"><?php echo $lang['uni_no'];?></label>
          </td>
        </tr>
        <tr class="config-help"><td colspan="2"><?php echo $lang['wiz_pagination_help'];?></td></tr>
        
      </table>
      
      
      <p class="c">
        <input type="hidden" name="action" value="wizard" />
        <button type="submit" name="add"><img src="./stuff/img/icons/wand.png" alt="" width="16" height="16" /> <?php echo $lang['wiz_generate'];?></button>
      </p>
          
          
    </fieldset>
  
  </form>


<?php
  }
  
  elseif (isset($admin_tmpl['wizard_done']) && $admin_tmpl['wizard_done']) {
?>

  <form id="integration">
  
    <fieldset>
      <legend><?php echo $lang['wiz_generated_code'];?>:</legend>
      <p class="help"><?php echo $lang['wiz_generated_code_help'];?></p>
      <textarea class="integration" id="generate" readonly="readonly">&lt;?php
  <?php echo $wizard_code;?>include '<?php echo dirname(__FILE__);?>/mn-show.php';
?&gt;</textarea>
    </fieldset>
    
  </form>

<?php
  }

  overall_footer();
?>