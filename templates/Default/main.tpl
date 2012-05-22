<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
{headers}
<link rel="shortcut icon" href="{THEME}/images/favicon.ico" />
<link media="screen" href="{THEME}/style/styles.css" type="text/css" rel="stylesheet" />
<link media="screen" href="{THEME}/style/engine.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{THEME}/js/libs.js"></script>
</head>
<body>
{AJAX}
<div id="toolbar" class="wwide">
	<div class="wrapper"><div class="dpad">
		<span class="htmenu"><a href="#" onclick="this.style.behavior='url(#default#homepage)';this.setHomePage('http://dle-news.ru');">Сделать домашней</a><span>|</span><a href="#" rel="sidebar" onclick="window.external.AddFavorite(location.href,'dle-news.ru'); return false;">Добавить в избранное</a></span>
		{login}
	</div></div>
	<div class="shadow">&nbsp;</div>
</div>
<div class="wrapper">
	<div id="header" class="dpad">
		<h1><a class="thide" href="/index.php" title="DataLife Engine - Softnews Media Group">DataLife Engine - Softnews Media Group</a></h1>
		<form action="" name="searchform" method="post">
			<input type="hidden" name="do" value="search" />
			<input type="hidden" name="subaction" value="search" />
			<ul class="searchbar reset">
				<li class="lfield"><input id="story" name="story" value="Поиск..." onblur="if(this.value=='') this.value='Поиск...';" onfocus="if(this.value=='Поиск...') this.value='';" type="text" /></li>
				<li class="lbtn"><input title="Найти" alt="Найти" type="image" src="{THEME}/images/spacer.gif" /></li>
			</ul>
		</form>
		<div class="headlinks">
			<ul class="reset">
				<li><a href="/index.php">Главная</a></li>
				[group=5]<li><a href="/index.php?do=register">Регистрация</a></li>[/group]
				<li><a href="/index.php?do=feedback">Контакты</a></li>
				<li><a href="/index.php?do=rules">Правила</a></li>
			</ul>
		</div>
	</div>
	<div class="himage"><div class="himage"><div class="himage dpad">
		<h2>Многопользовательский новостной движок,<br />
		предназначенный для организации собственных<br />
		СМИ и блогов в интернете.</h2>
	</div></div></div>
	<div class="mbar" id="menubar"><div class="mbar"><div class="mbar dpad">
		<div class="menubar">
			{include file="topmenu.tpl"}
		</div>
	</div></div></div>
	<div class="wtop wsh"><div class="wsh"><div class="wsh">&nbsp;</div></div></div>
	<div class="shadlr"><div class="shadlr">
		<div class="container">
			<div class="vsep">
				<div class="vsep">
					<div id="midside" class="rcol">
						[not-aviable=main]{speedbar}[/not-aviable]
						<div class="hbanner">
							<div class="dpad" align="center">{banner_header}</div>
							<div class="dbtm"><span class="thide">на правах рекламы</span></div>
						</div>
						[sort]<div class="sortn dpad"><div class="sortn">{sort}</div></div>[/sort]
						{info}
						{content}
					</div>
					<div id="sidebar" class="lcol">
						{include file="sidebar.tpl"}
					</div>
					<div class="clr"></div>
				</div>
			</div>
			<div class="footbox">
				<div class="rcol">
					<div class="btags">
						{tags}
						<div class="shadow">&nbsp;</div>
					</div>
				</div>
				<div class="lcol">
					<p>Уважаемые вебмастера, Вы<br />
					просматриваете тестовую страницу<br />
					<b>DataLife Engine</b>.<br />
					Текущая версия 9.6.</p>
				</div>
			</div>
		</div>
	</div></div>
	<div class="wbtm wsh"><div class="wsh"><div class="wsh">&nbsp;</div></div></div>
</div>
<div id="footmenu" class="wwide">
	<div class="wrapper"><div class="dpad">
		<ul class="reset">
			<li><a href="/index.php">Главная страница</a></li>
			[group=5]<li><a href="/index.php?do=register">Регистрация</a></li>[/group]
			[not-group=5]<li><a href="/addnews.html">Добавить новость</a></li>[/not-group]
			<li><a href="/newposts/">Новое на сайте</a></li>
			<li><a href="/statistics.html">Статистика</a></li>
			<li><a href="http://dle-news.ru">Поддержка скрипта</a></li>
		</ul>
	</div></div>
	<div class="shadow">&nbsp;</div>
</div>
<div id="footer" class="wwide">
	<div class="wrapper"><div class="dpad">
		<span class="copyright">
			Copyright &copy; 2004-2012 <a href="http://dle-news.ru">SoftNews Media Group</a> All Rights Reserved.<br />
			Powered by DataLife Engine &copy; 2012 <a href="http://6dle.ru" target="_blank">Шаблоны для CMS DataLife Engine</a>
		</span>
		<div class="counts">
			<ul class="reset">
				<li><a href="http://www.mid-team.ws/" title="M.I.D Team" target="_blank"><img src="/uploads/button.gif" style="border: none;" /></a></li>
				<li><a href="http://www.mid-team.ws/" title="M.I.D Team" target="_blank"><img src="/uploads/button.gif" style="border: none;" /></a></li>
				<li><a href="http://www.mid-team.ws/" title="M.I.D Team" target="_blank"><img src="/uploads/button.gif" style="border: none;" /></a></li>
			</ul>
		</div>
		<div class="clr"></div>
	</div></div>
</div>
</body>
</html>