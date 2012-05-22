<?php
/*
=====================================================
DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
Copyright (c) 2004,2012
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: newsletter.php
-----------------------------------------------------
 Назначение: Отправка массовых сообщений
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( ! $user_group[$member_id['user_group']]['admin_newsletter'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if (isset ($_REQUEST['editor'])) $editor = $_REQUEST['editor']; else $editor = "";
if (isset ($_REQUEST['type'])) $type = $_REQUEST['type']; else $type = "";
if (isset ($_REQUEST['action'])) $action = $_REQUEST['action']; else $action = "";
if (isset ($_REQUEST['a_mail'])) $a_mail = intval($_REQUEST['a_mail']); else $a_mail = "";

if (isset ($_GET['empfanger'])) {

	$empfanger = array ();

	if( !count( $_GET['empfanger'] ) ) {
		$empfanger[] = '0';
	} else {

		foreach ( $_GET['empfanger'] as $value ) {
			$empfanger[] = intval($value);
		}

	}

	if ( $empfanger[0] ) $empfanger = $db->safesql( implode( ',', $empfanger ) ); else $empfanger = "0";

} else $empfanger = "0";

if ($action=="send") {

	include_once ENGINE_DIR.'/classes/parse.class.php';

	$parse = new ParseFilter(Array(), Array(), 1, 1);

	$title = strip_tags(stripslashes($parse->process($_POST['title'])));
	$message = stripslashes($parse->process($_POST['message']));
	$start_from = intval($_GET['start_from']);
	$limit = intval($_GET['limit']);
	$interval = intval($_GET['interval']) * 1000;

	if ($limit < 1) {

		$limit = 20;

	}

	if ($editor == "wysiwyg"){

		$message = $parse->BB_Parse($message);

	} else {

		$message = $parse->BB_Parse($message, false);
	}

	$where = array();

	if ($empfanger) {
	
		$user_list = array(); 
	
		$temp = explode(",", $empfanger); 
	
		foreach ( $temp as $value ) {
			$user_list[] = intval($value);
		}
	
		$user_list = implode( "','", $user_list );
	
		$user_list = "user_group IN ('" . $user_list . "')";
	
	} else $user_list = false;

	if ($user_list) $where[] = $user_list;
	if ($a_mail) $where[] = "allow_mail = '1'";

	if (count($where)) $where = " WHERE ".implode (" AND ", $where);
	else $where = "";

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . USERPREFIX . "_users".$where);

	if ($start_from > $row['count'] OR $start_from < 0) $start_from = 0;

	if ($type == "email")
		$type_send = $lang['bb_b_mail'];
	else
		$type_send = $lang['nl_pm'];

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '47', '{$type_send}')" );


echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset={$config['charset']}" http-equiv="content-type" />
<title>{$lang['nl_seng']}</title>
<style type="text/css">
html,body{
height:100%;
margin:0px;
padding: 0px;
background: #F4F3EE;
}

form {
margin:0px;
padding: 0px;
}

p {
margin:0px;
padding: 0px;
}

table{
border:0px;
border-collapse:collapse;
}

table td{
padding:0px;
font-size: 11px;
font-family: verdana;
}

a:active,
a:visited,
a:link {
	color: #4b719e;
	text-decoration:none;
	}

a:hover {
	color: #4b719e;
	text-decoration: underline;
	}

.navigation {
	color: #999898;
	font-size: 11px;
	font-family: tahoma;
}
.unterline {
	background: url(engine/skins/images/line_bg.gif);
	width: 100%;
	height: 9px;
	font-size: 3px;
	font-family: tahoma;
	margin-bottom: 4px;
}
.hr_line {
	background: url(engine/skins/images/line.gif);
	width: 100%;
	height: 7px;
	font-size: 3px;
	font-family: tahoma;
	margin-top: 4px;
	margin-bottom: 4px;
}
.edit {
	border:1px solid #9E9E9E;
	color: #000000;
	font-size: 11px;
	font-family: Verdana; BACKGROUND-COLOR: #ffffff 
}
.buttons {
	background: #FFF;
	border: 1px solid #9E9E9E;
	color: #666666;
	font-family: Verdana, Tahoma, helvetica, sans-serif;
	padding: 0px;
	vertical-align: absmiddle;
	font-size: 11px; 
	height: 21px;
}
select, option {
	color: #000000;
	font-size: 11px;
	font-family: Verdana; 
	background-color: #ffffff 
}

textarea {
	border: #9E9E9E 1px solid;
	color: #000000;
	font-size: 11px;
	font-family: Verdana; 
	background-color: #ffffff 
}
#hintbox{ /*CSS for pop up hint box */
position:absolute;
top: 0;
background-color: lightyellow;
width: 150px; /*Default width of hint.*/ 
padding: 3px;
border:1px solid #787878;
font:normal 11px Verdana;
line-height:18px;
z-index:100;
border-right: 2px solid #787878;
border-bottom: 2px solid #787878;
visibility: hidden;
}

.hintanchor{ 
padding-left: 8px;
}
</style>
<link rel="stylesheet" type="text/css" href="engine/skins/jquery-ui.css">
<script type="text/javascript" src="engine/classes/js/jquery.js"></script>
<script type="text/javascript" src="engine/classes/js/jqueryui.js"></script>
</head>
<body>
<script language="javascript" type="text/javascript">
var total = {$row['count']};

	$(function() {

		$("#status").ajaxError(function(event, request, settings){
		   $(this).html('{$lang['nl_error']}');
			$('#button').attr("disabled", false);
		 });

		$( "#progressbar" ).progressbar({
			value: 0
		});

		$('#button').click(function() {
			$('#status').html('{$lang['nl_sinfo']}');
			$('#button').attr("disabled", "disabled");
			$('#button').val("{$lang['send_forw']}");
			senden( $('#sendet_ok').val() );
			return false;
		});

	});

function senden( startfrom ){

	var title = $('#title').html();
	var message = $('#message').html();

	$.post("engine/ajax/newsletter.php", { startfrom: startfrom, title: title, message: message, type: '{$type}', empfanger: '{$empfanger}', a_mail: '{$a_mail}', limit: '{$limit}'  },
		function(data){

			if (data) {

				if (data.status == "ok") {

					$('#gesendet').html(data.count);
					$('#sendet_ok').val(data.count);

					var proc = Math.round( (100 * data.count) / total );

					if ( proc > 100 ) proc = 100;

					$('#progressbar').progressbar( "option", "value", proc );

			         if (data.count >= total) 
			         {
			              $('#status').html('{$lang['nl_finish']}');
			         }
			         else 
			         { 
			              setTimeout("senden(" + data.count + ")", {$interval} );
			         }


				}

			}
		}, "json");

	return false;
}
</script>
<table align="center" width="97%">
    <tr>
        <td width="4" height="16"><img src="engine/skins/images/tb_left.gif" width="4" height="16" border="0" /></td>
		<td background="engine/skins/images/tb_top.gif"><img src="engine/skins/images/tb_top.gif" width="1" height="16" border="0" /></td>
		<td width="4"><img src="engine/skins/images/tb_right.gif" width="3" height="16" border="0" /></td>
    </tr>
	<tr>
        <td width="4" background="engine/skins/images/tb_lt.gif"><img src="engine/skins/images/tb_lt.gif" width="4" height="1" border="0" /></td>
		<td valign="top" style="padding:8px;" bgcolor="#FFFFFF">
HTML;

echo <<<HTML
<form action="" method="post">
<table width="100%">
    <tr>
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['nl_seng']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="100" style="padding:4px;">{$lang['nl_empf']}</td>
        <td>{$row['count']}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['nl_type']}</td>
        <td>{$type_send}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td><div id="progressbar"></div>{$lang['nl_sendet']} <span style="color:red;" id='gesendet'>{$start_from}</span> {$lang['mass_i']} <span style="color:blue;">{$row['count']}</span> {$lang['nl_status']} <span id="status"></span><br /><br /><input id="button" type="button" value="{$lang['nl_start']}" class="edit" style="width:190px;"><input type="hidden" id="sendet_ok" name="sendet_ok" value="{$start_from}"></td>
    </tr>
    <tr>
        <td><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td class="navigation">{$lang['nl_info']}</td>
    </tr>
</table>
</form>
HTML;

$message = stripslashes($message);

echo <<<HTML
		</td>
		<td width="4" background="engine/skins/images/tb_rt.gif"><img src="engine/skins/images/tb_rt.gif" width="4" height="1" border="0" /></td>
    </tr>
	<tr>
        <td height="16" background="engine/skins/images/tb_lb.gif"></td>
		<td background="engine/skins/images/tb_tb.gif"></td>
		<td background="engine/skins/images/tb_rb.gif"></td>
    </tr>
</table>
<pre style="display:none;" id="title">{$title}</pre>
<pre style="display:none;" id="message">{$message}</pre>
</body>

</html>
HTML;

}
elseif ($action=="preview")
{
include_once ENGINE_DIR.'/classes/parse.class.php';

$parse = new ParseFilter(Array(), Array(), 1, 1);

$title = strip_tags(stripslashes($parse->process($_POST['title'])));
$message = stripslashes($parse->process($_POST['message']));

if ($editor == "wysiwyg"){
$message = $parse->BB_Parse($message);
} else {
$message = $parse->BB_Parse($message, false);
}

echo <<<HTML
<html><title>{$title}</title>
<meta content="text/html; charset={$config['charset']}" http-equiv=Content-Type>
<style type="text/css">
html,body{
height:100%;
margin:0px;
padding: 0px;
font-size: 11px;
font-family: verdana;
}
p {
margin:0px;
padding: 0px;
}
table{
border:0px;
border-collapse:collapse;
}

table td{
padding:0px;
font-size: 11px;
font-family: verdana;
}

a:active,
a:visited,
a:link {
	color: #4b719e;
	text-decoration:none;
	}

a:hover {
	color: #4b719e;
	text-decoration: underline;
	}
</style>
<body>
HTML;

echo "<fieldset style=\"border-style:solid; border-width:1; border-color:black;\"><legend> <span style=\"font-size: 10px; font-family: Verdana\">{$title}</span> </legend>{$message}</fieldset>";


}
elseif ($action=="message") {


    echoheader("newsletter", "");


    echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function send(){";

	if ($editor == "wysiwyg"){
	echo "submit_all_data();";
	}

	echo "if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEalert('$lang[vote_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','snd','height=280,width=580,resizable=1,scrollbars=1')
        document.addnews.action.value='send';document.addnews.target='snd'
        document.addnews.submit();dd.focus()
    }
    }
    </SCRIPT>";

    echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function preview(){";

	if ($editor == "wysiwyg"){
	echo "submit_all_data();";
	}

	echo "if(document.addnews.message.value == '' || document.addnews.title.value == ''){ DLEalert('$lang[vote_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=300,width=600,resizable=1,scrollbars=1')
        document.addnews.action.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.action.value='send';document.addnews.target='_self'\",500)
    }
    }
    </SCRIPT>";

echo <<<HTML
<form method="POST" name="addnews" id="addnews" action="">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['nl_main']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="150" style="padding:6px;">{$lang['edit_title']}</td>
        <td><input class="edit bk" type="text" size="55" name="title"></td>
    </tr>
HTML;

if ($_REQUEST['editor'] == "wysiwyg"){

include(ENGINE_DIR.'/editor/newsletter.php');

} else {

include(ENGINE_DIR.'/inc/include/inserttag.php');

echo <<<HTML
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['nl_message']}</td>
        <td>
		<table width="100%"><tr><td>{$bb_code}
	<textarea rows=17 style="width:98%;" onclick=setFieldName(this.name) name="message" id="message" class="bk"></textarea><br><br>{$lang['nl_info_1']} <b>{$lang['nl_info_2']}</b><script type=text/javascript>var selField  = "message";</script></td>
	</tr></table>
</td></tr>
HTML;
}

$start_from = intval($_GET['start_from']);

echo <<<HTML
    <tr>
        <td style="padding:6px;">&nbsp;</td>
        <td><input type="hidden" name="mod" value="newsletter">
		<input type="hidden" name="action" value="send">
		<input type="hidden" name="type" value="{$type}">
		<input type="hidden" name="a_mail" value="{$a_mail}">
		<input type="hidden" name="editor" value="{$editor}">
		<input type="hidden" name="start_from" value="{$start_from}">
		<br /><input type="button" onClick="send(); return false;" class="btn btn-success" value="{$lang['btn_send']}" style="width:100px;">&nbsp;
        <input onClick="preview()" type="button" class="btn btn-info" value="{$lang['btn_preview']}" style="width:100px;"></td>
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
}
else {

  echoheader("newsletter", "");
  $group_list = get_groups ();

echo <<<HTML
<form method="GET" action="">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['nl_main']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:6px;">{$lang['nl_type']}</td>
        <td><select name="type">
           <option value="email">{$lang['bb_b_mail']}</option>
          <option value="pm">{$lang['nl_pm']}</option></select></td>
    </tr>
    <tr>
        <td width="220" style="padding:6px;">{$lang['nl_empf']}</td>
        <td><select name="empfanger[]" class="cat_select" multiple>
           <option value="all" selected>{$lang['edit_all']}</option>
           {$group_list}
		   </select></td>
    </tr>
    <tr>
        <td style="padding:6px;">{$lang['nl_editor']}</td>
        <td><select name="editor">
           <option value="bbcodes">BBCODES</option>
          <option value="wysiwyg">WYSIWYG</option></select></td>
    </tr>
    <tr>
        <td style="padding:6px;">{$lang['nl_startfrom']}</td>
        <td><input class="edit bk" type="text" size="10" name="start_from" value="0"> {$lang['nl_user']}</td>
    </tr>
    <tr>
        <td style="padding:6px;">{$lang['nl_n_mail']}</td>
        <td><input class="edit bk" type="text" size="10" name="limit" value="20"></td>
    </tr>
    <tr>
        <td style="padding:6px;">{$lang['nl_interval']}</td>
        <td><input class="edit bk" type="text" size="10" name="interval" value="3"></td>
    </tr>
    <tr>
        <td style="padding:6px;">{$lang['nl_amail']}</td>
        <td><input type="checkbox" name="a_mail" value="1"></td>
    </tr>
    <tr>
        <td style="padding:6px;">&nbsp;</td>
        <td><input type="hidden" name="mod" value="newsletter"><input type="hidden" name="action" value="message"><input type="submit" class="btn btn-primary" value="{$lang['edit_next']}" style="width:100px;"></td>
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
}
?>