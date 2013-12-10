<?php

	$xf_vars = '';

	if (file_exists(MN_ROOT . $file['xfields'])) {
	
		$xfields = get_unserialized_array('xfields');
		foreach ($xfields as $xVar => $x) {
			if ($x['section'] != 'pages') continue;
			else $xf_vars .= '<li><span class="variable">{x' . strtoupper($x['var']) . '}</span> &mdash; ' . $x['name'] . '</li>';
		}
	
	}

?>
<ul class="tmpl-variables">
        <li><span class="variable">{TITLE}</span> &mdash; <?php echo $lang['tmpl_var_page_title'];?></li>
        <li><span class="variable">{TEXT}</span> &mdash; <?php echo $lang['tmpl_var_page_text'];?></li>
        <li><span class="variable">{AUTHOR}</span> &mdash; <?php echo $lang['tmpl_var_author'];?></li>
        <li><span class="variable">{PAGE_ID}</span> &mdash; <?php echo $lang['tmpl_var_page_id'];?></li>
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
        <?php echo $xf_vars;?>
      </ul>