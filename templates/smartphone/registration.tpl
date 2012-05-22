<div class="panel">
    [registration]Регистрация нового пользователя[/registration][validation]Обновление профиля пользователя[/validation]
</div> 
<div class="post">
[registration]
<strong>Здравствуйте, уважаемый посетитель нашего сайта!</strong><br /><br />Регистрация на нашем сайте позволит Вам быть его полноценным участником. Вы сможете добавлять новости на сайт, оставлять свои комментарии, просматривать скрытый текст и многое другое.<br /><br />В случае возникновения проблем с регистрацией, обратитесь к администратору сайта.<br /><br />
[/registration]

[validation]
<strong>Уважаемый посетитель</strong>,<br /><br />Ваш аккаунт был зарегистрирован на нашем сайте, однако информация о Вас является неполной, поэтому заполните дополнительные поля в Вашем профиле.<br /><br />
[/validation]
</div>
<div class="panel">&nbsp;</div>
<table width="100%">
[registration]
                            <tr>
                              <td height="25" width="150">Логин:</td>
                              <td><input type="text" name="name" id='name' class="f_input" /><br /><input style="height:18px; font-family:tahoma; font-size:11px; border:1px solid #DFDFDF; background: #FFFFFF" title="Проверить доступность логина для регистрации" onclick="CheckLogin(); return false;" type="button" value="Проверить имя" /><div id='result-registration'></div></td>
                            </tr>
                            <tr>
                              <td height="25">Пароль:</td>
                              <td><input type="password" name="password1" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Повторите пароль:</td>
                              <td><input type="password" name="password2" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Ваш E-Mail:</td>
                              <td><input type="text" name="email" class="f_input" /></td>
                            </tr>
		[question]
		<tr>
			<td class="label">
				Вопрос:
			</td>
			<td>
				<div>{question}</div>
			</td>
		</tr>
		<tr>
			<td class="label">
				Ответ:<span class="impot">*</span>
			</td>
			<td>
				<div><input type="text" name="question_answer" class="f_input" /></div>
			</td>
		</tr>
		[/question]
[sec_code]
                            <tr>
                              <td colspan="2" height="25"><strong>Подтверждение кода безопасности</strong></td>
                            </tr>
                            <tr>
                              <td height="25">Код безопасности:</td>
                              <td>{reg_code}</td>
                            </tr>
                            <tr>
                              <td height="25">Введите код:</td>
                              <td><input type="text" name="sec_code" style="width:115px" class="f_input" /></td>
                            </tr>
[/sec_code]
[recaptcha]
                      <tr>
                        <td colspan="2" height="25"><strong>Введите два слова, показанных на изображении:</strong></td>
                      </tr>
                      <tr>
                        <td colspan="2" height="25">{recaptcha}</td>
                      </tr>
[/recaptcha]
[/registration]
[validation]
                            <tr>
                              <td height="25">Ваше Имя:</td>
                              <td><input type="text" name="fullname" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25"><nobr>Место жительства:  </nobr></td>
                              <td><input type="text" name="land" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Номер ICQ:</td>
                              <td><input type="text" name="icq" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Фото:</td>
                              <td><input type="file" name="image" style="width:304px; height:18px" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">О себе:</td>
                              <td><textarea name="info" style="width:98%; height:70px" /></textarea></td>
                            </tr>
{xfields}
[/validation]
</div>
                            <tr>
                              <td height="25">&nbsp;</td>
                              <td><div style="padding-top:2px; padding-left:0px;">
                              <input type="submit" value=" Отправить "></div>
                              </td>
                            </tr>
                          </table>