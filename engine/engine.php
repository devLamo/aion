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
 Файл: engine.php
-----------------------------------------------------
 Назначение: подключение основных компонентов
=====================================================
*/
if (! defined ( 'DATALIFEENGINE' )) {
	die ( "Hacking attempt!" );
}

if ($cstart < 0) $cstart = 0;
$CN_HALT = FALSE;

$allow_add_comment = FALSE;
$allow_active_news = FALSE;
$allow_comments = FALSE;
$allow_userinfo = FALSE;
$active = FALSE;
$newsmodule = FALSE;
$disable_index = FALSE;

switch ( $do ) {
	
	case "search" :
		
		if ($_REQUEST['mode'] == "advanced") $_REQUEST['full_search'] = 1;
		include ENGINE_DIR . '/modules/search.php';
		break;

	case "changemail" :
		include ENGINE_DIR . '/modules/changemail.php';
		break;
	
	case "deletenews" :
		include ENGINE_DIR . '/modules/deletenews.php';
		break;

	case "comments" :
		include ENGINE_DIR . '/modules/comments.php';
		break;
	
	case "stats" :
		include ENGINE_DIR . '/modules/stats.php';
		break;
	
	case "addnews" :
		include ENGINE_DIR . '/modules/addnews.php';
		break;
	
	case "register" :
		include ENGINE_DIR . '/modules/register.php';
		break;
	
	case "lostpassword" :
		include ENGINE_DIR . '/modules/lostpassword.php';
		break;
	
	case "rules" :
		$_GET['page'] = "dle-rules-page";
		include ENGINE_DIR . '/modules/static.php';
		break;
	
	case "static" :
		include ENGINE_DIR . '/modules/static.php';
		break;
	
	case "alltags" :
		include_once ENGINE_DIR . '/modules/tagscloud.php';
		break;
	
	case "favorites" :
		if ($is_logged) {
			
			$config['allow_cache'] = false;
			
			include ENGINE_DIR . '/modules/favorites.php';
		
		} else
			msgbox ( $lang['all_err_1'], $lang['fav_error'] );
		break;
	
	case "feedback" :
		include ENGINE_DIR . '/modules/feedback.php';
		break;
	
	case "lastcomments" :
		include ENGINE_DIR . '/modules/lastcomments.php';
		break;
	
	case "pm" :
		include ENGINE_DIR . '/modules/pm.php';
		break;

	case "unsubscribe" :
		$_GET['post_id'] = intval ($_GET['post_id']);
		$_GET['user_id'] = intval ($_GET['user_id']);

		if ($_GET['post_id'] AND $_GET['user_id'] AND $_GET['hash']) {

			$row = $db->super_query( "SELECT hash FROM " . PREFIX . "_subscribe WHERE news_id='{$_GET['post_id']}' AND user_id='{$_GET['user_id']}'" );

			if ($row['hash'] AND $row['hash'] == $_GET['hash']) {

				$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE news_id='{$_GET['post_id']}' AND user_id='{$_GET['user_id']}'" );
				msgbox( $lang['all_info'],  $lang['unsubscribe_ok']);

			} else {
				msgbox( $lang['all_info'],  $lang['unsubscribe_err']);
			}

		} else {
			msgbox( $lang['all_info'],  $lang['unsubscribe_err']);
		}

		break;
	
	default :
		
		$is_main = 0;
		$active = false;
		$user_query = "";
		
		$thisdate = date ( "Y-m-d H:i:s", (time () + ($config['date_adjust'] * 60)) );
		if ($config['no_date'] AND !$config['news_future']) $where_date = " AND date < '" . $thisdate . "'";
		else $where_date = "";
		
		if ($config['allow_fixed']) $fixed = "fixed desc, ";
		else $fixed = "";
		
		$config['news_number'] = intval ( $config['news_number'] );

		if ( $smartphone_detected AND $config['mobile_news'] ) $config['news_number'] = intval ( $config['mobile_news'] );

		$attachments = array ();
		
		$news_sort_by = ($config['news_sort']) ? $config['news_sort'] : "date";
		$news_direction_by = ($config['news_msort']) ? $config['news_msort'] : "DESC";
		
		$allow_list = explode ( ',', $user_group[$member_id['user_group']]['allow_cats'] );
		
		if ($allow_list[0] != "all") {
			
			if ($config['allow_multi_category']) {
				
				$stop_list = "category regexp '[[:<:]](" . implode ( '|', $allow_list ) . ")[[:>:]]' AND ";
			
			} else {
				
				$stop_list = "category IN ('" . implode ( "','", $allow_list ) . "') AND ";
			
			}
		
		} else
			$stop_list = "";
		
		if ($user_group[$member_id['user_group']]['allow_short']) $stop_list = "";
		
		$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];

		$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date;
		$sql_news = "";
		
		// ################ Вывод отдельной категории #################
		if ($do == "cat" and $category != '' and $subaction == '') {

			$newsmodule = true;
			
			if( $config['allow_alt_url'] == "yes" AND $config['seo_control'] AND $category_id AND $view_template != "rss") {

				$re_cat = get_url( $category_id );

				if ($re_cat != $_GET['category'] OR substr ( $_SERVER['REQUEST_URI'], - 1, 1 ) != '/' ) {	
					$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
					$re_url = reset ( $re_url );

					header("HTTP/1.0 301 Moved Permanently");
					header("Location: {$re_url}{$re_cat}/");
					die("Redirect");
				}
			}

			if (!$category_id) $category_id = 'not detected';
			
			if ($allow_list[0] != "all") {
				if (! $user_group[$member_id['user_group']]['allow_short'] and ! in_array ( $category_id, $allow_list )) $category_id = 'not detected';
			}
			
			if ($config['show_sub_cats']) $get_cats = get_sub_cats ( $category_id );
			else $get_cats = $category_id;
			
			if ($cat_info[$category_id]['news_sort'] != "") $news_sort_by = $cat_info[$category_id]['news_sort'];
			if ($cat_info[$category_id]['news_msort'] != "") $news_direction_by = $cat_info[$category_id]['news_msort'];
			if ($cat_info[$category_id]['news_number']) $config['news_number'] = $cat_info[$category_id]['news_number'];
			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}
			
			$url_page = $config['http_home_url'] . get_url ( $category_id );
			$user_query = "do=cat&amp;category=" . $cat_info[$category_id]['alt_name'];
			
			if ($config['allow_multi_category']) {
				
				$where_category = "category regexp '[[:<:]](" . $get_cats . ")[[:>:]]'";
			
			} else {
				
				if ($config['show_sub_cats']) {
					
					$get_cats = str_replace ( "|", "','", $get_cats );
					$where_category = "category IN ('" . $get_cats . "')";
				
				} else {
					
					$where_category = "category = '{$get_cats}'";
				
				}
			
			}
			
			if (isset ( $view_template ) and $view_template == "rss") {
				
				$sql_select = "SELECT id, autor, date, short_story, full_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE {$where_category} AND approve=1" . $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];
			
			} else {
				
				if (isset ( $_SESSION['dle_sort_cat'] )) $news_sort_by = $_SESSION['dle_sort_cat'];
				if (isset ( $_SESSION['dle_direction_cat'] )) $news_direction_by = $_SESSION['dle_direction_cat'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$where_category} AND approve=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$where_category} AND approve=1" . $where_date;
			}
		
		} elseif ($do == 'lastnews') {
			// ################ Вывод всех последних новостей #################			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$newsmodule = true;
			
			$url_page = $config['http_home_url'] . "lastnews";
			$user_query = "do=lastnews";
			
			if (isset ( $_SESSION['dle_sort_lastnews'] )) $news_sort_by = $_SESSION['dle_sort_lastnews'];
			else $news_sort_by = "date";
			if (isset ( $_SESSION['dle_direction_lastnews'] )) $news_direction_by = $_SESSION['dle_direction_lastnews'];
			else $news_direction_by = "DESC";
			
			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}approve=1" . $where_date;
		
		} elseif ($do == 'tags') {
			// ################ Поиск новостей по тегу #################			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$newsmodule = true;

			$tag = urldecode ( $_GET['tag'] );

			if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($tag) ) {
				$tag = iconv( "UTF-8", "windows-1251//IGNORE", $tag );
			}

			$tag = @$db->safesql ( htmlspecialchars ( strip_tags ( stripslashes ( trim ( $tag ) ) ), ENT_QUOTES ) );
			$url_page = $config['http_home_url'] . "tags/" . urlencode ( $tag );
			$user_query = "do=tags&amp;tag=" . urlencode ( $tag );
			
			if (isset ( $_SESSION['dle_sort_tags'] )) $news_sort_by = $_SESSION['dle_sort_tags'];
			if (isset ( $_SESSION['dle_direction_tags'] )) $news_direction_by = $_SESSION['dle_direction_tags'];
			
			$db->query ( "SELECT news_id FROM " . PREFIX . "_tags WHERE tag='{$tag}'" );
			
			$tag_array = array ();
			
			while ( $row = $db->get_row () ) {
				
				$tag_array[] = $row['news_id'];
			
			}
			
			if (count ( $tag_array )) {
				
				$tag_array = "(" . implode ( ",", $tag_array ) . ")";
			
			} else {
				
				$tag_array = "('undefined')";
			
			}
			
			$db->free ();
			
			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}p.id IN {$tag_array} AND p.approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}id IN {$tag_array} AND approve=1" . $where_date;
			$allow_active_news = true;
			
			$tag_array = array ();
			unset ( $tag_array );

		} elseif ($do == 'xfsearch') {
			// ################ Поиск новостей по доп. полям #################			
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$newsmodule = true;

			$xf = urldecode ( $_GET['xf'] );

			if ( $config['charset'] == "windows-1251" AND $config['charset'] != detect_encoding($xf) ) {
				$xf = iconv( "UTF-8", "windows-1251//IGNORE", $xf );
			}

			$xf = @$db->safesql ( htmlspecialchars ( strip_tags ( stripslashes ( trim ( $xf ) ) ), ENT_QUOTES ) );
			$url_page = $config['http_home_url'] . "xfsearch/" . urlencode ( str_replace("&#039;", "'", $xf) );
			$user_query = "do=xfsearch&amp;xf=" . urlencode ( str_replace("&#039;", "'", $xf) );
		
			if (isset ( $_SESSION['dle_sort_xfsearch'] )) $news_sort_by = $_SESSION['dle_sort_xfsearch'];
			if (isset ( $_SESSION['dle_direction_xfsearch'] )) $news_direction_by = $_SESSION['dle_direction_xfsearch'];
			
			$sql_select = "SELECT SQL_CALC_FOUND_ROWS p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}xfields LIKE '%{$xf}%' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
			$sql_count = "SELECT FOUND_ROWS() as count";
			$allow_active_news = true;
		
		} elseif ($subaction == 'userinfo') {
			// ################ Вывод профиля пользователя #################
			if ($cstart) {
				
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			
			}
			
			$url_page = $config['http_home_url'] . "user/" . urlencode ( $user );
			$user_query = "subaction=userinfo&user=" . urlencode ( $user );
			
			if ($member_id['name'] == $user or $user_group[$member_id['user_group']]['allow_all_edit']) {
				if (isset ( $_SESSION['dle_sort_userinfo'] )) $news_sort_by = $_SESSION['dle_sort_userinfo'];
				if (isset ( $_SESSION['dle_direction_userinfo'] )) $news_direction_by = $_SESSION['dle_direction_userinfo'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE autor = '{$user}' AND approve=0 ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE autor = '$user' AND approve=0";
				$allow_active_news = true;
			} else {
				$allow_active_news = false;
			}
			
			$config['allow_cache'] = false;
		} elseif ($subaction == 'allnews') {
			// ################ Вывод всех новостей пользователя #################
			if ($cstart) {
				
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			
			}

			$newsmodule = true;
			
			$url_page = $config['http_home_url'] . "user/" . urlencode ( $user ) . "/news";
			$user_query = "subaction=allnews&amp;user=" . urlencode ( $user );
			
			if (isset ( $view_template ) and $view_template == "rss") {
				
				$sql_select = "SELECT id, autor, date, short_story, full_story, xfields, title, category, alt_name FROM " . PREFIX . "_post where {$stop_list}autor = '$user' AND approve=1" . $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];
			
			} else {
				
				if (isset ( $_SESSION['dle_sort_allnews'] )) $news_sort_by = $_SESSION['dle_sort_allnews'];
				if (isset ( $_SESSION['dle_direction_allnews'] )) $news_direction_by = $_SESSION['dle_direction_allnews'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}autor = '$user' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}autor = '$user' AND approve=1" . $where_date;
			}
			
			$allow_active_news = true;
		
		} elseif ($subaction == 'newposts') {
			// ################ Вывод непрочитанных новостей #################
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$newsmodule = true;
			
			$url_page = $config['http_home_url'] . "newposts";
			$user_query = "subaction=newposts";
			
			$thistime = date ( "Y-m-d H:i:s", $_TIME );
			
			if (isset ( $_SESSION['member_lasttime'] )) {
				$lasttime = date ( "Y-m-d H:i:s", $_SESSION['member_lasttime'] );
			} else {
				$lasttime = date ( "Y-m-d H:i:s", (time () + ($config['date_adjust'] * 60) - (3600 * 4)) );
			}
			
			if (isset ( $_SESSION['dle_sort_newposts'] )) $news_sort_by = $_SESSION['dle_sort_newposts'];
			if (isset ( $_SESSION['dle_direction_newposts'] )) $news_direction_by = $_SESSION['dle_direction_newposts'];
			
			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1 AND date between '$lasttime' and '$thistime' order by " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}approve=1 AND date between '$lasttime' and '$thistime'";
			
			$config['allow_cache'] = false;
		} elseif ($catalog != "") {
			// ################ Вывод по буквенному идентификатору #################
			if ($cstart) {
				$cstart = $cstart - 1;
				$cstart = $cstart * $config['news_number'];
			}

			$newsmodule = true;
			
			$url_page = $config['http_home_url'] . "catalog/" . urlencode ( $catalog );
			$user_query = "catalog=" . urlencode ( $catalog );
			
			$news_sort_by = ($config['catalog_sort']) ? $config['catalog_sort'] : "date";
			$news_direction_by = ($config['catalog_msort']) ? $config['catalog_msort'] : "DESC";
			
			if (isset ( $_SESSION['dle_sort_catalog'] )) $news_sort_by = $_SESSION['dle_sort_catalog'];
			if (isset ( $_SESSION['dle_direction_catalog'] )) $news_direction_by = $_SESSION['dle_direction_catalog'];
			
			$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}symbol = '$catalog' AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
			$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}symbol = '$catalog' AND approve=1" . $where_date;

		} else {
			
			// ################ Новости на главной #################
			if ($year == '' and $month == '' and $day == '') {
				if ($cstart) {
					
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
					$newsmodule = true;			
				}
				
				$url_page = substr ( $config['http_home_url'], 0, strlen ( $config['http_home_url'] ) - 1 );
				$user_query = "";
				
				if (isset ( $view_template ) and $view_template == "rss") {
					
					$sql_select = "SELECT id, autor, date, short_story, full_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE {$stop_list}approve=1";
					
					if ($config['rss_mtype']) {
						
						$sql_select .= " AND allow_main=1";
					
					}
					
					$sql_select .= $where_date . " ORDER BY date DESC LIMIT 0," . $config['rss_number'];
				
				} else {
					
					if (isset ( $_SESSION['dle_sort_main'] )) $news_sort_by = $_SESSION['dle_sort_main'];
					if (isset ( $_SESSION['dle_direction_main'] )) $news_direction_by = $_SESSION['dle_direction_main'];
					
					$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date . " ORDER BY " . $fixed . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
					$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}approve=1 AND allow_main=1" . $where_date;
				
				}
			}
			
			// ################ Новости за год #################
			if ($year != '' and $month == '' and $day == '') {
				if ($cstart) {
					
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}

				$newsmodule = true;
				
				$url_page = $config['http_home_url'] . $year;
				$user_query = "year=" . $year;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}date >= '{$year}-01-01'AND date < '{$year}-01-01' + INTERVAL 1 YEAR AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post where {$stop_list}date >= '{$year}-01-01'AND date < '{$year}-01-01' + INTERVAL 1 YEAR AND approve=1" . $where_date;
			}
			
			// ################ Новости за месяц #################
			if ($year != '' and $month != '' and $day == '') {
				if ($cstart) {
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}

				$newsmodule = true;
				$url_page = $config['http_home_url'] . $year . "/" . $month;
				$user_query = "year=" . $year . "&amp;month=" . $month;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}date >= '{$year}-{$month}-01'AND date < '{$year}-{$month}-01' + INTERVAL 1 MONTH AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post where {$stop_list}date >= '{$year}-{$month}-01'AND date < '{$year}-{$month}-01' + INTERVAL 1 MONTH AND approve=1" . $where_date;
			}
			
			// ################ Новости за день #################
			if ($year != '' and $month != '' and $day != '' and $subaction == '') {
				if ($cstart) {
					$cstart = $cstart - 1;
					$cstart = $cstart * $config['news_number'];
				}

				$newsmodule = true;				
				$url_page = $config['http_home_url'] . $year . "/" . $month . "/" . $day;
				$user_query = "year=" . $year . "&amp;month=" . $month . "&amp;day=" . $day;
				
				if (isset ( $_SESSION['dle_sort_date'] )) $news_sort_by = $_SESSION['dle_sort_date'];
				if (isset ( $_SESSION['dle_direction_date'] )) $news_direction_by = $_SESSION['dle_direction_date'];
				
				$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, SUBSTRING(p.full_story, 1, 15) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE {$stop_list}date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR AND approve=1" . $where_date . " ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['news_number'];
				$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE {$stop_list}date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR AND approve=1" . $where_date;
			
			}
			
			// ################ Новость целиком #################
			if ($subaction != '' or $newsid) {
				if (! $newsid) $sql_news = "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE alt_name ='$news_name' AND date >= '{$year}-{$month}-{$day}' AND date < '{$year}-{$month}-{$day}' + INTERVAL 24 HOUR LIMIT 1";
				else $sql_news = "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE  id = '{$newsid}'";
				
				if ($subaction == '') $subaction = "showfull";
			}
		}
		
		if (($subaction == "showfull" or $subaction == "addcomment") and ((! isset ( $category ) or $category == ""))) {
			
			//####################################################################################################################
			//             Добавление комментария в базу данных
			//####################################################################################################################
			if (isset( $_POST['subaction'] ) AND $_POST['subaction'] == "addcomment") {
				
				$allow_add_comment = TRUE;
				$allow_comments = TRUE;
				$ajax_adds = false;
				
				include_once (ENGINE_DIR . '/modules/addcomments.php');
			}
			//####################################################################################################################
			//         Просмотр полной новости
			//####################################################################################################################
			if ($subaction == "showfull") {
				$allow_comments = TRUE;
				
				include_once (ENGINE_DIR . '/modules/show.full.php');
			}
		
		} else {
			
			//####################################################################################################################
			//         Просмотр профиля пользователя
			//####################################################################################################################
			if ($subaction == 'userinfo') {
				
				$allow_userinfo = TRUE;
				include_once (ENGINE_DIR . '/modules/profile.php');
			
			} else {
				$allow_active_news = TRUE;
			}
			
			//####################################################################################################################
			//         Просмотр кратких новостей
			//####################################################################################################################
			

			$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
			
			if ($catalog != "") {
				
				$cache_prefix = "_catalog_" . $catalog;
			
			} elseif ($do == "lastnews") {
				
				$cache_prefix = "_lastnews";
			
			} elseif ($subaction == 'allnews') {

				$cache_prefix = "_allnews_". $user;

			} elseif ($do == 'tags') {

				$cache_prefix = "_tagscl_". $tag;

			} elseif ($do == 'xfsearch') {

				$cache_prefix = "_xfsearch_". $xf;

			} else {
				
				$cache_prefix = "_";
				
				if ($month) $cache_prefix .= "month_" . $month;
				if ($year) $cache_prefix .= "year_" . $year;
				if ($day) $cache_prefix .= "day_" . $day;
				if ($category) $cache_prefix .= "category_" . $category;
			}
			
			$cache_prefix .= "_tempate_" . $config['skin'];
			
			if (isset($view_template) AND $view_template == "rss") {

				if ($catalog) $active = dle_cache ( "rss", $catalog, false );				
				else $active = dle_cache ( "rss", $category_id, false );
			
			} else {
				
				if ($is_logged and ($user_group[$member_id['user_group']]['allow_edit'] and ! $user_group[$member_id['user_group']]['allow_all_edit'])) $config['allow_cache'] = false;
				if (isset($_SESSION['dle_no_cache']) AND $_SESSION['dle_no_cache']) $config['allow_cache'] = false;
				if ($cstart) $cache_id = ($cstart / $config['news_number']) + 1;
				else $cache_id = "1";
				
				if ($cache_id < 6 and !isset ( $_POST['login'] )) $active = dle_cache ( "news", $cache_id . $cache_prefix, true );
				else $active = false;
			
			}
			
			if ($active) {
		
				$tpl->result['content'] .= $active;
				$active = null;
				if ($config['allow_quick_wysiwyg'] and ($user_group[$member_id['user_group']]['allow_edit'] or $user_group[$member_id['user_group']]['allow_all_edit'])) $allow_comments_ajax = true;
				else $allow_comments_ajax = false;
			
			} else {
				
				include_once (ENGINE_DIR . '/modules/show.short.php');
				
				if (! $config['allow_quick_wysiwyg']) $allow_comments_ajax = false;
				
				if ($config['files_allow'] == "yes") if (strpos ( $tpl->result['content'], "[attachment=" ) !== false) {
					$tpl->result['content'] = show_attach ( $tpl->result['content'], $attachments );
				}
				
				if (isset($view_template) AND $view_template == "rss" AND $news_found) {
					
					if ($catalog) create_cache ( "rss", $tpl->result['content'], $catalog, false );
					else create_cache ( "rss", $tpl->result['content'], $category_id, false );
				
				} elseif ($news_found and $cache_id < 6) create_cache ( "news", $tpl->result['content'], $cache_id . $cache_prefix, true );
			
			}
		
		}

}

/*
=====================================================
 Вывод заголовка страницы 
=====================================================
*/
$titl_e = '';
$nam_e = '';
$rss_url = '';

if ($do == "cat" and $category != '' and $subaction == '') {
	
	$metatags['description'] = ($cat_info[$category_id]['descr'] != '') ? $cat_info[$category_id]['descr'] : $metatags['description'];
	$metatags['keywords'] = ($cat_info[$category_id]['keywords'] != '') ? $cat_info[$category_id]['keywords'] : $metatags['keywords'];

	if ($cat_info[$category_id]['metatitle'] != '') $metatags['header_title'] = $cat_info[$category_id]['metatitle'];
	else $nam_e = stripslashes ( $cat_info[$category_id]['name'] );
	
	if ($config['allow_alt_url'] == "yes") {
		$rss_url = $url_page . "/" . "rss.xml";
	} else {
		$rss_url = $config['http_home_url'] . "engine/rss.php?do=cat&category=" . $cat_info[$category_id]['alt_name'];
	}

} elseif ($subaction == 'userinfo') {
	$nam_e = $user;
	
	if ($config['allow_alt_url'] == "yes") {
		$rss_url = $url_page . "/" . "rss.xml";
	} else {
		$rss_url = $config['http_home_url'] . "engine/rss.php?subaction=allnews&user=" . urlencode ( $user );
	}

} elseif ($subaction == 'allnews') {
	$nam_e = $lang['show_user_news'] . ' ' . $user;
	
	if ($config['allow_alt_url'] == "yes") {
		$rss_url = $config['http_home_url'] . "user/" . urlencode ( $user ) . "/" . "rss.xml";
	} else {
		$rss_url = $config['http_home_url'] . "engine/rss.php?subaction=allnews&user=" . urlencode ( $user );
	}

} elseif ($subaction == 'newposts') $nam_e = $lang['title_new'];
elseif ($do == 'stats') $nam_e = $lang['title_stats'];
elseif ($do == 'addnews') $nam_e = $lang['title_addnews'];
elseif ($do == 'register') $nam_e = $lang['title_register'];
elseif ($do == 'favorites') $nam_e = $lang['title_fav'];
elseif ($do == 'pm') $nam_e = $lang['title_pm'];
elseif ($do == 'feedback') $nam_e = $lang['title_feed'];
elseif ($do == 'lastcomments') $nam_e = $lang['title_last'];
elseif ($do == 'lostpassword') $nam_e = $lang['title_lost'];
elseif ($do == 'search') $nam_e = $lang['title_search'];
elseif ($do == 'static') $titl_e = $static_descr;
elseif ($do == 'lastnews') $nam_e = $lang['last_news'];
elseif ($do == 'alltags') $nam_e = $lang['tag_cloud'];
elseif ($do == 'tags') $nam_e = $tag;
elseif ($do == 'xfsearch') $nam_e = $xf;
elseif ($catalog != "") { 
	$nam_e = $lang['title_catalog'] . ' &raquo; ' . $catalog;

	if ($config['allow_alt_url'] == "yes") {
		$rss_url = $config['http_home_url'] . "catalog/" . urlencode ( $catalog ) . "/" . "rss.xml";
	} else {
		$rss_url = $config['http_home_url'] . "engine/rss.php?catalog=" . urlencode ( $catalog );
	}

}
else {
	
	if ($year != '' and $month == '' and $day == '') $nam_e = $lang['title_date'] . ' ' . $year . ' ' . $lang['title_year'];
	if ($year != '' and $month != '' and $day == '') $nam_e = $lang['title_date'] . ' ' . $r[$month - 1] . ' ' . $year . ' ' . $lang['title_year1'];
	if ($year != '' and $month != '' and $day != '' and $subaction == '') $nam_e = $lang['title_date'] . ' ' . $day . '.' . $month . '.' . $year;
	if (($subaction != '' or $newsid != '') and $news_found) $titl_e = $metatags['title'];

}

if (intval($_GET['cstart']) > 1 ){

	$page_extra = ' &raquo; '.$lang['news_site'].' '.intval($_GET['cstart']);

} else $page_extra = '';

if ($nam_e) {

	$metatags['title'] = $nam_e . $page_extra . ' &raquo; ' . $metatags['title'];
	$rss_title = $metatags['title'];

} elseif ($titl_e) {

	$metatags['title'] = $titl_e . $page_extra . ' &raquo; ' . $config['home_title'];

} else $metatags['title'] .= $page_extra;

if ( $metatags['header_title'] ) $metatags['title'] = stripslashes($metatags['header_title'].$page_extra);
if ( $disable_index ) $disable_index = "\n<meta name=\"robots\" content=\"noindex,nofollow\" />"; else $disable_index = "";

if (! $rss_url) {
	
	if ($config['allow_alt_url'] == "yes") {
		$rss_url = $config['http_home_url'] . "rss.xml";
	} else {
		$rss_url = $config['http_home_url'] . "engine/rss.php";
	}
	
	$rss_title = $config['home_title'];
}

$metatags = <<<HTML
<meta http-equiv="Content-Type" content="text/html; charset={$config['charset']}" />
<title>{$metatags['title']}</title>
<meta name="description" content="{$metatags['description']}" />
<meta name="keywords" content="{$metatags['keywords']}" />{$disable_index}
<meta name="generator" content="DataLife Engine Nulled by M.I.D-Team (http://www.mid-team.ws)" />
<link rel="search" type="application/opensearchdescription+xml" href="{$config['http_home_url']}engine/opensearch.php" title="{$config['home_title']}" />
HTML;

if ($config['allow_rss']) $metatags .= <<<HTML

<link rel="alternate" type="application/rss+xml" title="{$rss_title}" href="{$rss_url}" />
HTML;

/*
=====================================================
 Формирование speedbar 
=====================================================
*/
if ($config['speedbar'] and ! isset ( $view_template )) {
	
	$s_navigation = "<a href=\"{$config['http_home_url']}\">" . $config['short_title'] . "</a>";
	
	if ($category_id) $s_navigation .= " &raquo; " . get_categories ( $category_id );
	elseif ($do == 'tags') {
		
		if ($config['allow_alt_url'] == "yes") $s_navigation .= " &raquo; <a href=\"" . $config['http_home_url'] . "tags/\">" . $lang['tag_cloud'] . "</a> &raquo; " . $tag;
		else $s_navigation .= " &raquo; <a href=\"?do=tags\">" . $lang['tag_cloud'] . "</a> &raquo; " . $tag;

	} elseif ($nam_e) $s_navigation .= " &raquo; " . $nam_e;

	if ($titl_e) $s_navigation .= " &raquo; " . $titl_e;
	
	$tpl->load_template ( 'speedbar.tpl' );
	$tpl->set ( '{speedbar}', '<span id=\'dle-speedbar\'>' . stripslashes ( $s_navigation ) . '</span>' );
	$tpl->compile ( 'speedbar' );
	$tpl->clear ();

}
?>