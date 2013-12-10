<?php

  header("Content-type: text/css");

  define('MN_ROOT', '../../');

  include_once  '../inc/mn-definitions.php';
  @include_once MN_ROOT . 'data/databases/config.php';
  if (isset($_GET['install'])) $conf['admin_theme'] = 'bluedee';

  $style_dir = (is_dir('../themes/' . $conf['admin_theme'] . '/')) ? '../themes/' . $conf['admin_theme'] . '/' : '../themes/bluedee/';
  $style = file_get_contents($style_dir . 'style.css');



  if (file_exists($style_dir . 'theme_config.php')) {
  
    include $style_dir . 'theme_config.php';
    @include_once MN_ROOT . 'data/databases/theme_config.php';
    
    if (isset($theme['name']) && $theme['name'] != $conf['admin_theme']) { 
      unset($theme);
      include $style_dir . 'theme_config.php';
    }
    

    if (isset($theme['background_img']) && !empty($theme['background_img'])) {
      $img_va = (isset($theme['background_img-va'])) ? $theme['background_img-va'] : 'center';
      $img_ha = (isset($theme['background_img-ha'])) ? $theme['background_img-ha'] : 'center';
      $img_rep = (isset($theme['background_img-rep'])) ? $theme['background_img-rep'] : 'no-repeat';
      $img_att = (isset($theme['background_img-att'])) ? $theme['background_img-att'] : 'fixed';
      
      $background_img = 'url("' . $theme['background_img'] . '") ' . $img_va . ' ' . $img_ha . ' ' . $img_rep . ' ' . $img_att;
    }
    else $background_img = '';

    
    $theme_search = array(
      '%%background_img%%',
      './img/',
    );
    $theme_replace = array(
      $background_img,
      $style_dir . 'img/',
    );
    
    $style = str_ireplace($theme_search, $theme_replace, $style);
    $style = preg_replace('#%%(.*?)%%#ie', '\$theme[\'$1\']', $style);

  }


  include './main.css';
  echo "\n\n\n\n\n/* ----- THEME STYLE ------ */\n\n";
  echo $style;

?>
