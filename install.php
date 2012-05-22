<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2012 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: install.php
-----------------------------------------------------
 Назначение: Установка скрипта
=====================================================
*/
session_start();

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname (__FILE__));
define('ENGINE_DIR', ROOT_DIR.'/engine');

$config['charset'] = "utf-8";

require_once(ROOT_DIR.'/language/Russian/adminpanel.lng');
require_once(ENGINE_DIR.'/inc/include/functions.inc.php');
require_once(ENGINE_DIR.'/skins/default.skin.php');

extract($_REQUEST, EXTR_SKIP);

if($_REQUEST['action'] == "eula")
{

if ( !$_SESSION['dle_install'] ) msg( "", "Ошибка", "Установка скрипта была начата не с начала. Вернитесь на главную страницу начала установки скрипта: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

echoheader("", "");
echo <<<HTML
<form id="check-eula" method="post" action="">
<script language='javascript'>
check_eula = function()
{
	if( document.getElementById( 'eula' ).checked == true )
	{
		return true;
	}
	else
	{
		alert( 'Вы должны принять лицензионное соглашение, прежде чем продолжите установку.' );
		return false;
	}
}
document.getElementById( 'check-eula' ).onsubmit = check_eula;
</script>
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Лицензионное соглашение</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">Пожалуйста, внимательно прочитайте и примите пользовательское соглашение по использованию DataLife Engine.<br /><br /><div style="height: 300px; border: 1px solid #76774C; background-color: #FDFDD3; padding: 5px; overflow: auto;"><b>Лицензионное соглашение конечного пользователя</b><br /><br /><b>Предмет лицензионного соглашения</b><br /><br />Предметом настоящего лицензионного соглашения является право использования одной лицензионной копии программного продукта <b>DataLife Engine</b>, в&nbsp;порядке и&nbsp;на&nbsp;условиях, установленных настоящим соглашением.<br /><br /><b>Содержание договора</b><br /><br />Срок обслуживания клиента с&nbsp;момента приобретения одной лицензионной копии программного продукта DataLife Engine по&nbsp;базовой лицензии равен <b>одному году</b>. Если по&nbsp;истечении срока обслуживания, Вы решите не&nbsp;продлевать его действие, то&nbsp;Ваш программный продукт будет функционировать в&nbsp;полном объеме, но&nbsp;без&nbsp;нашей технической поддержки и&nbsp;без&nbsp;предоставления новых версий скрипта, за&nbsp;исключением критических обновлений скрипта.<br /><br />В случае приобретения и использования только базовой лицензии на скрипт, обслуживание клиентов, на время действия лицензионного соглашения, осуществляется только предоставлением стандартных услуг по обслуживанию: предоставление дистрибутивов, новых версий скрипта, критических обновлений скрипта. Технической поддержки по базовым лицензиям не предоставляется. Для получения технической поддержки по скрипту, пользователям необходимо иметь лицензию, включающую в себя службу технической поддержки, либо быть подписчиком на службу технической поддержки.<br /><br />Мы оставляем за&nbsp;собой право публиковать списки избранных пользователей своих программных продуктов. Мы оставляем за&nbsp;собой право в&nbsp;любое время изменять условия данного договора, но&nbsp;данные действия не&nbsp;имеют обратной силы. Изменения данного договора будут разосланы пользователям по&nbsp;электронной почте на&nbsp;адреса, указанные при&nbsp;приобретении системы.<br /><br /><b>Ограниченное использование</b><br /><br />Приобретая лицензию на&nbsp;программный продукт <b>DataLife Engine</b>, вы должны знать, что&nbsp;не&nbsp;приобретаете авторские права на&nbsp;программный продукт. Вы приобретаете только <b>право на&nbsp;использование</b> программного продукта на&nbsp;единственном веб сайте (одном домене второго уровня и его поддоменах), принадлежащем Вам или&nbsp;Вашему клиенту. Для&nbsp;использования скрипта на&nbsp;другом сайте, вам необходимо приобретать повторую лицензию. Запрещается перепродажа скрипта третьим лицам, и&nbsp;если вы приобретаете скрипт для&nbsp;Ваших клиентов, то&nbsp;вы обязаны ознакомить Ваших клиентов с&nbsp;данным лицензионным соглашением. Также в случае приобретения скрипта не для собственного использования, а для установки на сайты Ваших клиентов, вам необходимо отдавать клиентский доступ на сайте dle-news.ru вашим заказчикам, в противном случае мы не несем обязательств по поддержке Ваших клиентов.<br /><br /><b>Права и&nbsp;обязанности сторон</b><br /><br /><b>Покупатель имеет право:</b><ul><li>Изменять дизайн и&nbsp;структуру программного кода в&nbsp;соответствии с&nbsp;нуждами своего сайта.</li><li>Производить и&nbsp;распространять инструкции по&nbsp;созданным Вами модификациям шаблонов и&nbsp;языковых файлов, если в&nbsp;них будет иметься указание на&nbsp;оригинального разработчика программного продукта до&nbsp;Ваших модификаций. Модификации, произведенные Вами самостоятельно, не&nbsp;являются собственностью SoftNews Media Group, если не&nbsp;содержат программные коды непосредственно скрипта.</li><li>Создавать модули, которые будут взаимодействовать с&nbsp;нашими программными кодами, с&nbsp;указанием на&nbsp;то, что&nbsp;это Ваш оригинальный продукт.</li><li>Переносить программный продукт на&nbsp;другой сайт после обязательного уведомления нас об&nbsp;этом, а&nbsp;также полного удаления скрипта с&nbsp;предыдущего сайта.</li></ul><br /><b>Покупатель не&nbsp;имеет право:</b><ul><li>Передавать права на&nbsp;использование программного продукта третьим лицам.</li><li>Изменять структуру программных кодов, функции программы, с&nbsp;целью создания родственных продуктов</li><li>Создавать отдельные самостоятельные продукты, базирующиеся на&nbsp;нашем программном коде</li><li>Использовать копии программного продукта DataLife Engine по&nbsp;одной лицензии на более чем одном сайте (одном домене второго уровня и его поддоменах)</li><li>Рекламировать, продавать или&nbsp;публиковать на&nbsp;своем сайте пиратские копии нашего программного продукта</li><li>Распространять или&nbsp;содействовать распространению нелицензионных копий программного продукта DataLife Engine</li><li>Удалять механизмы проверки наличия оригинальной лицензии на&nbsp;использование скрипта</li></ul><b>Ограничение гарантийных обязательств</b><br /><br />Мы хотим отметить, что&nbsp;механизмы безопасности, установленные на&nbsp;<b>DataLife Engine</b>, имеют известные ограничения, и&nbsp;несмотря на&nbsp;то, что&nbsp;мы прилагаем максимальные усилия по&nbsp;обеспечению безопасности скрипта, вы должны быть ознакомлены с&nbsp;отсутствием абсолютных гарантий от&nbsp;взлома вашего сайта. Так&nbsp;же Наши гарантии и&nbsp;техническая поддержка не&nbsp;распространяются на&nbsp;модификации, произведенные третьей стороной, включая изменения программного кода, стиля, языковых пакетов, а&nbsp;также на&nbsp;изменения перечисленных частей, внесенные владельцем лицензии самостоятельно. Если программный продукт изменен Вами или&nbsp;третьей стороной, то&nbsp;мы вправе отказать Вам в&nbsp;технической поддержке. Вы должны быть ознакомлены, что&nbsp;программный продукт <b>DataLife Engine</b> не&nbsp;подлежит возврату или&nbsp;обмену из-за&nbsp;отсутствия гарантий защищающих программный продукт от&nbsp;копирования.<br /><br /><b>Права на&nbsp;интеллектуальную собственность</b><br /><br />Название <b>DataLife Engine</b>, а&nbsp;также входящие в&nbsp;данный продукт скрипты являются собственностью <b>SoftNews Media Group</b>, за&nbsp;исключением случаев, когда для&nbsp;компонента системы применяется другой тип лицензии. Программный продукт защищен законом об&nbsp;авторских правах. Любые публикуемые оригинальные материалы, создаваемые в&nbsp;результате использования нашего скрипта, и&nbsp;связанные с&nbsp;этим права на&nbsp;них, являются собственностью пользователя и&nbsp;защищены законом. SoftNews Media Group не&nbsp;несет никакой ответственности за&nbsp;содержание сайтов, создаваемых пользователем скрипта DataLife Engine.<br /><br /><b>Досрочное расторжение договорных обязательств</b><br /><br />Данное соглашение расторгается автоматически, если Вы отказываетесь выполнять условия нашего договора. Данное лицензионное соглашение может быть расторгнуто нами в&nbsp;одностороннем порядке, в&nbsp;случае установления фактов нарушения данного лицензионного соглашения. В&nbsp;случае досрочного расторжения договора Вы обязуетесь удалить все Ваши копии нашего программного продукта в&nbsp;течении 3 рабочих дней, с&nbsp;момента получения соответствующего уведомления.</div>
		<input type='checkbox' name='eula' id='eula'><b>Я принимаю данное соглашение</b>
		<br />
</td>
    </tr>
    <tr>
        <td style="padding:2px;"><input type=hidden name=action value="function_check"><input class=buttons type=submit value=" Продолжить >> "></td>
    </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;

}
elseif($_REQUEST['action'] == "function_check")
{

if ( !$_SESSION['dle_install'] ) msg( "", "Ошибка", "Установка скрипта была начата не с начала. Вернитесь на главную страницу начала установки скрипта: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

echoheader("", "");

echo <<<HTML
<form method="post" action="">
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Проверка установленных компонентов PHP</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;

echo"<tr>
<td height=\"25\" width=\"250\">&nbsp;Минимальные требования скрипта
<td height=\"25\" colspan=2>&nbsp;Текущее значение
<tr><td colspan=3><div class=\"hr_line\"></div></td></tr>";
 
$status = phpversion() < '5.1' ? '<font color=red><b>Нет</b></font>' : '<font color=green><b>Да</b></font>';

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Версия PHP 5.1 и выше</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = function_exists('mysql_connect') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Поддержка MySQL</td>
         <td colspan=2>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";


$status = extension_loaded('zlib') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Поддержка сжатия ZLib</td>
         <td colspan=2>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = extension_loaded('xml') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Поддержка XML</td>
         <td colspan=2>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = function_exists('iconv') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Поддержка iconv</td>
         <td colspan=2>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

   echo"<tr>
         <td colspan=3 class=\"navigation\"><br />Если любой из этих пунктов выделен красным, то пожалуйста выполните действия для исправления положения. В случае несоблюдения минимальных требований скрипта возможна его некорректная работа в системе.<br /><br /></td>
         </tr>";

echo"<tr><td colspan=3><div class=\"hr_line\"></div></td></tr><tr>
<td height=\"25\">&nbsp;Рекомендуемые настройки
<td height=\"25\" width=\"200\">&nbsp;Рекомендуемое значение
<td height=\"25\">&nbsp;Текущее значение
<tr><td colspan=3><div class=\"hr_line\"></div></td></tr>";

$status = ini_get('safe_mode') ? '<font color=red><b>Включено</b></font>' : '<font color=green><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Safe Mode</td>
         <td>&nbsp;Выключено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = function_exists('mysqli_connect') ? '<font color=green><b>Да</b></font>' : '<font color=red><b>Нет</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Поддержка MySQLi</td>
         <td>&nbsp;Да</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";


$status = ini_get('file_uploads') ? '<font color=green><b>Включено</b></font>' : '<font color=red><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Загрузка файлов</td>
         <td>&nbsp;Включено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = ini_get('output_buffering') ? '<font color=red><b>Включено</b></font>' : '<font color=green><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Буферизация вывода</td>
         <td>&nbsp;Выключено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = ini_get('magic_quotes_runtime') ? '<font color=red><b>Включено</b></font>' : '<font color=green><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Magic Quotes Runtime</td>
         <td>&nbsp;Выключено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = ini_get('register_globals') ? '<font color=red><b>Включено</b></font>' : '<font color=green><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Register Globals</td>
         <td>&nbsp;Выключено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

$status = ini_get('session.auto_start') ? '<font color=red><b>Включено</b></font>' : '<font color=green><b>Выключено</b></font>';;

   echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;Session auto start</td>
         <td>&nbsp;Выключено</td>
         <td>&nbsp;$status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";

   echo"<tr>
         <td colspan=3 class=\"navigation\"><br />Данные настройки являются рекомендуемыми для полной совместимости, однако скрипт способен работать даже если рекомендуемые настройки несовпадают с текущими.<br /><br /></td>
         </tr>";

echo <<<HTML
     <tr>
     <td height="40" colspan=3 align="right">&nbsp;&nbsp;
     <input class=buttons type=submit value="&nbsp;&nbsp;Продолжить >>&nbsp;&nbsp;">&nbsp;&nbsp;<input type=hidden name="action" value="chmod_check">
     </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;

} 
// ********************************************************************************
// Проверка прав на запись
// ********************************************************************************
elseif($_REQUEST['action'] == "chmod_check")
{

if ( !$_SESSION['dle_install'] ) msg( "", "Ошибка", "Установка скрипта была начата не с начала. Вернитесь на главную страницу начала установки скрипта: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

echoheader("", "");

echo <<<HTML
<form method="post" action="">
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Проверка на запись у важных файлов системы</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;

echo"<tr>
<td height=\"25\">&nbsp;Папка/Файл
<td width=\"100\" height=\"25\">&nbsp;CHMOD
<td width=\"100\" height=\"25\">&nbsp;Статус</tr><tr><td colspan=3><div class=\"hr_line\"></div></td></tr>";
 
$important_files = array(
'./backup/',
'./engine/data/',
'./engine/cache/',
'./engine/cache/system/',
'./uploads/',
'./uploads/files/',
'./uploads/fotos/',
'./uploads/posts/',
'./uploads/posts/thumbs/',
'./uploads/thumbs/',
'./templates/',
'./templates/Default/',
);


$chmod_errors = 0;
$not_found_errors = 0;
    foreach($important_files as $file){

        if(!file_exists($file)){
            $file_status = "<font color=red>не найден!</font>";
            $not_found_errors ++;
        }
        elseif(is_writable($file)){
            $file_status = "<font color=green>разрешено</font>";
        }
        else{
            @chmod($file, 0777);
            if(is_writable($file)){
                $file_status = "<font color=green>разрешено</font>";
            }else{
                @chmod("$file", 0755);
                if(is_writable($file)){
                    $file_status = "<font color=green>разрешено</font>";
                }else{
                    $file_status = "<font color=red>запрещено</font>";
                    $chmod_errors ++;
                }
            }
        }
        $chmod_value = @decoct(@fileperms($file)) % 1000;

    echo"<tr>
         <td height=\"22\" class=\"tableborder main\">&nbsp;$file</td>
         <td>&nbsp; $chmod_value</td>
         <td>&nbsp; $file_status</td>
         </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=3></td></tr>";
    }
if($chmod_errors == 0 and $not_found_errors == 0){
$status_report = 'Проверка успешно завершена! Можете продолжить установку!';
}else{
if($chmod_errors > 0){
$status_report = "<font color=red>Внимание!!!</font><br /><br />Во время проверки обнаружены ошибки: <b>$chmod_errors</b>. Запрещена запись в файл.<br />Вы должны выставить для папок CHMOD 777, для файлов CHMOD 666, используя ФТП-клиент.<br /><br /><font color=red><b>Настоятельно не рекомендуется</b></font> продолжать установку, пока не будут произведены изменения.<br />";
}
if($not_found_errors > 0){
$status_report .= "<font color=red>Внимание!!!</font><br />Во время проверки обнаружены ошибки: <b>$not_found_errors</b>. Файлы не найдены!<br /><br /><font color=red><b>Не рекомендуется</b></font> продолжать установку, пока не будут произведены изменения.<br />";
}
}

echo"<tr><td colspan=3><div class=\"hr_line\"></div></td></tr><tr><td height=\"25\" colspan=3>&nbsp;&nbsp;Состояние проверки</td></tr><tr><td style=\"padding: 5px\" colspan=3>$status_report</td></tr><tr><td colspan=3><div class=\"hr_line\"></div></td></tr>";    

echo <<<HTML
     <tr>
     <td height="40" colspan=3 align="right">&nbsp;&nbsp;
     <input class=buttons type=submit value="&nbsp;&nbsp;Продолжить >>&nbsp;&nbsp;">&nbsp;&nbsp;<input type=hidden name="action" value="doconfig">
     </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;

} elseif($_REQUEST['action'] == "doconfig")
{

if ( !$_SESSION['dle_install'] ) msg( "", "Ошибка", "Установка скрипта была начата не с начала. Вернитесь на главную страницу начала установки скрипта: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );


$url  = preg_replace( "'/install.php'", "", $_SERVER['HTTP_REFERER']);
$url  = preg_replace( "'\?(.*)'", "", $url);
if(substr("$url", -1) == "/"){ $url = substr($url, 0, -1); }
echoheader("", "");
echo <<<HTML
<form method="post" action="">
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Настройка конфигурации системы</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;


echo'<tr>
<td width="175" style="padding: 5px;">URL сайта:
<td style="padding: 5px;"><input name=url value="'.$url.'/" size=38 type=text class="edit"><br><span class="navigation">Укажите путь без имени файла, знак слеша <font color="red">/</font> на конце обязателен</span></tr>
<tr><td colspan="3" height="40">&nbsp;&nbsp;<b>Данные для доступа к MySQL серверу</b><td></tr>
<tr><td style="padding: 5px;">Сервер MySQL:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbhost" value="localhost"></tr>
<tr><td style="padding: 5px;">Имя базы данных:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbname"></tr>
<tr><td style="padding: 5px;">Имя пользователя:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbuser"></tr>
<tr><td style="padding: 5px;">Пароль:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbpasswd"></tr>
<tr><td style="padding: 5px;">Префикс:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbprefix" value="dle"> <span class="navigation">Не изменяйте параметр, если не знаете для чего он предназначен</span></tr>
<tr><td style="padding: 5px;">Кодировка для MySQL:<td style="padding: 5px;"><input class="edit" type=text size="28" name="dbcollate" value="utf8" readonly="readonly"> <span class="navigation">Не изменяйте параметр, если не знаете для чего он предназначен</span></tr>
<tr><td colspan="3"  height="40">&nbsp;&nbsp;<b>Данные для доступа к панели управления</b><td></tr>
<tr><td style="padding: 5px;">Имя администратора:<td style="padding: 5px;"><input class="edit" type=text size="28" name="reg_username" ></tr>
<tr><td style="padding: 5px;">Пароль:<td style="padding: 5px;"><input class="edit" type=password size="28" name="reg_password1"> <span class="navigation"><b>не</b> забудьте пароль!</span></tr>
<tr><td style="padding: 5px;">Повторите пароль:<td style="padding: 5px;"><input class="edit" type=password size="28" name="reg_password2"></tr>
<tr><td style="padding: 5px;">E-mail:<td style="padding: 5px;"><input class="edit" type=text size="28" name="reg_email"></tr>
<tr><td colspan="3"  height="40">&nbsp;&nbsp;<b>Дополнительные настройки</b><td></tr>
<tr><td style="padding: 5px;">Включить поддержку ЧПУ:
<td style="padding: 5px;">
<select class=rating name="alt_url"><option value="yes">Да</option><option value="no">Нет</option></select>&nbsp;&nbsp;<span class="navigation">Eсли вы отключите поддержку ЧПУ, то не забудьте удалить файл .htaccess в корневой папке</span>
</tr>';

echo <<<HTML
     <tr>
     <td height="40" colspan=3 align="right">&nbsp;&nbsp;
     <input class=buttons type=submit value="&nbsp;&nbsp;Продолжить >>&nbsp;&nbsp;">&nbsp;&nbsp;<input type=hidden name="action" value="doinstall">
     </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;

}
// ********************************************************************************
// Do Install
// ********************************************************************************
elseif($_REQUEST['action'] == "doinstall")
{

	if ( !$_SESSION['dle_install'] ) msg( "", "Ошибка", "Установка скрипта была начата не с начала. Вернитесь на главную страницу начала установки скрипта: <br /><br /><a href=\"http://{$_SERVER['HTTP_HOST']}/install.php\">http://{$_SERVER['HTTP_HOST']}/install.php</a>" );

    if(!$reg_username or !$reg_email or !$reg_password1 or !$url or $reg_password1 != $reg_password2){ msg("error", "Ошибка!!!" ,"Заполните необходимые поля!", "javascript:history.go(-1)"); }
	if (preg_match("/[\||\'|\<|\>|\"|\!|\$|\@|\&\~\*\+]/", $reg_username))
	{	
		msg("error", "Ошибка!!!" ,"Введенное имя администратора недопустимо к регистрации!", "javascript:history.go(-1)");	 
	}

    $reg_password = md5(md5($reg_password1));

	$url = htmlspecialchars( $url, ENT_QUOTES);
	$reg_email = htmlspecialchars( $reg_email, ENT_QUOTES);
	$alt_url = htmlspecialchars( $alt_url, ENT_QUOTES);
	$url = str_replace( "$", "&#036;", $url );
	$reg_email = str_replace( "$", "&#036;", $reg_email );
	$alt_url = str_replace( "$", "&#036;", $alt_url );


$config = <<<HTML
<?PHP

//System Configurations

\$config = array (

'version_id' => "9.6",

'home_title' => "DataLife Engine Nulled by M.I.D-Team",

'http_home_url' => "$url",

'charset' => "utf-8",

'admin_mail' => "$reg_email",

'description' => "Демонстрационная страница движка DataLife Engine",

'keywords' => "DataLife, Engine, CMS, PHP движок",

'date_adjust' => "0",

'site_offline' => "no",

'allow_alt_url' => "$alt_url",

'langs' => "Russian",

'skin' => "Default",

'allow_gzip' => "no",

'allow_admin_wysiwyg' => "no",

'allow_static_wysiwyg' => "no",

'news_number' => "10",

'smilies' => "wink,winked,smile,am,belay,feel,fellow,laughing,lol,love,no,recourse,request,sad,tongue,wassat,crying,what,bully,angry",

'timestamp_active' => "j-m-Y, H:i",

'news_sort' => "date",

'news_msort' => "DESC",

'hide_full_link' => "no",

'allow_site_wysiwyg' => "no",

'allow_comments' => "yes",

'comm_nummers' => "30",

'comm_msort' => "ASC",

'flood_time' => "30",

'auto_wrap' => "80",

'timestamp_comment' => "j F Y H:i",

'allow_comments_wysiwyg' => "no",

'allow_registration' => "yes",

'allow_cache' => "no",

'allow_votes' => "yes",

'allow_topnews' => "yes",

'allow_read_count' => "yes",

'allow_calendar' => "yes",

'allow_archives' => "yes",

'files_allow' => "yes",

'files_type' => "zip,rar,exe,doc,pdf,swf",

'files_count' => "yes",

'reg_group' => "4",

'registration_type' => "0",

'allow_sec_code' => "yes",

'allow_skin_change' => "yes",

'max_users' => "0",

'max_users_day' => "0",

'max_up_size' => "200",

'max_image_days' => "2",

'allow_watermark' => "yes",

'max_watermark' => "150",

'max_image' => "450",

'jpeg_quality' => "85",

'files_antileech' => "1",

'allow_banner' => "1",

'log_hash' => "0",

'show_sub_cats' => "1",

'tag_img_width' => "0",

'mail_metod' => "php",

'smtp_host' => "localhost",

'smtp_port' => "25",

'smtp_user' => "",

'smtp_pass' => "",

'mail_bcc' => "0",

'speedbar' => "1",

'safe_xfield' => "0",

'extra_login' => "0",

'image_align' => "left",

'ip_control' => "1",

'cache_count' => "0",

'related_news' => "1",

'no_date' => "1",

'mail_news' => "1",

'mail_comments' => "1",

'admin_path' => "admin.php",

'rss_informer' => "1",

'allow_cmod' => "0",

'max_up_side' => "0",

'files_force' => "1",

'files_max_speed' => "0",

'short_rating' => "1",

'full_search' => "0",

'allow_multi_category' => "1",

'short_title' => "Демонстрационный сайт",

'allow_rss' => "1",

'rss_mtype' => "0",

'rss_number' => "10",

'rss_format' => "1",

'comments_maxlen' => "3000",

'offline_reason' => "Сайт находится на текущей реконструкции, после завершения всех работ сайт будет открыт.<br /><br />Приносим вам свои извинения за доставленные неудобства.",

'catalog_sort' => "date",

'catalog_msort' => "DESC",

'related_number' => "5",

'seo_type' => "2",

'max_moderation' => "0",

'allow_quick_wysiwyg' => "0",

'sec_addnews' => "1",

'mail_pm' => "1",

'allow_change_sort' => "1",

'registration_rules' => "1",

'allow_tags' => "1",

'allow_add_tags' => "1",

'allow_fixed' => "1",

'max_file_size' => "4096",

'max_file_count' => "0",

'allow_smartphone' => "1",

'allow_smart_images' => "0",

'allow_smart_video' => "0",

'allow_search_print' => "1",

'allow_search_link' => "1",

'allow_smart_format' => "1",

'thumb_dimming' => "0",

'thumb_gallery' => "1",

'max_comments_days' => "0",

'allow_combine' => "1",

'allow_subscribe' => "1",

'parse_links' => "0",

't_seite' => "0",

'comments_minlen' => "10",

'js_min' => "0",

'outlinetype' => "0",

'fast_search' => "1",

'login_log' => "0",

'allow_recaptcha' => "0",

'recaptcha_public_key' => "6LfoOroSAAAAAEg7PViyas0nRqCN9nIztKxWcDp_",

'recaptcha_private_key' => "6LfoOroSAAAAAMgMr_BTRMZy20PFir0iGT2OQYZJ",

'recaptcha_theme' => "clean",

'search_number' => "10",

'news_navigation' => "1",

'mail_additional' => "",

'smtp_mail' => "",

'seo_control' => "0",

'news_restricted' => "0",

'comments_restricted' => "0",

'auth_metod' => "0",

'comments_ajax' => "0",

'create_catalog' => "0",

'mobile_news' => "10",

'reg_question' => "0",

'smtp_helo' => "HELO",

'news_future' => "0",

'cache_type' => "0",

'memcache_server' => "localhost:11211",

'allow_comments_cache' => "1",

);

?>
HTML;

$dbhost = str_replace ('"', '\"', str_replace ("$", "\\$", $dbhost) );
$dbname = str_replace ('"', '\"', str_replace ("$", "\\$", $dbname) );
$dbuser = str_replace ('"', '\"', str_replace ("$", "\\$", $dbuser) );
$dbpasswd = str_replace ('"', '\"', str_replace ("$", "\\$", $dbpasswd) );
$dbprefix = str_replace ('"', '\"', str_replace ("$", "\\$", $dbprefix) );
$dbcollate = str_replace ('"', '\"', str_replace ("$", "\\$", $dbcollate) );

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$dbhost}"); 

define ("DBNAME", "{$dbname}");

define ("DBUSER", "{$dbuser}");

define ("DBPASS", "{$dbpasswd}");  

define ("PREFIX", "{$dbprefix}"); 

define ("COLLATE", "{$dbcollate}"); 

define ("USERPREFIX", "{$dbprefix}"); 

\$db = new db;
 
?>
HTML;


$video_config = <<<HTML
<?PHP

//Videoplayers Configurations

\$video_config = array (

'width' => "425",

'height' => "325",

'play' => "false",

'progressBarColor' => "0xFFFFFF",

'flv_watermark' => "1",

'tube_related' => "0",

'tube_dle' => "0",

'startframe' => "0",

'progressBarColor' => "0xFFFFFF",

'preview' => "1",

'buffer' => "3",

'autohide' => "0",

'flv_watermark_pos' => "left",

'flv_watermark_al' => "1",

'youtube_q' => "hd720",

'play' => "0",

'fullsizeview' => "2",

);

?>
HTML;

$con_file = fopen("engine/data/config.php", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, $config);
fclose($con_file);
@chmod("engine/data/config.php", 0666);

$con_file = fopen("engine/data/dbconfig.php", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/dbconfig.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);
@chmod("engine/data/dbconfig.php", 0666);

$con_file = fopen("engine/data/videoconfig.php", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/videoconfig.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, $video_config);
fclose($con_file);
@chmod("engine/data/videoconfig.php", 0666);

$con_file = fopen("engine/data/wordfilter.db.php", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/wordfilter.db.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/wordfilter.db.php", 0666);

$con_file = fopen("engine/data/xfields.txt", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/xfields.txt</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/xfields.txt", 0666);

$con_file = fopen("engine/data/xprofile.txt", "w+") or die("Извините, но невозможно создать файл <b>.engine/data/xprofile.txt</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, '');
fclose($con_file);
@chmod("engine/data/xprofile.txt", 0666);

@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/cache/system/informers.php');
@unlink(ENGINE_DIR.'/data/snap.db');

define ("PREFIX", $dbprefix);
define ("COLLATE", $dbcollate);

$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_category";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_category (
  `id` smallint(5) NOT NULL auto_increment,
  `parentid` smallint(5) NOT NULL default '0',
  `posi` smallint(5) NOT NULL default '1',
  `name` varchar(50) NOT NULL default '',
  `alt_name` varchar(50) NOT NULL default '',
  `icon` varchar(200) NOT NULL default '',
  `skin` varchar(50) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `keywords` text NOT NULL,
  `news_sort` varchar(10) NOT NULL default '',
  `news_msort` varchar(4) NOT NULL default '',
  `news_number` smallint(5) NOT NULL default '0',
  `short_tpl` varchar(40) NOT NULL default '',
  `full_tpl` varchar(40) NOT NULL default '',
  `metatitle` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_comments";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_comments (
  `id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(11) NOT NULL default '0',
  `user_id` mediumint(8) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `autor` varchar(40) NOT NULL default '',
  `email` varchar(40) NOT NULL default '',
  `text` text NOT NULL,
  `ip` varchar(16) NOT NULL default '',
  `is_register` tinyint(1) NOT NULL default '0',
  `approve` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  KEY `approve` (`approve`),
  FULLTEXT KEY `text` (`text`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_email";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_email (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL default '',
  `template` text NOT NULL,
  PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";


$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_flood";

$tableSchema[] = "CREATE TABLE  " . PREFIX . "_flood (
  `f_id` int(11) unsigned NOT NULL auto_increment,
  `ip` varchar(40) NOT NULL default '',
  `id` varchar(20) NOT NULL default '',
  `flag` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`f_id`),
  KEY `ip` (`ip`),
  KEY `id` (`id`),
  KEY `flag` (`flag`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_images";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_images (
  `id` int(10) unsigned NOT NULL auto_increment,
  `images` text NOT NULL,
  `news_id` int(10) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `author` (`author`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_logs";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_logs (
  `id` int(10) unsigned NOT NULL auto_increment,
  `news_id` int(10) NOT NULL default '0',
  `member` varchar(40) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `member` (`member`),
  KEY `ip` (`ip`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_vote";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_vote (
  `id` mediumint(8) NOT NULL auto_increment,
  `category` text NOT NULL,
  `vote_num` mediumint(8) NOT NULL default '0',
  `date` varchar(25) NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `body` text NOT NULL,
  `approve` tinyint(1) NOT NULL default '1',
  `start` varchar(15) NOT NULL default '',
  `end` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `approve` (`approve`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_vote_result";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_vote_result (
  `id` int(10) NOT NULL auto_increment,
  `ip` varchar(16) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `vote_id` mediumint(8) NOT NULL default '0',
  `answer` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `answer` (`answer`),
  KEY `vote_id` (`vote_id`),
  KEY `ip` (`ip`),
  KEY `name` (`name`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_lostdb";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_lostdb (
  `id` mediumint(8) NOT NULL auto_increment,
  `lostname` mediumint(8) NOT NULL default '0',
  `lostid` varchar( 40 ) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `lostid` (`lostid`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_pm";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_pm (
  `id` int(10) unsigned NOT NULL auto_increment,
  `subj` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `user` MEDIUMINT(8) NOT NULL default '0',
  `user_from` varchar(50) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  `pm_read` TINYINT(1) NOT NULL default '0',
  `folder` varchar(10) NOT NULL default '',
  `reply` tinyint(1) NOT NULL default '0',
  `sendid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `folder` (`folder`),
  KEY `user` (`user`),
  KEY `user_from` (`user_from`),
  KEY `pm_read` (`pm_read`)
  ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_post (
  `id` int(11) NOT NULL auto_increment,
  `autor` varchar(40) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `short_story` text NOT NULL,
  `full_story` text NOT NULL,
  `xfields` text NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `keywords` text NOT NULL,
  `category` varchar(200) NOT NULL default '0',
  `alt_name` varchar(200) NOT NULL default '',
  `comm_num` mediumint(8) unsigned NOT NULL default '0',
  `allow_comm` tinyint(1) NOT NULL default '1',
  `allow_main` tinyint(1) unsigned NOT NULL default '1',
  `approve` tinyint(1) NOT NULL default '0',
  `fixed` tinyint(1) NOT NULL default '0',
  `allow_br` tinyint(1) NOT NULL default '1',
  `symbol` varchar(3) NOT NULL default '',
  `tags` VARCHAR(255) NOT NULL default '',
  `metatitle` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `autor` (`autor`),
  KEY `alt_name` (`alt_name`),
  KEY `category` (`category`),
  KEY `approve` (`approve`),
  KEY `allow_main` (`allow_main`),
  KEY `date` (`date`),
  KEY `symbol` (`symbol`),
  KEY `comm_num` (`comm_num`),
  KEY `tags` (`tags`),
  KEY `fixed` (`fixed`),
  FULLTEXT KEY `short_story` (`short_story`,`full_story`,`xfields`,`title`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post_extras";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_post_extras (
  `eid` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL DEFAULT '0',
  `news_read` mediumint(8) NOT NULL DEFAULT '0',
  `allow_rate` tinyint(1) NOT NULL DEFAULT '1',
  `rating` mediumint(8) NOT NULL DEFAULT '0',
  `vote_num` mediumint(8) NOT NULL DEFAULT '0',
  `votes` tinyint(1) NOT NULL DEFAULT '0',
  `view_edit` tinyint(1) NOT NULL DEFAULT '0',
  `disable_index` tinyint(1) NOT NULL DEFAULT '0',
  `related_ids` varchar(255) NOT NULL DEFAULT '',
  `access` varchar(150) NOT NULL DEFAULT '',
  `editdate` int(11) NOT NULL DEFAULT '0',
  `editor` varchar(40) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eid`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_static";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_static (
  `id` MEDIUMINT(8) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `template` text NOT NULL,
  `allow_br` tinyint(1) NOT NULL default '0',
  `allow_template` tinyint(1) NOT NULL default '0',
  `grouplevel` varchar(100) NOT NULL default 'all',
  `tpl` varchar(40) NOT NULL default '',
  `metadescr` varchar(200) NOT NULL default '',
  `metakeys` text NOT NULL,
  `views` mediumint(8) NOT NULL default '0',
  `template_folder` varchar(50) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  `metatitle` varchar(255) NOT NULL default '',
  `allow_count` tinyint(1) NOT NULL default '1',
  `sitemap` tinyint(1) NOT NULL default '1',
  `disable_index` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  FULLTEXT KEY `template` (`template`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_users";

$tableSchema[] = "CREATE TABLE " . PREFIX . "_users (
  `email` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `user_id` int(11) NOT NULL auto_increment,
  `news_num` mediumint(8) NOT NULL default '0',
  `comm_num` mediumint(8) NOT NULL default '0',
  `user_group` smallint(5) NOT NULL default '4',
  `lastdate` varchar(20) default NULL,
  `reg_date` varchar(20) default NULL,
  `banned` varchar(5) NOT NULL default '',
  `allow_mail` tinyint(1) NOT NULL default '1',
  `info` text NOT NULL,
  `signature` text NOT NULL,
  `foto` varchar(30) NOT NULL default '',
  `fullname` varchar(100) NOT NULL default '',
  `land` varchar(100) NOT NULL default '',
  `icq` varchar(20) NOT NULL default '',
  `favorites` text NOT NULL,
  `pm_all` smallint(5) NOT NULL default '0',
  `pm_unread` smallint(5) NOT NULL default '0',
  `time_limit` varchar(20) NOT NULL default '',
  `xfields` text NOT NULL,
  `allowed_ip` varchar(255) NOT NULL default '',
  `hash` varchar(32) NOT NULL default '',
  `logged_ip` varchar(16) NOT NULL default '',
  `restricted` TINYINT(1) NOT NULL default '0',
  `restricted_days` SMALLINT(4) NOT NULL default '0',
  `restricted_date` VARCHAR(15) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_banned";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_banned (
  `id` smallint(5) NOT NULL auto_increment,
  `users_id` mediumint(8) NOT NULL default '0',
  `descr` text NOT NULL,
  `date` varchar(15) NOT NULL default '',
  `days` smallint(4) NOT NULL default '0',
  `ip` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`users_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_files";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_files (
  `id`  MEDIUMINT(8) NOT NULL auto_increment,
  `news_id` int(10) NOT NULL default '0',
  `name` varchar(250) NOT NULL default '',
  `onserver` varchar(250) NOT NULL default '',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(15) NOT NULL default '',
  `dcount` smallint(5) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_usergroups";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_usergroups (
  `id` smallint(5) NOT NULL auto_increment,
  `group_name` varchar(32) NOT NULL default '',
  `allow_cats` text NOT NULL,
  `allow_adds` tinyint(1) NOT NULL default '1',
  `cat_add` text NOT NULL,
  `allow_admin` tinyint(1) NOT NULL default '0',
  `allow_addc` tinyint(1) NOT NULL default '0',
  `allow_editc` tinyint(1) NOT NULL default '0',
  `allow_delc` tinyint(1) NOT NULL default '0',
  `edit_allc` tinyint(1) NOT NULL default '0',
  `del_allc` tinyint(1) NOT NULL default '0',
  `moderation` tinyint(1) NOT NULL default '0',
  `allow_all_edit` tinyint(1) NOT NULL default '0',
  `allow_edit` tinyint(1) NOT NULL default '0',
  `allow_pm` tinyint(1) NOT NULL default '0',
  `max_pm` smallint(5) NOT NULL default '0',
  `max_foto` VARCHAR(10) NOT NULL default '',
  `allow_files` tinyint(1) NOT NULL default '0',
  `allow_hide` tinyint(1) NOT NULL default '1',
  `allow_short` tinyint(1) NOT NULL default '0',
  `time_limit` tinyint(1) NOT NULL default '0',
  `rid` smallint(5) NOT NULL default '0',
  `allow_fixed` tinyint(1) NOT NULL default '0',
  `allow_feed`  tinyint(1) NOT NULL default '1',
  `allow_search`  tinyint(1) NOT NULL default '1',
  `allow_poll`  tinyint(1) NOT NULL default '1',
  `allow_main`  tinyint(1) NOT NULL default '1',
  `captcha`  tinyint(1) NOT NULL default '0',
  `icon` varchar(200) NOT NULL default '',
  `allow_modc`  tinyint(1) NOT NULL default '0',
  `allow_rating` tinyint(1) NOT NULL default '1',
  `allow_offline` tinyint(1) NOT NULL default '0',
  `allow_image_upload` tinyint(1) NOT NULL default '0',
  `allow_file_upload` tinyint(1) NOT NULL default '0',
  `allow_signature` tinyint(1) NOT NULL default '0',
  `allow_url` tinyint(1) NOT NULL default '1',
  `news_sec_code` tinyint(1) NOT NULL default '1',
  `allow_image` tinyint(1) NOT NULL default '0',
  `max_signature` SMALLINT(6) NOT NULL default '0',
  `max_info` SMALLINT(6) NOT NULL default '0',
  `admin_addnews` tinyint(1) NOT NULL default '0',
  `admin_editnews` tinyint(1) NOT NULL default '0',
  `admin_comments` tinyint(1) NOT NULL default '0',
  `admin_categories` tinyint(1) NOT NULL default '0',
  `admin_editusers` tinyint(1) NOT NULL default '0',
  `admin_wordfilter` tinyint(1) NOT NULL default '0',
  `admin_xfields` tinyint(1) NOT NULL default '0',
  `admin_userfields` tinyint(1) NOT NULL default '0',
  `admin_static` tinyint(1) NOT NULL default '0',
  `admin_editvote` tinyint(1) NOT NULL default '0',
  `admin_newsletter` tinyint(1) NOT NULL default '0',
  `admin_blockip` tinyint(1) NOT NULL default '0',
  `admin_banners` tinyint(1) NOT NULL default '0',
  `admin_rss` tinyint(1) NOT NULL default '0',
  `admin_iptools` tinyint(1) NOT NULL default '0',
  `admin_rssinform` tinyint(1) NOT NULL default '0',
  `admin_googlemap` tinyint(1) NOT NULL default '0',
  `allow_html` tinyint(1) NOT NULL default '1',
  `group_prefix` text NOT NULL,
  `group_suffix` text NOT NULL,
  `allow_subscribe` tinyint(1) NOT NULL default '0',
  `allow_image_size` tinyint(1) NOT NULL default '0',
  `cat_allow_addnews` text NOT NULL,
  `flood_news` smallint(6) NOT NULL default '0',
  `max_day_news` smallint(6) NOT NULL default '0',
  `force_leech` tinyint(1) NOT NULL default '0',
  `edit_limit` smallint(6) NOT NULL default '0',
  `captcha_pm` tinyint(1) NOT NULL default '0',
  `max_pm_day` smallint(6) NOT NULL default '0',
  `max_mail_day` smallint(6) NOT NULL default '0',
  `admin_tagscloud` tinyint(1) NOT NULL default '0',
  `allow_vote` tinyint(1) NOT NULL default '0',
  `admin_complaint` tinyint(1) NOT NULL default '0',
  `news_question` tinyint(1) NOT NULL default '0',
  `comments_question` tinyint(1) NOT NULL default '0',
  `max_comment_day` smallint(6) NOT NULL default '0',
  `max_images` smallint(6) NOT NULL default '0',
  `max_files` smallint(6) NOT NULL default '0',
  `disable_news_captcha` smallint(6) NOT NULL default '0',
  `disable_comments_captcha` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_poll";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `frage` varchar(200) NOT NULL default '',
  `body` text NOT NULL,
  `votes` mediumint(8) NOT NULL default '0',
  `multiple` tinyint(1) NOT NULL default '0',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_poll_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_poll_log (
  `id` int(10) unsigned NOT NULL auto_increment,
  `news_id` int(10) unsigned NOT NULL default '0',
  `member` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`,`member`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_banners";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_banners (
  `id` smallint(5) NOT NULL auto_increment,
  `banner_tag` varchar(40) NOT NULL default '',
  `descr` varchar(200) NOT NULL default '',
  `code` text NOT NULL,
  `approve` tinyint(1) NOT NULL default '0',
  `short_place` tinyint(1) NOT NULL default '0',
  `bstick` tinyint(1) NOT NULL default '0',
  `main` tinyint(1) NOT NULL default '0',
  `category` VARCHAR(255) NOT NULL default '',
  `grouplevel` varchar(100) NOT NULL default 'all',
  `start` varchar(15) NOT NULL default '',
  `end` varchar(15) NOT NULL default '',
  `fpage` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rss";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rss (
  `id` smallint(5) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `allow_main` tinyint(1) NOT NULL default '0',
  `allow_rating` tinyint(1) NOT NULL default '0',
  `allow_comm` tinyint(1) NOT NULL default '0',
  `text_type` tinyint(1) NOT NULL default '0',
  `date` tinyint(1) NOT NULL default '0',
  `search` text NOT NULL,
  `max_news` tinyint(3) NOT NULL default '0',
  `cookie` text NOT NULL,
  `category` smallint(5) NOT NULL default '0',
  `lastdate` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_views";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_views (
  `id` mediumint(8) NOT NULL auto_increment,
  `news_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";


$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_rssinform";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_rssinform (
  `id` smallint(5) NOT NULL auto_increment,
  `tag` varchar(40) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `category` varchar(200) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `template` varchar(40) NOT NULL default '',
  `news_max` smallint(5) NOT NULL default '0',
  `tmax` smallint(5) NOT NULL default '0',
  `dmax` smallint(5) NOT NULL default '0',
  `approve` tinyint(1) NOT NULL default '1',
  `rss_date_format` VARCHAR(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_notice";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_notice (
  `id` mediumint(8) NOT NULL auto_increment,
  `user_id` mediumint(8) NOT NULL default '0',
  `notice` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_static_files";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_static_files (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `static_id` mediumint(8) NOT NULL default '0',
  `author` varchar(40) NOT NULL default '',
  `date` varchar(50) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `onserver` varchar(255) NOT NULL default '',
  `dcount` smallint(5) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `static_id` (`static_id`),
  KEY `onserver` (`onserver`),
  KEY `author` (`author`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_tags";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_tags (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `tag` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_post_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_post_log (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `expires` varchar(15) NOT NULL default '',
  `action` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `expires` (`expires`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_admin_sections";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_admin_sections (
  `id` mediumint(8) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `descr` varchar(255) NOT NULL default '',
  `icon` varchar(255) NOT NULL default '',
  `allow_groups` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_subscribe";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_subscribe (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `email`  varchar(50) NOT NULL default '',
  `news_id` int(11) NOT NULL default '0',
  `hash` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `user_id` (`user_id`) 
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_sendlog";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_sendlog (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) NOT NULL DEFAULT '',
  `date` varchar(20) NOT NULL DEFAULT '',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `date` (`date`),
  KEY `flag` (`flag`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_login_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_login_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `count` smallint(6) NOT NULL DEFAULT '0',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_mail_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_mail_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `hash` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_complaint";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_complaint (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `p_id` int(10) NOT NULL DEFAULT '0',
  `c_id` int(10) NOT NULL DEFAULT '0',
  `n_id` int(10) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `from` varchar(40) NOT NULL DEFAULT '',
  `to` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `c_id` (`c_id`),
  KEY `p_id` (`p_id`),
  KEY `n_id` (`n_id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_ignore_list";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_ignore_list (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL default '0',
  `user_from` VARCHAR(50) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`user`),
  KEY `user_from` (`user_from`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_admin_logs";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_admin_logs (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(16) NOT NULL DEFAULT '',
  `action` int(11) NOT NULL DEFAULT '0',
  `extras` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_question";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_question (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "INSERT INTO " . PREFIX . "_rssinform VALUES (1, 'dle', 'Новости от M.I.D Team', '0', 'http://www.mid-team.ws/rss.xml', 'informer', 3, 0, 200, 1, 'j F Y H:i')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (1, 'Администраторы', 'all', 1, 'all', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_1.gif', 0, 1, 1, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,1,'<b><span style=\"color:red\">','</span></b>',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (2, 'Главные редакторы', 'all', 1, 'all', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 50, 101, 1, 1, 1, 0, 2, 1, 1, 1, 1, 1, 0, '{THEME}/images/icon_2.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (3, 'Журналисты', 'all', 1, 'all', 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 50, 101, 1, 1, 1, 0, 3, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_3.gif', 0, 1, 0, 1, 1, 1, 1, 0, 1,500,1000,1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,1,'all', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (4, 'Посетители', 'all', 1, 'all', 0, 1, 1, 1, 0, 0, 0, 0, 0, 1, 20, 101, 1, 1, 1, 0, 4, 0, 1, 1, 1, 1, 0, '{THEME}/images/icon_4.gif', 0, 1, 0, 1, 0, 1, 1, 1, 0,500,1000,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,1,'','',1,0,'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0)";
$tableSchema[] = "INSERT INTO " . PREFIX . "_usergroups VALUES (5, 'Гости', 'all', 0, 'all', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 5, 0, 1, 1, 1, 0, 1, '{THEME}/images/icon_5.gif', 0, 1, 0, 0, 0, 0, 1, 1, 0,1,1,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0,'','',0,0,'all', 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0)";

$tableSchema[] = "INSERT INTO " . PREFIX . "_rss VALUES (1, 'http://dle-news.ru/rss.xml', 'Официальный сайт DataLife Engine', 1, 1, 1, 1, 1, '<div id=\"news-id-{skip}\">{get}</div><br /><br /></td>', 5, '', 1, '')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (1, 'reg_mail', '{%username%},\r\n\r\nЭто письмо отправлено с сайта $url\r\n\r\nВы получили это письмо, так как этот e-mail адрес был использован при регистрации на сайте. Если Вы не регистрировались на этом сайте, просто проигнорируйте это письмо и удалите его. Вы больше не получите такого письма.\r\n\r\n------------------------------------------------\r\nВаш логин и пароль на сайте:\r\n------------------------------------------------\r\n\r\nЛогин: {%username%}\r\nПароль: {%password%}\r\n\r\n------------------------------------------------\r\nИнструкция по активации\r\n------------------------------------------------\r\n\r\nБлагодарим Вас за регистрацию.\r\nМы требуем от Вас подтверждения Вашей регистрации, для проверки того, что введённый Вами e-mail адрес - реальный. Это требуется для защиты от нежелательных злоупотреблений и спама.\r\n\r\nДля активации Вашего аккаунта, зайдите по следующей ссылке:\r\n\r\n{%validationlink%}\r\n\r\nЕсли и при этих действиях ничего не получилось, возможно Ваш аккаунт удалён. В этом случае, обратитесь к Администратору, для разрешения проблемы.\r\n\r\nС уважением,\r\n\r\nАдминистрация $url.')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (2, 'feed_mail', '{%username_to%},\r\n\r\nДанное письмо вам отправил {%username_from%} с сайта $url\r\n\r\n------------------------------------------------\r\nТекст сообщения\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nIP адрес отправителя: {%ip%}\r\n\r\n------------------------------------------------\r\nПомните, что администрация сайта не несет ответственности за содержание данного письма\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (3, 'lost_mail', 'Уважаемый {%username%},\r\n\r\nВы сделали запрос на получение забытого пароля на сайте $url Однако в целях безопасности все пароли хранятся в зашифрованном виде, поэтому мы не можем сообщить вам ваш старый пароль, поэтому если вы хотите сгенерировать новый пароль, зайдите по следующей ссылке: \r\n\r\n{%lostlink%}\r\n\r\nЕсли вы не делали запроса для получения пароля, то просто удалите данное письмо, ваш пароль храниться в надежном месте, и недоступен посторонним лицам.\r\n\r\nIP адрес отправителя: {%ip%}\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (4, 'new_news', 'Уважаемый администратор,\r\n\r\nуведомляем вас о том, что на сайт  $url была добавлена новость, которая в данный момент ожидает модерации.\r\n\r\n------------------------------------------------\r\nКраткая информация о новости\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nЗаголовок новости: {%title%}\r\nКатегория: {%category%}\r\nДата добавления: {%date%}\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (5, 'comments', 'Уважаемый {%username_to%},\r\n\r\nуведомляем вас о том, что на сайт  $url был добавлен комментарий к новости, на которую вы были подписаны.\r\n\r\n------------------------------------------------\r\nКраткая информация о комментарии\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nДата добавления: {%date%}\r\nСсылка на новость: {%link%}\r\n\r\n------------------------------------------------\r\nТекст комментария\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\n------------------------------------------------\r\n\r\nЕсли вы не хотите больше получать уведомлений о новых комментариях к данной новости, то проследуйте по данной ссылке: {%unsubscribe%}\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (6, 'pm', 'Уважаемый {%username%},\r\n\r\nуведомляем вас о том, что на сайте  $url вам было отправлено персональное сообщение.\r\n\r\n------------------------------------------------\r\nКраткая информация о сообщении\r\n------------------------------------------------\r\n\r\nОтправитель: {%fromusername%}\r\nДата  получения: {%date%}\r\nЗаголовок: {%title%}\r\n\r\n------------------------------------------------\r\nТекст сообщения\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_category (name, alt_name, keywords) values ('Информация', 'main', '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_banners (banner_tag, descr, code, approve, short_place, bstick, main, category) values ('header', 'Верхний баннер', '<div align=\"center\"><a href=\"http://www.mid-team.ws/\" title=\"M.I.D Team\" target=\"_blank\"><img src=\"{$url}templates/Default/images/_banner_.gif\" style=\"border: none;\" alt=\"\" /></a></div>', 1, 0, 0, 0, 0)";

$add_time = time();
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_static (`name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`, `date`) VALUES ('dle-rules-page', 'Общие правила на сайте', '<b>Общие правила поведения на сайте:</b><br /><br />Начнем с того, что на сайте общаются сотни людей, разных религий и взглядов, и все они являются полноправными посетителями нашего сайта, поэтому если мы хотим чтобы это сообщество людей функционировало нам и необходимы правила. Мы настоятельно рекомендуем прочитать настоящие правила, это займет у вас всего минут пять, но сбережет нам и вам время и поможет сделать сайт более интересным и организованным.<br /><br />Начнем с того, что на нашем сайте нужно вести себя уважительно ко всем посетителям сайта. Не надо оскорблений по отношению к участникам, это всегда лишнее. Если есть претензии - обращайтесь к Админам или Модераторам (воспользуйтесь личными сообщениями). Оскорбление других посетителей считается у нас одним из самых тяжких нарушений и строго наказывается администрацией. <b>У нас строго запрещен расизм, религиозные и политические высказывания.</b> Заранее благодарим вас за понимание и за желание сделать наш сайт более вежливым и дружелюбным.<br /><br /><b>На сайте строго запрещено:</b> <br /><br />- сообщения, не относящиеся к содержанию статьи или к контексту обсуждения<br />- оскорбление и угрозы в адрес посетителей сайта<br />- в комментариях запрещаются выражения, содержащие ненормативную лексику, унижающие человеческое достоинство, разжигающие межнациональную рознь<br />- спам, а также реклама любых товаров и услуг, иных ресурсов, СМИ или событий, не относящихся к контексту обсуждения статьи<br /><br />Давайте будем уважать друг друга и сайт, на который Вы и другие читатели приходят пообщаться и высказать свои мысли. Администрация сайта оставляет за собой право удалять комментарии или часть комментариев, если они не соответствуют данным требованиям.<br /><br />При нарушении правил вам может быть дано <b>предупреждение</b>. В некоторых случаях может быть дан бан <b>без предупреждений</b>. По вопросам снятия бана писать администратору.<br /><br /><b>Оскорбление</b> администраторов или модераторов также караются <b>баном</b> - уважайте чужой труд.<br /><br /><div align=\"center\">{ACCEPT-DECLINE}</div>', 1, 1, 'all', '', 'Общие правила', 'Общие правила', 0, '', '{$add_time}')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_users (name, password, email, reg_date, lastdate, user_group, news_num, info, signature, favorites, xfields) values ('$reg_username', '$reg_password', '$reg_email', '$add_time', '$add_time', '1', '4', '', '', '', '')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_vote (category, vote_num, date, title, body) VALUES ('all', '0', '$thistime', 'Оцените работу движка', 'Лучший из новостных<br />Неплохой движок<br />Устраивает ... но ...<br />Встречал и получше<br />Совсем не понравился')";

$title = "Добро пожаловать";
$short_story = "<div align=\"center\"><img src=\"".$url."uploads/boxsmall.jpg\" alt=\"\" /></div>Добро пожаловать на демонстрационную страницу движка DataLife Engine. DataLife Engine это многопользовательский новостной движок, обладающий большими функциональными возможностями. Движок предназначен в первую очередь для создание новостных блогов и сайтов с большим информационным контекстом. Однако он имеет большое количество настроек, которые позволяют использовать его практически для любых целей. Движок может быть интегрирован практически в любой существующий дизайн, и не имеет никаких ограничений по созданию шаблонов для него. Еще одной ключевой особенностью DataLife Engine является низкая нагрузка на системные ресурсы, Даже при очень большой аудитории сайта нагрузка не сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации. Движок оптимизирован под поисковые системы. Обо всех функциональных особенностях вы сможете прочитать на <a href=\"http://www.mid-team.ws/\" title=\"M.I.D Team\" target=\"_blank\">нашей странице</a>.<br /><br />Обсуждение скрипта по всем вопросам ведется <a href=\"http://www.mid-team.ws/forum/\" title=\"M.I.D Team Forum\" target=\"_blank\">здесь</a>. Так же там Вы сможете получить оперативную помощь.";
$full_story = "";

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('1', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post1', '1', '1', '1', 'по, новости')";

$title = "Приобретение и оплата скрипта";
$short_story = "Уважаемые вебмастера хотим для вас сделать небольшое дополнение. Прежде чем обратиться с каким-либо вопросом в службу поддержки скрипта, убедитесь что вы тщательно прочитали документацию по скрипту и не нашли там для вас необходимого ответа. Мы оставляем за собой право игнорировать вопросы, поступившие к нам от пользователей, использующих некоммерческую версию скрипта или не оплативших лицензию, включающую в себя службу технической поддержки. Вы можете приобрести один из двух типов лицензии на DataLife Engine по вашему желанию:<br /><br />- <b>Базовая лицензия.</b> Стоимость данной лицензии составляет: <span style=\"color:red\">59$</span>. При приобретении данной лицензии вы также получаете возможность получения бесплатно новых версий скрипта в течении <b>одного года</b>.<br /><br />- <b>Расширенная лицензия.</b> Стоимость данной лицензии составляет: <span style=\"color:red\">78$</span>. При приобретении данной лицензии вы получаете все что входит в базовую лицензию, а также дополнительно входит служба технической поддержки скрипта и разрешение на снятие копирайтов на скрипт с пользовательской части (видимой для обычных посетителей сайта).<br /><br /><b>Срок действия лицензии</b> составляет <span style=\"color:#FF0000\">1 год</span>, в течении которого вы бесплатно будете получать все последующие версии скрипта и обновления, а в случае приобретения расширенной лицензии, и тех. поддержку. После окончания срока лицензии вы можете ее продлить, либо использовать пожизненно бесплатно актуальную на тот момент времени версию скрипта. В случае если вы захотите продлить лицензию для получения новых версий скрипта, то стоимость продления лицензии на год, составляет <span style=\"color:red\">39$</span><br /><br /><b>Как оплатить скрипт вы можете прочитать на</b> <a href=\"http://dle-news.ru/price.html\" target=\"_blank\">http://dle-news.ru/price.html</a><br /><br />Помните что лицензия выдается только на один домен (проект) и не может использоваться на других сайтах, а также запрещена передача вашего файла лицензии третьим лицам.<br /><br /><b>С уважением,<br /><br />SoftNews Media Group</b>";

$add_time = time()-20;
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('2', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post2', '1', '1', '1', 'по, новости')";

$title = "Шаблоны для DataLife Engine";
$short_story = "<br /><div align=\"center\"><a href=\"http://www.dletemplates.com/\" target=\"_blank\"><img src=\"".$url."uploads/dlelogo.gif\" alt=\"\" /></a></div><br />Совместно с нашими партнерами <a href=\"http://www.dletemplates.com/\" target=\"_blank\">www.dletemplates.com</a> мы рады предложить вам  также высококачественные шаблоны, специально подготовленные для использования под управлением DataLife Engine. Предлагаемые шаблоны созданы на высоком качественном уровне опытными дизайнерами и программистами. Каждый из предлагаемых шаблонов полностью настроен и готов к использованию сразу же после его установки на сервер. Помимо того что все шаблоны уже подготовлены к использованию на портале, вы получаете также все исходные материалы, которые были использованы при создании шаблона.<br /><br />Если вы еще только раздумываете как будет выглядеть ваш сайт и желаете воспользоваться услугами профессионалов по вполне разумным ценам, то мы рекомендуем вам обратиться к нашим партнерам <a href=\"http://www.dletemplates.com/\" target=\"_blank\">www.dletemplates.com</a>, которые в сжатые строки создадут для вас персональный макет вашего сайта, а также подготовят его непосредственно к работе с движком. Также на сайте оказываются услуги по интеграции уже готового Вашего макета в движок.";

$add_time = time()-50;
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('3', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post3', '1', '1', '1', 'по, шаблоны')";

$title = "Осуществление технической поддержки скрипта";
$short_story = "<b>Техническая поддержка скрипта</b> осуществляется силами <a href=\"http://www.mid-team.ws/forum/\" title=\"M.I.D Team Forum\" target=\"_blank\">форума поддержки</a>, а также по E-Mail. По мере поступления возникших у вас вопросов мы стараемся ответить на все ваши вопросы, но в связи с большим количеством посетителей, это не всегда является возможным. Поэтому введена дополнительная услуга платной поддержки скрипта. Стоимость данной услуги составляет дополнительно <!--colorstart:#FF0000--><span style=\"color:#FF0000\"><!--/colorstart-->19 $<!--colorend--></span><!--/colorend--> одноразово на весь срок действия лицензии.<br /><br /><b>Услуги по дополнительной поддержки скрипта включают в себя:</b><br /><br />1. Приоритетное получение ответа на вопросы, которые задают пользователи впервые столкнувшиеся со скриптом и естественно не знающие всех нюансов работы скрипта. В компетенции службы поддержки находится только помощь только по непосредственным сбоям самого скрипта, в случае если причиной некорректной работы скрипта явился ваш шаблон, не соответствующий требованиям скрипта, то в поддержке вам может быть отказано.<br /><br />2. Также вы получаете возможность одноразовой установки скрипта вам на сервер, включая настройку его до полной работоспособности с учетом текущих настроек сервера (иногда нужно верно отключить поддержку ЧПУ, включение специальных директив для Русского Апача, для верной загрузки картинок и прочее...).<br /><br />3. Вы получаете консультационную поддержку по работе со структурой скрипта, например у вас есть желание добавить небольшие изменения в скрипт для более удобной работы для вас, вы сможете сэкономить время на поиске нужного куска кода просто спросив у нас. Вам будет предоставлена консультация где это копать и как вообще лучше реализовать поставленную задачу. (Внимание мы не пишем за вас дополнительные модули, а только помогаем вам лучше разобраться со структурой скрипта, поэтому всегда задавайте вопросы по существу, вопросы типа: \"как мне сделать такую фишку\" могут быть проигнорированы службой поддержки).<br /><br />4. Еще одна из часто возникающих проблем это некорректное обновление скрипта, например во время обновления произошел сбой сервера, часть новых данных была внесена в базу и настройки, часть нет, в итоге вы получаете нерабочий скрипт, со всеми вытекающими последствиями. В данном случае для вас будет проведена ручная коррекция поврежденной структуры базы данных.<br /><br />В случае если вы не являетесь подписчиком дополнительной службы поддержки, ваши вопросы могут быть проигнорированы и оставлены без ответа.<br /><br /><b>С уважением,<br /><br />SoftNews Media Group</b>";

$add_time = time()-70;
$thistime = date ("Y-m-d H:i:s", $add_time);

$tableSchema[] = "INSERT INTO " . PREFIX . "_post (id, date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, tags) values ('4', '$thistime', '$reg_username', '$short_story', '$full_story', '', '$title', '', '1', 'post4', '1', '1', '1', '')";

$tableSchema[] = "INSERT INTO " . PREFIX . "_post_extras (news_id, user_id) values ('1', '1'), ('2', '1'), ('3', '1'), ('4', '1')";


$tableSchema[] = "INSERT INTO " . PREFIX . "_tags (news_id, tag) values ('1', 'по'), ('2', 'по'), ('3', 'по'), ('1', 'новости'), ('2', 'новости'), ('3', 'шаблоны')";

include ENGINE_DIR.'/classes/mysql.php';
include ENGINE_DIR.'/data/dbconfig.php';

      foreach($tableSchema as $table) {

        $db->query($table);

      }

echoheader("", "");

echo <<<HTML
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Установка успешно завершена</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;"><br>Поздравляем Вас, DataLife Engine был успешно установлен на Ваш сервер. Вы можете просмотреть теперь главную <a href="index.php">страницу вашего сайта</a>  и посмотреть возможности скрипта. Либо Вы можете <a href="admin.php">зайти</a> в панель управления DataLife Engine и изменить другие настройки системы. 
<br><br><font color="red">Внимание: при установки скрипта создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки системы, поэтому после успешной установки удалите файл <b>install.php</b> во избежание повторной установки скрипта!</font><br><br>
Приятной Вам работы<br><br>
SoftNews Media Group<br><br></td>
    </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div>
HTML;
 
}
else {

if (@file_exists(ENGINE_DIR.'/data/config.php')) { 
echoheader("", "");
echo <<<HTML
<form method="post" action="">
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Установка скрипта автоматически заблокирована</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">Внимание, на сервере обнаружена уже установленная копия DataLife Engine. Если вы хотите еще раз произвести установку скрипта, то вам необходимо вручную удалить файл <b>/engine/data/config.php</b>, используя FTP протокол.<br /><br /></td>
    </tr>
    <tr>
        <td style="padding:2px;"><input class=buttons type=submit value="&nbsp;&nbsp;Обновить&nbsp;&nbsp;"></td>
    </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;

echofooter();

die ();
}

$_SESSION['dle_install'] = 1;

// ********************************************************************************
// Приветствие
// ********************************************************************************
echoheader("", "");
echo <<<HTML
<form method="post" action="">
<div style="padding-top:5px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">Мастер установки скрипта</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">Добро пожаловать в мастер установки DataLife Engine. Данный мастер поможет вам установить скрипт всего за пару минут. Однако, не смотря на это, мы настоятельно рекомендуем Вам ознакомиться с документацией по работе с движком, а также по его установке, которая поставляется вместе со скриптом.<br><br>
Прежде чем начать установку убедитесь, что все файлы дистрибутива загружены на сервер, а также выставлены необходимые права доступа для папок и файлов.<br><br>
Обращаем Ваше внимание на то, что движок поддерживает работу с ЧПУ, а для этого необходимо, чтобы был установлен модуль <b>modrewrite</b> и его использование было разрешено. Eсли вы хотите отключить эту возможность, то удалите файл <b>.htaccess</b> в корневой папке и в процессе установки скрипта отключите поддержку этой функции.<br><br>
<font color="red">Внимание: при установке скрипта создается структура базы данных, создается аккаунт администратора, а также прописываются основные настройки системы, поэтому после успешной установки удалите файл <b>install.php</b> во избежание повторной установки скрипта!</font><br><br>
Приятной Вам работы,<br><br>
SoftNews Media Group</td>
    </tr>
    <tr>
        <td style="padding:2px;"><input type=hidden name=action value="eula"><input class=buttons type=submit value="&nbsp;&nbsp;Начать установку&nbsp;&nbsp;"></td>
    </tr>
</table>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;
}


echofooter();
?>