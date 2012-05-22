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
 Файл: videoconfig.php
-----------------------------------------------------
 Назначение: настройка видеоплееров
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

require_once (ENGINE_DIR . '/data/videoconfig.php');



if( $action == "save" ) {

	if( $member_id['user_group'] != 1 ) {
		msg( "error", $lang['opt_denied'], $lang['opt_denied'] );
	}

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '78', '')" );
	
	$save_con = $_POST['save_con'];
	
	$find[] = "'\r'";
	$replace[] = "";
	$find[] = "'\n'";
	$replace[] = "";
	
	$save_con = $save_con + $video_config;
	
	$handler = fopen( ENGINE_DIR . '/data/videoconfig.php', "w" );
	
	fwrite( $handler, "<?PHP \n\n//Videoplayers Configurations\n\n\$video_config = array (\n\n" );
	foreach ( $save_con as $name => $value ) {
		
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES);
		$value = preg_replace( $find, $replace, $value );
			
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES );
		$name = preg_replace( $find, $replace, $name );
		
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		
		fwrite( $handler, "'{$name}' => \"{$value}\",\n\n" );
	
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );
	
	clear_cache();
	msg( "info", $lang['opt_sysok'], "$lang[opt_sysok_1]<br /><br /><a href=$PHP_SELF?mod=videoconfig>$lang[db_prev]</a>" );
}



echoheader( "home", $lang['db_info'] );

function showRow($title = "", $description = "", $field = "") {
	   echo "<tr>
       <td style=\"padding:4px\" class=\"option\">
        <b>$title</b><br /><span class=small>$description</span>
        <td width=\"50%\" align=\"left\" >
        $field
        </tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
		$bg = "";
		$i ++;
}
	
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


echo <<<HTML
<form action="$PHP_SELF?mod=videoconfig&action=save" name="conf" id="conf" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['vconf_title']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;

	showRow( $lang['vconf_widht'], $lang['vconf_widhtd'], "<input class=\"edit bk\" type=text style=\"text-align: center;\" name=\"save_con[width]\" value=\"{$video_config['width']}\" size=10>" );
	showRow( $lang['vconf_height'], $lang['vconf_heightd'], "<input class=\"edit bk\" type=text style=\"text-align: center;\" name=\"save_con[height]\" value=\"{$video_config['height']}\" size=10>" );
	showRow( $lang['vconf_play'], $lang['vconf_playd'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[play]", "{$video_config['play']}" ) );
	showRow( $lang['opt_sys_flvw'], $lang['opt_sys_flvwd'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[flv_watermark]", "{$video_config['flv_watermark']}" ) );
	showRow( $lang['vconf_flvpos'], $lang['vconf_flvposd'], makeDropDown( array ("left" => $lang['opt_sys_left'], "center" => $lang['opt_sys_center'], "right" => $lang['opt_sys_right'] ), "save_con[flv_watermark_pos]", "{$video_config['flv_watermark_pos']}" ) );
	showRow( $lang['vconf_flval'], $lang['vconf_flvald'], "<input class=\"edit bk\" type=text style=\"text-align: center;\" name=\"save_con[flv_watermark_al]\" value=\"{$video_config['flv_watermark_al']}\" size=10>" );

	showRow( $lang['opt_sys_turel'], $lang['opt_sys_tureld'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[tube_related]", "{$video_config['tube_related']}" ) );
	showRow( $lang['opt_sys_tudle'], $lang['opt_sys_tudled'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[tube_dle]", "{$video_config['tube_dle']}" ) );


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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['vconf_flv_title']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;
	showRow( $lang['vconf_youtube_q'], $lang['vconf_youtube_qd'], makeDropDown( array ("default" => $lang['vconf_youtube_d'], "small" => $lang['vconf_youtube_s'], "medium" => $lang['vconf_youtube_m'], "large" => $lang['vconf_youtube_l'], "hd720" => "HD 720p" ), "save_con[youtube_q]", "{$video_config['youtube_q']}" ) );

	showRow( $lang['vconf_startframe'], $lang['vconf_startframed'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[startframe]", "{$video_config['startframe']}" ) );
	showRow( $lang['vconf_preview'], $lang['vconf_previewd'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[preview]", "{$video_config['preview']}" ) );
	showRow( $lang['vconf_autohide'], $lang['vconf_autohided'], makeDropDown( array ("1" => $lang['opt_sys_yes'], "0" => $lang['opt_sys_no'] ), "save_con[autohide]", "{$video_config['autohide']}" ) );
	showRow( $lang['opt_sys_fsv'], $lang['opt_sys_fsvd'], makeDropDown( array ("1" => $lang['opt_sys_fsv_1'], "2" => $lang['opt_sys_fsv_2'], "3" => $lang['opt_sys_fsv_3'] ), "save_con[fullsizeview]", "{$video_config['fullsizeview']}" ) );
	showRow( $lang['vconf_buffer'], $lang['vconf_bufferd'], "<input class=\"edit bk\" type=text style=\"text-align: center;\" name=\"save_con[buffer]\" value=\"{$video_config['buffer']}\" size=20>" );

	showRow( $lang['vconf_prbarbolor'], $lang['vconf_prbarbolord'], "<input class=\"edit bk\" type=text style=\"text-align: center;\" name=\"save_con[progressBarColor]\" value=\"{$video_config['progressBarColor']}\" size=20>" );

	if(!is_writable(ENGINE_DIR . '/data/videoconfig.php')) {

		$lang['stat_system'] = str_replace ("{file}", "engine/data/videoconfig.php", $lang['stat_system']);

		$fail = "<br /><br /><div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_system']}</div>";

	} else $fail = "";

echo <<<HTML
    <tr>
        <td style="padding-top:10px; padding-bottom:10px;padding-right:10px;" colspan="2"><span class="small">{$lang['vconf_info']}</span></td>
    </tr>
    <tr>
        <td style="padding-top:10px; padding-bottom:10px;padding-right:10px;" colspan="2">
    <input type="hidden" name="user_hash" value="$dle_login_hash" /><input type="submit" class="btn btn-success" value="&nbsp;&nbsp;{$lang['user_save']}&nbsp;&nbsp;">{$fail}</td>
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