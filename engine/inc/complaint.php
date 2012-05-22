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
 Файл: complaint.php
-----------------------------------------------------
 Назначение: управление жалобами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( !$user_group[$member_id['user_group']]['admin_complaint'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if ($_GET['action'] == "delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$id = intval($_GET['id']);

	$db->query( "DELETE FROM " . PREFIX . "_complaint WHERE id = '{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=complaint" ); die();
}

if ($_POST['action'] == "mass_delete") {
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$selected_complaint = $_POST['selected_complaint'];

	if( ! $selected_complaint ) {
		msg( "error", $lang['mass_error'], $lang['opt_complaint_6'], "?mod=complaint" );
	}

	foreach ( $selected_complaint as $complaint ) {

		$complaint = intval($complaint);

		$db->query( "DELETE FROM " . PREFIX . "_complaint WHERE id = '{$complaint}'" );
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '22', '')" );

	header( "Location: ?mod=complaint" ); die();
}

$found = false;

echoheader("", "");

	echo <<<HTML
<script type="text/javascript">
<!-- begin
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
// end -->
</script>
HTML;

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar" id="optionsbar">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_complaint_1']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;



$db->query("SELECT `id`, `p_id`, `text`, `from`, `to`  FROM " . PREFIX . "_complaint WHERE p_id > '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);

	$from = "<a onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	$to = "<a onclick=\"javascript:popupedit('".urlencode($row['to'])."'); return(false)\" href=\"#\">{$row['to']}</a>, <a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['to'])."\" target=\"_blank\">{$lang['send_pm']}</a>";

	$entries .= "<tr>
	<td style=\"padding:4px;\" nowrap><b>{$from}</b></td>
    <td align=left><br /> {$lang['opt_complaint_4']} <b>{$to}</b><br /><br />{$row['text']}<br /><br /></td>
    <td align=center>[&nbsp;<a uid=\"{$row['id']}\" class=\"dellink1\" href=\"?mod=complaint\">{$lang['opt_complaint_11']}</a>&nbsp;]</td>
    <td align=center><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>
    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

}


echo <<<HTML
<table width="100%" id="list1">
	<tr class="thead">
    <th width="150" style="padding:2px;">{$lang['opt_complaint_3']}</th>
    <th>{$lang['opt_complaint_2']}</th>
    <th width="150" align="center"><div style="text-align: center;">&nbsp;{$lang['user_action']}&nbsp;</div></th>
    <th width="30" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	<tr class="tfoot"><th colspan="4" valign="top">
<div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>&nbsp;<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}"></div></th></tr>
</table>

<script type="text/javascript">
$(function(){

		$("#list1").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink1').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
});
</script>
HTML;


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
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
</form>
<script language="javascript" type="text/javascript">  
<!-- 

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

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE c_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar2" id="optionsbar2">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_complaint_15']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;



$db->query("SELECT " . PREFIX . "_complaint.id, `c_id`, " . PREFIX . "_complaint.text, `from`, `to`, " . PREFIX . "_comments.autor, is_register, post_id, " . PREFIX . "_comments.text as c_text, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category FROM " . PREFIX . "_complaint LEFT JOIN " . PREFIX . "_comments ON " . PREFIX . "_complaint.c_id=" . PREFIX . "_comments.id LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id WHERE c_id > '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);

	if ($row['c_text']) {

		$row['c_text'] = "<div class=\"quote\">" . stripslashes( $row['c_text'] ) . "</div>";
		$edit_link = "<br /><br />[&nbsp;<a href=\"" . $config['http_home_url'] . "index.php?do=comments&amp;action=comm_edit&amp;id=" . $row['c_id'] ."\" target=\"_blank\">{$lang['opt_complaint_12']}</a>&nbsp;]";
		$del_c_link = "<br /><br />[&nbsp;<a href=\"javascript:DeleteComments('{$row['c_id']}')\">{$lang['opt_complaint_13']}</a>&nbsp;]";

	} else {

		$row['c_text'] = "<div class=\"quote\">" .$lang['opt_complaint_10']. "</div>";
		$edit_link = "";
		$del_c_link = "";
	}

	$from = "<a onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";

	if($row['is_register'])
		$to = "<a onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\">{$row['autor']}</a>, <a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['autor'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	else $to = $row['autor'];

	$row['category'] = intval( $row['category'] );

	if( $config['allow_alt_url'] == "yes" ) {
					
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
						
			if( $row['category'] and $config['seo_type'] == 2 ) {
							
				$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			} else {
							
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			}
					
		} else {
						
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime ($row['newsdate']) ) . $row['alt_name'] . ".html";
		}
				
	} else {
					
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}

	$full_link = "<a href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";

	$entries .= "<tr>
	<td style=\"padding:4px;\" nowrap><b>{$from}</b></td>
    <td align=left><br />{$lang['opt_complaint_7']} {$full_link}<br /><br />{$lang['opt_complaint_8']} <b>{$to}</b><br /><br /><b>{$lang['opt_complaint_9']}</b><br />{$row['c_text']}<b>{$lang['opt_complaint_2']}</b><br />{$row['text']}<br /><br /></td>
    <td align=center>[&nbsp;<a uid=\"{$row['id']}\" class=\"dellink2\" href=\"?mod=complaint\">{$lang['opt_complaint_11']}</a>&nbsp;]{$edit_link}{$del_c_link}</td>
    <td align=center><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>
    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

}


echo <<<HTML
<table width="100%" id="list2">
	<tr class="thead">
    <th width="150" style="padding:2px;">{$lang['opt_complaint_3']}</th>
    <th>{$lang['opt_complaint_2']}</th>
    <th width="200" align="center"><div style="text-align: center;">&nbsp;{$lang['user_action']}&nbsp;</div></th>
    <th width="30" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all2()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	<tr class="tfoot"><th colspan="4" valign="top">
<div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>&nbsp;<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}"></div></th></tr>
</table>

<script type="text/javascript">
$(function(){

		$("#list2").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink2').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
});
</script>
HTML;


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
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all2() {
	    var frm = document.optionsbar2;
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

function DeleteComments(id) {

    DLEconfirm( '{$lang['opt_complaint_13']}?', '{$lang['p_confirm']}', function () {

		ShowLoading('');
	
		$.get("engine/ajax/deletecomments.php", { id: id, dle_allow_hash: '{$dle_login_hash}' }, function(r){
	
			HideLoading('');
	
			r = parseInt(r);
		
			if (!isNaN(r)) {
		
				DLEalert('$lang[opt_complaint_14]', '$lang[p_info]');
				
			}
	
		});

	} );

};

//-->
</script>
HTML;

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE n_id > '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar3" id="optionsbar3">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_complaint_16']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;



$db->query("SELECT " . PREFIX . "_complaint.id, `n_id`, " . PREFIX . "_complaint.text, `from`, `to`,  " . PREFIX . "_post.id as post_id, " . PREFIX . "_post.title, " . PREFIX . "_post.date as newsdate, " . PREFIX . "_post.alt_name, " . PREFIX . "_post.category  FROM " . PREFIX . "_complaint LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_complaint.n_id=" . PREFIX . "_post.id WHERE n_id > '0' ORDER BY id DESC");


$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);

	if ($row['post_id']) {

		$edit_link = "<br /><br />[&nbsp;<a href=\"?mod=editnews&amp;action=editnews&amp;id=" . $row['n_id'] ."\" target=\"_blank\">{$lang['opt_complaint_18']}</a>&nbsp;]";

	} else {

		$edit_link = "";
	}

	$from = "<a onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";


	$row['category'] = intval( $row['category'] );

	if( $config['allow_alt_url'] == "yes" ) {
					
		if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
						
			if( $row['category'] and $config['seo_type'] == 2 ) {
							
				$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			} else {
							
				$full_link = $config['http_home_url'] . $row['post_id'] . "-" . $row['alt_name'] . ".html";
						
			}
					
		} else {
						
			$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime ($row['newsdate']) ) . $row['alt_name'] . ".html";
		}
				
	} else {
					
		$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['post_id'];
	
	}

	$full_link = "<a href=\"" . $full_link . "\" target=\"_blank\">" . stripslashes( $row['title'] ) . "</a>";

	$entries .= "<tr>
	<td style=\"padding:4px;\" nowrap><b>{$from}</b></td>
    <td align=left><br />{$lang['opt_complaint_17']} {$full_link}<br /><br /><b>{$lang['opt_complaint_2']}</b><br />{$row['text']}<br /><br /></td>
    <td align=center>[&nbsp;<a uid=\"{$row['id']}\" class=\"dellink3\" href=\"?mod=complaint\">{$lang['opt_complaint_11']}</a>&nbsp;]{$edit_link}</td>
    <td align=center><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>
    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

}


echo <<<HTML
<table width="100%" id="list3">
	<tr class="thead">
    <th width="150" style="padding:2px;">{$lang['opt_complaint_3']}</th>
    <th>{$lang['opt_complaint_2']}</th>
    <th width="200" align="center"><div style="text-align: center;">&nbsp;{$lang['user_action']}&nbsp;</div></th>
    <th width="30" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all3()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	<tr class="tfoot"><th colspan="4" valign="top">
<div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>&nbsp;<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}"></div></th></tr>
</table>

<script type="text/javascript">
$(function(){

		$("#list3").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink3').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
});
</script>
HTML;


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
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all3() {
	    var frm = document.optionsbar3;
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

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint WHERE p_id = '0' AND c_id = '0' AND n_id = '0'" );

if($row['count']) {

echo <<<HTML
<form action="?mod=complaint" method="post" name="optionsbar4" id="optionsbar4">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_complaint_21']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;



$db->query("SELECT `id`, `text`, `from`, `to`  FROM " . PREFIX . "_complaint WHERE p_id = '0' AND c_id = '0' AND n_id = '0' ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()) {

	$found = true;

	$row['text'] = stripslashes($row['text']);

	if (count(explode(".", $row['from'])) != 4 ) $from = "<a onclick=\"javascript:popupedit('".urlencode($row['from'])."'); return(false)\" href=\"#\">{$row['from']}</a><br /><br /><a href=\"" . $config['http_home_url'] . "index.php?do=pm&doaction=newpm&username=".urlencode($row['from'])."\" target=\"_blank\">{$lang['send_pm']}</a>";
	else $from = $row['from'];

	if ( $config['charset'] == "windows-1251") {
		$row['to'] = iconv( "UTF-8", "windows-1251//IGNORE", $row['to'] );
	}

	$to = "<a href=\"{$row['to']}\" target=\"_blank\">{$row['to']}</a>";

	$entries .= "<tr>
	<td style=\"padding:4px;\" nowrap><b>{$from}</b></td>
    <td align=left><br /> {$lang['opt_complaint_22']} <b>{$to}</b><br /><br />{$row['text']}<br /><br /></td>
    <td align=center>[&nbsp;<a uid=\"{$row['id']}\" class=\"dellink4\" href=\"?mod=complaint\">{$lang['opt_complaint_11']}</a>&nbsp;]</td>
    <td align=center><input name=\"selected_complaint[]\" value=\"{$row['id']}\" type=\"checkbox\"></td>
    </tr>
    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

}


echo <<<HTML
<table width="100%" id="list4">
	<tr class="thead">
    <th width="150" style="padding:2px;">{$lang['opt_complaint_3']}</th>
    <th>{$lang['opt_complaint_2']}</th>
    <th width="150" align="center"><div style="text-align: center;">&nbsp;{$lang['user_action']}&nbsp;</div></th>
    <th width="30" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all4()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
	<tr class="tfoot"><th colspan="4" valign="top">
<div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>&nbsp;<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}"></div></th></tr>
</table>

<script type="text/javascript">
$(function(){

		$("#list4").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		var tag_name = '';

		$('.dellink4').click(function(){

			id_comp = $(this).attr('uid');

		    DLEconfirm( '{$lang['opt_complaint_5']}', '{$lang['p_confirm']}', function () {

				document.location='?mod=complaint&user_hash={$dle_login_hash}&action=delete&id=' + id_comp + '';

			} );

			return false;
		});
});
</script>
HTML;


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
<input type="hidden" name="mod" value="complaint">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
</form>
<script language="javascript" type="text/javascript">  
<!-- 

	function ckeck_uncheck_all4() {
	    var frm = document.optionsbar4;
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

}

if (!$found) {


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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_complaint']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;height:50px;"><div align="center">{$lang['opt_complaint_19']}<br /><br> <a class="main" href="javascript:history.go(-1)">{$lang['func_msg']}</a></div></td>
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


}

echofooter();
?>