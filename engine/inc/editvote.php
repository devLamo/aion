<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 Autor email: nick-on@mail.ru  icq: 333396679
-----------------------------------------------------
 Copyright (c) 2004,2012
=====================================================
 Файл: editvote.php
-----------------------------------------------------
 Назначение: Список опросов, Управление опросами
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editvote'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";

if( $_GET['action'] == "delete" ) {

		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_vote WHERE id='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_vote_result WHERE vote_id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '27', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
	msg( "info", $lang['vote_str_2'], $lang['vote_str_2'], "?mod=editvote" );

}
if( $_GET['action'] == "clear" ) {

		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set vote_num='0' WHERE id='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_vote_result WHERE vote_id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '28', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
	msg( "info", $lang['vote_clear3'], $lang['vote_clear3'], "?mod=editvote" );

}

if( $_GET['action'] == "off" ) {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set approve='0' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '29', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
}
if( $_GET['action'] == "on" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_vote set approve='1' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '30', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/vote.php' );
}
if( $_GET['action'] == "views" AND $_GET['id']) {

	$id = intval ($_GET['id']);

	$row = $db->super_query( "SELECT id, title, category, body, vote_num FROM " . PREFIX . "_vote WHERE id='$id'" );
		
	$title = stripslashes( $row['title'] );
	$body = stripslashes( $row['body'] );
	$body = explode( "<br />", $body );
	$max = $row['vote_num'];


	$db->query( "SELECT answer, count(*) as count FROM " . PREFIX . "_vote_result WHERE vote_id='$id' GROUP BY answer" );
	
	$pn = 0;
	$entry = "";
	$answer = array ();
	
	while ( $row = $db->get_row() ) {
		$answer[$row['answer']]['count'] = $row['count'];
	}
	
	$db->free();

	for($i = 0; $i < sizeof( $body ); $i ++) {
			
		++ $pn;
		if( $pn > 5 ) $pn = 1;
			
		$num = $answer[$i]['count'];
		if( ! $num ) $num = 0;
		if( $max != 0 ) $proc = (100 * $num) / $max;
		else $proc = 0;
		$proc = round( $proc, 2 );
			
		$entry .= "<div class=\"vote\" align=\"left\">$body[$i] - $num ($proc%)</div>
      <div class=\"vote\" align=\"left\">
        <img src=\"" . $config['http_home_url'] . "templates/" . $config['skin'] . "/dleimages/poll{$pn}.gif\" height=\"10\" width=\"".intval($proc)."%\" style=\"border:1px solid black\">
      </div>\n";
	}

	if ( !$title ) $entry = $lang['vote_notfound'];

	$entry = "<div style=\"width:500px;\">$entry</div>";

	echoheader( "", "" );

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['vote_result']}&nbsp;{$title}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">
		<div id="main_box" align="center"><br />{$entry}<br /><br />{$lang['vote_count']}&nbsp;{$max}<br /><br /> 
		<input id = "next_button" onclick="history.go(-1); return false;" class="buttons" style="width:150px;" type="button" value="{$lang['func_msg']}">
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

} else {

echoheader( "vote", $lang[editvote] );
// ********************************************************************************
// Список голосований
// ********************************************************************************


echo "
 <script language=\"javascript\">
 <!-- begin
    function confirmdelete(id){
	    DLEconfirm( '{$lang['vote_confirm']}', '{$lang['p_confirm']}', function () {
			document.location=\"?mod=editvote&action=delete&user_hash={$dle_login_hash}&id=\"+id;
		} );
    }
    function confirmclear(id){
	    DLEconfirm( '{$lang['vote_clear']}', '{$lang['p_confirm']}', function () {
			document.location=\"?mod=editvote&action=clear&user_hash={$dle_login_hash}&id=\"+id;
		} );
    }
 // end -->
 </script>";

/* Vote List */
$db->query( "SELECT * FROM " . PREFIX . "_vote ORDER BY id DESC" );

$entries = "";

while ( $row = $db->get_row() ) {
	
	$item_id = $row['id'];
	$item_date = date( "d.m.Y", strtotime( $row['date'] ) );
	$title = htmlspecialchars( stripslashes( $row['title'] ) );

	if ( $row['start'] ) $start_date = date( "d.m.Y H:i", $row['start'] ); else $start_date = "--";
	if ( $row['end'] ) $end_date = date( "d.m.Y H:i", $row['end'] ); else $end_date = "--";
	
	if( dle_strlen( $title, $config['charset'] ) > 74 ) {
		$title = dle_substr( $title, 0, 70, $config['charset'] ) . " ...";
	}
	
	$item_num = $row['vote_num'];
	if( empty( $row['category'] ) ) {
		$item_category = "<center>--</center>";
	} elseif( $row['category'] == "all" ) {
		$item_category = $lang['edit_all'];
	} else {
		$item_category = $cat[$row['category']];
	}
	;
	
	if( $row['approve'] ) {
		$status = "led_green.gif";
		$lang['led_title'] = $lang['led_on_title'];
		$led_action = "off";
	} else {
		$status = "led_gray.gif";
		$lang['led_title'] = $lang['led_off_title'];
		$led_action = "on";
	}
	
	$entries .= "
   <tr>
    <td height=22 class=\"list\">
    $item_date&nbsp;-&nbsp;<a title='$lang[word_ledit]' href=\"$PHP_SELF?mod=addvote&action=edit&id=$item_id\">{$title}</td>
    <td class=\"list\" align=\"center\">{$start_date}</td>
    <td class=\"list\" align=\"center\">{$end_date}</td>
    <td class=\"list\" align=\"center\"><img src=\"engine/skins/images/" . $status . "\" title=\"" . $lang['led_title'] . "\" border=\"0\"></td>
    <td class=\"list\" align=\"center\">{$row['vote_num']}</td>
    <td class=\"list\" align=\"center\">{$item_category}</td>
    <td class=\"list\" align=\"center\"><a onClick=\"return dropdownmenu(this, event, MenuBuild('" . $item_id . "', '" . $led_action . "'), '170px')\" href=\"#\"><img src=\"engine/skins/images/browser_action.gif\" border=\"0\"></a></td>
     </tr>
	<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=7></td></tr>";
}
$db->free();

if( empty( $entries ) ) {
	$entries = "<tr><td colspan=7 align=center height=40>" . $lang['vote_nodata'] . "</td></tr>";
}

echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function MenuBuild( m_id , led_action){

var menu=new Array()
var lang_action = "";

if (led_action == 'off') { lang_action = "{$lang['vote_aus']}"; } else { lang_action = "{$lang['vote_ein']}"; }


menu[0]='<a onClick="document.location=\'?mod=editvote&action=views&id=' + m_id + '\'; return(false)" href="#">{$lang['vote_view']}</a>';
menu[1]='<a onClick="document.location=\'?mod=editvote&action=' + led_action + '&user_hash={$dle_login_hash}&id=' + m_id + '\'; return(false)" href="#">' + lang_action + '</a>';
menu[2]='<a onClick="javascript:confirmclear(' + m_id + '); return(false)" href="#">{$lang['vote_clear2']}</a>';
menu[3]='<a onClick="javascript:confirmdelete(' + m_id + '); return(false)" href="#">{$lang['cat_del']}</a>';

return menu;
}
//-->
</script>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_votec']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
  <tr>
   <td>&nbsp;&nbsp;{$lang['edit_title']}</td>
   <td width=110 align="center">{$lang['vote_startinfo']}</td>
   <td width=110 align="center">{$lang['vote_endinfo']}</td>
   <td width=50 align="center">{$lang['led_status']}</td>
   <td width=60 align="center">{$lang['vote_count']}</td>
   <td width=150 align="center">{$lang['edit_cl']}</td>
   <td width=80 align="center">{$lang[vote_action]}</td>
  </tr>
	<tr><td colspan="7"><div class="hr_line"></div></td></tr>
	{$entries}
	<tr><td colspan="7"><div class="hr_line"></div></td></tr>
  <tr><td colspan="7"><a href="?mod=addvote"><input onclick="document.location='?mod=addvote'" type="button" class="btn btn-primary" value="&nbsp;&nbsp;{$lang['poll_new']}&nbsp;&nbsp;"></a></td></tr>
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

}
?>