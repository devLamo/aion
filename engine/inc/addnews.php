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
 Файл: addnews.php
-----------------------------------------------------
 Назначение: Добавление новости
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_addnews'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if( $action == "addnews" ) {

	$id= "";

	$js_array[] = "engine/skins/calendar.js";
	$js_array[] = "engine/skins/tabs.js";
	$js_array[] = "engine/skins/autocomplete.js";
	$js_array[] = "engine/skins/chosen/chosen.js";

	echoheader( "addnews", $lang['addnews'] );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = "no";	
	
	$xfieldsaction = "categoryfilter";
	include (ENGINE_DIR . '/inc/xfields.php');
	echo $categoryfilter;
	

	echo "
    <script type=\"text/javascript\">
    function preview(){";
	
	if( $config['allow_admin_wysiwyg'] == "yes" ) {
		echo "submit_all_data();";
	}
	
	echo "if(document.addnews.title.value == ''){ DLEalert('$lang[addnews_alert]', '$lang[p_info]'); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=1,scrollbars=1')
        document.addnews.mod.value='preview';document.addnews.target='prv'
        document.addnews.submit();dd.focus()
        setTimeout(\"document.addnews.mod.value='addnews';document.addnews.target='_self'\",500)
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

	function find_relates ( )
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post('engine/ajax/find_relates.php', { title: title }, function(data){
	
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
    </script>";
	
	echo "<form method=post name=\"addnews\" id=\"addnews\" onsubmit=\"if(checkxf()=='fail') return false;\" action=\"$PHP_SELF\">";
	
	$categories_list = CategoryNewsSelection( 0, 0 );
	
	if( $config['allow_multi_category'] ) $category_multiple = "class=\"categoryselect\" multiple";
	else $category_multiple = "class=\"categoryselect\"";
	
	echo <<<HTML
<link rel="stylesheet" type="text/css" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['addnews_news']}</div></td>
    </tr>
</table>

<div class="unterline"></div>
<div id="dle_tabView1">

<div class="dle_aTab">

<table width="100%">
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_title']}</td>
        <td><input class="edit bk" type="text" style="width:350px;" name="title" id="title">&nbsp;&nbsp;<input class="btn btn-mini" type="button" onClick="find_relates(); return false;" style="width:160px;" value="{$lang['b_find_related']}"> <a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_title]}', this, event, '220px')">[?]</a><span id="related_news"></span></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['addnews_date']}</td>
        <td><input type="text" name="newdate" id="f_date_c" size="20"  class="edit bk" >
<img src="engine/skins/images/img.gif"  align="absmiddle" id="f_trigger_c" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;<input type="checkbox" name="allow_date" value="yes" checked>&nbsp;{$lang['edit_jdate']}<a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_calendar]}', this, event, '320px')">[?]</a>
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
        <td height="29" style="padding-left:5px;">{$lang['addnews_cat']}</td>
        <td><select data-placeholder="{$lang['addnews_cat_sel']}" name="category[]" id="category" onchange="onCategoryChange(this)" $category_multiple style="width:350px;">
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
        <td height="29" width="140" style="padding-left:5px;">{$lang['addnews_short']}<br /><input class=bbcodes style="width: 30px;" onclick="document.addnews.short_story.rows += 5;" type=button value=" + ">&nbsp;&nbsp;<input class=bbcodes style="width: 30px;" onclick="document.addnews.short_story.rows -= 5;" type=button value=" - "></td>
        <td>{$bb_code}<textarea rows="16" style="width:98%; padding:0px;" onclick="setFieldName(this.name)" name="short_story" id="short_story" class="bk"></textarea>
	</td></tr>
HTML;
	}
	
	if( $config['allow_admin_wysiwyg'] == "yes" ) {
		
		include (ENGINE_DIR . '/editor/fullnews.php');
	
	} else {
		
		echo <<<HTML
    <tr>
    <td height="29" style="padding-left:5px;">{$lang['addnews_full']}<br /><span class="navigation">({$lang['addnews_alt']})</span><br /><input class=bbcodes style="width: 30px;" onclick="document.addnews.full_story.rows += 5;" type=button value=" + ">&nbsp;&nbsp;<input class=bbcodes style="width: 30px;" onclick="document.addnews.full_story.rows -= 5;" type=button value=" - "></td>
    <td>{$bb_panel}<textarea rows="19" onclick="setFieldName(this.name)" name="full_story" id="full_story" style="width:98%;" class="bk"></textarea>
	</td></tr>
HTML;
	}
	
	// XFields Call
	$xfieldsaction = "list";
	$xfieldsadd = true;
	include (ENGINE_DIR . '/inc/xfields.php');
	// End XFields Call

	if( $config['allow_admin_wysiwyg'] != "yes" ) $output = str_replace("<!--panel-->", $bb_panel, $output);

	echo $output;
	
	if( $user_group[$member_id['user_group']]['allow_fixed'] and $config['allow_fixed'] ) $fix_input = "<input type=\"checkbox\" name=\"news_fixed\" value=\"1\">&nbsp;$lang[addnews_fix]"; else $fix_input = "&nbsp;";
	if( $user_group[$member_id['user_group']]['allow_main'] ) $main_input = "<input type=\"checkbox\" name=\"allow_main\" value=\"1\" checked>&nbsp;{$lang['addnews_main']}"; else $main_input = "&nbsp;";
	if($member_id['user_group'] < 3 ) $disable_index = "<input type=\"checkbox\" name=\"disable_index\" value=\"1\">&nbsp;{$lang['add_disable_index']}"; else $disable_index = "&nbsp;";

	if( $config['allow_admin_wysiwyg'] != "yes" ) $fix_br = "<input type=\"checkbox\" name=\"allow_br\" value=\"1\" checked>&nbsp;{$lang['allow_br']}";
	else $fix_br = "";
	
	echo <<<HTML
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['addnews_option']}</td>
        <td>
		<table>
			<tr>
				<td style="width:220px;"><input type="checkbox" name="approve" value="1" checked>&nbsp;{$lang['addnews_mod']}</td>
				<td style="width:200px;"><br /><br />&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>{$main_input}</td>
				<td><input type="checkbox" name="allow_comm" value="1" checked>&nbsp;{$lang['addnews_comm']}</td>
				<td>{$disable_index}</td>
			</tr>
			<tr>
				<td><input type="checkbox" name="allow_rating" value="1" checked>&nbsp;{$lang['addnews_allow_rate']}</td>
				<td>{$fix_input}</td>
				<td>&nbsp;</td>
			</tr>
		</table><br />{$fix_br}</td>
	</tr>
</table>
	</div>
HTML;
	
	echo <<<HTML
	<div class="dle_aTab" style="display:none;">
<table width="100%">
    <tr>
        <td width="140" style="padding:4px;">{$lang['v_ftitle']}</td>
        <td ><input type="text" class="edit bk" name="vote_title" style="width:350px"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_ftitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">{$lang['vote_title']}</td>
        <td><input type="text" class="edit bk" name="frage" style="width:350px"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_vtitle]}', this, event, '250px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:4px;">$lang[vote_body]<br /><span class="navigation">$lang[vote_str_1]</span></td>
        <td><textarea rows="10" style="width:356px;" name="vote_body" class="bk"></textarea>
    </td>
    </tr>
    <tr>
        <td style="padding:4px;">&nbsp;</td>
        <td><input type="checkbox" name="allow_m_vote" value="1"> {$lang['v_multi']}</td>
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
        <td><input type="text" name="catalog_url" size="5"  class="edit bk"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[catalog_hint_url]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_url']}</td>
        <td><input type="text" name="alt_name" size="55"  class="edit bk"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_url]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td width="140" height="29" style="padding-left:5px;">{$lang['addnews_tags']}</td>
        <td><input type="text" name="tags" id="tags" size="55"  class="edit bk" autocomplete="off" /><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_tags]}', this, event, '300px')">[?]</a></td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td height="29" style="padding-left:5px;">{$lang['date_expires']}</td>
        <td><input type="text" name="expires" id="e_date_c" size="20"  class="edit bk">
<img src="engine/skins/images/img.gif"  align="absmiddle" id="e_trigger_c" style="cursor: pointer; border: 0" /> {$lang['cat_action']} <select name="expires_action"><option value="0">{$lang['edit_dnews']}</option><option value="1" >{$lang['mass_edit_notapp']}</option><option value="2" >{$lang['mass_edit_notmain']}</option><option value="3" >{$lang['mass_edit_notfix']}</option></select><a href="#" class="hintanchor" onMouseover="showhint('{$lang['hint_expires']}', this, event, '320px')">[?]</a>
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
		<option value="1">{$lang['ng_read']}</option>
		<option value="2">{$lang['ng_all']}</option>
		<option value="3">{$lang['ng_denied']}</option>
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
	
	echo <<<HTML
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
</table>
<div class="navigation">{$lang['tabs_g_info']}</div>
</div>

</div>
HTML;
	
	echo <<<HTML
<div style="padding-left:150px;padding-top:5px;padding-bottom:5px;">
	<input type="submit" class="btn btn-success" value="{$lang['btn_send']}" style="width:100px;">&nbsp;
	<input onClick="preview()" type="button" class="btn btn-info" value="{$lang['btn_preview']}" style="width:100px;">
    <input type=hidden name=mod value=addnews>
	<input type=hidden name=action value=doaddnews>
	<input type="hidden" name="user_hash" value="$dle_login_hash" />
</div>
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
jQuery(document).ready(function($){
	initTabs('dle_tabView1',Array('{$lang['tabs_news']}','{$lang['tabs_vote']}','{$lang['tabs_extra']}','{$lang['tabs_perm']}'),0, '100%');
});
	</script>
HTML;
	
	echofooter();

} // ********************************************************************************
// Do add News
// ********************************************************************************
elseif( $action == "doaddnews" ) {
	
	include_once ENGINE_DIR . '/classes/parse.class.php';
	
	$parse = new ParseFilter( Array (), Array (), 1, 1 );
	
	$allow_comm = isset( $_POST['allow_comm'] ) ? intval( $_POST['allow_comm'] ) : 0;
	$allow_main = isset( $_POST['allow_main'] ) ? intval( $_POST['allow_main'] ) : 0;
	$approve = isset( $_POST['approve'] ) ? intval( $_POST['approve'] ) : 0;
	$allow_rating = isset( $_POST['allow_rating'] ) ? intval( $_POST['allow_rating'] ) : 0;
	$news_fixed = isset( $_POST['news_fixed'] ) ? intval( $_POST['news_fixed'] ) : 0;
	$allow_br = isset( $_POST['allow_br'] ) ? intval( $_POST['allow_br'] ) : 0;
	$category = $_POST['category'];
	$disable_index = isset( $_POST['disable_index'] ) ? intval( $_POST['disable_index'] ) : 0;

	if($member_id['user_group'] > 2 ) $disable_index = 0;

	if( !count( $category ) ) {
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
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) and $member_id['user_group'] != "1" ) $approve = 0;
	}

	if( !$user_group[$member_id['user_group']]['moderation'] ) $approve = 0;

	$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
	
	foreach ( $category as $selected ) {
		if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) ) msg( "error", $lang['addnews_error'], $lang['news_err_41'], "javascript:history.go(-1)" );
	}

	$title = $parse->process(  trim( strip_tags ($_POST['title']) ) );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

		$_POST['short_story'] = strip_tags ($_POST['short_story']);
		$_POST['full_story'] = strip_tags ($_POST['full_story']);

	}

	if ( $config['allow_admin_wysiwyg'] == "yes" ) $parse->allow_code = false;
	
	$full_story = $parse->process( $_POST['full_story'] );
	$short_story = $parse->process( $_POST['short_story'] );

	if( $config['allow_admin_wysiwyg'] == "yes" OR $allow_br != '1' ) {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story ) );
	
	} else {
		
		$full_story = $db->safesql( $parse->BB_Parse( $full_story, false ) );
		$short_story = $db->safesql( $parse->BB_Parse( $short_story, false ) );
	}

	if( $parse->not_allowed_text ) {
		msg( "error", $lang['addnews_error'], $lang['news_err_39'], "javascript:history.go(-1)" );
	}
	
	$alt_name = $_POST['alt_name'];
	
	if( trim( $alt_name ) == "" or ! $alt_name ) $alt_name = totranslit( stripslashes( $title ), true, false );
	else $alt_name = totranslit( stripslashes( $alt_name ), true, false );
	
	$title = $db->safesql( $title );
	
	$metatags = create_metatags( $short_story );
	
	$catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['catalog_url'] ) ) ) ), 0, 3, $config['charset'] ) );

	if ($config['create_catalog'] AND !$catalog_url) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ) ), 0, 1, $config['charset'] ) );
	
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
	if( $member_id['user_group'] < 3 ) {
		
		$group_regel = array ();
		
		foreach ( $_POST['group_extra'] as $key => $value ) {
			if( $value ) $group_regel[] = intval( $key ) . ':' . intval( $value );
		}
		
		if( count( $group_regel ) ) $group_regel = implode( "||", $group_regel );
		else $group_regel = "";
	
	} else
		$group_regel = '';
	
	if( trim( $_POST['expires'] ) != "" ) {
		$expires = $_POST['expires'];
		if( (($expires = strtotime( $expires )) === - 1) OR !$expires ) {
			msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
		} 
	} else $expires = '';

		
	// Обработка даты и времени
	$added_time = time() + ($config['date_adjust'] * 60);
	$newdate = $_POST['newdate'];
	
	if( $_POST['allow_date'] != "yes" ) {
		
		if( (($newsdate = strtotime( $newdate )) === - 1) OR !$newsdate ) {
			msg( "error", $lang['addnews_error'], $lang['addnews_erdate'], "javascript:history.go(-1)" );
		} else {
			$thistime = date( "Y-m-d H:i:s", $newsdate );
		}
		
		if( ! intval( $config['no_date'] ) and $newsdate > $added_time ) {
			$thistime = date( "Y-m-d H:i:s", $added_time );
		}
	
	} else
		$thistime = date( "Y-m-d H:i:s", $added_time );
		////////////////////////////	

	if( trim( $title ) == "") {
		msg( "error", $lang['addnews_error'], $lang['addnews_alert'], "javascript:history.go(-1)" );
	}

	if( dle_strlen( $title, $config['charset'] ) > 255 ) {
		msg( "error", $lang['addnews_error'], $lang['addnews_error'], "javascript:history.go(-1)" );
	}

	if( $config['safe_xfield'] ) {
		$parse->ParseFilter();
		$parse->safe_mode = true;
	}
	
	$xfieldsid = $added_time;
	$xfieldsaction = "init";
	include (ENGINE_DIR . '/inc/xfields.php');

	
	$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, descr, keywords, category, alt_name, allow_comm, approve, allow_main, fixed, allow_br, symbol, tags, metatitle) values ('$thistime', '{$member_id['name']}', '$short_story', '$full_story', '$filecontents', '$title', '{$metatags['description']}', '{$metatags['keywords']}', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '$news_fixed', '$allow_br', '$catalog_url', '{$_POST['tags']}', '{$metatags['title']}')" );
	
	$row = $db->insert_id();

	$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, access, user_id) VALUES('{$row}', '{$allow_rating}', '{$add_vote}', '{$disable_index}', '{$group_regel}', '{$member_id['user_id']}')" );
	
	if( $add_vote ) {
		$db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$row}', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );
	}

	if( $expires ) {
		$expires_action = intval($_POST['expires_action']);
		$db->query( "INSERT INTO " . PREFIX . "_post_log (news_id, expires, action) VALUES('{$row}', '$expires', '$expires_action')" );
	}
	
	if( $_POST['tags'] != "" and $approve ) {
		
		$tags = array ();
		
		$_POST['tags'] = explode( ",", $_POST['tags'] );
		
		foreach ( $_POST['tags'] as $value ) {
			
			$tags[] = "('" . $row . "', '" . trim( $value ) . "')";
		}
		
		$tags = implode( ", ", $tags );
		$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
	
	}
	
	$db->query( "UPDATE " . PREFIX . "_images set news_id='{$row}' where author = '{$member_id['name']}' AND news_id = '0'" );
	$db->query( "UPDATE " . PREFIX . "_files set news_id='{$row}' where author = '{$member_id['name']}' AND news_id = '0'" );
	$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '1', '{$title}')" );
	
	clear_cache( array('news_', 'related_', 'tagscloud_', 'archives_', 'calendar_', 'topnews_', 'rss') );
	
	msg( "info", $lang['addnews_ok'], $lang['addnews_ok_1'] . " \"" . stripslashes( stripslashes( $title ) ) . "\" " . $lang['addnews_ok_2'] );
}
?>