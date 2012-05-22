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
 Файл: dboption.php
-----------------------------------------------------
 Назначение: работа с базой данных
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

if( isset( $_REQUEST['restore'] ) ) $restore = $_REQUEST['restore']; else $restore = "";

if( $action == "dboption" and count( $_REQUEST['ta'] ) ) {
	$arr = $_REQUEST['ta'];
	reset( $arr );
	
	$tables = "";
	
	while ( list ( $key, $val ) = each( $arr ) ) {
		$tables .= ", `" . $db->safesql( $val ) . "`";
	}
	
	$tables = substr( $tables, 1 );
	if( $_REQUEST['whattodo'] == "optimize" ) {
		$query = "OPTIMIZE TABLE  ";
	} else {
		$query = "REPAIR TABLE ";
	}
	$query .= $tables;

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '23', '')" );

	
	if( $db->query( $query ) ) {
		msg( "info", $lang['db_ok'], $lang['db_ok_1'] . "<br /><br /><a href=$PHP_SELF?mod=dboption>" . $lang['db_prev'] . "</a>" );
	} else {
		msg( "error", $lang['db_err'], $lang['db_err_1'] . "<br /><br /><a href=$PHP_SELF?mod=dboption>" . $lang['db_prev'] . "</a>" );
	}

}

echoheader( "home", $lang['db_info'] );

$tabellen = "";

$db->query( "SHOW TABLES" );
while ( $row = $db->get_array() ) {
	$titel = $row[0];
	if( substr( $titel, 0, strlen( PREFIX ) ) == PREFIX ) {
		$tabellen .= "<option value=\"$titel\" selected>$titel</option>\n";
	}
}
$db->free();

echo <<<HTML
<form action="$PHP_SELF?mod=dboption&action=dboption" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['db_info']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="220" style="padding:2px;"><select style="width:240px;" size="7" name="ta[]" multiple="multiple">{$tabellen}</select> <br />
    <br /><center><input type="submit" id="rest" class="btn btn-primary" value="{$lang['db_action']}" /></center></td>
        <td>    <table width="100%" border="0" cellspacing="1" cellpadding="0">
          <tr>
            <td width="5%"><img src="engine/skins/images/db_optimize.gif" alt="" hspace="3" /></td>
            <td width="5%" nowrap="nowrap"><div align="left">
                <input style="border:0px" type="radio" name="whattodo" checked="checked" value="optimize" />
              </div></td>
            <td class="option"><b>$lang[db_opt]</b><br /><span class="small">$lang[db_opt_i]</span></td>
          </tr>
          <tr>
            <td width="5%"><img src="engine/skins/images/db_repair.gif" alt="" hspace="3" /></td>
            <td width="5%" nowrap="nowrap"><div align="left">
                <input style="border:0px" type="radio" name="whattodo" value="repair" />
              </div></td>
            <td class="option"><b>$lang[db_re]</b><br /><span class="small">$lang[db_re_i]</span></td>
          </tr>
        </table></td>
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

if( function_exists( "bzopen" ) ) {
	$comp_methods[2] = 'BZip2';
}
if( function_exists( "gzopen" ) ) {
	$comp_methods[1] = 'GZip';
}
$comp_methods[0] = $lang['opt_notcompress'];

function fn_select($items, $selected) {
	$select = '';
	foreach ( $items as $key => $value ) {
		$select .= $key == $selected ? "<OPTION VALUE='{$key}' SELECTED>{$value}" : "<OPTION VALUE='{$key}'>{$value}";
	}
	return $select;
}
$comp_methods = fn_select( $comp_methods, '' );

echo <<<HTML
    <SCRIPT LANGUAGE="JavaScript">
    function save(){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dlepopup").remove();
		$("body").append("<div id='dlepopup' title='{$lang['db_back']}' style='display:none'></div>");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 530,
			height: 320,
			dialogClass: "modalfixed",
			buttons: {
				"Ok": function() { 
					$(this).dialog("close");
					$("#dlepopup").remove();							
				} 
			},
			open: function(event, ui) { 
				$("#dlepopup").html("<iframe width='99%' height='220' src='{$PHP_SELF}?mod=dumper&action=backup&comp_method=" + $("#comp_method").val() + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' scrolling='no'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dlepopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }

		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$( '#dlepopup').dialog( "option", "position", ['0','0'] );
		}

		return false;

    }
    </SCRIPT>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['db_back']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['b_method']} <select name="comp_method" id="comp_method">{$comp_methods}</select>&nbsp;&nbsp;<input type="button" class="btn btn-success btn-mini" onclick="save(); return false;" value="{$lang['b_save']}" /></td>
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

define( 'PATH', 'backup/' );

function file_select() {
	$files = array ('' );
	if( is_dir( PATH ) && $handle = opendir( PATH ) ) {
		while ( false !== ($file = readdir( $handle )) ) {
			if( preg_match( "/^.+?\.sql(\.(gz|bz2))?$/", $file ) ) {
				$files[$file] = $file;
			}
		}
		closedir( $handle );
	}
	return $files;
}

$files = fn_select( file_select(), '' );

echo <<<HTML
    <SCRIPT LANGUAGE="JavaScript">
    function dbload(){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dlepopup").remove();
		$("body").append("<div id='dlepopup' title='{$lang['db_load']}' style='display:none'></div>");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 530,
			height: 320,
			dialogClass: "modalfixed",
			buttons: {
				"Ok": function() { 
					$(this).dialog("close");
					$("#dlepopup").remove();							
				} 
			},
			open: function(event, ui) { 
				$("#dlepopup").html("<iframe width='99%' height='220' src='{$PHP_SELF}?mod=dumper&action=restore&file=" + $("#file").val() + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' scrolling='no'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dlepopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$( '#dlepopup' ).dialog( "option", "position", ['0','0'] );
		}

		return false;

    }
    </SCRIPT>
<form action="$PHP_SELF?mod=dumper&action=restore" name="restore" id="restore" method="post" >
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['db_load']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['b_restore']} <select name="file" id="file">{$files}</select>&nbsp;&nbsp;<input type="button" class="btn btn-danger btn-mini" onclick="dbload(); return false;" value="{$lang['b_load']}" /></td>
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