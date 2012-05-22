<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group
-----------------------------------------------------
http://dle-news.ru/
-----------------------------------------------------
Copyright (c) 2004,2012 SoftNews Media Group
=====================================================
Файл: rssinform.php
-----------------------------------------------------
Назначение: управление RSS информерами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_rssinform'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";

if( $_REQUEST['action'] == "doadd" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$rss_tag = totranslit( strip_tags( trim( $_POST['rss_tag'] ) ) );
	$rss_descr = $db->safesql( strip_tags( trim( $_POST['rss_descr'] ) ) );
	$rss_url = $db->safesql( strip_tags( trim( $_POST['rss_url'] ) ) );
	$rss_template = totranslit( strip_tags( trim( $_POST['rss_template'] ) ) );
	$rss_max = intval( $_POST['rss_max'] );
	$rss_tmax = intval( $_POST['rss_tmax'] );
	$rss_dmax = intval( $_POST['rss_dmax'] );
	$rss_date_format = $db->safesql( strip_tags( trim( $_POST['rss_date_format'] ) ) );

	$category = $_POST['category'];

	if( !count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );
	
	if( $rss_tag == "" or $rss_descr == "" or $rss_url == "" or $rss_template == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "INSERT INTO " . PREFIX . "_rssinform (tag, descr, category, url, template, news_max, tmax, dmax, rss_date_format) values ('$rss_tag', '$rss_descr', '$category', '$rss_url', '$rss_template', '$rss_max', '$rss_tmax', '$rss_dmax', '$rss_date_format')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '53', '{$rss_tag}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
	header( "Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?mod=rssinform" );

}

if( $_REQUEST['action'] == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$rss_tag = totranslit( strip_tags( trim( $_POST['rss_tag'] ) ) );
	$rss_descr = $db->safesql( strip_tags( trim( $_POST['rss_descr'] ) ) );
	$rss_url = $db->safesql( strip_tags( trim( $_POST['rss_url'] ) ) );
	$rss_template = totranslit( strip_tags( trim( $_POST['rss_template'] ) ) );
	$rss_max = intval( $_POST['rss_max'] );
	$rss_tmax = intval( $_POST['rss_tmax'] );
	$rss_dmax = intval( $_POST['rss_dmax'] );
	$rss_date_format = $db->safesql( strip_tags( trim( $_POST['rss_date_format'] ) ) );
	
	$category = $_POST['category'];

	if( !count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );
	
	if( $rss_tag == "" or $rss_descr == "" or $rss_url == "" or $rss_template == "" ) msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
	
	$db->query( "UPDATE " . PREFIX . "_rssinform SET tag='$rss_tag', descr='$rss_descr', category='$category', url='$rss_url', template='$rss_template', news_max='$rss_max', tmax='$rss_tmax', dmax='$rss_dmax', rss_date_format='$rss_date_format' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '54', '{$rss_tag}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
	header( "Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?mod=rssinform" );
}

if( $_GET['action'] == "off" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_rssinform set approve='0' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '55', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}
if( $_GET['action'] == "on" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_rssinform set approve='1' WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '56', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}

if( $_GET['action'] == "delete" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_rssinform WHERE id='$id'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '57', '{$id}')" );

	@unlink( ENGINE_DIR . '/cache/system/informers.php' );
	clear_cache();
}

if( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	if( $_REQUEST['action'] == "add" ) {
		$doaction = "doadd";
		$all_cats = "selected";
		$rss_max = "5";
		$rss_tmax = 0;
		$rss_dmax = 200;
		$rss_template = "informer";
		$rss_date_format = "j F Y H:i";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_rssinform WHERE id='$id' LIMIT 0,1" );
		$rss_tag = $row['tag'];
		$rss_descr = htmlspecialchars( stripslashes( $row['descr'] ) );
		$rss_url = htmlspecialchars( stripslashes( $row['url'] ) );
		$rss_template = htmlspecialchars( stripslashes( $row['template'] ) );
		$rss_max = $row['news_max'];
		$rss_tmax = $row['tmax'];
		$rss_dmax = $row['dmax'];
		$rss_date_format = $row['rss_date_format'];
		$doaction = "doedit";
	}
	
	$opt_category = CategoryNewsSelection( explode( ',', $row['category'] ), 0, FALSE );
	if( ! $row['category'] ) $all_cats = "selected";
	else $all_cats = "";
	
	echoheader( "", "" );
	
	echo <<<HTML
    <form action="" method="post">
      <input type="hidden" name="mod" value="rssinform">
      <input type="hidden" name="action" value="{$doaction}">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_rssinform']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="230" style="padding:4px;">{$lang['rssinform_xname']}</td>
        <td><input class="edit bk" style="width: 200px;" type="text" name="rss_tag" value="{$rss_tag}" />&nbsp;&nbsp;&nbsp;({$lang['xf_lat']})</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['rssinform_xdescr']}</td>
        <td><input  class="edit bk" style="width: 312px;" type="text" name="rss_descr" value="{$rss_descr}" /></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['addnews_cat']}</td>
        <td><select name="category[]" class="cat_select" multiple>
   <option value="0" {$all_cats}>{$lang['edit_all']}</option>
   {$opt_category}
   </select></td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['rssinform_url']}</td>
        <td><input  class="edit bk" style="width: 312px;" type="text" name="rss_url" value="{$rss_url}" /></td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['opt_sys_an']}</td>
        <td><input  class="edit bk" style="width: 200px;" type="text" name="rss_date_format" value="{$rss_date_format}" /> <a onClick="javascript:Help('date'); return false;" class="main" href="#">{$lang['opt_sys_and']}</a></td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['rssinform_template']}</td>
        <td><input  class="edit bk" style="width: 200px;" type="text" name="rss_template" value="{$rss_template}" /> .tpl</td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['rssinform_max']}</td>
        <td><input  class="edit bk" style="width: 50px;" type="text" name="rss_max" value="{$rss_max}" /> <a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_ri_max]}', this, event, '220px')">[?]</a></td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['rssinform_tmax']}</td>
        <td><input  class="edit bk" style="width: 50px;" type="text" name="rss_tmax" value="{$rss_tmax}" /> <a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_ri_tmax]}', this, event, '220px')">[?]</a></td>
    </tr>
	<tr>
        <td style="padding:4px;">{$lang['rssinform_dmax']}</td>
        <td><input  class="edit bk" style="width: 50px;" type="text" name="rss_dmax" value="{$rss_dmax}" /> <a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_ri_dmax]}', this, event, '220px')">[?]</a></td>
    </tr>
        <tr>
		<td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan=2><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td colspan=2 style="padding:4px;"><input type="submit" class="btn btn-success" value="&nbsp;&nbsp;{$lang['user_save']}&nbsp;&nbsp;"></td>
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
    </form>
HTML;
	
	echofooter();

} else {
	
	echoheader( "", "" );
	
	$db->query( "SELECT * FROM " . PREFIX . "_rssinform ORDER BY id ASC" );
	
	$entries = "";
	
	if( ! $config['rss_informer'] ) $offline = "<font color=\"red\">" . $lang['modul_offline'] . "</font><br /><br />";
	else $offline = "";
	
	while ( $row = $db->get_row() ) {
		
		$row['descr'] = stripslashes( $row['descr'] );
		$row['tag'] = "{inform_" . $row['tag'] . "}";
		
		if( $row['approve'] ) {
			$status = "led_green.gif";
			$lang['led_active'] = $lang['rssinform_on'];
			$led_action = "off";
		} else {
			$status = "led_gray.gif";
			$lang['led_active'] = $lang['rssinform_off'];
			$led_action = "on";
		}
		
		$entries .= "
   <tr>
    <td height=22 class=\"list\">
    {$row['tag']}</td>
    <td class=\"list\" style=\"padding:2px;\">{$row['descr']}</td>
    <td class=\"list\" style=\"padding:2px;\">{$row['template']}.tpl</td>
    <td class=\"list\" style=\"padding:2px;\"><img src=\"engine/skins/images/" . $status . "\" title=\"" . $lang['led_active'] . "\" border=\"0\" align=\"absmiddle\"></td>
    <td class=\"list\" align=\"center\"><a onClick=\"return dropdownmenu(this, event, MenuBuild('" . $row['id'] . "', '" . $led_action . "'), '150px')\" href=\"#\"><img src=\"engine/skins/images/browser_action.gif\" border=\"0\"></a></td>
     </tr>
	<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=5></td></tr>";
	}
	$db->free();
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function MenuBuild( m_id , led_action){

var menu=new Array()
var lang_action = "";

if (led_action == 'off') { lang_action = "{$lang['banners_aus']}"; } else { lang_action = "{$lang['rssinform_ein']}"; }


menu[0]='<a onClick="document.location=\'?mod=rssinform&user_hash={$dle_login_hash}&action=' + led_action + '&id=' + m_id + '\'; return(false)" href="#">' + lang_action + '</a>';
menu[1]='<a onClick="document.location=\'?mod=rssinform&user_hash={$dle_login_hash}&action=edit&id=' + m_id + '\'; return(false)" href="#">{$lang['group_sel1']}</a>';
menu[2]='<a onClick="javascript:confirmdelete(' + m_id + '); return(false)" href="#">{$lang['cat_del']}</a>';

return menu;
}
function confirmdelete(id){
	    DLEconfirm( '{$lang['rssinform_del']}', '{$lang['p_confirm']}', function () {
			document.location="?mod=rssinform&user_hash={$dle_login_hash}&action=delete&id="+id;
		} );
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['rssinform_title']}</div></td>
    </tr>
</table>
<div class="unterline"></div>

<table width="100%">
  <tr>
   <td width=150>{$lang['banners_tag']}</td>
   <td>{$lang['static_descr']}</td>
   <td width=150>{$lang['rssinform_template']}</td>
   <td width=50>{$lang['banners_opt']}</td>
   <td width=80 align="center">{$lang[vote_action]}</td>
  </tr>
	<tr><td colspan="5"><div class="hr_line"></div></td></tr>
	{$entries}
	<tr><td colspan="5"><div class="hr_line"></div></td></tr>
  <tr><td colspan="5">{$offline}<a href="?mod=rssinform&action=add"><input onclick="document.location='?mod=rssinform&action=add'" type="button" class="btn btn-primary" value="&nbsp;&nbsp;{$lang['rssinform_create']}&nbsp;&nbsp;"></a></td></tr>
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