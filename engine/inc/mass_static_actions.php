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
 Файл: mass_static_action.php
-----------------------------------------------------
 Назначение: массовые действие над статическими страницами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_static'] ) {
	msg( "error", $lang['mass_error'], $lang['mass_ddenied'], $_SESSION['admin_referrer'] );
}

if( ! $_SESSION['admin_referrer'] ) {
	
	$_SESSION['admin_referrer'] = "?mod=static&amp;action=list";

}

$selected_news = $_REQUEST['selected_news'];

if( ! $selected_news ) {
	msg( "error", $lang['mass_error'], $lang['mass_denied'], $_SESSION['admin_referrer'] );
}

if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	die( "Hacking attempt! User not found" );

}

$action = htmlspecialchars( strip_tags( stripslashes( $_POST['action'] ) ) );

$k_mass = false;
$field = false;

if( $action == "mass_date" ) {
	$field = "date";
	$value = time() + ($config['date_adjust'] * 60);
	$k_mass = true;
	$title = $lang['mass_static_edit_date_tl'];
	$lang['mass_confirm'] = $lang['mass_static_edit_date_tl'];
	$lang['mass_confirm_1'] = $lang['mass_static_confirm_2'];
} elseif( $action == "mass_clear_count" ) {
	$field = "views";
	$value = 0;
	$k_mass = true;
	$title = $lang['mass_clear_count_2'];
	$lang['mass_confirm'] = $lang['mass_clear_count_1'];
	$lang['mass_confirm_1'] = $lang['mass_static_confirm_2'];
}

if( $_POST['doaction'] == "mass_update" AND $field ) {
	foreach ( $selected_news as $id ) {
		$id = intval( $id );
		$db->query( "UPDATE " . PREFIX . "_static SET {$field}='{$value}' WHERE id='{$id}'" );
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '39', '')" );
	msg( "info", $lang['db_ok'], $lang['db_ok_1'].$t1, $_SESSION['admin_referrer'] );
}

if( $k_mass ) {
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$title}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['mass_confirm']}
HTML;
	
	echo " (<b>" . count( $selected_news ) . "</b>) $lang[mass_confirm_1]<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=static&action=list'\">
<input type=hidden name=action value=\"{$action}\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=doaction value=\"mass_update\">
<input type=hidden name=mod value=\"mass_static_actions\">";
	foreach ( $selected_news as $newsid ) {
		$newsid = intval($newsid);
		echo "<input type=hidden name=selected_news[] value=\"$newsid\">\n";
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

}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Подтвреждение удаления
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $action == "mass_delete" ) {
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['mass_static_delete']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="100" align="center">{$lang['mass_confirm']}
HTML;
	
	echo "(<b>" . count( $selected_news ) . "</b>) $lang[mass_static_confirm_3]<br><br>
<input class=\"btn btn-success\" type=submit value=\"   $lang[mass_yes]   \"> &nbsp; <input type=button class=\"btn btn-danger\" value=\"  $lang[mass_no]  \" onclick=\"javascript:document.location='$PHP_SELF?mod=static&action=list'\">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_static_actions\">";
	foreach ( $selected_news as $newsid ) {
		$newsid = intval($newsid);
		echo "<input type=hidden name=selected_news[] value=\"$newsid\">\n";
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

} 
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Удаление новостей
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
elseif( $action == "do_mass_delete" ) {
	
	$deleted_articles = 0;
	
	foreach ( $selected_news as $id ) {
		
		$id = intval( $id );

		$deleted_articles ++;
		
		$db->query( "DELETE FROM " . PREFIX . "_static WHERE id='$id'" );

		$db->query( "SELECT name, onserver FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );

		while ( $row = $db->get_row() ) {
			if( $row['onserver'] ) {
				@unlink( ROOT_DIR . "/uploads/files/" . $row['onserver'] );
			} else {
				$url_image = explode( "/", $row['name'] );
				if( count( $url_image ) == 2 ) {
					$folder_prefix = $url_image[0] . "/";
					$dataimages = $url_image[1];
				} else {
					$folder_prefix = "";
					$dataimages = $url_image[0];
				}
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $dataimages );
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "thumbs/" . $dataimages );
			}
		}
	
		$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );

	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '40', '')" );
	
	if( count( $selected_news ) == $deleted_articles ) {
		msg( "info", $lang['mass_static_delete'], $lang['mass_delok'], $_SESSION['admin_referrer'] );
	} else {
		msg( "error", $lang['mass_notok'], "$deleted_articles $lang[mass_i] " . count( $selected_news ) . " $lang[mass_notok_1]", $_SESSION['admin_referrer'] );
	}
} 
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  Ничего не выбрано
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
else {
	
	msg( "info", $lang['mass_noact'], $lang['mass_noact_1'], $_SESSION['admin_referrer'] );

}
?>