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
	<li><span class="variable">{TEXT}</span> &mdash; <?php echo $lang['tmpl_var_post_text'];?></li>
	<li><span class="variable">{TEXT_FULL}</span> &mdash; <?php echo $lang['tmpl_var_post_text_full'];?></li>
	<li><span class="variable">{POST_URL}</span> &mdash; <?php echo $lang['tmpl_var_rss_link'];?></li>
	<li><span class="variable">{AUTHOR_NAME}</span> &mdash; <?php echo $lang['tmpl_var_author_name'];?></li>
	<li><span class="variable">{CATEGORY_NAME}</span> &mdash; <?php echo $lang['tmpl_var_post_category_name'];?></li>
	<li><span class="variable">{PUBDATE}</span> &mdash; <?php echo $lang['tmpl_var_rss_pubdate'];?></li>
	<?php echo $xf_vars;?>
</ul>