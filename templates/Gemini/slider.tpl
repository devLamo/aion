<script type="text/javascript" src="{THEME}/js/slides.js"></script>
<script type="text/javascript">
	$(function(){
		$('#slides').slides({
			effect: 'fade',
			play: 5000,
			pause: 2500,
			generatePagination: false,
			preload: true,
			hoverPause: true
		});
	});
</script>
<div id="slides">
	<div class="slides_container">
		<div><a href="#"><img src="{THEME}/img/slide1.jpg" alt="" /></a></div>
		<div><a href="#"><img src="{THEME}/img/slide2.jpg" alt="" /></a></div>
		<div><a href="#"><img src="{THEME}/img/slide3.jpg" alt="" /></a></div>
		<div><a href="#"><img src="{THEME}/img/slide4.jpg" alt="" /></a></div>
	</div>
	<a href="#" class="thide prev">&lt;</a>
	<a href="#" class="thide next">&gt;</a>
</div>