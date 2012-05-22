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
 Файл: editnews.php
-----------------------------------------------------
 Назначение: редактирование новостей
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_editnews'] ) {
	msg( "error", $lang['addnews_denied'], $lang['edit_denied'] );
}

if( isset( $_REQUEST['author'] ) ) $author = $db->safesql( trim( htmlspecialchars( $_REQUEST['author'] ) ) ); else $author = "";
if( isset( $_REQUEST['ifdelete'] ) ) $ifdelete = $_REQUEST['ifdelete']; else $ifdelete = "";
if( isset( $_REQUEST['news_fixed'] ) ) $news_fixed = $_REQUEST['news_fixed']; else $news_fixed = "";
if( isset( $_REQUEST['search_cat'] ) ) $search_cat = intval( $_REQUEST['search_cat'] ); else $search_cat = "";

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( Array (), Array (), 1, 1 );

if( $action == "list" ) {
	
	$_SESSION['admin_referrer'] = $_SERVER['REQUEST_URI'];
	$js_array[] = "engine/skins/calendar.js";

	echoheader( "editnews", $lang['edit_head'] );
	
	$search_field = $db->safesql( trim( htmlspecialchars( stripslashes( urldecode( $_REQUEST['search_field'] ) ), ENT_QUOTES ) ) );
	$search_author = $db->safesql( trim( htmlspecialchars( stripslashes( urldecode( $_REQUEST['search_author'] ) ), ENT_QUOTES ) ) );
	$fromnewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['fromnewsdate'] ), ENT_QUOTES ) ) );
	$tonewsdate = $db->safesql( trim( htmlspecialchars( stripslashes( $_REQUEST['tonewsdate'] ), ENT_QUOTES ) ) );
	
	$start_from = intval( $_REQUEST['start_from'] );
	$news_per_page = intval( $_REQUEST['news_per_page'] );
	$gopage = intval( $_REQUEST['gopage'] );
	
	$_REQUEST['news_status'] = intval( $_REQUEST['news_status'] );
	$news_status_sel = array ('0' => '', '1' => '', '2' => '' );
	$news_status_sel[$_REQUEST['news_status']] = 'selected="selected"';
	
	if( ! $news_per_page or $news_per_page < 1 ) {
		$news_per_page = 50;
	}
	if( $gopage ) $start_from = ($gopage - 1) * $news_per_page;
	
	if( $start_from < 0 ) $start_from = 0;
	
	$where = array ();
	
	if( ! $user_group[$member_id['user_group']]['allow_all_edit'] and $member_id['user_group'] != 1 ) {
		
		$where[] = "autor = '{$member_id['name']}'";
	
	}
	
	if( $search_field != "" ) {
		
		$where[] = "(short_story like '%$search_field%' OR title like '%$search_field%' OR full_story like '%$search_field%' OR xfields like '%$search_field%')";
	
	}
	
	if( $search_author != "" ) {
		
		$where[] = "autor like '$search_author%'";
	
	}
	
	if( $search_cat != "" ) {
		
		if ($search_cat == -1) $where[] = "category = '' OR category = '0'";
		else $where[] = "category regexp '[[:<:]]($search_cat)[[:>:]]'";
	
	}
	
	if( $fromnewsdate != "" ) {
		
		$where[] = "date >= '$fromnewsdate'";
	
	}
	
	if( $tonewsdate != "" ) {
		
		$where[] = "date <= '$tonewsdate'";
	
	}
	
	if( $_REQUEST['news_status'] == 1 ) $where[] = "approve = '1'";
	elseif( $_REQUEST['news_status'] == 2 ) $where[] = "approve = '0'";
	
	if( count( $where ) ) {
		
		$where = implode( " AND ", $where );
		$where = " WHERE " . $where;
	
	} else {
		$where = "";
	}
	
	$order_by = array ();
	
	if( $_REQUEST['search_order_f'] == "asc" or $_REQUEST['search_order_f'] == "desc" ) $search_order_f = $_REQUEST['search_order_f'];
	else $search_order_f = "";
	if( $_REQUEST['search_order_m'] == "asc" or $_REQUEST['search_order_m'] == "desc" ) $search_order_m = $_REQUEST['search_order_m'];
	else $search_order_m = "";
	if( $_REQUEST['search_order_d'] == "asc" or $_REQUEST['search_order_d'] == "desc" ) $search_order_d = $_REQUEST['search_order_d'];
	else $search_order_d = "";
	if( $_REQUEST['search_order_t'] == "asc" or $_REQUEST['search_order_t'] == "desc" ) $search_order_t = $_REQUEST['search_order_t'];
	else $search_order_t = "";
	
	if( ! empty( $search_order_f ) ) {
		$order_by[] = "fixed $search_order_f";
	}
	if( ! empty( $search_order_m ) ) {
		$order_by[] = "approve $search_order_m";
	}
	if( ! empty( $search_order_d ) ) {
		$order_by[] = "date $search_order_d";
	}
	if( ! empty( $search_order_t ) ) {
		$order_by[] = "title $search_order_t";
	}
	
	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) $order_by = "fixed desc, approve asc, date desc";
	
	$search_order_fixed = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_f'] ) ) {
		$search_order_fixed[$search_order_f] = 'selected';
	} else {
		$search_order_fixed['desc'] = 'selected';
	}
	$search_order_mod = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_m'] ) ) {
		$search_order_mod[$search_order_m] = 'selected';
	} else {
		$search_order_mod['asc'] = 'selected';
	}
	$search_order_date = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( isset( $_REQUEST['search_order_d'] ) ) {
		$search_order_date[$search_order_d] = 'selected';
	} else {
		$search_order_date['desc'] = 'selected';
	}
	$search_order_title = array ('----' => '', 'asc' => '', 'desc' => '' );
	if( ! empty( $search_order_t ) ) {
		$search_order_title[$search_order_t] = 'selected';
	} else {
		$search_order_title['----'] = 'selected';
	}
	
	$db->query( "SELECT p.id, p.date, p.title, p.category, p.autor, p.alt_name, p.comm_num, p.approve, p.fixed, e.news_read, e.votes FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) " . $where . " ORDER BY " . $order_by . " LIMIT $start_from,$news_per_page" );
	
	// Prelist Entries

	if( $start_from == "0" ) {
		$start_from = "";
	}
	$i = $start_from;
	$entries_showed = 0;
	
	$entries = "";
	
	while ( $row = $db->get_array() ) {
		
		$i ++;
		
		$itemdate = date( "d.m.Y", strtotime( $row['date'] ) );
		
		if( dle_strlen( $row['title'], $config['charset'] ) > 65 ) $title = dle_substr( $row['title'], 0, 65, $config['charset'] ) . " ...";
		else $title = $row['title'];
		
		$title = htmlspecialchars( stripslashes( $title ), ENT_QUOTES );
		$title = str_replace("&amp;","&", $title );
		
		$entries .= "<tr>
        <td class=\"list\" style=\"padding:4px;\">
        $itemdate - ";
		
		if( $row['fixed'] ) $entries .= "<font color=\"red\">$lang[edit_fix] </font> ";

		if( $row['votes'] ) $entries .= "<img src=\"engine/skins/images/poll.gif\" style=\"vertical-align: middle;border: none;\" />&nbsp;&nbsp;";

		if( $config['allow_alt_url'] == "yes" ) {
			
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				
				if( intval( $row['category'] ) and $config['seo_type'] == 2 ) {
					
					$full_link = $config['http_home_url'] . get_url( intval( $row['category'] ) ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				} else {
					
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				
				}
			
			} else {
				
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $row['date'] ) ) . $row['alt_name'] . ".html";
			}
		
		} else {
			
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		
		}
		
		if( $row['comm_num'] > 0 ) {
			
			$comm_link = "<a class=\"list\" onClick=\"return dropdownmenu(this, event, MenuBuild('" . $row['id'] . "', '{$full_link}'), '150px')\"href=\"{$full_link}\" target=\"_blank\">{$row['comm_num']}</a>";
		
		} else {
			$comm_link = $row['comm_num'];
		}
		
		$entries .= "<a title='$lang[edit_act]' class=\"list\" href=\"$PHP_SELF?mod=editnews&action=editnews&id=$row[0]\">$title</a>
        <td align=center><a title=\"{$lang['comm_view']}\" class=\"list\" href=\"{$full_link}\" target=\"_blank\">{$row['news_read']}</a></td><td align=center>" . $comm_link;
		
		$entries .= "</td><td style=\"text-align: center\">";
		
		if( $row['approve'] ) $erlaub = "$lang[edit_yes]";
		else $erlaub = "<font color=\"red\">$lang[edit_no]</font>";
		$entries .= $erlaub;
		
		$entries .= "<td align=\"center\">";
		
		if( ! $row['category'] ) $my_cat = "---";
		else {
			
			$my_cat = array ();
			$cat_list = explode( ',', $row['category'] );
			
			foreach ( $cat_list as $element ) {
				if( $element ) $my_cat[] = $cat[$element];
			}
			$my_cat = implode( ',<br />', $my_cat );
		}
		
		$entries .= "$my_cat<td class=\"list\"><a class=list href=\"?mod=editusers&action=list&search=yes&search_name=" . $row['autor'] . "\">" . $row['autor'] . "</a>

               <td align=center><input name=\"selected_news[]\" value=\"{$row['id']}\" type='checkbox'>

             </tr>
			<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=7></td></tr>
            ";
		$entries_showed ++;
		
		if( $i >= $news_per_page + $start_from ) {
			break;
		}
	}
	

	// End prelisting
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" . $where );
	
	$all_count_news = $result_count['count'];
	
	///////////////////////////////////////////
	// Options Bar
	$category_list = CategoryNewsSelection( $search_cat, 0, false );
	
	echo <<<HTML
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
<script language="javascript">
    function search_submit(prm){
      document.optionsbar.start_from.value=prm;
      document.optionsbar.submit();
      return false;
    }
    function gopage_submit(prm){
      document.optionsbar.start_from.value= (prm - 1) * {$news_per_page};
      document.optionsbar.submit();
      return false;
    }
    </script>
<form action="?mod=editnews&amp;action=list" method="GET" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="editnews">
<input type="hidden" name="action" value="list">
<div style="padding-top:5px;padding-bottom:2px;display:none" name="advancedsearch" id="advancedsearch">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['edit_stat']} <b>{$entries_showed}</b> {$lang['edit_stat_1']} <b>{$all_count_news}</b></div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
     <tr>
		<td style="padding:5px;">{$lang['edit_search_news']}</td>
		<td style="padding-left:5px;"><input class="edit bk" name="search_field" value="{$search_field}" type="text" size="35"></td>
		<td style="padding-left:5px;">{$lang['search_by_author']}</td>
		<td style="padding-left:22px;"><input class="edit bk" name="search_author" value="{$search_author}" type="text" size="36"></td>

    </tr>
     <tr>
		<td style="padding:5px;">{$lang['edit_cat']}</td>
		<td style="padding-left:5px;"><select name="search_cat" ><option selected value="">$lang[edit_all]</option><option value="-1">$lang[cat_in_none]</option>{$category_list}</select></td>
		<td style="padding-left:5px;">{$lang['search_by_date']}</td>
		<td style="padding-left:5px;">{$lang['edit_fdate']} <input type="text" name="fromnewsdate" id="fromnewsdate" size="11" maxlength="16" class="edit bk" value="{$fromnewsdate}">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_dnews" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>
<script type="text/javascript">
    Calendar.setup({
      inputField     :    "fromnewsdate",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "f_trigger_dnews",  // trigger for the calendar (button ID)
      align          :    "Br",           // alignment 
		  timeFormat     :    "24",
		  showsTime      :    false,
      singleClick    :    true
    });
</script> {$lang['edit_tdate']} <input type="text" name="tonewsdate" id="tonewsdate" size="11" maxlength="16" class="edit bk" value="{$tonewsdate}">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_tnews" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>
<script type="text/javascript">
    Calendar.setup({
      inputField     :    "tonewsdate",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "f_trigger_tnews",  // trigger for the calendar (button ID)
      align          :    "Br",           // alignment 
		  timeFormat     :    "24",
		  showsTime      :    false,
      singleClick    :    true
    });
</script></td>

    </tr>
     <tr>
		<td style="padding:5px;">{$lang['search_by_status']}</td>
		<td style="padding-left:5px;"><select name="news_status" id="news_status">
								<option {$news_status_sel['0']} value="0">{$lang['news_status_all']}</option>
								<option {$news_status_sel['1']} value="1">{$lang['news_status_approve']}</option>
                                <option {$news_status_sel['2']} value="2">{$lang['news_status_mod']}</option>
							</select></td>
		<td style="padding-left:5px;">{$lang['edit_page']}</td>
		<td style="padding-left:22px;"><input class="edit bk" style="text-align: center" name="news_per_page" value="{$news_per_page}" type="text" size="36"></td>

    </tr>
    <tr>
        <td colspan="4"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td colspan="4">{$lang['news_order']}</td>
    </tr>
    <tr>
        <td style="padding:5px;">{$lang['news_order_fixed']}</td>
        <td style="padding:5px;">{$lang['edit_approve']}</td>
        <td style="padding:5px;">{$lang['search_by_date']}</td>
        <td style="padding:5px;">{$lang['edit_et']}</td>
    </tr>
    <tr>
        <td style="padding-left:2px;"><select name="search_order_f" id="search_order_f">
           <option {$search_order_fixed['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_fixed['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_fixed['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
        </td>
        <td style="padding-left:2px;"><select name="search_order_m" id="search_order_m">
           <option {$search_order_mod['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_mod['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_mod['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
        </td>
        <td style="padding-left:2px;"><select name="search_order_d" id="search_order_d">
           <option {$search_order_date['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_date['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_date['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
        </td>
        <td style="padding-left:2px;" colspan="2"><select name="search_order_t" id="search_order_t">
           <option {$search_order_title['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_title['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_title['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="4"><div class="hr_line"></div></td>
    </tr>
    <tr>
		<td style="padding:5px;">&nbsp;</td>
		<td colspan="3">
<input type="hidden" name="start_from" id="start_from" value="{$start_from}">
<input onClick="javascript:search_submit(0); return(false);" class="btn btn-primary" type="submit" value="{$lang['edit_act_1']}"></td>

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
</form>
HTML;
	// End Options Bar
	

	echo <<<JSCRIPT
<script language='JavaScript' type="text/javascript">
<!--
function ckeck_uncheck_all() {
    var frm = document.editnews;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; }
            else{ elmnt.checked=true; }
        }
    }
    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
}
-->
</script>
JSCRIPT;
	
	if( $entries_showed == 0 ) {
		
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['news_list']}</div></td>
        <td bgcolor="#EFEFEF" height="29" style="padding:5px;" align="right"><a href="javascript:ShowOrHide('advancedsearch');">{$lang['news_advanced_search']}</a></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td align="center" style="height:50px;">{$lang['edit_nonews']}</td>
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
	
	} else {
		
		echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function cdelete(id){

	    DLEconfirm( '{$lang['db_confirmclear']}', '{$lang['p_confirm']}', function () {
			document.location='?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id=' + id + '';
		} );
}
function MenuBuild( m_id, m_link ){

var menu=new Array()

menu[0]='<a href="' + m_link + '" target="_blank">{$lang['comm_view']}</a>';
menu[1]='<a href="?mod=comments&action=edit&id=' + m_id + '">{$lang['vote_edit']}</a>';
menu[2]='<a onClick="javascript:cdelete(' + m_id + '); return(false)" href="?mod=comments&user_hash={$dle_login_hash}&action=dodelete&id=' + m_id + '" >{$lang['comm_del']}</a>';

return menu;
}
//-->
</script>
<form action="" method="post" name="editnews">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['news_list']}</div></td>
        <td bgcolor="#EFEFEF" height="29" style="padding:5px;" align="right"><a href="javascript:ShowOrHide('advancedsearch');">{$lang['news_advanced_search']}</a></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td>
	<table width="100%" id="newslist">
	<tr class="thead">
    <th>&nbsp;&nbsp;{$lang['edit_title']}</th>
	<th width="80">&nbsp;{$lang['st_views']}&nbsp;</th>
	<th width="80">&nbsp;{$lang['edit_com']}&nbsp;</th>
    <th width="80" style="text-align: center;">{$lang['edit_approve']}</th>
    <th width="120" style="text-align: center;">{$lang['edit_cl']}</th>
    <th width="70" >{$lang['edit_autor']}</th>
    <th width="10" style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></th>
	</tr>
	<tr class="tfoot"><th colspan="7"><div class="hr_line"></div></td></th>
	{$entries}
	<tr class="tfoot"><th colspan="7"><div class="hr_line"></div></td></th>
HTML;
		
		// pagination

		$npp_nav = "<div class=\"news_navigation\" style=\"margin-bottom:5px; margin-top:5px;\">";
		
		if( $start_from > 0 ) {
			$previous = $start_from - $news_per_page;
			$npp_nav .= "<a onClick=\"javascript:search_submit($previous); return(false);\" href=\"#\" title=\"{$lang['edit_prev']}\">&lt;&lt;</a> ";
		}
		
		if( $all_count_news > $news_per_page ) {
			
			$enpages_count = @ceil( $all_count_news / $news_per_page );
			$enpages_start_from = 0;
			$enpages = "";
			
			if( $enpages_count <= 10 ) {
				
				for($j = 1; $j <= $enpages_count; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a> ";
					
					} else {
						
						$enpages .= "<span>$j</span> ";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$npp_nav .= $enpages;
			
			} else {
				
				$start = 1;
				$end = 10;
				
				if( $start_from > 0 ) {
					
					if( ($start_from / $news_per_page) > 4 ) {
						
						$start = @ceil( $start_from / $news_per_page ) - 3;
						$end = $start + 9;
						
						if( $end > $enpages_count ) {
							$start = $enpages_count - 10;
							$end = $enpages_count - 1;
						}
						
						$enpages_start_from = ($start - 1) * $news_per_page;
					
					}
				
				}
				
				if( $start > 2 ) {
					
					$enpages .= "<a onClick=\"javascript:search_submit(0); return(false);\" href=\"#\">1</a> ... ";
				
				}
				
				for($j = $start; $j <= $end; $j ++) {
					
					if( $enpages_start_from != $start_from ) {
						
						$enpages .= "<a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$j</a> ";
					
					} else {
						
						$enpages .= "<span>$j</span> ";
					}
					
					$enpages_start_from += $news_per_page;
				}
				
				$enpages_start_from = ($enpages_count - 1) * $news_per_page;
				$enpages .= "... <a onClick=\"javascript:search_submit($enpages_start_from); return(false);\" href=\"#\">$enpages_count</a> ";
				
				$npp_nav .= $enpages;
			
			}
		
		}
		
		if( $all_count_news > $i ) {
			$how_next = $all_count_news - $i;
			if( $how_next > $news_per_page ) {
				$how_next = $news_per_page;
			}
			$npp_nav .= "<a onClick=\"javascript:search_submit($i); return(false);\" href=\"#\" title=\"{$lang['edit_next']}\">&gt;&gt;</a>";
		}
		
		$npp_nav .= "</div>";
		
		// pagination
		

		if( $entries_showed != 0 ) {
			echo <<<HTML
<tr class="tfoot"><th>{$npp_nav}</th>
<th colspan=5 valign="top"><div style="margin-bottom:5px; margin-top:5px; text-align: right;">
<select name=action>
<option value="">{$lang['edit_selact']}</option>
<option value="mass_move_to_cat">{$lang['edit_selcat']}</option>
<option value="mass_edit_symbol">{$lang['edit_selsymbol']}</option>
<option value="mass_edit_author">{$lang['edit_selauthor']}</option>
<option value="mass_edit_cloud">{$lang['edit_cloud']}</option>
<option value="mass_date">{$lang['mass_edit_date']}</option>
<option value="mass_approve">{$lang['mass_edit_app']}</option>
<option value="mass_not_approve">{$lang['mass_edit_notapp']}</option>
<option value="mass_fixed">{$lang['mass_edit_fix']}</option>
<option value="mass_not_fixed">{$lang['mass_edit_notfix']}</option>
<option value="mass_comments">{$lang['mass_edit_comm']}</option>
<option value="mass_not_comments">{$lang['mass_edit_notcomm']}</option>
<option value="mass_rating">{$lang['mass_edit_rate']}</option>
<option value="mass_not_rating">{$lang['mass_edit_notrate']}</option>
<option value="mass_main">{$lang['mass_edit_main']}</option>
<option value="mass_not_main">{$lang['mass_edit_notmain']}</option>
<option value="mass_clear_count">{$lang['mass_clear_count']}</option>
<option value="mass_clear_rating">{$lang['mass_clear_rating']}</option>
<option value="mass_clear_cloud">{$lang['mass_clear_cloud']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>
<input type=hidden name=mod value="massactions">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}">
</div></th></tr>
HTML;
			
			if( $all_count_news > $news_per_page ) {
				
				echo <<<HTML
<tr class="tfoot"><th colspan="6">
{$lang['edit_go_page']} <input class="edit bk" style="text-align: center" name="gopage" id="gopage" value="" type="text" size="3"> <input onClick="javascript:gopage_submit(document.getElementById('gopage').value); return(false);" class="btn btn-info btn-mini" type="button" value=" ok ">
</th></tr>
HTML;
			
			}
		
		}
		
		echo <<<HTML
	</table>
</td>
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
</div></form>
<script type="text/javascript">
$(function(){

	$("#newslist").delegate("tr", "hover", function(){
	  $(this).toggleClass("hoverRow");
	});

});
</script>
HTML;
	
	}
	
	echofooter();
} 

// ********************************************************************************
// Показ новости и редактирование
// ********************************************************************************
elseif( $action == "editnews" ) {
	$id = intval( $_GET['id'] );
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );
	
	$found = FALSE;
	
	if( $id == $row['id'] ) $found = TRUE;
	if( ! $found ) {
		msg( "error", $lang['cat_error'], $lang['edit_nonews'] );
	}
	
	$cat_list = explode( ',', $row['category'] );
	
	$have_perm = 0;
	
	if( $user_group[$member_id['user_group']]['allow_edit'] and $row['autor'] == $member_id['name'] ) {
		$have_perm = 1;
	}

	if( $user_group[$member_id['user_group']]['allow_all_edit'] ) {
		$have_perm = 1;
		
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
		
		foreach ( $cat_list as $selected ) {
			if( $allow_list[0] != "all" and !in_array( $selected, $allow_list ) AND $row['approve']) $have_perm = 0;
		}
	}
	
	if( ($member_id['user_group'] == 1) ) {
		$have_perm = 1;
	}
	
	if( ! $have_perm ) {
		msg( "error", $lang['addnews_denied'], $lang['edit_denied'], "$PHP_SELF?mod=editnews&action=list" );
	}
	
	$row['title'] = $parse->decodeBBCodes( $row['title'], false );
	$row['title'] = str_replace("&amp;","&", $row['title'] );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );
	$row['keywords'] = $parse->decodeBBCodes( $row['keywords'], false );
	$row['expires'] = ($row['expires'] == "0000-00-00") ? "" : $row['expires'];
	$row['metatitle'] = stripslashes( $row['metatitle'] );
	
	if( $row['allow_br'] != '1' or $config['allow_admin_wysiwyg'] == "yes" ) {
		$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], true, $config['allow_admin_wysiwyg'] );
		$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], true, $config['allow_admin_wysiwyg'] );
	} else {
		$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], false );
		$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], false );
	}
	
	$access = permload( $row['access'] );
	
	if( $row['votes'] ) {
		$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll where news_id = '{$row['id']}'" );
		$poll['title'] = $parse->decodeBBCodes( $poll['title'], false );
		$poll['frage'] = $parse->decodeBBCodes( $poll['frage'], false );
		$poll['body'] = $parse->decodeBBCodes( $poll['body'], false );
		$poll['multiple'] = $poll['multiple'] ? "checked" : "";
	}

	$expires = $db->super_query( "SELECT * FROM " . PREFIX . "_post_log where news_id = '{$row['id']}'" );

	if ( $expires['expires'] ) $expires['expires'] = date("Y-m-d", $expires['expires']);

	$js_array[] = "engine/skins/calendar.js";
	$js_array[] = "engine/skins/tabs.js";
	$js_array[] = "engine/skins/autocomplete.js";
	$js_array[] = "engine/skins/chosen/chosen.js";

	echoheader( "editnews", $lang['edit_head'] );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = "no";
	
	// Доп. поля
	$xfieldsaction = "categoryfilter";
	include (ENGINE_DIR . '/inc/xfields.php');
	echo $categoryfilter;
	

	echo <<<HTML
<script type="text/javascript">
<!-- begin
function popupedit( name ){

		var rndval = new Date().getTime(); 

		$('body').append('<div id="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #666666; opacity: .40;filter:Alpha(Opacity=40); z-index: 999; display:none;"></div>');
		$('#modal-overlay').css({'filter' : 'alpha(opacity=40)'}).fadeIn('slow');
	
		$("#dleuserpopup").remove();
		$("body").append("<div id='dleuserpopup' title='{$lang['user_edhead']}' style='display:none'></div>");
	
		$('#dleuserpopup').dialog({
			autoOpen: true,
			width: 560,
			height: 500,
			dialogClass: "modalfixed",
			buttons: {
				"{$lang['user_can']}": function() { 
					$(this).dialog("close");
					$("#dleuserpopup").remove();							
				},
				"{$lang['user_save']}": function() { 
					document.getElementById('edituserframe').contentWindow.document.getElementById('saveuserform').submit();							
				}
			},
			open: function(event, ui) { 
				$("#dleuserpopup").html("<iframe name='edituserframe' id='edituserframe' width='100%' height='400' src='{$PHP_SELF}?mod=editusers&action=edituser&user=" + name + "&rndval=" + rndval + "' frameborder='0' marginwidth='0' marginheight='0' allowtransparency='true'></iframe>");
			},
			beforeClose: function(event, ui) { 
				$("#dleuserpopup").html("");
			},
			close: function(event, ui) {
					$('#modal-overlay').fadeOut('slow', function() {
			        $('#modal-overlay').remove();
			    });
			 }
		});

		if ($(window).width() > 830 && $(window).height() > 530 ) {
			$('.modalfixed.ui-dialog').css({position:"fixed"});
			$('#dleuserpopup').dialog( "option", "position", ['0','0'] );
		}

		return false;

}
// end -->
</script>
HTML;

	echo "
    <script type=\"text/javascript\">
    function preview(){";
	
	if( $config['allow_admin_wysiwyg'] == "yes" ) {
		echo "submit_all_data();";
	}
	
	echo "if(document.addnews.title.value == ''){ DLEalert('$lang[addnews_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=400,width=750,left=0,top=0,resizable=1,scrollbars=1')
        document.addnews.mod.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.mod.value='editnews';document.addnews.target='_self'\",500)
    }
    }
    function sendNotice( id ){
		var b = {};

		b[dle_act_lang[3]] = function() { 
			$(this).dialog('close');						
		};

		b['{$lang['p_send']}'] = function() { 
			if ( $('#dle-promt-text').val().length < 1) {
				$('#dle-promt-text').addClass('ui-state-error');
			} else {
				var response = $('#dle-promt-text').val()
				$(this).dialog('close');
				$('#dlepopup').remove();
				$.post('engine/ajax/message.php', { id: id,  text: response, allowdelete: \"no\" },
					function(data){
						if (data == 'ok') { DLEalert('{$lang['p_send_ok']}', '{$lang['p_info']}'); }
					});
	
			}				
		};

		$('#dlepopup').remove();
					
		$('body').append(\"<div id='dlepopup' title='{$lang['p_title']}' style='display:none'><br />{$lang['p_text']}<br /><br /><textarea name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea></div>\");
					
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			buttons: b
		});

	}

    function confirmDelete(url, id){

		var b = {};
	
		b[dle_act_lang[1]] = function() { 
						$(this).dialog(\"close\");						
				    };

		b['{$lang['p_message']}'] = function() { 
						$(this).dialog(\"close\");

						var bt = {};
					
						bt[dle_act_lang[3]] = function() { 
										$(this).dialog('close');						
								    };
					
						bt['{$lang['p_send']}'] = function() { 
										if ( $('#dle-promt-text').val().length < 1) {
											 $('#dle-promt-text').addClass('ui-state-error');
										} else {
											var response = $('#dle-promt-text').val()
											$(this).dialog('close');
											$('#dlepopup').remove();
											$.post('engine/ajax/message.php', { id: id,  text: response },
											  function(data){
											    if (data == 'ok') { document.location=url; } else { DLEalert('{$lang['p_not_send']}', '{$lang['p_info']}'); }
										  });
	
										}				
									};
					
						$('#dlepopup').remove();
					
						$('body').append(\"<div id='dlepopup' title='{$lang['p_title']}' style='display:none'><br />{$lang['p_text']}<br /><br /><textarea name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea></div>\");
					
						$('#dlepopup').dialog({
							autoOpen: true,
							width: 500,
							buttons: bt
						});
					
				    };
	
		b[dle_act_lang[0]] = function() { 
						$(this).dialog(\"close\");
						document.location=url;					
					};
	
		$(\"#dlepopup\").remove();
	
		$(\"body\").append(\"<div id='dlepopup' title='{$lang['p_confirm']}' style='display:none'><br /><div id='dlepopupmessage'>{$lang['edit_cdel']}</div></div>\");
	
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			buttons: b
		});


    }

    function CheckStatus(Form){
		if(Form.allow_date.checked) {
		Form.allow_now.disabled = true;
		Form.allow_now.checked = false;
		} else {
		Form.allow_now.disabled = false;
		}
    }

	function auto_keywords ( key )
	{
		var wysiwyg = '{$config['allow_admin_wysiwyg']}';

		if (wysiwyg == \"yes\") {
			submit_all_data();
		}

		var short_txt = document.getElementById('short_story').value;
		var full_txt = document.getElementById('full_story').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, full_txt: full_txt, key: key }, function(data){

			HideLoading('');

			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').val(data); }
	
		});

		return false;
	}

	function find_relates ()
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post('engine/ajax/find_relates.php', { title: title, id: '{$row['id']}' }, function(data){
	
			HideLoading('');
	
			$('#related_news').html(data);
	
		});

		return false;

	};

	function checkxf ( )
	{

		var status = '';

		$('[uid=\"essential\"]:visible').each(function(indx) {

			if($.trim($(this).find('[rel=\"essential\"]').val()).length < 1) {
			
				DLEalert('{$lang['addnews_xf_alert']}', '{$lang['p_info']}');

				status = 'fail';
			
			}

		});

		if(document.addnews.title.value == ''){

			DLEalert('{$lang['addnews_alert']}', '{$lang['p_info']}'); 

			status = 'fail';

		}

		return status;

	};

	$(function(){
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
 
		$( '#tags' ).autocomplete({
			source: function( request, response ) {
				$.getJSON( 'engine/ajax/find_tags.php', {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				terms.pop();
				terms.push( ui.item.value );
				terms.push( '' );
				this.value = terms.join( ', ' );
				return false;
			}
		});

		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
    </SCRIPT>";
	
	echo "<form method=post name=\"addnews\" id=\"addnews\" onsubmit=\"if(checkxf()=='fail') return false;\" action=\"\">";
	
	$categories_list = CategoryNewsSelection( $cat_list, 0 );
	if( $config['allow_multi_category'] ) $category_multiple = "class=\"categoryselect\" multiple";
	else $category_multiple = "class=\"categoryselect\"";
	
	if( $member_id['user_group'] == 1 ) {
		
		$author_info = "<input type=\"text\" name=\"new_author\" size=\"20\"  class=\"edit bk\" style=\"vertical-align: middle;\" value=\"{$row['autor']}\"><input type=\"hidden\" name=\"old_author\" value=\"{$row['autor']}\" />";
	
	} else {
		
		$author_info = "<b>{$row['autor']}</b>";
	
	}


	if ( $user_group[$member_id['user_group']]['admin_editusers'] ) {

		$author_info .= "&nbsp;<a onclick=\"javascript:popupedit('".urlencode($row['autor'])."'); return(false)\" href=\"#\"><img src=\"engine/skins/images/user_edit.png\" style=\"vertical-align: middle;border: none;\" /></a>";

	}

	
	echo <<<HTML
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
<link rel="stylesheet" type="text/css" href="engine/skins/chosen/chosen.css"/>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['edit_etitle']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<div id="dle_tabView1">
<div class="dle_aTab">
<table width="100%">
    <tr>
        <td width="140" style="padding-left:5px;">{$lang['edit_info']}</td>
        <td>ID=<b>{$row['id']}</b>, {$lang['edit_eau']} {$author_info}</td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['edit_et']}</td>
        <td><input class="edit bk" type="text" style="width:350px;" name="title" id="title" value="{$row['title']}"> <input class="btn btn-mini" type="button" onClick="find_relates(); return false;" style="width:160px;" value="{$lang['b_find_related']}"> <a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_title]}', this, event, '220px')">[?]</a><span id="related_news"></span></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['edit_edate']}</td>
        <td><input type="text" name="newdate" id="f_date_c" size="20"  class="edit bk" value="{$row['date']}">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<input type="checkbox" name="allow_date" id="allow_date" value="yes" onclick="CheckStatus(addnews)" checked>&nbsp;{$lang['edit_ndate']}&nbsp;<input type="checkbox" name="allow_now" id="allow_now" value="yes" disabled>&nbsp;{$lang['edit_jdate']}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_c",     // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
        button         :    "f_trigger_c",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment 
		timeFormat     :    "24",
		showsTime      :    true,
        singleClick    :    true
    });
</script></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['edit_cat']}</td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="category[]" id="category" onchange="onCategoryChange(this)" {$category_multiple} style="width:350px;">
		{$categories_list}
		</select>
		</td>
    </tr>
</table>
<div class="hr_line"></div>
<table width="100%">
HTML;
	
	if( $config['allow_admin_wysiwyg'] == "yes" ) {
		
		include (ENGINE_DIR . '/editor/shortnews.php');
	
	} else {
		$bb_editor = true;
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		
		echo <<<HTML
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_short']}<br /><input class=bbcodes style="width: 30px;" onclick="document.addnews.short_story.rows += 5;" type=button value=" + ">&nbsp;&nbsp;<input class=bbcodes style="width: 30px;" onclick="document.addnews.short_story.rows -= 5;" type=button value=" - "></td>
        <td>{$bb_code}
	<textarea rows="16" style="width:98%;" onclick="setFieldName(this.name)" name="short_story" id="short_story" class="bk">{$row['short_story']}</textarea>
	</td></tr>
HTML;
	}
	
	if( $config['allow_admin_wysiwyg'] == "yes" ) {
		
		include (ENGINE_DIR . '/editor/fullnews.php');
	
	} else {
		
		echo <<<HTML
    <tr>
    <td height="29" style="padding-left:5px;">{$lang['addnews_full']}<br /><span class="navigation">({$lang['addnews_alt']})</span><br /><input class=bbcodes style="width: 30px;" onclick="document.addnews.full_story.rows += 5;" type=button value=" + ">&nbsp;&nbsp;<input class=bbcodes style="width: 30px;" onclick="document.addnews.full_story.rows -= 5;" type=button value=" - "></td>
    <td>{$bb_panel}<textarea rows="19" onclick="setFieldName(this.name)" name="full_story" id="full_story" style="width:98%;" class="bk">{$row['full_story']}</textarea>
	</td></tr>
HTML;
	}
	
	// Доп. поля
	$xfieldsaction = "list";
	$xfieldsid = $row['xfields'];
	$xfieldscat = $row['category'];
	include (ENGINE_DIR . '/inc/xfields.php');

	if( $config['allow_admin_wysiwyg'] != "yes" ) $output = str_replace("<!--panel-->", $bb_panel, $output);
	echo $output;
	
	if( $row['allow_comm'] ) $ifch = "checked";	else $ifch = "";
	if( $row['allow_main'] ) $ifmain = "checked"; else $ifmain = "";
	if( $row['approve'] ) $ifapp = "checked"; else $ifapp = "";
	if( $row['fixed'] ) $iffix = "checked";	else $iffix = "";
	if( $row['allow_rate'] ) $ifrat = "checked"; else $ifrat = "";
	if( $row['disable_index'] ) $ifdis = "checked"; else $ifdis = "";
	
	if( $user_group[$member_id['user_group']]['allow_fixed'] and $config['allow_fixed'] ) $fix_input = "<input type=\"checkbox\" name=\"news_fixed\" value=\"1\" $iffix>&nbsp;$lang[addnews_fix]"; else $fix_input = "&nbsp;";
	if( $user_group[$member_id['user_group']]['allow_main'] ) $main_input = "<input type=\"checkbox\" name=\"allow_main\" value=\"1\" {$ifmain}>&nbsp;{$lang['addnews_main']}"; else $main_input = "&nbsp;";
	if($member_id['user_group'] < 3 ) $disable_index = "<input type=\"checkbox\" name=\"disable_index\" value=\"1\" {$ifdis}>&nbsp;{$lang['add_disable_index']}"; else $disable_index = "&nbsp;";
	
	if( $row['allow_br'] == '1' ) $fix_br_cheked = "checked";
	else $fix_br_cheked = "";
	
	if( $config['allow_admin_wysiwyg'] != "yes" ) $fix_br = "<input type=\"checkbox\" name=\"allow_br\" value=\"1\" {$fix_br_cheked}>&nbsp;{$lang['allow_br']}";
	else $fix_br = "";
	
	if( $row['editdate'] ) {
		$row['editdate'] = date( "d.m.Y H:i:s", $row['editdate'] );
		$lang['news_edit_date'] = $lang['news_edit_date'] . " " . $row['editor'] . " - " . $row['editdate'];
	} else
		$lang['news_edit_date'] = "";
	if( $row['view_edit'] == '1' ) $view_edit_cheked = "checked";
	else $view_edit_cheked = "";

	$exp_action = array();
	$exp_action[$expires['action']] = "selected=\"selected\"";


	echo <<<HTML
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;"><br /><br /><br />{$lang['news_edit_reason']}</td>
        <td><input type="checkbox" name="view_edit" value="1" {$view_edit_cheked}>{$lang['allow_view_edit']}<br /><br /><input class="edit bk" type="text" size="55" name="editreason" id="editreason" value="{$row['reason']}"> {$lang['news_edit_date']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['addnews_option']}</td>
        <td>
		<table>
			<tr>
				<td style="width:220px;"><input type="checkbox" name="approve" value="1" {$ifapp}>&nbsp;{$lang['addnews_mod']}</td>
				<td style="width:200px;"><br /><br />&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>{$main_input}</td>
				<td><input type="checkbox" name="allow_comm" value="1" {$ifch}>&nbsp;{$lang['addnews_comm']}</td>
				<td>{$disable_index}</td>
			</tr>
			<tr>
				<td><input type="checkbox" name="allow_rating" value="1" {$ifrat}>&nbsp;{$lang['addnews_allow_rate']}</td>
				<td>{$fix_input}</td>
				<td>&nbsp;</td>
			</tr>
		</table><br />{$fix_br}</td>
	</tr>
</table></div>
HTML;
	
	echo <<<HTML
	<div class="dle_aTab" style="display:none;">
<table width="100%">
    <tr>
        <td width="140" style="padding:4px;">{$lang['v_ftitle']}</td>
        <td ><input type="text" class="edit bk" name="vote_title" style="width:350px" value="{$poll['title']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_ftitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['vote_title']}</td>
        <td><input type="text" class="edit bk" name="frage" style="width:350px" value="{$poll['frage']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vtitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">$lang[vote_body]<br /><span class="navigation">$lang[vote_str_1]</span></td>
        <td><textarea rows="10" style="width:356px;" name="vote_body" class="bk">{$poll['body']}</textarea>
    </td>
    </tr>
    <tr>
        <td style="padding:4px;">&nbsp;</td>
        <td><input type="checkbox" name="allow_m_vote" value="1" {$poll['multiple']}> {$lang['v_multi']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
</table>
<div class="navigation">{$lang['v_info']}</div>
	</div>
	<div class="dle_aTab" style="display:none;">
	<table width="100%">
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['catalog_url']}</td>
        <td><input type="text" name="catalog_url" size="5"  class="edit bk" value="{$row['symbol']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[catalog_hint_url]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_url']}</td>
        <td><input type="text" name="alt_name" size="55"  class="edit bk" value="{$row['alt_name']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_url]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_tags']}</td>
        <td><input type="text" id="tags" name="tags" size="55"  class="edit bk" value="{$row['tags']}" autocomplete="off" /><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_tags]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['date_expires']}</td>
        <td><input type="text" name="expires" id="e_date_c" size="20"  class="edit bk" value="{$expires['expires']}">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="e_trigger_c" style="cursor: pointer; border: 0" /> {$lang['cat_action']} <select name="expires_action"><option value="0" {$exp_action[0]}>{$lang['edit_dnews']}</option><option value="1" {$exp_action[1]}>{$lang['mass_edit_notapp']}</option><option value="2" {$exp_action[2]}>{$lang['mass_edit_notmain']}</option><option value="3" {$exp_action[3]}>{$lang['mass_edit_notfix']}</option></select><a href="#" class="hintanchor" onMouseover="showhint('{$lang['hint_expires']}', this, event, '320px')">[?]</a>
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "e_date_c",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "e_trigger_c",  // trigger for the calendar (button ID)
        align          :    "Br",           // alignment 
        singleClick    :    true
    });
</script></td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
	    <tr>
	        <td>&nbsp;</td>
	        <td>{$lang['add_metatags']}<a href="#" class="hintanchor" onMouseover="showhint('{$lang['hint_metas']}', this, event, '220px')">[?]</a></td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_title']}</td>
	        <td><input type="text" name="meta_title" style="width:388px;" class="edit bk" value="{$row['metatitle']}"></td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_descr']}</td>
	        <td><input type="text" name="descr" id="autodescr" style="width:388px;" class="edit bk" value="{$row['descr']}"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_keys']}</td>
	        <td><textarea name="keywords" id='keywords' style="width:394px;height:70px;" class="bk">{$row['keywords']}</textarea><br />
			<input onClick="auto_keywords(1)" type="button" class="btn" value="{$lang['btn_descr']}" style="width:170px;">&nbsp;
			<input onClick="auto_keywords(2)" type="button" class="btn" value="{$lang['btn_keyword']}" style="width:216px;">
			</td>
	    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
	</table>
	</div>
	<div class="dle_aTab" style="display:none;">

<table width="100%">
HTML;
	
	if( $member_id['user_group'] < 3 ) {
		foreach ( $user_group as $group ) {
			if( $group['id'] > 1 ) {
				echo <<<HTML
    <tr>
        <td width="150" style="padding:4px;">{$group['group_name']}</td>
        <td><select name="group_extra[{$group['id']}]">
		<option value="0">{$lang['ng_group']}</option>
		<option value="1" {$access[$group['id']][1]}>{$lang['ng_read']}</option>
		<option value="2" {$access[$group['id']][2]}>{$lang['ng_all']}</option>
		<option value="3" {$access[$group['id']][3]}>{$lang['ng_denied']}</option>
		</select></td>
    </tr>
HTML;
			}
		}
	} else {
		
		echo <<<HTML
    <tr>
        <td style="padding:4px;"><br />{$lang['tabs_not']}</br /><br /></td>
    </tr>
HTML;
	
	}

	if ($row['autor'] != $member_id['name']) $notice_btn = "<input onClick=\"sendNotice('{$id}')\" type=\"button\" class=\"btn btn-inverse\" value=\"{$lang['btn_notice']}\" >&nbsp;"; else $notice_btn = "";
	
	echo <<<HTML
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
</table>
<div class="navigation">{$lang['tabs_g_info']}</div>
	</div>
</div>
	<script type="text/javascript">
jQuery(document).ready(function($){
	initTabs('dle_tabView1',Array('{$lang['tabs_news']}','{$lang['tabs_vote']}','{$lang['tabs_extra']}','{$lang['tabs_perm']}'),0, '100%',0);
});
	</script>
<div style="padding-left:150px;padding-top:5px;padding-bottom:5px;">
	<input type="submit" class="btn btn-success" value="{$lang['btn_send']}" style="width:100px;">&nbsp;
	<input onClick="preview()" type="button" class="btn btn-info" value="{$lang['btn_preview']}" style="width:100px;">&nbsp;
	{$notice_btn}
	<input onClick="confirmDelete('$PHP_SELF?mod=editnews&action=doeditnews&ifdelete=yes&id=$id&user_hash=$dle_login_hash', '{$id}')" type="button" class="btn btn-danger" value="{$lang['edit_dnews']}" style="width:100px;">
    <input type="hidden" name="id" value="$id" />
    <input type="hidden" name="expires_alt" value="{$expires['expires']}{$expires['action']}" />
    <input type="hidden" name="user_hash" value="$dle_login_hash" />
    <input type="hidden" name="action" value="doeditnews" />
    <input type="hidden" name="mod" value="editnews" />
</div>
HTML;
	
	echo <<<HTML
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div></form>
HTML;
	
	echofooter();
} 
// ********************************************************************************
// Сохранение или удаление новости
// ********************************************************************************
elseif( $action == "doeditnews" ) {

	$id = intval( $_GET['id'] );
	
	$allow_comm = isset( $_POST['allow_comm'] ) ? intval( $_POST['allow_comm'] ) : 0;
	$allow_main = isset( $_POST['allow_main'] ) ? intval( $_POST['allow_main'] ) : 0;
	$approve = isset( $_POST['approve'] ) ? intval( $_POST['approve'] ) : 0;
	$allow_rating = isset( $_POST['allow_rating'] ) ? intval( $_POST['allow_rating'] ) : 0;
	$news_fixed = isset( $_POST['news_fixed'] ) ? intval( $_POST['news_fixed'] ) : 0;
	$allow_br = isset( $_POST['allow_br'] ) ? intval( $_POST['allow_br'] ) : 0;
	$view_edit = isset( $_POST['view_edit'] ) ? intval( $_POST['view_edit'] ) : 0;
	$category = $_POST['category'];
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;

	if($member_id['user_group'] > 2 ) $disable_index = 0;

	if( ! count( $category ) ) {
		$category = array ();
		$category[] = '0';
	}

	$category_list = array();

	foreach ( $category as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );
	
	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
	
	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) and $member_id['user_group'] != 1 ) $approve = 0;
	}

	if( !$user_group[$member_id['user_group']]['moderation'] ) $approve = 0;

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	
	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" AND ! in_array( $selected, $allow_list ) AND $ifdelete != "yes") msg( "error", $lang['addnews_error'], $lang['news_err_41'], "javascript:history.go(-1)" );
	}

	$title = $parse->process( trim( strip_tags ($_POST['title']) ) );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

		$_POST['short_story'] = strip_tags ($_POST['short_story']);
		$_POST['full_story'] = strip_tags ($_POST['full_story']);

	}

	if ( $config['allow_admin_wysiwyg'] == "yes" ) $parse->allow_code = false;
	
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );
	
	if( $config['allow_admin_wysiwyg'] == "yes" or $allow_br != '1' ) {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
	
	} else {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
	
	}

	if( $parse->not_allowed_text ) {
		msg( "error", $lang['addnews_error'], $lang['news_err_39'], "javascript:history.go(-1)" );
	}
	
	if( trim( $title ) == "" and $ifdelete != "yes" ) msg( "error", $lang['cat_error'], $lang['addnews_alert'], "javascript:history.go(-1)" );

	if( dle_strlen( $title, $config['charset'] ) > 255 ) {
		msg( "error", $lang['cat_error'], $lang['addnews_ermax'], "javascript:history.go(-1)" );
	}
	
	if( trim( $_POST['alt_name'] ) == "" or ! $_POST['alt_name'] ) $alt_name = totranslit( stripslashes( $title ) );
	else $alt_name = totranslit( stripslashes( $_POST['alt_name'] ) );
	
	$title = $db->safesql( $title );
	$metatags = create_metatags( $short_story . $full_story );
	
	$catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['catalog_url'] ) ) ) ), 0, 3, $config['charset'] ) );

	if ($config['create_catalog'] AND !$catalog_url) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ) ), 0, 1, $config['charset'] ) );

	$editreason = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['editreason'] ) ) ), ENT_QUOTES ) );
	
	if( @preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_POST['tags'] ) ) $_POST['tags'] = "";
	else $_POST['tags'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_QUOTES ) );

	if ( $_POST['tags'] ) {

		$temp_array = array();
		$tags_array = array();
		$temp_array = explode (",", $_POST['tags']);

		if (count($temp_array)) {

			foreach ( $temp_array as $value ) {
				if( trim($value) ) $tags_array[] = trim( $value );
			}

		}

		if ( count($tags_array) ) $_POST['tags'] = implode(", ", $tags_array); else $_POST['tags'] = "";

	}
	
	// обработка опроса
	if( trim( $_POST['vote_title'] != "" ) ) {
		
		$add_vote = 1;
		$vote_title = trim( $db->safesql( $parse->process( $_POST['vote_title'] ) ) );
		$frage = trim( $db->safesql( $parse->process( $_POST['frage'] ) ) );
		$vote_body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['vote_body'] ), false ) );
		$allow_m_vote = intval( $_POST['allow_m_vote'] );
	
	} else
		$add_vote = 0;
		
	// обработка доступа
	if( $member_id['user_group'] < 3 and $ifdelete != "yes" ) {
		
		$group_regel = array ();
		
		foreach ( $_POST['group_extra'] as $key => $value ) {
			if( $value ) $group_regel[] = intval( $key ) . ':' . intval( $value );
		}
		
		if( count( $group_regel ) ) $group_regel = implode( "||", $group_regel );
		else $group_regel = "";
	
	} else
		$group_regel = '';

	if ( ($_POST['expires'].$_POST['expires_action']) != $_POST['expires_alt'] ) {	
		if( trim( $_POST['expires'] ) != "" ) {
			if( (($expires = strtotime( $_POST['expires'] )) === - 1) OR !$expires) {
				msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
			} 
		} else $expires = '';

		$expires_change = true;

	} else $expires_change = false;
	
	$no_permission = FALSE;
	$okdeleted = FALSE;
	$okchanges = FALSE;
	
	$db->query( "SELECT id, title, autor, category, approve, tags, news_id FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '$id'" );
	
	while ( $row = $db->get_row() ) {
		$item_db[0] = $row['id'];
		$item_db[1] = $row['autor'];
		$item_db[2] = $row['tags'];
		$item_db[3] = $row['approve'];
		$item_db[4] = $db->safesql( $row['title'] );
		$item_db[5] = explode( ',', $row['category'] );
		$item_db[6] = $row['news_id'];
	}
	
	$db->free();

	if( $ifdelete != "yes" ) {

		if( $config['safe_xfield'] ) {
			$parse->ParseFilter();
			$parse->safe_mode = true;
		}

		$xfieldsaction = "init";
		$xfieldsid = $item_db[0];
		include (ENGINE_DIR . '/inc/xfields.php');
	}
	

	if( $item_db[0] ) {
		
		$have_perm = 0;
		
		if( $user_group[$member_id['user_group']]['allow_all_edit'] ) $have_perm = 1;
		
		if( $user_group[$member_id['user_group']]['allow_edit'] and $item_db[1] == $member_id['name'] ) {
			$have_perm = 1;
		}

		if( $ifdelete == "yes" ) {
	
			$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
			
			foreach ( $item_db[5] as $selected ) {
				if( $allow_list[0] != "all" AND !in_array($selected, $allow_list) ) $have_perm = 0;
			}

			if( !$user_group[$member_id['user_group']]['moderation']) {

				$have_perm = 0;

			}	
		}

		if( ($member_id['user_group'] == 1) ) {
			$have_perm = 1;
		}
		
		if( $have_perm ) {
			
			if( $ifdelete != "yes" ) {
				$okchanges = TRUE;
				
				// Обработка даты и времени
				$added_time = time() + ($config['date_adjust'] * 60);
				$newdate = $_POST['newdate'];
				
				if( $_POST['allow_date'] != "yes" ) {
					
					if( $_POST['allow_now'] == "yes" ) $thistime = date( "Y-m-d H:i:s", $added_time );
					elseif( (($newsdate = strtotime( $newdate )) === - 1) OR !$newsdate ) {
						msg( "error", $lang['cat_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
					} else {
						
						$thistime = date( "Y-m-d H:i:s", $newsdate );
						
						if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
							$thistime = date( "Y-m-d H:i:s", $added_time );
						}
					
					}
					
					$db->query( "UPDATE " . PREFIX . "_post SET title='$title', date='$thistime', short_story='$short_story', full_story='$full_story', xfields='$filecontents', descr='{$metatags['description']}', keywords='{$metatags['keywords']}', category='$category_list', alt_name='$alt_name', allow_comm='$allow_comm', approve='$approve', allow_main='$allow_main', fixed='$news_fixed', allow_br='$allow_br', symbol='$catalog_url', tags='{$_POST['tags']}', metatitle='{$metatags['title']}' WHERE id='$item_db[0]'" );
				
				} else {
					
					$db->query( "UPDATE " . PREFIX . "_post SET title='$title', short_story='$short_story', full_story='$full_story', xfields='$filecontents', descr='{$metatags['description']}', keywords='{$metatags['keywords']}', category='$category_list', alt_name='$alt_name', allow_comm='$allow_comm', approve='$approve', allow_main='$allow_main', fixed='$news_fixed', allow_br='$allow_br', symbol='$catalog_url', tags='{$_POST['tags']}', metatitle='{$metatags['title']}' WHERE id='$item_db[0]'" );
				}

				if ($item_db[6]) $db->query( "UPDATE " . PREFIX . "_post_extras SET allow_rate='$allow_rating', votes='$add_vote', disable_index='$disable_index', access='$group_regel', editdate='$added_time', editor='{$member_id['name']}', reason='$editreason', view_edit='$view_edit' WHERE news_id='$item_db[0]'" );
				else $db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, access, editdate, editor, reason, view_edit) VALUES('{$item_db[0]}', '{$allow_rating}', '{$add_vote}', '{$disable_index}', '{$group_regel}', '{$added_time}', '{$member_id['name']}', '{$editreason}', '{$view_edit}')" );

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '25', '{$title}')" );

				
				if( $add_vote ) {
					
					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll WHERE news_id = '$item_db[0]'" );
					
					if( $count['count'] ) $db->query( "UPDATE  " . PREFIX . "_poll set title='$vote_title', frage='$frage', body='$vote_body', multiple='$allow_m_vote' WHERE news_id = '$item_db[0]'" );
					else $db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('$item_db[0]', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );
				
				} else {
					$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='$item_db[0]'" );
					$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='$item_db[0]'" );
				}

				if ( $expires_change ) {
					if( $expires ) {
						$expires_action = intval($_POST['expires_action']);
						$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='$item_db[0]'" );
						$db->query( "INSERT INTO " . PREFIX . "_post_log (news_id, expires, action) VALUES('$item_db[0]', '$expires', '$expires_action')" );
					} else {
	
						$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='$item_db[0]'" );
	
					}
				}
				
				// Смена автора публикации
				if( $member_id['user_group'] == 1 AND $_POST['new_author'] != $_POST['old_author'] ) {
					
					$_POST['new_author'] = $db->safesql( $_POST['new_author'] );
					
					$row = $db->super_query( "SELECT user_id  FROM " . USERPREFIX . "_users WHERE name = '{$_POST['new_author']}'" );
					
					if( $row['user_id'] ) {
						
						$db->query( "UPDATE " . PREFIX . "_post SET autor='{$_POST['new_author']}' WHERE id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_post_extras SET user_id='{$row['user_id']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_images SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );
						$db->query( "UPDATE " . PREFIX . "_files SET author='{$_POST['new_author']}' WHERE news_id='$item_db[0]'" );
						
						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num+1 where user_id='{$row['user_id']}'" );
						$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 where name='$item_db[1]'" );
					
					} else {
						
						msg( "error", $lang['addnews_error'], $lang['edit_no_author'], "javascript:history.go(-1)" );
					
					}
				
				}
				
				// Облако тегов
				if( $_POST['tags'] != $item_db[2] or $approve != $item_db[3] ) {
					$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '$item_db[0]'" );
					
					if( $_POST['tags'] != "" and $approve ) {
						
						$tags = array ();
						
						$_POST['tags'] = explode( ",", $_POST['tags'] );
						
						foreach ( $_POST['tags'] as $value ) {
							
							$tags[] = "('" . $item_db[0] . "', '" . trim( $value ) . "')";
						}
						
						$tags = implode( ", ", $tags );
						$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
					
					}
				}
			
			} else {
				
				$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '$item_db[0]'" );
				$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '$item_db[0]'" );

				$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num-1 where name='$item_db[1]'" );
				
				$okdeleted = TRUE;

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '26', '{$item_db[4]}')" );

				
				$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images where news_id = '$item_db[0]'" );
				
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
				
				$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '$item_db[0]'" );
				
				$db->query( "SELECT id, onserver FROM " . PREFIX . "_files WHERE news_id = '$item_db[0]'" );
				
				while ( $row = $db->get_row() ) {
					
					@unlink( ROOT_DIR . "/uploads/files/" . $row['onserver'] );
				
				}
				
				$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '$item_db[0]'" );
			
			}
		} else
			$no_permission = TRUE;
	
	}

	clear_cache( array('news_', 'full_'.$item_db[0], 'comm_'.$item_db[0], 'tagscloud_', 'archives_', 'calendar_', 'rss') );

	if( ! $_SESSION['admin_referrer'] ) {
		
		$_SESSION['admin_referrer'] = "?mod=editnews&amp;action=list";
	
	}
	
	if( $no_permission ) {
		msg( "error", $lang['addnews_error'], $lang['edit_denied'], $_SESSION['admin_referrer'] );
	} elseif( $okdeleted ) {
		msg( "info", $lang['edit_delok'], $lang['edit_delok_1'], $_SESSION['admin_referrer'] );
	} elseif( $okchanges ) {
		msg( "info", $lang['edit_alleok'], $lang['edit_alleok_1'], $_SESSION['admin_referrer'] );
	} else {
		msg( "error", $lang['addnews_error'], $lang['edit_allerr'], $_SESSION['admin_referrer'] );
	}
}
?>