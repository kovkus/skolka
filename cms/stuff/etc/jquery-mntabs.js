// mnTabs for MNews CMS by Milan Nemčík
// Based on simpleTabs by Jonathan Coulet ( http://supercanard.phpnet.org/jquery-test/simpleTabs/ )

(function($){
	$.fn.mnTabs = function(option){
		var param = jQuery.extend({
			defautContent: 1
		}, option);
		$(this).each(function() {
			var $this = this;
			var $thisId = '#'+this.id;
			var nbTab = $($thisId+' > div').size();
			hideAll();
			changeContent(param.defautContent);
			
			function hideAll(){$($thisId+' .t-content').hide();}
			
			function changeContent(indice){
				hideAll();
				$($thisId+' .t-nav li').removeClass('active');
				$($thisId+' #t-nav-'+indice).addClass('active');
				$($thisId+' #t-'+indice).fadeIn();
				$('#t-id').attr('value', indice);
			}
			
			$($thisId+' .t-nav li').click(function(){
				var numContent = this.id.substr(this.id.length-1,this.id.length);
				changeContent(numContent);
			});
		});
 }
})(jQuery);