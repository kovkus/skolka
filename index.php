<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<!-- Meta info begin-->
<title>MŠ Miškovecká</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="robots" content="index, follow" />
<meta name="author" content="David Kovac - "
<!-- Meta info end-->
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/default.css" />
<link rel="stylesheet" href="css/nivo-slider.css" type="text/css" media="screen" />
<link rel="shortcut icon"  href="img/favicon.ico" />
<script type="text/javascript" src="js/jq.js"></script>
<script type="text/javascript" src="js/cufon-yui.js"></script>
<script type="text/javascript" src="js/Museo.font.js"></script>
<script type="text/javascript" src="js/Museo_Sans.font.js"></script>
<script type="text/javascript" src="js/supersized.3.1.3.core.min.js"></script>
<script type="text/javascript" src="js/superfish-compile.js"></script>
<script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
<script type="text/javascript" src="js/jquery.tinycarousel.min.js"></script>
<script type="text/javascript" src="js/jquery.colorbox.js"></script>
<script type="text/javascript" src="js/jquery.tweet.js"></script>
<script type="text/javascript" src="js/p2.js"></script>
</head>
<body>
<div id="content-wrapper"> 
	
	<!-- Container begin -->
	<div id="container"> 
		<!-- Header begin-->
		<div id="header" class="clearfix">
			<div id="logo" > <a href="index.php"><img src="img/logo.png" alt="logo" /></a> </div>
			
			<!-- Navigation start -->
			<ul class="sf-menu">
				<li> <a href="#">Domov</a></li>
				<li><a href="#">Triedy</a>
					<ul>
						<li><a href="?mn_page=1">Trieda I.</a></li>
						<li><a href="?mn_page=2">Trieda II.</a></li>
						<li><a href="?mn_page=3">Trieda III.</a></li>
						<li><a href="?mn_page=4">Trieda IV.</a></li>
						<li><a href="?mn_page=5">Trieda V.</a></li>
					</ul>
				</li>
				<li><a href="?mn_page=6">Aktivity</a></li>
				<li><a href="?page=fotoalbum">Fotoalbum</a></li>
				<li> <a href="?mn_page=7">Kontakt</a></li>
			</ul>
			<!-- Navigation end --> 
		</div>
		<!-- Header end --> 
		<!-- slide start -->
		<div id="slide-container"  class="clearfix">
			<div id="slider-wrapper" class="left">
				<div id="slider" class="nivoSlider"> <a href="#" target="_blank"><img src="img/stock/slide-image-1.jpg" alt="image 1" /></a> <a href="#" target="_blank"><img src="img/stock/slide-image-2.jpg" alt=""  /></a> <a href="#" target="_blank"><img src="img/stock/slide-image-3.jpg" alt="" /></a> <img src="img/stock/slide-image-4.jpg" alt="" /> </div>
				
			</div>
			<div id="quickmenu" class="left">
				<h2 class="replace">Odkazy</h2>
				<div class="viewport">
					<ul class="overview">
						<?php
						if (isset($_GET['mn_post'])) {
							$old_mnpost = $_GET['mn_post'];
							$mn_cat = '2';
						  	$mn_tmpl = 'odkazy_uvod';
						  	$_GET['mn_post'] = '0';
						  	include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
						  	$_GET['mn_post'] = $old_mnpost;
						}
						elseif (isset($_GET['mn_page'])) {
							$old_mnpage = $_GET['mn_page'];
							$mn_cat = '2';
						  	$mn_tmpl = 'odkazy_uvod';
						  	$_GET['mn_page'] = '0';
						  	include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
						  	 $_GET['mn_page'] = $old_mnpage;
						}
						elseif (isset($_GET['mn_cat'])) {
							$old_mnpage = $_GET['mn_cat'];
							$mn_cat = '2';
						  	$mn_tmpl = 'odkazy_uvod';
						  	$_GET['mn_cat'] = '0';
						  	include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
						  	 $_GET['mn_cat'] = $old_mnpage;
						}
						else {
							$mn_cat = '2';
						  	$mn_tmpl = 'odkazy_uvod';
						  	include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
						}
						 
						

						?>
					</ul>
				</div>
				<a class="buttons prev" href="#">Prev</a> <a class="buttons next" href="#">Next</a> </div>
		</div>
		<!-- slide end --> 
		<!-- Main begin-->
		<div id="main" class="round_8 clearfix">
			<div id="home-content" class="left">
				<?php
				 
    error_reporting(E_ALL ^ E_NOTICE);
     if (!$_GET['page']) {
     // include "pages/intro.php";
     	include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
     }
     elseif ($_GET['page']=='fotoalbum') {
     	echo "<h2>Fotoalbum</h2>";
	  $mn_cat = '4';
	  $mn_tmpl = 'fotoalbum';
	  include '/home/kovkus/msmiskovecka.eu/htdocs/www/cms/mn-show.php';
     }
     else {
     //include "pages/".$_GET['page'].".php";
     
     }
  	
?>

</div>
			<div  id="sidebar" class="left">
				<?php
				if (!isset($_GET['mn_page'])) {
					echo "&nbsp;";
				}
				elseif ($_GET['mn_page'] == '1' || $_GET['mn_page'] == '2' || $_GET['mn_page'] == '3' || $_GET['mn_page'] == '4' || $_GET['mn_page'] == '5') {
					echo '<ul id="submenu" class="shadow-light">
					<li><a href="?mn_page=1"><span>Trieda I.</span></a></li>
					<li><a href="?mn_page=2"><span>Trieda II.</span></a></li>
					<li><a href="?mn_page=3"><span>Trieda III.</span></a></li>
					<li><a href="?mn_page=4"><span>Trieda IV.</span></a></li>
					<li><a href="?mn_page=5"><span>Trieda V.</span></a></li>
					</ul>';
				}
				?>
			</div>	
	
				
			
		</div>
		<!-- Main end --> 
		<!-- Footer begin -->
		<div id="footer" class="round_8 clearfix">
			<div id="footer-bottom"> <div id="toTop" class="left">Naspäť hore</div><div id="footer-note" class="right"><small>©2014 <strong>Dávid Kováč</strong></small></div>
				
			</div>
		</div>
		<!-- Footer end --> 
		
	</div>
	<!-- Container end --> 
</div>
<script type="text/javascript">
    $(window).load(function() {
        $('#slider').nivoSlider({
			effect: 'fade' 		
		});
    });
	// Quick Menu
	$('#quickmenu').tinycarousel({ 
		axis: 'y',
		display: 3, 
		duration: 500
	});
</script> 
	
</body>
</html>