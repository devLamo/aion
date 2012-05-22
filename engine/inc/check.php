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
 Файл: check.php
-----------------------------------------------------
 Назначение: Анализ производительности скрипта
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}
if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}


if ( file_exists( ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng' ) ) {
	require_once (ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng');
}

$result = array();

foreach($user_group as $value) {

	if ( $value['allow_cats'] != "all" ) {

		$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">".str_replace("{name}", $value['group_name'],$lang['admin_check_32'])."</div>";

	}

}

if ( $config['allow_cache'] == "yes" AND $config['allow_change_sort'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_28']}</div>";

}

if ( $config['allow_tags'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_27']}</div>";

}

if ( $config['allow_archives'] == "yes" ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_25']}</div>";

}

if ( $config['allow_calendar'] == "yes" ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_24']}</div>";

}

if ( $config['allow_read_count'] == "yes" AND !$config['cache_count'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_23']}</div>";

}

if ( $config['allow_cmod'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_19']}</div>";

}

if ( $config['no_date'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_15']}</div>";

}

if ( $config['related_news'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_14']}</div>";

}

if ( $config['allow_multi_category'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_13']}</div>";

}

if ( $config['allow_cache'] == "no" ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_12']}</div>";

}

if ( $config['fast_search'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_10']}</div>";

}

if ( $config['full_search'] ) {

	$result[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_9']}</div>";

}

if ( $config['allow_subscribe'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_33']}</div>";

}

if ( $config['allow_skin_change'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_30']}</div>";

}

if ( $config['files_allow'] == "yes" AND $config['files_count'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_29']}</div>";

}

if ( $config['rss_informer'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_26']}</div>";

}

if ( $config['allow_read_count'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_22']}</div>";

}

if ( $config['allow_topnews'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_21']}</div>";

}

if ( $config['allow_banner'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_18']}</div>";

}

if ( $config['allow_fixed'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_16']}</div>";

}

if ( $config['js_min'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_8']}</div>";

}

if ( $config['allow_registration'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_11']}</div>";

}

if ( $config['allow_gzip'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_7']}</div>";

}

if ( $config['allow_comments'] == "yes" ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_6']}</div>";

}

if ( $config['show_sub_cats'] ) {

	$result[] = "<div class=\"ui-state-highlight ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_4']}</div>";

}

if ( $config['mail_pm'] ) {

	$result[] = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_31']}</div>";

}

if ( $config['allow_votes'] == "yes" ) {

	$result[] = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_20']}</div>";

}

if ( $config['speedbar'] ) {

	$result[] = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_17']}</div>";

}

if ( $config['allow_admin_wysiwyg'] == "yes" OR $config['allow_site_wysiwyg'] == "yes" OR $config['allow_quick_wysiwyg'] OR $config['allow_comments_wysiwyg'] == "yes" ) {

	$result[] = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_3']}</div>";

}

if ( $config['short_rating'] ) {

	$result[] = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px; margin-bottom:10px;\">{$lang['admin_check_5']}</div>";

}

if ( count($result) ) {
	$result = implode("", $result);
} else 	$result = "<div class=\"ui-state-green ui-corner-all\" style=\"padding:10px;\">{$lang['admin_check_2']}</div>";

echoheader( "", "" );
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_check']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%" id="actionlist">
    <tr class="thead">
        <th style="padding:2px;">{$lang['admin_check_1']}</th>
    </tr>
	<tr class="tfoot"><th><div class="hr_line"></div></th></tr>
HTML;
	
		echo "
        <tr>
        <td style=\"padding-top:5px;padding-bottom:5px\">{$result}</td>
        </tr>
        ";



	echo <<<HTML
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></th></tr>
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
?>