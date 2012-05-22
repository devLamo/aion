<script type="text/javascript">//<![CDATA[
$(function(){
	$("#slidemenu").UlMenu();
});
//]]></script>
<div id="rightmenu" class="block">
	<div class="dtop"><h4 class="btl"><span>Навигация</span> по сайту</h4></div>
	<ul id="slidemenu" class="reset">
		<li><a href="#">О сайте</a></li>
		<li><a href="#">В мире</a></li>
		<li><span class="sublnk">Экономика</span></li>
		<li class="submenu">
			<ul>
				<li><a href="#">Подпункт 3.1</a></li>
				<li><a href="#">Подпункт 3.2</a></li>
				<li><a href="#">Подпункт 3.3</a></li>
			</ul>
		</li>
		<li><span class="sublnk">Религия</span></li>
		<li class="submenu">
			<ul>
				<li><a href="#">Подпункт 4.1</a></li>
				<li><a href="#">Подпункт 4.2</a></li>
				<li><a href="#">Подпункт 4.3</a></li>
			</ul>
		</li>
		<li><a href="#">Криминал</a></li>
		<li><a href="#">Спорт</a></li>
		<li><a href="#">Культура</a></li>
		<li><a href="#">Инопресса</a></li>
	</ul>
	<div class="linesbg">
		<ul class="reset">
			<li><a href="http://dle-news.ru">Поддержка скрипта</a></li>
			<li><a href="/index.php?do=search&amp;mode=advanced">Расширенный поиск</a></li>
			<li><a href="/index.php?do=lastnews">Все последние новости</a></li>
			<li><a href="/index.php?action=mobile">Мобильная версия сайта</a></li>
		</ul>
	</div>
</div>

<div id="popular" class="block redb">
	<div class="dcont">
		<h4 class="btl">Популярные статьи</h4>
		<ul>{topnews}</ul>
	</div>
	<div class="thide dbtm">------</div>
</div>

<div id="bcalendar" class="block">
	<div class="dtop"><h4 class="btl">Календарь</h4></div>
	<div class="dcont">{calendar}</div>
</div>

<div id="change-skin" class="block">
	<div class="change-skin">
		<div class="rcol">{changeskin}</div>
		<h4 class="btl">Оформление:</h4>
	</div>
</div>

{vote}

<div id="news-partner" class="block">
	<div class="dtop"><h4 class="btl"><span>Новости</span> партнеров</h4></div>
	{inform_dle}
</div>