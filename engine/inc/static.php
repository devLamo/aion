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
 Файл: static.php
-----------------------------------------------------
 Назначение: редактирование статистических страниц
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_static'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( Array (), Array (), 1, 1 );

function SelectSkin($skin) {
	global $lang;
	
	$templates_list = array ();
	
	$handle = opendir( './templates' );
	
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( "./templates/$file" ) and ($file != "." and $file != "..") ) {
			$templates_list[] = $file;
		}
	}
	closedir( $handle );
	
	$skin_list = "<select name=skin_name>";
	$skin_list .= "<option value=\"\">" . $lang['cat_skin_sel'] . "</option>";
	
	foreach ( $templates_list as $single_template ) {
		if( $single_template == $skin ) $selected = " selected";
		else $selected = "";
		$skin_list .= "<option value=\"$single_template\"" . $selected . ">$single_template</option>";
	}
	$skin_list .= '</select>';
	
	return $skin_list;
}

if( !$action ) $action = "list";

if( $action == "list" ) {
	$_SESSION['admin_referrer'] = $_SERVER['REQUEST_URI'];

	$js_array[] = "engine/skins/calendar.js";

	echoheader( "static", "static" );
	
	$search_field = $db->safesql( trim( htmlspecialchars( stripslashes( @urldecode( $_GET['search_field'] ) ), ENT_QUOTES ) ) );
	if ($_GET['fromnewsdate']) $fromnewsdate = strtotime( $_GET['fromnewsdate'] ); else $fromnewsdate = "";
	if ($_GET['tonewsdate']) $tonewsdate = strtotime( $_GET['tonewsdate'] ); else $tonewsdate = "";


	if ($fromnewsdate === -1 OR !$fromnewsdate) $fromnewsdate = "";
	if ($tonewsdate === -1 OR !$tonewsdate)   $tonewsdate = "";
	
	$start_from = intval( $_GET['start_from'] );
	$news_per_page = intval( $_GET['news_per_page'] );
	$gopage = intval( $_REQUEST['gopage'] );

	if( ! $news_per_page or $news_per_page < 1 ) {
		$news_per_page = 50;
	}
	if( $gopage ) $start_from = ($gopage - 1) * $news_per_page;
	
	if( $start_from < 0 ) $start_from = 0;

	$where = array ();
	$where[] = "name != 'dle-rules-page'";
	
	if( $search_field != "" ) {
		
		$where[] = "(template like '%$search_field%' OR descr like '%$search_field%')";
	
	}
	
	if( $fromnewsdate != "" ) {
		
		$where[] = "date >= '$fromnewsdate'";
	
	}
	
	if( $tonewsdate != "" ) {
		
		$where[] = "date <= '$tonewsdate'";
	
	}
	
	if( count( $where ) ) {
		
		$where = implode( " AND ", $where );
		$where = " WHERE " . $where;
	
	} else {
		$where = "";
	}
	
	$order_by = array ();
	
	if( $_REQUEST['search_order_t'] == "asc" or $_REQUEST['search_order_t'] == "desc" ) $search_order_t = $_REQUEST['search_order_t'];
	else $search_order_t = "";
	if( $_REQUEST['search_order_d'] == "asc" or $_REQUEST['search_order_d'] == "desc" ) $search_order_d = $_REQUEST['search_order_d'];
	else $search_order_d = "";
	
	if( ! empty( $search_order_t ) ) {
		$order_by[] = "name $search_order_t";
	}
	if( ! empty( $search_order_d ) ) {
		$order_by[] = "date $search_order_d";
	}
	
	$order_by = implode( ", ", $order_by );
	if( ! $order_by ) $order_by = "date desc";
	
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

	$db->query( "SELECT id, name, descr, template, views, date FROM " . PREFIX . "_static" . $where . " ORDER BY " . $order_by . " LIMIT $start_from,$news_per_page" );
	
	// Prelist Entries

	if( $start_from == "0" ) {
		$start_from = "";
	}
	$i = $start_from;
	$entries_showed = 0;
	
	$entries = "";
	
	while ( $row = $db->get_array() ) {

		$i ++;
		
		$itemdate = @date( "d.m.Y H:i", $row['date'] );
		
		$title = htmlspecialchars( stripslashes( $row['name'] ), ENT_QUOTES );
		$descr = stripslashes($row['descr']);
		if( $config['allow_alt_url'] == "yes" ) $vlink = $config['http_home_url'] . $row['name'] . ".html";
		else $vlink = $config['http_home_url'] . "index.php?do=static&page=" . $row['name'];

		$entries .= "<tr>

        <td class=\"list\" style=\"padding:4px;\" nowrap>
        $itemdate - <a title=\"{$lang[static_view]}\" class=\"list\" href=\"{$vlink}\" target=\"_blank\">$title</a></td>
        <td align=left><a title=\"{$lang[edit_static_act]}\" class=\"list\" href=\"$PHP_SELF?mod=static&action=doedit&id={$row['id']}\">$descr</a></td>
        <td align=center>{$row['views']}</td>
        <td align=center><input name=\"selected_news[]\" value=\"{$row['id']}\" type='checkbox' /></td>
        </tr>
        <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

		$entries_showed ++;
		
		if( $i >= $news_per_page + $start_from ) {
			break;
		}
	}
	
	// End prelisting
	$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static" . $where );
	
	$all_count_news = $result_count['count'];
	if ( $fromnewsdate ) $fromnewsdate = date("Y-m-d", $fromnewsdate );
	if ( $tonewsdate ) $tonewsdate = date("Y-m-d", $tonewsdate );

	
	///////////////////////////////////////////
	// Options Bar
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
<form action="?mod=static&amp;action=list" method="GET" name="optionsbar" id="optionsbar">
<input type="hidden" name="mod" value="static">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['edit_stat']}&nbsp;<b>{$entries_showed}</b>&nbsp;&nbsp;&nbsp;{$lang['edit_stat_1']}&nbsp;<b>{$all_count_news}</b></div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
     <tr>
		<td style="padding:5px;">{$lang['edit_search_static']}</td>
		<td style="padding-left:5px;"><input class="edit bk" name="search_field" value="{$search_field}" type="text" size="35"></td>
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
		<td style="padding:5px;">{$lang['static_per_page']}</td>
		<td style="padding-left:5px;"><input class="edit bk" style="text-align: center" name="news_per_page" value="{$news_per_page}" type="text" size="10"></td>
    <td colspan="2"></td>

    </tr>
    <tr>
        <td colspan="4"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td colspan="4">{$lang['static_order']}</td>
    </tr>
    <tr>
        <td style="padding:5px;">{$lang['edit_et']}</td>
        <td style="padding-left:5px;"><select name="search_order_t" id="search_order_t">
           <option {$search_order_title['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_title['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_title['desc']} value="desc">{$lang['user_order_minus']}</option>
            </select>
        </td>
        <td style="padding-left:5px;">{$lang['search_by_date']}</td>
        <td style="padding-left:5px;"><select name="search_order_d" id="search_order_d">
           <option {$search_order_date['----']} value="">{$lang['user_order_no']}</option>
           <option {$search_order_date['asc']} value="asc">{$lang['user_order_plus']}</option>
           <option {$search_order_date['desc']} value="desc">{$lang['user_order_minus']}</option>
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
<input onClick="javascript:search_submit(0); return(false);" class="btn btn-primary" type="submit" value="{$lang['edit_act_1']}">
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
</div>
</form>
HTML;
	// End Options Bar
	

	echo <<<JSCRIPT
<script language='JavaScript' type="text/javascript">
<!--
function ckeck_uncheck_all() {
    var frm = document.static;
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['static_head']}</div></td>
        <td bgcolor="#EFEFEF" height="29" style="padding:5px;" align="right"><a href="javascript:ShowOrHide('advancedsearch');">{$lang['static_advanced_search']}</a></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="height:50px;"><br><br><center>{$lang['edit_nostatic']}</center>
<br><br>&nbsp;&nbsp;&nbsp;<input type="button" value="{$lang['static_new']}" class="btn btn-primary" onclick="document.location='$PHP_SELF?mod=static&action=addnew'"></td>
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
<form action="" method="post" name="static">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['static_head']}</div></td>
        <td bgcolor="#EFEFEF" height="29" style="padding:5px;" align="right"><a href="javascript:ShowOrHide('advancedsearch');">{$lang['static_advanced_search']}</a></td>
    </tr>
</table>
<div class="unterline"></div>
<table width=100% id="staticlist">
	<tr class="thead">
    <th width="350" style="padding:2px;">{$lang['static_title']}</th>
    <th>{$lang['static_descr']}</th>
    <th width="100" align="center"><div style="text-align: center;">&nbsp;{$lang['st_views']}&nbsp;</div></th>
    <th width="10" align="center"><div style="text-align: center;"><input type="checkbox" name="master_box" title="{$lang['edit_selall']}" onclick="javascript:ckeck_uncheck_all()"></div></th>
	</tr>
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></td></th>
	{$entries}
	<tr class="tfoot"><th colspan="4"><div class="hr_line"></div></td></th>
</table>
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
<table width=100%>
<tr><td>{$npp_nav}</td>
<td align="right" valign="top"><div style="margin-bottom:5px; margin-top:5px;">
<select name="action">
<option value="">{$lang['edit_selact']}</option>
<option value="mass_date">{$lang['mass_edit_date']}</option>
<option value="mass_clear_count">{$lang['mass_clear_count']}</option>
<option value="mass_delete">{$lang['edit_seldel']}</option>
</select>
<input type="hidden" name="mod" value="mass_static_actions">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<input class="btn btn-warning btn-mini" type="submit" value="{$lang['b_start']}">
</div><td></tr>
HTML;
			
			if( $all_count_news > $news_per_page ) {
				
				echo <<<HTML
<tr><td colspan="2">
{$lang['edit_go_page']} <input class="edit bk" style="text-align: center" name="gopage" id="gopage" value="" type="text" size="3"> <input onClick="javascript:gopage_submit(document.getElementById('gopage').value); return(false);" class="btn btn-primary btn-mini" type="button" value=" ok ">
</td></tr>
HTML;
			
			}
		
		}
		
		echo <<<HTML
<tr><td colspan="2">
&nbsp;&nbsp;&nbsp;<input type="button" value="&nbsp;&nbsp;{$lang['static_new']}&nbsp;&nbsp;" class="btn btn-primary" onclick="document.location='$PHP_SELF?mod=static&action=addnew'">
</td></tr>
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

	$("#staticlist").delegate("tr", "hover", function(){
	  $(this).toggleClass("hoverRow");
	});

});
</script>
HTML;
	
	}
	
	echofooter();

} elseif( $action == "addnew" ) {

	$js_array[] = "engine/skins/calendar.js";

	echoheader( "static", "static" );
	
	echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function preview(){";
	
	if( $config['allow_static_wysiwyg'] == "yes" ) {
		echo "submit_all_data();";
	}
	
	echo "if(document.static.template.value == '' || document.static.description.value == '' || document.static.name.value == ''){ DLEalert('$lang[static_err_1]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.static.mod.value='preview';document.static.target='prv'
        document.static.submit(); dd.focus()
        setTimeout(\"document.static.mod.value='static';document.static.target='_self'\",500)
    }
    }
    onload=focus;function focus(){document.forms[0].name.focus();}

	function auto_keywords ( key )
	{

		var wysiwyg = '{$config['allow_static_wysiwyg']}';

		if (wysiwyg == \"yes\") {
			submit_all_data();
		}

		var short_txt = document.getElementById('template').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, key: key }, function(data){
	
			HideLoading('');
	
			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').val(data); }
	
		});

		return false;
	}
    </SCRIPT>";
	
	if( $config['allow_static_wysiwyg'] == "yes" ) echo "<form method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == '' || document.static.template.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\" action=\"\">";
	else echo "<form method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == '' || document.static.template.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\" action=\"\">";
	
	echo <<<HTML
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['static_a']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="150" style="padding:2px;">{$lang['static_title']}</td>
        <td style="padding:2px;"><input type="text" name="name" size="25"  class="edit bk"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_stitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['static_descr']}</td>
        <td style="padding:2px;"><input type="text" name="description" size="55"  class="edit bk"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_sdesc]}', this, event, '250px')">[?]</a></td>
    </tr>

    <tr>
        <td style="padding:2px;">{$lang['edit_edate']}</td>
        <td style="padding:2px;"><input type="text" name="newdate" id="f_date_c" size="20"  class="edit bk" value="">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<input type="checkbox" name="allow_now" id="allow_now" value="yes" checked>&nbsp;{$lang['edit_jdate']}
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

HTML;
	
	if( $config['allow_static_wysiwyg'] == "yes" ) {
		
		include (ENGINE_DIR . '/editor/static.php');
	
	} else {
		
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		
		echo <<<HTML
    <tr>
        <td style="padding:2px;">{$lang['static_templ']}</td>
        <td style="padding-left:2px;">{$bb_code}<textarea class="bk" style="width:98%; height:300px;" name="template" id="template"  onclick=setFieldName(this.name)></textarea><script type=text/javascript>var selField  = "template";</script></td>
    </tr>
HTML;
	
	}
	
	if( $config['allow_static_wysiwyg'] != "yes" ) $fix_br = "<input type=\"radio\" name=\"allow_br\" value=\"1\" checked=\"checked\" /> {$lang['static_br_html']}<br /><input type=\"radio\" name=\"allow_br\" value=\"0\" /> {$lang['static_br_html_1']}";
	else $fix_br = "<input type=\"radio\" name=\"allow_br\" value=\"0\" /> {$lang['static_br_html_1']}";

	if ($member_id['user_group'] == 1 ) $fix_br .= "<br /><input type=\"radio\" name=\"allow_br\" value=\"2\" /> {$lang['static_br_html_2']}";

	$groups = get_groups();
	$skinlist = SelectSkin( '' );
	
	echo <<<HTML
		<tr><td>{$lang['static_type']}</td><td>{$fix_br}</td></tr>
		<tr><td colspan="2"><div class="hr_line"></div></td></tr>
	    <tr>
	        <td>&nbsp;</td>
	        <td>{$lang['add_metatags']}<a href="#" class="hintanchor" onMouseover="showhint('{$lang['hint_metas']}', this, event, '220px')">[?]</a></td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_title']}</td>
	        <td><input type="text" name="meta_title" style="width:388px;" class="edit bk"></td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_descr']}</td>
	        <td><input type="text" name="descr" id="autodescr" style="width:388px;" class="edit bk"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_keys']}</td>
	        <td><textarea name="keywords" id='keywords' style="width:394px;height:70px;" class="bk"></textarea><br />
			<input onClick="auto_keywords(1)" type="button" class="btn" value="{$lang['btn_descr']}" style="width:170px;">&nbsp;
			<input onClick="auto_keywords(2)" type="button" class="btn" value="{$lang['btn_keyword']}" style="width:216px;">
            </td>
	    </tr>
		<tr><td colspan="2"><div class="hr_line"></div></td></tr>
    <tr>
        <td style="padding:2px;">{$lang['static_tpl']}</td>
        <td style="padding-left:2px;"><input type="text" name="static_tpl" size="20"  class="edit bk">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_stpl]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['static_skin']}</td>
        <td style="padding:2px;">{$skinlist}<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_static_skin]}', this, event, '250px')">[?]</a> <input type="checkbox" name="allow_template" value="1" checked> {$lang['st_al_templ']}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_allow']}</td>
        <td style="padding:2px;"><select name="grouplevel[]" style="width:150px;height:93px;" multiple><option value="all" selected>{$lang['edit_all']}</option>{$groups}</select></td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td style="padding:2px;"><input type="checkbox" name="allow_count" value="1" checked> {$lang['allow_count']}<br /><input type="checkbox" name="allow_sitemap" value="1" checked> {$lang['allow_sitemap']}<br /><input type="checkbox" name="disable_index" value="1">&nbsp;{$lang['add_disable_index']}</td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td><br /><br /><input type="submit" value="{$lang['user_save']}" class="btn btn-success" style="width:100px;">&nbsp;&nbsp;&nbsp;<input onClick="preview()" type="button" class="btn btn-info" value="{$lang['btn_preview']}" style="width:100px;">
	<input type=hidden name="action" value="dosavenew">
	<input type=hidden name="mod" value="static">
	<input type=hidden name="preview_mode" value="static" >
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
	<br><br></td>
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
HTML;
	
	echofooter();
} elseif( $action == "dosavenew" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$allow_br = intval( $_POST['allow_br'] );
	if ($member_id['user_group'] != 1 AND $allow_br > 1 ) $allow_br = 1;

	if ($allow_br == 2) {

		if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['template'] = stripslashes( $_POST['template'] );  

		$template = trim( addslashes( $_POST['template'] ) );

	} else {

		if ( $config['allow_static_wysiwyg'] == "yes" ) $parse->allow_code = false;

		$template = $parse->process( $_POST['template'] );
	
		if( $config['allow_static_wysiwyg'] == "yes" or $allow_br != '1' ) {
			$template = $parse->BB_Parse( $template );
		} else {
			$template = $parse->BB_Parse( $template, false );
		}

	}

	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;
	$metatags = create_metatags( $template );
	$name = trim( totranslit( $_POST['name'], true, false ) );
	$descr = trim( $db->safesql( htmlspecialchars( $_POST['description'] ) ) );
	$template = $db->safesql( $template );
	$tpl = trim( totranslit( $_POST['static_tpl'] ) );
	$skin_name =  trim( totranslit( $_POST['skin_name'], false, false ) );
	$newdate = $_POST['newdate'];
    if( isset( $_POST['allow_now'] ) ) $allow_now = $_POST['allow_now']; else $allow_now = "";
	
	if( ! count( $_POST['grouplevel'] ) ) $_POST['grouplevel'] = array ("all" );
	$grouplevel = $db->safesql( implode( ',', $_POST['grouplevel'] ) );
	
	$allow_template = intval( $_POST['allow_template'] );
	$allow_count = intval( $_POST['allow_count'] );
	$allow_sitemap = intval( $_POST['allow_sitemap'] );

  // Обработка даты и времени
	$added_time = time() + ($config['date_adjust'] * 60);
	$newsdate = strtotime( $newdate );

	if( ($allow_now == "yes") OR ($newsdate === - 1) OR !$newsdate) {
		$thistime = $added_time;
	} else {
		$thistime = $newsdate;
		if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) $thistime = $added_time;
	}
					
	if( $name == "" or $descr == "" or $template == "" ) msg( "error", $lang['static_err'], $lang['static_err_1'], "javascript:history.go(-1)" );

	$static_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE name='$name'" );

	if ($static_count['count']) msg( "error", $lang['static_err'], $lang['static_err_2'], "javascript:history.go(-1)" );
	
	$db->query( "INSERT INTO " . PREFIX . "_static (name, descr, template, allow_br, allow_template, grouplevel, tpl, metadescr, metakeys, template_folder, date, metatitle, allow_count, sitemap, disable_index) values ('$name', '$descr', '$template', '$allow_br', '$allow_template', '$grouplevel', '$tpl', '{$metatags['description']}', '{$metatags['keywords']}', '{$skin_name}', '{$thistime}', '{$metatags['title']}', '$allow_count', '$allow_sitemap', '$disable_index')" );
	$row = $db->insert_id();
	$db->query( "UPDATE " . PREFIX . "_static_files SET static_id='{$row}' WHERE author = '{$member_id['name']}' AND static_id = '0'" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '59', '{$name}')" );
	
	msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );

} elseif( $action == "doedit" ) {
	
	$id = intval( $_GET['id'] );
	
	if( $_GET['page'] == "rules" ) {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_static where name='dle-rules-page'" );
		$lang['static_edit'] = $lang['rules_edit'];
		if( ! $row['id'] ) {
			$id = "";
			$row['allow_template'] = "1";
		} else
			$id = $row['id'];
		
		if( ! $config['registration_rules'] ) $lang['rules_descr'] = $lang['rules_descr'] . " <font color=\"red\">" . $lang['rules_check'] . "</font>";
	
	} else {
		
		$row = $db->super_query( "SELECT * FROM " . PREFIX . "_static where id='$id'" );
	}

	if ($row['allow_br'] == 2) {

		if ($member_id['user_group'] != 1) msg( "error", $lang['index_denied'], $lang['static_not_allowed'] );

		$row['template'] = htmlspecialchars( stripslashes( $row['template'] ) );


	} else {
	
		if( $row['allow_br'] != '1' or $config['allow_static_wysiwyg'] == "yes" ) {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], true, $config['allow_static_wysiwyg'] );
		
		} else {
			
			$row['template'] = $parse->decodeBBCodes( $row['template'], false );
		
		}
	}
	
	$skinlist = SelectSkin( $row['template_folder'] );
	$row['descr'] = stripslashes($row['descr']);
	$row['metatitle'] = stripslashes( $row['metatitle'] );
	$itemdate = @date( "Y-m-d H:i", $row['date'] );

	$js_array[] = "engine/skins/calendar.js";
	
	echoheader( "static", "static" );
	
	echo <<<HTML
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
<script language="javascript">

function CheckStatus(Form){
	if(Form.allow_date.checked) {
		Form.allow_now.disabled = true;
		Form.allow_now.checked = false;
	} else {
		Form.allow_now.disabled = false;
	}
}

function confirmdelete(id) {
	    DLEconfirm( '{$lang['static_confirm']}', '{$lang['p_confirm']}', function () {
			document.location="{$PHP_SELF}?mod=static&action=dodelete&user_hash={$dle_login_hash}&id="+id;
		} );
}
</script>
HTML;

	echo "
    <SCRIPT LANGUAGE=\"JavaScript\">
    function preview(){";
	
	if( $config['allow_static_wysiwyg'] == "yes" ) {
		echo "submit_all_data();";
	}
	
	echo "if(document.static.template.value == ''){ DLEalert('$lang[static_err_1]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.static.mod.value='preview';document.static.target='prv'
        document.static.submit(); dd.focus()
        setTimeout(\"document.static.mod.value='static';document.static.target='_self'\",500)
    }
    }

	function auto_keywords ( key )
	{

		var wysiwyg = '{$config['allow_static_wysiwyg']}';

		if (wysiwyg == \"yes\") {
			submit_all_data();
		}

		var short_txt = document.getElementById('template').value;

		ShowLoading('');

		$.post(\"engine/ajax/keywords.php\", { short_txt: short_txt, key: key }, function(data){
	
			HideLoading('');
	
			if (key == 1) { $('#autodescr').val(data); }
			else { $('#keywords').val(data); }
	
		});

		return false;
	}
    </SCRIPT>";
	
	if( $_GET['page'] == "rules" ) {
		
		echo "<form method=post name=\"static\" id=\"static\" action=\"\">";
	
	} else {
		
		if( $config['allow_static_wysiwyg'] == "yes" ) echo "<form method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == '' || document.static.template.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\" action=\"\">";
		else echo "<form method=post name=\"static\" id=\"static\" onsubmit=\"if(document.static.name.value == '' || document.static.description.value == '' || document.static.template.value == ''){DLEalert('$lang[vote_alert]', '$lang[p_info]');return false}\" action=\"\">";
	
	}
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['static_edit']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;
	
	if( $_GET['page'] == "rules" ) {
		
		echo <<<HTML
    <tr>
        <td width="150" style="padding:2px;">{$lang['static_descr']}</td>
        <td style="padding:2px;" class="navigation">{$lang['rules_descr']}</td>
    </tr>
HTML;
	
	} else {
		
		echo <<<HTML
    <tr>
        <td width="150" style="padding:2px;">{$lang['static_title']}</td>
        <td style="padding:2px;"><input type="text" name="name" size="25"  class="edit bk" value="{$row['name']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_stitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['static_descr']}</td>
        <td style="padding:2px;"><input type="text" name="description" size="55"  class="edit bk" value="{$row['descr']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_sdesc]}', this, event, '250px')">[?]</a></td>
    </tr>
HTML;
	
	}
	
		echo <<<HTML
    <tr>
        <td style="padding:2px;">{$lang['edit_edate']}</td>
        <td style="padding:2px;"><input type="text" name="newdate" id="f_date_c" size="20"  class="edit bk" value="{$itemdate}">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<input type="checkbox" name="allow_date" id="allow_date" value="yes" onclick="CheckStatus(static)" checked>&nbsp;{$lang['edit_ndate']}&nbsp;<input type="checkbox" name="allow_now" id="allow_now" value="yes" disabled>&nbsp;{$lang['edit_jdate']}
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
HTML;


	if( $config['allow_static_wysiwyg'] == "yes" ) {
		
		include (ENGINE_DIR . '/editor/static.php');
	
	} else {
		
		include (ENGINE_DIR . '/inc/include/inserttag.php');
		
		echo <<<HTML
    <tr>
        <td style="padding:2px;">{$lang['static_templ']}</td>
        <td style="padding:2px;">{$bb_code}<textarea class="bk" style="width:98%; height:300px;" name="template" id="template"  onclick=setFieldName(this.name)>{$row['template']}</textarea><script type=text/javascript>var selField  = "template";</script></td>
    </tr>
HTML;
	
	}
	
	$check = array();

	$check[$row['allow_br']] = "checked=\"checked\"";

	if( $config['allow_static_wysiwyg'] != "yes" ) $fix_br = "<input type=\"radio\" name=\"allow_br\" value=\"1\" {$check[1]} /> {$lang['static_br_html']}<br /><input type=\"radio\" name=\"allow_br\" value=\"0\" {$check[0]} /> {$lang['static_br_html_1']}";
	else $fix_br = "<input type=\"radio\" name=\"allow_br\" value=\"0\" {$check[0]} /> {$lang['static_br_html_1']}";

	if ($member_id['user_group'] == 1 ) $fix_br .= "<br /><input type=\"radio\" name=\"allow_br\" value=\"2\" {$check[2]} /> {$lang['static_br_html_2']}";

	if( $row['allow_template'] ) $check_t = "checked";
	else $check_t = "";

	if( $row['allow_count'] ) $check_c = "checked";
	else $check_c = "";

	if( $_GET['page'] != "rules" ) {

		if( $row['sitemap'] ) $allow_sitemap = "<br /><input type=\"checkbox\" name=\"allow_sitemap\" value=\"1\" checked> {$lang['allow_sitemap']}";
		else $allow_sitemap = "<br /><input type=\"checkbox\" name=\"allow_sitemap\" value=\"1\"> {$lang['allow_sitemap']}";

		if( $row['disable_index'] ) $disable_index = "<br /><input type=\"checkbox\" name=\"disable_index\" value=\"1\" checked> {$lang['add_disable_index']}";
		else $disable_index = "<br /><input type=\"checkbox\" name=\"disable_index\" value=\"1\"> {$lang['add_disable_index']}";
	
	} else $allow_sitemap = "";


	$groups = get_groups( explode( ',', $row['grouplevel'] ) );
	if( $row['grouplevel'] == "all" ) $check_all = "selected";
	else $check_all = "";
	
	echo <<<HTML
		<tr><td>{$lang['static_type']}</td><td>{$fix_br}</td></tr>
		<tr><td colspan="2"><div class="hr_line"></div></td></tr>
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
	        <td><input type="text" name="descr" id="autodescr" style="width:388px;" class="edit bk" value="{$row['metadescr']}"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td height="29" style="padding-left:5px;">{$lang['meta_keys']}</td>
	        <td><textarea name="keywords" id='keywords' style="width:394px;height:70px;" class="bk">{$row['metakeys']}</textarea><br />
			<input onClick="auto_keywords(1)" type="button" class="btn" value="{$lang['btn_descr']}" style="width:170px;">&nbsp;
			<input onClick="auto_keywords(2)" type="button" class="btn" value="{$lang['btn_keyword']}" style="width:216px;">
            </td>
	    </tr>
		<tr><td colspan="2"><div class="hr_line"></div></td></tr>
    <tr>
        <td style="padding:2px;">{$lang['static_tpl']}</td>
        <td style="padding:2px;"><input type="text" name="static_tpl" size="20" value="{$row['tpl']}" class="edit bk">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_stpl]}', this, event, '250px')">[?]</a></td>
    </tr>
HTML;
	
	if( $_GET['page'] != "rules" ) echo <<<HTML
    <tr>
        <td style="padding:2px;">{$lang['static_skin']}</td>
        <td style="padding:2px;">{$skinlist}<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_static_skin]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_allow']}</td>
        <td style="padding:2px;"><select name="grouplevel[]" style="width:150px;height:93px;" multiple><option value="all" {$check_all}>{$lang['edit_all']}</option>{$groups}</select></td>
    </tr>
HTML;
	
	echo <<<HTML
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td style="padding-left:2px;"><input type="checkbox" name="allow_template" value="1" {$check_t}> {$lang['st_al_templ']}</td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td style="padding-left:2px;"><input type="checkbox" name="allow_count" value="1" {$check_c}> {$lang['allow_count']}{$allow_sitemap}{$disable_index}</td>
    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td><br>&nbsp;<input type="submit" value="{$lang['user_save']}" class="btn btn-success" style="width:100px;">&nbsp;&nbsp;&nbsp;<input onClick="preview()" type="button" class="btn btn-info" value="{$lang['btn_preview']}" style="width:100px;">&nbsp;&nbsp;&nbsp;<input onClick="confirmdelete('{$row['id']}'); return(false)" type="button" class="btn btn-danger" value="{$lang['edit_dnews']}" style="width:100px;">
	<input type="hidden" name="action" value="dosaveedit">
	<input type=hidden name="mod" value="static">
	<input type=hidden name="preview_mode" value="static" >
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
	<input type="hidden" name="static_date" value="{$row['date']}" />
	<input type="hidden" name="id" value="{$id}">
	<br><br></td>
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
HTML;
	
	echofooter();
} elseif( $action == "dosaveedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$allow_br = intval( $_POST['allow_br'] );
	if ($member_id['user_group'] != 1 AND $allow_br > 1 ) $allow_br = 1;

	if ($allow_br == 2) {

		if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) $_POST['template'] = stripslashes( $_POST['template'] );  

		$template = trim( addslashes( $_POST['template'] ) );

	} else {

		if ( $config['allow_static_wysiwyg'] == "yes" ) $parse->allow_code = false;

		$template = $parse->process( $_POST['template'] );
	
		if( $config['allow_static_wysiwyg'] == "yes" or $allow_br != '1' ) {
			$template = $parse->BB_Parse( $template );
		} else {
			$template = $parse->BB_Parse( $template, false );
		}

	}
	
	$metatags = create_metatags( $template );
	
	if( $_GET['page'] == "rules" ) {
		
		$name = "dle-rules-page";
		$descr = $lang['rules_edit'];
	
	} else {
		
		$name = trim( totranslit( $_POST['name'], true, false ) );
		$descr = trim( $db->safesql( htmlspecialchars( $_POST['description'] ) ) );
		
		if( ! count( $_POST['grouplevel'] ) ) $_POST['grouplevel'] = array ("all" );
		$grouplevel = $db->safesql( implode( ',', $_POST['grouplevel'] ) );
	
	}

	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;	
	$template = $db->safesql( $template );
	$allow_template = intval( $_POST['allow_template'] );
	$allow_count = intval( $_POST['allow_count'] );
	$allow_sitemap = intval( $_POST['allow_sitemap'] );
	$tpl = trim( totranslit( $_POST['static_tpl'] ) );
	$skin_name =  trim( totranslit( $_POST['skin_name'], false, false ) );
	$newdate = $_POST['newdate'];
	if( isset( $_POST['allow_date'] ) ) $allow_date = $_POST['allow_date']; else $allow_date = "";
	if( isset( $_POST['allow_now'] ) )  $allow_now = $_POST['allow_now']; else $allow_now = "";

	// Обработка даты и времени
	$added_time = time() + ($config['date_adjust'] * 60);
	$newsdate = strtotime( $newdate );

	if( $allow_date != "yes" ) {

		if( $allow_now == "yes" ) $thistime = $added_time;
		elseif( ($newsdate === - 1) OR !$newsdate ) {
				$thistime = $added_time;
		} else {

			$thistime = $newsdate;

			if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
				$thistime = $added_time;
			}

		}
					
	} else {
		$thistime = intval( $_POST['static_date'] );
	}
	
	if( $_GET['page'] == "rules" ) {
		
		if( $_POST['id'] ) {
			
			$db->query( "UPDATE " . PREFIX . "_static SET descr='$descr', template='$template', allow_br='$allow_br', allow_template='$allow_template', grouplevel='all', tpl='$tpl', metadescr='{$metatags['description']}', metakeys='{$metatags['keywords']}', template_folder='{$skin_name}', date='{$thistime}', metatitle='{$metatags['title']}', allow_count='{$allow_count}', sitemap='0', disable_index='0' WHERE name='dle-rules-page'" );

			$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '60', 'dle-rules-page')" );
		
		} else {
			
			$db->query( "INSERT INTO " . PREFIX . "_static (name, descr, template, allow_br, allow_template, grouplevel, tpl, metadescr, metakeys, template_folder, date, metatitle, allow_count, sitemap, disable_index) values ('$name', '$descr', '$template', '$allow_br', '$allow_template', 'all', '$tpl', '{$metatags['description']}', '{$metatags['keywords']}', '{$skin_name}', '{$thistime}', '{$metatags['title']}', '{$allow_count}', '0', '0')" );
			$row = $db->insert_id();
			$db->query( "UPDATE " . PREFIX . "_static_files SET static_id='{$row}' WHERE author = '{$member_id['name']}' AND static_id = '0'" );
		
		}
		
		msg( "info", $lang['rules_ok'], $lang['rules_ok'], "?mod=static&action=doedit&page=rules" );
	
	} else {
		
		$id = intval( $_GET['id'] );

		if( $name == "" or $descr == "" or $template == "" ) msg( "error", $lang['static_err'], $lang['static_err_1'], "javascript:history.go(-1)" );

		$static_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE name='$name' AND id != '$id'" );
	
		if ($static_count['count']) msg( "error", $lang['static_err'], $lang['static_err_2'], "javascript:history.go(-1)" );

		$db->query( "UPDATE " . PREFIX . "_static SET name='$name', descr='$descr', template='$template', allow_br='$allow_br', allow_template='$allow_template', grouplevel='$grouplevel', tpl='$tpl', metadescr='{$metatags['description']}', metakeys='{$metatags['keywords']}', template_folder='{$skin_name}', date='{$thistime}', metatitle='{$metatags['title']}', allow_count='{$allow_count}', sitemap='{$allow_sitemap}', disable_index='$disable_index' WHERE id='$id'" );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '60', '{$name}')" );
		
		msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );
	
	}
	
	msg( "info", $lang['static_addok'], $lang['static_addok_1'], "?mod=static" );

} elseif( $action == "dodelete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$id = intval( $_GET['id'] );
	
	$db->query( "DELETE FROM " . PREFIX . "_static WHERE id='$id'" );
	
	$db->query( "SELECT name, onserver FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );
	
	while ( $row = $db->get_row() ) {
		
		if( $row['onserver'] ) {
			
			@unlink( ROOT_DIR . "/uploads/files/" . $row['onserver'] );
		
		} else {
			
			$url_image = explode( "/", $row['name'] );
			
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
	
	$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );
	
	msg( "info", $lang['static_del'], $lang['static_del_1'], "$PHP_SELF?mod=static" );

}
?>