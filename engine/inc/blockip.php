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
 Файл: blockip.php
-----------------------------------------------------
 Назначение: Блокировка посетителей по IP
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_blockip'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['ip_add'] ) ) $ip_add = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['ip_add'] ) ) ) ); else $ip_add = "";
if( isset( $_REQUEST['ip'] ) ) $ip = htmlspecialchars( strip_tags( trim( $_REQUEST['ip'] ) ) ); else $ip = "";
if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = 0;

if( $action == "add" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( );
	$parse->safe_mode = true;
	$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['descr'] ), false ) );
	
	if( (trim( $_POST['date'] ) == "") OR (($_POST['date'] = strtotime( $_POST['date'] )) === - 1) OR !$_POST['date']) {
		$this_time = 0;
		$days = 0;
	} else {
		$this_time = $_POST['date'];
		$days = 1;
	}
	
	if( ! $ip_add ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "$PHP_SELF?mod=blockip" );
	}

	$row = $db->super_query( "SELECT id FROM " . PREFIX . "_banned WHERE ip ='$ip_add'" );

	if ( $row['id'] ) {
		msg( "error", $lang['ip_error_1'], $lang['ip_error_1'], "$PHP_SELF?mod=blockip" );
	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_banned (descr, date, days, ip) values ('$banned_descr', '$this_time', '$days', '$ip_add')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '9', '{$ip_add}')" );
	
	@unlink( ENGINE_DIR . '/cache/system/banned.php' );

} elseif( $action == "delete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $id ) {
		msg( "error", $lang['ip_error'], $lang['ip_error'], "$PHP_SELF?mod=blockip" );
	}
	
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE id = '$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '10', '')" );

	@unlink( ENGINE_DIR . '/cache/system/banned.php' );

}

$js_array[] = "engine/skins/calendar.js";

echoheader( "", "" );

echo <<<HTML
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['ip_add']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">{$lang['ip_example']}</td> 
    </tr>
	<tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:2px;" width="160">{$lang['ip_type']}</td> 
		<td style="padding:2px;" width="100%"><input class="edit bk" style="width:250px;" type="text" name="ip_add" value="{$ip}"></td>
    </tr>
    <tr>
        <td style="padding:2px;" width="160" nowrap>{$lang['ban_date']}</td> 
		<td style="padding:2px;"><input type="text" name="date" id="f_date_c" size="20" class="edit bk">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_c",     // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
        button         :    "f_trigger_c",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['ban_descr']}</td> 
        <td style="padding:2px;">{$lang['ip_add_descr']} <textarea class="edit bk" style="width:250px;height:70px;" name="descr"></textarea></td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td> 
		<td style="padding:2px;"><input type="submit" value="{$lang['user_save']}" class="btn btn-success"></td>
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
<input type="hidden" name="action" value="add">
<input type="hidden" name="user_hash" value="$dle_login_hash">
</form>
HTML;

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
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['ip_list']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="200" style="padding:2px;">&nbsp;</td>
        <td width="190">{$lang['ban_date']}</td>
        <td width="250">{$lang['ban_descr']}</td>
        <td>&nbsp;</td>
    </tr>
	<tr><td colspan="4"><div class="hr_line"></div></td></tr>
HTML;

$db->query( "SELECT * FROM " . USERPREFIX . "_banned WHERE users_id = '0' ORDER BY id DESC" );

$i = 0;
while ( $row = $db->get_row() ) {
	$i ++;
	
	if( $row['date'] ) $endban = langdate( "j M Y H:i", $row['date'] );
	else $endban = $lang['banned_info'];
	
	echo "
        <tr>
        <td style=\"padding:3px\">
        {$row['ip']}
        </td>
        <td style=\"padding:3px\">
        {$endban}
        </td>
        <td style=\"padding:3px\">
        " . stripslashes( $row['descr'] ) . "
        </td>
        <td>
        [<a href=\"$PHP_SELF?mod=blockip&action=delete&id={$row['id']}&user_hash={$dle_login_hash}\">{$lang['ip_unblock']}</a>]</td>
        </tr>
	</tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>
        ";
}

if( $i == 0 ) {
	echo "<tr>
     <td height=\"18\" colspan=\"4\">
       <p align=\"center\"><br><b>$lang[ip_empty]<br><br></b>
    </tr>";
}

echo <<<HTML
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