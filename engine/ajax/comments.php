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
 Назначение: Вывод комментариев
=====================================================
*/

@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

@session_start();

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';

$_TIME = time () + ($config['date_adjust'] * 60);

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/comments.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

$_REQUEST['skin'] = trim(totranslit($_REQUEST['skin'], false, false));

if( $_REQUEST['skin'] == "" OR !@is_dir( ROOT_DIR . '/templates/' . $_REQUEST['skin'] ) ) {
	die( "Hacking attempt!" );
}

//################# Определение групп пользователей
$user_group = get_vars( "usergroup" );

if( ! $user_group ) {
	$user_group = array ();
	
	$db->query( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row() ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars( "usergroup", $user_group );
	$db->free();
}

//####################################################################################################################
//                    Определение категорий и их параметры
//####################################################################################################################
$cat_info = get_vars ( "category" );

if (! is_array ( $cat_info )) {
	$cat_info = array ();
	
	$db->query ( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
	while ( $row = $db->get_row () ) {
		
		$cat_info[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$cat_info[$row['id']][$key] = stripslashes ( $value );
		}
	
	}
	set_vars ( "category", $cat_info );
	$db->free ();
}

if( $config["lang_" . $_REQUEST['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/modules/sitelogin.php';

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $_REQUEST['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

include_once ENGINE_DIR . '/classes/comments.class.php';
$news_id = intval($_GET['news_id']);
$user_query = "newsid=" . $news_id;

if ($news_id < 1) die( "Hacking attempt!" );

$row = $db->super_query("SELECT id, date, category, alt_name, comm_num FROM " . PREFIX . "_post WHERE  id = '{$news_id}'");

if (!$row['id']) die( "Hacking attempt!" );

$row['date'] = strtotime( $row['date'] );
$category_id = intval( $row['category'] );

if( $row['date'] >= ($_TIME - 2592000) ) {
				
	$allow_full_cache = $row['id'];
			
} else $allow_full_cache = false;

if( $config['allow_alt_url'] == "yes" ) {
			
	if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
		if( $category_id AND $config['seo_type'] == 2 ) {

			$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
			$link_page = $config['http_home_url'] . get_url( $category_id ) . "/" . 'page,1,';
			$news_name = $row['alt_name'];
				
		} else {

			$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
			$link_page = $config['http_home_url'] . 'page,1,';
			$news_name = $row['alt_name'];
				
		}
			
	} else {

		$link_page = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . 'page,1,';
		$news_name = $row['alt_name'];
			
	}
		
} else {
			
	$link_page = "";
	$news_name = "";
		
}

$comments = new DLE_Comments( $db, $row['comm_num'], intval($config['comm_nummers']) );

if( $config['comm_msort'] == "" ) $config['comm_msort'] = "ASC";

if( $config['allow_cmod'] ) $where_approve = " AND " . PREFIX . "_comments.approve='1'";
else $where_approve = "";

$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, icq, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.post_id = '$news_id'" . $where_approve . " ORDER BY date " . $config['comm_msort'];

$comments->build_comments('comments.tpl', 'ajax', $allow_full_cache );

$comments->build_navigation('navigation.tpl', $link_page . "{page}," . $news_name . ".html#comment", $user_query);		

if ($config['comm_msort'] == "DESC" )
	$tpl->result['comments'] = "<div id=\"dle-ajax-comments\"></div>" . $tpl->result['comments'];
else
	$tpl->result['comments'] = $tpl->result['comments']."<div id=\"dle-ajax-comments\"></div>";

if ($user_group[$member_id['user_group']]['del_allc'] AND !$user_group[$member_id['user_group']]['edit_limit'])		
	$tpl->result['comments'] .= "\n<div class=\"mass_comments_action\">{$lang['mass_comments']}&nbsp;<select name=\"mass_action\"><option value=\"\">{$lang['edit_selact']}</option><option value=\"mass_combine\">{$lang['edit_selcomb']}</option><option value=\"mass_delete\">{$lang['edit_seldel']}</option></select>&nbsp;&nbsp;<input type=\"submit\" class=\"bbcodes\" value=\"{$lang['b_start']}\" /></div>\n<input type=\"hidden\" name=\"do\" value=\"comments\" /><input type=\"hidden\" name=\"dle_allow_hash\" value=\"{$dle_login_hash}\" /><input type=\"hidden\" name=\"area\" value=\"news\" />";


$tpl->result['comments'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $_REQUEST['skin'], $tpl->result['comments'] );
$tpl->result['commentsnavigation'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $_REQUEST['skin'], $tpl->result['commentsnavigation'] );

$tpl->result['comments'] = addcslashes($tpl->result['comments'], "\v\t\n\r\f\"\\/");
$tpl->result['comments'] = str_replace( "{", '', $tpl->result['comments'] );
$tpl->result['comments'] = str_replace( "}", '', $tpl->result['comments'] );


$tpl->result['commentsnavigation'] = addcslashes($tpl->result['commentsnavigation'], "\v\t\n\r\f\"\\/");
$tpl->result['commentsnavigation'] = str_replace( "{", '', $tpl->result['commentsnavigation'] );
$tpl->result['commentsnavigation'] = str_replace( "}", '', $tpl->result['commentsnavigation'] );

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo "{\"navigation\": \"{$tpl->result['commentsnavigation']}\",\"comments\": \"{$tpl->result['comments']}\"}";
?>