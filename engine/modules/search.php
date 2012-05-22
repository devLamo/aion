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
 Файл: search.php
-----------------------------------------------------
 Назначение: поиск по сайту
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['allow_search'] ) {
	
	$lang['search_denied'] = str_replace( '{group}', $user_group[$member_id['user_group']]['group_name'], $lang['search_denied'] );
	msgbox( $lang['all_info'], $lang['search_denied'] );

} else {

	function strip_data($text) {
		$quotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r", "'", ",", "/", ";", ":", "@", "[", "]", "{", "}", "=", ")", "(", "*", "&", "^", "%", "$", "<", ">", "?", "!", '"' );
		$goodquotes = array ("-", "+", "#" );
		$repquotes = array ("\-", "\+", "\#" );
		$text = stripslashes( $text );
		$text = trim( strip_tags( $text ) );
		$text = str_replace( $quotes, '', $text );
		$text = str_replace( $goodquotes, $repquotes, $text );
		return $text;
	}
	
	$count_result = 0;
	$sql_count = "";
	$sql_find = "";

	// Минимальное количество символов в слове поиска
	$config['search_length_min'] = 4;

	$tpl->load_template( 'search.tpl' );
	
	$config['search_number'] = intval($config['search_number']);

	if ( $config['search_number'] < 1) $config['search_number'] = 1;
	
	$this_date = date( "Y-m-d H:i:s", $_TIME );
	if( $config['no_date'] AND !$config['news_future'] ) $this_date = " AND " . PREFIX . "_post.date < '" . $this_date . "'"; else $this_date = "";
	
	if( isset( $_REQUEST['story'] ) ) $story = dle_substr( strip_data( rawurldecode( $_REQUEST['story'] ) ), 0, 90, $config['charset'] ); else $story = "";
	if( isset( $_REQUEST['search_start'] ) ) $search_start = intval( $_REQUEST['search_start'] ); else $search_start = 0;
	if( isset( $_REQUEST['titleonly'] ) ) $titleonly = intval( $_REQUEST['titleonly'] ); else $titleonly = 0;
	if( isset( $_REQUEST['searchuser'] ) ) $searchuser = dle_substr( strip_data( $_REQUEST['searchuser'] ), 0, 40, $config['charset'] ); else $searchuser = "";
	if( isset( $_REQUEST['exactname'] ) ) $exactname = $_REQUEST['exactname']; else $exactname = "";
	if( isset( $_REQUEST['all_word_seach'] ) ) $all_word_seach = intval($_REQUEST['all_word_seach']); else $all_word_seach = 0;
	if( isset( $_REQUEST['replyless'] ) ) $replyless = intval( $_REQUEST['replyless'] ); else $replyless = 0;
	if( isset( $_REQUEST['replylimit'] ) ) $replylimit = intval( $_REQUEST['replylimit'] ); else $replylimit = 0;
	if( isset( $_REQUEST['searchdate'] ) ) $searchdate = intval( $_REQUEST['searchdate'] ); else $searchdate = 0;
	if( isset( $_REQUEST['beforeafter'] ) ) $beforeafter = strip_data( $_REQUEST['beforeafter'] ); else $beforeafter = "after";

	if ($config['full_search']) {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = strip_data( $_REQUEST['sortby'] ); else $sortby = "";
	} else {
		if( isset( $_REQUEST['sortby'] ) ) $sortby = strip_data( $_REQUEST['sortby'] ); else $sortby = "date";
	}

	if( isset( $_REQUEST['resorder'] ) ) $resorder = strip_data( $_REQUEST['resorder'] ); else $resorder = "desc";
	if( isset( $_REQUEST['showposts'] ) ) $showposts = intval( $_REQUEST['showposts'] ); else $showposts = 0;
	if( isset( $_REQUEST['result_from'] ) ) $result_from = intval( $_REQUEST['result_from'] ); else $result_from = 1; // Показать страницу с результатом № ХХХ
	$full_search = intval( $_REQUEST['full_search'] );

	if( !count( $_REQUEST['catlist'] ) ) {
		$catlist = array ();
		$catlist[] = '0';
	} else
		$catlist = $_REQUEST['catlist'];

	$category_list = array();
	
	foreach ( $catlist as $value ) {
		$category_list[] = intval($value);
	}

	$category_list = $db->safesql( implode( ',', $category_list ) );

	$findstory = stripslashes( $story ); // Для вывода в поле поиска

	if ($titleonly == 2 AND !empty( $searchuser ) ) $searchuser = "";
	if( empty( $story ) AND !empty( $searchuser ) AND $titleonly != 2) $story = "___SEARCH___ALL___"; // Для поиска всех статей
	if( $search_start < 0 ) $search_start = 0; // Начальная страница поиска
	if( $titleonly < 0 or $titleonly > 3 ) $titleonly = 0; // Искать в заголовках, статьях, комментариях
	if( $replyless < 0 or $replyless > 1 ) $replyless = 0; // Искать больше или меньше ответов
	if( $replylimit < 0 ) $replylimit = 0; // Лимит ответов
	if( $showposts < 0 or $showposts > 1 ) $showposts = 0; // Искать в статьях или комментариях юзера
	
	$listdate = array (0, - 1, 1, 7, 14, 30, 90, 180, 365 ); // Искать за период ХХХ дней
	if( ! (in_array( $searchdate, $listdate )) ) $searchdate = 0;
	if( $beforeafter != "after" and $beforeafter != "before" ) $beforeafter = "after"; // Искать до или после периода дней
	$listsortby = array ("date", "title", "comm_num", "news_read", "autor", "category", "rating" );

	if ($config['full_search']) {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = ""; // Сортировать по полям
	} else {
		if( ! (in_array( $sortby, $listsortby )) ) $sortby = "date"; // Сортировать по полям
	}

	$listresorder = array ("desc", "asc" );
	if( ! (in_array( $resorder, $listresorder )) ) $resorder = "desc"; // Сортировать по возрастающей или убывающей
	

	// Определение выбранных ранее опций, переданных в форме
	$titleonly_sel = array ('0' => '', '1' => '', '2' => '', '3' => '' );
	$titleonly_sel[$titleonly] = 'selected="selected"';
	$replyless_sel = array ('0' => '', '1' => '' );
	$replyless_sel[$replyless] = 'selected="selected"';
	$searchdate_sel = array ('0' => '', '-1' => '', '1' => '', '7' => '', '14' => '', '30' => '', '90' => '', '180' => '', '365' => '' );
	$searchdate_sel[$searchdate] = 'selected="selected"';
	$beforeafter_sel = array ('after' => '', 'before' => '' );
	$beforeafter_sel[$beforeafter] = 'selected="selected"';
	$sortby_sel = array ('date' => '', 'title' => '', 'comm_num' => '', 'news_read' => '', 'autor' => '', 'category' => '', 'rating' => '' );
	$sortby_sel[$sortby] = 'selected="selected"';
	$resorder_sel = array ('desc' => '', 'asc' => '' );
	$resorder_sel[$resorder] = 'selected="selected"';
	$showposts_sel = array ('0' => '', '1' => '' );
	$showposts_sel[$showposts] = 'checked="checked"';
	if( $exactname == "yes" ) $exactname_sel = 'checked="checked"';
	else $exactname_sel = '';

	if( $all_word_seach == 1 ) $all_word_seach_sel = 'checked="checked"';
	else $all_word_seach_sel = '';
	
	// Вывод формы поиска
	if( $category_list == "" or $category_list == "0" ) {
		$catselall = "selected=\"selected\"";
	} else {
		$catselall = "";
		$category_list = preg_replace( "/^0\,/", '', $category_list );
	}
	
	// Определение и вывод доступных категорий
	$cats = "<select class=\"rating\" style=\"width:95%;height:200px;\" name=\"catlist[]\" size=\"13\" multiple=\"multiple\">";
	$cats .= "<option " . $catselall . " value=\"0\">" . $lang['s_allcat'] . "</option>";
	$cats .= CategoryNewsSelection( explode( ',', $category_list ), 0, false );
	$cats .= "</select>";
	
	$tpl->copy_template .= <<<HTML
<script type="text/javascript" language="javascript">
<!-- begin
function clearform(frmname){
  var frm = document.getElementById(frmname);
  for (var i=0;i<frm.length;i++) {
    var el=frm.elements[i];
    if (el.type=="checkbox" || el.type=="radio") {
    	if (el.name=='showposts') {document.getElementById('rb_showposts_0').checked=1; } else {el.checked=0; }
    }
    if ((el.type=="text") || (el.type=="textarea") || (el.type == "password")) { el.value=""; continue; }
    if ((el.type=="select-one") || (el.type=="select-multiple")) { el.selectedIndex=0; }
  }
  document.getElementById('replylimit').value = 0;
  document.getElementById('search_start').value = 0;
  document.getElementById('result_from').value = 1;
}
function list_submit(prm){
  var frm = document.getElementById('fullsearch');
	if (prm == -1) {
		prm=Math.ceil(frm.result_from.value / {$config['search_number']});
	} else {
		frm.result_from.value=(prm-1) * {$config['search_number']} + 1;
	}
	frm.search_start.value=prm;

  frm.submit();
  return false;
}
function full_submit(prm){
    document.getElementById('fullsearch').full_search.value=prm;
    list_submit(-1);
}
function reg_keys(key) {
	var code;
	if (!key) var key = window.event;
	if (key.keyCode) code = key.keyCode;
	else if (key.which) code = key.which;

	if (code == 13) {
		list_submit(-1);
	}
};

document.onkeydown = reg_keys;
// end -->
</script>
HTML;
	
	$searchtable = <<<HTML
<form name="fullsearch" id="fullsearch" action="{$config['http_home_url']}index.php?do=search" method="post">
<input type="hidden" name="do" id="do" value="search" />
<input type="hidden" name="subaction" id="subaction" value="search" />
<input type="hidden" name="search_start" id="search_start" value="$search_start" />
<input type="hidden" name="full_search" id="full_search" value="$full_search" />
<input type="hidden" name="result_from" id="result_from" value="$result_from" />
HTML;
	
	if( $full_search ) {

		if ($config['full_search']) {
			$full_search_option = "<option value=\"\" selected=\"selected\">{$lang['s_fsrelate']}</option><option {$sortby_sel['date']} value=\"date\">{$lang['s_fsdate']}</option>";
			$all_word_option = "";
		} else {

			$full_search_option = "<option {$sortby_sel['date']} value=\"date\" selected=\"selected\">{$lang['s_fsdate']}</option>";
			$all_word_option = "<div><label for=\"all_word_seach\"><input type=\"checkbox\" name=\"all_word_seach\" value=\"1\" id=\"all_word_seach\" {$all_word_seach_sel} />{$lang['s_fword']}</label></div>";
		}
		
		$searchtable .= <<<HTML
<table cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="search">
      <div align="center">
        <table cellpadding="0" cellspacing="2" width="100%">

        <tr style="vertical-align: top;">
				<td class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_con']}</legend>
						<table cellpadding="0" cellspacing="3" border="0">
						<tr>
						<td class="search">
							<div>{$lang['s_word']}</div>
							<div><input type="text" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" /></div>
							{$all_word_option}
						</td>
						</tr>
						<tr>
						<td class="search">
							<select class="textin" name="titleonly" id="titleonly">
								<option {$titleonly_sel['0']} value="0">{$lang['s_ncom']}</option>
								<option {$titleonly_sel['1']} value="1">{$lang['s_ncom1']}</option>
                                <option {$titleonly_sel['2']} value="2">{$lang['s_static']}</option>
								<option {$titleonly_sel['3']} value="3">{$lang['s_tnews']}</option>
							</select>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>

				<td class="search" valign="top">					
					<fieldset style="margin:0px">
						<legend>{$lang['s_mname']}</legend>
						<table cellpadding="0" cellspacing="3" border="0">
						<tr>
						<td class="search">
							<div>{$lang['s_fname']}</div>
							<div id="userfield"><input type="text" name="searchuser" id="searchuser" value="$searchuser" class="textin" style="width:250px" /><br /><label for="exactname"><input type="checkbox" name="exactname" value="yes" id="exactname" {$exactname_sel} />{$lang['s_fgname']}</label>
							</div>
						</td>
						</tr>
						</table>
					</fieldset>
				</td>
				</tr>

				<tr style="vertical-align: top;">

				<td width="50%" class="search">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fart']}</legend>
						<div style="padding:3px">
							<select class="textin" name="replyless" id="replyless" style="width:200px">
								<option {$replyless_sel['0']} value="0">{$lang['s_fmin']}</option>
								<option {$replyless_sel['1']} value="1">{$lang['s_fmax']}</option>
							</select>
							<input type="text" name="replylimit" id="replylimit" size="5" value="$replylimit" class="textin" /> {$lang['s_wcomm']}
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fdaten']}</legend>

						<div style="padding:3px">					
							<select name="searchdate" id="searchdate" class="textin" style="width:200px">
								<option {$searchdate_sel['0']} value="0">{$lang['s_tall']}</option>
								<option {$searchdate_sel['-1']} value="-1">{$lang['s_tlast']}</option>
								<option {$searchdate_sel['1']} value="1">{$lang['s_tday']}</option>
								<option {$searchdate_sel['7']} value="7">{$lang['s_tweek']}</option>
								<option {$searchdate_sel['14']} value="14">{$lang['s_ttweek']}</option>
								<option {$searchdate_sel['30']} value="30">{$lang['s_tmoth']}</option>
								<option {$searchdate_sel['90']} value="90">{$lang['s_tfmoth']}</option>
								<option {$searchdate_sel['180']} value="180">{$lang['s_tsmoth']}</option>
								<option {$searchdate_sel['365']} value="365">{$lang['s_tyear']}</option>
							</select>
							<select name="beforeafter" id="beforeafter" class="textin">
								<option {$beforeafter_sel['after']} value="after">{$lang['s_fnew']}</option>
								<option {$beforeafter_sel['before']} value="before">{$lang['s_falt']}</option>
							</select>
						</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_fsoft']}</legend>
							<div style="padding:3px">
								<select name="sortby" id="sortby" class="textin" style="width:200px">
									{$full_search_option}
									<option {$sortby_sel['title']} value="title" >{$lang['s_fstitle']}</option>
									<option {$sortby_sel['comm_num']} value="comm_num" >{$lang['s_fscnum']}</option>
									<option {$sortby_sel['news_read']} value="news_read" >{$lang['s_fsnnum']}</option>
									<option {$sortby_sel['autor']} value="autor" >{$lang['s_fsaut']}</option>
									<option {$sortby_sel['category']} value="category" >{$lang['s_fscat']}</option>
									<option {$sortby_sel['rating']} value="rating" >{$lang['s_fsrate']}</option>
								</select>
								<select name="resorder" id="resorder" class="textin">
									<option {$resorder_sel['desc']} value="desc">{$lang['s_fsdesc']}</option>
									<option {$resorder_sel['asc']} value="asc">{$lang['s_fsasc']}</option>
								</select>
							</div>
					</fieldset>

					<fieldset style="padding-top:10px">
						<legend>{$lang['s_vlegend']}</legend>

						<table cellpadding="0" cellspacing="3" border="0">
						<tr align="left" valign="middle">
						<td align="left" class="search">{$lang['s_vwie']}&nbsp;&nbsp;
							<label for="rb_showposts_0"><input type="radio" name="showposts" value="0" id="rb_showposts_0" {$showposts_sel['0']} />{$lang['s_vnews']}</label>
							<label for="rb_showposts_1"><input type="radio" name="showposts" value="1" id="rb_showposts_1" {$showposts_sel['1']} />{$lang['s_vtitle']}</label>
						</td>
						</tr>

						</table>
					</fieldset>
				</td>

				<td width="50%" class="search" valign="top">
					<fieldset style="margin:0px">
						<legend>{$lang['s_fcats']}</legend>
							<div style="padding:3px">
								<div>$cats</div>
							</div>

					</fieldset>
				</td>
				</tr>

        <tr>
                <td class="search" colspan="2">
                    <div style="margin-top:6px">
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 0px;" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;" />
                        <input type="button" class="bbcodes" style="margin:0px 20px 0 20px;" name="doclear" id="doclear" value="{$lang['s_fstop']}" onclick="javascript:clearform('fullsearch'); return false;" />
                        <input type="reset" class="bbcodes" style="margin:0px 20px 0 20px;" name="doreset" id="doreset" value="{$lang['s_freset']}" />
                    </div>

                </td>
                </tr>

        </table>
      </div>
    </td>
  </tr>
</table>
HTML;
	
	} else {

	if ( $smartphone_detected ) {

		$link_full_search = "";

	} else {

		$link_full_search = "<input type=\"button\" class=\"bbcodes\" name=\"dofullsearch\" id=\"dofullsearch\" value=\"{$lang['s_ffullstart']}\" onclick=\"javascript:full_submit(1); return false;\" />";

	}
		
		$searchtable .= <<<HTML
<table cellpadding="4" cellspacing="0" width="100%">
  <tr>
    <td class="search">
      <div style="margin:10px;">
                <input type="text" name="story" id="searchinput" value="$findstory" class="textin" style="width:250px" /><br /><br />
                <input type="button" class="bbcodes" name="dosearch" id="dosearch" value="{$lang['s_fstart']}" onclick="javascript:list_submit(-1); return false;" />
                {$link_full_search}
            </div>

        </td>
    </tr>
</table>
HTML;
	
	}
	
	$searchtable .= <<<HTML

</form>
HTML;
	
	$tpl->set( '{searchtable}', $searchtable );
	// По умолчанию, выводится только форма поиска
	if( $subaction != "search" ) {
		$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
		$tpl->compile( 'content' );
	}
	// Конец вывода формы поиска
	

	if( $subaction == "search" ) {
		// Вывод результатов поиска		

		if ($config['full_search']) {
	
			$arr = explode( ' ', $story );
			$story_maxlen = 0;
			$story = array ();
			
			foreach ( $arr as $word ) {
				$wordlen = dle_strlen( trim( $word ), $config['charset'] );
				
				if( $wordlen >= $config['search_length_min'] ) $story[] = $word;
				
				if( $wordlen > $story_maxlen ) {
					$story_maxlen = $wordlen;
				}
			}
			
			$story = implode( " ", $story );
	
		} else {
	
			if ( !$all_word_seach ) $story = preg_replace( "#(\s+|__OR__)#i", '%', $story );

			$story_maxlen = dle_strlen( trim( $story ), $config['charset'] );
	
		}
	
		if( (empty( $story ) or ($story_maxlen < $config['search_length_min'])) and (empty( $searchuser ) or (strlen( $searchuser ) < $config['search_length_min'])) ) {
			
			msgbox( $lang['all_info'], $lang['search_err_3'] );
			
			$tpl->set( '{searchmsg}', '' );
			$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
			$tpl->compile( 'content' );
		
		} else {
			// Начало подготовки поиска
			if( $search_start ) {
				$search_start = $search_start - 1;
				$search_start = $search_start * $config['search_number'];
			}
			
			// Проверка разрешенных категорий из списка выбранных категорий
			$allow_cats = $user_group[$member_id['user_group']]['allow_cats'];
			$allow_list = explode( ',', $allow_cats );
			$stop_list = "";
			if( $allow_list[0] == "all" ) {
				// Все категории доступны для группы
				if( $category_list == "" or $category_list == "0" ) {
					// Выбран поиск по всем категориям
					;
				} else {
					// Выбран поиск по некоторым категориям
					$stop_list = str_replace( ',', '|', $category_list );
				}
			} else {
				// Не все категории доступны для группы
				if( $category_list == "" or $category_list == "0" ) {
					// Выбран поиск по всем категориям
					$stop_list = str_replace( ',', '|', $allow_cats );
				} else {
					// Выбран поиск по некоторым категориям
					$cats_list = explode( ',', $category_list );
					foreach ( $cats_list as $id ) {
						if( in_array( $id, $allow_list ) ) $stop_list .= $id . '|';
					}
					$stop_list = substr( $stop_list, 0, strlen( $stop_list ) - 1 );
				}
			}
			// Ограничение по категориям
			$where_category = "";
			if( ! empty( $stop_list ) ) {
				
				if( $config['allow_multi_category'] ) {
					
					$where_category = "category regexp '[[:<:]](" . $stop_list . ")[[:>:]]'";
				
				} else {
					
					$stop_list = str_replace( "|", "','", $stop_list );
					$where_category = "category IN ('" . $stop_list . "')";
				
				}
			}
			
			if( $story == "___SEARCH___ALL___" ) $story = '';
			$thistime = date( "Y-m-d H:i:s", (time() + $config['date_adjust'] * 60) );
			
			if( $exactname == 'yes' ) $likename = '';
			else $likename = '%';
			if( $searchdate != '0' ) {
				if( $searchdate != '-1' ) {
					$qdate = date( "Y-m-d H:i:s", (time() + $config['date_adjust'] * 60 - $searchdate * 86400) );
				} else {
					if( $is_logged and isset( $_SESSION['member_lasttime'] ) ) $qdate = date( "Y-m-d H:i:s", $_SESSION['member_lasttime'] );
					else $qdate = $thistime;
				}
			}
			
			// Поиск по автору статьи или комментария
			$autor_posts = '';
			$autor_comms = '';
			$searchuser = $db->safesql($searchuser);
			if( ! empty( $searchuser ) ) {
				switch ($titleonly) {
					case 0 :
						// Искать только в статьях
						$autor_posts = PREFIX . "_post.autor like '$searchuser$likename'";
						break;
					case 3 :
						// Искать только в статьях
						$autor_posts = PREFIX . "_post.autor like '$searchuser$likename'";
						break;
					case 1 :
						// Искать только в комментариях
						$autor_comms = PREFIX . "_comments.autor like '$searchuser$likename'";
						break;
				}
			}
			
			$where_reply = "";
			if( ! empty( $replylimit ) ) {
				if( $replyless == 0 ) $where_reply = PREFIX . "_post.comm_num >= '" . $replylimit . "'";
				else $where_reply = PREFIX . "_post.comm_num <= '" . $replylimit . "'";
			}
			
			// Поиск по ключевым словам

			if ($config['full_search']) {
	
					$titleonly_where = array ('0' => "MATCH(title,short_story,full_story,xfields) AGAINST ('{story}')", // Искать только в статьях
											  '1' => "MATCH(text) AGAINST ('{story}')", // Искать только в комментариях
											  '2' => "MATCH(" . PREFIX . "_static.template) AGAINST ('{story}')", // Искать только в статических страницах
											  '3' => "title LIKE '%{story}%'" ); // Искать только в заголовках статей
	
			} else {
	
					$titleonly_where = array ('0' => "short_story LIKE '%{story}%' OR full_story LIKE '%{story}%' OR xfields LIKE '%{story}%' OR title LIKE '%{story}%'", // Искать только в статьях
											  '1' => "text LIKE '%{story}%'", // Искать только в комментариях
											  '2' => PREFIX . "_static.template LIKE '%{story}%'", // Искать только в статических страницах
											  '3' => "title LIKE '%{story}%'" ); // Искать только в заголовках статей
			}

			if( !empty( $story ) ) {
	
				foreach ( $titleonly_where as $name => $value ) {
					$value2 = str_replace( "{story}", $db->safesql($story), $value );
						
					$titleonly_where[$name] = $value2;
				}
			}
			
			// Поиск по статьям
			if( in_array( $titleonly, array (0, 3 ) ) ) {
				$where_posts = "WHERE " . PREFIX . "_post.approve=1" . $this_date;
				if( ! empty( $where_category ) ) $where_posts .= " AND " . $where_category;

				if ($config['full_search']) {
					if( ! empty( $story ) ) $where_posts .= " AND " . $titleonly_where[$titleonly];
				} else {
					if( ! empty( $story ) ) $where_posts .= " AND (" . $titleonly_where[$titleonly] . ")";
				}

				if( ! empty( $autor_posts ) ) $where_posts .= " AND " . $autor_posts;

				$sdate = PREFIX . "_post.date";

				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_posts .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_posts .= " AND " . $where_reply;
				$where = $where_posts;

				if ($config['full_search']) if( $titleonly_where[$titleonly] == "" ) $titleonly_where[$titleonly] = "''";

				$posts_fields = "SELECT SQL_CALC_FOUND_ROWS id, autor, " . PREFIX . "_post.date AS newsdate, " . PREFIX . "_post.date AS date, short_story AS story, " . PREFIX . "_post.xfields AS xfields, title, descr, keywords, category, alt_name, comm_num AS comm_in_news, allow_comm, rating, news_read, editdate, editor, reason, view_edit, tags, '' AS output_comms";
				$posts_from = "FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id)";
				$sql_fields = $posts_fields;
				$sql_find = "$sql_fields $posts_from $where";

			}

			// Поиск по комментариям 
			if( $titleonly == 1) {
				$where_comms = "WHERE " . PREFIX . "_post.approve=1" . $this_date;
				if( ! empty( $where_category ) ) $where_comms .= " AND " . $where_category;
				if( ! empty( $story ) ) $where_comms .= " AND (" . $titleonly_where['1'] . ")";
				if( ! empty( $autor_comms ) ) $where_comms .= " AND " . $autor_comms;
				$sdate = PREFIX . "_comments.date";
				if( $searchdate != '0' ) {
					if( $beforeafter == 'before' ) $where_date = $sdate . " < '" . $qdate . "'";
					else $where_date = $sdate . " between '" . $qdate . "' and '" . $thistime . "'";
					$where_comms .= " AND " . $where_date;
				}

				if( ! empty( $where_reply ) ) $where_comms .= " AND " . $where_reply;
				$where = $where_comms;

				if( $config['allow_cmod'] ) $where .= " AND " . PREFIX . "_comments.approve=1";
				$comms_fields = "SELECT SQL_CALC_FOUND_ROWS " . PREFIX . "_comments.id AS coms_id, post_id AS id, " . PREFIX . "_comments.date, " . PREFIX . "_comments.autor AS autor, " . PREFIX . "_comments.email AS gast_email, " . PREFIX . "_comments.text AS story, ip, is_register, name, " . USERPREFIX . "_users.email, news_num, " . USERPREFIX . "_users.comm_num, reg_date, banned, signature, foto, fullname, land, icq, " . PREFIX . "_post.date AS newsdate, " . PREFIX . "_post.title, " . PREFIX . "_post.category, " . PREFIX . "_post.alt_name, '1' AS output_comms";
				$comms_from = "FROM " . PREFIX . "_comments LEFT JOIN " . PREFIX . "_post ON " . PREFIX . "_comments.post_id=" . PREFIX . "_post.id LEFT JOIN " . USERPREFIX . "_users ON " . PREFIX . "_comments.user_id=" . USERPREFIX . "_users.user_id";
				$sql_fields = $comms_fields;
				$sql_find = "$sql_fields $comms_from $where";

			}
			
			$order_by = $sortby . " " . $resorder;
			
			// Поиск в статических страницах
			if( $titleonly == 2 ) {
				$sql_from = "FROM " . PREFIX . "_static";
				$sql_fields = "SELECT SQL_CALC_FOUND_ROWS id, name AS static_name, descr AS title, template AS story, allow_template, grouplevel, date, views";
				if ( $titleonly_where[$titleonly] )	$where = "WHERE " . $titleonly_where[$titleonly];
				else $where = "";
				$sql_find = "$sql_fields $sql_from $where";
				$order_by = "id";
			}
			
			// ------ Запрос к базе
	
			$from_num = $search_start + 1;
			
			if ($config['full_search']) {

				if( $sortby != "" ) $order_by = "ORDER BY " . $order_by; else $order_by = "";
				
				$sql_request = "$sql_find $order_by LIMIT $search_start,{$config['search_number']}";
	
			} else {
	
				$sql_request = "$sql_find ORDER BY $order_by LIMIT $search_start,{$config['search_number']}";
	
			}
			
			$sql_result = $db->query( $sql_request );
			$found_result = $db->num_rows( $sql_result );

			$result_count = $db->super_query( "SELECT FOUND_ROWS() as count" );
			$count_result = $result_count['count'];
			if( $count_result > ($config['search_number'] * 5) ) $count_result = ($config['search_number'] * 5);

			
			// Не найдено
			if( ! $found_result ) {
				msgbox( $lang['all_info'], $lang[search_err_2] );
				$tpl->set( '{searchmsg}', '' );
				$tpl->set_block( "'\[searchmsg\](.*?)\[/searchmsg\]'si", "" );
				$tpl->compile( 'content' );
			} else {
				$to_num = $search_start + $found_result;
				
				// Вывод информации о количестве найденных результатов
				$searchmsg = "$lang[search_ok] " . $count_result . " $lang[search_ok_1] ($lang[search_ok_2] " . $from_num . " - " . $to_num . ") :";
				$tpl->set( '{searchmsg}', $searchmsg );
				$tpl->set( '[searchmsg]', "" );
				$tpl->set( '[/searchmsg]', "" );
				$tpl->compile( 'content' );
				
				$tpl->load_template( 'searchresult.tpl' );
				$xfields = xfieldsload();
				
				function hilites($search, $txt) {
					
					$r = preg_split( '((>)|(<))', $txt, - 1, PREG_SPLIT_DELIM_CAPTURE );
					
					for($i = 0; $i < count( $r ); $i ++) {
						if( $r[$i] == "<" ) {
							$i ++;
							continue;
						}
						$r[$i] = preg_replace( "#($search)#i", "<span style='background-color:yellow;'><font color='red'>\\1</font></span>", $r[$i] );
					}
					return join( "", $r );
				}
				
				// Вывод текста статьи или комментария во всплывающей подсказке при выводе только заголовков
				function create_description($txt) {
					$fastquotes = array ("\x27", "\x22", "\x60", "\t", "\n", "\r" );
					$quotes = array ('"', "'" );
					$maxchr = 80;
					$txt = preg_replace( "/\[hide\](.*?)\[\/hide\]/ims", "", $txt );
					$txt = stripslashes( $txt );
					$txt = trim( strip_tags( $txt ) );
					$txt = str_replace( $fastquotes, ' ', $txt );
					$txt = str_replace( $quotes, '', $txt );
					$txt = preg_replace( "#\s+#i", ' ', $txt );
					$txt = substr( $txt, 0, 300 );
					$txt = wordwrap( $txt, $maxchr, "  " );
					return $txt;
				}
				
				// Вывод результатов поиска
				$search_id = $search_start;
				while ( $row = $db->get_row( $sql_result ) ) {
					
					// Порядковый номер результата поиска
					$search_id ++;
					
					$attachments[] = $row['id'];
					if( $titleonly != 2 ) {
						$row['newsdate'] = strtotime( $row['newsdate'] );
						$row['date'] = strtotime( $row['date'] );
					}

					$row['story'] = stripslashes( $row['story'] );

					if( $user_group[$member_id['user_group']]['allow_hide'] ) $row['story'] = str_ireplace( "[hide]", "", str_ireplace( "[/hide]", "", $row['story']) );
					else $row['story'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#is", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $row['story'] );

					if ($config['full_search']) $arr = explode( " ", $story ); else	$arr = explode( "%", $story );
					
					foreach ( $arr as $word ) {
						if( strlen( trim( $word ) ) >= $config['search_length_min'] ) {
							$row['story'] = hilites( $word, $row['story'] );
						}
						;
					}
					
					if( $titleonly == 2 ) {
						// Результаты поиска в статических страницах
						$row['grouplevel'] = explode( ',', $row['grouplevel'] );
						if( $row['grouplevel'][0] != "all" and ! in_array( $member_id['user_group'], $row['grouplevel'] ) ) {
							$tpl->result['content'] .= $lang['static_denied'];
						} else {
							
							$row['story'] = stripslashes( $row['story'] );

							$news_seiten = explode( "{PAGEBREAK}", $row['story'] );
							$anzahl_seiten = count( $news_seiten );

							$row['story'] = $news_seiten[0];

							$news_seiten = "";
							unset( $news_seiten );

							if( $anzahl_seiten > 1 ) {

								if( $config['allow_alt_url'] == "yes" ) {
									$replacepage = "<a href=\"" . $config['http_home_url'] . "page," . "\\1" . "," . $row['static_name'] . ".html\">\\2</a>";
								} else {
									$replacepage = "<a href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "&news_page=\\1\">\\2</a>";
								}

								$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", $replacepage, $row['story'] );

							} else {
								
								$row['story'] = preg_replace( "'\[PAGE=(.*?)\](.*?)\[/PAGE\]'si", "", $row['story'] );
							
							}
	
							$title = stripslashes( strip_tags( $row['title'] ) );
							
							if( $row['allow_template'] ) {
								$tpl->load_template( 'static.tpl' );
								if( $config['allow_alt_url'] == "yes" ) $static_descr = "<a title=\"" . $title . "\" href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $title . "</a>";
								else $static_descr = "<a title=\"" . $title . "\" href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "\" >" . $title . "</a>";
								$tpl->set( '{description}', $static_descr );

								if (dle_strlen( $row['story'], $config['charset'] ) > 2000) {

									$row['story'] = dle_substr( strip_tags ($row['story']), 0, 2000, $config['charset'])." .... ";
									if( $config['allow_alt_url'] == "yes" ) $row['story'] .= "( <a href=\"" . $config['http_home_url'] . $row['static_name'] . ".html\" >" . $lang['search_s_go'] . "</a> )";
									else $row['story'] .= "( <a href=\"$PHP_SELF?do=static&page=" . $row['static_name'] . "\" >" . $lang['search_s_go'] . "</a> )";

								}

								$tpl->set( '{static}', $row['story'] );
								$tpl->set( '{pages}', '' );

								if( @date( "Ymd", $row['date'] ) == date( "Ymd", $_TIME ) ) {
									
									$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
								
								} elseif( @date( "Ymd", $row['date'] ) == date( "Ymd", ($_TIME - 86400) ) ) {
									
									$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
								
								} else {
									
									$tpl->set( '{date}', langdate( $config['timestamp_active'], $row['date'] ) );
								
								}
						
								$tpl->copy_template = preg_replace ( "#\{date=(.+?)\}#ie", "langdate('\\1', '{$row['date']}')", $tpl->copy_template );

								$tpl->set( '{views}', $row['views'] );
			
								if( $config['allow_alt_url'] == "yes" ) $print_link = $config['http_home_url'] . "print:" . $row['static_name'] . ".html";
								else $print_link = $config['http_home_url'] . "engine/print.php?do=static&amp;page=" . $row['static_name'];
								
								$tpl->set( '[print-link]', "<a href=\"" . $print_link . "\">" );
								$tpl->set( '[/print-link]', "</a>" );
								
								$tpl->compile( 'content' );
								$tpl->clear();
							} else
								$tpl->result['content'] .= $row['story'];
							
							if( $config['files_allow'] == "yes" ) {
								if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
									$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments, true );
								}
							}
						
						}
					} else {
						// Результаты поиска в статьях и комментариях
						

						$tpl->set( '{result-date}', langdate( $config['timestamp_active'], $row['date'] ) );
						$tpl->copy_template = preg_replace ( "#\{date=(.+?)\}#ie", "langdate('\\1', '{$row['date']}')", $tpl->copy_template );
						
						$row_title = stripslashes( $row['title'] );
						$tpl->set( '{result-title}', $row_title );

						$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
						$go_page = "onclick=\"ShowProfile('" . urlencode( $row['autor'] ) . "', '" . htmlspecialchars( $go_page ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
						
						if( $config['allow_alt_url'] == "yes" ) $tpl->set( '{result-author}', "<a {$go_page} href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/\">" . $row['autor'] . "</a>" );
						else $tpl->set( '{result-author}', "<a {$go_page} href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] ) . "\">" . $row['autor'] . "</a>" );
												
						$tpl->set( '{result-comments}', $row['comm_in_news'] );
						$my_news_id = "<a title=\"" . $row_title . "\" href=\"$PHP_SELF?newsid=" . $row['id'] . "\">№ " . $row['id'] . "</a>";
						$tpl->set( '{news-id}', $my_news_id );
						
						if( ! $row['category'] ) {
							$my_cat = "---";
							$my_cat_link = "---";
						} else {
							
							$my_cat = array ();
							$my_cat_link = array ();
							$cat_list = explode( ',', $row['category'] );
							
							if( count( $cat_list ) == 1 ) {
								
								$my_cat[] = $cat_info[$cat_list[0]]['name'];
								
								$my_cat_link = get_categories( $cat_list[0] );
							
							} else {
								
								foreach ( $cat_list as $element ) {
									if( $element ) {
										$my_cat[] = $cat_info[$element]['name'];
										if( $config['allow_alt_url'] == "yes" ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
										else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
									}
								}
								
								$my_cat_link = stripslashes( implode( ', ', $my_cat_link ) );
							}
							
							$my_cat = stripslashes( implode( ', ', $my_cat ) );
						}

						if( strpos( $tpl->copy_template, "[catlist=" ) !== false ) {
							$tpl->copy_template = preg_replace( "#\\[catlist=(.+?)\\](.*?)\\[/catlist\\]#ies", "check_category('\\1', '\\2', '{$row['category']}')", $tpl->copy_template );
						}
						
						if( strpos( $tpl->copy_template, "[not-catlist=" ) !== false ) {
							$tpl->copy_template = preg_replace( "#\\[not-catlist=(.+?)\\](.*?)\\[/not-catlist\\]#ies", "check_category('\\1', '\\2', '{$row['category']}', false)", $tpl->copy_template );
						}
						
						$row['category'] = intval( $row['category'] );
						
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
						
						$tpl->set( '{link-category}', $my_cat_link );
						$tpl->set( '{views}', $row['news_read'] );
						
						if( $row['output_comms'] == '1' ) {
							
							// Обработка и вывод комментариев
							

							if( ! $row['is_register'] ) {

								if( $row['gast_email'] != "" ) {
									$tpl->set( '{result-author}', "<a href=\"mailto:".htmlspecialchars($row['gast_email'], ENT_QUOTES)."\">" . stripslashes( $row['autor'] ) . "</a>" );
								} else {
									$tpl->set( '{result-author}', stripslashes( $row['autor'] ) );
								}

							} else {

								$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
								$go_page = "onclick=\"ShowProfile('" . urlencode( $row['autor'] ) . "', '" . htmlspecialchars( $go_page ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
								
								if( $config['allow_alt_url'] == "yes" ) $tpl->set( '{result-author}', "<a {$go_page} href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/\">" . $row['autor'] . "</a>" );
								else $tpl->set( '{result-author}', "<a {$go_page} href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] ) . "\">" . $row['autor'] . "</a>" );
							}
							
							if( $is_logged and $member_id['user_group'] == '1' ) $tpl->set( '{ip}', "IP: <a onclick=\"return dropdownmenu(this, event, IPMenu('" . $row['ip'] . "', '" . $lang['ip_info'] . "', '" . $lang['ip_tools'] . "', '" . $lang['ip_ban'] . "'), '190px')\" href=\"https://www.nic.ru/whois/?ip={$row['ip']}\" target=\"_blank\">{$row['ip']}</a>" );
							else $tpl->set( '{ip}', '' );

							$edit_limit = false;
							if (!$user_group[$member_id['user_group']]['edit_limit']) $edit_limit = true;
							elseif ( ($row['date'] + ($user_group[$member_id['user_group']]['edit_limit'] * 60)) > $_TIME ) {
								$edit_limit = true;
							}

							if( $is_logged AND $edit_limit AND (($member_id['name'] == $row['name'] AND $row['is_register'] AND $user_group[$member_id['user_group']]['allow_editc']) OR $user_group[$member_id['user_group']]['edit_allc']) ) {
								$tpl->set( '[com-edit]', "<a onclick=\"ajax_comm_edit('" . $row['coms_id'] . "', 'news'); return false;\" href=\"" . $config['http_home_url'] . "?do=comments&amp;action=comm_edit&amp;id=" . $row['coms_id'] . "\">" );
								$tpl->set( '[/com-edit]', "</a>" );
								$allow_comments_ajax = true;
							} else
								$tpl->set_block( "'\\[com-edit\\](.*?)\\[/com-edit\\]'si", "" );
							
							if( $is_logged AND $edit_limit AND (($member_id['name'] == $row['name'] and $row['is_register'] and $user_group[$member_id['user_group']]['allow_delc']) or $member_id['user_group'] == '1' or $user_group[$member_id['user_group']]['del_allc']) ) {
								$tpl->set( '[com-del]', "<a href=\"javascript:DeleteComments('{$row['coms_id']}', '{$dle_login_hash}')\">" );
								$tpl->set( '[/com-del]', "</a>" );
							} else
								$tpl->set_block( "'\\[com-del\\](.*?)\\[/com-del\\]'si", "" );
							
							$tpl->set_block( "'\\[fast\\](.*?)\\[/fast\\]'si", "" );
							
							$tpl->set( '{mail}', $row['email'] );
							$tpl->set( '{comment-id}', '--' );
							
							if( $row['banned'] == 'yes' or $row['name'] == '' or ! $row['is_register'] ) {
								$tpl->set( '{foto}', "{THEME}/images/noavatar.png" );
							} else {
								if( $row['foto'] ) $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto'] );
								else $tpl->set( '{foto}', "{THEME}/images/noavatar.png" );
							}
							
							if( $row['is_register'] and $row['icq'] ) $tpl->set( '{icq}', stripslashes( $row['icq'] ) );
							else $tpl->set( '{icq}', '--' );
							
							if( $row['is_register'] ) $tpl->set( '{registration}', langdate( "d.m.Y", $row['reg_date'] ) );
							else $tpl->set( '{registration}', '--' );
							
							if( $row['is_register'] and $row['news_num'] ) $tpl->set( '{news_num}', $row['news_num'] );
							else $tpl->set( '{news_num}', '0' );
							
							if( $row['is_register'] and $row['comm_num'] ) $tpl->set( '{comm_num}', $row['comm_num'] );
							else $tpl->set( '{comm_num}', '0' );
							
							$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "" );
							$tpl->set( '{result-text}', "<div id='comm-id-" . $row['coms_id'] . "'>" . $row['story'] . "</div>" );
						
						} else {
                            // Обработка дополнительных полей
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

                                $xfields_val = stripslashes($xfieldsdata[$value[0]]);
                                $tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", $xfields_val, $tpl->copy_template );
                            }
                            // Обработка дополнительных полей
							

							if( $is_logged and (($member_id['name'] == $row['autor'] and $user_group[$member_id['user_group']]['allow_edit']) or $user_group[$member_id['user_group']]['allow_all_edit']) ) {
								$tpl->set( '[edit]', "<a onclick=\"return dropdownmenu(this, event, MenuNewsBuild('" . $row['id'] . "', 'short'), '170px')\" href=\"#\">" );
								$tpl->set( '[/edit]', "</a>" );
								$allow_comments_ajax = true;
							} else {
								$tpl->set_block( "'\\[edit\\](.*?)\\[/edit\\]'si", "" );
							}


							if ($smartphone_detected) {

								if (!$config['allow_smart_format']) {
				
										$row['story'] = strip_tags( $row['story'], '<p><br><a>' );
				
								} else {
				
									if ( !$config['allow_smart_images'] ) {
					
										$row['story'] = preg_replace( "#<!--TBegin-->(.+?)<!--TEnd-->#is", "", $row['story'] );
										$row['story'] = preg_replace( "#<img(.+?)>#is", "", $row['story'] );
					
									}
					
									if ( !$config['allow_smart_video'] ) {
					
										$row['story'] = preg_replace( "#<!--dle_video_begin(.+?)<!--dle_video_end-->#is", "", $row['story'] );
										$row['story'] = preg_replace( "#<!--dle_audio_begin(.+?)<!--dle_audio_end-->#is", "", $row['story'] );
					
									}
								}
				
							}

                            if ($is_logged){

                                $fav_arr = explode (',', $member_id['favorites']);

                                if (!in_array ($row['id'], $fav_arr))
                                    $tpl->set('{favorites}',"<a id=\"fav-id-".$row['id']."\" href=\"$PHP_SELF?do=favorites&amp;doaction=add&amp;id=".$row['id']."\"><img src=\"".$config['http_home_url']."templates/{$config['skin']}/dleimages/plus_fav.gif\" onclick=\"doFavorites('".$row['id']."', 'plus'); return false;\" alt=\"".$lang['news_addfav']."\" align=\"middle\" border=\"0\" /></a>");
                                else
                            		$tpl->set('{favorites}',"<a id=\"fav-id-".$row['id']."\" href=\"$PHP_SELF?do=favorites&amp;doaction=del&amp;id=".$row['id']."\"><img src=\"".$config['http_home_url']."templates/{$config['skin']}/dleimages/minus_fav.gif\" onclick=\"doFavorites('".$row['id']."', 'minus'); return false;\" alt=\"".$lang['news_minfav']."\" align=\"middle\" border=\"0\" /></a>");

                            } else $tpl->set('{favorites}',"");
							
							$tpl->set( '{result-text}', "<div id='news-id-" . $row['id'] . "'>" . $row['story'] . "</div>" );
						
						}
						
						$tpl->set( '{search-id}', $search_id );
						
						if( $showposts == 0 ) {
							// Показать короткую новость
							$tpl->set_block( "'\\[shortresult\\].*?\\[/shortresult\\]'si", "" );
							$tpl->set( '[fullresult]', "" );
							$tpl->set( '[/fullresult]', "" );
							$alt_text = $row_title;
						} else {
							// Показать только заголовок
							$tpl->set_block( "'\\[fullresult\\].*?\\[/fullresult\\]'si", "" );
							$tpl->set( '[shortresult]', "" );
							$tpl->set( '[/shortresult]', "" );
							$alt_text = create_description( $row['story'] );
						}
						
						if( $config['allow_alt_url'] == "yes" ) {
							
							if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
								
								if( $row['category'] and $config['seo_type'] == 2 ) {
									
									$full_link = $config['http_home_url'] . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
								
								} else {
									
									$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
								
								}
							
							} else {
								
								$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['newsdate'] ) . $row['alt_name'] . ".html";
							}
						
						} else {
							
							$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
						
						}
						
						$tpl->set( '[result-link]', "<a href=\"" . $full_link . "\" >" );
						$tpl->set( '[/result-link]', "</a>" );

						if( $cat_info[$row['category']]['icon'] ) {
							
							$tpl->set( '{category-icon}', $cat_info[$row['category']]['icon'] );
						
						} else {
							
							$tpl->set( '{category-icon}', "{THEME}/dleimages/no_icon.gif" );
						
						}
				
						if ( $row['category'] )
							$tpl->set( '{category-url}', $config['http_home_url'] . get_url( $row['category'] ) . "/" );
						else
							$tpl->set( '{category-url}', "#" );
						
						if( $row['output_comms'] == '1' ) {
							// Для вывода комментариев
							$tpl->set_block( "'\\[searchposts\\].*?\\[/searchposts\\]'si", "" );
							$tpl->set( '[searchcomments]', "<div id='comment-id-{$row['coms_id']}'>" );
							$tpl->set( '[/searchcomments]', "</div>" );
						} else {
							// Для вывода статей
							$tpl->set_block( "'\\[searchcomments\\].*?\\[/searchcomments\\]'si", "" );
							$tpl->set( '[searchposts]', "" );
							$tpl->set( '[/searchposts]', "" );
						}
						
						$tpl->compile( 'content' );

						if( $user_group[$member_id['user_group']]['allow_hide'] ) $tpl->result['content'] = str_replace( "[hide]", "", str_replace( "[/hide]", "", $tpl->result['content']) );
						else $tpl->result['content'] = preg_replace ( "#\[hide\](.+?)\[/hide\]#is", "<div class=\"quote\">" . $lang['news_regus'] . "</div>", $tpl->result['content'] );
						
						if( $config['files_allow'] == "yes" ) {
							if( strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
								$tpl->result['content'] = show_attach( $tpl->result['content'], $attachments );
							}
						}
					} // Результаты поиска в статьях и комментариях
				} // while
				

				$tpl->clear();
				$db->free( $sql_result );
			}
		}
	}
	
	$tpl->clear();
	
	//####################################################################################################################
	//         Навигация по новостям
	//####################################################################################################################
	if( $found_result > 0 ) {
		$tpl->load_template( 'navigation.tpl' );
		
		//----------------------------------
		// Previous link
		//----------------------------------
		if( isset( $search_start ) and $search_start != "" and $search_start > 0 ) {
			$prev = $search_start / $config['search_number'];
			$prev_page = "<a name=\"prevlink\" id=\"prevlink\" onclick=\"javascript:list_submit($prev); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", $prev_page . "\\1</a>" );
		
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}
		
		//----------------------------------
		// Pages
		//----------------------------------
		if( $config['search_number'] ) {
			$pages_count = @ceil( $count_result / $config['search_number'] );
			$pages_start_from = 0;
			$pages = "";

			for($j = 1; $j <= $pages_count; $j ++) {
				if( $pages_start_from != $search_start ) {
					$pages .= "<a onclick=\"javascript:list_submit($j); return(false)\" href=\"#\">$j</a> ";
				} else {
					$pages .= " <span>$j</span> ";
				}
				$pages_start_from += $config['search_number'];
			}

			$tpl->set( '{pages}', $pages );
		}
		
		//----------------------------------
		// Next link
		//----------------------------------
		if( $config['search_number'] < $count_result and $to_num < $count_result ) {
			$next_page = $to_num / $config['search_number'] + 1;
			$next = "<a name=\"nextlink\" id=\"nextlink\" onclick=\"javascript:list_submit($next_page); return(false)\" href=\"#\">";
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", $next . "\\1</a>" );
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}
		
		if( ! $no_prev or ! $no_next ) {
			$tpl->compile( 'content' );
		}
		
		$tpl->clear();
	}
}
?>