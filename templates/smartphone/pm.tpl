<div class="title"><h2>Персональные сообщения</h2></div>
<div class="post">
    <div class="news">[inbox]Входящие сообщения[/inbox] <br /> [outbox]Отправленные сообщения[/outbox] <br /> [new_pm]Отправить сообщение[/new_pm]</div>
</div>
<div class="panel">
    &nbsp;
</div> 

[pmlist]
<div class="title"><h2>Список сообщений</h2></div>
<div class="post">
    <div class="news">{pmlist}</div>
</div>
<div class="panel">
    &nbsp;
</div> 
[/pmlist]
[newpm]
<div class="title"><h2>Отправка сообщения</h2></div>
<div class="post">
 <table width="450" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td height="25">Получатель:</td>
                              <td height="25"><input type="text" name="name" value="{author}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td height="25">Тема:</td>
                              <td height="25"><input type="text" name="subj" value="{subj}" class="f_input" /></td>
                            </tr>
                            <tr>
                              <td colspan="2"><textarea name="comments" id="comments" cols="40" style="height:80px;" />{text}</textarea></td>
                            </tr>
                            <tr>
                              <td colspan="2" class="news"><input type="checkbox" name="outboxcopy" value="1" /> Сохранить сообщение в папке "Отправленные"</td>
                            </tr>
[sec_code]                  <tr>
                              <td height="25">Код:</td>
                              <td height="25"><br />{sec_code}</td>
                            </tr>
                            <tr>
                              <td height="25">Введите код:</td>
                              <td height="25"><input type="text" name="sec_code" id="sec_code" style="width:115px" class="f_input" /></td>
                            </tr>
[/sec_code]
[recaptcha]           <tr>
                        <td colspan="2" height="25"><strong>Введите два слова, показанных на изображении:</strong></td>
                      </tr>
                      <tr>
                        <td colspan="2" height="25">{recaptcha}</td>
                      </tr>
[/recaptcha]
                            <tr>
                              <td colspan="2"><br /><input class="bbcodes" type="submit" name="add" value="отправить" />&nbsp;&nbsp;<input class="bbcodes" type="button" onclick="dlePMPreview()" value="просмотр" /></td>
                            </tr>
                          </table>
</div>
<div class="panel">
    &nbsp;
</div> 
[/newpm]
[readpm]
<div class="title"><strong>{subj}</strong></div>
<div class="post">
    <div class="news">{text}</div>
</div>
<div class="panel">
    Отправил: <strong>{author}</strong> | [reply]ответить[/reply] | [del]удалить[/del]
</div>
[/readpm]