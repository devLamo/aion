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
 Файл: categories.php
-----------------------------------------------------
 Назначение: управление категориями
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

$result = "";
$catid = intval( $_REQUEST['catid'] );

if( ! $user_group[$member_id['user_group']]['admin_categories'] ) {
	msg( "error", $lang['index_denied'], $lang['cat_perm'] );
}

function get_sub_cats($id, $subcategory = false) {
	
	global $cat_info;
	$subfound = array ();
	
	if( ! $subcategory ) {
		$subcategory = array ();
		$subcategory[] = $id;
	}
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		$subcategory[] = $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
	}
	
	return $subcategory;

}

function makeDropDown($options, $name, $selected) {
	$output = "<select size=1 name=\"$name\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"$value\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function SelectSkin($skin) {
	global $lang;
	
	$templates_list = array ();
	
	$handle = opendir( ROOT_DIR . '/templates' );
	
	while ( false !== ($file = readdir( $handle )) ) {
		if( is_dir( ROOT_DIR . "/templates/$file" ) and ($file != "." and $file != ".." and $file != "smartphone") ) {
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
// ********************************************************************************
// Сортировка категорий
// ********************************************************************************


if( $_REQUEST['action'] == 'sort' ) {
	
	foreach ( $_POST["posi"] as $id => $posi ) {
		if( $posi != "" ) {
			$posi = intval( $posi );
			$id = intval( $id );
			$db->query( "UPDATE " . PREFIX . "_category SET posi='{$posi}' WHERE id = '{$id}'" );
		}
	}
	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '11', '')" );

	header( "Location:$PHP_SELF?mod=categories" );
}

// ********************************************************************************
// Добавление категории
// ********************************************************************************
if( $action == "add" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r" );

	if( $_POST['cat_icon'] == $lang['cat_icon'] ) {
		$_POST['cat_icon'] = "";
	}
	
	$cat_name  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_name'] ) ), ENT_QUOTES) );
	$skin_name = trim( totranslit($_POST['skin_name'], false, false) );
	$cat_icon  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_icon']) ), ENT_QUOTES) );
	
	$alt_cat_name = totranslit( stripslashes( $_POST['alt_cat_name'] ), true, false );
	
	if( ! $cat_name ) {
		msg( "error", $lang['cat_error'], $lang['cat_ername'], "javascript:history.go(-1)" );
	}
	if( ! $alt_cat_name ) {
		msg( "error", $lang['cat_error'], $lang['cat_erurl'], "javascript:history.go(-1)" );
	}
	
	if ( in_array($_POST['news_sort'], array("date", "rating", "news_read", "title")) )	{

		$news_sort = $db->safesql( $_POST['news_sort'] );

	} else $news_sort = "";

	if ( in_array($_POST['news_msort'], array("ASC", "DESC")) )	{

		$news_msort = $db->safesql( $_POST['news_msort'] );

	} else $news_msort = "";

	if ( $_POST['news_number'] > 0)
		$news_number = intval( $_POST['news_number'] );
	else $news_number = 0;

	if ( $_POST['category'] > 0)
		$category = intval( $_POST['category'] );
	else $category = 0;
	
	$short_tpl = totranslit( stripslashes( trim( $_POST['short_tpl'] ) ) );
	$full_tpl = totranslit( stripslashes( trim( $_POST['full_tpl'] ) ) );
	
	$meta_title = $db->safesql( htmlspecialchars ( strip_tags( stripslashes( $_POST['meta_title'] ) ) ) );
	$description = $db->safesql( dle_substr( strip_tags( stripslashes( $_POST['descr'] ) ), 0, 200, $config['charset'] ) );
	$keywords = $db->safesql( str_replace( $quotes, " ", strip_tags( stripslashes( $_POST['keywords'] ) ) ) );
	
	$row = $db->super_query( "SELECT alt_name FROM " . PREFIX . "_category WHERE alt_name ='{$alt_cat_name}'" );
	
	if( $row['alt_name'] ) {
		msg( "error", $lang['cat_error'], $lang['cat_eradd'], "?mod=categories" );
	}
	
	$db->query( "INSERT INTO " . PREFIX . "_category (parentid, name, alt_name, icon, skin, descr, keywords, news_sort, news_msort, news_number, short_tpl, full_tpl, metatitle) values ('$category', '$cat_name', '$alt_cat_name', '$cat_icon', '$skin_name', '$description', '$keywords', '$news_sort', '$news_msort', '$news_number', '$short_tpl', '$full_tpl', '$meta_title')" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '12', '{$cat_name}')" );

	
	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	clear_cache();
	
	msg( "info", $lang['cat_addok'], $lang['cat_addok_1'], "?mod=categories" );

} 
// ********************************************************************************
// Удаление категории
// ********************************************************************************
elseif( $action == "remove" ) {
	

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	function DeleteSubcategories($parentid) {
		global $db;
		
		$subcategories = $db->query( "SELECT id FROM " . PREFIX . "_category WHERE parentid = '$parentid'" );
		
		while ( $subcategory = $db->get_row( $subcategories ) ) {
			DeleteSubcategories( $subcategory['id'] );
			
			$db->query( "DELETE FROM " . PREFIX . "_category WHERE id = '" . $subcategory['id'] . "'" );
		}
	}
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "$PHP_SELF?mod=categories" );
	}
	
	$row = $db->super_query( "SELECT count(*) as count FROM " . PREFIX . "_post WHERE category regexp '[[:<:]]($catid)[[:>:]]'" );
	
	if( $row['count'] ) {
		
		if( is_array( $_REQUEST['new_category'] ) ) {
			if( ! in_array( $catid, $new_category ) ) {
				
				$category_list = $db->safesql( htmlspecialchars( strip_tags( stripslashes( implode( ',', $_REQUEST['new_category']))), ENT_QUOTES ) );
				
				$db->query( "UPDATE " . PREFIX . "_post set category='$category_list' WHERE category regexp '[[:<:]]($catid)[[:>:]]'" );
				
				$db->query( "DELETE FROM " . PREFIX . "_category WHERE id='$catid'" );
				
				DeleteSubcategories( $catid );
				
				@unlink( ENGINE_DIR . '/cache/system/category.php' );
				
				clear_cache();

				$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '13', '{$catid}')" );

				
				msg( "info", $lang['cat_delok'], $lang['cat_delok_1'], "?mod=categories" );
			}
		}
		
		msg( "info", $lang['all_info'], "<form action=\"\" method=\"post\">{$lang['comm_move']} <select name=\"new_category[]\" class=\"cat_select\" align=\"absmiddle\" multiple>" . CategoryNewsSelection( 0, 0 ) . "</select> <input class=\"edit\" type=\"submit\" value=\"{$lang['b_start']}\"></form>", "$PHP_SELF?mod=categories" );
	
	} else {
		
		$db->query( "DELETE FROM " . PREFIX . "_category WHERE id='$catid'" );
		
		DeleteSubcategories( $catid );
		
		@unlink( ENGINE_DIR . '/cache/system/category.php' );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '13', '{$catid}')" );
		
		clear_cache();
		
		msg( "info", $lang['cat_delok'], $lang['cat_delok_1'], "?mod=categories" );
	}
} 
// ********************************************************************************
// Редактирование категории
// ********************************************************************************
elseif( $action == "edit" ) {
	
	$catid = intval( $_GET['catid'] );
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "$PHP_SELF?mod=categories" );
	}
	
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_category WHERE id = '$catid'" );
	
	if( ! $row['id'] ) msg( "error", $lang['cat_error'], $lang['cat_noid'], "$PHP_SELF?mod=categories" );

	echoheader( "options", $lang['cat_head'] );
	
	$categorylist = CategoryNewsSelection( $row['parentid'], 0 );
	$skinlist = SelectSkin( $row['skin'] );
	
	$row['name'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['name'] ) );
	$row['metatitle'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['metatitle'] ) );
	$row['descr'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['descr'] ) );
	$row['keywords'] = stripslashes( preg_replace( array ("'\"'", "'\''" ), array ("&quot;", "&#039;" ), $row['keywords'] ) );
	
	$row['news_sort'] = makeDropDown( array ("" => $lang['sys_global'], "date" => $lang['opt_sys_sdate'], "rating" => $lang['opt_sys_srate'], "news_read" => $lang['opt_sys_sview'], "title" => $lang['opt_sys_salph'] ), "news_sort", $row['news_sort'] );
	$row['news_msort'] = makeDropDown( array ("" => $lang['sys_global'], "DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "news_msort", $row['news_msort'] );
	
	echo <<<HTML
<form method="post" action="">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['cat_edit']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="260" style="padding:4px;">{$lang['cat_name']}</td>
        <td><input class="edit bk" value="{$row['name']}" type="text" name="cat_name"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_catname]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_url']}</td>
        <td><input class="edit bk" value="{$row['alt_name']}" type="text" name="alt_cat_name"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_cataltname]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_addicon']}</td>
        <td><input class="edit bk" value="{$row['icon']}" type="text" name="cat_icon"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_caticon]}', this, event, '250px')">[?]</a></td>
    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_title']}</td>
	        <td><input type="text" name="meta_title" style="width:345px;" value="{$row['metatitle']}" class="edit bk"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_descr_cat']}</td>
	        <td><input type="text" name="descr" style="width:345px;" value="{$row['descr']}" class="edit bk"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_keys']}</td>
	        <td><textarea name="keywords" style="width:351px;height:100px;" class="bk">{$row['keywords']}</textarea></td>
	    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_parent']}</td>
        <td><select name="parentid" >{$categorylist}</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_skin']}</td>
        <td>{$skinlist}<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_cattempl]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_sort']}</td>
        <td>{$row['news_sort']}</td>
    </tr>
        <td style="padding:4px;">{$lang['opt_sys_msort']}</td>
        <td>{$row['news_msort']}</td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_newc']}</td>
        <td><input class="edit bk" type="text" name="news_number" value="{$row['news_number']}"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_news_number]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_s_tpl']}</td>
        <td><input class="edit bk" type="text" name="short_tpl" value="{$row['short_tpl']}">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[cat_s_tpl_hit]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_f_tpl']}</td>
        <td><input class="edit bk" type="text" name="full_tpl" value="{$row['full_tpl']}">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[cat_f_tpl_hit]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:4px;">&nbsp;</td>
        <td><input type="submit" class="btn btn-success" value="&nbsp;&nbsp;{$lang['vote_edit']}&nbsp;&nbsp;">
  <input type=hidden name=action value=doedit>
  <input type="hidden" name="user_hash" value="$dle_login_hash" />
  <input type=hidden name=catid value=$row[id]>
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
	die();

} 
// ********************************************************************************
// Запись отредактированной категории
// ********************************************************************************
elseif( $action == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", '"' );

	$cat_name  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_name'] ) ), ENT_QUOTES) );
	$skin_name = trim( totranslit($_POST['skin_name'], false, false) );
	$cat_icon  = $db->safesql(  htmlspecialchars( strip_tags( stripslashes($_POST['cat_icon']) ), ENT_QUOTES) );
	$alt_cat_name = totranslit( stripslashes( $_POST['alt_cat_name'] ), true, false );

		
	$catid = intval( $_POST['catid'] );
	$parentid = intval( $_POST['parentid'] );

	$meta_title = $db->safesql( htmlspecialchars ( strip_tags( stripslashes( $_POST['meta_title'] ) ) ) );
	$description = $db->safesql( dle_substr( strip_tags( stripslashes( $_POST['descr'] ) ), 0, 200, $config['charset'] ) );
	$keywords = $db->safesql( str_replace( $quotes, " ", strip_tags( stripslashes( $_POST['keywords'] ) ) ) );
	
	$short_tpl = totranslit( stripslashes( trim( $_POST['short_tpl'] ) ) );
	$full_tpl = totranslit( stripslashes( trim( $_POST['full_tpl'] ) ) );

	if ( in_array($_POST['news_sort'], array("date", "rating", "news_read", "title")) )	{

		$news_sort = $db->safesql( $_POST['news_sort'] );

	} else $news_sort = "";

	if ( in_array($_POST['news_msort'], array("ASC", "DESC")) )	{

		$news_msort = $db->safesql( $_POST['news_msort'] );

	} else $news_msort = "";

	if ( $_POST['news_number'] > 0)
		$news_number = intval( $_POST['news_number'] );
	else $news_number = 0;
	
	if( ! $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_noid'], "$PHP_SELF?mod=categories" );
	}
	if( $cat_name == "" ) {
		msg( "error", $lang['cat_error'], $lang['cat_noname'], "javascript:history.go(-1)" );
	}
	
	$row = $db->super_query( "SELECT id, alt_name FROM " . PREFIX . "_category WHERE alt_name = '$alt_cat_name'" );
	
	if( $row['id'] and $row['id'] != $catid ) {
		msg( "error", $lang['cat_error'], $lang['cat_eradd'], "javascript:history.go(-1)" );
	}
	
	if( in_array( $parentid, get_sub_cats( $catid ) ) ) {
		msg( "error", $lang['cat_error'], $lang['cat_noparentid'], "$PHP_SELF?mod=categories" );
	}
	
	$db->query( "UPDATE " . PREFIX . "_category set parentid='$parentid', name='$cat_name', alt_name='$alt_cat_name', icon='$cat_icon', skin='$skin_name', descr='$description', keywords='$keywords', news_sort='$news_sort', news_msort='$news_msort', news_number='$news_number', short_tpl='$short_tpl', full_tpl='$full_tpl', metatitle='$meta_title' WHERE id='$catid'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '14', '{$cat_name}')" );
	
	@unlink( ENGINE_DIR . '/cache/system/category.php' );
	clear_cache();
	
	msg( "info", $lang['cat_editok'], $lang['cat_editok_1'], "$PHP_SELF?mod=categories" );
}
// ********************************************************************************
// List all Categories
// ********************************************************************************
echoheader( "options", $lang['cat_head'] );

$categorylist = CategoryNewsSelection( 0, 0 );
$skinlist = SelectSkin( '' );

echo <<<HTML
<form method="post" action="">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['cat_add']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="260" style="padding:4px;">{$lang['cat_name']}</td>
        <td><input class="edit bk" type="text" name="cat_name"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_catname]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_url']}</td>
        <td><input class="edit bk" type="text" name="alt_cat_name"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_cataltname]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_addicon']}</td>
        <td><input class="edit bk" onFocus="this.select()" value="$lang[cat_icon]" type="text" name="cat_icon"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_caticon]}', this, event, '250px')">[?]</a></td>
    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_title']}</td>
	        <td><input type="text" name="meta_title" style="width:345px;"  class="edit bk"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_descr_cat']}</td>
	        <td><input type="text" name="descr"  style="width:345px;"  class="edit bk"> ({$lang['meta_descr_max']})</td>
	    </tr>
	    <tr>
	        <td style="padding:4px;">{$lang['meta_keys']}</td>
	        <td><textarea name="keywords" style="width:351px;height:100px;" class="bk"></textarea></td>
	    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_parent']}</td>
        <td><select name="category" >{$categorylist}</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_skin']}</td>
        <td>{$skinlist}<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_cattempl]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_sort']}</td>
        <td><select size=1 name="news_sort">
<option value="" selected >{$lang['sys_global']}</option>
<option value="date">{$lang['opt_sys_sdate']}</option>
<option value="rating">{$lang['opt_sys_srate']}</option>
<option value="news_read">{$lang['opt_sys_sview']}</option>
<option value="title">{$lang['opt_sys_salph']}</option>
</select></td>
    </tr>
        <td style="padding:4px;">{$lang['opt_sys_msort']}</td>
        <td><select size=1 name="news_msort">
<option value="" selected >{$lang['sys_global']}</option>
<option value="DESC">{$lang['opt_sys_mminus']}</option>
<option value="ASC">{$lang['opt_sys_mplus']}</option>
</select></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['opt_sys_newc']}</td>
        <td><input class="edit bk" type="text" name="news_number"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_news_number]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_s_tpl']}</td>
        <td><input class="edit bk" type="text" name="short_tpl">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[cat_s_tpl_hit]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['cat_f_tpl']}</td>
        <td><input class="edit bk" type="text" name="full_tpl">.tpl<a href="#" class="hintanchor" onMouseover="showhint('{$lang[cat_f_tpl_hit]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:4px;">&nbsp;</td>
        <td><input type="submit" class="btn btn-success" value="&nbsp;&nbsp;{$lang['vote_new']}&nbsp;&nbsp;">
<input type=hidden name=mod value=categories>
<input type="hidden" name="user_hash" value="$dle_login_hash" />
<input type=hidden name=action value=add></td>
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

if( ! count( $cat_info ) ) {
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['cat_list']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td height="100" align="center">{$lang['cat_nocat']}</td>
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
	
	function DisplayCategories($parentid = 0, $sublevelmarker = '') {
		global $lang, $cat_info, $config, $dle_login_hash;
		
		// start table
		if( $parentid == 0 ) {
			
			echo <<<HTML
<form method="post" action="">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['cat_list']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">ID</td>
        <td style="padding:2px;">Position</td>
        <td>{$lang['cat_cat']}</td>
        <td>{$lang['cat_url']}</td>
        <td>{$lang['cat_addicon']}</td>
        <td>{$lang['cat_skin_t']}</td>
        <td width="120">{$lang['cat_action']}</td>
    </tr>
    <tr>
        <td colspan="7"><div class="hr_line"></div></td>
    </tr>
HTML;
		
		} else {
			$sublevelmarker .= '--';
		}
		
		if( count( $cat_info ) ) {
			
			foreach ( $cat_info as $cats ) {
				if( $cats['parentid'] == $parentid ) $root_category[] = $cats['id'];
			}
			
			if( count( $root_category ) ) {
				
				foreach ( $root_category as $id ) {
					
					$category_name = $cat[$id];
					
					if( $config['allow_alt_url'] == "yes" ) $link = "<a class=\"list\" href=\"" . $config['http_home_url'] . get_url( $id ) . "/\" target=\"_blank\">" . stripslashes( $cat_info[$id]['name'] ) . "</a>";
					else $link = "<a class=\"list\" href=\"{$config['http_home_url']}index.php?do=cat&category=" . $cat_info[$id]['alt_name'] . "\" target=\"_blank\">" . stripslashes( $cat_info[$id]['name'] ) . "</a>";
					
					echo "<tr>
						<td height=\"14\">&nbsp;<b>" . $cat_info[$id]['id'] . "</b></td>
						<td height=\"20\"><input class=\"edit\" type=\"text\" size=\"5\" name=\"posi[{$cat_info[$id]['id']}]\" maxlength=\"5\" value=\"{$cat_info[$id]['posi']}\"></td>
						<td>&nbsp;$sublevelmarker&nbsp;" . $link . "</td>
						<td>";
					if( $cat_info[$id]['alt_name'] != "" ) {
						echo $cat_info[$id]['alt_name'];
					} else {
						echo "---";
					}
					echo "</td><td>";
					if( $cat_info[$id]['icon'] != "" ) {
						echo "<img border=0 src=\"" . $cat_info[$id]['icon'] . "\" height=40 width=40 alt=\"" . $cat_info[$id]['icon'] . "\">";
					} else {
						echo "---";
					}
					echo "</td><td>";
					if( $cat_info[$id]['skin'] != "" ) {
						echo $cat_info[$id]['skin'];
					} else {
						echo "---";
					}
					echo "</td>
						<td class=\"list\"><nobr>[<a href=\"?mod=categories&action=edit&catid=" . $cat_info[$id]['id'] . "\">$lang[cat_ed]</a>] [<a class=maintitle href=\"?mod=categories&user_hash=" . $dle_login_hash . "&action=remove&catid=" . $cat_info[$id]['id'] . "\">$lang[cat_del]</a>]</nobr></td>
						</tr>
						<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=7></td></tr>";
					
					DisplayCategories( $id, $sublevelmarker );
				}
			}
		}
		
		// end table
		if( $parentid == 0 ) {
			
			echo <<<HTML
    <tr>
        <td colspan="7" style="padding:5px;"><input type=hidden name=action value=sort><input type="submit" id="posi" class="btn btn-primary" value="$lang[cat_posi]" /></td>
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
		
		}
	}
	
	DisplayCategories();
}

echofooter();
?>