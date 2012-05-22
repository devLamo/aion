<?PHP
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
 Файл: help.php
-----------------------------------------------------
 Назначение: помощь
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$help_sections = array();
$section = totranslit($_REQUEST['section']);

require_once (ROOT_DIR . '/language/' . $config['langs'] . '/help.lng');


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 Загрузка секции
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if($section){

if(!isset($help_sections["$section"])){ die("Невозможно найти справочную секцию <b>$section</b>"); }

echo"<HTML>
	<meta content=\"text/html; charset={$config['charset']}\" http-equiv=\"content-type\" />
    <style type=\"text/css\">
	<!--
	a:active,a:visited,a:link {color: #446488; text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt;}
	a:hover {color: #00004F; text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; }
	.code {
		font-family : Verdana, Courier;
		font-size : 11px;
		border: 1px solid #BBCDDB;
		margin:10px;
		padding:4px;
		background:#FBFFFF;
	}
    h1 {
		background-color : #C4BFB9;
		border : #000000 1px solid;

		color : #6B6256;
		font-family : Tahoma, Verdana, Arial, Helvetica, sans-serif;
		font-size : 11px;
		font-weight : bold;
		padding:4px;
		text-decoration : none;
	}
	BODY, TD, TR {text-align:justify ;padding: 0; leftMargin: 0; topMargin: 0; text-decoration: none; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 8pt; cursor: default;}
	-->
	</style>
<title>{$lang['skin_title']}</title>
<body><div width=\"97%\">". $help_sections["$section"] ."</div></body></html>";
}
else{
if($member_id['user_group'] > 1){ msg("error", $lang['index_denied'], $lang['index_denied']); }

echoheader("", "");
echo"<style type=\"text/css\">
	<!--
	.code {
		font-family : Verdana, Courier;
		font-size : 11px;
		border: 1px solid #BBCDDB;
		margin:10px;
		padding:4px;
		background:#FBFFFF;
	}
	-->
	</style>";


echo <<<HTML
<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
HTML;

foreach($help_sections as $help_section){
echo "$help_section<br /><br />";
}

echo <<<HTML
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
echofooter();
}
?>