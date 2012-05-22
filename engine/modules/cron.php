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
 Файл: cron.php
-----------------------------------------------------
 Назначение: Выполнение автоматических операций
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

set_vars( "cron", $_TIME );

if( $config['cache_count'] ) {
	$result = $db->query( "SELECT COUNT(*) as count, news_id FROM " . PREFIX . "_views GROUP BY news_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . PREFIX . "_post_extras SET news_read=news_read+{$row['count']} WHERE news_id='{$row['news_id']}'" );
	
	}
	
	$db->free( $result );
	$db->query( "TRUNCATE TABLE " . PREFIX . "_views" );

	clear_cache( array('news_', 'full_', 'rss') );

}

if( $cron == 2 ) {
	$db->query( "TRUNCATE TABLE " . PREFIX . "_login_log" );
	$db->query( "TRUNCATE TABLE " . PREFIX . "_flood" );
	$db->query( "TRUNCATE TABLE " . PREFIX . "_mail_log" );
	
	$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE days != '0' AND date < '$_TIME' AND users_id = '0'" );
	@unlink( ENGINE_DIR . '/cache/system/banned.php' );
	
	$sql_cron = $db->query( "SELECT news_id, action FROM " . PREFIX . "_post_log WHERE expires <= '" . $_TIME . "'" );
	
	while ( $row = $db->get_row( $sql_cron ) ) {

		if ( $row['action'] == 1 ) {

			$db->query( "UPDATE " . PREFIX . "_post SET approve='0' WHERE id='{$row['news_id']}'" );
	
		} elseif ( $row['action'] == 2 ) {

			$db->query( "UPDATE " . PREFIX . "_post SET allow_main='0' WHERE id='{$row['news_id']}'" );

		} elseif ( $row['action'] == 3 ) {

			$db->query( "UPDATE " . PREFIX . "_post SET fixed='0' WHERE id='{$row['news_id']}'" );
	
		} else {
		
			$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$row['news_id']}'" );
			$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='{$row['news_id']}'" );
			
			$row_1 = $db->super_query( "SELECT images  FROM " . PREFIX . "_images where news_id = '{$row['news_id']}'" );
			
			$listimages = explode( "|||", $row_1['images'] );
			
			if( $row_1['images'] != "" ) foreach ( $listimages as $dataimages ) {
				$url_image = explode( "/", $dataimages );
				
				if( count( $url_image ) == 2 ) {
					
					$folder_prefix = $url_image[0] . "/";
					$dataimages = $url_image[1];
				
				} else {
					
					$folder_prefix = "";
					$dataimages = $url_image[0];
				
				}
				
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $dataimages );
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "thumbs/" . $dataimages );
			}
			
			$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$row['news_id']}'" );
			
			$getfiles = $db->query( "SELECT id, onserver FROM " . PREFIX . "_files WHERE news_id = '{$row['news_id']}'" );
			
			while ( $row_1 = $db->get_row( $getfiles ) ) {
				
				@unlink( ROOT_DIR . "/uploads/files/" . $row_1['onserver'] );
			
			}
			$db->free( $getfiles );
			
			$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '{$row['news_id']}'" );

		}
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE expires <= '" . $_TIME . "'" );
	
	$db->free( $sql_cron );
	
	if( intval( $config['max_users_day'] ) ) {
		$thisdate = $_TIME - ($config['max_users_day'] * 3600 * 24);
		
		$sql_result = $db->query( "SELECT name, user_id, foto FROM " . USERPREFIX . "_users WHERE lastdate < '$thisdate' and user_group = '4'" );
		
		while ( $row = $db->get_row( $sql_result ) ) {

			$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user_from = '{$row['name']}' AND folder = 'outbox'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE user='{$row['user_id']}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_banned WHERE users_id='{$row['user_id']}'" );
			$db->query( "DELETE FROM " . USERPREFIX . "_users WHERE user_id = '{$row['user_id']}'" );
			@unlink( ROOT_DIR . "/uploads/fotos/" . $row['foto'] );
		}

		$db->free( $sql_result );
		
	}
	
	if( intval( $config['max_image_days'] ) ) {
		$thisdate = $_TIME - ($config['max_image_days'] * 3600 * 24);
		
		$db->query( "SELECT images  FROM " . PREFIX . "_images where date < '$thisdate' AND news_id = '0'" );
		
		while ( $row = $db->get_row() ) {
			
			$listimages = explode( "|||", $row['images'] );
			
			if( $row['images'] != "" ) foreach ( $listimages as $dataimages ) {
				$url_image = explode( "/", $dataimages );
				
				if( count( $url_image ) == 2 ) {
					
					$folder_prefix = $url_image[0] . "/";
					$dataimages = $url_image[1];
				
				} else {
					
					$folder_prefix = "";
					$dataimages = $url_image[0];
				
				}
				
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $dataimages );
				@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . "thumbs/" . $dataimages );
			}
		
		}
		
		$db->free();
		
		$db->query( "DELETE FROM " . PREFIX . "_images where date < '$thisdate' AND news_id = '0'" );

		$db->query( "SELECT id, onserver FROM " . PREFIX . "_files WHERE date < '$thisdate' AND news_id = '0'" );
				
		while ( $row = $db->get_row() ) {
					
			@unlink( ROOT_DIR . "/uploads/files/" . totranslit($row['onserver']) );
				
		}
				
		$db->query( "DELETE FROM " . PREFIX . "_files WHERE date < '$thisdate' AND news_id = '0'" );
	
	}
	
	clear_cache();

}
?>