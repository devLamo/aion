[not-group=5]
	<ul class="reset loginbox">
		<li class="loginava">
			<a href="{profile-link}">
				<img src="{foto}" alt="{login}" />
				<b>&nbsp;</b>
			</a>
		</li>
		<li class="loginbtn">
			<a class="lbn" id="logbtn" href="#"><b>{login}</b></a>
			<a class="thide lexit" href="{logout-link}">Выход</a>
				<div id="logform" class="radial">
					<ul class="reset loginenter">
		[admin-link]<li><a href="{admin-link}" target="_blank"><b>Админпанель</b></a></li>[/admin-link]
					<li><a href="{profile-link}">Мой профиль</a></li>
					<li><a href="{favorites-link}">Мои закладки ({favorite-count})</a></li>
					<li><a href="{newposts-link}">Непрочитанное</a></li>
					<li><a href="/?do=lastcomments">Последние комментарии</a></li>
					<li><a href="{stats-link}">Статистика</a></li>
				</ul>
			</div>
		</li>
		<li class="lvsep"><a href="{addnews-link}">Добавить новость</a></li>
		<li class="lvsep"><a class="radial" href="{pm-link}">{new-pm}</a><a href="{pm-link}">Сообщений</a></li>
	</ul>
[/not-group]
[group=5]
	<ul class="reset loginbox">
		<li class="loginbtn">
			<a class="lbn" id="logbtn" href="#"><b>Войти</b></a>
			<form method="post" action="">
				<div id="logform" class="radial">
					<ul class="reset">
						<li class="lfield"><label for="login_name">{login-method}</label><input type="text" name="login_name" id="login_name" /></li>
						<li class="lfield lfpas"><label for="login_password">Пароль (<a href="{lostpassword-link}">Забыли?</a>):</label><input type="password" name="login_password" id="login_password" /></li>
						<li class="lfield lfchek"><input type="checkbox" name="login_not_save" id="login_not_save" value="1"/><label for="login_not_save">&nbsp;Чужой компьютер</label></li>
						<li class="lbtn"><button class="fbutton" onclick="submit();" type="submit" title="Войти"><span>Войти</span></button></li>
					</ul>
					<input name="login" type="hidden" id="login" value="submit" />
				</div>
			</form>
		</li>
		<li class="lvsep"><a href="{registration-link}">Регистрация</a></li>
	</ul>
[/group]