<?php

	$xf_vars = '';

	if (file_exists(MN_ROOT . $file['xfields'])) {
	
		$xfields = get_unserialized_array('xfields');
		foreach ($xfields as $xVar => $x) {
			if ($x['section'] != 'posts') continue;
			else $xf_vars .= '<li><span class="variable">{x' . strtoupper($x['var']) . '}</span> &mdash; ' . $x['name'] . '</li>';
		}
	
	}

?>
<ul class="tmpl-variables">
        <li><span class="variable">{TITLE}</span> &mdash; <?php echo $lang['tmpl_var_post_title'];?></li>
        <li><span class="variable">{TEXT}</span> &mdash; <?php echo $lang['tmpl_var_post_text_full'];?> [<span class="simurl toggle450" rel="tmpl-text"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-text" class="hide">
            <li><span class="variable">{TEXT_PEREX}</span> &mdash; <?php echo $lang['tmpl_var_post_short_story'];?></li>
            <li><span class="variable">{TEXT_LONG}</span> &mdash; <?php echo $lang['tmpl_var_post_long_story'];?></li>
          </ul>
        </li>
        <li><span class="variable">[PERM-LINK]</span> &amp; <span class="variable">[/PERM-LINK]</span> &mdash; <?php echo $lang['tmpl_var_post_link_perm'];?></li>
        <li><span class="variable">{AUTHOR}</span> &mdash; <?php echo $lang['tmpl_var_author'];?> [<span class="simurl toggle450" rel="tmpl-author"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-author" class="hide">
            <li><span class="variable">{AUTHOR_NAME}</span> &mdash; <?php echo $lang['tmpl_var_author_name'];?></li>
            <li><span class="variable">{AUTHOR_ID}</span> &mdash; <?php echo $lang['tmpl_var_author_id'];?></li>
          </ul>
        </li>
        <li><span class="variable">{CATEGORY}</span> &mdash; <?php echo $lang['tmpl_var_post_category'];?> [<span class="simurl toggle450" rel="tmpl-cat"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-cat" class="hide">
            <li><span class="variable">{CATEGORY_NAME}</span> &mdash; <?php echo $lang['tmpl_var_post_category_name'];?></li>
            <li><span class="variable">{CATEGORY_ID}</span> &mdash; <?php echo $lang['tmpl_var_post_category_id'];?></li>
          </ul>
        </li>
        <li><span class="variable">{TAGS}</span> &mdash; <?php echo $lang['tmpl_var_post_tags'];?> [<span class="simurl toggle450" rel="tmpl-tags"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-tags" class="hide">
            <li><span class="variable">{TAG_NAMES}</span> &mdash; <?php echo $lang['tmpl_var_post_tag_names'];?></li>
          </ul>
        </li>
        <li><span class="variable">{IMAGE}</span> &mdash; <?php echo $lang['tmpl_var_post_image'];?> [<span class="simurl toggle450" rel="tmpl-image"><?php echo $lang['tmpl_vars_more_variables'];?></span>]
          <ul id="tmpl-image" class="hide">
            <li><span class="variable">{IMAGE_FILENAME}</span> &mdash; <?php echo $lang['tmpl_var_post_image_filename'];?></li>
            <li><span class="variable">{IMAGE_URL}</span> &mdash; <?php echo $lang['tmpl_var_post_image_url'];?></li>
            <li><span class="variable">{IMAGE_WIDTH}</span> &mdash; <?php echo $lang['tmpl_var_post_image_width'];?></li>
            <li><span class="variable">{IMAGE_HEIGHT}</span> &mdash; <?php echo $lang['tmpl_var_post_image_height'];?></li>
          </ul>
        </li>
        <li><span class="variable">{COMMENTS}</span> &mdash; <?php echo $lang['tmpl_var_post_comments'];?></li>
        <?php if ($conf['web_counter']) { ?><li><span class="variable">{VIEWS}</span> &mdash; <?php echo $lang['tmpl_var_post_views'];?></li><?php } ?>
        <li><span class="variable">{POST_ID}</span> &mdash; <?php echo $lang['tmpl_var_post_id'];?></li>
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