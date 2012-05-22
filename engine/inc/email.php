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
 Файл: email.php
-----------------------------------------------------
 Назначение: шаблоны писем
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

if( $action == "save" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$find = array ("<", ">");
	$replace = array ("&lt;", "&gt;");
	
	$reg_mail_text = $db->safesql(str_replace( $find, $replace, $_POST['reg_mail_text'] ) );
	$feed_mail_text = $db->safesql(str_replace( $find, $replace, $_POST['feed_mail_text'] ) );
	$lost_mail_text = $db->safesql(str_replace( $find, $replace, $_POST['lost_mail_text'] ) );
	$new_news_text = $db->safesql(str_replace( $find, $replace, $_POST['new_news_text'] ) );
	$new_comments_text = $db->safesql(str_replace( $find, $replace, $_POST['new_comments_text'] ) );
	$new_pm_text = $db->safesql(str_replace( $find, $replace, $_POST['new_pm_text'] ) );
	
	$db->query( "UPDATE " . PREFIX . "_email set template='$reg_mail_text' where name='reg_mail'" );
	$db->query( "UPDATE " . PREFIX . "_email set template='$feed_mail_text' where name='feed_mail'" );
	$db->query( "UPDATE " . PREFIX . "_email set template='$lost_mail_text' where name='lost_mail'" );
	$db->query( "UPDATE " . PREFIX . "_email set template='$new_news_text' where name='new_news'" );
	$db->query( "UPDATE " . PREFIX . "_email set template='$new_comments_text' where name='comments'" );
	$db->query( "UPDATE " . PREFIX . "_email set template='$new_pm_text' where name='pm'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '31', '')" );
	
	msg( "info", $lang['mail_addok'], $lang['mail_addok_1'], "?mod=email" );

} else {
	
	echoheader( "home", $lang['db_info'] );
	
	$db->query( "SELECT name, template FROM " . PREFIX . "_email" );
	
	while ( $row = $db->get_row() ) {
		$$row['name'] = stripslashes( $row['template'] );
	}
	$db->free();
	
	echo <<<HTML
<form action="$PHP_SELF?mod=email&action=save" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_reg_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="reg_mail_text" class="bk">{$reg_mail}</textarea></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info_1']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_feed_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="feed_mail_text" class="bk">{$feed_mail}</textarea></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info_2']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_lost_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="lost_mail_text" class="bk">{$lost_mail}</textarea>
</td>
    </tr>
</table>


<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info_3']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_news_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="new_news_text" class="bk">{$new_news}</textarea>
</td>
    </tr>
</table>

<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info_4']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_comm_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="new_comments_text" class="bk">{$comments}</textarea>
</td>
    </tr>
</table>


<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mail_info_6']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['mail_pm_info']}</td>
    </tr>
    <tr>
        <td style="padding:2px;"><textarea rows="15" style="width:98%;" name="new_pm_text" class="bk">{$pm}</textarea>
<br /><br />&nbsp;&nbsp;<input type="submit" value="&nbsp;&nbsp;{$lang['user_save']}&nbsp;&nbsp;" class="btn btn-success"></td>
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
<input type="hidden" name="user_hash" value="$dle_login_hash" />
</div></form>
HTML;
	
	echofooter();
}
?>