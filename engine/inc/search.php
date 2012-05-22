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
 Файл: search.php
-----------------------------------------------------
 Назначение: поиск и замена текста в базе данных
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if($member_id['user_group'] != 1){ msg("error", $lang['addnews_denied'], $lang['db_denied']); }

if ($_POST['action'] == "replace") {

	if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {

		  die("Hacking attempt! User not found");

	}

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) {

		$_POST['find'] = stripslashes( $_POST['find'] );
		$_POST['replace'] = stripslashes( $_POST['replace'] );

	} 

	$find = $db->safesql(addslashes(trim($_POST['find'])));
	$replace = $db->safesql(addslashes(trim($_POST['replace'])));

	if ($find == "" OR !count($_POST['table'])) msg("error",$lang['addnews_error'],$lang['vote_alert'], "javascript:history.go(-1)");

	if (in_array("news", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_post` SET `short_story`=REPLACE(`short_story`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `full_story`=REPLACE(`full_story`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `xfields`=REPLACE(`xfields`,'$find','$replace')");

	}

	if (in_array("comments", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_comments` SET `text`=REPLACE(`text`,'$find','$replace')");
	}

	if (in_array("pm", $_POST['table'])) {
		$db->query("UPDATE `" . USERPREFIX . "_pm` SET `text`=REPLACE(`text`,'$find','$replace')");
	}

	if (in_array("static", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_static` SET `template`=REPLACE(`template`,'$find','$replace')");

	}

	if (in_array("tags", $_POST['table'])) {
		$db->query("UPDATE `" . PREFIX . "_tags` SET `tag`=REPLACE(`tag`,'$find','$replace')");
		$db->query("UPDATE `" . PREFIX . "_post` SET `tags`=REPLACE(`tags`,'$find','$replace')");
     }

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '58', '".htmlspecialchars("find: ".$find." replace: ".$replace, ENT_QUOTES)."')" );

	clear_cache ();
	msg("info", $lang['find_done_h'], $lang['find_done'], "?mod=search");

}


echoheader("", "");

echo <<<HTML
<form action="" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['find_main']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">{$lang['find_info']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:2px;" width="150" valign="top">{$lang['find_ftable']}</td>
        <td style="padding:2px;" width="100%"><select name="table[]" style="height:72px;" multiple>
		<option value="news" selected>{$lang['find_rnews']}</option><option value="comments" selected>{$lang['find_rcomms']}</option><option value="pm" selected>{$lang['find_rpm']}</option><option value="static" selected>{$lang['find_rstatic']}</option><option value="tags" selected>{$lang['find_rtags']}</option>
		</select></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:2px;" width="150" valign="top" nowrap>{$lang['find_ftext']}</td>
        <td style="padding:2px;" width="100%"><textarea class="edit bk" name="find" style="width:550px;height:200px;"></textarea></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:2px;" valign="top">{$lang['find_rtext']}</td>
        <td style="padding:2px;"><textarea class="edit bk" name="replace" style="width:550px;height:200px;"></textarea></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;" colspan="2"><input type="submit" class="btn btn-primary" value="&nbsp;&nbsp;{$lang['find_rstart']}&nbsp;&nbsp;"><input type="hidden" name="action" value="replace"><input type="hidden" name="user_hash" value="{$dle_login_hash}"></td>
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
?>