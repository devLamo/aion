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
 Файл: init.php
-----------------------------------------------------
 Назначение: подключение дополнительных модулей
=====================================================
*/
if (! defined ( 'DATALIFEENGINE' )) {
	die ( "Hacking attempt!" );
}

@include (ENGINE_DIR . '/data/config.php');

if ($config['http_home_url'] == "") {
	
	$config['http_home_url'] = explode ( "index.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset ( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

if ( !$config['version_id'] ) {

	if ( file_exists(ROOT_DIR . '/install.php') AND !file_exists(ENGINE_DIR . '/data/config.php') ) {

		header( "Location: ".str_replace("index.php","install.php",$_SERVER['PHP_SELF']) );
		die ( "Datalife Engine not installed. Please run install.php" );

	} else {
	
		die ( "Datalife Engine not installed. Please run install.php" );
	}

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/modules/gzip.php';

$Timer = new microTimer ( );
$Timer->start ();

check_xss ();

$cron = false;
$_TIME = time () + ($config['date_adjust'] * 60);
$config['charset'] = strtolower($config['charset']);

$cron_time = get_vars ( "cron" );

if (date ( "Y-m-d", $cron_time ) != date ( "Y-m-d", $_TIME )) $cron = 2;
elseif ($config['cache_count'] and (($cron_time + (3600 * 2)) < $_TIME)) $cron = 1;

if ($cron) include_once ENGINE_DIR . '/modules/cron.php';

if (isset ( $_REQUEST['year'] )) $year = intval ( $_GET['year'] ); else $year = '';
if (isset ( $_REQUEST['month'] )) $month = @$db->safesql ( strip_tags ( str_replace ( '/', '', $_GET['month'] ) ) ); else $month = '';
if (isset ( $_REQUEST['day'] )) $day = @$db->safesql ( strip_tags ( str_replace ( '/', '', $_GET['day'] ) ) ); else $day = '';
if (isset ( $_REQUEST['news_name'] )) $news_name = @$db->safesql ( strip_tags ( str_replace ( '/', '', $_GET['news_name'] ) ) ); else $news_name = '';
if (isset ( $_REQUEST['newsid'] )) $newsid = intval ( $_GET['newsid'] ); else $newsid = 0;
if (isset ( $_REQUEST['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 0;
if (isset ( $_REQUEST['news_page'] )) $news_page = intval ( $_GET['news_page'] ); else $news_page = 0;

if ($cstart > 9000000) {

	header( "Location: ".str_replace("index.php","",$_SERVER['PHP_SELF']) );
	die();
}

if (isset ( $_REQUEST['catalog'] )) {

	$catalog = @strip_tags ( str_replace ( '/', '', urldecode ( $_GET['catalog'] ) ) );

	if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($catalog) ) {
		$catalog = iconv( "UTF-8", "windows-1251//IGNORE", $catalog );
	}

	$catalog = $db->safesql ( dle_substr ( $catalog, 0, 3, $config['charset'] ) ); 

} else $catalog = '';

if (isset ( $_REQUEST['user'] )) {

	$user = @strip_tags ( str_replace ( '/', '', urldecode ( $_GET['user'] ) ) );

	if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($user) ) {
		$user = iconv( "UTF-8", "windows-1251//IGNORE", $user );
	}

	$user = $db->safesql ( $user ); 

} else $user = '';

if (isset ( $_REQUEST['category'] )) {
	if (substr ( $_GET['category'], - 1, 1 ) == '/') $_GET['category'] = substr ( $_GET['category'], 0, - 1 );
	$category = explode ( '/', $_GET['category'] );
	$category = end ( $category );
	$category = $db->safesql ( strip_tags ( $category ) );
} else $category = '';

$PHP_SELF = $config['http_home_url'] . "index.php";
$pm_alert = "";
$ajax = "";
$allow_comments_ajax = false;
$_DOCUMENT_DATE = false;
$user_query = "";
$static_result = "";
$is_logged = false;
$member_id = array ();


$js_array = array ();

$metatags = array (
				'title' => $config['home_title'], 
				'description' => $config['description'], 
				'keywords' => $config['keywords'],
				'header_title' => "" );

//################# Определение групп пользователей
$user_group = get_vars ( "usergroup" );

if (! $user_group) {
	$user_group = array ();
	
	$db->query ( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row () ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}
	
	}
	set_vars ( "usergroup", $user_group );
	$db->free ();
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

//####################################################################################################################
//                    Определение забаненных пользователей и IP
//####################################################################################################################
$banned_info = get_vars ( "banned" );

if (! is_array ( $banned_info )) {
	$banned_info = array ();
	
	$db->query ( "SELECT * FROM " . USERPREFIX . "_banned" );
	while ( $row = $db->get_row () ) {
		
		if ($row['users_id']) {
			
			$banned_info['users_id'][$row['users_id']] = array (
																'users_id' => $row['users_id'], 
																'descr' => stripslashes ( $row['descr'] ), 
																'date' => $row['date'] );
		
		} else {
			
			if (count ( explode ( ".", $row['ip'] ) ) == 4)
				$banned_info['ip'][$row['ip']] = array (
														'ip' => $row['ip'], 
														'descr' => stripslashes ( $row['descr'] ), 
														'date' => $row['date']
														);
			elseif (strpos ( $row['ip'], "@" ) !== false)
				$banned_info['email'][$row['ip']] = array (
															'email' => $row['ip'], 
															'descr' => stripslashes ( $row['descr'] ), 
															'date' => $row['date'] );
			else $banned_info['name'][$row['ip']] = array (
															'name' => $row['ip'], 
															'descr' => stripslashes ( $row['descr'] ), 
															'date' => $row['date'] );
		
		}
	
	}
	set_vars ( "banned", $banned_info );
	$db->free ();
}

$category_skin = "";

if ($category != '') $category_id = get_ID ( $cat_info, $category );
else $category_id = false;

if ($category_id) $category_skin = $cat_info[$category_id]['skin'];

// #################################
if ($news_name != '' OR $newsid) {
	
	$allow_sql_skin = false;
	
	foreach ( $cat_info as $cats ) {
		if ($cats['skin'] != '') $allow_sql_skin = true;
	}
	
	if ($allow_sql_skin) {
		
		if (!$newsid) $sql_skin = $db->super_query ( "SELECT category FROM " . PREFIX . "_post where month(date) = '$month' AND year(date) = '$year' AND dayofmonth(date) = '$day' AND alt_name ='$news_name'" );
		else $sql_skin = $db->super_query ( "SELECT category FROM " . PREFIX . "_post where  id = '$newsid' AND approve" );
		
		$base_skin = explode ( ',', $sql_skin['category'] );
		
		$category_skin = $cat_info[$base_skin[0]]['skin'];
		
		unset ( $sql_skin );
		unset ( $base_skin );
	
	}

}

if (isset($_GET['do']) AND $_GET['do'] == "static") {
	
	$name = $db->safesql ( $_GET['page'] );
	$static_result = $db->super_query ( "SELECT * FROM " . PREFIX . "_static WHERE name='$name'" );
	$category_skin = $static_result['template_folder'];

}

if ($category_skin != "") {

	$category_skin = trim( totranslit($category_skin, false, false) );

	if ($category_skin != '' AND @is_dir ( ROOT_DIR . '/templates/' . $category_skin )) {
		$config['skin'] = $category_skin;
	}

} elseif (isset ( $_REQUEST['action_skin_change'] )) {
	
	$_REQUEST['skin_name'] = trim( totranslit($_REQUEST['skin_name'], false, false) );
	
	if ($_REQUEST['skin_name'] != '' AND @is_dir ( ROOT_DIR . '/templates/' . $_REQUEST['skin_name'] ) ) {
		$config['skin'] = $_REQUEST['skin_name'];
		set_cookie ( "dle_skin", $_REQUEST['skin_name'], 365 );
	}

} elseif (isset ( $_COOKIE['dle_skin'] ) ) {

	$_COOKIE['dle_skin'] = trim( totranslit($_COOKIE['dle_skin'], false, false) );

	if ($_COOKIE['dle_skin'] != '' AND @is_dir ( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] )) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if (isset ( $config["lang_" . $config['skin']] ) and $config["lang_" . $config['skin']] != '') {
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {	
		include_once ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng';
	} else die("Language file not found");
} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

$smartphone_detected = false;

if( isset( $_REQUEST['action'] ) and $_REQUEST['action'] == "mobiledisable" ) $_SESSION['mobile_disable'] = 1;
if( isset( $_REQUEST['action'] ) and $_REQUEST['action'] == "mobile" ) { $_SESSION['mobile_enable'] = 1; $_SESSION['mobile_disable'] = 0;}
if( !isset( $_SESSION['mobile_disable'] ) ) $_SESSION['mobile_disable'] = 0;
if( !isset( $_SESSION['mobile_enable'] ) ) $_SESSION['mobile_enable'] = 0;

if ( $config['allow_smartphone'] AND !$_SESSION['mobile_disable'] ) {

	if ( check_smartphone() ) {

		if ( @is_dir ( ROOT_DIR . '/templates/smartphone' ) ) {

			$config['skin'] = "smartphone";
			$smartphone_detected = true;
			$config['allow_comments_wysiwyg'] = "no";

		}

	}

}

if (!isset ( $do ) AND isset ($_REQUEST['do']) ) $do = totranslit ( $_REQUEST['do'] ); elseif(isset ( $do )) $do = totranslit ( $do ); else $do = "";
if (!isset ( $subaction ) AND isset ($_REQUEST['subaction']) ) $subaction = totranslit ($_REQUEST['subaction']); else $subaction = totranslit ($subaction);
if ( isset ($_REQUEST['doaction']) ) $doaction = totranslit ($_REQUEST['doaction']); else $doaction = "";
if ($do == "tags" AND !$_GET['tag']) $do = "alltags";

$dle_module = $do;
if ($do == "" and ! $subaction and $year) $dle_module = "date";
elseif ($do == "" and $catalog) $dle_module = "catalog";
elseif ($do == "") $dle_module = $subaction;
if ($subaction == '' AND $newsid) $dle_module = "showfull";
$dle_module = $dle_module ? $dle_module : "main";

require_once ENGINE_DIR . '/classes/templates.class.php';

$tpl = new dle_template();
$tpl->dir = ROOT_DIR . '/templates/' . $config['skin'];
define ( 'TEMPLATE_DIR', $tpl->dir );

if (isset ( $_POST['set_new_sort'] ) and $config['allow_change_sort']) {
	
	$allowed_sort = array (
							'date', 
							'rating', 
							'news_read', 
							'comm_num', 
							'title' );
	
	$find_sort = str_replace ( ".", "", totranslit ( $_POST['set_new_sort'] ) );
	$direction_sort = str_replace ( ".", "", totranslit ( $_POST['set_direction_sort'] ) );
	
	if (in_array ( $_POST['dlenewssortby'], $allowed_sort )) {
		
		if ($_POST['dledirection'] == "desc" or $_POST['dledirection'] == "asc") {
			
			$_SESSION[$find_sort] = $_POST['dlenewssortby'];
			$_SESSION[$direction_sort] = $_POST['dledirection'];
			$_SESSION['dle_no_cache'] = "1";
		
		}
	
	}

}

if ($config['allow_registration'] == "yes") {
	
	include_once ENGINE_DIR . '/modules/sitelogin.php';
	
	if ( isset( $banned_info['ip'] ) ) $blockip = check_ip ( $banned_info['ip'] );  else $blockip = false;
	
	if (($is_logged AND $member_id['banned'] == "yes") OR $blockip) include_once ENGINE_DIR . '/modules/banned.php';

	if ($is_logged) {
		
		set_cookie ( "dle_newpm", $member_id['pm_unread'], 365 );
		
		if ($member_id['pm_unread'] > intval ( $_COOKIE['dle_newpm'] ) AND !$smartphone_detected) {
			
			include_once ENGINE_DIR . '/modules/pm_alert.php';
		
		}
	
	}
	
	if ($is_logged and $user_group[$member_id['user_group']]['time_limit']) {
		
		if ($member_id['time_limit'] != "" and (intval ( $member_id['time_limit'] ) < $_TIME)) {
			
			$db->query ( "UPDATE " . USERPREFIX . "_users set user_group='{$user_group[$member_id['user_group']]['rid']}', time_limit='' WHERE user_id='$member_id[user_id]'" );
			$member_id['user_group'] = $user_group[$member_id['user_group']]['rid'];
		
		}
	}

}

if (!$is_logged) $member_id['user_group'] = 5;

$tpl->load_template( 'login.tpl' );
$tpl->set( '{login-method}', $config['auth_metod'] ? "E-Mail:" : $lang['login_metod'] );
$tpl->set( '{registration-link}', $PHP_SELF . "?do=register" );
$tpl->set( '{lostpassword-link}', $PHP_SELF . "?do=lostpassword" );
$tpl->set( '{logout-link}', $PHP_SELF . "?action=logout" );
$tpl->set( '{admin-link}', $config['http_home_url'] . $config['admin_path'] . "?mod=main" );
$tpl->set( '{login}', $member_id['name'] );
$tpl->set( '{pm-link}', $PHP_SELF . "?do=pm" );
$tpl->set( '{new-pm}', $member_id['pm_unread'] );
$tpl->set( '{all-pm}', $member_id['pm_all'] );

if( $user_group[$member_id['user_group']]['icon'] ) $tpl->set( '{group-icon}', "<img src=\"" . $user_group[$member_id['user_group']]['icon'] . "\" alt=\"\" />" );
else $tpl->set( '{group-icon}', "" );

if ($member_id['favorites']) {
    $tpl->set( '{favorite-count}', count(explode("," ,$member_id['favorites'])) );
}
else $tpl->set( '{favorite-count}', '0' );

if( $member_id['foto'] ) $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $member_id['foto'] );
else $tpl->set( '{foto}', "{THEME}/images/noavatar.png" );

if ( $user_group[$member_id['user_group']]['allow_admin'] ) {
	$tpl->set( '[admin-link]', "" );
	$tpl->set( '[/admin-link]', "" );
} else {
	$tpl->set_block( "'\\[admin-link\\](.*?)\\[/admin-link\\]'si", "" );
}

if ($config['allow_alt_url'] == "yes") {
	$tpl->set( '{profile-link}', $config['http_home_url'] . "user/" . urlencode ( $member_id['name'] ) . "/" );
	$tpl->set( '{stats-link}', $config['http_home_url'] . "statistics.html" );
	$tpl->set( '{addnews-link}', $config['http_home_url'] . "addnews.html" );
	$tpl->set( '{favorites-link}', $config['http_home_url'] . "favorites/" );
	$tpl->set( '{newposts-link}', $config['http_home_url'] . "newposts/" );

} else {
	$tpl->set( '{profile-link}', $PHP_SELF . "?subaction=userinfo&user=" . urlencode ( $member_id['name'] ) );
	$tpl->set( '{stats-link}', $PHP_SELF . "?do=stats" );
	$tpl->set( '{addnews-link}', $PHP_SELF . "?do=addnews" );
	$tpl->set( '{favorites-link}', $PHP_SELF . "?do=favorites" );
	$tpl->set( '{newposts-link}', $PHP_SELF . "?subaction=newposts" );

}
$tpl->compile( 'login_panel' );
$tpl->clear();

if ($config['site_offline'] == "yes") include_once ENGINE_DIR . '/modules/offline.php';

require_once ENGINE_DIR . '/modules/calendar.php';

if ($config['allow_topnews'] == "yes") include_once ENGINE_DIR . '/modules/topnews.php';

if ($config['rss_informer']) include_once ENGINE_DIR . '/modules/rssinform.php';

require_once ROOT_DIR . '/engine/engine.php';

if ($config['allow_votes'] == "yes") include_once ENGINE_DIR . '/modules/vote.php';

if ( !defined('BANNERS') ) {
	if ($config['allow_banner']) include_once ENGINE_DIR . '/modules/banners.php';
}

if ($config['allow_tags']) include_once ENGINE_DIR . '/modules/tagscloud.php';

?>