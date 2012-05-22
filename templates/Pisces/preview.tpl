[short-preview]
<div class="base shortstory">
	<div class="dpad">
		<h3 class="btl">[full-link]{title}[/full-link]</h3>
		<p class="binfo small">Автор: {author} от [day-news]{date}[/day-news], посмотрело: {views}</p>
		<div class="maincont">
			<span class="argcoms">[com-link]<b>{comments-num}</b>[/com-link]</span>
			{short-story}
			<div class="clr"></div>
		</div>
		<div class="mlink"><div class="mlink">
			<span class="argmore">[full-link]<b>Подробнее</b>[/full-link]</span>
		</div></div>
		<p class="argcat small">Категория: {link-category}</p>
	</div>
</div>
[/short-preview]
[full-preview]
<div class="base fullstory">
	<div class="dpad">
		<h3 class="btl">{title}</h3>
		<p class="binfo small">Автор: {author} от [day-news]{date}[/day-news], посмотрело: {views}</p>
		<div class="maincont">
			<span class="argcoms"><b>{comments-num}</b></span>
			{full-story}
			<div class="clr"></div>
		</div>
		<div class="storenumber">{pages}</div>
		[tags]<div class="btags"><div class="btags small">Теги: {tags}</div></div>[/tags]
		<div class="mlink[tags] mlinktags[/tags]"><div class="mlink">
			[not-group=5]
			<ul class="isicons reset">
				<li>[edit]<img src="{THEME}/dleimages/editstore.png" title="Редактировать" alt="Редактировать" />[/edit]</li>
				<li>{favorites}</li>
			</ul>
			[/not-group]
		</div></div>
		<p class="argcat small">Категория: {link-category}</p>
	</div>
</div>
[/full-preview]
[static-preview]
<div class="dpad">
	<h2 class="heading">{description}</h2>
	<div class="basecont">
		{static}
		<br clear="all" />
		<div class="storenumber">{pages}</div>
	</div>
</div>
[/static-preview]
