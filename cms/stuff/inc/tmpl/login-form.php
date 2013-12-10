
  <form action="<?php echo $conf['admin_url'] . '/mn-login.php';?>" method="post" id="mn-comment-form">
    <fieldset>
      <legend><?php echo encoding($lang['comm_comment_addition'] . ' / ' . $lang['login_login']);?></legend>
      
      <p>
        <?php
          echo encoding($lang['comm_msg_login']);
          if ($conf['users_registration']) echo ' (<a href="' . $conf['admin_url'] . '/mn-login.php?action=register">' . encoding($lang['login_register']) . '</a>)';
        ?>
      </p>

      <input type="text" name="user_login" id="user_login" value="<?php echo check_text(@$_COOKIE['mn_user_name']);?>" /> <label for="comment_author"><?php echo  encoding($lang['login_user_login']);?> *</label><br />
      <input type="password" name="user_pass" id="user_pass" value="" /> <label for="comment_pass"><?php echo  encoding($lang['login_user_password']);?> *</label><br />

      <input type="hidden" name="action" value="login" />
      <input type="hidden" name="redir" value="<?php echo trim('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], '?') . '#mn-comment-form';?>" />
      <input type="submit" name="submit" id="mn-login-submit" value="<?php echo encoding($lang['login_log_in']);?>" />
    </fieldset>
  </form>
