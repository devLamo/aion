[pmlist]
<div class="pheading"><h2>Список сообщений</h2></div>
[/pmlist]
[newpm]
<div class="pheading"><h2>Новое сообщение</h2></div>
[/newpm]
[readpm]
<div class="pheading"><h2>Ваши сообщения</h2></div>
[/readpm]
<div class="basecont">
<div class="dpad">
<div class="pm_status">
	<div class="pm_status_head">Состояние папок</div>
	<div class="pm_status_content">Папки персональных сообщений заполнены на:
{pm-progress-bar}
{proc-pm-limit}% от лимита ({pm-limit} сообщений)
	</div>
</div>
<div style="padding-top:10px;">[inbox]Входящие сообщения[/inbox]<br /><br />
[outbox]Отправленные сообщения[/outbox]<br /><br />
[new_pm]Отправить сообщение[/new_pm]</div>
</div><br />
<div class="clr"></div>
<br />
[pmlist]
<div class="dpad">{pmlist}</div>
[/pmlist]
[newpm]
<div class="baseform">
	<table class="tableform">
		<tr>
			<td class="label">
				Кому:
			</td>
			<td><input type="text" name="name" value="{author}" class="f_input" /></td>
		</tr>
		<tr>
			<td class="label">
				Тема:<span class="impot">*</span>
			</td>
			<td><input type="text" name="subj" value="{subj}" class="f_input" /></td>
		</tr>
		<tr>
			<td class="label">
				Сообщение:<span class="impot">*</span>
			</td>
			<td class="editorcomm">
			{editor}<br />
			<div class="checkbox"><input type="checkbox" id="outboxcopy" name="outboxcopy" value="1" /> <label for="outboxcopy">Сохранить сообщение в папке "Отправленные"</label></div>
			</td>
		</tr>
		[sec_code]
		<tr>
			<td class="label">
				Код:<span class="impot">*</span>
			</td>
			<td>
				<div>{sec_code}</div>
				<div><input type="text" name="sec_code" id="sec_code" style="width:115px" class="f_input" /></div>
			</td>
		</tr>
		[/sec_code]
		[recaptcha]
		<tr>
			<td class="label">
				Введите два слова, показанных на изображении:<span class="impot">*</span>
			</td>
			<td>
				<div>{recaptcha}</div>
			</td>
		</tr>
		[/recaptcha]
	</table>
	<div class="fieldsubmit">
		<button type="submit" name="add" class="fbutton"><span>Отправить</span></button>
		<input type="button" class="fbutton" onclick="dlePMPreview()" title="Просмотр" value="Просмотр" />
	</div>	
</div>
[/newpm]
[readpm]
<div class="bcomment">
	<div class="dtop">
		<div class="lcol"><span><img src="{foto}" alt=""/></span></div>
		<div class="rcol">
			<span class="reply">[reply]<b>Ответить</b>[/reply]</span>
			<ul class="reset">
				<li><h4>{author}</h4></li>
				<li>{date}</li>
			</ul>
			<ul class="cmsep reset">
				<li>Группа: {group-name}</li>
				<li>ICQ: {icq}</li>
			</ul>
		</div>
		<div class="clr"></div>
	</div>
	<div class="cominfo"><div class="dpad">
		<div class="comedit">
			<ul class="reset">
				<li>[complaint]Пожаловаться[/complaint]</li>
				<li>[ignore]Игнорировать[/ignore]</li>
				<li>[del]Удалить[/del]</li>
			</ul>
		</div>
		<ul class="cominfo reset">
			<li>Регистрация: {registration}</li>
			<li>Комментариев: {comm-num}</li>
			<li>Публикаций: {news-num}</li>
		</ul>
	</div>
	<span class="thide">^</span>
	</div>
	<div class="dcont">
		<h3 style="margin-bottom: 0.4em;">[reply]{subj}[/reply]</h3>
		{text}
		[signature]<br clear="all" /><div class="signature">--------------------</div><div class="slink">{signature}</div>[/signature]
		<div class="clr"></div>
	</div>
</div>
[/readpm]
</div>