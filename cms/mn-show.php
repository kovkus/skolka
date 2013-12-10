<?php

  # define ROOT 
  if (!defined('MN_ROOT')) define('MN_ROOT', str_replace('\\', '/', dirname(__FILE__) . '/'));




  if (is_dir(MN_ROOT . 'data/posts/') && file_exists(MN_ROOT . 'data/databases/config.php')) {

    # everything looks good, let's include some config files, functions, lang file and definitions
    include_once MN_ROOT . 'stuff/inc/mn-definitions.php';
   @include_once MN_ROOT . 'data/databases/config.php';
    include_once MN_ROOT . 'stuff/inc/mn-functions.php';
    $lng = (empty($mn_lang)) ? select_lang() : $mn_lang;
    include_once MN_ROOT . 'stuff/lang/lang_' . $lng . '.php';
    
    
    
    if (defined('DEBUG') && DEBUG == true) error_reporting(E_ALL);
    else error_reporting(0);

    
    if (file_exists(MN_ROOT . $file['banned_ips'])) include_once MN_ROOT . $file['banned_ips'];
    else $banned_ips = array();



    # create url, where we want to show posts, page, ...
    if (!empty($mn_url)) $mn_url = $mn_url;
    elseif (empty($conf['web_url'])) $mn_url = htmlentities($_SERVER['REQUEST_URI']);
    else $mn_url = $conf['web_url'];

    $mn_url = generate_url($mn_url);



    # check settings, choose best option
    $mn_count = (isset($mn_count) && is_numeric($mn_count) && $mn_count > 0) ? (int)$mn_count : (int)$conf['web_posts_count'];
    $mn_pagination = (isset($mn_pagination)) ? $mn_pagination : $conf['web_pagination'];
    $mn_author = (isset($mn_author) && !empty($mn_author)) ? $mn_author : @$_GET['mn_a'];
    $mn_cat = (isset($mn_cat) && !empty($mn_cat)) ? $mn_cat : @$_GET['mn_cat'];
    $mn_cat = str_replace(' ', '', $mn_cat);
    $mn_cats = explode(',', $mn_cat);


	if (isset($_GET['mn_page']) && !empty($_GET['mn_page']) && @$mn_lock != 'mn_page') $mn_page_int = $_GET['mn_page'];
	elseif (isset($mn_page) && !empty($mn_page)) $mn_page_int = $mn_page;
	else $mn_page_int = null;
        
            
    $mn_users = load_basic_data('users');
    $mn_categories = load_basic_data('categories');
    $mn_tags = load_basic_data('tags');



    # --- we can specify RSS action directly by setting mn-show.php?mode=rss
    if (isset($mn_mode) && $mn_mode == 'news') {$mn_lock = 'news';}
    elseif (isset($_GET['mode']) && $_GET['mode'] == 'rss') {$mn_mode = 'rss';}
    elseif (isset($_GET['mn_gallery']) && is_numeric($_GET['mn_gallery']) && !isset($mn_mode) && empty($mn_cat) && empty($mn_page_int)) {
      $mn_mode = 'gallery';
      $mn_gallery = (int)$_GET['mn_gallery'];
    }
    # check page number
    if (!isset($_GET['mn_p']) || !is_numeric($_GET['mn_p']) || $_GET['mn_p'] <= 0) $_GET['mn_p'] = 1;
    




    # --- Categories menu
    if (isset($mn_mode) && $mn_mode == 'menu') {
      if (file_exists(MN_ROOT . $file['categories'])) {

        $cats = load_basic_data('categories');
        
        if (isset($mn_menu_tmpl)) {
        	$menu_tmpl = $mn_menu_tmpl;
        	$mn_menu = '';
        	$posts_count = get_posts_count();
        }
        else {
        	$menu_tmpl = "\t\t\t<li>{ITEM}</li>\n";
        	$mn_menu = "\n\n\t\t<ul class=\"mn-menu\" id=\"mn-cat-menu\">\n";
        	$posts_count = array();
        }


        // unshift item at the beginning
        if (isset($mn_menu_unshift)) {
        	if (is_array($mn_menu_unshift)) foreach ($mn_menu_unshift as $n => $unshift_item) {$mn_menu .= str_ireplace('{ITEM}', $unshift_item, $menu_tmpl);}
        	else $mn_menu .= str_ireplace('{ITEM}', $mn_menu_unshift, $menu_tmpl);
        }



		if (file_exists(MN_ROOT . $file['categories_order'])) {
			$categories_order = unserialize(file_get_contents(MN_ROOT . $file['categories_order']));
		}
		else {
			$categories_order = array();
			$i = 1;
			foreach ($cats as $id => $cname) {$categories_order[$i] = $id; $i++;}
		}

		// show menu items
        foreach ($categories_order as $cat_order => $cat_id) {
        
          $active_cat = (isset($_GET['mn_cat']) && $_GET['mn_cat'] == $cat_id) ? ' class="active"' : '';
          $cat_posts_count = (!isset($mn_menu_tmpl) || empty($posts_count[$cat_id])) ? '<span class="trivial">0</span>' : $posts_count[$cat_id];

          $mn_menu .= str_ireplace(
          	array('{ITEM}', '{ITEM_ID}', '{ITEM_NAME}', '{POSTS_COUNT}'),
          	array("<a href=\"" . $mn_url . "mn_cat=" . $cat_id . "\"" . $active_cat . ">" . $cats[$cat_id] . "</a>", $cat_id, $cats[$cat_id], $cat_posts_count),
          	$menu_tmpl
          );

        }


		// push item to the end
        if (isset($mn_menu_push)) {
        	if (is_array($mn_menu_push)) foreach ($mn_menu_push as $n => $push_item) {$mn_menu .= str_ireplace('{ITEM}', $push_item, $menu_tmpl);}
        	else $mn_menu .= str_ireplace('{ITEM}', $mn_menu_push, $menu_tmpl);
        }
        
        $mn_menu .= (isset($mn_menu_tmpl)) ? '' : "\t\t</ul>\n\n";
        echo encoding($mn_menu);
        
      }
      else echo encoding($lang['web_msg_no_cats_file']);
    }





    # --- Pages menu
    elseif (isset($mn_mode) && $mn_mode == 'pagemenu') {
    
      if (is_dir(MN_ROOT . $dir['pages'])) {
        $p_dir = dir(MN_ROOT . $dir['pages']);
        $pages = $pages_order = $pages_slugs = array();

        while ($page_file = $p_dir->read()) {
          if (!is_file(MN_ROOT . $dir['pages'] . $page_file)) continue;
          else {
            $page_var = get_page_data($page_file, false);

            if ($page_var['visible'] == '1') {
              $pages[$page_var['id']] = $page_var['title'];
              $pages_slugs[$page_var['id']] = $page_var['friendly_url'];
              $pages_order[$page_var['id']] = $page_var['order'];
            }
            else continue;
          }
        }
      }
      
      
      if (!empty($pages)) {
        asort($pages_order);

        $menu_tmpl = (isset($mn_menu_tmpl)) ? $mn_menu_tmpl : "\t\t\t<li>{ITEM}</li>\n";
        $mn_menu = (isset($mn_menu_tmpl)) ? '' : "\n\n\t\t<ul class=\"mn-menu\" id=\"mn-page-menu\">\n";


		// unshift item at the beginning
		if (isset($mn_menu_unshift)) {
			if (is_array($mn_menu_unshift)) foreach ($mn_menu_unshift as $n => $unshift_item) {$mn_menu .= str_ireplace('{ITEM}', $unshift_item, $menu_tmpl);}
			else $mn_menu .= str_ireplace('{ITEM}', $mn_menu_unshift, $menu_tmpl);
		}

		// show menu items
        foreach ($pages_order as $page_id => $page_order) {
          $active_page = (isset($_GET['mn_page']) && $_GET['mn_page'] == $page_id) ? ' class="active"' : '';
          
          $mn_menu .= str_ireplace(
          	array('{ITEM}', '{ITEM_ID}', '{ITEM_NAME}', '{FRIENDLY_URL}'),
          	array('<a href="' . $mn_url . 'mn_page=' . $page_id . '"' . $active_page . '>' . $pages[$page_id] . '</a>', $page_id, $pages[$page_id], $pages_slugs[$page_id]),
          	$menu_tmpl
          );
          
        }

		// push item to the end
		if (isset($mn_menu_push)) {
			if (is_array($mn_menu_push)) foreach ($mn_menu_push as $n => $push_item) {$mn_menu .= str_ireplace('{ITEM}', $push_item, $menu_tmpl);}
			else $mn_menu .= str_ireplace('{ITEM}', $mn_menu_push, $menu_tmpl);
		}


        $mn_menu .= (isset($mn_menu_tmpl)) ? '' : "\t\t</ul>\n\n";
        echo encoding($mn_menu);
      }

      else echo encoding($lang['web_msg_no_pages']);
    }





    # --- Search form
    elseif (isset($mn_mode) && $mn_mode == 'search') {
      echo '<form action="' . $mn_url . '" method="get" class="mn-search-form"><input type="text" name="mn_q" value="" /> <input type="submit" class="submit" value="' . encoding($lang['web_search']) . '" /></form>';
    }





    # --- Advanced search form
    elseif (isset($mn_mode) && ($mn_mode == 'advanced_search' || $mn_mode == 'advanced-search')) {
      $timestamps = get_unique_timestamps();
      
      echo '<form action="' . $mn_url . '" method="get" class="mn-search-form"><fieldset>';

      echo '<select name="mn_cat" id="mn_cat"><option value="" class="description">--- ' . encoding($lang['posts_all_categories']) . ' ---</option>';
      foreach ($mn_categories as $cat_id => $cat_name) {echo '<option value="' . $cat_id . '">' . encoding($cat_name) . '</option>';}
      echo '</select> ';

      $posts_counts = get_posts_count('users');
      echo '<select name="mn_a" id="mn_a"><option value="" class="description">--- ' . encoding($lang['posts_all_users']) . ' ---</option>';
      foreach ($mn_users as $user_id => $user_name) {
        if (empty($posts_counts[$user_id])) continue;
        else echo '<option value="' . $user_id . '">' . $user_name . '</option>';
      }
      echo '</select> ';

      echo '<select name="mn_archive" id="mn_archive"><option value="" class="description">--- ' . encoding($lang['posts_all_dates']) . ' ---</option>';
      foreach ($timestamps as $key => $value) {echo '<option value="' . $value . '">' . encoding($lang['month'][date('n', $key)]) . ' ' . date('Y', $key) . '</option>';}
      echo '</select> ';

      echo '<input type="text" name="mn_q" value="" />
      <input type="submit" class="submit" value="' . encoding($lang['web_search']) . '" />
      </fieldset></form>';
    }





    # --- Gallery
    elseif (isset($mn_mode) && $mn_mode == 'gallery' && (!isset($mn_lock) || $mn_lock == 'gallery')) {
    
      if (file_exists(MN_ROOT . $file['galleries']) && file_exists(MN_ROOT . $file['files'])) {
      
        echo encoding(mn_gallery($mn_gallery));
      
      }
      else echo encoding($lang['web_msg_no_gals_files_file']);
    
    }





    # --- Archives
    elseif (isset($mn_mode) && $mn_mode == 'archive') {
    
      $timestamps = get_unique_timestamps();
      
      echo '<ul class="mn-archive">';
      foreach ($timestamps as $key => $value) {
        echo '<li><a href="' . $mn_url . 'mn_archive=' . $value . '">' . encoding($lang['month'][date('n', $key)]) . ' ' . date('Y', $key) . '</a></li>';
      }
      echo '</ul>';
      
    }





	# --- Tag cloud
	elseif (isset($mn_mode) && ($mn_mode == 'tagcloud' || $mn_mode == 'tags')) {
		
		if (is_array($mn_tags) && !empty($mn_tags)) {
		
			$tag_divider = (isset($mn_tag_divider)) ? $mn_tag_divider : ', ';
			$tag_cloud = '';

			$posts_count = file_exists(MN_ROOT . $file['tags']) ? get_posts_count('tags') : array();
			$tag_max_count = max($posts_count);

			foreach ($mn_tags as $tag_id => $tag_name) {
			
				$percent = floor(($posts_count[$tag_id] / $tag_max_count) * 100);
				
				// decide which class should be assign, according to tag popularity
				if ($percent < 20) $tag_class = 'smallest';
				elseif ($percent >= 20 && $percent < 40) $tag_class = 'small';
				elseif ($percent >= 40 && $percent < 60) $tag_class = 'medium';
				elseif ($percent >= 60 && $percent < 80) $tag_class = 'large';
				else $tag_class = 'largest';
			
				$tag_cloud .= '<a href="' . $mn_url . 'mn_tag=' . $tag_id . '" class="mn-tag mn-tag-' . $tag_class . '" title="' . $lang['posts_posts'] . ': ' . $posts_count[$tag_id] . '">' . $tag_name . '</a>' . $tag_divider;
			
			}
			
			$cut_length = mb_strlen($tag_divider);
			$tag_cloud = mb_substr($tag_cloud, 0, -($cut_length));
			
			echo encoding($tag_cloud);
			
		}
		
		else echo encoding($lang['web_msg_no_tags']);
	
	}





	# --- Include options (page title, ...)
    elseif (isset($mn_mode) && $mn_mode == 'include') {
    
      function get_title() {
        global $dir, $lang, $mn_categories, $mn_users;
        if (isset($_GET['mn_post'])) {
          if (is_numeric($_GET['mn_post']) && file_exists(MN_ROOT . $dir['posts'] . 'post_' . $_GET['mn_post'] . '.php')) {
            $mn_post_id = $_GET['mn_post'];
          }
          else {
            $post_slugs = get_post_slugs();

            if (in_array(check_text($_GET['mn_post'], true), $post_slugs)) {
              $mn_post_id = array_search(check_text($_GET['mn_post'], true), $post_slugs);
            }
            else {
              $mn_post_id = 0;
            }
          }

          $p = get_post_data($mn_post_id);
          return encoding($p['title']);
        }
        elseif (isset($_GET['mn_page'])) {
          if (is_numeric($_GET['mn_page']) && file_exists(MN_ROOT . $dir['pages'] . 'post_' . $_GET['mn_page'] . '.php')) {
            $mn_page_id = $_GET['mn_page'];
          }
          else {
            $pages_dir = dir(MN_ROOT . $dir['pages']); $mn_page_id = '';
            while ($p_file = $pages_dir->read()) {
              if (!is_file(MN_ROOT . $dir['pages'] . $p_file)) continue;
              else {
                $temp_var = get_page_data($p_file, false);
                if ($temp_var['friendly_url'] == $_GET['mn_page']) $mn_page_id = $temp_var['id'];
                else continue;
              }
            }
          }
          
          $p = get_page_data($mn_page_id);
          return encoding($p['title']);
        }
        elseif (isset($_GET['mn_cat']) && !empty($_GET['mn_cat'])) {
          return encoding($lang['cats_category'] . ': ' . $mn_categories[$_GET['mn_cat']]);
        }
        elseif (isset($_GET['mn_user']) && !empty($_GET['mn_user'])) {
          return encoding($lang['users_user'] . ': ' . $mn_users[$_GET['mn_user']]);
        }
        else return encoding($lang['posts_posts']);
        
      }
      
    }





    # --- check search query for dangerous characters
    elseif (!empty($_GET['mn_q']) && (preg_match('/[\'$&\/()=%*\"#~|+{}<>]/i', $_GET['mn_q'])) && !isset($mn_lock)) {
      echo encoding($lang['web_msg_search_forbidden']);
      echo '<!-- Powered by MNews: www.mnewscms.com -->';
    }





    # --- Show post
    elseif (isset($_GET['mn_post']) && !empty($_GET['mn_post']) && !isset($mn_page) && (!isset($mn_lock) || $mn_lock == 'post')) {
      
      if (is_numeric($_GET['mn_post']) && file_exists(MN_ROOT . $dir['posts'] . 'post_' . $_GET['mn_post'] . '.php')) {
        $mn_post_id = $_GET['mn_post'];
      }
      else {
        $post_slugs = get_post_slugs();

        if (in_array(check_text($_GET['mn_post'], true), $post_slugs)) {
          $mn_post_id = array_search(check_text($_GET['mn_post'], true), $post_slugs);
        }
        else {
          $mn_post_id = 0;
        }
      }



      if (file_exists(MN_ROOT . $dir['posts'] . 'post_' . $mn_post_id . '.php')) {

        $p = get_post_data($mn_post_id);

        if ($p['timestamp'] <= mn_time() && $p['status'] <= '2') {

          $detail_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_10.html')) ? $mn_tmpl : DEFAULT_TMPL;
          $post_result = posts_tmpl($p['id'], $detail_tmpl . '_10', $mn_url);
          echo encoding($post_result);


          if ($conf['web_counter'] && empty($_COOKIE['mn_user_name'])) mn_put_contents(MN_ROOT . $dir['posts'] . 'post_' . $p['id'] . '.php', SAFETY_LINE . DELIMITER . "\n" . $p['id'] . DELIMITER . $p['timestamp'] . DELIMITER . $p['title'] . DELIMITER . $p['friendly_url'] . DELIMITER . $p['author'] . DELIMITER . $p['cat'] . DELIMITER . $p['status'] . DELIMITER . $p['comments'] . DELIMITER . ($p['views'] + 1) . DELIMITER . $p['tags'] . DELIMITER . $p['image'] . DELIMITER . '' . DELIMITER . '' . DELIMITER . $p['xfields'] . DELIMITER . "\n" . $p['short_story'] . DELIMITER . "\n" . $p['full_story']);



          if ($conf['comments'] === true || $conf['comments'] >= 1) {

            $mn_comm_users = load_complex_data('users');

            if ($p['comments'] != 2 && file_exists(MN_ROOT . $dir['comments'] . 'comments_' . $p['id'] . '.php')) {

              $comment_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_12.html')) ? $mn_tmpl : DEFAULT_TMPL;

              $c_result = '';
              $c_file = file(MN_ROOT . $dir['comments'] . 'comments_' . $p['id'] . '.php');
              array_shift($c_file);

              if ($conf['comments_order'] == 'reverse') {
                $c_i = count($c_file);
                $c_file = array_reverse($c_file);
              }
              else $c_i = 1;

              if (!empty($c_file)) {
                foreach ($c_file as $c_line) {
                  $comment = get_values('comments', $c_line, false);

                  if ($comment['status'] == '1' || $comment['status'] == '3') {
                    $c_result .= comment_tmpl($comment_tmpl . '_12', $mn_url, $c_i);
                    ($conf['comments_order'] == 'reverse') ? $c_i-- : $c_i++;
                  }
                  else continue;
                }
              }

              if (!empty($c_result)) echo '<div id="mn-comments">' . encoding($c_result) . '</div>';
              else echo '<p id="mn-comments" class="mn-comment-info">' . encoding($lang['web_msg_no_comments']) . '</p>';
            }
            elseif ($p['comments'] == 1) echo '<p id="mn-comments" class="mn-comment-info">' . encoding($lang['web_msg_no_comments']) . '</p>';


            if ($p['comments'] == 1 && check_ip_ban($_SERVER['REMOTE_ADDR'], $banned_ips)) {
              echo '<p class="mn-comment-info">' . encoding($lang['web_msg_banned_ip']) . '</p>';
            }
            elseif ($p['comments'] == 1 && $conf['comments'] == '1') {
              $post_id = $p['id'];
              include MN_ROOT . 'stuff/inc/tmpl/comment-form.php';
            }
            elseif ($p['comments'] == 1 && $conf['comments'] == '2') {

              if ((isset($_COOKIE['mn_logged']) && isset($_COOKIE['mn_user_name'])) || (isset($_COOKIE['mn_user_name']) && isset($_COOKIE['mn_user_hash']))) {
                $post_id = $p['id'];
                include MN_ROOT . 'stuff/inc/tmpl/comment-form.php';
              }
              else {
                include MN_ROOT . 'stuff/inc/tmpl/login-form.php';
              }
            }
            else {
              $mn_comments_id = (empty($c_result)) ? ' id="mn-comments"' : '';
              echo '<p' . $mn_comments_id . ' class="mn-comment-info">' . encoding($lang['web_msg_comments_forbidden']) . '</p>';
            }

          }

        }
        else echo '<p class="mn-info">' .  $lang['web_msg_post_unavailable'] . '</p>';

      }
      else echo '<p class="mn-info">' .  $lang['web_msg_post_unavailable'] . '</p>';

      echo '<!-- Powered by MNews: www.mnewscms.com -->';

    }





    # --- Show page
    elseif (isset($mn_page_int) && !empty($mn_page_int) && (!isset($mn_lock) || $mn_lock == 'page' || $mn_lock == 'mn_page')) {

      if (!is_numeric($mn_page_int)) {
        $pages_dir = dir(MN_ROOT . $dir['pages']); $read_page_id = '';
        while ($p_file = $pages_dir->read()) {
          if (!is_file(MN_ROOT . $dir['pages'] . $p_file)) continue;
          else {
            $temp_var = get_page_data($p_file, false);
            if ($temp_var['friendly_url'] == $mn_page_int) $read_page_id = $temp_var['id'];
            else continue;
          }
        }
        $mn_page_int = $read_page_id;
      }



      if (file_exists(MN_ROOT . $dir['pages'] . 'page_' . $mn_page_int . '.php')) {
        $page = get_page_data($mn_page_int);

        if (isset($page['pass']) && !empty($page['pass'])) {

          if ((isset($_GET['mn_key']) && $_GET['mn_key'] == substr(md5(substr($page['pass'], 1, 22) . $page['id'] . date('d.m.Y')), 6, 9)) || (isset($_POST['mn_pagepass']) && $page['pass'] == sha1($_POST['mn_pagepass']))) {
            $page_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_11.html')) ? $mn_tmpl : DEFAULT_TMPL;
            $page_result = page_tmpl($mn_page_int, $page_tmpl . '_11', $mn_url);

            echo encoding($page_result);
          }

          else {
            $page_result = '<p>' . $lang['web_page_password'] . ':</p>';
            $page_result .= '<form action="' . $mn_url . 'mn_page=' . $page['id'] . '" method="post"><input type="password" name="mn_pagepass" /> <input type="submit" value="vstúpiť" /></form>';
            echo encoding($page_result);
          }

        }


        else {
          $page_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_11.html')) ? $mn_tmpl : DEFAULT_TMPL;
          $page_result = page_tmpl($mn_page_int, $page_tmpl . '_11', $mn_url);

          echo encoding($page_result);
        }

      }

      echo '<!-- Powered by MNews: www.mnewscms.com -->';

    }





    # --- User profile
    elseif (isset($_GET['mn_user']) && !empty($_GET['mn_user']) && !empty($mn_users[$_GET['mn_user']]) && (!isset($mn_lock) || $mn_lock == 'user')) {

      $user_tmpl = (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_13.html')) ? $mn_tmpl : DEFAULT_TMPL;
      $user_result = user_tmpl($_GET['mn_user'], $user_tmpl . '_13', $mn_url);

      echo encoding($user_result);
      echo '<!-- Powered by MNews: www.mnewscms.com -->';

    }





    # --- Else... show posts :)
    elseif (file_exists(MN_ROOT . $file['posts'])) {
    
      # read posts file
      $p_file = file(MN_ROOT . $file['posts']);
      $posts = array();
      array_shift($p_file);
      $p_file = mn_natcasesort($p_file);
      $p_file = array_reverse($p_file, true);

      
      # put posts to arrays - one array for IDs, one for timestamps
      foreach ($p_file as $p_line) {
        $post = get_values('posts', $p_line, false);


        # we want only actuall and approved posts
        if ($post['timestamp'] > mn_time()) continue;
        elseif (!isset($mn_author) && $post['status'] != '1') continue;
        elseif (isset($mn_author) && $post['status'] > '2') continue;
        else {
          # check other settings
          if (!empty($mn_cat) && (!in_array($post['cat'], $mn_cats))) continue;
          if (isset($_GET['mn_archive']) && !empty($_GET['mn_archive']) && ($_GET['mn_archive'] != date('Y-m', $post['timestamp']))) continue;
          if (isset($mn_author) && !empty($mn_author) && ($mn_author != $post['author'])) continue;
          if (isset($_GET['mn_tag']) && !empty($_GET['mn_tag'])) {
            $post['tags_array'] = (!empty($post['tags'])) ? explode(',', trim($post['tags'])) : array();
            if (!in_array($_GET['mn_tag'], $post['tags_array'])) continue;
          }
          if (isset($_GET['mn_q']) && !empty($_GET['mn_q']) && (strlen($_GET['mn_q']) > 2)) {
            $post_content = file_get_contents(MN_ROOT . $dir['posts'] . 'post_' . $post['post_id'] . '.php');
            if (stripos($post_content, $_GET['mn_q']) === false) continue;
          }
          
          $posts[] = $post['post_id'];
        }
      }



      # OMG we actually do have some posts! Let's show them
      if (!empty($posts)) {

        // skip first X posts with $mn_skip
        if (isset($mn_skip) && is_numeric($mn_skip) && $mn_skip > 0) {
          for ($i = 1; $i <= $mn_skip; $i++) {
            array_shift($posts);
          }
        }


        $posts_count = count($posts);

        # if we want to see pagination, we need some hard work
        if ($mn_pagination) {

          # set pagination link path
          $mn_pagination_url = $mn_url;
          if (isset($_GET['mn_a'])) $mn_pagination_url = $mn_pagination_url . 'mn_a=' . $_GET['mn_a'] . '&amp;';
          if (isset($_GET['mn_archive'])) $mn_pagination_url = $mn_pagination_url . 'mn_archive=' . $_GET['mn_archive'] . '&amp;';
          if (isset($_GET['mn_cat'])) $mn_pagination_url = $mn_pagination_url . 'mn_cat=' . $_GET['mn_cat'] . '&amp;';
          if (isset($_GET['mn_tag'])) $mn_pagination_url = $mn_pagination_url . 'mn_tag=' . $_GET['mn_tag'] . '&amp;';
          if (isset($_GET['mn_q'])) $mn_pagination_url = $mn_pagination_url . 'mn_q=' . $_GET['mn_q'] . '&amp;';


          require_once MN_ROOT . 'stuff/inc/paginator.class.php';

          $pages = new Paginator;
          $pages->items_total = $posts_count;
          $pages->items_per_page = $mn_count;
          $pages->current_page = $_GET['mn_p'];
          $pages->show_path = $mn_pagination_url;
          $pages->paginate();
          $pages_tmpl_content = $pages->display_pages();

          $j = ( ($_GET['mn_p'] - 1) * $mn_count );
          $max = ( ($posts_count - $j) > $mn_count ) ? ( $j + $mn_count ) : $posts_count;

          if ($j == 0 && $max == $posts_count) $mn_pagination = false;

        }
        else {
          $j = ( ($_GET['mn_p'] - 1) * $mn_count );
          $max = ( ($posts_count - $j) > $mn_count ) ? ( $j + $mn_count ) : $posts_count;
        }
        


        # choose template
        # scary piece of code, I know...
        if (!empty($mn_mode) && $mn_mode == 'rss') $tmpl_type = 14;
        elseif (isset($mn_cat) && !empty($mn_cat)) $tmpl_type = 2;
        elseif (isset($_GET['mn_archive']) && !empty($_GET['mn_archive'])) $tmpl_type = 3;
        elseif (isset($_GET['mn_q']) && !empty($_GET['mn_q'])) $tmpl_type = 4;
        elseif (isset($mn_author) && !empty($mn_author)) $tmpl_type = 5;
        else $tmpl_type = 1;
        
        if (isset($mn_tmpl) && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_' . $tmpl_type . '.html')) $post_tmpl = $mn_tmpl . '_' . $tmpl_type;
        elseif (isset($mn_tmpl) && !file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_' . $tmpl_type . '.html') && file_exists(MN_ROOT . $dir['templates'] . $mn_tmpl . '_1.html')) $post_tmpl = $mn_tmpl . '_1';
        elseif (file_exists(MN_ROOT . $dir['templates'] . DEFAULT_TMPL . '_' . $tmpl_type . '.html')) $post_tmpl = DEFAULT_TMPL . '_' . $tmpl_type;
        else $post_tmpl = DEFAULT_TMPL . '_1';

        
        $show_path = $mn_url;
        $posts_result = '';
        for ($i = $j; $i <= $max-1; $i++) {
          $posts_result .= posts_tmpl($posts[$i], $post_tmpl, $mn_url);
        }
        


        if (isset($mn_mode) && $mn_mode == 'rss') {
          $domain = explode('/', $conf['admin_url']);
          header('Content-Type: application/rss+xml; charset=UTF-8');
          echo "<?xml version=\"1.0\" encoding=\"utf-8\"?" . ">\n<rss version=\"2.0\">\n<channel>\n\t<title>" . $conf['web_title'] . "</title>\n\t<link>http://" . $domain[2] . "/</link>\n\t<description>" . $lang['web_rss_description'] . " " . $domain[2] . ".</description>\n\t<language>" . $conf['lang'] . "</language>\n\n" . $posts_result . "\n</channel>\n</rss>";
        }
        
        else {
          if (isset($_GET['mn_q']) && (strlen($_GET['mn_q']) > 2) && @$conf['web_section_titles'] !== false) echo encoding('<p class="mn-info">' . str_replace('%s%', '"<strong>' . htmlspecialchars($_GET['mn_q'], ENT_QUOTES) . '</strong>"', $lang['web_msg_search_found']) . '</p>');
          elseif (isset($_GET['mn_cat']) && !empty($mn_categories[$_GET['mn_cat']]) && @$conf['web_section_titles'] !== false) echo encoding('<p class="mn-info">' . str_replace('%c%', '"<strong>' . $mn_categories[$_GET['mn_cat']] . '</strong>"', $lang['web_msg_cat_posts']) . '</p>');
          elseif (isset($_GET['mn_tag']) && !empty($mn_tags[$_GET['mn_tag']]) && @$conf['web_section_titles'] !== false) echo encoding('<p class="mn-info">' . str_replace('%t%', '"<strong>' . $mn_tags[$_GET['mn_tag']] . '</strong>"', $lang['web_msg_tag_posts']) . '</p>');

          echo encoding($posts_result);

          if ($mn_pagination) echo encoding($pages_tmpl_content);
          if ($conf['web_powered_by']) echo '<p style="text-align: center; margin: 5px 0; font-size: 0.9em;">Powered by <a href="http://mnewscms.com/">MNews</a>!</p>';

          echo '<!-- Powered by MNews: www.mnewscms.com -->';
        }
        
        
        
      }




      else {
        echo '<p class="mn-info">';
        if (isset($_GET['mn_q'])) echo '<p class="mn-info">' . str_replace('%s%', '"<strong>' . htmlspecialchars($_GET['mn_q']) . '</strong>"', encoding($lang['web_msg_search_no_posts'])) . '</p>';
        elseif (isset($mn_cat) && !empty($mn_cat)) echo encoding($lang['web_msg_cat_no_posts']);
        else echo encoding($lang['web_msg_no_posts']);
        echo '</p>';
      }

    }
    
    
    
    
    
    else {
      echo '<p class="mn-info">' . encoding($lang['web_msg_no_posts']) . '</p>';
    }







  }

  
  
  else {
    if (!isset($mn_mode)) echo '<p class="mn-info">We\'re sorry, but our system is temporarily unavailable(probably due to scheduled maintenance or upgrade)!<br /><em>If the problem persists, please contact the website administrator.</em></p>';
    else echo $mn_mode . ' temporarily unavailable.';
  }



  unset($active_cat, $active_page, $c_file, $c_result, $cat_id, $cat_name, $comment_tmpl, $detail_tmpl, $galleries, $key, $mn_author, $mn_cat, $mn_count, $mn_gallery, $mn_lock, $mn_menu, $mn_menu_tmpl, $mn_menu_push, $mn_menu_unshift, $mn_mode, $mn_page, $mn_page_int, $mn_pagination, $mn_skip, $mn_tmpl, $mn_unset, $mn_url, $p, $p_line, $p_file, $page_id, $page_order, $page_result, $page_tmpl, $page_var, $page_file, $pages, $pages_order, $post, $post_tmpl, $posts, $posts_result, $timestamps, $user_id, $user_name, $value);



  ##### This was a triumph. ########################################################################
  ##### http://youtu.be/NCt2nZF2nLk ################################################################

?>