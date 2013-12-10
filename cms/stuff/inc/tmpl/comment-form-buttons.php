<?php
  if (($conf['comments_bb']) || ($conf['comments_smiles'])) {
?>

	<script type="text/javascript">
		// insert_value() source: http://www.webmasterworld.com/forum91/4686.htm
		function insert_value(e){var t=document.getElementById('comment_text'); if(document.selection){t.focus();sel=document.selection.createRange();sel.text=e}else if(t.selectionStart||t.selectionStart=="0"){t.focus();var n=t.selectionStart;var r=t.selectionEnd;t.value=t.value.substring(0,n)+e+t.value.substring(r,t.value.length);t.setSelectionRange(r+e.length,r+e.length)}else{t.value+=e}}
		function mark_bbtext(t,n,r){var i=document.getElementById('comment_text');var s=0;if(document.selection&&!is_gecko){var o=document.selection.createRange().text;if(!o)o=r;i.focus();if(o.charAt(o.length-1)==" "){o=o.substring(0,o.length-1);s=1}if(o.charAt(0)==" "){o=o.substring(1,o.length);if(s==1)s=3;else s=2}if(s==1)document.selection.createRange().text=t+o+n+" ";else if(s==2)document.selection.createRange().text=" "+t+o+n;else if(s==3)document.selection.createRange().text=" "+t+o+n+" ";else document.selection.createRange().text=t+o+n}else if(i.selectionStart||i.selectionStart=="0"){var u=false;var a=i.selectionStart;var f=i.selectionEnd;if(f-a)u=true;var l=i.scrollTop;var c=i.value.substring(a,f);if(!c)c=r;if(c.charAt(c.length-1)==" "){s=1}if(c.charAt(0)==" "){if(s==1)s=3;else s=2}if(s==1)subst=t+c.substring(0,c.length-1)+n+" ";else if(s==2)subst=" "+t+c.substring(1,c.length)+n;else if(s==3)subst=" "+t+c.substring(1,c.length-1)+n+" ";else subst=t+c+n;i.value=i.value.substring(0,a)+subst+i.value.substring(f,i.value.length);i.focus();if(u){var h=a+(t.length+c.length+n.length);i.selectionStart=h;i.selectionEnd=h}else{i.selectionStart=a+t.length;i.selectionEnd=a+t.length+c.length}i.scrollTop=l}if(i.createTextRange)i.caretPos=document.selection.createRange().duplicate()}var clientPC=navigator.userAgent.toLowerCase();var is_gecko=clientPC.indexOf("gecko")!=-1&&clientPC.indexOf("spoofer")==-1&&clientPC.indexOf("khtml")==-1&&clientPC.indexOf("netscape/7.0")==-1
	</script>

<?php
    echo '<div id="mn-comment-buttons">';
    if ($conf['comments_bb']) {
      if (!isset($conf['comments_bb_buttons']) && empty($conf['comments_bb_buttons'])) $conf['comments_bb_buttons'] = '110010';
?>


  <?php if($conf['comments_bb_buttons'][0] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-bold.png" alt="Bold text" title="<?php echo $lang['web_bbcode_bold'];?>" height="16" width="16" onclick="mark_bbtext('[B]', '[/B]', 'text')" /><?php } ?>
  <?php if($conf['comments_bb_buttons'][1] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-italic.png" alt="Italics" title="<?php echo $lang['web_bbcode_italics'];?>" height="16" width="16" onclick="mark_bbtext('[I]', '[/I]', 'text')" /><?php } ?>
  <?php if($conf['comments_bb_buttons'][2] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-underline.png" alt="Underline" title="<?php echo $lang['web_bbcode_underline'];?>" height="16" width="16" onclick="mark_bbtext('[U]', '[/U]', 'text')" /><?php } ?>
  <?php if($conf['comments_bb_buttons'][3] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-strikethrough.png" alt="Strikethrough" title="<?php echo $lang['web_bbcode_strikethrough'];?>" height="16" width="16" onclick="mark_bbtext('[S]', '[/S]', 'text')" /><?php } ?>
  <?php if($conf['comments_bb_buttons'][4] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-link.png" alt="Link" title="<?php echo $lang['web_bbcode_link'];?>" height="16" width="16" onclick="mark_bbtext('[URL=http://]', '[/URL]', 'text')" /><?php } ?>
  <?php if($conf['comments_bb_buttons'][5] == '1') {?><img src="<?php echo $conf['admin_url'];?>/stuff/img/icons/text-code.png" alt="Code" title="<?php echo $lang['web_bbcode_code'];?>" height="16" width="16" onclick="mark_bbtext('[CODE]', '[/CODE]', 'text')" /><?php } ?>


<?php
    }
    
    if (($conf['comments_bb']) && ($conf['comments_smiles'])) echo "&nbsp;&nbsp;";
    
    if ($conf['comments_smiles']) {
?>


  <img onclick="insert_value(':-)');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-smile.gif" alt=":-)" />
  <img onclick="insert_value(';-)');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-wink.gif" alt=";-)" />
  <img onclick="insert_value('8-)');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-cool.gif" alt="8-)" />
  <img onclick="insert_value(':-D';"   src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-lol.gif" alt=":-D" />
  <img onclick="insert_value(':-P');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-tongue.gif" alt=":-P" />
  <img onclick="insert_value(':-O');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-surprised.gif" alt=":-O" />
  <img onclick="insert_value(':-(');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-sad.gif" alt=":-(" />
  <img onclick="insert_value(':-/');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-undecided.gif" alt=":-/" />
  <img onclick="insert_value(';,(');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-cry.gif" alt=";,(" />
  <img onclick="insert_value(']:-D');" src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-evil.gif" alt="]:-D" />
  <img onclick="insert_value(':-[');"  src="<?php echo $conf['admin_url'];?>/stuff/img/smiles/smiley-angry.gif" alt=":-[" />


<?php
    }
    echo '</div>';
  }
?>
