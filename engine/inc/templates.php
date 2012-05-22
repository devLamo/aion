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
 Файл: templates.php
-----------------------------------------------------
 Назначение: Управление шаблонами
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['opt_denied'], $lang['opt_denied'] );
}

if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	header( "Location: $PHP_SELF?mod=templates&user_hash=" . $dle_login_hash );

}

$_REQUEST['do_template'] = trim( totranslit($_REQUEST['do_template'], false, false) );

$do_template = $_REQUEST['do_template'];
$subaction = $_REQUEST['subaction'];

$templates_list = array ();
if( ! $handle = opendir( ROOT_DIR . "/templates" ) ) {
	die( $lang['opt_errfo'] );
}
while ( false !== ($file = readdir( $handle )) ) {
	if( is_dir( ROOT_DIR . "/templates/$file" ) and ($file != "." and $file != "..") ) {
		$templates_list[] = $file;
	}
}
closedir( $handle );
sort($templates_list);


$language_list = array ();
if( ! $handle = opendir( ROOT_DIR . "/language" ) ) {
	die( $lang['opt_errfo'] );
}
while ( false !== ($file = readdir( $handle )) ) {
	if( is_dir( ROOT_DIR . "/language/$file" ) and ($file != "." and $file != "..") ) {
		$language_list[] = $file;
	}
}
closedir( $handle );

if( $_REQUEST['subaction'] == "language" ) {
	
	$allow_save = false;
	$_REQUEST['do_template'] = trim( totranslit($_REQUEST['do_template'], false, false) );
	$_REQUEST['do_language'] = trim( totranslit($_REQUEST['do_language'], false, false) );

	if( $_REQUEST['do_template'] != "" and $_REQUEST['do_language'] != "" ) {
		$config["lang_" . $_REQUEST['do_template']] = $_REQUEST['do_language'];
		$allow_save = true;
	
	} elseif( $config["lang_" . $_REQUEST['do_template']] and $_REQUEST['do_language'] == "" ) {
		unset( $config["lang_" . $_REQUEST['do_template']] );
		$allow_save = true;
	}
	
	if( $allow_save ) {

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '66', '{$_REQUEST['do_template']}')" );
		
		if( $auto_detect_config ) $config['http_home_url'] = "";
		
		$handler = fopen( ENGINE_DIR . '/data/config.php', "w" );
		fwrite( $handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n" );
		foreach ( $config as $name => $value ) {
			fwrite( $handler, "'{$name}' => \"{$value}\",\n\n" );
		}
		fwrite( $handler, ");\n\n?>" );
		fclose( $handler );
	
	}

}

if( $subaction == "new" ) {

	$b_form = "<form method=post action=\"$PHP_SELF\"><table width=100%><tr><td height=\"150\"><center>$lang[opt_newtemp_1] <select name=base_template>";

	foreach ( $templates_list as $single_template ) {
		$b_form .= "<option value=\"$single_template\">$single_template</option>";
	}

	$b_form .= '</select> ' . $lang[opt_msgnew] . ' <input class="edit" type=text name=template_name> <br /><br /><input type="submit" value="' . $lang['b_start'] . '" class="btn btn-primary">
        <input type=hidden name=mod value=templates>
        <input type=hidden name=action value=templates>
        <input type=hidden name=subaction value=donew>
        <input type=hidden name=user_hash value="' . $dle_login_hash . '">
        </td></tr></table></form>';

		msg( "info", '', $b_form );
	exit();
}

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Создания нового шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "donew" ) {
	
	function open_dir($dir, $newdir) { //The function that will copy the files
		if( file_exists( $dir ) && file_exists( $newdir ) ) {
			$open_dir = opendir( $dir );
			while ( false !== ($file = readdir( $open_dir )) ) {
				if( $file != "." && $file != ".." ) {
					if( @filetype( $dir . "/" . $file . "/" ) == "dir" ) {
						if( ! file_exists( $newdir . "/" . $file . "/" ) ) {
							mkdir( $newdir . "/" . $file . "/" );
							@chmod( $newdir . "/" . $file, 0777 );
							open_dir( $dir . "/" . $file . "/", $newdir . "/" . $file . "/" );
						}
					} else {
						copy( $dir . "/" . $file, $newdir . "/" . $file );
						@chmod( $newdir . "/" . $file, 0666 );
					}
				}
			}
		}
	}

	$base_template = trim( totranslit($_REQUEST['base_template'], false, false) );
	$template_name = trim( totranslit($_REQUEST['template_name'], false, false) );
	
	if( preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $template_name ) ) {
		msg( "error", $lang['opt_error'], $lang['opt_error_1'], "$PHP_SELF?mod=templates&subaction=new&user_hash={$dle_login_hash}" );
	}
	
	$result = @mkdir( ROOT_DIR . "/templates/" . $template_name, 0777 );
	@chmod( ROOT_DIR . "/templates/" . $template_name, 0777 );
	
	if( ! $result ) msg( "error", $lang['opt_error'], $lang['opt_cr_err'], "$PHP_SELF?mod=templates&subaction=new&user_hash={$dle_login_hash}" );
	else open_dir( ROOT_DIR . "/templates/" . $base_template, ROOT_DIR . "/templates/" . $template_name );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '67', '{$template_name}')" );
	
	msg( "info", $lang['opt_info'], $lang['opt_info_1'], "$PHP_SELF?mod=templates&user_hash={$dle_login_hash}" );
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Подготовка к удалению
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "delete" ) {
	if( strtolower( $do_template ) == strtolower($config['skin']) OR strtolower( $do_template ) == "smartphone" OR strtolower( $do_template ) == '' ) {
		msg( "Error", $lang['opt_error'], $lang['opt_error_4'], "$PHP_SELF?mod=templates&user_hash={$dle_login_hash}" );
	}
	$msg = "<form method=post action=\"$PHP_SELF\">$lang[opt_info_2] <b>$do_template</b>?<br><br>
        <input class=\"btn btn-success\" type=submit value=\" $lang[opt_yes] \"> &nbsp;<input class=\"btn btn-danger\" onClick=\"document.location='$PHP_SELF?mod=templates';\" type=button value=\"$lang[opt_no]\">
        <input type=hidden name=mod value=templates>
        <input type=hidden name=subaction value=dodelete>
        <input type=hidden name=do_template value=\"$do_template\">
        <input type=hidden name=user_hash value=\"$dle_login_hash\">
        </form>";
	
	msg( "info", $lang['opt_info_3'], $msg );
}
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Удаление шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
if( $subaction == "dodelete" ) {
	if( strtolower( $do_template ) == strtolower($config['skin']) OR strtolower( $do_template ) == "smartphone" ) {
		msg( "Error", $lang['opt_error'], $lang['opt_error_4'], "$PHP_SELF?mod=templates&user_hash={$dle_login_hash}" );
	}
	if(!$do_template OR preg_match( "/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $do_template ) ) {
		msg( "error", $lang['opt_error'], $lang['opt_error_1'], "$PHP_SELF?mod=templates&user_hash={$dle_login_hash}" );
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '68', '{$do_template}')" );
	
	listdir( ROOT_DIR . "/templates/" . $do_template );
	
	msg( "info", $lang['opt_info_3'], $lang['opt_info_4'], "$PHP_SELF?mod=templates&user_hash={$dle_login_hash}" );
}


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Редактирование шаблона
 ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
$show_delete_link = '';

$do_template = trim( totranslit($do_template, false, false) );

if( $do_template == '' or ! $do_template ) {
	$do_template = $config['skin'];
} elseif( $do_template != $config['skin'] AND $do_template != "smartphone" ) {
	$show_delete_link = "[<a class=\"maintitle\" href=\"$PHP_SELF?mod=templates&subaction=delete&user_hash={$dle_login_hash}&do_template=$do_template\">$lang[opt_dellink]</a>]";
}

if (!@is_dir ( ROOT_DIR . '/templates/' . $do_template )) {
	die ( "Template not found!" );
}

if(!is_writable(ROOT_DIR . '/templates/' . $do_template . "/")) {

	$lang['stat_template'] = str_replace ("{template}", '/templates/'.$do_template.'/', $lang['stat_template']);

	$fail = "<br /><br /><div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_template']}</div>";

} else $fail = "";

$js_array[] = "engine/skins/filetree.js";
$js_array[] = "engine/skins/codemirror/js/code.js";

echoheader( "options", $lang['opt_theads'] );

echo <<<HTML
<link rel="stylesheet" type="text/css" href="engine/skins/codemirror/css/default.css">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_edit_head']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="140" style="padding:4px;">{$lang['opt_theads']}</td>
        <td style="padding:4px;"><b>{$do_template}</b></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_al']}</td>
        <td style="padding:4px;"><form method="post" action="{$PHP_SELF}?mod=templates">
        <select name="do_language">
		<option value="">{$lang['sys_global']}</option>
HTML;

foreach ( $language_list as $single_language ) {
	if( $single_language == $config["lang_" . $do_template] ) {
		echo "<option selected value=\"$single_language\">$single_language</option>";
	} else {
		echo "<option value=\"$single_language\">$single_language</option>";
	}
}

echo <<<HTML
		</select>&nbsp;&nbsp;<input type="submit" value="{$lang['b_select']}" class="btn btn-inverse btn-mini"><input type=hidden name=user_hash value="$dle_login_hash"><input type="hidden" name="subaction" value="language"><input type="hidden" name="do_template" value="{$do_template}"></form>
		</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_newtepled']}</td>
        <td style="padding:4px;"><form method="post" action="{$PHP_SELF}?mod=templates"><select name="do_template">
HTML;

foreach ( $templates_list as $single_template ) {
	if( $single_template == $do_template ) {
		echo "<option selected value=\"$single_template\">$single_template</option>";
	} else {
		echo "<option value=\"$single_template\">$single_template</option>";
	}
}

echo <<<HTML
</select>&nbsp;&nbsp;<input type="submit" value="{$lang['b_start']}" class="btn btn-inverse btn-mini">&nbsp;&nbsp;<a onClick="javascript:Help('templates')" class="maintitle" href="#">{$lang['opt_temphelp']}</a><input type=hidden name=user_hash value="$dle_login_hash"><input type="hidden" name="action" value="templates"></form></td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td><br>[<a class="maintitle" href="{$PHP_SELF}?mod=templates&subaction=new&action=templates&user_hash={$dle_login_hash}">{$lang['opt_enewtepl']}</a>]&nbsp;
    {$show_delete_link}
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_edteil']} <b>{$do_template}</b> {$lang['templates_help']} <a class="main" href="http://dle-news.ru/extras/online/all2.html" target="_blank">http://dle-news.ru/extras/online/all2.html</a></div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
<tr>
<td width="220">
	<div id="filetree" class="filetree"></div>
	<br /><input onClick="createfile()" type="button" class="btn btn-primary btn-small" value="{$lang['template_create']}" style="width:200px;">
</td>
<td valign="top">
	<div id="fileedit" style="border: solid 1px #BBB;height: 510px; padding:5px;"></div>
</td></tr>
</table>
{$fail}

<script type="text/javascript">
$(document).ready( function() {

	$('#filetree').fileTree({ root: '{$do_template}/', script: 'engine/ajax/templates.php', folderEvent: 'click', expandSpeed: 750, collapseSpeed: 750, multiFolder: false }, function(file) { 
	
		ShowLoading('');		
		$.post('engine/ajax/templates.php', { action: "load", file: file, user_hash: "{$dle_login_hash}" }, function(data){
			
			HideLoading('');
			
			RunAjaxJS('fileedit', data);
			
		});
	});

});
function savefile( file ){
	var content = editor.getValue();

	ShowLoading('');		
	$.post('engine/ajax/templates.php', { action: "save", file: file, content: content, user_hash: "{$dle_login_hash}" }, function(data){
			
		HideLoading('');
			
		if ( data == "ok" ) {
			DLEalert('{$lang['template_saved']}', '{$lang['p_info']}');
		} else {
			DLEalert( data, '{$lang['p_info']}');
		}

	});

};

function createfile( ){

	DLEprompt("{$lang['template_enter']}", '', "{$lang['p_prompt']}", function (file) {

		ShowLoading('');		
		$.post('engine/ajax/templates.php', { action: "create", file: file, template: '{$do_template}', user_hash: "{$dle_login_hash}" }, function(data){
				
			HideLoading('');
				
			if ( data == "ok" ) {
				document.location='?mod=templates&do_template={$do_template}&user_hash={$dle_login_hash}';
			} else {
				DLEalert( data, '{$lang['p_info']}');
			}
	
		});

	});

};
</script>
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
?>