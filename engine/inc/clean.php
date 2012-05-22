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
 Файл: clean.php
-----------------------------------------------------
 Назначение: очистка и оптимизация базы данных
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if($member_id['user_group'] != 1){ msg("error", $lang['addnews_denied'], $lang['db_denied']); }

$db->query("SHOW TABLE STATUS FROM `".DBNAME."`");
			$mysql_size = 0;
			while ($r = $db->get_array()) {
			if (strpos($r['Name'], PREFIX."_") !== false)
			$mysql_size += $r['Data_length'] + $r['Index_length'] ;
			}
$db->free();

$lang['clean_all'] = str_replace ('{datenbank}', '<font color="red">'.formatsize($mysql_size).'</font>', $lang['clean_all']);

$js_array[] = "engine/skins/calendar.js";

echoheader("", "");

echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function start_clean ( step, size ){

	$("#status").html('{$lang['ajax_info']}');

	if (document.getElementById( 'f_date_c' )) {
		var date = document.getElementById( 'f_date_c' ).value;
	} else { var date = ''; }

	if (document.getElementById( 'next_button' )) {
		document.getElementById( 'next_button' ).disabled = true;
	}
	if (document.getElementById( 'skip_button' )) {
		document.getElementById( 'skip_button' ).disabled = true;
	}

	$.get("engine/ajax/clean.php", { step: step, date: date, size: size, user_hash: "{$dle_login_hash}" }, function(data){

		RunAjaxJS('main_box', data);

	});

	return false;
}
//-->
</script>
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
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
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['clean_title']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">
		<div id="main_box"><br />{$lang['clean_all']}<br /><br /><font color="red"><span id="status"></span></font><br /><br />
		<input id = "next_button" onclick="start_clean('1', '{$mysql_size}'); return false;" class="btn btn-success" style="width:100px;" type="button" value="{$lang['edit_next']}">
		</div>
		</td>
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


echofooter();
?>