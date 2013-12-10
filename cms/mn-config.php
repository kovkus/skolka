<?php

  include './stuff/inc/mn-start.php';
  $auth = user_auth('8');



  if (isset($_GET['action']) && $_GET['action'] == 'theme') {
  
    $theme_config = MN_ROOT . DIR_THEMES . $conf['admin_theme'] . '/theme_config.php';
  
    if (file_exists($theme_config)) {
      include './stuff/themes/' . $conf['admin_theme'] . '/theme_config.php';
      @include_once MN_ROOT . 'data/databases/theme_config.php';
    
      if (isset($theme['name']) && $theme['name'] != $conf['admin_theme']) {
        unset($theme);
        include $theme_config;
      }
    
      $theme_result = '';

      foreach ($theme as $key => $value) {
        if ($key == 'name' || substr($key, -3, 3) == '-va' || substr($key, -3, 3) == '-ha' || substr($key, -4, 4) == '-rep' || substr($key, -4, 4) == '-att') continue; 
        elseif (substr($key, -4, 4) == '_img') {
          $theme_result .= '<tr><td class="r"><label for="' . $key . '">' . $key . ':</label></td><td class="l"><input type="text" name="' . $key . '" placeholder="URL" id="' . $key . '" class="extra" value="' . $value . '" /><br />
            <select name="' . $key . '-va">
              <option value="top"' . ((isset($theme[$key . '-va']) && $theme[$key . '-va'] == 'top') ? ' selected="selected"' : '') . '>top</option>
              <option value="center"' . ((!isset($theme[$key . '-va']) || $theme[$key . '-va'] == 'center') ? ' selected="selected"' : '') . '>center</option>
              <option value="bottom"' . ((isset($theme[$key . '-va']) && $theme[$key . '-va'] == 'bottom') ? ' selected="selected"' : '') . '>bottom</option>
            </select>
            <select name="' . $key . '-ha">
              <option value="left"' . ((isset($theme[$key . '-ha']) && $theme[$key . '-ha'] == 'left') ? ' selected="selected"' : '') . '>left</option>
              <option value="center"' . ((!isset($theme[$key . '-ha']) || $theme[$key . '-ha'] == 'center') ? ' selected="selected"' : '') . '>center</option>
              <option value="right"' . ((isset($theme[$key . '-ha']) && $theme[$key . '-ha'] == 'right') ? ' selected="selected"' : '') . '>right</option>
            </select>
            <select name="' . $key . '-rep">
              <option value="repeat"' . ((isset($theme[$key . '-rep']) && $theme[$key . '-rep'] == 'repeat') ? ' selected="selected"' : '') . '>repeat</option>
              <option value="repeat-x"' . ((isset($theme[$key . '-rep']) && $theme[$key . '-rep'] == 'repeat-x') ? ' selected="selected"' : '') . '>repeat-x</option>
              <option value="repeat-y"' . ((isset($theme[$key . '-rep']) && $theme[$key . '-rep'] == 'repeat-y') ? ' selected="selected"' : '') . '>repeat-y</option>
              <option value="no-repeat"' . ((!isset($theme[$key . '-rep']) || $theme[$key . '-rep'] == 'no-repeat') ? ' selected="selected"' : '') . '>no-repeat</option>
            </select>
            <select name="' . $key . '-att">
              <option value="fixed"' . ((!isset($theme[$key . '-att']) || $theme[$key . '-att'] == 'fixed') ? ' selected="selected"' : '') . '>fixed</option>
              <option value="scroll"' . ((isset($theme[$key . '-att']) && $theme[$key . '-att'] == 'scroll') ? ' selected="selected"' : '') . '>scroll</option>
            </select>
          </td></tr>';
        }
        else {
          $var_help = (is_array($theme_help) && array_key_exists($key, $theme_help) && $theme_help[$key] != '') ? ' <span class="help tooltip" title="' . $theme_help[$key] . '">?</span>' : '';
          $theme_result .= '<tr><td class="r"><label for="' . $key . '">' . $key . ':</label></td><td class="l"><input type="text" maxlength="7" name="' . $key . '" id="' . $key . '" class="colorpick" value="' . $value . '" /> <label for="' . $key . '"><span class="colorpreview" id="preview-' . $key . '" style="background: ' . $value . '"></span>' . $var_help . '</label></td></tr>';
        }
      }
    }
    else {
      header('Location: ./mn-config.php');
      exit;
    }
    overall_header($lang['config_theme_config'], $lang['config_theme_config'] . ': ' . $conf['admin_theme'], 'main');
    $admin_tmpl['theme'] = true;
  
  }





  elseif (isset($_POST['action']) && $_POST['action'] == 'theme') {
  
    $tc_content = "<?php\n\n\t\$theme['name'] = '" . $conf['admin_theme'] . "';\n";
    foreach ($_POST as $key => $value) {
      if ($key == 'action' || $key == 'add') continue;
      $tc_content .= "\t\$theme['" . $key . "'] = '" . $value . "';\n";
    }
    $tc_content .= "\n?".">";
    
    if (mn_put_contents($file['theme_config'], $tc_content)) {
      header('location: ./mn-config.php?action=theme&back=ok');
      exit;
    }
    else overall_header($lang['config_config'], $lang['config_msg_put_contents_error'], 'error');
  
  }




  
  elseif (isset($_POST['action']) && $_POST['action'] == 'save') {

    $posts_count = (is_numeric($_POST['web_posts_count']) && $_POST['web_posts_count'] > 0) ? $_POST['web_posts_count'] : '10';
    $postimg_size = (is_numeric($_POST['posts_image_size']) && $_POST['posts_image_size'] > 10) ? $_POST['posts_image_size'] : '0';
    $web_title = (empty($_POST['web_title'])) ? $conf['web_title'] : check_text($_POST['web_title']);
    $web_title_header = (empty($_POST['web_title_header'])) ? 'false' : 'true';
    $admin_url = (empty($_POST['admin_url'])) ? $conf['admin_url'] : $_POST['admin_url'];
    $comments_antiflood = (is_numeric($_POST['comments_antiflood']) && $_POST['comments_antiflood'] > 0 && $_POST['comments_antiflood'] < 3600) ? $_POST['comments_antiflood'] : '30';
    $field_email = (empty($_POST['comments_field_email'])) ? 'false' : 'true';
    $field_www = (empty($_POST['comments_field_www'])) ? 'false' : 'true';
    $field_preview = (empty($_POST['comments_field_preview'])) ? 'false' : 'true';
    if (substr($admin_url, -1) == '/') $admin_url = substr($admin_url, 0, -1);
    
    $bb_bold = (isset($_POST['bb_bold'])) ? '1' : '0';
    $bb_italics = (isset($_POST['bb_italics'])) ? '1' : '0';
    $bb_underline = (isset($_POST['bb_underline'])) ? '1' : '0';
    $bb_strikethrough = (isset($_POST['bb_strikethrough'])) ? '1' : '0';
    $bb_link = (isset($_POST['bb_link'])) ? '1' : '0';
    $bb_code = (isset($_POST['bb_code'])) ? '1' : '0';
    $bb_buttons = $bb_bold . $bb_italics . $bb_underline . $bb_strikethrough . $bb_link . $bb_code;
    
    $thumb_size = (is_numeric($_POST['admin_thumb_size']) && $_POST['admin_thumb_size'] > 20 && $_POST['admin_thumb_size'] < 300) ? $_POST['admin_thumb_size'] : $default['thumb_size'];
    
    $avatar_standard = (is_numeric($_POST['users_avatar_standard']) && $_POST['users_avatar_standard'] > 0 && $_POST['users_avatar_standard'] < 300) ? $_POST['users_avatar_standard'] : $default['avatar_size_standard'];
    $avatar_small = (is_numeric($_POST['users_avatar_small']) && $_POST['users_avatar_small'] > 0 && $_POST['users_avatar_small'] < 150) ? $_POST['users_avatar_small'] : $default['avatar_size_small'];
    $avatar_mini = (is_numeric($_POST['users_avatar_mini']) && $_POST['users_avatar_mini'] > 0 && $_POST['users_avatar_mini'] < 75) ? $_POST['users_avatar_mini'] : $default['avatar_size_mini'];

    $c_content  = "<?php\n\n\t\$conf = array();\n\n\t";
    $c_content .= "// Administration:\n\t";
    $c_content .= "\$conf['admin_url'] = '" . $admin_url . "';\n\t";
    $c_content .= "\$conf['lang'] = '" . $_POST['lang'] . "';\n\t";
    $c_content .= "\$conf['admin_theme'] = '" . $_POST['admin_theme'] . "';\n\t";
    $c_content .= "\$conf['admin_wysiwyg'] = " . $_POST['admin_wysiwyg'] . ";\n\t";
    $c_content .= "\$conf['admin_multiupload'] = " . $_POST['admin_multiupload'] . ";\n\t";
	$c_content .= "\$conf['admin_thumb_size'] = " . $thumb_size . ";\n\t";
    $c_content .= "\$conf['time_adj'] = '" . $_POST['time_adj'] . "';\n\t";
    $c_content .= "\$conf['admin_update_check'] = " . $_POST['admin_update_check'] . ";\n\t";
    $c_content .= "\$conf['admin_icons'] = " . $_POST['admin_icons'] . ";\n\n\t";
    $c_content .= "// Website:\n\t";
    $c_content .= "\$conf['web_title'] = '" . $web_title . "';\n\t";
    $c_content .= "\$conf['web_title_header'] = " . $web_title_header . ";\n\t";
    $c_content .= "\$conf['web_url'] = '" . check_text($_POST['web_url']) . "';\n\t";
    $c_content .= "\$conf['web_format'] = '" . $_POST['web_format'] . "';\n\t";
    $c_content .= "\$conf['web_encoding'] = '" . $_POST['web_encoding'] . "';\n\t";
    $c_content .= "\$conf['web_section_titles'] = " . $_POST['web_section_titles'] . ";\n\t";
    $c_content .= "\$conf['web_powered_by'] = " . $_POST['web_powered_by'] . ";\n\n\t";
    $c_content .= "// Posts:\n\t";
    $c_content .= "\$conf['web_posts_count'] = " . (int)$posts_count . ";\n\t";
    $c_content .= "\$conf['web_pagination'] = " . $_POST['web_pagination'] . ";\n\t";
    $c_content .= "\$conf['web_counter'] = " . $_POST['web_counter'] . ";\n\t";
    $c_content .= "\$conf['posts_image'] = " . $_POST['posts_image'] . ";\n\t";
    $c_content .= "\$conf['posts_image_size'] = " . $postimg_size . ";\n\n\t";
    $c_content .= "// Comments:\n\t";
    $c_content .= "\$conf['comments'] = '" . $_POST['comments'] . "';\n\t";
    $c_content .= "\$conf['comments_order'] = '" . $_POST['comments_order'] . "';\n\t";
    $c_content .= "\$conf['comments_approval'] = " . $_POST['comments_approval'] . ";\n\t";
    $c_content .= "\$conf['comments_antispam'] = " . (int)$_POST['comments_antispam'] . ";\n\t";
    $c_content .= "\$conf['comments_antiflood'] = " . (int)$comments_antiflood . ";\n\t";
    $c_content .= "\$conf['comments_captcha'] = " . $_POST['comments_captcha'] . ";\n\t";
    $c_content .= "\$conf['comments_bb'] = " . $_POST['comments_bb'] . ";\n\t";
    $c_content .= "\$conf['comments_bb_buttons'] = '" . $bb_buttons . "';\n\t";
    $c_content .= "\$conf['comments_smiles'] = " . $_POST['comments_smiles'] . ";\n\t";
    $c_content .= "\$conf['comments_links_auto'] = " . $_POST['comments_links_auto'] . ";\n\t";
    $c_content .= "\$conf['comments_links_target'] = " . $_POST['comments_links_target'] . ";\n\t";
    $c_content .= "\$conf['comments_links_nofollow'] = " . $_POST['comments_links_nofollow'] . ";\n\t";
    $c_content .= "\$conf['comments_field_email'] = " . $field_email . ";\n\t";
    $c_content .= "\$conf['comments_field_www'] = " . $field_www . ";\n\t";
    $c_content .= "\$conf['comments_field_preview'] = " . $field_preview . ";\n\n\t";
    $c_content .= "// Users:\n\t";
    $c_content .= "\$conf['users_registration'] = " . $_POST['users_registration'] . ";\n\t";
    $c_content .= "\$conf['users_default_group'] = " . (int)$_POST['users_default_group'] . ";\n\t";
    $c_content .= "\$conf['users_perm_login'] = " . $_POST['users_perm_login'] . ";\n\t";
    $c_content .= "\$conf['users_avatar_standard'] = " . $avatar_standard . ";\n\t";
    $c_content .= "\$conf['users_avatar_small'] = " . $avatar_small . ";\n\t";
    $c_content .= "\$conf['users_avatar_mini'] = " . $avatar_mini . ";\n\n";
    $c_content .= "?" . ">";
    
    if (mn_put_contents($file['config'], $c_content)) {
      $tid = (!empty($_POST['t-id']) || $_POST['t-id'] == '1') ? 't=' . $_POST['t-id'] . '&' : '';
      header('location: ./mn-config.php?' . $tid . 'back=saved');
      exit;
    }
    else overall_header($lang['config_config'], $lang['config_msg_put_contents_error'], 'error');
  }





  elseif (isset($_GET['back']) && $_GET['back'] == 'saved') overall_header($lang['config_config'], $lang['config_msg_saved'], 'ok');
  
  else overall_header($lang['config_config'], $lang['config_config'], 'main');
  
  
  $url_path = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
  $url_path = str_replace('mn-config.php', '', $url_path);
  if (substr($url_path, -1) == '/') $url_path = substr($url_path, 0, -1);
  if (!isset($conf['comments_bb_buttons']) || empty($conf['comments_bb_buttons'])) $conf['comments_bb_buttons'] = '110010';


  if (isset($admin_tmpl['theme']) && $admin_tmpl['theme']) {
?>

	<script type="text/javascript" src="./stuff/etc/jquery-colorpicker.js"></script>
	<script type="text/javascript">
	 	$(document).ready( function() {
	 		$('.colorpick').ColorPicker({
	 		    onSubmit: function(hsb, hex, rgb, el) {
	 		  		$(el).val('#' + hex);
	 		  		$(el).ColorPickerHide();
	 		        $('#preview-' + $(el).attr('id')).css('background-color', '#' + hex);
	 		
	 		  	},
	 		  	onBeforeShow: function () {
	 		  		$(this).ColorPickerSetColor(this.value);
	 		  	}
	 		});
		});
	</script>

  <a href="./mn-config.php" id="tmpl-link"><img src="./stuff/img/icons/config.png" alt="" /> <span><?php echo $lang['config_config'];?></span></a>

  <form action="./mn-config.php" method="post" id="config-edit">
    <fieldset>
    
      <table id="theme-config">
        <?php echo $theme_result;?>
      </table>
    
    </fieldset>
    
    <p class="c">
      <input type="hidden" name="action" value="theme" />
      <button type="submit" name="add"><img src="./stuff/img/icons/tick.png" alt="" width="16" height="16" /> <?php echo $lang['uni_save'];?></button>
    </p>
    
  </form>
  
  <p class="r">
    <span class="simurl" onclick="<?php unset($theme); include './stuff/themes/' . $conf['admin_theme'] . '/theme_config.php'; foreach ($theme as $k => $v) {echo '$(\'#' . $k . '\').attr(\'value\', \'' . $v . '\'); $(\'#preview-' . $k . '\').css(\'background-color\', \'' . $v . '\'); ';}?>"><?php echo $lang['config_theme_default_values'];?></span>
  </p>

<?php
  }
  else {
?>

  <script type="text/javascript" src="./stuff/etc/jquery-mntabs.js"></script>
  <script type="text/javascript">
    $(document).ready( function() {
      $('#t').mnTabs({
    		defautContent: <?php echo (isset($_GET['t']) && is_numeric($_GET['t']) && ($_GET['t'] > 0) && ($_GET['t'] <= 5)) ? $_GET['t'] : '1';?>
    	});
     $('#auto-url').click(function() {$('#admin_url').attr('value', '<?php echo $url_path;?>');});
    });
  </script>

  <form action="./mn-config.php" method="post" id="config-edit">
    <fieldset>
    
    <div id="t">
    	<div class="t-nav">
      	<ul class="round">
        	<li id="t-nav-1" class="first"><?php echo $lang['config_tab_1'];?></li>
        	<li id="t-nav-2"><?php echo $lang['config_tab_2'];?></li>
          <li id="t-nav-3"><?php echo $lang['config_tab_3'];?></li>
        	<li id="t-nav-4"><?php echo $lang['config_tab_4'];?></li>
        	<li id="t-nav-5" class="last"><?php echo $lang['config_tab_5'];?></li>
      	</ul>
    	</div>
    	
    	<div id="t-1" class="t-content">
    	
    		<table class="config-edit">
          <tr><td class="labels"><label for="admin_url"><img src="./stuff/img/icons/link.png" alt="" /> <?php echo $lang['config_admin_url'];?>:</label></td><td class="inputs"><input type="text" id="admin_url" name="admin_url" class="text<?php echo ($url_path == check_text($conf['admin_url'])) ? '' : ' extra';?>" value="<?php echo check_text($conf['admin_url']);?>" /><?php if ($url_path != check_text($conf['admin_url'])) { ?>&nbsp;<img src="./stuff/img/icons/refresh-gray.png" id="auto-url" class="tooltip" title="<?php echo $lang['config_admin_auto_url'];?>" alt="" /><?php } ?></td></tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_url_help'];?><div id="huhu"></div></td></tr>
          <tr>
            <td><label for="lang"><img src="./stuff/img/icons/world.png" alt="" /> <?php echo $lang['config_language'];?>:</label></td>
            <td>
              <select name="lang" id="lang" class="custom long">
                <?php
                  foreach ($languages as $lang_abbr => $lang_name) {
                    $sel = ($conf['lang'] == $lang_abbr) ? " selected='selected'" : "";
                    echo "<option value='$lang_abbr'$sel>$lang_name</option>";
                  }
                ?>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_language_help'];?></td></tr>
          
          <tr>
            <td><label for="admin_theme"><img src="./stuff/img/icons/theme.png" alt="" /> <?php echo $lang['config_admin_theme'];?>:</label></td>
            <td>
              <select name="admin_theme" id="admin_theme" class="custom extra">
                <?php
                  $themes_dir = dir(MN_ROOT . DIR_THEMES);

                  while ($t_dir = $themes_dir->read()) {
                    if ($t_dir == '.' || $t_dir == '..' || !is_dir(MN_ROOT . DIR_THEMES . $t_dir)) continue;
                    $sel = ($conf['admin_theme'] == $t_dir) ? " selected='selected'" : "";
                    $t_name = file_get_contents(MN_ROOT . DIR_THEMES . $t_dir . '/info.txt');
                    echo '<option value="' . $t_dir . '"' . $sel . '>' . $t_name . '</option>';
                  }
                ?>
              </select> <?php if (file_exists('./stuff/themes/' . $conf['admin_theme'] . '/theme_config.php')) { ?><a href="./mn-config.php?action=theme" class="tooltip" title="<?php echo $lang['config_theme_config'];?>"><img src="./stuff/img/icons/config.png" alt="" /></a><?php } ?>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_theme_help'];?></td></tr>

          <tr>
            <td><label for="admin_wysiwyg1"><img src="./stuff/img/icons/wysiwyg.png" alt="" /> <?php echo $lang['config_admin_wysiwyg'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="admin_wysiwyg1" name="admin_wysiwyg" value="true"<?php if (($conf['admin_wysiwyg']) || $conf['admin_wysiwyg'] == 'simple' || $conf['admin_wysiwyg'] == 'advanced') echo ' checked="checked"';?>> <label for="admin_wysiwyg1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="admin_wysiwyg2" name="admin_wysiwyg" value="false"<?php if (!$conf['admin_wysiwyg']) echo ' checked="checked"';?>> <label for="admin_wysiwyg2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_wysiwyg_help'];?></td></tr>

		  <tr>
		    <td><label for="admin_multiupload1"><img src="./stuff/img/icons/folder-up.png" alt="" /> <?php echo $lang['config_admin_multiupload'];?>:</label></td>
		    <td>
		      <input type="radio" class="radio" id="admin_multiupload1" name="admin_multiupload" value="true"<?php if (!isset($conf['admin_multiupload']) || ($conf['admin_multiupload'])) echo ' checked="checked"';?>> <label for="admin_multiupload1" class="custom"><?php echo $lang['uni_yes'];?></label>
		      <input type="radio" class="radio secondrb" id="admin_multiupload2" name="admin_multiupload" value="false"<?php if (isset($conf['admin_multiupload']) && !$conf['admin_multiupload']) echo ' checked="checked"';?>> <label for="admin_multiupload2" class="custom"><?php echo $lang['uni_no'];?></label>
		    </td>
		  </tr>
		  <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_multiupload_help'];?></td></tr>

		  <tr>
		    <td><label for="admin_thumb_size"><img src="./stuff/img/icons/image.png" alt="" /> <?php echo $lang['config_admin_thumb_size'];?>:</label></td>
		    <td><input type="number" class="custom" name="admin_thumb_size" id="admin_thumb_size" size="3" min="20" max="300" value="<?php echo (isset($conf['admin_thumb_size']) && is_numeric($conf['admin_thumb_size'])) ? $conf['admin_thumb_size'] : $default['thumb_size'];?>" /><span class="help">px</span> </td>
		  </tr>
		  <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_thumb_size_help'];?></td></tr>

          <tr>
            <td><label for="time_adj"><img src="./stuff/img/icons/time.png" alt="" /> <?php echo $lang['config_time_adj'];?>:</label></td>
            <td>
              <select name="time_adj" id="time_adj" class="custom">
                <?php
                  for ($i=-12;$i<=12;$i++) {
                    $j = ($i > 0) ? '+' . $i : $i;
                    $sel = ($conf['time_adj'] == $i) ? ' selected="selected"' : '';
                    echo '<option value="' . $i . '"' . $sel . '>' . $j . '</option>';
                  }
                ?>
              </select>
              <em class="help"><?php echo date('d.m.Y H:i', time() + ($conf['time_adj'] * 3600));?></em>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_time_adj_help'] . ' ' . date('d.m.Y H:i');?></td></tr>
         
          <tr>
            <td><label for="admin_update_check1"><img src="./stuff/img/icons/date-go.png" alt="" /> <?php echo $lang['config_admin_update_check'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="admin_update_check1" name="admin_update_check" value="true"<?php if (!isset($conf['admin_update_check']) || $conf['admin_update_check']) echo ' checked="checked"';?>> <label for="admin_update_check1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="admin_update_check2" name="admin_update_check" value="false"<?php if (isset($conf['admin_update_check']) && !$conf['admin_update_check']) echo ' checked="checked"';?>> <label for="admin_update_check2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_update_check_help'];?></td></tr>
         
          
          <tr>
            <td><label for="admin_icons1"><img src="./stuff/img/icons/edit.png" alt="" /> <?php echo $lang['config_admin_icons'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="admin_icons1" name="admin_icons" value="true"<?php if ($conf['admin_icons']) echo ' checked="checked"';?>> <label for="admin_icons1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="admin_icons2" name="admin_icons" value="false"<?php if (!$conf['admin_icons']) echo ' checked="checked"';?>> <label for="admin_icons2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_admin_icons_help'];?></td></tr>

        </table>
        
    	</div>





    	<div id="t-2" class="t-content">
    		
    		<table class="config-edit">
          <tr><td class="labels"><label for="web_title"><img src="./stuff/img/icons/title.png" alt="" /> <?php echo $lang['config_web_title'];?>:</label></td><td class="inputs"><input type="text" id="web_title" name="web_title" class="text extra" value="<?php echo check_text($conf['web_title']);?>" /> <input type="checkbox" name="web_title_header" class="tooltip" value="true"<?php echo (isset($conf['web_title_header']) && $conf['web_title_header'] == true) ? ' checked="checked"' : '';?> title="<?php echo $lang['config_web_title_checkbox'];?>" /> </td></tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_title_help'];?></td></tr>
          
          <tr><td class="labels"><label for="web_url"><img src="./stuff/img/icons/link.png" alt="" /> <?php echo $lang['config_web_url'];?>:</label></td><td class="inputs"><input type="text" id="web_url" name="web_url" class="text" value="<?php echo check_text($conf['web_url']);?>" /></td></tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_url_help'];?></td></tr>

          <tr>
            <td><label for="format1"><img src="./stuff/img/icons/html.png" alt="" /> <?php echo $lang['config_web_format'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="web_format2" name="web_format" value="html"<?php if ($conf['web_format'] == 'html' || empty($conf['web_format'])) echo ' checked="checked"';?>> <label for="web_format2" class="custom">HTML</label>
              <input type="radio" class="radio secondrb" id="web_format1" name="web_format" value="xhtml"<?php if ($conf['web_format'] == 'xhtml') echo ' checked="checked"';?>> <label for="web_format1" class="custom">XHTML</label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_format_help'];?></td></tr>
          
          <tr>
            <td><label for="web_encoding"><img src="./stuff/img/icons/encoding.png" alt="" /> <?php echo $lang['config_web_encoding'];?>:</label></td>
            <td>
              <select name="web_encoding" id="web_encoding" class="custom long">
                <?php
                  foreach ($encodings as $encoding_abbr => $encoding_name) {
                    $sel = ($conf['web_encoding'] == $encoding_abbr) ? " selected='selected'" : "";
                    echo '<option value="' . $encoding_abbr . '"' . $sel . '>' . $encoding_name . '</option>';
                  }
                ?>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_encoding_help'];?></td></tr>

          <tr>
            <td><label for="web_section_titles1"><img src="./stuff/img/icons/page-header.png" alt="" /> <?php echo $lang['config_web_section_titles'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="web_section_titles1" name="web_section_titles" value="true"<?php if (!isset($conf['web_section_titles']) || $conf['web_section_titles']) echo ' checked="checked"';?>> <label for="web_section_titles1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="web_section_titles2" name="web_section_titles" value="false"<?php if (isset($conf['web_section_titles']) && !$conf['web_section_titles']) echo ' checked="checked"';?>> <label for="web_section_titles2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_section_titles_help'];?></td></tr>
        
          <tr>
            <td><label for="web_powered_by1"><img src="./stuff/img/icons/information.png" alt="" /> <?php echo $lang['config_web_powered_by'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="web_powered_by1" name="web_powered_by" value="true"<?php if ($conf['web_powered_by']) echo ' checked="checked"';?>> <label for="web_powered_by1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="web_powered_by2" name="web_powered_by" value="false"<?php if (!$conf['web_powered_by']) echo ' checked="checked"';?>> <label for="web_powered_by2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_powered_by_help'];?></td></tr>

        </table>
        
    	</div>





      <div id="t-3" class="t-content">

    		<table class="config-edit">

          <tr><td class="labels"><label for="web_posts_count"><img src="./stuff/img/icons/posts.png" alt="" /> <?php echo $lang['config_web_posts_count'];?>:</label></td><td class="inputs"><input type="number" class="custom" name="web_posts_count" id="web_posts_count" size="2" value="<?php echo $conf['web_posts_count'];?>" /></td></tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_posts_count_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="web_pagination1"><img src="./stuff/img/icons/pagination.png" alt="" /> <?php echo $lang['config_web_pagination'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="web_pagination1" name="web_pagination" value="true"<?php if ($conf['web_pagination']) echo ' checked="checked"';?>> <label for="web_pagination1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="web_pagination2" name="web_pagination" value="false"<?php if (!$conf['web_pagination']) echo ' checked="checked"';?>> <label for="web_pagination2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_pagination_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="web_counter1"><img src="./stuff/img/icons/chart-bar.png" alt="" /> <?php echo $lang['config_web_counter'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="web_counter1" name="web_counter" value="true"<?php if ($conf['web_counter']) echo ' checked="checked"';?>> <label for="web_counter1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="web_counter2" name="web_counter" value="false"<?php if (!$conf['web_counter']) echo ' checked="checked"';?>> <label for="web_counter2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_web_counter_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="posts_image1"><img src="./stuff/img/icons/image.png" alt="" /> <?php echo $lang['config_posts_image'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="posts_image1" name="posts_image" value="true"<?php if (isset($conf['posts_image']) && $conf['posts_image']) echo ' checked="checked"';?>> <label for="posts_image1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="posts_image2" name="posts_image" value="false"<?php if (!isset($conf['posts_image']) || !$conf['posts_image']) echo ' checked="checked"';?>> <label for="posts_image2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_posts_image_help'];?></td></tr>

          <tr><td class="config-legend"><label for="web_postimg_size"><img src="./stuff/img/icons/image-edit.png" alt="" /> <?php echo $lang['config_posts_image_size'];?>:</label></td><td><input type="number" class="custom" name="posts_image_size" id="posts_image_size" size="2" value="<?php echo (!isset($conf['posts_image_size']) || !is_numeric($conf['posts_image_size'])) ? '0' : $conf['posts_image_size'];?>" /><span class="help">px</span></td></tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_posts_image_size_help'];?></td></tr>

        </table>

    	</div>





    	<div id="t-4" class="t-content">
    		
    		<table class="config-edit">
          <tr>
            <td class="labels"><label for="comments1"><img src="./stuff/img/icons/comments.png" alt="" /> <?php echo $lang['config_comments'];?>:</label></td>
            <td>
              <select name="comments" class="custom long">
                <option value="1"<?php if ($conf['comments'] === '1' || $conf['comments'] === true) echo ' selected="selected"';?>><?php echo $lang['config_comments_a_all'];?></option>
                <option value="2"<?php if ($conf['comments'] === '2') echo ' selected="selected"';?>><?php echo $lang['config_comments_a_registered'];?></option>
                <option value="0"<?php if ($conf['comments'] === '0' || $conf['comments'] === false) echo ' selected="selected"';?>><?php echo $lang['config_comments_a_nobody'];?></option>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="comments_order"><img src="./stuff/img/icons/comments-order.png" alt="" /> <?php echo $lang['config_comments_order'];?>:</label></td>
            <td>
              <select name="comments_order" class="custom long">
                <option value="normal"<?php if ($conf['comments_order'] == 'normal') echo ' selected="selected"';?>><?php echo $lang['config_comments_order_normal'];?></option>
                <option value="reverse"<?php if ($conf['comments_order'] == 'reverse') echo ' selected="selected"';?>><?php echo $lang['config_comments_order_reverse'];?></option>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_order_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_approval"><img src="./stuff/img/icons/tick.png" alt="" /> <?php echo $lang['config_comments_approval'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_approval1" name="comments_approval" value="true"<?php if (isset($conf['comments_approval']) && $conf['comments_approval']) echo ' checked="checked"';?>> <label for="comments_approval1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_approval2" name="comments_approval" value="false"<?php if (!isset($conf['comments_approval']) || !$conf['comments_approval']) echo ' checked="checked"';?>> <label for="comments_approval2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_approval_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_antispam"><img src="./stuff/img/icons/antispam.png" alt="" /> <?php echo $lang['config_comments_antispam'];?>:</label></td>
            <td>
              <select name="comments_antispam" class="custom">
                <?php for ($i=1;$i<=20;$i++) {$sel = ($conf['comments_antispam'] == $i) ? ' selected="selected"' : ''; echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';}?>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_antispam_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_antiflood"><img src="./stuff/img/icons/shield.png" alt="" /> <?php echo $lang['config_comments_antiflood'];?>:</label></td>
            <td>
              <input type="number" class="custom" name="comments_antiflood" id="comments_antiflood" size="2" value="<?php echo (isset($conf['comments_antiflood']) && is_numeric($conf['comments_antiflood'])) ? $conf['comments_antiflood'] : '30';?>" /> <label for="comments_antiflood" class="custom"><?php echo $lang['config_seconds'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_antiflood_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_captcha"><img src="./stuff/img/icons/captcha.png" alt="" /> <?php echo $lang['config_comments_captcha'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_captcha1" name="comments_captcha" value="true"<?php if (isset($conf['comments_captcha']) && ($conf['comments_captcha'])) echo ' checked="checked"';?>> <label for="comments_captcha1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_captcha2" name="comments_captcha" value="false"<?php if (!isset($conf['comments_captcha']) || !$conf['comments_captcha']) echo ' checked="checked"';?>> <label for="comments_captcha2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_captcha_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="comments_bb"><img src="./stuff/img/icons/comments-bb.png" alt="" /> <?php echo $lang['config_comments_bb'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_bb1" name="comments_bb" value="true"<?php if ($conf['comments_bb']) echo ' checked="checked"';?>> <label for="comments_bb1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_bb2" name="comments_bb" value="false"<?php if (!$conf['comments_bb']) echo ' checked="checked"';?>> <label for="comments_bb2" class="custom"><?php echo $lang['uni_no'];?></label>
              <span class="simurl fr<?php if (!$conf['comments_bb']) echo ' hide';?>" id="bb-buttons-viewer"><?php echo $lang['config_comments_bb_buttons'];?></span>
              <div id="bb-buttons" class="hide">
                <input type="checkbox" name="bb_bold" id="bb-bold" value="1"<?php if($conf['comments_bb_buttons'][0] == '1') echo ' checked="checked"';?> /><label for="bb-bold"><img src="./stuff/img/icons/text-bold.png" alt="Bold text" class="tooltip" title="<?php echo $lang['web_bbcode_bold'];?>" height="16" width="16" /></label>
                <input type="checkbox" name="bb_italics" id="bb-italic" value="1"<?php if($conf['comments_bb_buttons'][1] == '1') echo ' checked="checked"';?> /><label for="bb-italic"><img src="./stuff/img/icons/text-italic.png" alt="Italics" class="tooltip" title="<?php echo $lang['web_bbcode_italics'];?>" height="16" width="16" /></label>
                <input type="checkbox" name="bb_underline" id="bb-underline" value="1"<?php if($conf['comments_bb_buttons'][2] == '1') echo ' checked="checked"';?> /><label for="bb-underline"><img src="./stuff/img/icons/text-underline.png" alt="Underline" class="tooltip" title="<?php echo $lang['web_bbcode_underline'];?>" height="16" width="16" /></label>
                <input type="checkbox" name="bb_strikethrough" id="bb-strikethrough" value="1"<?php if($conf['comments_bb_buttons'][3] == '1') echo ' checked="checked"';?> /><label for="bb-strikethrough"><img src="./stuff/img/icons/text-strikethrough.png" alt="Strikethrough" class="tooltip" title="<?php echo $lang['web_bbcode_strikethrough'];?>" height="16" width="16" /></label>
                <input type="checkbox" name="bb_link" id="bb-link" value="1"<?php if($conf['comments_bb_buttons'][4] == '1') echo ' checked="checked"';?> /><label for="bb-link"><img src="./stuff/img/icons/text-link.png" alt="Link" class="tooltip" title="<?php echo $lang['web_bbcode_link'];?>" height="16" width="16" /></label>
                <input type="checkbox" name="bb_code" id="bb-code" value="1"<?php if($conf['comments_bb_buttons'][5] == '1') echo ' checked="checked"';?> /><label for="bb-code"><img src="./stuff/img/icons/text-code.png" alt="Code" class="tooltip" title="<?php echo $lang['web_bbcode_code'];?>" height="16" width="16" /></label>
              </div>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_bb_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="comments_smile1"><img src="./stuff/img/icons/smile.png" alt="" /> <?php echo $lang['config_comments_smiles'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_smiles1" name="comments_smiles" value="true"<?php if ($conf['comments_smiles']) echo ' checked="checked"';?>> <label for="comments_smiles1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_smiles2" name="comments_smiles" value="false"<?php if (!$conf['comments_smiles']) echo ' checked="checked"';?>> <label for="comments_smiles2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_smiles_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_links_auto1"><img src="./stuff/img/icons/link-auto.png" alt="" /> <?php echo $lang['config_comments_links_auto'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_links_auto1" name="comments_links_auto" value="true"<?php if ($conf['comments_links_auto'] || empty($conf['comments_links_auto'])) echo ' checked="checked"';?>> <label for="comments_links_auto1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_links_auto2" name="comments_links_auto" value="false"<?php if (!$conf['comments_links_auto']) echo ' checked="checked"';?>> <label for="comments_links_auto2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_links_auto_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="comments_links_target1"><img src="./stuff/img/icons/link-target.png" alt="" /> <?php echo $lang['config_comments_links_target'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_links_target1" name="comments_links_target" value="true"<?php if ($conf['comments_links_target']) echo ' checked="checked"';?>> <label for="comments_links_target1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_links_target2" name="comments_links_target" value="false"<?php if (!$conf['comments_links_target'] || empty($conf['comments_links_target'])) echo ' checked="checked"';?>> <label for="comments_links_target2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_links_target_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label for="comments_links_nofollow1"><img src="./stuff/img/icons/link-nofollow.png" alt="" /> <?php echo $lang['config_comments_links_nofollow'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="comments_links_nofollow1" name="comments_links_nofollow" value="true"<?php if ($conf['comments_links_nofollow'] || empty($conf['comments_links_nofollow'])) echo ' checked="checked"';?>> <label for="comments_links_nofollow1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="comments_links_nofollow2" name="comments_links_nofollow" value="false"<?php if (!$conf['comments_links_nofollow']) echo ' checked="checked"';?>> <label for="comments_links_nofollow2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_links_nofollow_help'];?></td></tr>
          
          <tr>
            <td class="config-legend"><label><img src="./stuff/img/icons/textfield.png" alt="" /> <?php echo $lang['config_comments_optional_fields'];?>:</label></td>
            <td>
              <input type="checkbox" class="checkbox" id="comments_field_email" name="comments_field_email" value="true"<?php if ($conf['comments_field_email']) echo ' checked="checked"';?>> <label for="comments_field_email" class="custom"><?php echo $lang['comm_email'];?></label><br />
              <input type="checkbox" class="checkbox" id="comments_field_www" name="comments_field_www" value="true"<?php if ($conf['comments_field_www']) echo ' checked="checked"';?>> <label for="comments_field_www" class="custom"><?php echo $lang['comm_www'];?></label><br />
              <input type="checkbox" class="checkbox" id="comments_field_preview" name="comments_field_preview" value="true"<?php if ($conf['comments_field_preview']) echo ' checked="checked"';?>> <label for="comments_field_preview" class="custom"><?php echo $lang['comm_preview_button'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_comments_optional_fields_help'];?></td></tr>

        </table>
        
    	</div>





      <div id="t-5" class="t-content">

        <table class="config-edit">
          <tr>
            <td class="labels"><label for="users_registration1"><img src="./stuff/img/icons/user-go.png" alt="" /> <?php echo $lang['config_users_registration'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="users_registration1" name="users_registration" value="true"<?php if ($conf['users_registration']) echo ' checked="checked"';?>> <label for="users_registration1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="users_registration2" name="users_registration" value="false"<?php if (!$conf['users_registration']) echo ' checked="checked"';?>> <label for="users_registration2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_users_registration_help'];?></td></tr>

          <tr>
            <td class="config-legend"><label for="users_default_group"><img src="./stuff/img/icons/group-go.png" alt="" /> <?php echo $lang['config_users_default_group'];?>:</label></td>
            <td>
              <select name="users_default_group" class="custom long">
                <?php
                  $groups = load_basic_data('groups');
                  foreach ($groups as $group_id => $group_name) {
                    $sel = ($group_id == $conf['users_default_group']) ? ' selected="selected"' : '';
                    if ($group_id == 1) continue;
                    else echo '<option value="' . $group_id . '"' . $sel . '>' . $group_name . '</option>';
                  }
                ?>
              </select>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_users_default_group_help'];?></td></tr>
          
          <tr>
            <td class="labels"><label for="users_perm_login1"><img src="./stuff/img/icons/permanent-login.png" alt="" /> <?php echo $lang['config_users_perm_login'];?>:</label></td>
            <td>
              <input type="radio" class="radio" id="users_perm_login1" name="users_perm_login" value="true"<?php if ($conf['users_perm_login']) echo ' checked="checked"';?>> <label for="users_perm_login1" class="custom"><?php echo $lang['uni_yes'];?></label>
              <input type="radio" class="radio secondrb" id="users_perm_login2" name="users_perm_login" value="false"<?php if (!$conf['users_perm_login']) echo ' checked="checked"';?>> <label for="users_perm_login2" class="custom"><?php echo $lang['uni_no'];?></label>
            </td>
          </tr>
          <tr class="config-help"><td colspan="2"><?php echo $lang['config_users_perm_login_help'];?></td></tr>
          
          <tr>
            <td class="labels vat">
              <label for="users_avatar_standard"><img src="./stuff/img/icons/vcard.png" alt="" /> <?php echo $lang['config_users_avatar_size'];?>:</label>
              <p class="help avatar-help"><?php echo $lang['config_users_avatar_size_help'];?></p>
            </td>
            <td>
              <input type="number" class="custom" name="users_avatar_standard" id="users_avatar_standard" size="2" value="<?php echo (isset($conf['users_avatar_standard']) && is_numeric($conf['users_avatar_standard'])) ? $conf['users_avatar_standard'] : $default['avatar_size_standard'];?>" /><span class="help">px</span> <label for="users_avatar_standard" class="custom">(<?php echo $lang['config_users_avatar_size_standard'];?>)</label><br />
              <input type="number" class="custom" name="users_avatar_small" id="users_avatar_small" size="2" value="<?php echo (isset($conf['users_avatar_small']) && is_numeric($conf['users_avatar_small'])) ? $conf['users_avatar_small'] : $default['avatar_size_small'];?>" /><span class="help">px</span> <label for="users_avatar_small" class="custom">(<?php echo $lang['config_users_avatar_size_small'];?>)</label><br />
              <input type="number" class="custom" name="users_avatar_mini" id="users_avatar_mini" size="2" value="<?php echo (isset($conf['users_avatar_mini']) && is_numeric($conf['users_avatar_mini'])) ? $conf['users_avatar_mini'] : $default['avatar_size_mini'];?>" /><span class="help">px</span> <label for="users_avatar_mini" class="custom">(<?php echo $lang['config_users_avatar_size_mini'];?>)</label>
            </td>
          </tr>

        </table>

      </div>

    </div>
    
    <p class="c">
      <input type="hidden" name="action" value="save" />
      <input type="hidden" name="t-id" id="t-id" value="" />
      <button type="submit" name="add"><img src="./stuff/img/icons/config-edit.png" alt="" width="16" height="16" /> <?php echo $lang['config_save'];?></button>
    </p>
    
    </fieldset>
  </form>
		
<?php
  }

  overall_footer();
?>