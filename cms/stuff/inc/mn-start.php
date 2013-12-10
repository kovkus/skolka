<?php
  
  session_start(); # start session


  include_once  './stuff/inc/mn-definitions.php';
  
  if ((defined('DEBUG') && DEBUG == true) || isset($_GET['debug'])) error_reporting(E_ALL);
  else error_reporting(0);



  header('pragma: no-cache');
  header('cache-control: no-cache');
  header('expires: ' . gmdate('D, d m Y H:i:s') . ' GMT');
  
  iconv_set_encoding('internal_encoding', 'UTF-8');


  define('IN_MNews', true);
  define('MN_ROOT', './');


  @include_once './data/databases/config.php';
  include_once  './stuff/inc/mn-functions.php';
  $lng = select_lang();
  include_once  './stuff/lang/lang_' . $lng .'.php';


  if (file_exists($file['banned_ips'])) include_once $file['banned_ips'];
  else $banned_ips = array();


  check_install(); # check install
  user_session();  # check user logged time



  ##### Boom De Ah Dah! Boom De Ah Dah! ############################################################
  ##### http://youtu.be/at_f98qOGY0 ################################################################
?>