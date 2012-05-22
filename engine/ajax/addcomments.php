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
 Файл: addcomments.php
-----------------------------------------------------
 Назначение: AJAX для добавления комментариев
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

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/addcomments.php", $_SERVER['PHP_SELF'] );
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

if( $config["lang_" . $_REQUEST['skin']] ) {

	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $_REQUEST['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	@include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}
$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];
$is_logged = false;
$member_id = array ();

if ($config['allow_registration'] == "yes") {
	require_once ENGINE_DIR . '/modules/sitelogin.php';
}

if( ! $is_logged ) {
	$member_id['user_group'] = 5;
}

if ( check_ip ( $banned_info['ip'] ) ) die("error");

$tpl = new dle_template( );
$tpl->dir = ROOT_DIR . '/templates/' . $_REQUEST['skin'];
define( 'TEMPLATE_DIR', $tpl->dir );

$ajax_adds = true;

$_POST['name'] = convert_unicode( $_POST['name'], $config['charset']  );
$_POST['mail'] = convert_unicode( $_POST['mail'], $config['charset'] );
$_POST['comments'] = convert_unicode( $_POST['comments'], $config['charset'] );
$_POST['question_answer'] = convert_unicode( $_POST['question_answer'], $config['charset'] );

require_once ENGINE_DIR . '/modules/addcomments.php';

if( $CN_HALT != TRUE ) {

	include_once ENGINE_DIR . '/classes/comments.class.php';
	$comments = new DLE_Comments( $db, 1, 1 );

	$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, icq, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.post_id = '$post_id' order by id DESC";
	$comments->build_comments('comments.tpl', 'ajax' );

}

if( $_POST['editor_mode'] == "wysiwyg" ) {
	
	$clear_value = "oUtil.obj.focus();oUtil.obj.loadHTML('');";

} else {
	
	$clear_value = "form.comments.value = '';";

}

if( $user_group[$member_id['user_group']]['comments_question'] ) {
	$qs = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
	$qs['question'] = htmlspecialchars( stripslashes( $qs['question'] ), ENT_QUOTES );
	$_SESSION['question'] = $qs['id'];
}

if( $CN_HALT ) {
	
	$stop = implode( '<br /><br />', $stop );
	
	$tpl->result['content'] = "<script language=\"JavaScript\" type=\"text/javascript\">\nvar form = document.getElementById('dle-comments-form');\n";
	
	if( ! $where_approve ) $tpl->result['content'] .= "
	{$clear_value}

	";
	
	$tpl->result['content'] .= "\n DLEalert('" . $stop . "', '". $lang['add_comm']."');\n var timeval = new Date().getTime();\n

	if ( document.getElementById('recaptcha_response_field') ) {
	   Recaptcha.reload(); 
    }

	if ( form.question_answer ) {

	   form.question_answer.value ='';
       jQuery('#dle-question').text('{$qs['question']}');
    }

	if ( document.getElementById('dle-captcha') ) {
		document.getElementById('dle-captcha').innerHTML = '<img src=\"' + dle_root + 'engine/modules/antibot.php?rand=' + timeval + '\" border=0><br /><a onclick=\"reload(); return false;\" href=\"#\">{$lang['reload_code']}</a>';
	}\n </script>";

} else {

	$tpl->result['content'] = "<div id=\"blind-animation\" style=\"display:none\">".$tpl->result['content']."<div>";

	
	$tpl->result['content'] .= <<<HTML
<script language='JavaScript' type="text/javascript">
	var timeval = new Date().getTime();

	var form = document.getElementById('dle-comments-form');

	if ( form.question_answer ) {

	   form.question_answer.value ='';
       jQuery('#dle-question').text('{$qs['question']}');

    }

	{$clear_value}
</script>
HTML;

}

$tpl->result['content'] = str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $_REQUEST['skin'], $tpl->result['content'] );

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $tpl->result['content'];
?>