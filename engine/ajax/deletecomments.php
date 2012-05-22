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
 Файл: deletecomments.php
-----------------------------------------------------
 Назначение: удаление комментария
=====================================================
*/
@session_start();
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';


$area = totranslit($_REQUEST['area'], true, false);
$_TIME = time () + ($config['date_adjust'] * 60);

if ( !$area) $area = "news";

$allowed_areas = array(

					'news' => array (
									'comments_table' => 'comments',
									'counter_table' => 'post'
									),

					'ajax' => array (
									'comments_table' => 'comments',
									'counter_table' => 'post'
									),

					'lastcomments' => array (
									'comments_table' => 'comments',
									'counter_table' => 'post'
									),

				);

if (! is_array($allowed_areas[$area]) ) die( "error" );

if( !$is_logged ) die( "error" );

$id = intval( $_REQUEST['id'] );

if( ! $id ) die( "error" );

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

$row = $db->super_query( "SELECT * FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} where id = '$id'" );
	
$author = $row['autor'];
$is_reg = $row['is_register'];
$post_id = $row['post_id'];

if ($row['id'])	{

	$have_perm = false;
	$row['date'] = strtotime( $row['date'] );

	if( $_GET['dle_allow_hash'] != "" AND $_GET['dle_allow_hash'] == $dle_login_hash AND (($member_id['user_id'] == $row['user_id'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_delc']) OR $member_id['user_group'] == '1' OR $user_group[$member_id['user_group']]['del_allc']) ) $have_perm = true;

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = false;
	}

	if( $have_perm ) {
		$db->query( "DELETE FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} WHERE id = '$id'" );
		
		// обновление количества комментариев у юзера 
		if( $is_reg ) {
			$author = $db->safesql($author);
			$db->query( "UPDATE " . USERPREFIX . "_users set comm_num=comm_num-1 where name ='$author'" );
		}
		
		// обновление количества комментариев в новостях 
		$db->query( "UPDATE " . PREFIX . "_{$allowed_areas[$area]['counter_table']} SET comm_num=comm_num-1 where id='$post_id'" );

		if ( $config['allow_alt_url'] == "yes" AND !$config['seo_type'] ) $cprefix = "full_"; else $cprefix = "full_".$post_id;

		clear_cache( array( 'news_', 'rss', 'comm_'.$post_id, $cprefix ) );
		
		@header( "Content-type: text/html; charset=" . $config['charset'] );
		echo $row['id'];
	
	} else die( "error" );

} else die( "error" );
?>