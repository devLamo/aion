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
 Файл: editcomments.php
-----------------------------------------------------
 Назначение: AJAX для редакторования
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

if( $config['http_home_url'] == "" ) {
	
	$config['http_home_url'] = explode( "engine/ajax/editcomments.php", $_SERVER['PHP_SELF'] );
	$config['http_home_url'] = reset( $config['http_home_url'] );
	$config['http_home_url'] = "http://" . $_SERVER['HTTP_HOST'] . $config['http_home_url'];

}

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

$_COOKIE['dle_skin'] = trim(totranslit( $_COOKIE['dle_skin'], false, false ));
$_TIME = time () + ($config['date_adjust'] * 60);

if( $_COOKIE['dle_skin'] ) {
	if( @is_dir( ROOT_DIR . '/templates/' . $_COOKIE['dle_skin'] ) ) {
		$config['skin'] = $_COOKIE['dle_skin'];
	}
}

if( $config["lang_" . $config['skin']] ) {
	
	if ( file_exists( ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng' ) ) {
		@include_once (ROOT_DIR . '/language/' . $config["lang_" . $config['skin']] . '/website.lng');
	} else die("Language file not found");

} else {
	
	include_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';

}

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR . '/classes/parse.class.php';
require_once ENGINE_DIR . '/modules/sitelogin.php';


$area = totranslit($_REQUEST['area'], true, false);

if ( !$area) $area = "news";

$allowed_areas = array(

					'news' => array (
									'comments_table' => 'comments',
									),

					'ajax' => array (
									'comments_table' => 'comments',
									),

					'lastcomments' => array (
									'comments_table' => 'comments',
									),

				);

if (! is_array($allowed_areas[$area]) ) die( "error" );

$parse = new ParseFilter( );
$parse->safe_mode = true;

if( ! $is_logged ) die( "error" );

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

$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

if( $_REQUEST['action'] == "edit" ) {
	$row = $db->super_query( "SELECT id, date, autor, text, is_register FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} where id = '$id'" );
	
	if( $id != $row['id'] ) die( "error" );

	$row['date'] = strtotime( $row['date'] );	
	$have_perm = 0;
	
	if( $is_logged and (($member_id['name'] == $row['autor'] and $row['is_register'] and $user_group[$member_id['user_group']]['allow_editc']) or $user_group[$member_id['user_group']]['edit_allc']) ) {
		$have_perm = 1;
	}

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}
	
	if( ! $have_perm ) die( "error" );
	
	if( $config['allow_comments_wysiwyg'] != "yes" ) {
		
		include_once ENGINE_DIR . '/ajax/bbcode.php';
		
		$comm_txt = $parse->decodeBBCodes( $row['text'], false );
	
	} else {
		
		$comm_txt = $parse->decodeBBCodes( $row['text'], true, "yes" );
		
		if( $user_group[$member_id['user_group']]['allow_url'] ) $link_icon = "\"LinkDialog\", \"DLELeech\","; else $link_icon = "";
		if( $user_group[$member_id['user_group']]['allow_image'] ) $link_icon .= "\"ImageDialog\",";
		
		$bb_code = <<<HTML

<script type="text/javascript">
function show_editor( root ) {
	
	oUtil.initializeEditor("ajaxwysiwygeditor",  {
		width: "100%", 
		height: "250", 
		css: root + "engine/editor/scripts/style/default.css",
		useBR: false,
		useDIV: false,
		groups:[
			["grpEdit1", "", ["Bold", "Italic", "Underline", "Strikethrough", "ForeColor"]],
			["grpEdit2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull", "Bullets", "Numbering"]],
			["grpEdit3", "", [{$link_icon}"DLESmiles", "DLEQuote", "DLEHide"]]
	    ],
		arrCustomButtons:[
			["DLESmiles", "modalDialog('"+ root +"engine/editor/emotions.php',250,160)", "{$lang['bb_t_emo']}", "btnEmoticons.gif"],
			["DLEQuote", "DLEcustomTag('[quote]', '[/quote]')", "{$lang['bb_t_quote']}", "dle_quote.gif"],
			["DLEHide", "DLEcustomTag('[hide]', '[/hide]')", "{$lang['bb_t_hide']}", "dle_hide.gif"],
			["DLELeech", "DLEcustomTag('[leech=http://]', '[/leech]')", "{$lang['bb_t_leech']}", "dle_leech.gif"]
		]
		}
	);	
};

show_editor(dle_root);
</script>
HTML;
	
	}
	
	$buffer = <<<HTML
<div class="editor">
{$bb_code}
<textarea name="dleeditcomments{$id}" id="dleeditcomments{$id}" onclick="setNewField(this.name, document.getElementById( 'dlemasscomments' ) )" class="ajaxwysiwygeditor" style="width:99%; height:150px; border:1px solid #E0E0E0; margin: 0px 1px 0px 0px;padding: 0px;">{$comm_txt}</textarea><br>
<div align="right" style="width:99%;padding-top:5px;"><input class=bbcodes title="$lang[bb_t_apply]" type=button onclick="ajax_save_comm_edit('{$id}', '{$area}'); return false;" value="$lang[bb_b_apply]">
<input class=bbcodes title="$lang[bb_t_cancel]" type=button onclick="ajax_cancel_comm_edit('{$id}'); return false;" value="$lang[bb_b_cancel]">
</div></div>
HTML;
} elseif( $_REQUEST['action'] == "save" ) {
	$row = $db->super_query( "SELECT id, post_id, date, autor, text, is_register, approve FROM " . PREFIX . "_{$allowed_areas[$area]['comments_table']} where id = '$id'" );
	
	if( $id != $row['id'] ) die( "error" );
	
	$have_perm = 0;
	$row['date'] = strtotime( $row['date'] );
	
	if( $is_logged AND (($member_id['name'] == $row['autor'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_editc']) OR $user_group[$member_id['user_group']]['edit_allc'] OR $user_group[$member_id['user_group']]['admin_comments']) ) {
		$have_perm = 1;
	}

	if ( $user_group[$member_id['user_group']]['edit_limit'] AND (($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) < $_TIME) ) {
		$have_perm = 0;
	}	

	if( ! $have_perm ) die( "error" );
	
	if( $config['allow_comments_wysiwyg'] == "yes" ) {
		
		$parse->wysiwyg = true;
		$use_html = true;
		
		$parse->ParseFilter( Array ('div','span','p','br','strong','em','ul','li','ol', 'b', 'u', 'i', 's'), Array (), 0, 1 );
		
		if( $user_group[$member_id['user_group']]['allow_url'] ) $parse->tagsArray[] = 'a';
		if( $user_group[$member_id['user_group']]['allow_image'] ) $parse->tagsArray[] = 'img';
	
	} else
		$use_html = false;
	
	$comm_txt = trim( $parse->BB_Parse( $parse->process( convert_unicode( $_POST['comm_txt'], $config['charset'] ) ), $use_html ) );
	
	if( $parse->not_allowed_tags ) {
		die( "error" );
	}

	if( $parse->not_allowed_text ) {
		die( "error" );
	}
	
	if( dle_strlen( $comm_txt, $config['charset'] ) > $config['comments_maxlen'] ) {
		
		die( "error" );
	
	}
	
	if( $comm_txt == "" ) {
		
		die( "error" );
	
	}

	if( intval($config['comments_minlen']) AND dle_strlen( $comm_txt, $config['charset'] ) < $config['comments_minlen'] ) {
	
		die( "error" );
	
	}

	//* Автоперенос длинных слов
	if( intval( $config['auto_wrap'] ) ) {
		
		$comm_txt = preg_split( '((>)|(<))', $comm_txt, - 1, PREG_SPLIT_DELIM_CAPTURE );
		$n = count( $comm_txt );
		
		for($i = 0; $i < $n; $i ++) {
			if( $comm_txt[$i] == "<" ) {
				$i ++;
				continue;
			}
			
			$comm_txt[$i] = preg_replace( "#([^\s\n\r]{" . intval( $config['auto_wrap'] ) . "})#i", "\\1<br />", $comm_txt[$i] );
		}
		
		$comm_txt = join( "", $comm_txt );
	
	}
	
	$comm_update = $db->safesql( $comm_txt );
	
	$db->query( "UPDATE " . PREFIX . "_{$allowed_areas[$area]['comments_table']} SET text='$comm_update', approve='1' WHERE id = '$id'" );
	
	if( !$row['approve'] ) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num+1 WHERE id='{$row['post_id']}'" );
	
	$comm_txt = str_replace( "[hide]", "", str_replace( "[/hide]", "", $comm_txt) );
	$buffer = stripslashes( $comm_txt );

	$buffer= str_replace( '{THEME}', $config['http_home_url'] . 'templates/' . $config['skin'], $buffer );

	if( !$row['approve'] ) {
		if ( $config['allow_alt_url'] == "yes" AND !$config['seo_type'] ) clear_cache( 'full_' ); else clear_cache( 'full_'.$row['post_id'] );
	}

	clear_cache( 'comm_'.$row['post_id'] );

} else
	die( "error" );

$db->close();

@header( "Content-type: text/html; charset=" . $config['charset'] );
echo $buffer;
?>