[not-group=5]
<div class="panel">
<div style="padding-top:2px; padding-left:5px;">Привет, <b>{login}</b>!</div>
<div style="padding-top:5px; padding-bottom:5px; padding-left:22px;">
    <a href="{profile-link}">Мой профиль</a><br />
    <a href="{pm-link}">Cообщения ({new-pm} | {all-pm})</a><br />
	<a href="{favorites-link}">Мои закладки</a><br />
	<a href="{stats-link}">Статистика</a><br />
	<a href="{newposts-link}">Обзор непрочитанного</a>
	</div>
<div style="padding-top:2px; padding-bottom:5px;"><a href="{logout-link}"><b>Завершить сеанс!</b></a></div>
<div style="padding-bottom:5px;">Вы просматриваете мобильную версию сайта. <a href="/index.php?action=mobiledisable">Перейти на полную версию сайта.</a></div>
</div>
[/not-group]
[group=5]
<div class="panel"><form method="post">
              {login-method}&nbsp;&nbsp;&nbsp;<input type="text" name="login_name" style="width:103px; font-family:tahoma; font-size:11px;"><br />
              Пароль: <input type="password" name="login_password" style="width:103px; font-family:tahoma; font-size:11px;"> <input type="submit" value=" Войти "><br />
					<input name="login" type="hidden" id="login" value="submit">
			  </form>
              <div style="padding-top:8px; padding-bottom:5px;"><a href="{registration-link}">Регистрация на сайте!</a> <a href="{lostpassword-link}">Забыли пароль?</a></div>
			  <div style="padding-bottom:5px;">Вы просматриваете мобильную версию сайта. <a href="/index.php?action=mobiledisable">Перейти на полную версию сайта.</a></div>
</div>
[/group]