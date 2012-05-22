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
 Файл: functions.inc.php
-----------------------------------------------------
 Назначение: Основные функции
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

function check_login($username, $md5_password, $post = true, $check_log = false) {
	global $member_id, $db, $user_group, $lang, $_IP, $_TIME, $config;

	if( $username == "" OR $md5_password == "" ) return false;
	
	$result = false;
	
	if( $post ) {
		
		$username = $db->safesql( $username );
		$md5_password = md5( $md5_password );

		if ($config['auth_metod']) {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\/|\\\|\&\~\*\+]/", $username) ) return false;	
			$where_name = "email='{$username}'";
	
		} else {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $username) ) return false;
			$where_name = "name='{$username}'";
	
		}

		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE {$where_name} AND password='{$md5_password}'" );
		
		if( $member_id['user_id'] AND $user_group[$member_id['user_group']]['allow_admin'] AND $member_id['banned'] != 'yes' ) $result = TRUE;
		else $member_id = array ();


	} else {
		
		$username = intval( $username );
		$md5_password = md5( $md5_password );
		
		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='$username'" );
		
		if( $member_id['user_id'] AND $member_id['password'] AND $member_id['password'] == $md5_password AND $user_group[$member_id['user_group']]['allow_admin'] AND $member_id['banned'] != 'yes' ) $result = TRUE;
		else $member_id = array ();
	
	}

	if( $result ) {
		
		if( !allowed_ip( $member_id['allowed_ip'] ) ) {
			
			$member_id = array ();
			$result = false;
			set_cookie( "dle_user_id", "", 0 );
			set_cookie( "dle_name", "", 0 );
			set_cookie( "dle_password", "", 0 );
			set_cookie( "dle_hash", "", 0 );
			@session_destroy();
			@session_unset();
			set_cookie( session_name(), "", 0 );
			
			msg( "info", $lang['index_msge'], $lang['ip_block'] );
		
		}
	}

	if ( !$result ) { 

		if ($config['login_log']) $db->query( "INSERT INTO " . PREFIX . "_login_log (ip, count, date) VALUES('{$_IP}', '0', '".time()."') ON DUPLICATE KEY UPDATE count=count+1, date='".time()."'" );

	} else {

		if ( $check_log AND !$_SESSION['check_log']) {

			if( $post ) { $a_id = 82; $extr =""; } else { $a_id = 86; if ($_SERVER['HTTP_REFERER']) $extr = $db->safesql(htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES)); else $extr = "Direct DLE Adminpanel"; }

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '{$a_id}', '{$extr}')" );
			$_SESSION['check_log'] = 1;
		}

	}

	return $result;
}

function formatsize($file_size) {
	if( $file_size >= 1073741824 ) {
		$file_size = round( $file_size / 1073741824 * 100 ) / 100 . " Gb";
	} elseif( $file_size >= 1048576 ) {
		$file_size = round( $file_size / 1048576 * 100 ) / 100 . " Mb";
	} elseif( $file_size >= 1024 ) {
		$file_size = round( $file_size / 1024 * 100 ) / 100 . " Kb";
	} else {
		$file_size = $file_size . " b";
	}
	return $file_size;
}

function CheckCanGzip() {
	
	if( headers_sent() || connection_aborted() || ! function_exists( 'ob_gzhandler' ) || ini_get( 'zlib.output_compression' ) ) return 0;
	
	if( strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip' ) !== false ) return "x-gzip";
	if( strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) return "gzip";
	
	return 0;
}

function GzipOut() {
	
	$ENCODING = CheckCanGzip();
	
	if( $ENCODING ) {
		$Contents = ob_get_contents();
		ob_end_clean();
		
		header( "Content-Encoding: $ENCODING" );
		
		$Contents = gzencode( $Contents, 1, FORCE_GZIP );
		echo $Contents;
		
		exit();
	} else {
		//      ob_end_flush(); 
		exit();
	}
}

class microTimer {
	function start() {
		global $starttime;
		$mtime = microtime();
		$mtime = explode( ' ', $mtime );
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
	}
	function stop() {
		global $starttime;
		$mtime = microtime();
		$mtime = explode( ' ', $mtime );
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round( ($endtime - $starttime), 5 );
		return $totaltime;
	}
}

function allowed_ip($ip_array) {
	
	$ip_array = trim( $ip_array );
	
	if( $ip_array == "" ) {
		return true;
	}
	
	$ip_array = explode( "|", $ip_array );
	
	$db_ip_split = explode( ".", $_SERVER['REMOTE_ADDR'] );
	
	foreach ( $ip_array as $ip ) {
		
		$ip_check_matches = 0;
		$this_ip_split = explode( ".", trim( $ip ) );
		
		for($i_i = 0; $i_i < 4; $i_i ++) {
			if( $this_ip_split[$i_i] == $db_ip_split[$i_i] or $this_ip_split[$i_i] == '*' ) {
				$ip_check_matches += 1;
			}
		
		}
		
		if( $ip_check_matches == 4 ) return true;
	
	}
	
	return FALSE;
}

////////////////////////////////////////////////////////
// Function:     msg
// Description: Displays message to user


function msg($type, $title, $text, $back = FALSE) {
	global $lang;
	
	if( $back ) {
		$back = "<br /><br> <a class=main href=\"$back\">$lang[func_msg]</a>";
	}
	
	echoheader( $type, $title );
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$title}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td height="100" align="center">{$text} {$back}</td>
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
HTML;
	
	echofooter();
	exit();
}

function echoheader($image, $header_text) {
	global $PHP_SELF, $skin_header, $member_id, $user_group, $js_array;
	
	$skin_header = str_replace( "{header-text}", $header_text, $skin_header );
	$skin_header = str_replace( "{user}", $member_id['name'], $skin_header );
	$skin_header = str_replace( "{group}", $user_group[$member_id['user_group']]['group_name'], $skin_header );
	$skin_header = str_replace( "{js_files}", build_js($js_array), $skin_header );
	
	echo $skin_header;
}

function echofooter() {
	
	global $PHP_SELF, $is_loged_in, $skin_footer;
	
	echo $skin_footer;

}

function listdir($dir) {
	
	$current_dir = opendir( $dir );
	while ( $entryname = readdir( $current_dir ) ) {
		if( is_dir( "$dir/$entryname" ) and ($entryname != "." and $entryname != "..") ) {
			listdir( "${dir}/${entryname}" );
		} elseif( $entryname != "." and $entryname != ".." ) {
			unlink( "${dir}/${entryname}" );
		}
	}
	@closedir( $current_dir );
	rmdir( ${dir} );
}

function totranslit($var, $lower = true, $punkt = true) {
	global $langtranslit;
	
	if ( is_array($var) ) return "";

	if (!is_array ( $langtranslit ) OR !count( $langtranslit ) ) {
		$var = trim( strip_tags( $var ) );
		return preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	}
	
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/ms", "-", $var );
	$var = str_replace( "/", "-", $var );

	$var = strtr($var, $langtranslit);
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );

	if ( $lower ) $var = strtolower( $var );

	$var = str_ireplace( ".php", "", $var );
	$var = str_ireplace( ".php", ".ppp", $var );
	
	if( strlen( $var ) > 200 ) {
		
		$var = substr( $var, 0, 200 );
		
		if( ($temp_max = strrpos( $var, '-' )) ) $var = substr( $var, 0, $temp_max );
	
	}
	
	return $var;
}


function langdate($format, $stamp) {
	global $langdate;
	
	return strtr( @date( $format, $stamp ), $langdate );

}

function CategoryNewsSelection($categoryid = 0, $parentid = 0, $nocat = TRUE, $sublevelmarker = '', $returnstring = '') {
	global $cat, $cat_parentid, $member_id, $user_group, $mod;
	
	if ($mod == "addnews" OR $mod == "editnews")
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	else
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );

	$spec_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
	
	if( $parentid == 0 ) {
		if( $nocat ) $returnstring .= '<option value="0"></option>';
	} else {
		$sublevelmarker .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	
	if( isset( $cat_parentid ) ) {
		
		$root_category = @array_keys( $cat_parentid, $parentid );
		
		if( is_array( $root_category ) ) {
			
			foreach ( $root_category as $id ) {
				
				$category_name = $cat[$id];
				
				if( ( $allow_list[0] == "all" OR in_array( $id, $allow_list ) ) OR $mod == "usergroup" ) {
					
					if( $spec_list[0] == "all" or in_array( $id, $spec_list ) ) $color = "black";
					else $color = "red";
					
					$returnstring .= "<option style=\"color: {$color}\" value=\"" . $id . '" ';
					
					if( is_array( $categoryid ) ) {
						foreach ( $categoryid as $element ) {
							if( $element == $id ) $returnstring .= 'SELECTED';
						}
					} elseif( $categoryid == $id ) $returnstring .= 'SELECTED';
					
					$returnstring .= '>' . $sublevelmarker . $category_name . '</option>';
				}
				
				$returnstring = CategoryNewsSelection( $categoryid, $id, $nocat, $sublevelmarker, $returnstring );
			}
		}
	}
	
	return $returnstring;
}

function filesize_url($url) {
	return ($data = @file_get_contents( $url )) ? strlen( $data ) : false;
}

$mcache = false;

if ( $config['cache_type'] ) {

	if ( function_exists('memcache_connect') ) {

		$memcache_server = explode(":", $config['memcache_server']);

		$mcache = @memcache_connect( $memcache_server[0], $memcache_server[1] );

		if( $mcache AND function_exists('memcache_set_compress_threshold') )
		{
			memcache_set_compress_threshold( $mcache, 20000, 0.2 );
		}

	}

}

function clear_cache($cache_areas = false) {
	global $mcache;

	if ( $mcache ) {

		memcache_flush($mcache);

	} else {

		if ( $cache_areas ) {
			if(!is_array($cache_areas)) {
				$cache_areas = array($cache_areas);
			}
		}
		
		$fdir = opendir( ENGINE_DIR . '/cache' );
		
		while ( $file = readdir( $fdir ) ) {
			if( $file != '.' and $file != '..' and $file != '.htaccess' and $file != 'system' ) {
				
				if( $cache_areas ) {
					
					foreach($cache_areas as $cache_area) if( strpos( $file, $cache_area ) !== false ) @unlink( ENGINE_DIR . '/cache/' . $file );
				
				} else {
					
					@unlink( ENGINE_DIR . '/cache/' . $file );
				
				}
			}
		}
	}
}

function xfieldsdataload($id) {
	
	if( $id == "" ) return;
	
	$xfieldsdata = explode( "||", $id );
	foreach ( $xfieldsdata as $xfielddata ) {
		list ( $xfielddataname, $xfielddatavalue ) = explode( "|", $xfielddata );
		$xfielddataname = str_replace( "&#124;", "|", $xfielddataname );
		$xfielddataname = str_replace( "__NEWL__", "\r\n", $xfielddataname );
		$xfielddatavalue = str_replace( "&#124;", "|", $xfielddatavalue );
		$xfielddatavalue = str_replace( "__NEWL__", "\r\n", $xfielddatavalue );
		$data[$xfielddataname] = $xfielddatavalue;
	}
	return $data;
}

function xfieldsload() {
	global $lang;
	$path = ENGINE_DIR . '/data/xfields.txt';
	$filecontents = file( $path );
	
	if( ! is_array( $filecontents ) ) msg( "error", $lang['xfield_error'], "$lang[xfield_err_3] \"engine/data/xfields.txt\". $lang[xfield_err_4]" );
	
	foreach ( $filecontents as $name => $value ) {
		$filecontents[$name] = explode( "|", trim( $value ) );
		foreach ( $filecontents[$name] as $name2 => $value2 ) {
			$value2 = str_replace( "&#124;", "|", $value2 );
			$value2 = str_replace( "__NEWL__", "\r\n", $value2 );
			$filecontents[$name][$name2] = $value2;
		}
	}
	return $filecontents;
}

function create_metatags($story) {
	global $config, $db;
	
	$keyword_count = 20;
	$newarr = array ();
	$headers = array ();
	$quotes = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", ".", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "-", "+", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"');
	$fastquotes = array ("\x22", "\x60", "\t", "\n", "\r", '"', '\r', '\n', "$", "{", "}", "[", "]", "<", ">");

	$story = preg_replace( "#\[hide\](.+?)\[/hide\]#is", "", $story );
	$story = preg_replace( "'\[attachment=(.*?)\]'si", "", $story );
	$story = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $story );
	$story = str_replace( "{PAGEBREAK}", "", $story );
	$story = str_replace( "&nbsp;", " ", $story );
	
	$story = str_replace( '<br />', ' ', $story );
	$story = strip_tags( $story );
	$story = preg_replace( "#&(.+?);#", "", $story );
	$story = trim(str_replace( " ,", "", $story ));
 
	if( trim( $_REQUEST['meta_title'] ) != "" ) {

		$headers['title'] = trim( htmlspecialchars( strip_tags( stripslashes($_REQUEST['meta_title'] ) ) ) );
		$headers['title'] = $db->safesql(str_replace( $fastquotes, '', $headers['title'] ));

	} else $headers['title'] = "";
	
	if( trim( $_REQUEST['descr'] ) != "" ) {

		$headers['description'] = dle_substr( strip_tags( stripslashes( $_REQUEST['descr'] ) ), 0, 190, $config['charset'] );
		$headers['description'] = $db->safesql( str_replace( $fastquotes, '', $headers['description'] ));
	
	} else {
		
		$story = str_replace( $fastquotes, '', $story );

		$headers['description'] = $db->safesql( dle_substr( stripslashes($story), 0, 190, $config['charset'] ) );
	}
	
	if( trim( $_REQUEST['keywords'] ) != "" ) {

		$headers['keywords'] = $db->safesql( str_replace( $fastquotes, " ", strip_tags( stripslashes( $_REQUEST['keywords'] ) ) ) );

	} else {
		
		$story = str_replace( $quotes, ' ', $story );
		
		$arr = explode( " ", $story );
		
		foreach ( $arr as $word ) {
			if( dle_strlen( $word, $config['charset'] ) > 4 ) $newarr[] = $word;
		}
		
		$arr = array_count_values( $newarr );
		arsort( $arr );
		
		$arr = array_keys( $arr );
		
		$total = count( $arr );
		
		$offset = 0;
		
		$arr = array_slice( $arr, $offset, $keyword_count );
		
		$headers['keywords'] = $db->safesql( implode( ", ", $arr ) );
	}
	
	return $headers;
}

function set_vars($file, $data) {
	
	$filename = ENGINE_DIR . '/cache/system/' . $file . '.php';
	
	$fp = fopen( $filename, 'wb+' );
	fwrite( $fp, serialize( $data ) );
	fclose( $fp );
	
	@chmod( $filename, 0666 );
}

function get_vars($file) {
	$filename = ENGINE_DIR . '/cache/system/' . $file . '.php';
	
	if( ! @filesize( $filename ) ) {
		return false;
	}
	
	return unserialize( file_get_contents( $filename ) );

}
function get_groups($id = false) {
	global $user_group;
	
	$returnstring = "";
	
	foreach ( $user_group as $group ) {
		$returnstring .= '<option value="' . $group['id'] . '" ';
		
		if( is_array( $id ) ) {
			foreach ( $id as $element ) {
				if( $element == $group['id'] ) $returnstring .= 'SELECTED';
			}
		} elseif( $id and $id == $group['id'] ) $returnstring .= 'SELECTED';
		
		$returnstring .= ">" . $group['group_name'] . "</option>\n";
	}
	
	return $returnstring;

}
function permload($id) {
	
	if( $id == "" ) return;
	
	$data = array ();
	
	$groups = explode( "|", $id );
	foreach ( $groups as $group ) {
		list ( $groupid, $groupvalue ) = explode( ":", $group );
		$data[$groupid][1] = ($groupvalue == 1) ? "selected" : "";
		$data[$groupid][2] = ($groupvalue == 2) ? "selected" : "";
		$data[$groupid][3] = ($groupvalue == 3) ? "selected" : "";
	}
	return $data;
}
function check_xss() {

	if ($_GET['mod'] == "editnews" AND $_GET['action'] == "list") return;
	
	$url = html_entity_decode( urldecode( $_SERVER['QUERY_STRING'] ) );

	$url = str_replace( "\\", "/", $url );

	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false) ) {
			
			if( $_GET['mod'] != "editnews" or $_GET['action'] != "list" ) die( "Hacking attempt!" );
		
		}
	
	}
	
	$url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ) );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, '\'' ) !== false) ) {
			
			die( "Hacking attempt!" );
		
		}
	
	}

}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", $url );
	$url = str_replace( "https://", "", $url );
	if( strtolower( substr( $url, 0, 4 ) ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

$domain_cookie = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
$domain_cookie_count = count($domain_cookie);
$domain_allow_count = -2;

if ( $domain_cookie_count > 2 ) {

	if ( in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) $domain_allow_count = -3;
	if ( $domain_cookie[$domain_cookie_count-1] == 'ua' ) $domain_allow_count = -3;
	$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
}

$domain_cookie = "." . implode (".", $domain_cookie);

if( ip2long($_SERVER['HTTP_HOST']) != -1 AND ip2long($_SERVER['HTTP_HOST']) !== FALSE ) define( 'DOMAIN', null );
else define( 'DOMAIN', $domain_cookie );

function set_cookie($name, $value, $expires) {
	
	if( $expires ) {
		
		$expires = time() + ($expires * 86400);
	
	} else {
		
		$expires = FALSE;
	
	}
	
	if( PHP_VERSION < 5.2 ) {
		
		setcookie( $name, $value, $expires, "/", DOMAIN . "; HttpOnly" );
	
	} else {
		
		setcookie( $name, $value, $expires, "/", DOMAIN, NULL, TRUE );
	
	}
}

function get_url($id) {
	
	global $cat_info;
	
	if( ! $id ) return;
	
	$parent_id = $cat_info[$id]['parentid'];
	
	$url = $cat_info[$id]['alt_name'];
	
	while ( $parent_id ) {
		
		$url = $cat_info[$parent_id]['alt_name'] . "/" . $url;
		
		$parent_id = $cat_info[$parent_id]['parentid'];
		
		if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
	
	}
	
	return $url;
}

function convert_unicode($t, $to = 'windows-1251') {
	$to = strtolower( $to );

	if( $to == 'utf-8' ) {
		
		return $t;
	
	} else {
		
		if( function_exists( 'iconv' ) ) $t = iconv( "UTF-8", $to . "//IGNORE", $t );
		else $t = "The library iconv is not supported by your server";
	
	}

	return $t;
}

function check_netz($ip1, $ip2) {
	
	$ip1 = explode( ".", $ip1 );
	$ip2 = explode( ".", $ip2 );
	
	if( $ip1[0] != $ip2[0] ) return false;
	if( $ip1[1] != $ip2[1] ) return false;
	
	return true;

}

function compare_filter($a, $b) {
	
	$a = explode( "|", $a );
	$b = explode( "|", $b );
	
	if( $a[1] == $b[1] ) return 0;
	
	return strcasecmp( $a[1], $b[1] );

}

function auth() {
	header( 'WWW-Authenticate: Basic realm="Admin Area"' );
	header( 'HTTP/1.0 401 Unauthorized' );
	echo "<H1>Access Denied</H1>";
	exit();
}

function build_js($js) {
	global $config;

	$js_array = array();

	if ($config['js_min'] AND version_compare(PHP_VERSION, '5.1.0', '>') ) {

		$js_array[] = "<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/classes/min/index.php?charset={$config['charset']}&amp;g=admin&amp;7\"></script>";

		if ( count($js) ) $js_array[] = "<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/classes/min/index.php?charset={$config['charset']}&amp;f=".implode(",", $js)."&amp;7\"></script>";

		return implode("\n", $js_array);

	} else {

		$default_array = array (
			'engine/classes/js/jquery.js',
			'engine/classes/js/jqueryui.js',
			'engine/skins/default.js',
		);

		if ( count($js) ) $js = array_merge($default_array, $js); else $js = $default_array;

		foreach ($js as $value) {
		
			$js_array[] = "<script type=\"text/javascript\" src=\"{$value}\"></script>";
		
		}

		return implode("\n", $js_array);
	}

}

function dle_strlen($value, $charset ) {

	if ( strtolower($charset) == "utf-8") return iconv_strlen($value, "utf-8");
	else return strlen($value);

}

function dle_substr($str, $start, $length, $charset ) {

	if ( strtolower($charset) == "utf-8") return iconv_substr($str, $start, $length, "utf-8");
	else return substr($str, $start, $length);

}

function check_allow_login($ip, $max ) {
	global $db;

	$block_date = time()-1200;

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_login_log WHERE ip='{$ip}'" );

	if ( $row['count'] AND $row['date'] < $block_date ) $db->query( "DELETE FROM " . PREFIX . "_login_log WHERE ip = '{$ip}'" );

	if ($row['count'] > $max AND $row['date'] > $block_date ) return false;
	else return true;

}

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
   
  foreach ($list as $item) {
     $sample = iconv($item, $item, $string);
     if (md5($sample) == md5($string))
       return $item;
   }
   return null;
}
?>