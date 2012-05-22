<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 Copyright (c) 2004,2012
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: addvote.php
-----------------------------------------------------
 Назначение: Добавление\Редактирование опроса
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editvote'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( );
$parse->filter_mode = false;

$stop = false;
if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] );
else $id = "";

if( $_GET['action'] == "add" ) {

	if( $_POST['user_hash'] == "" or $_POST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	$category = $_POST['category'];
	
	if( !count( $category ) ) {
		$category = array ();
		$category[] = 'all';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		if ($value == "all") $category_list[] = $value; else $category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );
	
	$title = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['title'] ), false ) );
	$body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['body'] ), false ) );
	
	$db->query( "INSERT INTO " . PREFIX . "_vote (category, vote_num, date, title, body, approve, start, end) VALUES ('$category', 0, CURRENT_DATE(), '$title', '$body', '1', '$start_date', '$end_date')" );
	@unlink( ENGINE_DIR . '/cache/system/vote.php' );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '2', '{$title}')" );

	msg( "info", $lang['vote_str_3'], $lang['vote_str_3'], "?mod=editvote" );

} elseif( $_GET['action'] == "update" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	if ( trim($_POST['start_date']) ) {

		$start_date = @strtotime( $_POST['start_date'] );

		if ($start_date === - 1 OR !$start_date) $start_date = "";

	} else $start_date = "";

	if ( trim($_POST['end_date']) ) {

		$end_date = @strtotime( $_POST['end_date'] );

		if ($end_date === - 1 OR !$end_date) $end_date = "";

	} else $end_date = "";
	
	$category = $_POST['category'];
	
	if( ! count( $category ) ) {
		$category = array ();
		$category[] = 'all';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		if ($value == "all") $category_list[] = $value; else $category_list[] = intval($value);
	}

	$category = $db->safesql( implode( ',', $category_list ) );
	
	$title = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['title'] ), false ) );
	$body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['body'] ), false ) );
	$id = intval( $_REQUEST['id'] );
	
	$db->query( "UPDATE " . PREFIX . "_vote set category='$category', title='$title', body='$body', start='$start_date', end='$end_date' where id=$id" );
	@unlink( ENGINE_DIR . '/cache/system/vote.php' );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '3', '{$title}')" );

	msg( "info", $lang['vote_str_4'], $lang['vote_str_4'], "?mod=editvote" );

} elseif( ! $stop ) {

	$js_array[] = "engine/skins/calendar.js";
	
	echoheader( "vote", $lang[addvote] );
	$canedit = false;
	$start_date = "";
	$stop_date  = "";
	
	// ********************************************************************************
	// Add Form
	// ********************************************************************************
	

	if( ($_GET['action'] == "edit") && $id != '' ) {
		$canedit = true;
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_vote WHERE id='$id' LIMIT 0,1" );
		
		$title = $parse->decodeBBCodes( $row['title'], false );
		$body = $parse->decodeBBCodes( $row['body'], false );
		$icategory = explode( ',', $row['category'] );
		if( $row['category'] == "all" ) $all_cats = "selected";
		else $all_cats = "";

		if ( $row['start'] ) $start_date = @date( "Y-m-d H:i", $row['start'] );
		if ( $row['end'] )  $end_date  = @date( "Y-m-d H:i", $row['end'] );
	
	} else {
		$canedit = false;
	}
	;
	
	$opt_category = CategoryNewsSelection( $icategory, 0, FALSE );
	
	echo "<script>
     function insertext(text,area){
      if(area == \"body\")  {
         document.addvote.body.focus();
         document.addvote.body.value=document.addvote.body.value +\" \"+ text;
         document.addvote.body.focus()
      }
     }
  </script>";
	
	if( $canedit == false ) {
		echo "<form method=post action=\"?mod=addvote&action=add\" style=\"padding:0; margin:0\" name=\"addvote\" onsubmit=\"if(document.addvote.title.value == '' || document.addvote.body.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\">";
		$button = "<input type=\"submit\" class=\"btn btn-success\" value=\"&nbsp;&nbsp;{$lang['vote_new']}&nbsp;&nbsp;\">";
	} else {
		echo "<form method=post action=\"?mod=addvote&action=update&id=$id\" style=\"padding:0; margin:0\" name=\"addvote\" onsubmit=\"if(document.addvote.title.value == '' || document.addvote.body.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\">";
		$button = "<input type=\"submit\" class=\"btn btn-success\" value=\"&nbsp;&nbsp;{$lang['vote_edit']}&nbsp;&nbsp;\">";
	
	}
	
	echo <<<HTML
<!-- calendar stylesheet -->
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_votec']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="130" style="padding:4px;">{$lang['vote_title']}</td>
        <td><input type="text" class="edit bk" name="title" style="width:312px" value="$title"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vtitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['addnews_cat']}</td>
        <td style="padding-bottom:2px;"><select name="category[]" class="cat_select" multiple>
   <option value="all" {$all_cats}>{$lang['edit_all']}</option>
   {$opt_category}
   </select><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vcat]}', this, event, '200px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['vote_startdate']}</td>
        <td><input type="text" name="start_date" id="f_date_s" size="20"  class="edit bk" value="{$start_date}" />&nbsp;<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_s" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vstart]}', this, event, '250px')">[?]</a>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_s",     // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
        button         :    "f_trigger_s",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment 
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script>
</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['vote_enddate']}</td>
        <td><input type="text" name="end_date" id="f_date_e" size="20"  class="edit bk" value="{$end_date}" />&nbsp;<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_e" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vend]}', this, event, '250px')">[?]</a>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_e",     // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
        button         :    "f_trigger_e",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment 
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script>
</td>
    </tr>

HTML;
	
	include (ENGINE_DIR . '/inc/include/inserttag.php');
	
	echo <<<HTML
    <tr>
        <td style="padding:4px;">$lang[vote_body]<br /><span class="navigation">$lang[vote_str_1]</span></td>
        <td>
	<table width="100%"><tr><td>{$bb_code}
     <textarea rows=16 style="width:98%;" name="body" id="body" onclick="setFieldName(this.name)" class="bk">{$body}</textarea><script type=text/javascript>var selField  = "body";</script>
    </td>
</tr></table>
	</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:4px;">&nbsp;</td>
        <td>{$button}</td>
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