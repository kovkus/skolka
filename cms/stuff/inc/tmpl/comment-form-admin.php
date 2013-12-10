<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <style>
    body {text-align: center; background: #f0f0f0; font-family: Verdana, Arial, lucida, sans-serif; font-size: 0.9em; padding: 10px;}
    form {margin: 20px 0 0 0;}
    form table {font-size: 0.9em;}
    form fieldset {border: 1px solid #c0c0c0; width: 440px; margin: 0 auto;}
    form legend {border: 1px solid #c0c0c0; color: #464646; margin: 0 10px; padding: 5px 10px; text-align: left; text-shadow: #fff 1px 1px 1px;}
    form textarea {height: 140px; padding: 3px; width: 420px;}
    form input {padding: 3px 5px; width: 240px;}
    form input.submit {padding: 10px 0; color: #464646; cursor: pointer; font-weight: bold; margin: 0 5px; text-align: center; width: 180px;}
    #mn-comment-buttons {margin: 0 0 0 4px;}
    #mn-comment-buttons img {cursor: pointer; padding: 3px; border: 1px solid #ccc; margin-left: -4px;}
    #mn-comment-buttons img:hover {background: #ccc;}
    form fieldset, form legend, form textarea, .info-red, #mn-comment-buttons img, #preview, #simlegend {
      -webkit-border-radius: 5px;
      -moz-border-radius: 5px;
      border-radius: 5px;
    }
    .info-red {width: 800px; text-shadow: 1px 1px 1px #fff; margin: 0 auto; color: crimson; border: 1px solid crimson; padding: 10px; font-weight: bold; text-align: center; background: #f9dbdb;}
    #preview {background: #f8f8f8; border: 1px solid #808080; margin: 0 auto; margin-top: 10px; padding: 10px; text-align: justify; width: 600px;}
    #preview span#simlegend {background: #f8f8f8; border: 1px solid #808080; color: #464646; font-weight: bold; padding: 3px 5px; position: absolute; text-shadow: #fff 1px -1px 1px; top: 10px;}
    .star {color: crimson;}
    .backlink {padding: 0 0 0 300px;}
    .c {text-align: center;}
    a {color: blue;}
    a:hover {color: crimson;}
  </style>
  <title><?php echo $conf['web_title'] . ' | ' . $lang['comm_comment_addition'];?></title>
  </head>
  <body>

  <?php
    if (isset($_POST['preview']) && !empty($_POST['comment_text'])) echo '<div id="preview"><span id="simlegend">' . $lang['comm_preview'] . '</span>' . comment_format(check_comment_text($_POST['comment_text'])) . '</div>';
    else echo '<div class="info-red"><img src="./stuff/img/icons/exclamation.png" alt="" /> ' . $error_msg . '</div>';
  ?>

    <form action="./mn-comments.php" method="post">
      <fieldset>
        <legend><?php echo $lang['comm_comment_addition'];?></legend>
        <?php
          $xfields_rows = '';
        
          if (file_exists(MN_ROOT . $file['xfields'])) {
          
          	$xfields = get_unserialized_array('xfields');
          	foreach ($xfields as $xVar => $x) {
          		if ($x['section'] != 'comments') continue;
          		else {
          		
          			$thisVar = (isset($_POST['x' . $xVar])) ? check_text($_POST['x' . $xVar], true) : @$var['xfields_array'][$xVar];
          		
          			if (isset($x['type']) && $x['type'] == 'select') {
          				$xField = '<select name="x' . $xVar . '" id="x' . $xVar . '" class="mn-xfield-select">';
          				foreach ($x['options'] as $oKey => $oValue) {
          					$sel = ($thisVar == $oKey) ? ' selected="selected"' : '';
          					$xField .= '<option value="' . $oKey . '"' . $sel . '>' . $oValue . '</option>';
          				}
          				$xField .= '</select>';
          			}
          			else {
          				$xField = '<input type="text" name="x' . $xVar . '" id="mn-x' . $xVar . '" value="' . $thisVar . '" class="mn-xfield-input" />';
          			}
          			$xfields_rows .= '<tr><td><label for="x' . $xVar . '">' . $x['name'] . ':</label></td><td>' . $xField . '</td></tr>';
          		}
          	}
          	
          	$xfields_rows .= '<input type="hidden" name="x_fields" value="true" />';

          }



          if (isset($_SESSION['mn_logged']) && $_SESSION['mn_logged']) {
          	echo '<p>' . $lang['web_logged_in_as'] . ' <strong>' . $_SESSION['mn_user_name'] . '</strong>.</p>';
          	if (!empty($xfields_rows)) echo '<table>' . $xfields_rows . '</table>';
          }

          else {
        ?>
          <table>
            <tr><td style="width: 100px;"><label for="comment_author"><?php echo $lang['comm_author'];?><span class="star">*</span> :</label></td><td><input type="text" name="comment_author" id="comment_author" value="<?php echo ($conf['web_encoding'] != 'utf-8' && !isset($_POST['form'])) ? iconv($conf['web_encoding'], 'utf-8', check_text($_POST['comment_author'])) : check_text($_POST['comment_author']);?>" /></td></tr>
          <?php if (in_array($_POST['comment_author'], $mn_users) && !$_SESSION['mn_logged']) { ?>
            <tr><td><label for="comment_pass"><?php echo $lang['comm_pass'];?><span class="star">*</span> :</label></td><td><input type="password" name="comment_pass" id="comment_pass" /></td></tr>
          <?php } else { ?>
            <?php if ($conf['comments_field_email']) { ?><tr><td><label for="comment_email"><?php echo $lang['comm_email'];?> :</label></td><td><input type="text" name="comment_email" id="comment_email" value="<?php echo check_text($_POST['comment_email']);?>" /></td></tr><?php } ?>
            <?php if ($conf['comments_field_www']) { ?><tr><td><label for="comment_www"><?php echo $lang['comm_www'];?> :</label></td><td><input type="text" name="comment_www" id="comment_www" value="<?php echo check_text($_POST['comment_www']);?>" /></td></tr><?php } ?>
          <?php
          		echo $xfields_rows;
          	}
          ?>
          
          
            <tr><td colspan="2">Text<span class="star">*</span> :</td></tr>
          </table>
        <?php
          }
          include './stuff/inc/tmpl/comment-form-buttons.php';
        ?>
        <textarea name="comment_text" id="comment_text" rows="5" cols="40"><?php echo ($conf['web_encoding'] != 'utf-8' && !isset($_POST['form'])) ? iconv($conf['web_encoding'], 'utf-8', check_text($_POST['comment_text'])) : check_text($_POST['comment_text']);?></textarea>
        <span id="spam-span"><?php echo str_replace('%n%', '<strong>' . $lang['num'][$conf['comments_antispam']] . '</strong>', $lang['comm_antispam']);?>: <input type="text" name="robot" id="spam-input" style="width:20px;" value="" /><br /></span>

        <?php
          if (!isset($_SESSION['mn_logged']) && isset($conf['comments_captcha']) && ($conf['comments_captcha'])) {
            echo '<script>var RecaptchaOptions = {custom_translations : { instructions_visual : "' . $lang['comm_captcha_help'] . ':"},theme : "white"};</script>';
            require_once(MN_ROOT . '/stuff/inc/recaptchalib.php');
            echo '<p class="c captcha">' . recaptcha_get_html('6LfnaQoAAAAAAJ1Jcz_JKqzvhpIb9aigaALEzsj8') . '</p>';
          }
        ?>

        <p>
          <input type="hidden" name="action" value="add-comment" />
          <input type="hidden" name="form" value="admin" />
          <input type="hidden" name="post_id" value="<?php echo $_POST['post_id'];?>" />
          <input type="hidden" name="redir" value="<?php echo $mn_redir;?>" />
          <?php if ($conf['comments_field_preview']) { ?><input type="submit" class="submit" name="preview" value="<?php echo $lang['comm_preview'];?>!" /><?php } ?>
          <input type="submit" class="submit" name="submit" value="<?php echo $lang['comm_add_comment'];?>!" />
        </p>
      </fieldset>
    </form>

    <script>
      document.getElementById('spam-input').value='<?php echo $conf['comments_antispam'];?>';
      document.getElementById('spam-span').style.display = 'none';
    </script>

    <p class="c backlink"><a href="<?php echo $mn_redir;?>">&laquo; <?php echo $lang['comm_back_to_post'];?></a></p>

  </body>
</html>
