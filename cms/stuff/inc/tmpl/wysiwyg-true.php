
  <script type="text/javascript" src="./stuff/etc/tinymce/jquery.tinymce.js"></script>
  <script type="text/javascript">
    $(function() {
      $('textarea.tinymce').tinymce({
	      script_url : './stuff/etc/tinymce/tiny_mce.js',
	      mode : 'exact',
	      theme : 'advanced',
	      skin : 'o2k7',
	      skin_variant : 'silver',
	      plugins : 'advhr,advimage,advlink,advlist,autolink,contextmenu,emotions,inlinepopups,lists,media,paste,pdw,searchreplace,smeditimage,table',
	      entity_encoding : 'named',
	      entities :
	          '160,nbsp,161,iexcl,162,cent,163,pound,164,curren,165,yen,166,brvbar,167,sect,168,uml,169,copy,170,ordf,'
	        + '171,laquo,172,not,173,shy,174,reg,175,macr,176,deg,177,plusmn,178,sup2,179,sup3,180,acute,181,micro,182,para,'
	        + '183,middot,184,cedil,185,sup1,186,ordm,187,raquo,188,frac14,189,frac12,190,frac34,191,iquest,215,times,216,Oslash,247,divide,248,oslash,'
	        + '917,Epsilon,918,Zeta,919,Eta,920,Theta,921,Iota,922,Kappa,923,Lambda,924,Mu,925,Nu,926,Xi,927,Omicron,928,Pi,929,Rho,'
	        + '931,Sigma,932,Tau,933,Upsilon,934,Phi,935,Chi,936,Psi,937,Omega,945,alpha,946,beta,947,gamma,948,delta,949,epsilon,'
	        + '950,zeta,951,eta,952,theta,953,iota,954,kappa,955,lambda,956,mu,957,nu,958,xi,959,omicron,960,pi,961,rho,962,sigmaf,'
	        + '963,sigma,964,tau,965,upsilon,966,phi,967,chi,968,psi,969,omega,977,thetasym,978,upsih,982,piv,8226,bull,8230,hellip,'
	        + '8242,prime,8243,Prime,8254,oline,8260,frasl,8472,weierp,8465,image,8476,real,8482,trade,8501,alefsym,8592,larr,8593,uarr,'
	        + '8594,rarr,8595,darr,8596,harr,8629,crarr,8656,lArr,8657,uArr,8658,rArr,8659,dArr,8660,hArr,8704,forall,8706,part,8707,exist,'
	        + '8709,empty,8711,nabla,8712,isin,8713,notin,8715,ni,8719,prod,8721,sum,8722,minus,8727,lowast,8730,radic,8733,prop,8734,infin,'
	        + '8736,ang,8743,and,8744,or,8745,cap,8746,cup,8747,int,8756,there4,8764,sim,8773,cong,8776,asymp,8800,ne,8801,equiv,8804,le,8805,ge,'
	        + '8834,sub,8835,sup,8836,nsub,8838,sube,8839,supe,8853,oplus,8855,otimes,8869,perp,8901,sdot,8968,lceil,8969,rceil,8970,lfloor,'
	        + '8971,rfloor,9001,lang,9002,rang,9674,loz,9824,spades,9827,clubs,9829,hearts,9830,diams,338,OElig,339,oelig,'
	        + '376,Yuml,710,circ,732,tilde,8194,ensp,8195,emsp,8201,thinsp,8204,zwnj,8205,zwj,8206,lrm,8207,rlm,8211,ndash,8212,mdash,8216,lsquo,'
	        + '8217,rsquo,8218,sbquo,8220,ldquo,8221,rdquo,8222,bdquo,8224,dagger,8225,Dagger,8240,permil,8249,lsaquo,8250,rsaquo,8364,euro',
	      height : '200',
	      width : '710',
	      convert_fonts_to_spans : true,
	      relative_urls : false,
	      remove_script_host : false,
	      language : '{LANG}',
	
	      theme_advanced_buttons1 : 'bold,italic,underline,strikethrough, | ,justifyleft,justifycenter,justifyright,justifyfull, | ,bullist,numlist,outdent,indent, | ,link,unlink,anchor, | ,image,smeditimage,media, | ,forecolor,backcolor, | ,sub,sup,advhr,charmap,emotions, | ,undo,redo, | ,pdw_toggle',
	      theme_advanced_buttons2 : ',formatselect,fontsizeselect, | ,tablecontrols, | ,cut,copy,paste,pastetext,pasteword,selectall, | ,search,replace,cleanup,removeformat,',
	      theme_advanced_buttons3 : '',
	      theme_advanced_toolbar_location : 'top',
	      theme_advanced_toolbar_align : 'left',
	      theme_advanced_statusbar_location : 'bottom',
	      theme_advanced_resizing : true,
	      extended_valid_elements : 'a[id|class|name|href|target|title|onclick|rel|style],img[id|class|style|src|border|alt|title|rel|hspace|vspace|width|height|align|onmouseover|onmouseout|name],font[face|size|color|style],span[id|class|style],div[id|class|style]',
	
	      pdw_toggle_on : 1,
	      pdw_toggle_toolbars : '2'

	  });

    });
  </script>
  