<ul class="tmpl-variables">
        <li><span class="variable">{AUTHOR}</span> &mdash; <?php echo $lang['tmpl_var_comment_author'];?></li>
        <li><span class="variable">{AVATAR}</span> &mdash; <?php echo $lang['tmpl_var_comment_avatar'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_standard'] . '&times;' . @$conf['users_avatar_standard'] . ')';?></span> [<span class="simurl toggle450" rel="tmpl-avatar"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-avatar" class="hide">
            <li><span class="variable">{AVATAR_SMALL}</span> &mdash; <?php echo $lang['tmpl_var_comment_avatar_small'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_small'] . '&times;' . @$conf['users_avatar_small'] . ')';?></span></li>
            <li><span class="variable">{AVATAR_MINI}</span> &mdash; <?php echo $lang['tmpl_var_comment_avatar_mini'];?> <span class="example"><?php echo '(' . @$conf['users_avatar_mini'] . '&times;' . @$conf['users_avatar_mini'] . ')';?></span></li>
          </ul>
        </li>
        <li><span class="variable">{TEXT}</span> &mdash; <?php echo $lang['tmpl_var_comment_text'];?></li>
        <li><span class="variable">[EMAIL]</span> &amp; <span class="variable" onclick="insert_value('[/EMAIL]', document.getElementById('tmpl'))">[/EMAIL]</span> &mdash; <?php echo $lang['tmpl_var_comment_email'];?></li>
        <li><span class="variable">[WWW]</span> &amp; <span class="variable" onclick="insert_value('[/WWW]', document.getElementById('tmpl'))">[/WWW]</span> &mdash; <?php echo $lang['tmpl_var_comment_www'];?></li>
        <li><span class="variable">{COMMENT_ID}</span> &mdash; <?php echo $lang['tmpl_var_comment_id'];?></li>
        <li><span class="variable">{COMMENT_NUM}</span> &mdash; <?php echo $lang['tmpl_var_comment_num'];?></li>
        <li><span class="variable">{DATE}</span> &mdash; <?php echo $lang['tmpl_var_date'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('j.n.Y') . '</span>';?> [<span class="simurl toggle450" rel="tmpl-date"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-date" class="hide">
            <li><span class="variable">{DATE_US}</span> &mdash; <?php echo $lang['tmpl_var_date_us'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('m/d/y') . '</span>';?></li>
            <li><span class="variable">{DATE_DAY}</span> &mdash; <?php echo $lang['tmpl_var_date_day'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('j') . '</span>';?></li>
            <li><span class="variable">{DATE_DAY_ABBR}</span> &mdash; <?php echo $lang['tmpl_var_date_day_abbr'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('D') . '</span>';?></li>
            <li><span class="variable">{DATE_MONTH}</span> &mdash; <?php echo $lang['tmpl_var_date_month'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('n') . '</span>';?></li>
            <li><span class="variable">{DATE_MONTH_ABBR}</span> &mdash; <?php echo $lang['tmpl_var_date_month_abbr'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('M') . '</span>';?></li>
            <li><span class="variable">{DATE_YEAR}</span> &mdash; <?php echo $lang['tmpl_var_date_year'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('Y') . '</span>';?></li>
          </ul>
        </li>
        <li><span class="variable">{TIME}</span> &mdash; <?php echo $lang['tmpl_var_time'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('H:i') . '</span>';?> [<span class="simurl toggle450" rel="tmpl-time"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-time" class="hide">
            <li><span class="variable">{TIME_US}</span> &mdash; <?php echo $lang['tmpl_var_time_us'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('h:i a') . '</span>';?></li>
            <li><span class="variable" >{TIME_HOUR}</span> &mdash; <?php echo $lang['tmpl_var_time_hour'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('H') . '</span>';?></li>
            <li><span class="variable">{TIME_HOUR_US}</span> &mdash; <?php echo $lang['tmpl_var_time_hour_us'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('h') . '</span>';?></li>
            <li><span class="variable">{TIME_MIN}</span> &mdash; <?php echo $lang['tmpl_var_time_minute'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('i') . '</span>';?></li>
            <li><span class="variable">{TIME_AMPM}</span> &mdash; <?php echo $lang['tmpl_var_time_ampm'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . date('a') . '</span>';?></li>
            <li><span class="variable">{TIMESTAMP}</span> &mdash; <?php echo $lang['tmpl_var_timestamp'] . ', ' . $lang['tmpl_vars_example'] . ': <span class="example">' . time() . '</span>';?></li>
          </ul>
        </li>
        <li><span class="variable">{IP_ADDRESS}</span> &mdash; <?php echo $lang['tmpl_var_comment_ip_address'];?></li>
      </ul>