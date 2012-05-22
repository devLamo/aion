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
 Файл: rss.php
-----------------------------------------------------
 Назначение: Управление RSS каналами
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_rss'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['id'] ) ) $id = intval( $_REQUEST['id'] ); else $id = "";


if( $_GET['subaction'] == "clear" ) {

	$lastdate = intval( $_GET['lastdate'] );
	if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );

}

if( $_REQUEST['action'] == "addnews" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	
	$allow_comm = intval( $_POST['allow_comm'] );
	$allow_main = intval( $_POST['allow_main'] );
	$allow_rating = intval( $_POST['allow_rating'] );
	$news_fixed = 0;
	$allow_br = intval( $_POST['text_type'] );
	$lastdate = intval( $_POST['lastdate'] );
	
	if( count( $_POST['content'] ) ) {
		
		foreach ( $_POST['content'] as $content ) {
			$approve = intval( $content['approve'] );
			
			if( ! count( $content['category'] ) ) {
				$content['category'] = array ();
				$content['category'][] = '0';
			}

			$category_list = array();
		
			foreach ( $content['category'] as $value ) {
				$category_list[] = intval($value);
			}
		
			$category_list = $db->safesql( implode( ',', $category_list ) );
			
			$full_story = $parse->process( $content['full'] );
			$short_story = $parse->process( $content['short'] );
			$title = $parse->process(  trim( strip_tags ($content['title']) ) );
			$_POST['title'] = $title;
			$alt_name = totranslit( stripslashes( $title ) );
			$title = $db->safesql( $title );
			
			if( ! $allow_br ) {
				$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
				$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
			} else {
				$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
				$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
			}
			
			$metatags = create_metatags( $short_story . $full_story );
			$thistime = date( "Y-m-d H:i:s", strtotime( $content['date'] ) );
			
			if( trim( $title ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_ertitle'], "javascript:history.go(-1)" );
			}
			if( trim( $short_story ) == "" ) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erstory'], "javascript:history.go(-1)" );
			}
			
			$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, descr, keywords, category, alt_name, allow_comm, approve, allow_main, allow_br) values ('$thistime', '{$member_id['name']}', '$short_story', '$full_story', '', '$title', '{$metatags['description']}', '{$metatags['keywords']}', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '$allow_br')" );

			$row = $db->insert_id();
			$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, user_id) VALUES('{$row}', '$allow_rating', '0', '{$member_id['user_id']}')" );


			$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
		
		}
		
		if( $id and $lastdate ) $db->query( "UPDATE " . PREFIX . "_rss SET lastdate='$lastdate' WHERE id='$id'" );
		
		clear_cache();
		msg( "info", $lang['addnews_ok'], $lang['rss_added'], "$PHP_SELF?mod=rss" );
	
	}
	
	msg( "error", $lang['addnews_error'], $lang['rss_notadded'], "$PHP_SELF?mod=rss" );

} elseif( $_REQUEST['action'] == "news" and $id ) {
	
	include_once ENGINE_DIR . '/classes/rss.class.php';
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	$parse->leech_mode = true;
	
	$rss = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='$id'" );
	
	$xml = new xmlParser( stripslashes( $rss['url'] ), $rss['max_news'] );
	
	if( $xml->rss_option == "UTF-8" ) $xml->convert( "UTF-8", strtolower( $config['charset'] ) );
	elseif( $xml->rss_charset != strtolower( $config['charset'] ) ) $xml->convert( $xml->rss_charset, strtolower( $config['charset'] ) );
	
	$xml->pre_lastdate = $rss['lastdate'];
	
	$xml->pre_parse( $rss['date'] );
	
	$i = 0;
	
	foreach ( $xml->content as $content ) {
		if( $rss['text_type'] ) {
			$xml->content[$i]['title'] = $parse->decodeBBCodes( $xml->content[$i]['title'], false );
			$xml->content[$i]['description'] = $parse->decodeBBCodes( $xml->content[$i]['description'], false );
			$xml->content[$i]['date'] = date( "Y-m-d H:i:s", $xml->content[$i]['date'] );
		
		} else {
			$xml->content[$i]['title'] = $parse->decodeBBCodes( $xml->content[$i]['title'], false );
			$xml->content[$i]['description'] = $parse->decodeBBCodes( $xml->content[$i]['description'], true, "yes" );
			$xml->content[$i]['date'] = date( "Y-m-d H:i:s", $xml->content[$i]['date'] );
		}
		$i ++;
	}

	$js_array[] = "engine/skins/calendar.js";
	
	echoheader( "", "" );
	
	echo <<<HTML
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
<script language="javascript" type="text/javascript">

	function doFull( link, news_id, rss_id )
	{

		ShowLoading('');

		$.post('engine/ajax/rss.php', { link: link, news_id: news_id, rss_id: rss_id, rss_charset: "{$xml->rss_charset}" }, function(data){
	
			HideLoading('');
	
			$('#cfull'+ news_id).html(data);
	
		});

	return false;
	}

	function RemoveTable( nummer ) {
	    DLEconfirm( '{$lang['edit_cdel']}', '{$lang['p_confirm']}', function () {
			document.getElementById('ContentTable' + nummer).innerHTML = '';
		} );
	}

	function preview( id )
	{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1');
        document.addnews.target='prv';
		document.addnews.title.value = document.getElementById('title_' + id).value;
		document.addnews.short_story.value = document.getElementById('short_' + id).value;
		if (document.getElementById('full_' + id)) {
		document.addnews.full_story.value = document.getElementById('full_' + id).value;
		} else {
		document.addnews.full_story.value = "";
		}
        document.addnews.submit();
    }
</script>
<form method=post name="addnewsrss" action="?mod=rss&action=addnews">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$rss['url']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;
	
	$i = 0;
	$categories_list = CategoryNewsSelection( $rss['category'], 0 );
	
	if( count( $xml->content ) ) {
		foreach ( $xml->content as $content ) {
			
			echo '<span id="ContentTable' . $i . '"><table width="100%"><tr><td height="20"  style="padding: 5px;" colspan="2">
    <b><a onClick="RemoveTable(' . $i . '); return false;" href="#" ><img src="engine/skins/images/delete.png"  style="vertical-align: middle;border: none;" /></a> <a class="main" href="javascript:ShowOrHide(\'cp' . $i . '\',\'cc' . $i . '\')" >' . $content['title'] . '</a></td>
    </tr>
    <tr id=\'cp' . $i . '\' style=\'display:none\'>
    <td width=200 valign="top" style="padding: 5px"><input class="edit bk" type="text" size="55" id="title_' . $i . '" name="content[' . $i . '][title]" value="' . $content['title'] . '"><br />
	<br /><input type="text" name="content[' . $i . '][date]" id="f_date_c' . $i . '" size="20"  class="edit bk" value="' . $content['date'] . '">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c' . $i . '" style="cursor: pointer; border: 0" title="' . $lang['edit_ecal'] . '"/>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_c' . $i . '",     // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
        button         :    "f_trigger_c' . $i . '",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment 
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script></td>
    <td valign="top" style="padding: 5px"><select name="content[' . $i . '][category][]" id="category" class="cat_select" multiple>
    ' . $categories_list . '</select></td>
    </tr>
    <tr id=\'cc' . $i . '\' style=\'display:none\'>
    <td colspan="2">
    <textarea class="bk" style="width:98%;height:200px;" id="short_' . $i . '" name="content[' . $i . '][short]">' . $content['description'] . '</textarea>
	<div id="cfull' . $i . '">' . htmlspecialchars( $content['link'] ) . '</div>
	<input type="checkbox" name="content[' . $i . '][approve]" value="1" checked>' . $lang['addnews_mod'] . '<br />
	<br /><input onClick="doFull(\'' . urlencode( rtrim( $content['link'] ) ) . '\', \'' . $i . '\', \'' . $rss['id'] . '\')" type="button" class="btn btn-success" value="&nbsp;&nbsp;' . $lang['rss_dofull'] . '&nbsp;&nbsp;">&nbsp;&nbsp;<input onClick="preview(' . $i . ')" type="button" class="btn btn-info" value="&nbsp;&nbsp;' . $lang['btn_preview'] . '&nbsp;&nbsp;">&nbsp;&nbsp;<input onClick="RemoveTable(' . $i . '); return false;" type="button" class="btn btn-danger" value="&nbsp;&nbsp;' . $lang['edit_dnews'] . '&nbsp;&nbsp;"><br /><br />
  </tr><tr><td background="engine/skins/images/mline.gif" height="1" colspan="2"></td></tr></table></span>';
			
			$i ++;
		}
		
		echo <<<HTML
    <br />&nbsp;&nbsp;<input type="submit" value=" {$lang['rss_addnews']} " class="btn btn-primary">
&nbsp;&nbsp;<a href="?mod=rss&action=news&subaction=clear&id={$id}&lastdate={$xml->lastdate}"><input onclick="document.location='?mod=rss&action=news&subaction=clear&id={$id}&lastdate={$xml->lastdate}'" type="button" value=" {$lang['rss_clear']} " class="btn btn-danger"></a>
	<input type=hidden name="allow_main" value="{$rss['allow_main']}">
	<input type=hidden name="allow_rating" value="{$rss['allow_rating']}">
	<input type=hidden name="allow_comm" value="{$rss['allow_comm']}">
	<input type=hidden name="lastdate" value="{$xml->lastdate}">
	<input type=hidden name="id" value="{$id}">
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
	<input type=hidden name="text_type" value="{$rss['text_type']}">
HTML;
	
	} else {
		
		echo "<div style=\"padding:10px;\" align=\"center\">" . $lang['rss_no_rss'] . "<br /><br><a class=main href=\"?mod=rss\">{$lang['func_msg']}</a></div>";
	
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
</div></form>
<form method=post name="addnews" id="addnews">
<input type=hidden name="mod" value="preview">
<input type=hidden name="title" value="">
<input type=hidden name="short_story" value="">
<input type=hidden name="full_story" value="">
<input type=hidden name="allow_br" value="{$rss['text_type']}">
</form>
HTML;
	
	echofooter();

} elseif( $_REQUEST['action'] == "doadd" or $_REQUEST['action'] == "doedit" ) {
	
	$url = $db->safesql( trim( $_REQUEST['rss_url'] ) );
	$description = $db->safesql( trim( $_REQUEST['rss_descr'] ) );
	
	$max_news = intval( $_REQUEST['rss_maxnews'] );
	$allow_main = intval( $_REQUEST['allow_main'] );
	$allow_rating = intval( $_REQUEST['allow_rating'] );
	$allow_comm = intval( $_REQUEST['allow_comm'] );
	$text_type = intval( $_REQUEST['text_type'] );
	$date = intval( $_REQUEST['rss_date'] );
	$category = intval( $_REQUEST['category'] );
	
	$search = $db->safesql( trim( $_REQUEST['rss_search'] ) );
	$cookies = $db->safesql( trim( $_REQUEST['rss_cookie'] ) );
	
	if( $url == "" ) msg( "error", $lang['addnews_error'], $lang['rss_err1'], "javascript:history.go(-1)" );
	
	if( $_REQUEST['action'] == "doadd" ) {
		$db->query( "INSERT INTO " . PREFIX . "_rss (url, description, allow_main, allow_rating, allow_comm, text_type, date, search, max_news, cookie, category) values ('$url', '$description', '$allow_main', '$allow_rating', '$allow_comm', '$text_type', '$date', '$search', '$max_news', '$cookies', '$category')" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '51', '{$url}')" );
		msg( "info", $lang['all_info'], $lang['rss_ok1'], "$PHP_SELF?mod=rss" );
	} else {
		$db->query( "UPDATE " . PREFIX . "_rss set url='$url', description='$description', allow_main='$allow_main', allow_rating='$allow_rating', allow_comm='$allow_comm', text_type='$text_type', date='$date', search='$search', max_news='$max_news', cookie='$cookies', category='$category', lastdate='' WHERE id='{$id}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '52', '{$url}')" );
		msg( "info", $lang['all_info'], $lang['rss_ok2'], "$PHP_SELF?mod=rss" );
	}

} elseif( $_REQUEST['action'] == "add" or $_REQUEST['action'] == "edit" ) {
	
	function makeDropDown($options, $name, $selected) {
		$output = "<select name=\"$name\">\r\n";
		foreach ( $options as $value => $description ) {
			$output .= "<option value=\"$value\"";
			if( $selected == $value ) {
				$output .= " selected ";
			}
			$output .= ">$description</option>\n";
		}
		$output .= "</select>";
		return $output;
	}
	
	echoheader( "", "" );
	
	if( $action == "add" ) {
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", "1" );
		$text_type = makeDropDown( array ("1" => "BBCODES", "0" => "HTML" ), "text_type", "1" );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", "1" );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", "1" );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", "1" );
		
		$rss_search_value = "<html>{get}</html>";
		$rss_maxnews_value = 5;
		
		$categories_list = CategoryNewsSelection( 0, 0 );
		$rss_info = $lang['rss_new'];
		$submit_value = $lang['rss_new'];
		$form_action = "$PHP_SELF?mod=rss&amp;action=doadd";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_rss WHERE id='$id'" );
		
		$rss_date = makeDropDown( array ("1" => $lang['rss_date_1'], "0" => $lang['rss_date_2'] ), "rss_date", $row['date'] );
		$text_type = makeDropDown( array ("1" => "BBCODES", "0" => "HTML" ), "text_type", $row['text_type'] );
		
		$allow_main = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_main", $row['allow_main'] );
		$allow_rating = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_rating", $row['allow_rating'] );
		$allow_comm = makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "allow_comm", $row['allow_comm'] );
		
		$rss_search_value = htmlspecialchars( stripslashes( $row['search'] ) );
		$rss_maxnews_value = $row['max_news'];
		
		$categories_list = CategoryNewsSelection( $row['category'], 0 );
		$rss_info = $row['url'];
		$submit_value = $lang['user_save'];
		$rss_url_value = htmlspecialchars( stripslashes( $row['url'] ) );
		$rss_descr_value = htmlspecialchars( stripslashes( $row['description'] ) );
		$rss_cookie_value = htmlspecialchars( stripslashes( $row['cookie'] ) );
		
		$form_action = "$PHP_SELF?mod=rss&amp;action=doedit&amp;id=" . $id;
	}
	
	echo <<<HTML
<form action="{$form_action}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$rss_info}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="240" style="padding:4px;">{$lang['rss_url']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><input class="edit bk" type="text" size="25" name="rss_url" value="{$rss_url_value}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[rss_hurl]}', this, event, '220px')">[?]</a></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_descr']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><input class="edit bk" type="text" size="55" name="rss_descr" value="{$rss_descr_value}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[rss_hdescr]}', this, event, '220px')">[?]</a></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_maxnews']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><input class="edit bk" type="text" size="5" name="rss_maxnews" value="{$rss_maxnews_value}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[rss_hmaxnews]}', this, event, '220px')">[?]</a></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['xfield_xcat']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><select name="category">
{$categories_list}
</select></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_date']}</td>
        <td style="padding-top:2px;padding-bottom:2px;">{$rss_date}</td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_main']}</td>
        <td style="padding-top:2px;padding-bottom:2px;">{$allow_main}</td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_rating']}</td>
        <td style="padding-top:2px;padding-bottom:2px;">{$allow_rating}</td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_comm']}</td>
        <td style="padding-top:2px;padding-bottom:2px;">{$allow_comm}</td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_text_type']}</td>
        <td style="padding-top:2px;padding-bottom:2px;">{$text_type}</td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_search']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><textarea cols="50" rows="5" class="edit" name="rss_search">{$rss_search_value}</textarea><a href="#" class="hintanchor" onMouseover="showhint('{$lang[rss_hsearch]}', this, event, '300px')">[?]</a></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:4px;">{$lang['rss_cookie']}</td>
        <td style="padding-top:2px;padding-bottom:2px;"><textarea cols="50" rows="5" class="edit" name="rss_cookie">{$rss_cookie_value}</textarea><a href="#" class="hintanchor" onMouseover="showhint('{$lang[rss_hcookie]}', this, event, '300px')">[?]</a></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
   <tr>
        <td style="padding:4px;"><input class="btn btn-success" type="submit" value=" $submit_value "></td>
        <td>&nbsp;</td>
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
	
	if( $_REQUEST['action'] == "del" and $id ) {
		
		if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
			
			die( "Hacking attempt! User not found" );
		
		}
		
		$db->query( "DELETE FROM " . PREFIX . "_rss WHERE id = '$id'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '50', '{$id}')" );

	}
	
	echoheader( "", "" );
	
	$db->query( "SELECT id, url, description FROM " . PREFIX . "_rss ORDER BY id DESC" );
	
	while ( $row = $db->get_row() ) {
		$row['description'] = stripslashes( $row['description'] );
		$entries .= "
    <tr>
    <td height=22 class=\"list\">&nbsp;&nbsp;<b>{$row['id']}</b></td>
    <td class=\"list\">{$row['url']}</td>
    <td class=\"list\">{$row['description']}</td>
    <td class=\"list\" align=\"center\"><a onClick=\"return dropdownmenu(this, event, MenuBuild('" . $row['id'] . "'), '150px')\" href=\"#\"><img src=\"engine/skins/images/browser_action.gif\" border=\"0\"></a></td>
     </tr>
	<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";
	}
	$db->free();
	echo <<<HTML
<div style="padding-top:5px;padding-bottom:2px;">
<script language="javascript" type="text/javascript">
<!--
function MenuBuild( m_id ){

var menu=new Array()

menu[0]='<a href="?mod=rss&action=news&id=' + m_id + '" >{$lang['rss_news']}</a>';
menu[1]='<a href="?mod=rss&action=edit&id=' + m_id + '" >{$lang['rss_edit']}</a>';
menu[2]='<a href="?mod=rss&action=del&user_hash={$dle_login_hash}&id=' + m_id + '" >{$lang['rss_del']}</a>';

return menu;
}
//-->
</script>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['rss_list']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">
<table width="100%">
  <tr>
   <td width=50>&nbsp;&nbsp;ID</td>
   <td>{$lang['rss_url']}</td>
   <td>{$lang['rss_descr']}</td>
   <td width=70 align="center">&nbsp;</td>
  </tr>
	<tr><td colspan="4"><div class="hr_line"></div></td></tr>
	{$entries}
	<tr><td colspan="4"><div class="hr_line"></div></td></tr>
  <tr><td colspan="4"><a href="?mod=rss&action=add"><input onclick="document.location='?mod=rss&action=add'" type="button" class="btn btn-primary" value=" {$lang['rss_new']} "></a></td></tr>
</table>
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
</form>
HTML;
	
	echofooter();
}
?>