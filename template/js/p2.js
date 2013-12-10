/*jQuery.noConflict();*/
jQuery(function($) { 
		
	// Superfish
	$("ul.sf-menu").supersubs({ 
		minWidth:    10,   // minimum width of sub-menus in em units 
		maxWidth:    25,   // maximum width of sub-menus in em units 
		extraWidth:  1     // extra width can ensure lines don't sometimes turn over 
						   // due to slight rounding differences and font-family 
	}).superfish({
		delay:			300,
		dropShadows:    false
	});  // call supersubs first, then superfish, so that subs are 
					 // not display:none when measuring. Call before initialising 
					 // containing tabs for same reason. 
			

	// Full page background
	$.supersized({
		//Background image
		slides	:  [ { image : 'img/bg1.jpg' } ]
	});
			
	
	// Cufon
	Cufon.replace('.replace,.sidebar-widget h4',{fontFamily: 'Museo 500'} );
	Cufon.replace('.sf-menu a',{fontFamily: 'Museo Sans 500'} );
	
	// ColorBox
	$(".video_modal").colorbox({iframe:true, innerWidth:"50%", innerHeight:"50%"});
	$("a[rel='example1']").colorbox();
	$("a[rel='example2']").colorbox({transition:"fade"});
	$("a[rel='example3']").colorbox({transition:"none"});
	$("a[rel='example4']").colorbox({slideshow:true});

	// Scroll to Top
	$('#toTop').click(function() {
		$('#content-wrapper').animate({scrollTop:0},600);
	});	
	
	// Twitter Feed
	$(".pkb-tweet").tweet({
		username: "envato",
		join_text: "auto",
		count: 1,
		auto_join_text_default: "we said,",
		auto_join_text_ed: "we",
		auto_join_text_ing: "we were",
		auto_join_text_reply: "we replied to",
		auto_join_text_url: "we were checking out",
		loading_text: "loading tweets..."
	});

	
	// Google Map
	$("#modalmap").colorbox({iframe:true, innerWidth:"50%", innerHeight:"50%", href:" http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=476+Whitehall+Street+Atlanta,+GA+30303&amp;aq=&sll=33.743594,-84.405288&sspn=0.006129,0.013733&ie=UTF8&hq=&hnear=476+Whitehall+St+SW,+Atlanta,+Georgia+30303&t=h&amp;ll=33.743527,-84.403068&amp;spn=0.02498,0.036478&amp;z=14&iwloc=A&output=embed" });
});




