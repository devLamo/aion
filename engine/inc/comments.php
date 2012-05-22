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
 Файл: comments.php
-----------------------------------------------------
 Назначение: Управления комментариями
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_comments'] ) {
	msg( "error", $lang['addnews_denied'], $lang['addnews_denied'], "$PHP_SELF?mod=editnews&amp;action=list" );
}

$id = intval( $_REQUEST['id'] );

if( $action == "dodelete" AND $id) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$result = $db->query( "SELECT COUNT(*) as count, user_id FROM " . PREFIX . "_comments WHERE post_id='$id' AND is_register='1' GROUP BY user_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-{$row['count']} where user_id='{$row['user_id']}'" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='$id'" );
	$db->query( "UPDATE " . PREFIX . "_post SET comm_num='0' where id ='$id'" );
	
	clear_cache();
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '20', '$id')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "$PHP_SELF?mod=editnews&amp;action=list" );

} elseif( $action == "mass_delete" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $_POST['selected_comments'] ) {
		msg( "error", $lang['mass_error'], $lang['mass_dcomm'], "$PHP_SELF?mod=comments&action=edit&id={$id}" );
	}
	
	foreach ( $_POST['selected_comments'] as $c_id ) {

		$c_id = intval( $c_id );
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_comments where id = '$c_id'" );

		$author = $row['autor'];
		$is_reg = $row['is_register'];
		$post_id = $row['post_id'];

		$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '$c_id'" );
		
		if( $is_reg ) {
			$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 where name ='$author'" );
		}
		
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 where id='$post_id'" );

	}
	
	clear_cache( array('news_', 'full_', 'comm_', 'rss') );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '21', '')" );
	
	msg( "info", $lang['mass_head'], $lang['mass_delokc'], "$PHP_SELF?mod=comments&action=edit&id={$id}" );

} elseif( $action == "edit" ) {
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( );
	$parse->safe_mode = true;

	if ( $id ) $where = "post_id = '{$id}' AND "; else $where = "";

	$start_from = intval( $_GET['start_from'] );
	if( $start_from < 0 ) $start_from = 0;
	$news_per_page = 50;
	$i = $start_from;

	$gopage = intval( $_GET['gopage'] );
	if( $gopage > 0 ) $start_from = ($gopage - 1) * $news_per_page;

	
	echoheader( "", "" );
	
	$entries = "";
	
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE {$where}approve='1'" );

	$db->query( "SELECT id, autor, text, ip FROM " . PREFIX . "_comments WHERE {$where}approve='1' order by date DESC LIMIT $start_from,$news_per_page" );
	
	while ( $row = $db->get_array() ) {
		$i ++;
		
		if( $config['allow_comments_wysiwyg'] == "yes" ) {
			$row['text'] = $parse->decodeBBCodes( $row['text'] );
		} else
			$row['text'] = $parse->decodeBBCodes( $row['text'], false );

		$row['autor'] = "<a onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\">{$row['autor']}</a>";
		$row['ip'] = "<a href=\"?mod=blockip&ip=".urlencode($row['ip'])."\" target=\"_blank\">{$row['ip']}</a>";

		$row['text'] = "<textarea id='edit-comm-{$row['id']}' style=\"width:550px; height:150px;font-family:verdana; font-size:11px; border:1px solid #E0E0E0\" class=\"bk\">" . $row['text'] . "</textarea>";
		
		$entries .= "<tr><td class=\"list\" style=\"padding:4px;\">" . $row['autor'] . "</td>";
		$entries .= "<td class=\"list\">" . stripslashes( $row['ip'] ) . "</td>";
		$entries .= "<td class=\"list\">" . $row['text'] . "</td>";
		$entries .= "<td class=\"list\"><input class=\"btn btn-success\" title=\"$lang[bb_t_apply]\" type=button onclick=\"ajax_save_comm_edit('{$row['id']}'); return false;\" value=\"&nbsp;&nbsp;$lang[bb_b_apply]&nbsp;&nbsp;\"></td>";
		$entries .= "<td class=\"list\"><input name=\"selected_comments[]\" value=\"{$row['id']}\" type='checkbox'></td>";
		$entries .= "<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=5></td></tr>";
	
	}
	
	$db->free();

		// pagination

		$npp_nav = "<div class=\"news_navigation\" style=\"margin-bottom:5px; margin-top:5px;\">";
		
		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<a href=\"?mod=comments&action=edit&id={$id}&start_from={$previous}\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a> ";
		}
		
		if( $result_count['count'] > $news_per_page ) {
			
			$enpages_count = @ceil( $result_count['count'] / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";
			
			if( $enpages_count <= 10 ) {
				
				for($j = 1; $j <= $enpages_count; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$j</a> ";
					
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
					
					$enpages .= "<a href=\"?mod=comments&action=edit&id={$id}&start_from=0\">1</a> ... ";
				
				}
				
				for($j = $start; $j <= $end; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$j</a> ";
					
					} else {
						
						$enpages .= "<span>$j</span> ";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "... <a href=\"?mod=comments&action=edit&id={$id}&start_from={$enpages_start_from}\">$enpages_count</a> ";
				
				$npp_nav .= $enpages;
			
			}
		
		}
		
		if( $result_count['count'] > $i ) {
			$how_next = $result_count['count'] - $i;
			if( $how_next > $news_per_page ) {
				$how_next = $news_per_page;
			}
			$npp_nav .= "<a href=\"?mod=comments&action=edit&id={$id}&start_from={$i}\" title=\"{$lang['edit_next']}\">&gt;&gt;</a>";
		}
		
		$npp_nav .= "</div>";
		
		// pagination

	
	echo <<<HTML
<script language='JavaScript' type="text/javascript">
<!--
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

	ShowLoading('');

	$.post('engine/ajax/editcomments.php', { comm_txt: comm_txt, id: c_id, action: "save" }, function(data){
	
		HideLoading('');
	
	});

	return false;
}

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
    <td width=150>&nbsp;&nbsp;{$lang['edit_autor']}
    <td width=150>IP:
	<td>{$lang['comm_ctext']}
    <td width=190>{$lang['vote_action']}
    <td width=10 class="list"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()">
	</tr>
	<tr><td colspan="5"><div class="hr_line"></div></td></tr>
	{$entries}
	<tr><td colspan="5"><div class="hr_line"></div></td></tr>


<tr>
<td colspan=3 align=left>{$npp_nav}&nbsp;</td>
<td colspan=2 align=right>
<select name=action>
<option value="mass_delete">{$lang['edit_seldel']}</option></select>
<input type=hidden name=mod value="comments">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<input class="btn btn-warning btn-mini" type="submit" value=" {$lang['b_start']} "></td>
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
} else {
	msg( "error", $lang['addnews_denied'], $lang['addnews_denied'], "$PHP_SELF?mod=editnews&amp;action=list" );
}
?>