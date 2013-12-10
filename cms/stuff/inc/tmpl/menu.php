<?php

  if ($_SESSION['mn_user_auth'][1] > 0) $menu .= '<li><a href="./mn-posts.php"><img src="./stuff/img/icons/posts.png" alt="" /> <span>Články</span></a></li>';
  if ($_SESSION['mn_user_auth'][3] > 0) $menu .= '<li><a href="./mn-comments.php"><img src="./stuff/img/icons/comments-gray.png" alt="" /> <span>Komentáre</span></a></li>';
  if ($_SESSION['mn_user_auth'][4] > 0) $menu .= '<li><a href="./mn-pages.php"><img src="./stuff/img/icons/pages-gray.png" alt="" /> <span>Stránky</span></a></li>';
  if ($_SESSION['mn_user_auth'][6] > 0) $menu .= '<li><a href="./mn-users.php"><img src="./stuff/img/icons/group-gray.png" alt="" /> <span>Užívatelia</span></a></li>';
  if ($_SESSION['mn_user_auth'][5] > 0) $menu .= '<li><a href="./mn-uploads.php"><img src="./stuff/img/icons/images-gray.png" alt="" /> <span>Uploads</span></a></li>';
  if (substr($_SESSION['mn_user_auth'], 8, 13) != '000000') $menu .= '<li><a href="./mn-tools.php"><img src="./stuff/img/icons/tools-gray.png" alt="" /> <span>Nástroje</span></a></li>';

  if (!empty($menu)) echo '<ul class="menu">' . $menu . '</ul>';
?>