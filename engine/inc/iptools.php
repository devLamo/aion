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
 Файл: iptools.php
-----------------------------------------------------
 Назначение: Поиск посетителей по IP
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_iptools'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( isset( $_REQUEST['ip'] ) ) $ip = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['ip'] ) ) ) ); else $ip = "";
if( isset( $_REQUEST['name'] ) ) $name = $db->safesql( htmlspecialchars( strip_tags( trim( $_REQUEST['name'] ) ) ) ); else $name = "";

if( $_REQUEST['doaction'] == "dodelcomments" AND $_REQUEST['id']) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$id = intval( $_REQUEST['id'] );
	
	$result = $db->query( "SELECT COUNT(*) as count, post_id FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1' GROUP BY post_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-{$row['count']} where id='{$row['post_id']}'" );
	
	}
	$db->free( $result );
	
	$db->query( "UPDATE " . USERPREFIX . "_users set comm_num='0' WHERE user_id ='$id'" );
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE user_id='$id' AND is_register='1'" );
}
	
echoheader( "", "" );

echo <<<HTML
<form action="?mod=iptools" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_iptoolsc']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="70">{$lang['opt_iptoolsc']}<br /><input class="edit bk" style="width:250px;" type="text" name="ip" value="{$ip}">&nbsp;&nbsp;&nbsp;<input type="submit" value="{$lang['b_find']}" class="btn btn-primary btn-mini"><br /><span class=small>{$lang['opt_ipfe']}</span></td>
    </tr>
    <tr>
        <td style="padding:2px;"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:2px;" height="70">{$lang['opt_iptoolsname']}<br /><input class="edit bk" style="width:250px;" type="text" name="name" value="{$name}">&nbsp;&nbsp;&nbsp;<input type="submit" value="{$lang['b_find']}" class="btn btn-primary btn-mini"></td>
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
<input type="hidden" name="action" value="find">
<input type="hidden" name="mod" value="iptools">
</form>
HTML;

if( $_REQUEST['action'] == "find" and $ip != "" ) {
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function popupedit( id ){

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
				"{$lang['edit_dnews']}": function() { 
					window.frames["edituserframe"].confirmDelete("$PHP_SELF?mod=editusers&action=dodeleteuser&popup=yes&id=" + id + "&user_hash={$dle_login_hash}");
				},
				"{$lang['user_save']}": function() { 
					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();							
				}
			},
			open: function(event, ui) { 
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='400' src='{$PHP_SELF}?mod=editusers&action=edituser&id=" + id + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
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
function cdelete(id){
	    DLEconfirm( '{$lang['comm_alldelconfirm']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=iptools&action=find&ip={$ip}&doaction=dodelcomments&user_hash={$dle_login_hash}&id=' + id + '';
		} );
}
function MenuBuild( m_id ){

var menu=new Array()

menu[0]='<a href="{$config['http_home_url']}index.php?do=lastcomments&userid=' + m_id + '" target="_blank">{$lang['comm_view']}</a>';
menu[1]='<a onClick="javascript:cdelete(' + m_id + '); return(false)" href="?mod=iptools&action=find&ip={$ip}&doaction=dodelcomments&user_hash={$dle_login_hash}&id=' + m_id + '" >{$lang['comm_del']}</a>';

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['ip_found_users']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="170" style="padding:2px;">{$lang['user_name']}</td>
        <td width="110" style="padding:2px;">IP</td>
        <td width="130">{$lang['user_reg']}</td>
        <td width="130">{$lang['user_last']}</td>
        <td width="60">{$lang['user_news']}</td>
        <td width="120" align="center">{$lang['user_coms']}</td>
        <td>{$lang['user_acc']}</td>
    </tr>
	<tr><td colspan="7"><div class="hr_line"></div></td></tr>
HTML;
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_users WHERE logged_ip LIKE '{$ip}%'" );
	
	$i = 0;
	while ( $row = $db->get_array() ) {
		$i ++;
		
		if( $row[news_num] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			$news_link = "[<a href=\"{$config['http_home_url']}index.php?subaction=allnews&user=" . urlencode( $row['name'] ) . "\" target=\"_blank\">" . $row[news_num] . "</a>]";
		}
		if( $row[comm_num] == 0 ) {
			$comms_link = $row['comm_num'];
		} else {
			$comms_link = "[<a onclick=\"return dropdownmenu(this, event, MenuBuild('" . $row['user_id'] . "'), '150px')\" href=\"#\" >" . $row[comm_num] . "</a>]";
		}
		
		if( $row['banned'] == 'yes' ) $group = "<font color=\"red\">" . $lang['user_ban'] . "</font>";
		else $group = $user_group[$row['user_group']]['group_name'];
		
		echo "
        <tr>
        <td style=\"padding:3px\">
        <a class=maintitle onClick=\"javascript:popupedit('$row[user_id]'); return(false)\" href=#>{$row['name']}</a>
        </td>
        <td>
        " . $row['logged_ip'] . "</td>
        <td>
        " . langdate( "d/m/Y - H:i", $row['reg_date'] ) . "</td>
        <td>
        " . langdate( 'd/m/Y - H:i', $row['lastdate'] ) . "</td>
        <td align=\"center\">
        " . $news_link . "</td>
        <td align=\"center\">
        " . $comms_link . "</td>
        <td>
        " . $group . "</td>
        </tr>
	    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=7></td></tr>
        ";
	}
	
	if( $i == 0 ) {
		echo "<tr>
     <td height=18 colspan=7>
       <p align=center><br><b>$lang[ip_empty]<br><br></b>
    </tr>";
	}
	
	echo <<<HTML
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['ip_found_comments']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="170" style="padding:2px;">{$lang['user_name']}</td>
        <td width="110" style="padding:2px;">IP</td>
        <td width="130">{$lang['user_reg']}</td>
        <td width="130">{$lang['user_last']}</td>
        <td width="60">{$lang['user_news']}</td>
        <td width="120" align="center">{$lang['user_coms']}</td>
        <td>{$lang['user_acc']}</td>
    </tr>
	<tr><td colspan="7"><div class="hr_line"></div></td></tr>
HTML;
	
	$db->query( "SELECT " . PREFIX . "_comments.user_id, " . PREFIX . "_comments.ip, " . USERPREFIX . "_users.comm_num, banned, user_group, reg_date, lastdate, " . USERPREFIX . "_users.name, " . USERPREFIX . "_users.news_num FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.ip LIKE '{$ip}%' AND " . PREFIX . "_comments.is_register = '1' AND " . USERPREFIX . "_users.name != '' GROUP BY " . PREFIX . "_comments.user_id" );
	
	$i = 0;
	while ( $row = $db->get_array() ) {
		$i ++;
		
		if( $row[news_num] == 0 ) {
			$news_link = "$row[news_num]";
		} else {
			$news_link = "[<a href=\"{$config['http_home_url']}index.php?subaction=userinfo&user=" . urlencode( $row['name'] ) . "\" target=\"_blank\">" . $row[news_num] . "</a>]";
		}
		if( $row[comm_num] == 0 ) {
			$comms_link = $row['comm_num'];
		} else {
			$comms_link = "[<a onClick=\"return dropdownmenu(this, event, MenuBuild('" . $row['user_id'] . "'), '150px')\" href=\"#\" >" . $row[comm_num] . "</a>]";
		}
		
		if( $row['banned'] == 'yes' ) $group = "<font color=\"red\">" . $lang['user_ban'] . "</font>";
		else $group = $user_group[$row['user_group']]['group_name'];
		
		echo "
        <tr>
        <td style=\"padding:3px\">
        <a class=maintitle onClick=\"javascript:popupedit('$row[user_id]'); return(false)\" href=#>{$row['name']}</a>
        </td>
        <td>
        " . $row['ip'] . "</td>
        <td>
        " . langdate( "d/m/Y - H:i", $row['reg_date'] ) . "</td>
        <td>
        " . langdate( 'd/m/Y - H:i', $row['lastdate'] ) . "</td>
        <td align=\"center\">
        " . $news_link . "</td>
        <td align=\"center\">
        " . $comms_link . "</td>
        <td>
        " . $group . "</td>
        </tr>
	    <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=7></td></tr>
        ";
	}
	
	if( $i == 0 ) {
		echo "<tr>
     <td height=18 colspan=7>
       <p align=center><br><b>$lang[ip_empty]<br><br></b>
    </tr>";
	}
	
	echo <<<HTML
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

if( $name != "" ) {
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
function MenuIPBuild( m_id ){

var menu=new Array()

menu[0]='<a href="https://www.nic.ru/whois/?ip=' + m_id + '" target="_blank">{$lang['ip_info']}</a>';
menu[1]='<a href="?mod=blockip&ip=' + m_id + '" target="_blank">{$lang['ip_ban']}</a>';

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_iptoolsname']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" height="70">
HTML;
	
	$row = $db->super_query( "SELECT user_id, name, logged_ip FROM " . USERPREFIX . "_users WHERE name='" . $name . "'" );
	
	if( ! $row['user_id'] ) {
		
		echo "<center><b>" . $lang['user_nouser'] . "</b></center>";
	
	} else {
		
		echo $lang['user_name'] . " <b>" . $row['name'] . "</b><br /><br />" . $lang['opt_iptoollast'] . "<a onclick=\"return dropdownmenu(this, event, MenuIPBuild('" . $row['logged_ip'] . "'), '190px')\" href=\"https://www.nic.ru/whois/?ip=" . $row['logged_ip'] . "\" target=\"_blank\"> <b>" . $row['logged_ip'] . "</b></a><br /><br />" . $lang['opt_iptoolcall'] . " <b>";
		
		$db->query( "SELECT ip FROM " . PREFIX . "_comments WHERE user_id = '{$row['user_id']}' GROUP BY ip" );
		
		$ip_list = array ();
		
		while ( $row = $db->get_array() ) {
			$ip_list[] = "<a onclick=\"return dropdownmenu(this, event, MenuIPBuild('" . $row['ip'] . "'), '190px')\" href=\"https://www.nic.ru/whois/?ip=" . $row['ip'] . "\" target=\"_blank\">" . $row['ip'] . "</a>";
		}
		
		echo implode( ", ", $ip_list );
	}
	
	echo <<<HTML
	</b></td>
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