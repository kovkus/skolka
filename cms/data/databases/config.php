<?php

	$conf = array();

	// Administration:
	$conf['admin_url'] = 'http://localhost/david/skolka/cms';
	$conf['lang'] = 'sk';
	$conf['admin_theme'] = 'bluedee';
	$conf['admin_wysiwyg'] = true;
	$conf['admin_multiupload'] = true;
	$conf['admin_thumb_size'] = 150;
	$conf['time_adj'] = '0';
	$conf['admin_update_check'] = true;
	$conf['admin_icons'] = false;

	// Website:
	$conf['web_title'] = 'Materská škola, Miškovecká';
	$conf['web_title_header'] = false;
	$conf['web_url'] = '';
	$conf['web_format'] = 'html';
	$conf['web_encoding'] = 'utf-8';
	$conf['web_section_titles'] = true;
	$conf['web_powered_by'] = false;

	// Posts:
	$conf['web_posts_count'] = 10;
	$conf['web_pagination'] = true;
	$conf['web_counter'] = false;
	$conf['posts_image'] = false;
	$conf['posts_image_size'] = 0;

	// Comments:
	$conf['comments'] = '0';
	$conf['comments_order'] = 'normal';
	$conf['comments_approval'] = false;
	$conf['comments_antispam'] = 2;
	$conf['comments_antiflood'] = 30;
	$conf['comments_captcha'] = false;
	$conf['comments_bb'] = true;
	$conf['comments_bb_buttons'] = '110010';
	$conf['comments_smiles'] = true;
	$conf['comments_links_auto'] = true;
	$conf['comments_links_target'] = false;
	$conf['comments_links_nofollow'] = true;
	$conf['comments_field_email'] = true;
	$conf['comments_field_www'] = true;
	$conf['comments_field_preview'] = false;

	// Users:
	$conf['users_registration'] = false;
	$conf['users_default_group'] = 5;
	$conf['users_perm_login'] = true;
	$conf['users_avatar_standard'] = 100;
	$conf['users_avatar_small'] = 50;
	$conf['users_avatar_mini'] = 20;

?>