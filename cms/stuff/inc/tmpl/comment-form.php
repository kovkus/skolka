

  <form action="<?php echo $conf['admin_url'] . '/mn-comments.php';?>" method="post" id="mn-comment-form">
    <fieldset>
      <legend><?php echo encoding($lang['comm_comment_addition']);?></legend>
      
      <?php
        if (isset($_GET['mn_msg'])) {
          $mn_comm_msg = ($_GET['mn_msg'] == 'c_added') ? $lang['web_msg_comment_added'] : $lang['web_msg_comment_sent'];
          $mn_wrap = ' style="display: none;"';
          
          echo encoding('<div class="mn-success"><img src="' . $conf['admin_url'] . '/stuff/img/icons/tick.png" alt="" /> ' . $mn_comm_msg . ' (<a href="#mn-comment-form" onclick="ShowHide(\'mn-comment-wrap\')">' . $lang['web_comment_add_another'] . '</a>)</div>');
          
          
        }
        else $mn_wrap = '';
      ?>

      <div id="mn-comment-wrap"<?php echo $mn_wrap;?>>
      
        <?php
          if ((isset($_SESSION['mn_logged'])) || (isset($_COOKIE['mn_logged']) && isset($_COOKIE['mn_user_name'])) || (isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_user_hash']))) {
            $mnlogged = true;
            $mn_username = (isset($_SESSION['mn_user_name'])) ? $_SESSION['mn_user_name'] : $_COOKIE['mn_user_name'];
            echo encoding('<p>' . $lang['web_logged_in_as'] . ' <strong>' . $mn_username . '</strong> (<a href="' . $conf['admin_url'] . '/mn-login.php?action=logout&amp;redir=referer">' . $lang['web_logout'] . '</a>). <input type="hidden" name="comment_author" value="' . check_text($mn_username) . '" /></p>') . "\n\n";
          }
          elseif (!empty($_COOKIE['mn_user_name']) && !isset($_COOKIE['mn_user_hash'])) {
        ?>
          <input type="text" name="comment_author" id="comment_author" value="<?php echo check_text($_COOKIE['mn_user_name']);?>" /> <label for="comment_author"><?php echo  encoding($lang['comm_login']);?> *</label> <a href="<?php echo $conf['admin_url'];?>/mn-login.php?mode=logout&amp;redir=referer">x</a><br />
          <input type="password" name="comment_pass" id="comment_pass" value="" /> <label for="comment_pass"><?php echo  encoding($lang['comm_pass']);?> *</label><br />
        <?php } else { ?>
          <input type="text" name="comment_author" id="comment_author" value="" /> <label for="comment_author"><?php echo  encoding($lang['comm_author']);?> *</label><br />
          <?php if ($conf['comments_field_email']) { ?><input type="text" name="comment_email" id="comment_email" value="@" /> <label for="comment_email"><?php echo  encoding($lang['comm_email']);?></label><br /><?php } ?>
          <?php if ($conf['comments_field_www']) { ?><input type="text" name="comment_www" id="comment_www" value="http://" /> <label for="comment_www"><?php echo  encoding($lang['comm_www']);?></label><br /><?php } ?>
        <?php } ?>
        
        
        <?php
        
        	if (file_exists(MN_ROOT . $file['xfields'])) {
        	
        		$xfields = get_unserialized_array('xfields');
        		foreach ($xfields as $xVar => $x) {
        			if ($x['section'] != 'comments') continue;
        			else {

        				if (isset($x['type']) && $x['type'] == 'select') {
        					$xField = '<select name="x' . $xVar . '" id="mn-x' . $xVar . '" class="mn-xfield-select">';
        					foreach ($x['options'] as $oKey => $oValue) {
        						$xField .= '<option value="' . $oKey . '">' . $oValue . '</option>';
        					}
        					$xField .= '</select>';
        				}
        				else {
        					$xField = '<input type="text" name="x' . $xVar . '" id="mn-x' . $xVar . '" value="" class="mn-xfield-input" />';
        				}
        				
        				echo $xField . ' <label for="mn-x' . $xVar . '">' . $x['name'] . '</label><br />';
        			}
        		}
        		
        		echo '<input type="hidden" name="x_fields" value="true" />';
        	
        	}
        
        ?>
        
        
        <label for="comment_text">Text *<br /></label>
        <?php include MN_ROOT . '/stuff/inc/tmpl/comment-form-buttons.php';?>
        <textarea name="comment_text" id="comment_text" rows="5" cols="40"></textarea>
        <br />

        <?php if (!isset($mnlogged) && isset($conf['comments_approval']) && ($conf['comments_approval'])) echo encoding($lang['comm_info_approval']) . '<br />';?>

        <span id="spam-span"><?php echo str_replace('%n%', '<strong>' . $lang['num'][$conf['comments_antispam']] . '</strong>', encoding($lang['comm_antispam']));?>: <input type="text" name="robot" id="spam-input" style="width:20px;" value="" /><br /></span>

        <?php
          if (!isset($mnlogged) && isset($conf['comments_captcha']) && ($conf['comments_captcha'])) {
            echo '<script>var RecaptchaOptions = {custom_translations : { instructions_visual : "' . $lang['comm_captcha_help'] . ':"},theme : "white"};</script>';
            require_once(MN_ROOT . '/stuff/inc/recaptchalib.php');
            echo recaptcha_get_html('6LfnaQoAAAAAAJ1Jcz_JKqzvhpIb9aigaALEzsj8');
          }
        ?>

        <input type="hidden" name="action" value="add-comment" />
        <input type="hidden" name="post_id" value="<?php echo $post_id;?>" />
        <?php if ($conf['comments_field_preview']) { ?><input type="submit" name="preview" id="mn-comment-preview" class="mn-submit" value="<?php echo encoding($lang['comm_preview']);?>" /><?php } ?>
        <input type="submit" name="submit" id="mn-comment-submit" class="mn-submit" value="<?php echo encoding($lang['comm_add_comment']);?>" />
        
      </div>

    </fieldset>
  </form>

  <script type="text/javascript">
    document.getElementById('spam-input').value='<?php echo $conf['comments_antispam'];?>';
    document.getElementById('spam-span').style.display = 'none';
  </script>
