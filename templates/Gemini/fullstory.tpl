<div class="base fullstory">
	<script type="text/javascript">//<![CDATA[
	$(function(){ $("#infb{news-id}").Button("#infc{news-id}"); });
	//]]></script>
	<div class="infbtn">
		<span id="infb{news-id}" class="thide" title="Информация к новости">Информация к новости</span>
		<div id="infc{news-id}" class="infcont">
			<ul>
				<li><i>Просмотров: {views}</i></li>
				<li><i>Автор: {author}</i></li>
				<li><i>Дата: {date}</i></li>
			</ul>
			[edit-date]<div class="editdate"><i>Изменил: <b>{editor}</b>[edit-reason]<br />Причина: {edit-reason}[/edit-reason]</i></div>[/edit-date]
			[rating]<div class="ratebox"><div class="rate">{rating}</div></div>[/rating]
		</div>
	</div>
	<span class="argbox">[day-news]<i>{date}</i>[/day-news]</span>
	<h3 class="btl">{title}</h3>
	<p class="argcat"><i>Категория: {link-category}</i></p>
	<div class="maincont">
		{full-story}
		<div class="clr"></div>
		[tags]<p class="basetags"><i>Метки к статье: {tags}</i></p>[/tags]
	</div>
	<div class="mlink">
		<span class="argback"><a href="javascript:history.go(-1)"><b>Вернуться</b></a></span>
		[not-group=5]<span class="argedit">[edit]<i>Редактировать</i>[/edit]</span>[/not-group]
		<span class="argcoms"><i>Комментариев: {comments-num}</i></span>
	</div>
	<div class="linesbg related">
		<h4 class="btl"><span>Наш сайт</span> рекомендует:</h4>	
		<ul class="reset">
			{related-news}
		</ul>
		<div class="frbtns">
			{favorites}
			[print-link]<img class="printlink" src="{THEME}/images/spacer.gif" alt="Распечатать" title="Распечатать" />[/print-link]
		</div>
	</div>
</div>
[group=5]
<div class="berrors"><div class="berrors">
	Уважаемый посетитель, Вы зашли на сайт как незарегистрированный пользователь.<br />
	Мы рекомендуем Вам <a href="/index.php?do=register">зарегистрироваться</a> либо войти на сайт под своим именем.
</div></div>
[/group]
<div id="tabbs">
	<ul class="reset tabmenu">
		<li><a class="selected" href="#tabln1"><b>Комментарии ({comments-num})</b></a></li>
		[poll]<li><a href="#tabln2"><b>Опрос к статье</b></a></li>[/poll]
	</ul>
	<div class="tabcont" id="tabln1">
		{comments}
		{navigation}
		{addcomments}
	</div>
	[poll]<div class="tabcont" id="tabln2">
		{poll}
	</div>[/poll]
</div>