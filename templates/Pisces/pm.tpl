[pmlist]
<h2 class="dpad heading" style="margin-bottom: 0;">Список сообщений</h2>
[/pmlist]
[newpm]
<h2 class="dpad heading" style="margin-bottom: 0;">Новое сообщение</h2>
[/newpm]
[readpm]
<h2 class="dpad heading" style="margin-bottom: 0;">Ваши сообщения</h2>
[/readpm]
<div class="dpad basecont">
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
[pmlist]
<div class="dpad basecont">{pmlist}</div>
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
				Введите код:<span class="impot">*</span>
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
				Введите два слова, показанных на изображении: <span class="impot">*</span>
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
		<div class="lcol">
			<span class="thide arcom">&lt;</span>
			<div class="avatar">
				<img src="{foto}" alt=""/>
				{group-icon}
			</div>
			<ul class="small reset">
				<li>Публикаций: {news-num}</li>
				<li>Комментариев: {comm-num}</li>
				<li>ICQ: {icq}</li>
			</ul>
		</div>
		<div class="rcol">
			<div class="dpad dtop">
				<span>{date}</span>
				<h3>{author}</h3>
			</div>
			<div class="dpad cominfo">
				<span class="argreply">[reply]<b>Ответить</b>[/reply]</span>
				<ul class="reset small">
					<li>Группа: {group-name}</li>
					<li>Регистрация: {registration}</li>
				</ul>
				<span class="dleft">&nbsp;</span>
			</div>
			<div class="dpad dcont">
				<h3 style="margin-bottom: 0.4em;">[reply]{subj}[/reply]</h3>
				{text}
				<br clear="all" />
				[signature]<div class="signature">--------------------</div><div class="slink">{signature}</div><br />[/signature]
			</div>
			<div class="dpad comedit">
				<ul class="reset small">
					<li>[complaint]Пожаловаться[/complaint]</li>
					<li>[ignore]Игнорировать[/ignore]</li>
					<li>[del]Удалить[/del]</li>
				</ul>
			</div>				
		</div>
		<div class="clr"></div>
	</div>
[/readpm]