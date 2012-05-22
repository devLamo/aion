<h2 class="dpad heading">Статистика сайта</h2>
<div class="baseform"><div class="dcont">
	<div class="lines">
		<ul class="reset">
			<li>За сутки: Добавлено <b>{news_day} новостей</b> и <b>{comm_day} комментариев</b>, зарегистрировано <b>{user_day} пользователей</b></li>
			<li>За неделю: Добавлено <b>{news_week} новостей</b> и <b>{comm_week} комментариев</b>, зарегистрировано <b>{user_week} пользователей</b></li>
			<li>За месяц: Добавлено <b>{news_month} новостей</b> и <b>{comm_month} комментариев</b>, зарегистрировано <b>{user_month} пользователей</b></li>
		</ul>
	</div>
</div></div>
<div class="dpad">
	<div class="basecont statistics">
		<ul class="lcol reset">
			<li><h5 class="green">Новости:</h5></li>
			<li>Общее кол-во новостей: <b class="blue">{news_num}</b></li>
			<li>Из них опубликовано: <b class="blue">{news_allow}</b></li>
			<li>Опубликовано на главной: <b class="blue">{news_main}</b></li>
			<li>Ожидает модерации: <b class="blue">{news_moder}</b></li>
		</ul>
		<ul class="lcol reset">
			<li><h5 class="green">Пользователи:</h5></li>
			<li>Общее кол-во пользователей: <b class="blue">{user_num}</b></li>
			<li>Из них забанено: <b class="blue">{user_banned}</b></li>
		</ul>
		<ul class="lcol reset">
			<li><h5 class="green">Комментарии:</h5></li>
			<li>Кол-во комментариев: <b class="blue">{comm_num}</b></li>
			<li><a href="/?do=lastcomments">Посмотреть последние</a></li>
		</ul>
		<br clear="all" />
	</div>
	<p><b>Общий размер базы данных: {datenbank}</b></p>
	<br /><br /><br />
	<div class="basecont">
		<h2 class="heading">Список лучших пользователей</h2>
		<table width="100%" class="userstop">{topusers}</table>
	</div>
</div>