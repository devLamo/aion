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
 Файл: mass_user_actions.php
-----------------------------------------------------
 Назначение: Массовые действия над пользователями
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( ! $user_group[$member_id['user_group']]['admin_editusers'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$selected_users = $_REQUEST['selected_users'];

if( ! $selected_users ) {
	msg( "error", $lang['mass_error'], $lang['massusers_denied'],"?mod=editusers&amp;action=list" );
}

if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	die( "Hacking attempt! User not found" );

}

if( $_POST['action'] == "mass_delete" ) {
	
	echoheader( "options", $lang['mass_head'] );


	echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['massusers_head']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['massusers_confirm']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=editusers&amp;action=list'\">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
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
	exit();

} elseif ($_POST['action'] == "do_mass_delete") {

	$deleted = 0;

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		if( $id == 1 ) {
			msg( "error", $lang['mass_error'], $lang['user_undel'], "?mod=editusers&amp;action=list" );
		}
	
		$row = $db->super_query( "SELECT user_id, user_group, name, foto FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
	
		if( ! $row['user_id'] ) msg( "error", $lang['mass_error'], $lang['user_undel'], "?mod=editusers&amp;action=list" );
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
			msg( "error", $lang['mass_error'], $lang['user_undel'], "?mod=editusers&amp;action=list" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '41', '{$row['name']}')" );

		$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user_from = '{$row['name']}' AND folder = 'outbox'" );
		$db->query( "delete FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
		$db->query( "delete FROM " . USERPREFIX . "_banned WHERE users_id='$id'" );
		$db->query( "delete FROM " . USERPREFIX . "_pm WHERE user='$id'" );

		@unlink( ROOT_DIR . "/uploads/fotos/" . $row['foto'] );

		$deleted ++;
	}

	clear_cache();
	@unlink( ENGINE_DIR . '/cache/system/banned.php' );
	
	if( count( $selected_users ) == $deleted ) {
		msg( "info", $lang['massusers_head'], $lang['massusers_delok'], "?mod=editusers&amp;action=list" );
	} else {
		msg( "error", $lang['mass_error'], "$deleted $lang[mass_i] " . count( $selected_users ) . " $lang[massusers_confirm_2]", "?mod=editusers&amp;action=list" );
	}

} elseif ($_POST['action'] == "mass_delete_comments") {

	echoheader( "options", $lang['mass_head'] );


	echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['massusers_head_1']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['massusers_confirm_3']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=editusers&amp;action=list'\">
<input type=hidden name=action value=\"do_mass_delete_comments\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
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
	exit();

} elseif ($_POST['action'] == "do_mass_delete_comments") {

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1' GROUP BY post_id" );
		
		while ( $row = $db->get_array( $result ) ) {
			
			$db->query( "UPDATE " . PREFIX . "_post set comm_num=comm_num-{$row['count']} where id='{$row['post_id']}'" );
		
		}
		$db->free( $result );
		
		$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' WHERE user_id ='$id'" );
		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1'" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '42', '{$id}')" );

	}

	clear_cache();
	msg( "info", $lang['massusers_head_1'], $lang['massusers_comok'], "?mod=editusers&amp;action=list" );

} elseif ($_POST['action'] == "mass_move_to_group") {

	$js_array[] = "engine/skins/calendar.js";

	echoheader( "options", $lang['mass_head'] );


	echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['massusers_head_2']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['massusers_confirm_4']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
{$lang['user_acc']} <select name=\"editlevel\" class=\"edit\">".get_groups()."</select> {$lang['user_gtlimit']} <input size=\"17\" name=\"time_limit\" id=\"time_limit\" class=\"edit\" value=\"{$row['time_limit']}\"> <img src=\"engine/skins/images/img.gif\"  align=\"absmiddle\" id=\"t_trigger_ent\" style=\"cursor: pointer; border: 0\" title=\"{$lang['edit_ecal']}\"/><a href=\"#\" class=\"hintanchor\" onMouseover=\"showhint('{$lang[hint_glhel]}', this, event, '250px')\">[?]</a>
<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=editusers&amp;action=list'\">
<input type=hidden name=action value=\"do_mass_move_to_group\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
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

<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
<script type="text/javascript">
    Calendar.setup({
      inputField     :    "time_limit",     // id of the input field
      ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
      button         :    "t_trigger_ent",  // trigger for the calendar (button ID)
      align          :    "Br",           // alignment 
	  timeFormat     :    "24",
	  showsTime      :    true,
      singleClick    :    true
    });
</script>
HTML;

	echofooter();
	exit();

} elseif ($_POST['action'] == "do_mass_move_to_group") {

	$editlevel = intval( $_POST['editlevel'] );
	$time_limit = trim( $_POST['time_limit'] ) ? strtotime( $_POST['time_limit'] ) : "";

	if( ! $user_group[$editlevel]['time_limit'] ) $time_limit = "";

	if ($member_id['user_group'] != 1 AND $editlevel < 2 ) 
		msg( "error", $lang['mass_error'], $lang['admin_not_access'], "?mod=editusers&amp;action=list" );

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		$row = $db->super_query( "SELECT name, user_group FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
			msg( "error", $lang['mass_error'], $lang['edit_not_admin'], "?mod=editusers&amp;action=list" );

		$db->query( "UPDATE " . USERPREFIX . "_users SET user_group='$editlevel', time_limit='$time_limit' WHERE user_id ='$id'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '43', '{$row['name']}')" );
	}

	clear_cache();
	msg( "info", $lang['massusers_head_2'], $lang['massusers_groupok']." <b>".$user_group[$editlevel]['group_name']."</b>", "?mod=editusers&amp;action=list" );

} elseif ($_POST['action'] == "mass_move_to_ban") {

	echoheader( "options", $lang['mass_head'] );


	echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['massusers_head_3']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['massusers_confirm_5']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<div style=\"width:350px;\" align=\"left\">{$lang['ban_date']} <input size=\"5\" name=\"banned_date\" class=\"edit\" value=\"0\"><a href=\"#\" class=\"hintanchor\" onMouseover=\"showhint('{$lang[hint_bandescr]}', this, event, '250px')\">[?]</a>
<br><br>{$lang['ban_descr']}<br><textarea style=\"width:100%; height:80px;\" name=\"banned_descr\"></textarea>
<br><br></div>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=editusers&amp;action=list'\">
<input type=hidden name=action value=\"do_mass_move_to_ban\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
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
	exit();

} elseif ($_POST['action'] == "do_mass_move_to_ban") {


	include_once ENGINE_DIR . '/classes/parse.class.php';
	$parse = new ParseFilter( );

	foreach ( $selected_users as $id ) {

		$id = intval( $id );

		$row = $db->super_query( "SELECT name, user_group FROM " . USERPREFIX . "_users WHERE user_id='$id'" );
	
		if ($member_id['user_group'] != 1 AND $row['user_group'] == 1 )
			msg( "error", $lang['mass_error'], $lang['edit_not_admin'], "?mod=editusers&amp;action=list" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '44', '{$row['name']}')" );


		$banned_descr = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['banned_descr'] ), false ) );
		$this_time = time() + ($config['date_adjust'] * 60);
		$banned_date = intval( $_POST['banned_date'] );
		$this_time = $banned_date ? $this_time + ($banned_date * 60 * 60 * 24) : 0;

		$row = $db->super_query( "SELECT users_id, days FROM " . USERPREFIX . "_banned WHERE users_id = '$id'" );
		
		if( ! $row['users_id'] ) $db->query( "INSERT INTO " . USERPREFIX . "_banned (users_id, descr, date, days) values ('$id', '$banned_descr', '$this_time', '$banned_date')" );
		else {
			
			if( $row['days'] != $banned_date ) $db->query( "UPDATE " . USERPREFIX . "_banned SET descr='$banned_descr', days='$banned_date', date='$this_time' WHERE users_id = '$id'" );
			else $db->query( "UPDATE " . USERPREFIX . "_banned set descr='$banned_descr' WHERE users_id = '$id'" );
		
		}
		
		@unlink( ENGINE_DIR . '/cache/system/banned.php' );

		$db->query( "UPDATE " . USERPREFIX . "_users SET banned='yes' WHERE user_id ='$id'" );


	}

	clear_cache();
	msg( "info", $lang['massusers_head_3'], $lang['massusers_banok'], "?mod=editusers&amp;action=list" );

} elseif ($_POST['action'] == "mass_delete_pm") {

	echoheader( "options", $lang['mass_head'] );


	echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['massusers_head_4']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['massusers_confirm_6']}
HTML;
	
	echo " (<b>" . count( $selected_users ) . "</b>) $lang[massusers_confirm_1]<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=editusers&amp;action=list'\">
<input type=hidden name=action value=\"do_mass_delete_pm\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_user_actions\">";
	foreach ( $selected_users as $userid ) {
		$userid = intval($userid);
		echo "<input type=hidden name=selected_users[] value=\"$userid\">\n";
	}
	
	echo <<<HTML
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
	exit();

} elseif ($_POST['action'] == "do_mass_delete_pm") {

	foreach ( $selected_users as $id ) {

		$id = intval( $id );
		$row = $db->super_query( "SELECT name FROM " . USERPREFIX . "_users WHERE user_id='$id'" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '45', '{$row['name']}')" );

		$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user='$id' AND folder = 'inbox'" );
		$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user_from='{$row['name']}' AND folder = 'outbox'" );
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread='0', pm_all='0'  WHERE user_id ='$id'" );

	}

	clear_cache();
	msg( "info", $lang['massusers_head_4'], $lang['massusers_pm_ok'], "?mod=editusers&amp;action=list" );

} else {

	msg( "info", $lang['mass_noact'], $lang['mass_noact_1'], "?mod=editusers&amp;action=list" );

}
?>