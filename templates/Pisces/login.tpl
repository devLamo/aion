[not-group=5]
<ul class="reset loginbox">
	<li class="lvsep"><a id="loginlink" href="#">Привет, {login}!</a></li>
	<li class="loginbtn"><a href="{logout-link}"><b>Выход</b></a></li>
</ul>
<div style="display: none;" id="logindialog" title="{login}">
	<div class="userinfo">
		<div class="lcol">
			<div style="margin: 0" class="avatar"><a href="{profile-link}"><img src="{foto}" alt="{login}" /></a></div>
		</div>
		<div class="rcol">
			<ul class="reset">
	[admin-link]<li><a href="{admin-link}" target="_blank"><b>Админпанель</b></a></li>[/admin-link]
				<li><a href="{addnews-link}"><b>Добавить новость</b></a></li>
				<li><a href="{pm-link}">Сообщения: ({new-pm} | {all-pm})</a></li>
				<li><a href="{profile-link}">Мой профиль</a></li>
				<li><a href="{favorites-link}">Мои закладки</a></li>
				<li><a href="{stats-link}">Статистика</a></li>
			</ul>
		</div>
		<div class="clr"></div>
	</div>
</div>
[/not-group]
[group=5]
<ul class="reset loginbox">
	<li class="lvsep"><a href="{registration-link}">Регистрация</a></li>
	<li class="loginbtn"><a id="loginlink" href="#"><b>Войти</b></a></li>
</ul>
<div style="display: none;" id="logindialog" title="Авторизация">
	<form method="post" action="">
		<div class="logform">
			<ul class="reset">
				<li class="lfield"><label for="login_name">{login-method}</label><br /><input type="text" name="login_name" id="login_name" /></li>
				<li class="lfield lfpas"><label for="login_password">Пароль (<a href="{lostpassword-link}">Забыли?</a>):</label><br /><input type="password" name="login_password" id="login_password" /></li>
				<li class="lfield lfchek"><input type="checkbox" name="login_not_save" id="login_not_save" value="1"/><label for="login_not_save">&nbsp;Чужой компьютер</label></li>
				<li class="lbtn"><button class="fbutton" onclick="submit();" type="submit" title="Войти"><span>Войти</span></button></li>
			</ul>
			<input name="login" type="hidden" id="login" value="submit" />
		</div>
	</form>
</div>
[/group]