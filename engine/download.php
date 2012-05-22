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
 Файл: download.php
-----------------------------------------------------
 Назначение: Скачивание файлов
=====================================================
*/
@session_start();

define ( 'DATALIFEENGINE', true );
define ( 'FILE_DIR', '../uploads/files/' );
define ( 'ROOT_DIR', '..' );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );

@error_reporting ( E_ALL ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_NOTICE );

require ENGINE_DIR . '/data/config.php';

if ($config['http_home_url'] == "") {
	
	$config['http_home_url'] = explode ( "engine/download.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset ( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once ENGINE_DIR . '/classes/download.class.php';

function reset_url($url) {
	$value = str_replace ( "http://", "", $url );
	$value = str_replace ( "https://", "", $value );
	$value = str_replace ( "www.", "", $value );
	$value = explode ( "/", $value );
	$value = reset ( $value );
	return $value;
}

//################# Определение групп пользователей
$user_group = get_vars ( "usergroup" );

if (! $user_group) {
	
	$user_group = array ();
	
	$db->query ( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );
	
	while ( $row = $db->get_row () ) {
		
		$user_group[$row['id']] = array ();
		
		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = $value;
		}
	
	}
	
	set_vars ( "usergroup", $user_group );
	$db->free ();

}

if (! $is_logged) {
	$member_id['user_group'] = 5;
}

if (! $user_group[$member_id['user_group']]['allow_files'])
	die ( "Access denied" );

if ($config['files_antileech']) {
	
	$_SERVER['HTTP_REFERER'] = reset_url ( $_SERVER['HTTP_REFERER'] );
	$_SERVER['HTTP_HOST'] = reset_url ( $_SERVER['HTTP_HOST'] );

	if ($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_REFERER']) {
		@header ( 'Location: ' . $config['http_home_url'] );
		die ( "Access denied!!!<br /><br />Please visit <a href=\"{$config['http_home_url']}\">{$config['http_home_url']}</a>" );
	}

}

$id = intval ( $_REQUEST['id'] );

if ($_REQUEST['area'] == "static")
	$row = $db->super_query ( "SELECT name, onserver FROM " . PREFIX . "_static_files WHERE id ='$id'" );
else
	$row = $db->super_query ( "SELECT name, onserver FROM " . PREFIX . "_files WHERE id ='$id'" );

if (! $row)
	die ( "Access denied" );

$config['files_max_speed'] = intval ( $config['files_max_speed'] );

$row['onserver'] = totranslit( $row['onserver'], false );

$file = new download ( FILE_DIR . $row['onserver'], $row['name'], $config['files_force'], $config['files_max_speed'] );

if ($_REQUEST['area'] == "static") {
	
	if ($config['files_count'] == "yes" and ! $file->range)
		$db->query ( "UPDATE " . PREFIX . "_static_files SET dcount=dcount+1 WHERE id ='$id'" );

} else {
	
	if ($config['files_count'] == "yes" and ! $file->range)
		$db->query ( "UPDATE " . PREFIX . "_files SET dcount=dcount+1 WHERE id ='$id'" );

}

$db->close ();
session_write_close();

$file->download_file();
?>