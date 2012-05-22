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
 Файл: show.short.php
-----------------------------------------------------
 Назначение: вывод новостей
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( $allow_active_news ) {
	
	if( $config['allow_banner'] ) include_once ENGINE_DIR . '/modules/banners.php';
	
	$i = $cstart;
	$news_found = FALSE;
	
	if( isset( $view_template ) and $view_template == "rss" ) {
	} elseif( $category_id and $cat_info[$category_id]['short_tpl'] != '' ) $tpl->load_template( $cat_info[$category_id]['short_tpl'] . '.tpl' );
	else $tpl->load_template( 'shortstory.tpl' );
	
	if( strpos( $tpl->copy_template, "[xfvalue_" ) !== false OR strpos( $tpl->copy_template, "[xfgiven_" ) !== false ) { $xfound = true; $xfields = xfieldsload();}
	else $xfound = false;
	
	if( count( $banners ) AND $config['allow_banner'] AND !$smartphone_detected) {
		
		$news_c = 1;
		
		if( isset( $ban_short ) ) {
			for($indx = 0, $max = sizeof( $ban_short['top'] ), $banners_topz = ''; $indx < $max; $indx ++)
				if( $ban_short['top'][$indx]['zakr'] ) {
					$banners_topz .= $ban_short['top'][$indx]['text'];
					unset( $ban_short['top'][$indx] );
				}
			
			for($indx = 0, $max = sizeof( $ban_short['cen'] ), $banners_cenz = ''; $indx < $max; $indx ++)
				if( $ban_short['cen'][$indx]['zakr'] ) {
					$banners_cenz .= $ban_short['cen'][$indx]['text'];
					unset( $ban_short['cen'][$indx] );
				}
			
			for($indx = 0, $max = sizeof( $ban_short['down'] ), $banners_downz = ''; $indx < $max; $indx ++)
				if( $ban_short['down'][$indx]['zakr'] ) {
					$banners_downz .= $ban_short['down'][$indx]['text'];
					unset( $ban_short['down'][$indx] );
				}
			
			$middle = floor( $config['news_number'] / 2 );
			$middle_s = floor( ($middle - 1) / 2 );
			$middle_e = floor( $middle + (($config['news_number'] - $middle) / 2) + 1 );
		}
	}
	
	$sql_result = $db->query( $sql_select );
	
	if( ! isset( $view_template ) ) {
		
		$count_all = $db->super_query( $sql_count );
		$count_all = $count_all['count'];
	
	} else
		$count_all = 0;
	
	while ( $row = $db->get_row( $sql_result ) ) {
		
		$news_found = TRUE;
		$attachments[] = $row['id'];
		$row['date'] = strtotime( $row['date'] );
		
		if( isset( $middle ) ) {
			
			if( $news_c == $middle_s ) {
				$tpl->copy_template .= bannermass( $banners_topz, $ban_short['top'] );
			} else if( $news_c == $middle ) {
				$tpl->copy_template .= bannermass( $banners_cenz, $ban_short['cen'] );
			} else if( $news_c == $middle_e ) {
				$tpl->copy_template .= bannermass( $banners_downz, $ban_short['down'] );
			}
			$news_c ++;
		}
		
		$i ++;
		
		if( ! $row['category'] ) {
			$my_cat = "---";
			$my_cat_link = "---";
		} else {
			
			$my_cat = array ();
			$my_cat_link = array ();
			$cat_list = explode( ',', $row['category'] );
			 
			if( count( $cat_list ) == 1 OR ($view_template == "rss" AND $config['rss_format'] == 2)) {
				
				$my_cat[] = $cat_info[$cat_list[0]]['name'];
				
				$my_cat_link = get_categories( $cat_list[0] );
			
			} else {
				
				foreach ( $cat_list as $element ) {
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

		if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[catlist=(.+?)\\](.*?)\\[/catlist\\]#ies", "check_category('\\1', '\\2', '{$row['category']}')", $tpl->copy_template );
		}
		
		if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[not-catlist=(.+?)\\](.*?)\\[/not-catlist\\]#ies", "check_category('\\1', '\\2', '{$row['category']}', false)", $tpl->copy_template );
		}
		
		$row['category'] = intval( $row['category'] );
		
		$news_find = array ('{comments-num}' => $row['comm_num'], '{views}' => $row['news_read'], '{category}' => $my_cat, '{link-category}' => $my_cat_link, '{news-id}' => $row['id'], '{PAGEBREAK}' => '' );
		
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

		if ( $row['comm_num'] ) {

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
		
		if( $row['allow_rate'] ) {
			
			if( $config['short_rating'] and $user_group[$member_id['user_group']]['allow_rating'] ) $tpl->set( '{rating}', ShortRating( $row['id'], $row['rating'], $row['vote_num'], 1 ) );
			else $tpl->set( '{rating}', ShortRating( $row['id'], $row['rating'], $row['vote_num'], 0 ) );

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
		
		if( $allow_userinfo and ! $row['approve'] and ($member_id['name'] == $row['autor'] and ! $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			$tpl->set( '[edit]', "<a href=\"" . $config['http_home_url'] . "index.php?do=addnews&id=" . $row['id'] . "\" >" );
			$tpl->set( '[/edit]', "</a>" );
		} elseif( $is_logged and (($member_id['name'] == $row['autor'] and $user_group[$member_id['user_group']]['allow_edit']) or $user_group[$member_id['user_group']]['allow_all_edit']) ) {
			
			$_SESSION['referrer'] = $_SERVER['REQUEST_URI'];
			$tpl->set( '[edit]', "<a onclick=\"return dropdownmenu(this, event, MenuNewsBuild('" . $row['id'] . "', 'short'), '170px')\" href=\"#\">" );
			$tpl->set( '[/edit]', "</a>" );
			$allow_comments_ajax = true;
		} else
			$tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );
		
		if( $config['allow_alt_url'] == "yes" ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2  ) {
				
				if( $row['category'] and $config['seo_type'] == 2 ) {
					
					$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		
		}
		
		if( (strlen( $row['full_story'] ) < 13) and $config['hide_full_link'] == "yes" ) $tpl->set_block( "'\\[full-link\\](.*?)\\[/full-link\\]'si", "" );
		else {
			
			$tpl->set( '[full-link]', "<a href=\"" . $full_link . "\">" );
			
			$tpl->set( '[/full-link]', "</a>" );
		}
		
		$tpl->set( '{full-link}', $full_link );
		
		if( $row['allow_comm'] ) {
			
			$tpl->set( '[com-link]', "<a href=\"" . $full_link . "#comment\">" );
			$tpl->set( '[/com-link]', "</a>" );
		
		} else
			$tpl->set_block( "'\\[com-link\\](.*?)\\[/com-link\\]'si", "" );
		
		if( strpos( $tpl->copy_template, "[category=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[category=(.+?)\\](.*?)\\[/category\\]#ies", "check_category('\\1', '\\2', '{$category_id}')", $tpl->copy_template );
		}
		
		if( strpos( $tpl->copy_template, "[not-category=" ) !== false ) {
			$tpl->copy_template = preg_replace( "#\\[not-category=(.+?)\\](.*?)\\[/not-category\\]#ies", "check_category('\\1', '\\2', '{$category_id}', false)", $tpl->copy_template );
		}
		
		if( $is_logged ) {
			
			$fav_arr = explode( ',', $member_id['favorites'] );
			
			if( ! in_array( $row['id'], $fav_arr ) or $config['allow_cache'] == "yes" ) $tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=add&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'plus'); return false;\" title=\"" . $lang['news_addfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );
			else $tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" href=\"$PHP_SELF?do=favorites&amp;doaction=del&amp;id=" . $row['id'] . "\"><img src=\"" . $config['http_home_url'] . "templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('" . $row['id'] . "', 'minus'); return false;\" title=\"" . $lang['news_minfav'] . "\" style=\"vertical-align: middle;border: none;\" alt=\"\" /></a>" );

			$tpl->set( '[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'news')\">" );
			$tpl->set( '[/complaint]', "</a>" );

		
		} else {
			$tpl->set( '{favorites}', "" );
			$tpl->set_block( "'\\[complaint\\](.*?)\\[/complaint\\]'si", "" );
		}

		
		if( $allow_userinfo and ! $row['approve'] ) {
			
			$tpl->set( '{approve}', $lang['approve'] );
		
		} else
			$tpl->set( '{approve}', "" );
			
		// Обработка дополнительных полей
		if( $xfound ) {
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
		// Обработка дополнительных полей
		

		if( isset($view_template) AND $view_template == "rss" ) {
			
			$tpl->set( '{rsslink}', $full_link );
			$tpl->set( '{rssauthor}', $row['autor'] );
			$tpl->set( '{rssdate}', date( "r", $row['date'] - ($config['date_adjust'] * 60) ) );
			$tpl->set( '{title}', htmlspecialchars( strip_tags( stripslashes( $row['title'] ) ) ) );
			
			if( $config['rss_format'] != 1 ) {
				$row['short_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['short_story'] );				
				$row['short_story'] = trim (htmlspecialchars( strip_tags( stripslashes( str_replace( "<br />", " ", $row['short_story'] ) ) ) ) );
			
			} else {
				
				$row['short_story'] = stripslashes( $row['short_story'] );
			
			}
			
			$tpl->set( '{short-story}', $row['short_story'] );
			
			if( $config['rss_format'] == 2 ) {

				$row['full_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['full_story'] );

				$row['full_story'] = trim (htmlspecialchars( strip_tags( stripslashes( str_replace( "<br />", " ", $row['full_story'] ) ), '<a>' ), ENT_QUOTES ) );

				if( $row['full_story'] == "" ) $row['full_story'] = $row['short_story'];
				
				$tpl->set( '{full-story}', $row['full_story'] );
			
			}
		
		} else {

			$row['short_story'] = stripslashes($row['short_story']);

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
					$i_count=0;
					foreach($images as $url) {
						$i_count++;
						$tpl->copy_template = str_replace( '{image-'.$i_count.'}', $url, $tpl->copy_template );
						$tpl->copy_template = str_replace( '[image-'.$i_count.']', "", $tpl->copy_template );
						$tpl->copy_template = str_replace( '[/image-'.$i_count.']', "", $tpl->copy_template );
					}
	
				}
	
				$tpl->copy_template = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template );
				$tpl->copy_template = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $tpl->copy_template );
	
			}

			if ($smartphone_detected) {

				if (!$config['allow_smart_format']) {

						$row['short_story'] = strip_tags( $row['short_story'], '<p><br><a>' );

				} else {


					if ( !$config['allow_smart_images'] ) {
	
						$row['short_story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['short_story'] );
						$row['short_story'] = preg_replace( "#<img(.+?)>#is", "", $row['short_story'] );
	
					}
	
					if ( !$config['allow_smart_video'] ) {
	
						$row['short_story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['short_story'] );
						$row['short_story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['short_story'] );
	
					}

				}

			}
			
			$tpl->set( '{title}', stripslashes( $row['title'] ) );

			if ( preg_match( "#\\{short-story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
				$count= intval($matches[1]);
	
				$row['short_story'] = str_replace( "</p><p>", " ", $row['short_story'] );
				$row['short_story'] = strip_tags( $row['short_story'], "<br>" );
				$row['short_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['short_story'] ) ) ) ));
	
				if( $count AND dle_strlen( $row['short_story'], $config['charset'] ) > $count ) {
						
					$row['short_story'] = dle_substr( $row['short_story'], 0, $count, $config['charset'] );
						
					if( ($temp_dmax = dle_strrpos( $row['short_story'], ' ', $config['charset'] )) ) $row['short_story'] = dle_substr( $row['short_story'], 0, $temp_dmax, $config['charset'] );
					
				}
	
				$tpl->set( $matches[0], "<div id=\"news-id-" . $row['id'] . "\" style=\"display:inline;\">" . $row['short_story'] . "</div>" );
	
			} else	$tpl->set( '{short-story}', "<div id=\"news-id-" . $row['id'] . "\" style=\"display:inline;\">" . $row['short_story'] . "</div>" );
		
		}
		
		$tpl->compile( 'content' );

		if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->result['content'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $tpl->result['content']) );
		else $tpl->result['content'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#ims", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $tpl->result['content'] );

	}
	
	$tpl->clear();
	$db->free( $sql_result );
	
	if( $do == "" ) $do = $subaction;
	if( $do == "" and $year ) $do = "date";
	$ban_short = array ();
	unset( $ban_short );

	if( !$news_found and $allow_userinfo and $member_id['name'] == $user and $user_group[$member_id['user_group']]['allow_adds'] ) {
		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['mod_list_f'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();
	} elseif( !$news_found and $do == 'newposts' and $view_template != 'rss') {
		msgbox( $lang['all_info'], $lang['newpost_notfound'] );
	} elseif( !$news_found AND !$allow_userinfo AND $do != '' and $do != 'favorites' and $view_template != 'rss' ) {
		if ( $newsmodule ) @header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], $lang['news_err_27'] );
	} elseif( ! $news_found and $catalog != "" ) {
		if ( $newsmodule ) @header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], $lang['news_err_27'] );
	} elseif( ! $news_found AND $do == 'favorites' ) {

		if ( $member_id['favorites'] AND !$count_all ) $db->query( "UPDATE " . USERPREFIX . "_users SET favorites='' WHERE user_id = '{$member_id['user_id']}'" );

		if (!$count_all) msgbox( $lang['all_info'], $lang['fav_notfound'] ); else msgbox( $lang['all_info'], $lang['fav_notfound_1'] );
	} elseif( !$news_found AND $cstart ) {
		if ( $newsmodule ) @header( "HTTP/1.0 404 Not Found" );
		msgbox( $lang['all_err_1'], $lang['news_err_27'] );
	}
	
	//####################################################################################################################
	//         Навигация по новостям
	//####################################################################################################################
	if( ! isset( $view_template ) AND $count_all AND $config['news_navigation'] AND $news_found) {
		
		$tpl->load_template( 'navigation.tpl' );
		
		//----------------------------------
		// Previous link
		//----------------------------------
		

		$no_prev = false;
		$no_next = false;
		
		if( isset( $cstart ) and $cstart != "" and $cstart > 0 ) {
			$prev = $cstart / $config['news_number'];
			
			if( $config['allow_alt_url'] == "yes" ) {

				if ($prev == 1)
					$prev_page = $url_page . "/";
				else
					$prev_page = $url_page . "/page/" . $prev . "/";

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );

			} else {

				if ($prev == 1)
					$prev_page = $PHP_SELF . "?" . $user_query;
				else
					$prev_page = $PHP_SELF . "?cstart=" . $prev . "&amp;" . $user_query;

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}
		
		//----------------------------------
		// Pages
		//----------------------------------
		if( $config['news_number'] ) {

			$pages = "";
			
			if( $count_all > $config['news_number'] ) {
				
				$enpages_count = @ceil( $count_all / $config['news_number'] );
				
				$cstart = ($cstart / $config['news_number']) + 1;
				
				if( $enpages_count <= 10 ) {
					
					for($j = 1; $j <= $enpages_count; $j ++) {
						
						if( $j != $cstart ) {
							
							if( $config['allow_alt_url'] == "yes" ) {

								if ($j == 1)
									$pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
								else
									$pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";

							} else {

								if ($j == 1)
									$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
								else
									$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";

							}
						
						} else {
							
							$pages .= "<span>$j</span> ";
						}
					
					}
				
				} else {
					
					$start = 1;
					$end = 10;
					$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $cstart > 0 ) {
						
						if( $cstart > 6 ) {
							
							$start = $cstart - 4;
							$end = $start + 8;
							
							if( $end >= $enpages_count ) {
								$start = $enpages_count - 9;
								$end = $enpages_count - 1;
								$nav_prefix = "";
							} else
								$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
						
						}
					
					}
					
					if( $start >= 2 ) {
						
						if( $config['allow_alt_url'] == "yes" ) $pages .= "<a href=\"" . $url_page . "/\">1</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
						else $pages .= "<a href=\"$PHP_SELF?{$user_query}\">1</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					}
					
					for($j = $start; $j <= $end; $j ++) {
						
						if( $j != $cstart ) {

							if( $config['allow_alt_url'] == "yes" ) {

								if ($j == 1)
									$pages .= "<a href=\"" . $url_page . "/\">$j</a> ";
								else
									$pages .= "<a href=\"" . $url_page . "/page/" . $j . "/\">$j</a> ";

							} else {

								if ($j == 1)
									$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
								else
									$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";

							}
						
						} else {
							
							$pages .= "<span>$j</span> ";
						}
					
					}
					
					if( $cstart != $enpages_count ) {
						
						if( $config['allow_alt_url'] == "yes" ) $pages .= $nav_prefix . "<a href=\"" . $url_page . "/page/{$enpages_count}/\">{$enpages_count}</a>";
						else $pages .= $nav_prefix . "<a href=\"$PHP_SELF?cstart={$enpages_count}&amp;$user_query\">{$enpages_count}</a>";
					
					} else
						$pages .= "<span>{$enpages_count}</span> ";
				
				}
			
			}
			$tpl->set( '{pages}', $pages );
		}
		
		//----------------------------------
		// Next link
		//----------------------------------
		if( $config['news_number'] AND $config['news_number'] < $count_all and $i < $count_all ) {
			$next_page = $i / $config['news_number'] + 1;
			
			if( $config['allow_alt_url'] == "yes" ) {
				$next = $url_page . '/page/' . $next_page . '/';
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
			} else {
				$next = $PHP_SELF . "?cstart=" . $next_page . "&amp;" . $user_query;
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}
		
		if( !$no_prev OR !$no_next ) {
			$tpl->compile( 'navi' );

			switch ( $config['news_navigation'] ) {

				case "2" :
					
					$tpl->result['content'] = $tpl->result['navi'].$tpl->result['content'];
					break;

				case "3" :
					
					$tpl->result['content'] = $tpl->result['navi'].$tpl->result['content'].$tpl->result['navi'];
					break;

				default :
					$tpl->result['content'] .= $tpl->result['navi'];
					break;
			
			}
		}
		
		$tpl->clear();
	}
}
?>