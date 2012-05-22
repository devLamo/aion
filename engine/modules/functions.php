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
 Файл: functions.php
-----------------------------------------------------
 Назначение: Основные функции
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
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

function flooder($ip, $news_time = false) {
	global $config, $db;
	
	if ( $news_time ) {

		$this_time = time() + ($config['date_adjust'] * 60) - $news_time;
		$db->query( "DELETE FROM " . PREFIX . "_flood where id < '$this_time' AND flag='1' " );
		
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_flood WHERE ip = '$ip' AND flag='1'");
		
		if( $row['count'] ) return TRUE;
		else return FALSE;

	} else {

		$this_time = time() + ($config['date_adjust'] * 60) - $config['flood_time'];
		$db->query( "DELETE FROM " . PREFIX . "_flood where id < '$this_time' AND flag='0' " );
		
		$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_flood WHERE ip = '$ip' AND flag='0'");
		
		if( $row['count'] ) return TRUE;
		else return FALSE;

	}

}

function totranslit($var, $lower = true, $punkt = true) {
	global $langtranslit;
	
	if ( is_array($var) ) return "";

	if (!is_array ( $langtranslit ) OR !count( $langtranslit ) ) {
		$var = trim( strip_tags( $var ) );
		return preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
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

function msgbox($title, $text) {
	global $tpl;
	
	$tpl_2 = new dle_template( );
	$tpl_2->dir = TEMPLATE_DIR;
	
	$tpl_2->load_template( 'info.tpl' );
	
	$tpl_2->set( '{error}', $text );
	$tpl_2->set( '{title}', $title );
	
	$tpl_2->compile( 'info' );
	$tpl_2->clear();
	
	$tpl->result['info'] .= $tpl_2->result['info'];
}

function ShowRating($id, $rating, $vote_num, $allow = true) {
	global $lang;
	
	if( $rating AND $vote_num ) $rating = round( ($rating / $vote_num), 0 );
	else $rating = 0;
	$rating = $rating * 17;
	
	if( !$allow ) {
		
		$rated = <<<HTML
<div class="rating" style="float:left;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}px;">{$rating}</li>
		</ul>
</div>
HTML;
		
		return $rated;
	}
	
	$rated = <<<HTML
<div id='ratig-layer'><div class="rating" style="float:left;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}px;">{$rating}</li>
		<li><a href="#" title="{$lang['useless']}" class="r1-unit" onclick="doRate('1', '{$id}'); return false;">1</a></li>
		<li><a href="#" title="{$lang['poor']}" class="r2-unit" onclick="doRate('2', '{$id}'); return false;">2</a></li>
		<li><a href="#" title="{$lang['fair']}" class="r3-unit" onclick="doRate('3', '{$id}'); return false;">3</a></li>
		<li><a href="#" title="{$lang['good']}" class="r4-unit" onclick="doRate('4', '{$id}'); return false;">4</a></li>
		<li><a href="#" title="{$lang['excellent']}" class="r5-unit" onclick="doRate('5', '{$id}'); return false;">5</a></li>
		</ul>
</div></div>
HTML;
	
	return $rated;
}

function ShortRating($id, $rating, $vote_num, $allow = true) {
	global $lang;
	
	if( $rating AND $vote_num ) $rating = round( ($rating / $vote_num), 0 );
	else $rating = 0;
	$rating = $rating * 17;
	
	if( ! $allow ) {
		
		$rated = <<<HTML
<div class="rating" style="float:left;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}px;">{$rating}</li>
		</ul>
</div>
HTML;
		
		return $rated;
	}
	
	$rated = "<div id='ratig-layer-" . $id . "'>";
	
	$rated .= <<<HTML
<div class="rating" style="float:left;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}px;">{$rating}</li>
		<li><a href="#" title="{$lang['useless']}" class="r1-unit" onclick="dleRate('1', '{$id}'); return false;">1</a></li>
		<li><a href="#" title="{$lang['poor']}" class="r2-unit" onclick="dleRate('2', '{$id}'); return false;">2</a></li>
		<li><a href="#" title="{$lang['fair']}" class="r3-unit" onclick="dleRate('3', '{$id}'); return false;">3</a></li>
		<li><a href="#" title="{$lang['good']}" class="r4-unit" onclick="dleRate('4', '{$id}'); return false;">4</a></li>
		<li><a href="#" title="{$lang['excellent']}" class="r5-unit" onclick="dleRate('5', '{$id}'); return false;">5</a></li>
		</ul>
</div>
HTML;
	
	$rated .= "</div>";
	
	return $rated;
}

function userrating($id) {
	global $db;
	
	$row = $db->super_query( "SELECT SUM(rating) as rating, SUM(vote_num) as num FROM " . PREFIX . "_post_extras WHERE user_id ='{$id}'" );
	
	if( $row['num'] ) $rating = round( ($row['rating'] / $row['num']), 0 );
	else $rating = 0;
	$rating = $rating * 17;
	
	$rated = <<<HTML
<div class="rating" style="display:inline;">
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}px;">{$rating}</li>
		</ul>
		</div>
HTML;
	
	return $rated;
}

function CategoryNewsSelection($categoryid = 0, $parentid = 0, $nocat = TRUE, $sublevelmarker = '', $returnstring = '') {
	global $cat_info, $user_group, $member_id, $dle_module;

	if ($dle_module == 'addnews') $allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	else $allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );

	$spec_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );

	$root_category = array ();
	
	if( $parentid == 0 ) {
		if( $nocat ) $returnstring .= '<option value="0"></option>';
	} else {
		$sublevelmarker .= '&nbsp;&nbsp;&nbsp;';
	}
	
	if( count( $cat_info ) ) {
		
		foreach ( $cat_info as $cats ) {
			if( $cats['parentid'] == $parentid ) $root_category[] = $cats['id'];
		}
		
		if( count( $root_category ) ) {
			foreach ( $root_category as $id ) {
				
				if( $allow_list[0] == "all" OR in_array( $id, $allow_list ) ) {
					
					if( $spec_list[0] == "all" or in_array( $id, $spec_list ) ) $color = "black";
					else $color = "red";
					
					$returnstring .= "<option style=\"color: {$color}\" value=\"" . $id . '" ';
					
					if( is_array( $categoryid ) ) {
						foreach ( $categoryid as $element ) {
							if( $element == $id ) $returnstring .= 'SELECTED';
						}
					} elseif( $categoryid == $id ) $returnstring .= 'SELECTED';
					
					$returnstring .= '>' . $sublevelmarker . $cat_info[$id]['name'] . '</option>';
				}
				$returnstring = CategoryNewsSelection( $categoryid, $id, $nocat, $sublevelmarker, $returnstring );
			}
		}
	}
	return $returnstring;
}

function get_ID($cat_info, $category) {
	foreach ( $cat_info as $cats ) {
		if( $cats['alt_name'] == $category ) return $cats['id'];
	}
	return false;
}

function set_vars($file, $data) {
	
	$fp = fopen( ENGINE_DIR . '/cache/system/' . $file . '.php', 'wb+' );
	fwrite( $fp, serialize( $data ) );
	fclose( $fp );
	
	@chmod( ENGINE_DIR . '/cache/system/' . $file . '.php', 0666 );
}

function get_vars($file) {
	
	return unserialize( @file_get_contents( ENGINE_DIR . '/cache/system/' . $file . '.php' ) );
}

function filesize_url($url) {
	return ($data = @file_get_contents( $url )) ? strlen( $data ) : false;
}

function dle_cache($prefix, $cache_id = false, $member_prefix = false) {
	global $config, $is_logged, $member_id, $mcache;
	
	if( $config['allow_cache'] != "yes" ) return false;
	
	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		
		$key = $prefix;
	
	} else {
		
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}

	if ( $mcache ) {

		return memcache_get( $mcache, md5( DBNAME . PREFIX . md5(DBUSER) .$key ) );

	} else {
	
		return @file_get_contents( ENGINE_DIR . "/cache/" . $key . ".tmp" );

	}
}

function create_cache($prefix, $cache_text, $cache_id = false, $member_prefix = false) {
	global $config, $is_logged, $member_id, $mcache;
	
	if( $config['allow_cache'] != "yes" ) return false;
	
	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		$key = $prefix;
	} else {
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}
	

	if ( $mcache ) {

		memcache_set( $mcache, md5( DBNAME . PREFIX . md5(DBUSER) .$key ), $cache_text, MEMCACHE_COMPRESSED, 86400 );

	} else {

		file_put_contents (ENGINE_DIR . "/cache/" . $key . ".tmp", $cache_text, LOCK_EX);
		
		@chmod( ENGINE_DIR . "/cache/" . $key . ".tmp", 0666 );
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

function ChangeSkin($dir, $skin) {
	
	$templates_list = array ();
	
	$handle = opendir( $dir );
	
	while ( false !== ($file = readdir( $handle )) ) {
		if( @is_dir( "./templates/$file" ) and ($file != "." AND $file != ".." AND $file != "smartphone") ) {
			$templates_list[] = $file;
		}
	}
	
	closedir( $handle );
	sort($templates_list);
	
	$skin_list = "<form method=\"post\" action=\"\"><select onchange=\"submit()\" name=\"skin_name\">";
	
	foreach ( $templates_list as $single_template ) {
		if( $single_template == $skin ) $selected = " selected=\"selected\"";
		else $selected = "";
		$skin_list .= "<option value=\"$single_template\"" . $selected . ">$single_template</option>";
	}
	
	$skin_list .= '</select><input type="hidden" name="action_skin_change" value="yes" /></form>';
	
	return $skin_list;
}

function custom_print($custom_category, $custom_template, $aviable, $custom_from, $custom_limit, $custom_cache, $do) {
	global $db, $is_logged, $member_id, $xf_inited, $cat_info, $config, $user_group, $category_id, $_TIME, $lang, $smartphone_detected, $dle_module, $PHP_SELF;
	
	$do = $do ? $do : "main";
	$aviable = explode( '|', $aviable );
	
	if( ! (in_array( $do, $aviable )) and ($aviable[0] != "global") ) return "";
	
	$custom_category = $db->safesql( str_replace( ',', '|', $custom_category ) );
	$custom_from = intval( $custom_from );
	$custom_limit = intval( $custom_limit );
	$thisdate = date( "Y-m-d H:i:s", (time() + $config['date_adjust'] * 60) );
	
	if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'";
	else $where_date = "";
	
	$tpl = new dle_template( );
	$tpl->dir = TEMPLATE_DIR;
	
	if( $custom_cache == "yes" ) $config['allow_cache'] = "yes";
	else $config['allow_cache'] = false;
	if( $is_logged and ($user_group[$member_id['user_group']]['allow_edit'] and ! $user_group[$member_id['user_group']]['allow_all_edit']) ) $config['allow_cache'] = false;

	$custom_cache_id = "custom_cat_" . $custom_category . "template_" . $custom_template . "from_" . $custom_from . "limit_" . $custom_limit;
	
	$content = dle_cache( "news", $custom_cache_id, true );
	
	if( $content ) {
		return $content;
	} else {
		
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		
		if( $allow_list[0] != "all" ) {
			
			if( $config['allow_multi_category'] ) {
				
				$stop_list = "category regexp '[[:<:]](" . implode( '|', $allow_list ) . ")[[:>:]]' AND ";
			
			} else {
				
				$stop_list = "category IN ('" . implode( "','", $allow_list ) . "') AND ";
			
			}
		
		} else
			$stop_list = "";
		
		if( $user_group[$member_id['user_group']]['allow_short'] ) $stop_list = "";
		
		if( $cat_info[$custom_category]['news_sort'] != "" ) $news_sort = $cat_info[$custom_category]['news_sort']; else $news_sort = $config['news_sort'];
		if( $cat_info[$custom_category]['news_msort'] != "" ) $news_msort = $cat_info[$custom_category]['news_msort']; else $news_msort = $config['news_msort'];
		
		if( $config['allow_multi_category'] ) {
			
			$where_category = "category regexp '[[:<:]](" . $custom_category . ")[[:>:]]'";
		
		} else {
			
			$custom_category = str_replace( "|", "','", $custom_category );
			$where_category = "category IN ('" . $custom_category . "')";
		
		}

		if ($config['allow_fixed']) $fixed = "fixed desc, ";
		else $fixed = "";
		
		$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE " . $stop_list . $where_category . " AND approve=1" . $where_date . " ORDER BY " . $fixed . $news_sort . " " . $news_msort . " LIMIT " . $custom_from . "," . $custom_limit;
		
		include (ENGINE_DIR . '/modules/show.custom.php');
		
		if( $config['files_allow'] == "yes" ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
			$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
		}
		
		create_cache( "news", $tpl->result['content'], $custom_cache_id, true );
	
	}
	return $tpl->result['content'];
}

function check_ip($ips) {
	
	$_IP = $_SERVER['REMOTE_ADDR'];
	
	$blockip = FALSE;
	
	if( is_array( $ips ) ) {
		foreach ( $ips as $ip_line ) {
			
			$ip_arr = rtrim( $ip_line['ip'] );
			
			$ip_check_matches = 0;
			$db_ip_split = explode( ".", $ip_arr );
			$this_ip_split = explode( ".", $_IP );
			
			for($i_i = 0; $i_i < 4; $i_i ++) {
				if( $this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*' ) {
					$ip_check_matches += 1;
				}
			
			}
			
			if( $ip_check_matches == 4 ) {
				$blockip = $ip_line['ip'];
				break;
			}
		
		}
	}
	
	return $blockip;
}

function check_netz($ip1, $ip2) {
	
	$ip1 = explode( ".", $ip1 );
	$ip2 = explode( ".", $ip2 );
	
	if( $ip1[0] != $ip2[0] ) return false;
	if( $ip1[1] != $ip2[1] ) return false;
	
	return true;

}

function show_attach($story, $id, $static = false) {
	global $db, $config, $lang, $user_group, $member_id;

	$find_1 = array();
	$find_2 = array();
	$replace_1 = array();
	$replace_2 = array();
	
	if( $static ) {
		
		if( is_array( $id ) and count( $id ) ) $where = "static_id IN (" . implode( ",", $id ) . ")";
		else $where = "static_id = '".intval($id)."'";
		
		$db->query( "SELECT id, name, onserver, dcount FROM " . PREFIX . "_static_files WHERE $where" );
		
		$area = "&amp;area=static";
	
	} else {
		
		if( is_array( $id ) and count( $id ) ) $where = "news_id IN (" . implode( ",", $id ) . ")";
		else $where = "news_id = '".intval($id)."'";
		
		$db->query( "SELECT id, name, onserver, dcount FROM " . PREFIX . "_files WHERE $where" );
		
		$area = "";
	
	}
	
	while ( $row = $db->get_row() ) {
		
		$size = formatsize( @filesize( ROOT_DIR . '/uploads/files/' . $row['onserver'] ) );
		$row['name'] = explode( "/", $row['name'] );
		$row['name'] = end( $row['name'] );

		$find_1[] = '[attachment=' . $row['id'] . ']';
		$find_2[] = "#\[attachment={$row['id']}:(.+?)\]#i";

		if ( ! $user_group[$member_id['user_group']]['allow_files'] ) {

			$replace_1[] = "<span class=\"attachment\">{$lang['att_denied']}</span>";
			$replace_2[] = "<span class=\"attachment\">{$lang['att_denied']}</span>";

		} elseif( $config['files_count'] == 'yes' ) {

			$replace_1[] = "<span class=\"attachment\"><a href=\"{$config['http_home_url']}engine/download.php?id={$row['id']}{$area}\" >{$row['name']}</a> [{$size}] ({$lang['att_dcount']} {$row['dcount']})</span>";
			$replace_2[] = "<span class=\"attachment\"><a href=\"{$config['http_home_url']}engine/download.php?id={$row['id']}{$area}\" >\\1</a> [{$size}] ({$lang['att_dcount']} {$row['dcount']})</span>";

		} else {

			$replace_1[] = "<span class=\"attachment\"><a href=\"{$config['http_home_url']}engine/download.php?id={$row['id']}{$area}\" >{$row['name']}</a> [{$size}]</span>";
			$replace_2[] = "<span class=\"attachment\"><a href=\"{$config['http_home_url']}engine/download.php?id={$row['id']}{$area}\" >\\1</a> [{$size}]</span>";

		}

	}

	$db->free();

	$story = str_replace ( $find_1, $replace_1, $story );
	$story = preg_replace( $find_2, $replace_2, $story );
	
	return $story;

}

function xfieldsload($profile = false) {
	global $lang;
	
	if( $profile ) $path = ENGINE_DIR . '/data/xprofile.txt';
	else $path = ENGINE_DIR . '/data/xfields.txt';
	
	$filecontents = file( $path );
	
	if( ! is_array( $filecontents ) ) msgbox( "System error", "File <b>{$path}</b> not found" );
	else {
		foreach ( $filecontents as $name => $value ) {
			$filecontents[$name] = explode( "|", trim( $value ) );
			foreach ( $filecontents[$name] as $name2 => $value2 ) {
				$value2 = str_replace( "&#124;", "|", $value2 );
				$value2 = str_replace( "__NEWL__", "\r\n", $value2 );
				$filecontents[$name][$name2] = $value2;
			}
		}
	}
	return $filecontents;
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

function create_keywords($story) {
	global $metatags, $config;
	
	$keyword_count = 20;
	$newarr = array ();
	
	$quotes = array ("\x22", "\x60", "\t", "\n", "\r", ",", ".", "/", "¬", "#", ";", ":", "@", "~", "[", "]", "{", "}", "=", "-", "+", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"');
	$fastquotes = array ("\x22", "\x60", "\t", "\n", "\r", '"', "\\", '\r', '\n', "/", "{", "}", "[", "]" );
	
	$story = preg_replace( "#\[hide\](.+?)\[/hide\]#is", "", $story );
	$story = preg_replace( "'\[attachment=(.*?)\]'si", "", $story );
	$story = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $story );
	$story = str_replace( "{PAGEBREAK}", "", $story );
	$story = str_replace( "&nbsp;", " ", $story );
	$story = str_replace( '<br />', ' ', $story );
	$story = strip_tags( $story );
	$story = preg_replace( "#&(.+?);#", "", $story );
	$story = trim(str_replace( " ,", "", stripslashes( $story )));
	
	$story = str_replace( $fastquotes, '', $story );
	
	$metatags['description'] = dle_substr( $story, 0, 190, $config['charset'] );
	
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
	
	$metatags['keywords'] = implode( ", ", $arr );
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

function news_permission($id) {
	
	if( $id == "" ) return;
	
	$data = array ();
	$groups = explode( "||", $id );
	foreach ( $groups as $group ) {
		list ( $groupid, $groupvalue ) = explode( ":", $group );
		$data[$groupid] = $groupvalue;
	}
	return $data;
}

function bannermass($fest, $massiv) {
	return $fest . $massiv[@array_rand( $massiv )]['text'];
}

function get_sub_cats($id, $subcategory = '') {
	
	global $cat_info;
	$subfound = array ();
	
	if( $subcategory == '' ) $subcategory = $id;
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		$subcategory .= "|" . $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
	}
	
	return $subcategory;

}

function check_xss() {

	if ($_GET['do'] == "xfsearch") return;
	
	$url = html_entity_decode( urldecode( $_SERVER['QUERY_STRING'] ) );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false) ) {
			if( $_GET['do'] != "search" OR $_GET['subaction'] != "search" ) die( "Hacking attempt!" );
		}
	
	}
	
	$url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ) );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, '\'' ) !== false) ) {
			if( $_GET['do'] != "search" OR $_GET['subaction'] != "search" ) die( "Hacking attempt!" );
		
		}
	
	}

}

function check_category($cats, $block, $category, $action = true) {

	$cats = str_replace(" ", "", $cats );	
	$cats = explode( ',', $cats );
	$category = explode( ',', $category );
	$found = false;
	
	foreach ( $category as $element ) {
		
		if( $action ) {
			
			if( in_array( $element, $cats ) ) {
				
				$block = str_replace( '\"', '"', $block );
				return $block;
			}
		
		} else {
			
			if( in_array( $element, $cats ) ) {
				$found = true;
			}
		
		}
	
	}

	if ( !$action AND !$found ) {	

		$block = str_replace( '\"', '"', $block  );
		return $block;
	}

	return "";

}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", strtolower( $url ) );
	$url = str_replace( "https://", "", $url );
	if( substr( $url, 0, 4 ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
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

function get_categories($id) {
	
	global $cat_info, $config, $PHP_SELF;
	
	if( ! $id ) return;
	
	$parent_id = $cat_info[$id]['parentid'];
	
	if( $config['allow_alt_url'] == "yes" ) $list = "<a href=\"" . $config['http_home_url'] . get_url( $id ) . "/\">{$cat_info[$id]['name']}</a>";
	else $list = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$id]['alt_name']}\">{$cat_info[$id]['name']}</a>";
	
	while ( $parent_id ) {
		
		if( $config['allow_alt_url'] == "yes" ) $list = "<a href=\"" . $config['http_home_url'] . get_url( $parent_id ) . "/\">{$cat_info[$parent_id]['name']}</a>" . " &raquo; " . $list;
		else $list = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$parent_id]['alt_name']}\">{$cat_info[$parent_id]['name']}</a>" . " &raquo; " . $list;
		
		$parent_id = $cat_info[$parent_id]['parentid'];
		
		if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
	
	}
	
	return $list;
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

function news_sort($do) {
	
	global $config, $lang;
	
	if( ! $do ) $do = "main";
	
	$find_sort = "dle_sort_" . $do;
	$direction_sort = "dle_direction_" . $do;
	
	$find_sort = str_replace( ".", "", $find_sort );
	$direction_sort = str_replace( ".", "", $direction_sort );
	
	$sort = array ();
	$allowed_sort = array ('date', 'rating', 'news_read', 'comm_num', 'title' );
	
	$soft_by_array = array (

	'date' => array (

	'name' => $lang['sort_by_date'], 'value' => "date", 'direction' => "desc", 'image' => "" ), 

	'rating' => array (

	'name' => $lang['sort_by_rating'], 'value' => "rating", 'direction' => "desc", 'image' => "" ), 

	'news_read' => array (

	'name' => $lang['sort_by_read'], 'value' => "news_read", 'direction' => "desc", 'image' => "" ), 

	'comm_num' => array (

	'name' => $lang['sort_by_comm'], 'value' => "comm_num", 'direction' => "desc", 'image' => "" ), 

	'title' => array (

	'name' => $lang['sort_by_title'], 'value' => "title", 'direction' => "desc", 'image' => "" )

	 );
	
	if( isset( $_SESSION[$direction_sort] ) AND ($_SESSION[$direction_sort] == "desc" OR $_SESSION[$direction_sort] == "asc") ) $direction = $_SESSION[$direction_sort];
	else $direction = $config['news_msort'];

	if( isset( $_SESSION[$find_sort] ) AND $_SESSION[$find_sort] AND in_array( $_SESSION[$find_sort], $allowed_sort ) ) $soft_by = $_SESSION[$find_sort];
	else $soft_by = $config['news_sort'];
	
	if( strtolower( $direction ) == "asc" ) {
		
		$soft_by_array[$soft_by]['image'] = "<img src=\"{THEME}/dleimages/asc.gif\" alt=\"\" />";
		$soft_by_array[$soft_by]['direction'] = "desc";
	
	} else {
		
		$soft_by_array[$soft_by]['image'] = "<img src=\"{THEME}/dleimages/desc.gif\" alt=\"\" />";
		$soft_by_array[$soft_by]['direction'] = "asc";
	}
	
	foreach ( $soft_by_array as $value ) {
		
		$sort[] = $value['image'] . "<a href=\"#\" onclick=\"dle_change_sort('{$value['value']}','{$value['direction']}'); return false;\">" . $value['name'] . "</a>";
	}
	
	$sort = "<form name=\"news_set_sort\" id=\"news_set_sort\" method=\"post\" action=\"\" >" . $lang['sort_main'] . "&nbsp;" . implode( " | ", $sort );
	
	$sort .= <<<HTML
<input type="hidden" name="dlenewssortby" id="dlenewssortby" value="{$config['news_sort']}" />
<input type="hidden" name="dledirection" id="dledirection" value="{$config['news_msort']}" />
<input type="hidden" name="set_new_sort" id="set_new_sort" value="{$find_sort}" />
<input type="hidden" name="set_direction_sort" id="set_direction_sort" value="{$direction_sort}" />
<script type="text/javascript" language="javascript">
<!-- begin

function dle_change_sort(sort, direction){

  var frm = document.getElementById('news_set_sort');

  frm.dlenewssortby.value=sort;
  frm.dledirection.value=direction;

  frm.submit();
  return false;
};

// end -->
</script></form>
HTML;
	
	return $sort;
}

function compare_tags($a, $b) {
	
	if( $a['tag'] == $b['tag'] ) return 0;
	
	return strcasecmp( $a['tag'], $b['tag'] );

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

function check_smartphone() {

	if ( $_SESSION['mobile_enable'] ) return true;

	$phone_array = array('iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'mobile windows', 'cellphone', 'opera mobi', 'operamobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'symbos', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser', 'android');
	$agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

	foreach ($phone_array as $value) {

		if ( strpos($agent, $value) !== false ) return true;

	}

	return false;

}

function build_js($js, $config) {

	$js_array = array();

	if ($config['js_min'] AND version_compare(PHP_VERSION, '5.1.0', '>') ) {

		$js_array[] = "<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/classes/min/index.php?charset={$config['charset']}&amp;g=general&amp;7\"></script>";

		if ( count($js) ) $js_array[] = "<script type=\"text/javascript\" src=\"{$config['http_home_url']}engine/classes/min/index.php?charset={$config['charset']}&amp;f=".implode(",", $js)."&amp;7\"></script>";

		return implode("\n", $js_array);

	} else {

		$default_array = array (
			'engine/classes/js/jquery.js',
			'engine/classes/js/jqueryui.js',
			'engine/classes/js/dle_js.js',
		);

		$js = array_merge($default_array, $js);

		foreach ($js as $value) {
		
			$js_array[] = "<script type=\"text/javascript\" src=\"{$config['http_home_url']}{$value}\"></script>";
		
		}

		return implode("\n", $js_array);
	}
}

function check_static($names, $block, $action = true) {
	global $dle_module;

	$names = str_replace(" ", "", $names );
	$names = explode( ',', $names );

	if ( isset($_GET['page']) ) $page = trim($_GET['page']); else $page = "";
	
	if( $action ) {
			
		if( in_array( $page, $names ) AND $dle_module == "static" ) {
				
			$block = str_replace( '\"', '"', $block );
			return $block;
		}
		
	} else {
			
		if( !in_array( $page, $names ) OR $dle_module != "static") {
				
			$block = str_replace( '\"', '"', $block  );
			return $block;
		}
		
	}
	
	return "";
}


function dle_strlen($value, $charset ) {

	if ( strtolower($charset) == "utf-8") return iconv_strlen($value, "utf-8");
	else return strlen($value);

}

function dle_substr($str, $start, $length, $charset ) {

	if ( strtolower($charset) == "utf-8") return iconv_substr($str, $start, $length, "utf-8");
	else return substr($str, $start, $length);

}

function dle_strrpos($str, $needle, $charset ) {

	if ( strtolower($charset) == "utf-8") return iconv_strrpos($str, $needle, "utf-8");
	else return strrpos($str, $needle);

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