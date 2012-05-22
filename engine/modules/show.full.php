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
 Файл: show.full.php
-----------------------------------------------------
 Назначение: Просмотр полной новости и комментариев
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

	
	$allow_list = explode( ',', $user_group[$member_id['user_group']]['allow_cats'] );
	$perm = 1;
	$i = 0;
	$news_found = false;
	$allow_full_cache = false;

	if ( $config['allow_alt_url'] == "yes" AND !$config['seo_type'] ) $cprefix = "full"; else $cprefix = "full_".$newsid;

	$row = dle_cache ( $cprefix, $sql_news );

	if( $row ) {
		$row = unserialize($row);
		$full_cache = true;
	} else {
		$row = $db->super_query( $sql_news );
		$full_cache = false;
	}
	
	$options = news_permission( $row['access'] );
	if( $options[$member_id['user_group']] AND $options[$member_id['user_group']] != 3 ) $perm = 1;
	if( $options[$member_id['user_group']] == 3 ) $perm = 0;
			
	if( $options[$member_id['user_group']] == 1 ) $user_group[$member_id['user_group']]['allow_addc'] = 0;
	if( $options[$member_id['user_group']] == 2 ) $user_group[$member_id['user_group']]['allow_addc'] = 1;
			
	if( ! $row['approve'] and $member_id['name'] != $row['autor'] and $member_id['user_group'] != '1' ) $perm = 0;
	if( ! $row['approve'] ) $allow_comments = false;

	if( ! $row['category'] ) {
		$my_cat = "---";
		$my_cat_link = "---";
	} else {
			
		$my_cat = array ();
		$my_cat_link = array ();
		$cat_list = explode( ',', $row['category'] );
			
		if( count( $cat_list ) == 1 ) {
				
			if( $allow_list[0] != "all" and ! in_array( $cat_list[0], $allow_list ) ) $perm = 0;
				
			$my_cat[] = $cat_info[$cat_list[0]]['name'];
				
			$my_cat_link = get_categories( $cat_list[0] );
			
		} else {
				
			foreach ( $cat_list as $element ) {
					
				if( $allow_list[0] != "all" and ! in_array( $element, $allow_list ) ) $perm = 0;
					
				if( $element ) {
					$my_cat[] = $cat_info[$element]['name'];
					if( $config['allow_alt_url'] == "yes" ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
					else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
				}
			}
				
			$my_cat_link = implode( ', ', $my_cat_link );
		}
			
		$my_cat = implode( ', ', $my_cat );
	}

	if ( $row['id'] AND  $perm ) {

		if( strtotime($row['date']) >= ($_TIME - 2592000) ) {
				
			$allow_full_cache = true;
			
		}

		$disable_index = $row['disable_index'];

		if ($allow_full_cache AND !$full_cache) create_cache ( $cprefix, serialize($row), $sql_news );		

		$xfields = xfieldsload();
		
		if( $row['votes'] and $view_template != "print" ) include_once ENGINE_DIR . '/modules/poll.php';
		
		$row['category'] = intval( $row['category'] );
		$category_id = $row['category'];
		
		if( isset( $view_template ) and $view_template == "print" ) $tpl->load_template( 'print.tpl' );
		elseif( $category_id and $cat_info[$category_id]['full_tpl'] != '' ) $tpl->load_template( $cat_info[$category_id]['full_tpl'] . '.tpl' );
		else $tpl->load_template( 'fullstory.tpl' );

		if( $config['allow_read_count'] == "yes" AND !$news_page AND !$cstart) {
			if( $config['cache_count'] ) $db->query( "INSERT INTO " . PREFIX . "_views (news_id) VALUES ('{$row['id']}')" );
			else $db->query( "UPDATE " . PREFIX . "_post_extras SET news_read=news_read+1 where news_id='{$row['id']}'" );
		}
		
		$news_found = TRUE;
		$row['date'] = strtotime( $row['date'] );
		
		if( (strlen( $row['full_story'] ) < 13) and (strpos( $tpl->copy_template, "{short-story}" ) === false) ) {
			$row['full_story'] = $row['short_story'];
		}
		
		if( ! $news_page ) {
			$news_page = 1;
		}

		
		$news_seiten = explode( "{PAGEBREAK}", $row['full_story'] );
		$anzahl_seiten = count( $news_seiten );
		
		if( $news_page <= 0 or $news_page > $anzahl_seiten ) {
			
			$news_page = 1;
		}
		
		if( $config['allow_alt_url'] == "yes" ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( $category_id AND $config['seo_type'] == 2 ) {

					$c_url = get_url( $category_id );				
					$full_link = $config['http_home_url'] . $c_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";

					if ($config['seo_control'] AND ( isset($_GET['seourl']) OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false ) ) {

						if ($_GET['seourl'] != $row['alt_name'] OR $_GET['seocat'] != $c_url OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false) {

							if ($view_template == "print") {

								$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							} else {

								$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							}

							header("HTTP/1.0 301 Moved Permanently");
							header("Location: {$re_url}{$c_url}/{$row['id']}-{$row['alt_name']}.html");
							die("Redirect");

						}

					}

					$print_link = $config['http_home_url'] . $c_url . "/print:page,1," . $row['id'] . "-" . $row['alt_name'] . ".html";
					$short_link = $config['http_home_url'] . $c_url . "/";
					$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
					$link_page = $config['http_home_url'] . $c_url . "/" . 'page,' . $news_page . ',';
					$news_name = $row['alt_name'];
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";

					if ($config['seo_control'] AND ( isset($_GET['seourl']) OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false ) ) {

						if ($_GET['seourl'] != $row['alt_name'] OR $_GET['seocat'] OR $_GET['news_name'] OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false ) {

							if ($view_template == "print") {

								$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							} else {

								$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
								$re_url = reset ( $re_url );

							}

							header("HTTP/1.0 301 Moved Permanently");
							header("Location: {$re_url}{$row['id']}-{$row['alt_name']}.html");
							die("Redirect");

						}

					}

					$print_link = $config['http_home_url'] . "print:page,1," . $row['id'] . "-" . $row['alt_name'] . ".html";
					$short_link = $config['http_home_url'];
					$row['alt_name'] = $row['id'] . "-" . $row['alt_name'];
					$link_page = $config['http_home_url'] . 'page,' . $news_page . ',';
					$news_name = $row['alt_name'];
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";

				if ( $config['seo_control'] ) {

					if ($_GET['newsid'] OR strpos ( $_SERVER['REQUEST_URI'], "?" ) !== false) {

						if ($view_template == "print") {

							$re_url = explode ( "engine/print.php", strtolower ( $_SERVER['PHP_SELF'] ) );
							$re_url = reset ( $re_url );

						} else {

							$re_url = explode ( "index.php", strtolower ( $_SERVER['PHP_SELF'] ) );
							$re_url = reset ( $re_url );

						}

						header("HTTP/1.0 301 Moved Permanently");
						header("Location: {$re_url}".date( 'Y/m/d/', $row['date'] ).$row['alt_name'].".html");
						die("Redirect");

					}

				}

				$print_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . "print:page,1," . $row['alt_name'] . ".html";
				$short_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] );
				$link_page = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . 'page,' . $news_page . ',';
				$news_name = $row['alt_name'];
			
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
			$print_link = $config['http_home_url'] . "engine/print.php?newsid=" . $row['id'];
			$short_link = "";
			$link_page = "";
			$news_name = "";
		
		}
		
		$i ++;
		
		//
		// обработка страниц
		//
		if( isset($view_template) AND $view_template == "print" ) {
			
			$row['full_story'] = str_replace( "{PAGEBREAK}", "", $row['full_story'] );
			$row['full_story'] = str_replace( "{pages}", "", $row['full_story'] );
			$row['full_story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", "\\2", $row['full_story'] );

		
		} else {
			
			$row['full_story'] = $news_seiten[$news_page - 1];
			
			$row['full_story'] = preg_replace( '#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $row['full_story'] ); // remove <br/> at end of string
			$news_seiten = "";
			unset( $news_seiten );
			
			if( $anzahl_seiten > 1 ) {
				
				if( $news_page < $anzahl_seiten ) {
					$pages = $news_page + 1;
					
					if( $config['allow_alt_url'] == "yes" ) {
						$nextpage = " | <a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">" . $lang['news_next'] . "</a>";
					} else {
						$nextpage = " | <a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">" . $lang['news_next'] . "</a>";
					}
				}
				
				if( $news_page > 1 ) {
					$pages = $news_page - 1;
					
					if( $config['allow_alt_url'] == "yes" ) {
						$prevpage = "<a href=\"" . $short_link . "page," . $pages . "," . $row['alt_name'] . ".html\">" . $lang['news_prev'] . "</a> | ";
					} else {
						$prevpage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=" . $pages . "\">" . $lang['news_prev'] . "</a> | ";
					}
				}
				
				$tpl->set( '{pages}', $prevpage . $lang['news_site'] . " " . $news_page . $lang['news_iz'] . $anzahl_seiten . $nextpage );
				
				if( $config['allow_alt_url'] == "yes" ) {
					
					$replacepage = "<a href=\"" . $short_link . "page," . "\\1" . "," . $row['alt_name'] . ".html\">\\2</a>";
				
				} else {
					
					$replacepage = "<a href=\"$PHP_SELF?newsid=" . $row['id'] . "&amp;news_page=\\1\">\\2</a>";
				}
				
				$row['full_story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", $replacepage, $row['full_story'] );
			
			} else {
				
				$tpl->set( '{pages}', '' );
				$row['full_story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", "", $row['full_story'] );
			}
		}
		
		$metatags['title'] = stripslashes( $row['title'] );
		$comments_num = $row['comm_num'];
		
		$news_find = array ('{comments-num}' => $comments_num, '{views}' => $row['news_read'], '{category}' => $my_cat, '{link-category}' => $my_cat_link, '{news-id}' => $row['id'] );
		
		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
		
		}

		$tpl->copy_template = preg_replace ( "#\{date=(.+?)\}#ie", "langdate('\\1', '{$row['date']}')", $tpl->copy_template );

		if ( $row['fixed'] ) {

			$tpl->set( '[fixed]', "" );
			$tpl->set( '[/fixed]', "" );
			$tpl->set_block( "'\\[not-fixed\\](.*?)\\[/not-fixed\\]'si", "" );

		} else {

			$tpl->set( '[not-fixed]', "" );
			$tpl->set( '[/not-fixed]', "" );
			$tpl->set_block( "'\\[fixed\\](.*?)\\[/fixed\\]'si", "" );
		}

		if ( $comments_num ) {

			$tpl->set( '[comments]', "" );
			$tpl->set( '[/comments]', "" );
			$tpl->set_block( "'\\[not-comments\\](.*?)\\[/not-comments\\]'si", "" );

		} else {

			$tpl->set( '[not-comments]', "" );
			$tpl->set( '[/not-comments]', "" );
			$tpl->set_block( "'\\[comments\\](.*?)\\[/comments\\]'si", "" );
		}

		if ( $row['votes'] ) {

			$tpl->set( '[poll]', "" );
			$tpl->set( '[/poll]', "" );
			$tpl->set_block( "'\\[not-poll\\](.*?)\\[/not-poll\\]'si", "" );

		} else {

			$tpl->set( '[not-poll]', "" );
			$tpl->set( '[/not-poll]', "" );
			$tpl->set_block( "'\\[poll\\](.*?)\\[/poll\\]'si", "" );
		}	
		
		if( $row['editdate'] ) $_DOCUMENT_DATE = $row['editdate'];
		else $_DOCUMENT_DATE = $row['date'];
		
		if( $row['view_edit'] and $row['editdate'] ) {
			
			if( date( Ymd, $row['editdate'] ) == date( Ymd, $_TIME ) ) {
				
				$tpl->set( '{edit-date}', $lang['time_heute'] . langdate( ", H:i", $row['editdate'] ) );
			
			} elseif( date( Ymd, $row['editdate'] ) == date( Ymd, ($_TIME - 86400) ) ) {
				
				$tpl->set( '{edit-date}', $lang['time_gestern'] . langdate( ", H:i", $row['editdate'] ) );
			
			} else {
				
				$tpl->set( '{edit-date}', langdate( $config['timestamp_active'], $row['editdate'] ) );
			
			}
			
			$tpl->set( '{editor}', $row['editor'] );
			$tpl->set( '{edit-reason}', $row['reason'] );
			
			if( $row['reason'] ) {
				
				$tpl->set( '[edit-reason]', "" );
				$tpl->set( '[/edit-reason]', "" );
			
			} else
				$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
			
			$tpl->set( '[edit-date]', "" );
			$tpl->set( '[/edit-date]', "" );
		
		} else {
			
			$tpl->set( '{edit-date}', "" );
			$tpl->set( '{editor}', "" );
			$tpl->set( '{edit-reason}', "" );
			$tpl->set_block( "'\\[edit-date\\](.*?)\\[/edit-date\\]'si", "" );
			$tpl->set_block( "'\\[edit-reason\\](.*?)\\[/edit-reason\\]'si", "" );
		}
		
		if( $config['allow_tags'] and $row['tags'] ) {
			
			$tpl->set( '[tags]', "" );
			$tpl->set( '[/tags]', "" );
			
			$tags = array ();
			
			$row['tags'] = explode( ",", $row['tags'] );
			
			foreach ( $row['tags'] as $value ) {
				
				$value = trim( $value );
				
				if( $config['allow_alt_url'] == "yes" ) $tags[] = "<a href=\"" . $config['http_home_url'] . "tags/" . urlencode( $value ) . "/\">" . $value . "</a>";
				else $tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=" . urlencode( $value ) . "\">" . $value . "</a>";
			
			}
			
			$tpl->set( '{tags}', implode( ", ", $tags ) );
		
		} else {
			
			$tpl->set_block( "'\\[tags\\](.*?)\\[/tags\\]'si", "" );
			$tpl->set( '{tags}', "" );
		
		}
		
		$tpl->set( '', $news_find );
		
		if( $cat_info[$row['category']]['icon'] ) {
			
			$tpl->set( '{category-icon}', $cat_info[$row['category']]['icon'] );
		
		} else {
			
			$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
		
		}

		if ( $row['category'] )
			$tpl->set( '{category-url}', $config['http_home_url'] . get_url( $row['category'] ) . "/" );
		else
			$tpl->set( '{category-url}', "#" );
		
		// Ссылки на версию для печати
		if ($config['allow_search_print']) {

			$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\">" );
			$tpl->set( '[/print-link]', "</a>" );

		} else {

			$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\" rel=\"nofollow\">" );
			$tpl->set( '[/print-link]', "</a>" );

		}
		// Ссылки на версию для печати
		

		if( $row['allow_rate'] ) { 

			$tpl->set( '{rating}', ShowRating( $row['id'], $row['rating'], $row['vote_num'], $user_group[$member_id['user_group']]['allow_rating'] ) );
			$tpl->set( '{vote-num}', $row['vote_num'] );
			$tpl->set( '[rating]', "" );
			$tpl->set( '[/rating]', "" );

		} else { 

			$tpl->set( '{rating}', "" );
			$tpl->set( '{vote-num}', "" );
			$tpl->set_block( "'\\[rating\\](.*?)\\[/rating\\]'si", "" );
		}
		
		if( $config['allow_alt_url'] == "yes" ) {
			
			$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
			$tpl->set( '[day-news]', "<a href=\"".$config['http_home_url'] . date( 'Y/m/d/', $row['date'])."\" >" );
		
		} else {
			
			$go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] );
			$tpl->set( '[day-news]', "<a href=\"$PHP_SELF?year=".date( 'Y', $row['date'])."&amp;month=".date( 'm', $row['date'])."&amp;day=".date( 'd', $row['date'])."\" >" );
		
		}
		
		$tpl->set( '[/day-news]', "</a>" );
		$tpl->set( '[profile]', "<a href=\"" . $go_page . "\">" );
		$tpl->set( '[/profile]', "</a>" );

		$tpl->set( '{login}', $row['autor'] );

		$tpl->set( '{author}', "<a onclick=\"ShowProfile('" . urlencode( $row['autor'] ) . "', '" . $go_page . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $go_page . "\">" . $row['autor'] . "</a>" );
		
		$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
		
		$tpl->set( '[full-link]', "<a href=\"" . $full_link . "\">" );
		$tpl->set( '[/full-link]', "</a>" );
		
		$tpl->set( '{full-link}', $full_link );
		
		if( $row['allow_comm'] ) {
			
			$tpl->set( '[com-link]', "<a id=\"dle-comm-link\" href=\"" . $full_link . "#comment\">" );
			$tpl->set( '[/com-link]', "</a>" );
		
		} else
			$tpl->set_block( "'\\[com-link\\](.*?)\\[/com-link\\]'si", "" );
		
		if( ! $row['approve'] and ($member_id['name'] == $row['autor'] and ! $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			$tpl->set( '[edit]', "<a href=\"" . $config['http_home_url'] . "index.php?do=addnews&amp;id=" . $row['id'] . "\" >" );
			$tpl->set( '[/edit]', "</a>" );
			if( $config['allow_quick_wysiwyg'] ) $allow_comments_ajax = true;
		} elseif( $is_logged and (($member_id['name'] == $row['autor'] and $user_group[$member_id['user_group']]['allow_edit']) or $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			$tpl->set( '[edit]', "<a onclick=\"return dropdownmenu(this, event, MenuNewsBuild('" . $row['id'] . "', 'full'), '170px')\" href=\"#\">" );
			$tpl->set( '[/edit]', "</a>" );
			if( $config['allow_quick_wysiwyg'] ) $allow_comments_ajax = true;
		} else
			$tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );
		
		if( $config['related_news'] AND $view_template != "print"  AND strpos( $tpl->copy_template, "{related-news}" ) !== false) {
			
			if ( $allow_full_cache ) $buffer = dle_cache( "related", $row['id'].$config['skin'], true ); else $buffer = false;
		
			if( $buffer === FALSE ) {

				if ( $row['related_ids'] ) {
					$db->query( "SELECT id, date, short_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE id IN({$row['related_ids']}) ORDER BY id DESC");
					$first_show = false;

				} else {
					$first_show = true;
					$related_ids = array();
			
					if( strlen( $row['full_story'] ) < strlen( $row['short_story'] ) ) $body = $row['short_story'];
					else $body = $row['full_story'];
					
					$body = $db->safesql( strip_tags( stripslashes( $metatags['title'] . " " . $body ) ) );
					
					$config['related_number'] = intval( $config['related_number'] );
					if( $config['related_number'] < 1 ) $config['related_number'] = 5;
	
					$allowed_cats = array();
	
					foreach ($user_group as $value) {
						if ($value['allow_cats'] != "all" AND !$value['allow_short'] ) $allowed_cats[] = $db->safesql($value['allow_cats']);
					}
	
					if (count($allowed_cats)) {
						$allowed_cats = implode(",", $allowed_cats);
						$allowed_cats = explode(",", $allowed_cats);
						$allowed_cats = array_unique($allowed_cats);
						sort($allowed_cats);
	
						if ($config['allow_multi_category']) {
							
							$allowed_cats = "category regexp '[[:<:]](" . implode ( '|', $allowed_cats ) . ")[[:>:]]' AND ";
						
						} else {
							
							$allowed_cats = "category IN ('" . implode ( "','", $allowed_cats ) . "') AND ";
						
						}
					} else $allowed_cats="";

					$db->query( "SELECT id, date, short_story, xfields, title, category, alt_name FROM " . PREFIX . "_post WHERE {$allowed_cats}MATCH (title, short_story, full_story, xfields) AGAINST ('$body') AND id != " . $row['id'] . " AND approve=1" . $where_date . " LIMIT " . $config['related_number'] );
				}

				$tpl2 = new dle_template();
				$tpl2->dir = TEMPLATE_DIR;
				$tpl2->load_template( 'relatednews.tpl' );

				if( strpos( $tpl2->copy_template, "[xfvalue_" ) !== false OR strpos( $tpl2->copy_template, "[xfgiven_" ) !== false ) { $xfound = true; }
				else $xfound = false;
								
				while ( $related = $db->get_row() ) {
					
					if ( $first_show ) $related_ids[] =	$related['id'];

					$related['date'] = strtotime( $related['date'] );

					if( ! $related['category'] ) {
						$my_cat = "---";
						$my_cat_link = "---";
					} else {
						
						$my_cat = array ();
						$my_cat_link = array ();
						$rel_cat_list = explode( ',', $related['category'] );
					 
						if( count( $rel_cat_list ) == 1 ) {
							
							$my_cat[] = $cat_info[$rel_cat_list[0]]['name'];
							
							$my_cat_link = get_categories( $rel_cat_list[0] );
						
						} else {
							
							foreach ( $rel_cat_list as $element ) {
								if( $element ) {
									$my_cat[] = $cat_info[$element]['name'];
									if( $config['allow_alt_url'] == "yes" ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
									else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
								}
							}
							
							$my_cat_link = implode( ', ', $my_cat_link );
						}
						
						$my_cat = implode( ', ', $my_cat );
					}

					$related['category'] = intval( $related['category'] );
					
					if( dle_strlen( $related['title'], $config['charset'] ) > 75 ) $related['title'] = dle_substr( $related['title'], 0, 75, $config['charset'] ) . " ...";
					
					if( $config['allow_alt_url'] == "yes" ) {
						
						if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
							
							if( $related['category'] and $config['seo_type'] == 2 ) {
								
								$full_link = $config['http_home_url'] . get_url( $related['category'] ) . "/" . $related['id'] . "-" . $related['alt_name'] . ".html";
							
							} else {
								
								$full_link = $config['http_home_url'] . $related['id'] . "-" . $related['alt_name'] . ".html";
							
							}
						
						} else {
							
							$full_link = $config['http_home_url'] . date( 'Y/m/d/', $related['date'] ) . $related['alt_name'] . ".html";
						}
					
					} else {
						
						$full_link = $config['http_home_url'] . "index.php?newsid=" . $related['id'];
					
					}

					$tpl2->set( '{title}', strip_tags( stripslashes( $related['title'] ) ) );
					$tpl2->set( '{link}', $full_link );
					$tpl2->set( '{category}', $my_cat );
					$tpl2->set( '{link-category}', $my_cat_link );

					$related['short_story'] = stripslashes( $related['short_story'] );

					if( $user_group[$member_id['user_group']]['allow_hide'] ) $related['short_story'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $related['short_story']) );
					else $related['short_story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $related['short_story'] );

					if (stripos ( $tpl2->copy_template, "{image-" ) !== false) {
			
						$images = array();
						preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $related['short_story'], $media);
						$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
			
						foreach($data as $url) {
							$info = pathinfo($url);
							if (isset($info['extension'])) {
								$info['extension'] = strtolower($info['extension']);
								if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
							}
						}
			
						if ( count($images) ) {
							$i=0;
							foreach($images as $url) {
								$i++;
								$tpl2->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl2->copy_template );
								$tpl2->copy_template = str_replace( '[image-'.$i.']', "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( '[/image-'.$i.']', "", $tpl2->copy_template );
							}
			
						}

						$tpl2->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl2->copy_template );			
						$tpl2->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl2->copy_template );
			
					}

					if ( preg_match( "#\\{text limit=['\"](.+?)['\"]\\}#i", $tpl2->copy_template, $matches ) ) {
						$count= intval($matches[1]);

						$related['short_story'] = str_replace( "</p><p>", " ", $related['short_story'] );
			
						$related['short_story'] = strip_tags( $related['short_story'], "<br>" );
						$related['short_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $related['short_story'] ) ) ) ));
						if( $count AND dle_strlen( $related['short_story'], $config['charset'] ) > $count ) {
								
							$related['short_story'] = dle_substr( $related['short_story'], 0, $count, $config['charset'] );
								
							if( ($temp_dmax = dle_strrpos( $related['short_story'], ' ', $config['charset'] )) ) $related['short_story'] = dle_substr( $related['short_story'], 0, $temp_dmax, $config['charset'] );
							
						}
			
						$tpl2->set( $matches[0], $related['short_story'] );
			
					} else $tpl2->set( '{text}', $related['short_story'] );

					if( $xfound ) {
						$xfieldsdata = xfieldsdataload( $related['xfields'] );
						
						foreach ( $xfields as $value ) {
							$preg_safe_name = preg_quote( $value[0], "'" );

								if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
									$temp_array = explode( ",", $xfieldsdata[$value[0]] );
									$value3 = array();
				
									foreach ($temp_array as $value2) {
				
										$value2 = trim($value2);
										$value2 = str_replace("&#039;", "'", $value2);
				
										if( $config['allow_alt_url'] == "yes" ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" . urlencode( $value2 ) . "/\">" . $value2 . "</a>";
										else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
									}
				
									$xfieldsdata[$value[0]] = implode(", ", $value3);
				
									unset($temp_array);
									unset($value2);
									unset($value3);
				
								}
					
							if( empty( $xfieldsdata[$value[0]] ) ) {
								$tpl2->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[xfnotgiven_{$preg_safe_name}]", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[/xfnotgiven_{$preg_safe_name}]", "", $tpl2->copy_template );
							} else {
								$tpl2->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[xfgiven_{$preg_safe_name}]", "", $tpl2->copy_template );
								$tpl2->copy_template = str_replace( "[/xfgiven_{$preg_safe_name}]", "", $tpl2->copy_template );
							}
							
							$tpl2->copy_template = str_replace( "[xfvalue_{$preg_safe_name}]", stripslashes( $xfieldsdata[$value[0]] ), $tpl2->copy_template );
						}
					}

					$tpl2->compile( 'content' );
				
				}

				$buffer = $tpl2->result['content'];
				unset($tpl2);
				$db->free();

				if ( $first_show ) {
					if ( count($related_ids) ) {
						$related_ids = implode(",",$related_ids);
						$db->query( "UPDATE " . PREFIX . "_post_extras SET related_ids='{$related_ids}' WHERE news_id='{$row['id']}'" );
					}
				}

				if ( $allow_full_cache ) create_cache( "related", $buffer, $row['id'].$config['skin'], true );
			}
			
			if ( $buffer ) {

				$tpl->set( '[related-news]', "" );
				$tpl->set( '[/related-news]', "" );

			} else $tpl->set_block( "'\\[related-news\\](.*?)\\[/related-news\\]'si", "" );

			$tpl->set( '{related-news}', $buffer );
			unset($buffer);
		
		}
		
		if( $is_logged ) {
			
			$fav_arr = explode( ',', $member_id['favorites'] );
			
			if( ! in_array( $row['id'], $fav_arr ) ) $tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=add&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'plus'); return false;\" title=\"" . $lang['news_addfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );
			else $tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=del&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'minus'); return false;\" title=\"" . $lang['news_minfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );

			$tpl->set( '[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'news')\">" );
			$tpl->set( '[/complaint]', "</a>" );
		
		} else {
			$tpl->set( '{favorites}', "" );
			$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
		}
		
		if( $row['votes'] ) $tpl->set( '{poll}', $tpl->result['poll'] );
		else $tpl->set( '{poll}', '' );
		
		if( $config['allow_banner'] ) include_once ENGINE_DIR . '/modules/banners.php';
		
		if( $config['allow_banner'] AND count( $banners ) ) {
			
			foreach ( $banners as $name => $value ) {
				$tpl->copy_template = str_replace( "{banner_" . $name . "}", $value, $tpl->copy_template );
			}
		}
		
		$tpl->set_block( "'{banner_(.*?)}'si", "" );
		
		if( strpos( $tpl->copy_template, "[category=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#ies", "check_category('\\1', '\\2', '{$row['category']}')", $tpl->copy_template );
		}
		
		if( strpos( $tpl->copy_template, "[not-category=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[not-category=(.+?)\\](.*?)\\[/not-category\\]#ies", "check_category('\\1', '\\2', '{$row['category']}', false)", $tpl->copy_template );
		}

		if (stripos ( $tpl->copy_template, "{custom" ) !== false) {
			$tpl->copy_template = preg_replace ( "#\\{custom category=['\"](.+?)['\"] template=['\"](.+?)['\"] aviable=['\"](.+?)['\"] from=['\"](.+?)['\"] limit=['\"](.+?)['\"] cache=['\"](.+?)['\"]\\}#ies", "custom_print('\\1', '\\2', '\\3', '\\4', '\\5', '\\6', '{$dle_module}')", $tpl->copy_template );
		}
		
		$tpl->set( '{title}', $metatags['title'] );

		$row['short_story'] = stripslashes($row['short_story']);
		$row['full_story'] = stripslashes($row['full_story']);

		if (stripos ( $tpl->copy_template, "{image-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
	
			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					$info['extension'] = strtolower($info['extension']);
					if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) array_push($images, $url);
				}
			}
	
			if ( count($images) ) {
				$i=0;
				foreach($images as $url) {
					$i++;
					$tpl->copy_template = str_replace( '{image-'.$i.'}', $url, $tpl->copy_template );
					$tpl->copy_template = str_replace( '[image-'.$i.']', "", $tpl->copy_template );
					$tpl->copy_template = str_replace( '[/image-'.$i.']', "", $tpl->copy_template );
				}
	
			}
	
			$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
			$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
	
		}

		if ($smartphone_detected) {

			if (!$config['allow_smart_format']) {

					$row['short_story'] = strip_tags( $row['short_story'], '<p><br><a>' );
					$row['full_story'] = strip_tags( $row['full_story'], '<p><br><a>' );

			} else {

				if ( !$config['allow_smart_images'] ) {
	
					$row['short_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['short_story'] );
					$row['short_story'] = preg_replace( "#<img(.+?)>#is", "", $row['short_story'] );
					$row['full_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['full_story'] );
					$row['full_story'] = preg_replace( "#<img(.+?)>#is", "", $row['full_story'] );
	
				}
	
				if ( !$config['allow_smart_video'] ) {
	
					$row['short_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['short_story'] );
					$row['short_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['short_story'] );
					$row['full_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['full_story'] );
					$row['full_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['full_story'] );
	
				}

			}

		}
		$tpl->set( '{comments}', "<!--dlecomments-->" );
		$tpl->set( '{addcomments}', "<!--dleaddcomments-->" );
		$tpl->set( '{navigation}', "<!--dlenavigationcomments-->" );

		$tpl->set( '{short-story}', $row['short_story'] );

		if ( preg_match( "#\\{full-story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);

			$row['full_story'] = str_replace( "</p><p>", " ", $row['full_story'] );
			$row['full_story'] = strip_tags( $row['full_story'], "<br>" );
			$row['full_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['full_story'] ) ) ) ));

			if( $count AND dle_strlen( $row['full_story'], $config['charset'] ) > $count ) {
					
				$row['full_story'] = dle_substr( $row['full_story'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['full_story'], ' ', $config['charset'] )) ) $row['full_story'] = dle_substr( $row['full_story'], 0, $temp_dmax, $config['charset'] );
				
			}

			$tpl->set( $matches[0], "<div id=\"news-id-" . $row['id'] . "\" style=\"display:inline;\">" .$row['full_story'] . "</div>" );

		} else $tpl->set( '{full-story}', "<div id=\"news-id-" . $row['id'] . "\" style=\"display:inline;\">" . $row['full_story'] . "</div>");
		
		if( $row['keywords'] == '' and $row['descr'] == '' ) create_keywords( $row['short_story'] . $row['full_story'] );
		else {
			$metatags['keywords'] = $row['keywords'];
			$metatags['description'] = $row['descr'];
		}

		if ($row['metatitle']) $metatags['header_title'] = $row['metatitle'];

		if( strpos( $tpl->copy_template, "[xfvalue_" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) {
			
			$xfieldsdata = xfieldsdataload( $row['xfields'] );
			
			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );

				if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
					$temp_array = explode( ",", $xfieldsdata[$value[0]] );
					$value3 = array();

					foreach ($temp_array as $value2) {

						$value2 = trim($value2);
						$value2 = str_replace("&#039;", "'", $value2);

						if( $config['allow_alt_url'] == "yes" ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" . urlencode( $value2 ) . "/\">" . $value2 . "</a>";
						else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
					}

					$xfieldsdata[$value[0]] = implode(", ", $value3);

					unset($temp_array);
					unset($value2);
					unset($value3);

				}
				
				if( empty( $xfieldsdata[$value[0]] ) ) {
					$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[xfnotgiven_{$preg_safe_name}]", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[/xfnotgiven_{$preg_safe_name}]", "", $tpl->copy_template );
				} else {
					$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[xfgiven_{$preg_safe_name}]", "", $tpl->copy_template );
					$tpl->copy_template = str_replace( "[/xfgiven_{$preg_safe_name}]", "", $tpl->copy_template );
				}
				
				$tpl->copy_template = str_replace( "[xfvalue_{$preg_safe_name}]", stripslashes( $xfieldsdata[$value[0]] ), $tpl->copy_template );
			}
		}
		
		$tpl->compile( 'content' );

		if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->result['content'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $tpl->result['content']) );
		else $tpl->result['content'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $tpl->result['content'] );

		
		$news_id = $row['id'];
		$allow_comments = $row['allow_comm'];

		$allow_add = true;

		if ( $config['max_comments_days'] ) {

			if ($row['date'] < ($_TIME - ($config['max_comments_days'] * 3600 * 24)) )	$allow_add = false;

		}
		
		if( isset( $view_template ) ) $allow_comments = false;
	
	}

	$tpl->clear();
	unset( $row );
	
	if( $config['files_allow'] == "yes" ) if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
		$tpl->result['content'] = show_attach( $tpl->result['content'], $news_id );
	}
	
	if( !$news_found AND !$perm ) msgbox( $lang['all_err_1'], "<b>{$user_group[$member_id['user_group']]['group_name']}</b> " . $lang['news_err_28'] );
	elseif( !$news_found ) {
		@header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], $lang['news_err_12'] );
	}

//####################################################################################################################
//		 Просмотр комментариев
//####################################################################################################################
if( $allow_comments AND $news_found) {
	
	if( $comments_num > 0 ) {

		include_once ENGINE_DIR . '/classes/comments.class.php';
		$comments = new DLE_Comments( $db, $comments_num, intval($config['comm_nummers']) );

		if( $config['comm_msort'] == "" ) $config['comm_msort'] = "ASC";

		if( $config['allow_cmod'] ) $where_approve = " AND " . PREFIX . "_comments.approve=1";
		else $where_approve = "";

		$comments->query = "SELECT " . PREFIX . "_comments.id, post_id, " . PREFIX . "_comments.user_id, date, autor as gast_name, " . PREFIX . "_comments.email as gast_email, text, ip, is_register, name, " . USERPREFIX . "_users.email, news_num, comm_num, user_group, lastdate, reg_date, signature, foto, fullname, land, icq, xfields FROM " . PREFIX . "_comments LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id WHERE " . PREFIX . "_comments.post_id = '$news_id'" . $where_approve . " ORDER BY date " . $config['comm_msort'];

		if ( $allow_full_cache AND $config['allow_comments_cache'] ) $allow_full_cache = $news_id; else $allow_full_cache = false;

		$comments->build_comments('comments.tpl', 'news', $allow_full_cache );

		unset ($tpl->result['comments']);

		if( isset($_GET['news_page']) AND $_GET['news_page'] ) $user_query = "newsid=" . $newsid . "&amp;news_page=" . intval( $_GET['news_page'] ); else $user_query = "newsid=" . $newsid;

		$comments->build_navigation('navigation.tpl', $link_page . "{page}," . $news_name . ".html#comment", $user_query);		

		unset ($comments);
		unset ($tpl->result['commentsnavigation']);
	
	}

	if ($is_logged AND $config['comments_restricted'] AND (($_TIME - $member_id['reg_date']) < ($config['comments_restricted'] * 86400)) ) {

		$lang['news_info_6'] = str_replace( '{days}', intval($config['comments_restricted']), $lang['news_info_8'] );
		$allow_add = false;

	}

	if (!isset($member_id['restricted'])) $member_id['restricted'] = false;
	
	if( $member_id['restricted'] AND $member_id['restricted_days'] AND $member_id['restricted_date'] < $_TIME ) {
		
		$member_id['restricted'] = 0;
		$db->query( "UPDATE LOW_PRIORITY " . USERPREFIX . "_users SET restricted='0', restricted_days='0', restricted_date='' WHERE user_id='{$member_id['user_id']}'" );
	
	}
	
	if( $user_group[$member_id['user_group']]['allow_addc'] AND $config['allow_comments'] == "yes" AND $allow_add AND ($member_id['restricted'] != 2 AND $member_id['restricted'] != 3) ) {

		if( !$comments_num ) {		
			if( strpos ( $tpl->result['content'], "<!--dlecomments-->" ) !== false ) {
	
				$tpl->result['content'] = str_replace ( "<!--dlecomments-->", "\n<div id=\"dle-ajax-comments\"></div>\n", $tpl->result['content'] );
	
			} else $tpl->result['content'] .= "\n<div id=\"dle-ajax-comments\"></div>\n";
		}
		
		$tpl->load_template( 'addcomments.tpl' );

		if ($config['allow_subscribe'] AND $user_group[$member_id['user_group']]['allow_subscribe']) $allow_subscribe = true; else $allow_subscribe = false;
		
		if( $config['allow_comments_wysiwyg'] == "yes" ) {
			include_once ENGINE_DIR . '/editor/comments.php';
			$bb_code = "";
			$allow_comments_ajax = true;
		} else
			include_once ENGINE_DIR . '/modules/bbcode.php';

		if ( $is_logged AND $user_group[$member_id['user_group']]['disable_comments_captcha'] AND $member_id['comm_num'] >= $user_group[$member_id['user_group']]['disable_comments_captcha'] ) {
		
			$user_group[$member_id['user_group']]['comments_question'] = false;
			$user_group[$member_id['user_group']]['captcha'] = false;
		
		}

		if( $user_group[$member_id['user_group']]['comments_question'] ) {

			$tpl->set( '[question]', "" );
			$tpl->set( '[/question]', "" );

			$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
			$tpl->set( '{question}', "<span id=\"dle-question\">".htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES )."</span>" );

			$_SESSION['question'] = $question['id'];

		} else {

			$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
			$tpl->set( '{question}', "" );

		}
		
		if( $user_group[$member_id['user_group']]['captcha'] ) {

			if ( $config['allow_recaptcha'] ) {

				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );

				$tpl->set( '{recaptcha}', '<div id="dle_recaptcha"></div>' );

				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{reg_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$path = parse_url( $config['http_home_url'] );
				$tpl->set( '{sec_code}', "<span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot.php\" alt=\"${lang['sec_image']}\" /><br /><a onclick=\"reload(); return false;\" href=\"#\">{$lang['reload_code']}</a></span>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

		} else {
			$tpl->set( '{sec_code}', "" );
			$tpl->set( '{recaptcha}', "" );
			$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
			$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
		}

		if( $config['allow_comments_wysiwyg'] == "yes" ) {

			$tpl->set( '{editor}', $wysiwyg );

		} else {
			$tpl->set( '{editor}', $bb_code );

		}
		
		$tpl->set( '{text}', '' );
		$tpl->set( '{title}', $lang['news_addcom'] );
		
		if( ! $is_logged ) {
			$tpl->set( '[not-logged]', '' );
			$tpl->set( '[/not-logged]', '' );
		} else
			$tpl->set_block( "'\\[not-logged\\](.*?)\\[/not-logged\\]'si", "" );
		
		if( $is_logged ) $hidden = "<input type=\"hidden\" name=\"name\" id=\"name\" value=\"{$member_id['name']}\" /><input type=\"hidden\" name=\"mail\" id=\"mail\" value=\"\" />";
		else $hidden = "";
		
		$tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" action=\"{$_SESSION['referrer']}\">" . $tpl->copy_template . "
		<input type=\"hidden\" name=\"subaction\" value=\"addcomment\" />{$hidden}
		<input type=\"hidden\" name=\"post_id\" id=\"post_id\" value=\"$news_id\" /></form>";

		if (!isset($path['path'])) $path['path'] = "/";

		$tpl->copy_template .= <<<HTML
<script language="javascript" type="text/javascript">
<!--
$(function(){

	$('#dle-comments-form').submit(function() {
	  doAddComments();
	  return false;
	});

});

function reload () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha').innerHTML = '<img src="{$path['path']}engine/modules/antibot.php?rndval=' + rndval + '" width="120" height="50" alt="" /><br /><a onclick="reload(); return false;" href="#">{$lang['reload_code']}</a>';

};
//-->
</script>
HTML;

		if ( $user_group[$member_id['user_group']]['captcha'] AND $config['allow_recaptcha'] ) {

		$tpl->copy_template .= <<<HTML
<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
<script language="javascript" type="text/javascript">
<!--
$(function(){
	Recaptcha.create("{$config['recaptcha_public_key']}",
     "dle_recaptcha",
     {
       theme: "{$config['recaptcha_theme']}",
       lang:  "{$lang['wysiwyg_language']}"
     }
   );
});
//-->
</script>
HTML;
		
		}
		
		$tpl->compile( 'addcomments' );
		$tpl->clear();

		if ( strpos ( $tpl->result['content'], "<!--dleaddcomments-->" ) !== false ) {

			$tpl->result['content'] = str_replace ( "<!--dleaddcomments-->", $tpl->result['addcomments'], $tpl->result['content'] );

		} else {

			$tpl->result['content'] .= $tpl->result['addcomments'];

		}

		unset ($tpl->result['addcomments']);

	} elseif( $member_id['restricted'] ) {
		
		$tpl->load_template( 'info.tpl' );
		
		if( $member_id['restricted_days'] ) {
			
			$tpl->set( '{error}', $lang['news_info_2'] );
			$tpl->set( '{date}', langdate( "j F Y H:i", $member_id['restricted_date'] ) );
		
		} else
			$tpl->set( '{error}', $lang['news_info_3'] );
		
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();

	} elseif( !$allow_add ) {

		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['news_info_6'] );
		$tpl->set( '{days}', $config['max_comments_days'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();
	
	} elseif( $config['allow_comments'] != "no") {
		
		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['news_info_1'] );
		$tpl->set( '{group}', $user_group[$member_id['user_group']]['group_name'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();
	
	}
}
?>