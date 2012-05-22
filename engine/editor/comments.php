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
 Файл: comments.php
-----------------------------------------------------
 Назначение: WYSIWYG для комментариев
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if ($user_group[$member_id['user_group']]['allow_url']) $link_icon = "\"LinkDialog\", \"DLELeech\","; else $link_icon = "";
if ($user_group[$member_id['user_group']]['allow_image']) $link_icon .= "\"ImageDialog\",";

$wysiwyg = <<<HTML
<style type="text/css">
.wseditor table td { 
	padding:0px;
	border:0;
}
</style>
    <div class="wseditor"><textarea id="comments" name="comments" rows="10" cols="50">{$text}</textarea>
<script type="text/javascript">
    var wscomm = new InnovaEditor("wscomm");

    wscomm.width = 540;
    wscomm.height = 250;
    wscomm.css = "{$config['http_home_url']}engine/editor/scripts/style/default.css";
    wscomm.useBR = false;
    wscomm.useDIV = false;
    wscomm.groups = [
			["grpEdit1", "", ["Bold", "Italic", "Underline", "Strikethrough", "ForeColor"]],
			["grpEdit2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull", "Bullets", "Numbering"]],
			["grpEdit3", "", [{$link_icon}"DLESmiles", "DLEQuote", "DLEHide"]]
        ];
    wscomm.arrCustomButtons.push(["DLESmiles", "modalDialog('{$config['http_home_url']}engine/editor/emotions.php',250,160)", "{$lang['bb_t_emo']}", "btnEmoticons.gif"]);
    wscomm.arrCustomButtons.push(["DLEQuote", "DLEcustomTag('[quote]', '[/quote]')", "{$lang['bb_t_quote']}", "dle_quote.gif"]);
    wscomm.arrCustomButtons.push(["DLEHide", "DLEcustomTag('[hide]', '[/hide]')", "{$lang['bb_t_hide']}", "dle_hide.gif"]);
    wscomm.arrCustomButtons.push(["DLELeech", "DLEcustomTag('[leech=http://]', '[/leech]')", "{$lang['bb_t_leech']}", "dle_leech.gif"]);

    wscomm.REPLACE("comments");
</script></div>
HTML;

if ( $allow_subscribe ) $wysiwyg .= "<br /><input type=\"checkbox\" name=\"allow_subscribe\" id=\"allow_subscribe\" value=\"1\" /><label for=\"allow_subscribe\">&nbsp;&nbsp;" . $lang['c_subscribe'] . "</label><br />";


?>