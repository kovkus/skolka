<?php

	$xf_vars = '';

	if (file_exists(MN_ROOT . $file['xfields'])) {
	
		$xfields = get_unserialized_array('xfields');
		foreach ($xfields as $xVar => $x) {
			if ($x['section'] != 'users') continue;
			else $xf_vars .= '<li><span class="variable">{x' . strtoupper($x['var']) . '}</span> &mdash; ' . $x['name'] . '</li>';
		}
	
	}

?>
<ul class="tmpl-variables">
        <li><span class="variable">{USER_ID}</span> &mdash; <?php echo $lang['tmpl_var_user_id'];?></li>
        <li><span class="variable">{USERNAME}</span> &mdash; <?php echo $lang['tmpl_var_user_username'];?></li>
        <li><span class="variable">{AVATAR}</span> &mdash; <?php echo $lang['tmpl_var_user_avatar'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_standard'] . '&times;' . @$conf['users_avatar_standard'] . ')';?></span> [<span class="simurl toggle450" rel="tmpl-avatar"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-avatar" class="hide">
            <li><span class="variable">{AVATAR_SMALL}</span> &mdash; <?php echo $lang['tmpl_var_user_avatar_small'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_small'] . '&times;' . @$conf['users_avatar_small'] . ')';?></span></li>
            <li><span class="variable">{AVATAR_MINI}</span> &mdash; <?php echo $lang['tmpl_var_user_avatar_mini'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_mini'] . '&times;' . @$conf['users_avatar_mini'] . ')';?></span></li>
          </ul>
        </li>
        <li><span class="variable">{BIRTHDAY}</span> &mdash; <?php echo $lang['tmpl_var_user_birthday'];?></li>
        <li><span class="variable">{EMAIL}</span> &mdash; <?php echo $lang['tmpl_var_user_email'];?></li>
        <li><span class="variable">{GENDER}</span> &mdash; <?php echo $lang['tmpl_var_user_gender'];?></li>
        <li><span class="variable">{GROUP}</span> &mdash; <?php echo $lang['tmpl_var_user_group'];?></li>
        <li><span class="variable">{ICQ}</span> &mdash; <?php echo $lang['tmpl_var_user_icq'];?></li>
        <li><span class="variable">{JABBER}</span> &mdash; <?php echo $lang['tmpl_var_user_jabber'];?></li>
        <li><span class="variable">{LOCATION}</span> &mdash; <?php echo $lang['tmpl_var_user_location'];?></li>
        <li><span class="variable">{MSN}</span> &mdash; <?php echo $lang['tmpl_var_user_msn'];?></li>
        <li><span class="variable">{NICKNAME}</span> &mdash; <?php echo $lang['tmpl_var_user_nickname'];?></li>
        <li><span class="variable">{SKYPE}</span> &mdash; <?php echo $lang['tmpl_var_user_skype'];?></li>
        <li><span class="variable">{WWW}</span> &mdash; <?php echo $lang['tmpl_var_user_www'];?></li>
        <li><span class="variable">{ABOUT}</span> &mdash; <?php echo $lang['tmpl_var_user_about'];?></li>
        <li><span class="variable">{OTHER1}</span> &mdash; <?php echo $lang['tmpl_var_user_other1'];?></li>
        <li><span class="variable">{OTHER2}</span> &mdash; <?php echo $lang['tmpl_var_user_other2'];?></li>
        <?php echo $xf_vars;?>
      </ul>