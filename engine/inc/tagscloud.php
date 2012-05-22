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
 Файл: tagscloud.php
-----------------------------------------------------
 Назначение: управление облаком тегов
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( !$user_group[$member_id['user_group']]['admin_tagscloud'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

function compare_tags($a, $b) {
	
	if( $a['tag'] == $b['tag'] ) return 0;
	
	return strcasecmp( $a['tag'], $b['tag'] );

}

$start_from = intval( $_REQUEST['start_from'] );
$news_per_page = 50;

if( $start_from < 0 ) $start_from = 0;

if ($_POST['action'] == "mass_delete") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$selected_tags = $_POST['selected_tags'];

	if( ! $selected_tags ) {
		msg( "error", $lang['mass_error'], $lang['mass_tags_err'], "?mod=tagscloud&start_from={$start_from}" );
	}

	foreach ( $selected_tags as $name ) {

		if( @preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $name ) ) $name = "";
		else $name = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $name ) ) ), ENT_QUOTES ) );

		if (!$name) { msg( "error", $lang['mass_error'], $lang['mass_tags_err_1'], "?mod=tagscloud&start_from={$start_from}" ); die(); }

		$db->query ( "SELECT news_id FROM " . PREFIX . "_tags WHERE tag = '{$name}'" );

		$tag_array = array ();
				
		while ( $row = $db->get_row () ) {
					
			$tag_array[] = $row['news_id'];
				
		}
		$db->free ();

		if (count ( $tag_array )) {
					
			$tag_array = "(" . implode ( ",", $tag_array ) . ")";
	
			$sql_result = $db->query( "SELECT id, tags FROM " . PREFIX . "_post WHERE id IN {$tag_array}" );
	
			while ( $row = $db->get_row( $sql_result ) ) {
	
				$row['tags'] = explode( ",", $row['tags'] );
	
				$tags = array ();
				
				foreach ( $row['tags'] as $value ) {
					
					$value = trim( $value );
					if ( $value == $name ) continue;
					$tags[] = $value;
				}
	
				$tags = array_unique($tags);
	
				if ( count($tags) ) $post_tags = implode( ", ", $tags ); else $post_tags = "";
	
				$db->query( "UPDATE " . PREFIX . "_post SET tags='{$post_tags}' WHERE id='{$row['id']}'" );
	
				$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['id']}'" );
	
				if ( count($tags) ) {
	
					$tagcloud = array ();
		
					foreach ( $tags as $value ) {
									
						$tagcloud[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
					}
		
					$tagcloud = implode( ", ", $tagcloud );
					$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tagcloud );
				}
			}
	
			$db->query( "DELETE FROM " . PREFIX . "_tags WHERE tag = '{$name}'" );
			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '61', '{$name}')" );

		}

	}

	clear_cache();
	header( "Location: ?mod=tagscloud&start_from={$start_from}" ); die();

}


if ($_GET['action'] == "delete") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$_GET['name'] = convert_unicode( urldecode ( $_GET['name'] ), $config['charset']  );

	if( @preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_GET['name'] ) ) $_GET['name'] = "";
	else $_GET['name'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_GET['name'] ) ) ), ENT_QUOTES ) );

	if (!$_GET['name']) { header( "Location: ?mod=tagscloud" ); die(); }

	$db->query ( "SELECT news_id FROM " . PREFIX . "_tags WHERE tag = '{$_GET['name']}'" );
			
	$tag_array = array ();
			
	while ( $row = $db->get_row () ) {
				
		$tag_array[] = $row['news_id'];
			
	}
	$db->free ();

	if (count ( $tag_array )) {
				
		$tag_array = "(" . implode ( ",", $tag_array ) . ")";

		$sql_result = $db->query( "SELECT id, tags FROM " . PREFIX . "_post WHERE id IN {$tag_array}" );

		while ( $row = $db->get_row( $sql_result ) ) {

			$row['tags'] = explode( ",", $row['tags'] );

			$tags = array ();
			
			foreach ( $row['tags'] as $value ) {
				
				$value = trim( $value );
				if ( $value == $_GET['name'] ) continue;
				$tags[] = $value;
			}

			$tags = array_unique($tags);

			if ( count($tags) ) $post_tags = implode( ", ", $tags ); else $post_tags = "";

			$db->query( "UPDATE " . PREFIX . "_post SET tags='{$post_tags}' WHERE id='{$row['id']}'" );

			$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['id']}'" );

			if ( count($tags) ) {

				$tagcloud = array ();
	
				foreach ( $tags as $value ) {
								
					$tagcloud[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
				}
	
				$tagcloud = implode( ", ", $tagcloud );
				$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tagcloud );
			}
		}

		$db->query( "DELETE FROM " . PREFIX . "_tags WHERE tag = '{$_GET['name']}'" );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '61', '{$_GET['name']}')" );

	}

	clear_cache();
	header( "Location: ?mod=tagscloud&start_from={$start_from}" ); die();
}

if ($_GET['action'] == "edit") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$_GET['oldname'] = convert_unicode( urldecode ( $_GET['oldname'] ), $config['charset']  );
	$_GET['newname'] = convert_unicode( urldecode ( $_GET['newname'] ), $config['charset']  );

	if( @preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_GET['oldname'] ) ) $_GET['oldname'] = "";
	else $_GET['oldname'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_GET['oldname'] ) ) ), ENT_QUOTES ) );

	if( @preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_GET['newname'] ) ) $_GET['newname'] = "";
	else $_GET['newname'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_GET['newname'] ) ) ), ENT_QUOTES ) );

	$_GET['newname'] = str_replace (",", " ", $_GET['newname']);

	if (!$_GET['oldname'] OR !$_GET['newname']) { header( "Location: ?mod=tagscloud" ); die(); }

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '62', '{$_GET['oldname']} to: {$_GET['newname']}')" );

	$db->query ( "SELECT news_id FROM " . PREFIX . "_tags WHERE tag = '{$_GET['oldname']}'" );
			
	$tag_array = array ();
			
	while ( $row = $db->get_row () ) {
				
		$tag_array[] = $row['news_id'];
			
	}
	$db->free ();

	if (count ( $tag_array )) {
				
		$tag_array = "(" . implode ( ",", $tag_array ) . ")";

		$sql_result = $db->query( "SELECT id, tags FROM " . PREFIX . "_post WHERE id IN {$tag_array}" );

		while ( $row = $db->get_row( $sql_result ) ) {

			$row['tags'] = explode( ",", $row['tags'] );

			$tags = array ();
			
			foreach ( $row['tags'] as $value ) {
				
				$value = trim( $value );
				if ( $value == $_GET['oldname'] ) $value = $_GET['newname'];
				$tags[] = $value;
			}

			if ( count($tags) ) { 

				$tags = array_unique($tags);
				$post_tags = implode( ", ", $tags );

			} else $post_tags = "";

			$db->query( "UPDATE " . PREFIX . "_post SET tags='{$post_tags}' WHERE id='{$row['id']}'" );

			$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['id']}'" );

			if ( count($tags) ) {

				$tagcloud = array ();
	
				foreach ( $tags as $value ) {
								
					$tagcloud[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
				}

				$tagcloud = implode( ", ", $tagcloud );
				$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tagcloud );

			}
		}
	}

	clear_cache();
	header( "Location: ?mod=tagscloud&start_from={$start_from}" ); die();

}

echoheader("", "");

echo <<<HTML
<form action="?mod=tagscloud" method="get" name="navi" id="navi">
<input type="hidden" name="mod" value="tagscloud">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
</form>
<form action="?mod=tagscloud" method="post" name="optionsbar" id="optionsbar">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_tagscloud']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;


$tags = array();
$list = array();

$i = $start_from;

$db->query("SELECT SQL_CALC_FOUND_ROWS tag, COUNT(*) AS count FROM " . PREFIX . "_tags GROUP BY tag LIMIT {$start_from},{$news_per_page}");

while($row = $db->get_row()){

	$tags[$row['tag']] = $row['count'];
	$i ++;
}
$db->free();

$result_count = $db->super_query("SELECT FOUND_ROWS() as count");
$all_count_news = $result_count['count'];


		// pagination

		$npp_nav = "<div class=\"news_navigation\" style=\"margin-bottom:5px; margin-top:5px;\">";
		
		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<a onClick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a> ";
		}
		
		if( $all_count_news > $news_per_page ) {
			
			$enpages_count = @ceil( $all_count_news / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";
			
			if( $enpages_count <= 10 ) {
				
				for($j = 1; $j <= $enpages_count; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a> ";
					
					} else {
						
						$enpages .= "<span>$j</span> ";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$npp_nav .= $enpages;
			
			} else {
				
				$start = 1;
				$end = 10;
				
				if( $start_from > 0 ) {
					
					if( ($start_from / $news_per_page) > 4 ) {
						
						$start = @ceil( $start_from / $news_per_page ) - 3;
						$end = $start + 9;
						
						if( $end > $enpages_count ) {
							$start = $enpages_count - 10;
							$end = $enpages_count - 1;
						}
						
						$enpages_start_from = ($start - 1) * $news_per_page;
					
					}
				
				}
				
				if( $start > 2 ) {
					
					$enpages .= "<a onClick=\"javascript:search_submit(0); return(false);\" href=\"#\">1</a> ... ";
				
				}
				
				for($j = $start; $j <= $end; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a> ";
					
					} else {
						
						$enpages .= "<span>$j</span> ";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "... <a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a> ";
				
				$npp_nav .= $enpages;
			
			}
		
		}
		
		if( $all_count_news > $i ) {
			$how_next = $all_count_news - $i;
			if( $how_next > $news_per_page ) {
				$how_next = $news_per_page;
			}
			$npp_nav .= "<a onClick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\">&gt;&gt;</a>";
		}
		
		$npp_nav .= "</div>";
		
		// pagination

$i = 0;

if ( count($tags) ) {

	foreach ($tags as $tag => $value) {
	
		$list[$tag]['tag']   = $tag;
		$list[$tag]['count']  = $value;
	
	}
	usort ($list, "compare_tags");

	$i = 0;
	$entries = "";

	foreach ($list as $value) {

		if (trim($value['tag']) != "" ) {

		$i ++;

		if( $config['allow_alt_url'] == "yes" ) $link = "<a href=\"" . $config['http_home_url'] . "tags/" . urlencode( $value['tag'] ) . "/\" target=\"_blank\">" . $lang['comm_view'] . "</a>";
		else $link = "<a href=\"{$config['http_home_url']}index.php?do=tags&amp;tag=" . urlencode( $value['tag'] ) . "\" target=\"_blank\">" . $lang['comm_view'] . "</a>";

		$entries .= "<tr>
        <td style=\"padding:4px;\" nowrap><div id=\"content_{$i}\">{$value['tag']}</div></td>
        <td align=left><b>{$value['count']}</b></td>
        <td align=center>[&nbsp;{$link}&nbsp;]&nbsp;&nbsp;[&nbsp;<a uid=\"{$i}\" class=\"editlink\" href=\"?mod=tagscloud\">{$lang['word_ledit']}</a>&nbsp;]&nbsp;&nbsp;[&nbsp;<a uid=\"{$i}\" class=\"dellink\" href=\"?mod=tagscloud\">{$lang['word_ldel']}</a>&nbsp;]</td>
        <td align=center><input name=\"selected_tags[]\" value=\"{$value['tag']}\" type=\"checkbox\"></td>
        </tr>
        <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

		}

	}

echo <<<HTML
<table width="100%" id="tagslist">
	<tr class="thead">
    <th width="350" style="padding:2px;">{$lang['tagscloud_name']}</th>
    <th>{$lang['tagscloud_count']}</th>
    <th width="100" align="center"><div style="text-align: center;">&nbsp;{$lang['user_action']}&nbsp;</div></th>
    <th width="30" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	<tr class="tfoot"><th colspan="2">{$npp_nav}</th><th colspan="2" valign="top">
<div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>&nbsp;<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}"></div></th></tr>
</table>

<script type="text/javascript">
$(function(){

		$("#tagslist").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink').click(function(){

			tag_name = $('#content_'+$(this).attr('uid')).text();

		    DLEconfirm( '{$lang['tagscloud_del']} <b>&laquo;'+tag_name+'&raquo;</b> {$lang['tagscloud_del_1']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=tagscloud&start_from={$start_from}&user_hash={$dle_login_hash}&action=delete&name=' + encodeURIComponent(tag_name) + '';

			} );

			return false;
		});


		$('.editlink').click(function(){

			tag_name = $('#content_'+$(this).attr('uid')).text();

			DLEprompt('{$lang['tagscloud_edit_1']}', tag_name, '{$lang['tagscloud_edit']}', function (r) {
				if (tag_name != r) {	
					document.location='?mod=tagscloud&start_from={$start_from}&user_hash={$dle_login_hash}&action=edit&oldname=' + encodeURIComponent(tag_name) + '&newname=' + encodeURIComponent(r);
				}		
			});

			return false;
		});

});
</script>
HTML;


}  else {

echo <<<HTML
<table width="100%">
    <tr>
        <td style="padding:2px;height:50px;"><div align="center">{$lang['tagscloud_not_found']}<br /><br> <a class="main" href="javascript:history.go(-1)">{$lang['func_msg']}</a></div></td>
    </tr>
</table>
HTML;

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
</div>
<input type="hidden" name="mod" value="tagscloud">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
</form>
<script language="javascript" type="text/javascript">  
<!-- 
    function search_submit(prm){
      document.navi.start_from.value=prm;
      document.navi.submit();
      return false;
    }

	function ckeck_uncheck_all() {
	    var frm = document.optionsbar;
	    for (var i=0;i<frm.elements.length;i++) {
	        var elmnt = frm.elements[i];
	        if (elmnt.type=='checkbox') {
	            if(frm.master_box.checked == true){ elmnt.checked=false; }
	            else{ elmnt.checked=true; }
	        }
	    }
	    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
	    else{ frm.master_box.checked = true; }
	}
//-->
</script>
HTML;


echofooter();
?>