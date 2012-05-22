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
 Файл: wordfilter.php
-----------------------------------------------------
 Назначение: фильтр слов
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}
if( ! $user_group[$member_id['user_group']]['admin_wordfilter'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$result = "";
$word_id = intval( $_REQUEST['word_id'] );

include_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( Array (), Array (), 1, 1 );
$parse->filter_mode = false;

// ********************************************************************************
// Добавление слова
// ********************************************************************************
if( $action == "add" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	$word_find = trim( strip_tags( stripslashes( $_POST['word_find'] ) ) );
	
	if( $word_find == "" ) {
		msg( "error", $lang['word_error'], $lang['word_word'], "?mod=wordfilter" );
	}
	
	if( $word_replace == "($lang[word_del])" ) {
		$word_replace = "";
	}
	
	$word_replace = stripslashes( $parse->BB_Parse( $parse->process( $_POST['word_replace'] ), false ) );
	
	$word_id = time();
	
	$all_items = file( ENGINE_DIR . '/data/wordfilter.db.php' );
	foreach ( $all_items as $item_line ) {
		$item_arr = explode( "|", $item_line );
		if( $item_arr[0] == $word_id ) {
			$word_id ++;
		}
	}
	
	foreach ( $all_items as $word_line ) {
		$word_arr = explode( "|", $word_line );
		if( $word_arr[1] == $word_find ) {
			msg( "error", $lang['word_error'], $lang['word_ar'], "?mod=wordfilter" );
		}
	}
	
	$new_words = fopen( ENGINE_DIR . '/data/wordfilter.db.php', "a" );
	$word_find = str_replace( "|", "&#124", $word_find );
	$word_replace = str_replace( "|", "&#124", $word_replace );

	$word_find = str_replace( "$", "&#036;", $word_find );
	$word_find = str_replace( "{", "&#123;", $word_find );
	$word_find = str_replace( "}", "&#125;", $word_find );

	$word_replace = str_replace( "$", "&#036;", $word_replace );
	$word_replace = str_replace( "{", "&#123;", $word_replace );
	$word_replace = str_replace( "}", "&#125;", $word_replace );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '79', '".htmlspecialchars($word_find, ENT_QUOTES)."')" );

	fwrite( $new_words, "$word_id|$word_find|$word_replace|" . intval( $_POST['type'] ) . "|". intval( $_POST['register'] ) ."|". intval( $_POST['filter_search'] ) ."|". intval( $_POST['filter_action'] ) ."||\n" );
	fclose( $new_words );

} 
// ********************************************************************************
// Удаление слова
// ********************************************************************************
elseif( $action == "remove" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! $word_id ) {
		msg( "error", $lang['word_error'], $lang['word_nof'], "$PHP_SELF?mod=wordfilter" );
	}
	
	$old_words = file( ENGINE_DIR . '/data/wordfilter.db.php' );
	$new_words = fopen( ENGINE_DIR . '/data/wordfilter.db.php', "w" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '80', '')" );
	
	foreach ( $old_words as $old_words_line ) {
		$word_arr = explode( "|", $old_words_line );
		if( $word_arr[0] != $word_id ) {
			fwrite( $new_words, $old_words_line );
		}
	}
	fclose( $new_words );
} 
// ********************************************************************************
// Редактирование слова
// ********************************************************************************
elseif( $action == "edit" ) {
	
	// Check if Filter was specified
	if( ! $word_id ) {
		msg( "error", $lang['word_error'], $lang['word_nof'], "$PHP_SELF?mod=wordfilter" );
	}
	
	// Search & Load filter in to the Form
	$all_words = file( ENGINE_DIR . '/data/wordfilter.db.php' );
	foreach ( $all_words as $word_line ) {
		$word_arr = explode( "|", $word_line );
		if( $word_arr[0] == $word_id ) {
			
			$word_arr[1] = $parse->decodeBBCodes( $word_arr[1], false );
			$word_arr[2] = $parse->decodeBBCodes( $word_arr[2], false );
			
			if( $word_arr[3] ) $selected = "selected";
			else $selected = "";

			if( $word_arr[4] ) $selected_1 = "selected";
			else $selected_1 = "";

			$selected_2[$word_arr[5]] = "selected";
			$selected_3[$word_arr[6]] = "selected";
			
			$msg = "<script type=\"text/javascript\" language=\"javascript\">onload=focus;function focus(){document.forms[0].word_find.focus();}</script>
		<form action=\"$PHP_SELF\" method=post>

<table width=\"100%\">
    <tr>
        <td style=\"padding:2px;\" width=\"140px;\" align=\"left\">{$lang['word_word']}</td>
        <td style=\"padding:2px;\" align=\"left\"><input class=\"edit bk\" style=\"width:250px;\" value=\"$word_arr[1]\" type=text name=word_find></td>

    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">{$lang['word_rep']}</td>
        <td style=\"padding:2px;\" align=\"left\"><input class=\"edit bk\" style=\"width:250px;\" value=\"$word_arr[2]\" type=text name=word_replace></td>
    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">{$lang['filter_type']}</td>
        <td style=\"padding:2px;\" align=\"left\"><select name=type><option value=\"0\">{$lang['filter_type_1']}</option><option value=\"1\" {$selected}>{$lang['filter_type_2']}</option></select></td>

    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">{$lang['filter_register']}</td>
        <td style=\"padding:2px;\" align=\"left\"><select name=register><option value=\"0\">{$lang['opt_sys_no']}</option><option value=\"1\" {$selected_1}>{$lang['opt_sys_yes']}</option></select></td>

    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">{$lang['filter_search']}</td>
        <td style=\"padding:2px;\" align=\"left\"><select name=\"filter_search\"><option value=\"0\" {$selected_2[0]}>{$lang['filter_search_0']}</option><option value=\"1\" {$selected_2[1]}>{$lang['filter_search_1']}</option><option value=\"2\" {$selected_2[2]}>{$lang['filter_search_2']}</option></select></td>

    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">{$lang['filter_action']}</td>
        <td style=\"padding:2px;\" align=\"left\"><select name=\"filter_action\"><option value=\"0\" {$selected_3[0]}>{$lang['filter_action_0']}</option><option value=\"1\" {$selected_3[1]}>{$lang['filter_action_1']}</option></select></td>

    </tr>
    <tr>
        <td style=\"padding:2px;\" align=\"left\">&nbsp;</td>
        <td style=\"padding:2px;\" align=\"left\"><input type=\"submit\" value=\"&nbsp;&nbsp;{$lang['user_save']}&nbsp;&nbsp;\" class=\"btn btn-success\"></td>

    </tr>
</table>

		<input type=hidden name=action value=doedit>
		<input type=hidden name=word_id value=\"$word_arr[0]\">
		<input type=hidden name=mod value=wordfilter>
		<input type=hidden name=user_hash value=\"$dle_login_hash\">
		</form>";
			
			// Messages
			msg( "wordfilter", $lang['word_head'], $msg );
		
		}
	}
} 
// ********************************************************************************
// Сохранение слова
// ********************************************************************************
elseif( $action == "doedit" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$word_find = trim( strip_tags( stripslashes( $_POST['word_find'] ) ) );
	
	if( $word_find == "" ) {
		msg( "error", $lang['word_error'], $lang['word_word'], "javascript:history.go(-1)" );
	}
	
	$word_replace = stripslashes( $parse->BB_Parse( $parse->process( $_POST['word_replace'] ), false ) );
	
	$word_find = str_replace( "|", "&#124", $word_find );
	$word_replace = str_replace( "|", "&#124", $word_replace );

	$word_find = str_replace( "$", "&#036;", $word_find );
	$word_find = str_replace( "{", "&#123;", $word_find );
	$word_find = str_replace( "}", "&#125;", $word_find );

	$word_replace = str_replace( "$", "&#036;", $word_replace );
	$word_replace = str_replace( "{", "&#123;", $word_replace );
	$word_replace = str_replace( "}", "&#125;", $word_replace );
	
	$old_words = file( ENGINE_DIR . '/data/wordfilter.db.php' );
	$new_words = fopen( ENGINE_DIR . '/data/wordfilter.db.php', "w" );

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '81', '".htmlspecialchars($word_find, ENT_QUOTES)."')" );
	
	foreach ( $old_words as $word_line ) {
		$word_arr = explode( "|", $word_line );
		if( $word_arr[0] == $word_id ) {
			fwrite( $new_words, "$word_id|$word_find|$word_replace|" . intval( $_POST['type'] ) . "|". intval( $_POST['register'] ) ."|". intval( $_POST['filter_search'] ) ."|". intval( $_POST['filter_action'] ) ."||\n" );
		} else {
			fwrite( $new_words, $word_line );
		}
	}
	
	fclose( $new_words );
}
// ********************************************************************************
// Список слов
// ********************************************************************************
echoheader( "wordfilter", $lang['word_head'] );

echo <<<HTML
<form action="{$PHP_SELF}" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['word_new']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" width="140px;">{$lang['word_word']}</td>
        <td style="padding:2px;"><input class="edit bk" type="text" style="width:250px;" name="word_find" title="{$lang['word_help']}" ></td>

    </tr>
    <tr>
        <td style="padding:2px;">{$lang['word_rep']}</td>
        <td style="padding:2px;"><input class="edit bk" style="width:250px;" type="text" name="word_replace" title="$lang[word_help_1]"></td>
    </tr>
    <tr>
        <td style="padding:2px;" width="140px;">{$lang['filter_type']}</td>
        <td style="padding:2px;"><select name=type><option value="0">{$lang['filter_type_1']}</option><option value="1">{$lang['filter_type_2']}</option></select></td>

    </tr>
    <tr>
        <td style="padding:2px;" width="140px;">{$lang['filter_register']}</td>
        <td style="padding:2px;"><select name=register><option value="0">{$lang['opt_sys_no']}</option><option value="1">{$lang['opt_sys_yes']}</option></select></td>

    </tr>
    <tr>
        <td style="padding:2px;" width="140px;">{$lang['filter_search']}</td>
        <td style="padding:2px;"><select name="filter_search"><option value="0">{$lang['filter_search_0']}</option><option value="1">{$lang['filter_search_1']}</option><option value="2">{$lang['filter_search_2']}</option></select></td>

    </tr>
    <tr>
        <td style="padding:2px;" width="140px;">{$lang['filter_action']}</td>
        <td style="padding:2px;"><select name="filter_action"><option value="0">{$lang['filter_action_0']}</option><option value="1">{$lang['filter_action_1']}</option></select></td>

    </tr>
    <tr>
        <td style="padding:2px;">&nbsp;</td>
        <td style="padding:2px;"><input type="submit" value="&nbsp;&nbsp;{$lang['user_save']}&nbsp;&nbsp;" class="btn btn-success"></td>

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
<input type="hidden" name="action" value="add">
<input type="hidden" name="mod" value="wordfilter">
<input type="hidden" name="user_hash" value="$dle_login_hash" />
</form>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['word_worte']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
HTML;

$all_words = file( ENGINE_DIR . '/data/wordfilter.db.php' );
$count_words = 0;

usort( $all_words, "compare_filter" );

foreach ( $all_words as $word_line ) {
	$word_arr = explode( "|", $word_line );
	
	$result .= "
    <tr>
     <td style=\"padding:3px\">
       $word_arr[1]
     </td><td>";
	
	if( $word_arr[2] == "" ) {
		$result .= "<font color=\"red\">$lang[word_del]</font>";
	} else {
		$result .= "$word_arr[2]";
	}
	
	$type = ($word_arr[3]) ? $lang['filter_type_2'] : $lang['filter_type_1'];
	$register = ($word_arr[4]) ? $lang['opt_sys_yes'] : $lang['opt_sys_no'];

	
	$result .= "</td><td>{$register}</td><td>{$type}</td><td>{$lang['filter_search_'.$word_arr[5]]}</td><td>{$lang['filter_action_'.$word_arr[6]]}</td><td>
       <a class=maintitle href=\"$PHP_SELF?mod=wordfilter&action=edit&word_id=$word_arr[0]\">[&nbsp;{$lang[word_ledit]}&nbsp;]</a>
    </td> <td >
       <a class=maintitle href=\"$PHP_SELF?mod=wordfilter&action=remove&user_hash={$dle_login_hash}&word_id=$word_arr[0]\">[&nbsp;$lang[word_ldel]&nbsp;]</a>
    </tr></tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=8></td></tr>";
	$count_words ++;
}

if( $count_words == 0 ) {
	echo "
    <tr>
     <td height=18 colspan=8>
       <p align=center><br><b>$lang[word_empty]</b>
    </tr>";
} else {
	
	echo "<tr>
	<td>
	$lang[word_worte]
	<td>
	$lang[word_lred]
	<td width=150>
	$lang[filter_register]
	<td width=150>
	$lang[filter_type]
	<td width=150>
	$lang[filter_search]
	<td width=120>
	$lang[filter_action]
	<td width=100>
	&nbsp;
	<td width=100>
	&nbsp;
	</tr><tr>
        <td colspan=\"8\"><div class=\"hr_line\"></div></td>
    </tr>";
	
	echo $result;

}

echo "<tr>
<td colspan=2 class=\"main\"><br>{$lang['word_help_2']}
</tr>";

echo <<<HTML
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
?>