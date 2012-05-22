<div class="title"><strong>Пользователь: {usertitle}</strong></div>
<div class="panel">
    <div class="news">Полное имя: {fullname}
                              <br />Дата регистрации: {registration}
                              <br />Последнее посещение: {lastdate}
                              <br />Группа:    <font color="red">{status}</font>[time_limit] в группе до: {time_limit}[/time_limit]
                              <br /><br />Место жительства: {land}
                              <br />Номер ICQ: {icq}
                              <br />Немного о себе:<br />{info}<br /><br />Количество публикаций:     {news_num}<br />[ {news} ] [rss]<img src="{THEME}/css/rss_icon.gif" border="0" />[/rss]
                              <br /><br />Количество комментариев: {comm_num}<br />[ {comments} ]<br /><br />[ {email} ]<br />[ {pm} ]<br />{edituser}</div>
</div>



[not-logged]
<div id="options" style="display:none;">
<div class="title"><strong>Редактирование информации</strong></div>
<div class="post"> 
                          <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td width="120" height="25">Ваш E-Mail:</td>
                              <td ><input type="text" name="email" value="{editmail}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>{hidemail}</td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td height="25">Ваше Имя:</td>
                              <td><input type="text" name="fullname" value="{fullname}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25"><nobr>Место жительства:  </nobr></td>
                              <td><input type="text" name="land" value="{land}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Номер ICQ:</td>
                              <td><input type="text" name="icq" value="{icq}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td height="25">Старый пароль:</td>
                              <td><input type="password" name="altpass" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Новый пароль:</td>
                              <td><input type="password" name="password1" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Повторите:</td>
                              <td><input type="password" name="password2" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                          </table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td height="25">Блокировка по IP:<br /><textarea name="allowed_ip" style="width:97%; height:70px" class="f_textarea" />{allowed-ip}</textarea><br />Ваш текущий IP: <strong>{ip}</strong><br /><br /><font style="color:red;font-size:10px;">* Внимание! Будьте бдительны при изменении данной настройки. Доступ к Вашему аккаунту будет доступен только с того IP-адреса или подсети, который Вы укажете. Вы можете указать несколько IP адресов, по одному адресу на каждую строчку.<br />Пример: 192.48.25.71 или 129.42.*.*</font></td>
                            </tr>
                          </table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td height="25">Аватар:</td>
                              <td><input type="file" name="image" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td><input type="checkbox" name="del_foto" value="yes" />  Удалить фотографию</td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                          </table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td height="25">О себе:<br /><textarea name="info" style="width:97%; height:70px" class="f_textarea" />{editinfo}</textarea></td>
                            </tr>
                            <tr>
                              <td height="25">Подпись:<br /><textarea name="signature" style="width:97%; height:70px" class="f_textarea" />{editsignature}</textarea></td>
                            </tr>
                          </table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
{xfields}
                            <tr>
                              <td colspan="2" height="25"><div style="padding-top:2px; padding-left:0px;">
                              <input type="submit" value=" Отправить " /><br />
                              <input name="submit" type="hidden" id="submit" value="submit" />
                              </div></td>
                            </tr>
                          </table>
</div>
</div>
[/not-logged]