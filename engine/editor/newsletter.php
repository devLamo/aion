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
 Файл: newsletter.php
-----------------------------------------------------
 Назначение: WYSIWYG для рассылки
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

echo <<<HTML
<tr>
	<td width="140" valign="top">
	<br />{$lang['nl_message']}</td>
	<td>
<script type="text/javascript" src="engine/editor/scripts/language/{$lang['wysiwyg_language']}/editor_lang.js"></script>
<script type="text/javascript" src="engine/editor/scripts/innovaeditor.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($){
	create_editor('');
});

function create_editor( root ) {
	
	oUtil.initializeEditor("wysiwygeditor",  {
		width: "98%", 
		height: "400", 
		css: root + "engine/editor/scripts/style/default.css",
		useBR: false,
		useDIV: false,
		groups:[
			["grpEdit1", "", ["Bold", "Italic", "Underline", "Strikethrough", "ForeColor", "BackColor", "RemoveFormat"]],
			["grpEdit2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyFull", "Bullets", "Numbering", "Indent", "Outdent"]],
			["grpEdit3", "", ["DLESmiles", "LinkDialog", "ImageDialog", "FlashDialog", "DLETypograf"]],
			["grpEdit4", "", ["Undo", "Redo", "SourceDialog"]]
	    ],
		arrCustomButtons:[
			["DLESmiles", "modalDialog('"+ root +"engine/editor/emotions.php',250,160)", "{$lang['bb_t_emo']}", "btnEmoticons.gif"],
			["DLETypograf", "tag_typograf()", "{$lang['bb_t_t']}", "dle_tt.gif"]
		]
		}
	);	
};

function tag_typograf() {

	ShowLoading('');

	var oEditor = oUtil.oEditor;
	var obj = oUtil.obj;

	obj.saveForUndo();
    oEditor.focus();
    obj.setFocus();

	var txt = obj.getXHTMLBody();

	$.post("engine/ajax/typograf.php", {txt: txt}, function(data){
	
		HideLoading('');
	
		obj.loadHTML(data); 
	
	});

};
</script>
    <textarea id="message" name="message" class="wysiwygeditor" style="width:98%;height:300px;"></textarea><br><br>{$lang['nl_info_1']} <b>{$lang['nl_info_2']}</b></td>
	</tr>
HTML;

?>