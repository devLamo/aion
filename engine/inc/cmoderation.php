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
 Файл: cmoderation.php
-----------------------------------------------------
 Назначение: Модерация комментариев
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_comments'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'], "?mod=main" );
}

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( );
$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

if( $action == "mass_approve" ) {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( $config['allow_comments_wysiwyg'] == "yes" ) {

		$parse->wysiwyg = true;
		$use_html = true;
		$parse->ParseFilter( Array ('div', 'a', 'span', 'p', 'br' ), Array (), 0, 1 );

	} else $use_html = false;
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_acomm'], "?mod=cmoderation" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		$comments = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['selected_text'][$c_id] ), $use_html ) );
		$post_id = intval( $_POST['post_id'][$c_id] );
		
		$db->query( "UPDATE " . PREFIX . "_comments SET text='{$comments}', approve='1' WHERE id='{$c_id}'" );
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$post_id}'" );
	
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	clear_cache();
	
	msg( "info", $lang['mass_head'], $lang['mass_approve_ok'], "?mod=cmoderation" );

}

if( $action == "mass_delete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_dcomm'], "?mod=cmoderation" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {
		
		$c_id = intval( $c_id );
		
		$row = $db->super_query( "SELECT user_id FROM " . PREFIX . "_comments WHERE id='$c_id'" );
		
		if( $row['user_id'] ) $db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 where user_id='{$row['user_id']}'" );
		
		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id='$c_id'" );
	
	}
	
	clear_cache();
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '19', '')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "?mod=cmoderation" );

}

echoheader( "", "" );

$entries = "";

$db->query( "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.autor, text, ip , " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE " . PREFIX . "_comments.approve = '0' ORDER BY " . PREFIX . "_comments.date DESC" );

while ( $row = $db->get_array() ) {
	
	if( $config['allow_comments_wysiwyg'] == "yes" ) {
		$row['text'] = $parse->decodeBBCodes( $row['text'] );
	} else
		$row['text'] = $parse->decodeBBCodes( $row['text'], false );

	$row['text'] = "<textarea id='edit-comm-{$row['id']}' name=\"selected_text[{$row['id']}]\" style=\"width:98%; height:100px;font-family:verdana; font-size:11px; border:1px solid #E0E0E0\" class=\"bk\">" . $row['text'] . "</textarea><input type=\"hidden\" name=\"post_id[{$row['id']}]\" value=\"{$row['post_id']}\">";
	$row['newsdate'] = strtotime( $row['newsdate'] );
	
	if( $config['allow_alt_url'] == "yes" ) {
		
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
			
			if( intval( $row['category'] ) and $config['seo_type'] == 2 ) {
				
				$full_link = $config['http_home_url'] . get_url( intval( $row['category'] ) ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
			
			} else {
				
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['newsdate'] ) . $row['alt_name'] . ".html";
		}
	
	} else {
		
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}
	
	$news_title = "<a href=\"" . $full_link . "\"  target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";
	$row['autor'] = "<a onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\">{$row['autor']}</a>";
	$row['ip'] = "<a href=\"?mod=blockip&ip=".urlencode($row['ip'])."\" target=\"_blank\">{$row['ip']}</a>";
	
	$entries .= "<span id='table-comm-{$row['id']}'><table width=100%><tr><td class=\"list\" style=\"padding:4px;\" width=120>" . $row['autor'] . "</td>";
	$entries .= "<td class=\"list\" width=100>" . stripslashes( $row['ip'] ) . "</td>";
	$entries .= "<td class=\"list\">{$lang['cmod_n_title']} {$news_title}<br />" . $row['text'] . "</td>";
	$entries .= "<td class=\"list\" width=130><input class=\"btn btn-success\" type=\"button\" style=\"width:110px;\" onclick=\"ajax_save_comm_edit('{$row['id']}'); return false;\" value=\"$lang[bb_b_approve]\"></td>";
	$entries .= "<td class=\"list\" width=20><input name=\"selected_comments[]\" value=\"{$row['id']}\" type='checkbox'></td>";
	$entries .= "<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=5></td></tr></table></span>";

}

$db->free();

echo <<<HTML
<script language='JavaScript' type="text/javascript">
<!--

function popupedit( name ){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dleuserpopup").remove();
		$("body").append("<div id='dleuserpopup' title='{$lang['user_edhead']}' style='display:none'></div>");
	
		$('#dleuserpopup').dialog({
			autoOpen: true,
			width: 560,
			height: 500,
			dialogClass: "modalfixed",
			buttons: {
				"{$lang['user_can']}": function() { 
					$(this).dialog("close");
					$("#dleuserpopup").remove();							
				},
				"{$lang['user_save']}": function() { 
					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();							
				}
			},
			open: function(event, ui) { 
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='400' src='{$PHP_SELF}?mod=editusers&action=edituser&user=" + name + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dleuserpopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$('#dleuserpopup').dialog( "option", "position", ['0','0'] );
		}

		return false;

}

function ckeck_uncheck_all() {
    var frm = document.editnews;
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
-->
</script>
<script language="javascript" type="text/javascript">
<!--
function ajax_save_comm_edit( c_id )
{
	var comm_txt = document.getElementById('edit-comm-'+c_id).value;

	document.getElementById('table-comm-'+c_id).innerHTML = '';

	ShowLoading('');

	$.post('engine/ajax/editcomments.php', { comm_txt: comm_txt, id: c_id, action: "save" }, function(data){
	
		HideLoading('');
	
	});

	return false;
}
//-->
</script>
<form action="" method="post" name="editnews">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['comm_einfo']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td>
	<table width=100%>
	<tr>
    <td width=120>&nbsp;&nbsp;{$lang['edit_autor']}
    <td width=100>IP:
	<td>{$lang['comm_ctext']}
    <td width=130>{$lang['vote_action']}
    <td width=20 class="list"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()">
	</tr>
	<tr><td colspan="5"><div class="hr_line"></div></td></tr></table>
	{$entries}
	<table width=100%><tr><td><div class="hr_line"></div></td></tr>


<tr><td align="right">
<select name="action">
<option value="">{$lang['edit_selact']}</option>
<option value="mass_approve">{$lang['bb_b_approve']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
<input type=hidden name=mod value="cmoderation">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}" />
</tr>

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
</div></form>
HTML;

echofooter();
?>