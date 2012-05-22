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
 Файл: inserttag.php
-----------------------------------------------------
 Назначение: bbcodes
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$i = 0;
$smiles = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\"><tr>";

$smilies = explode(",", $config['smilies']);
foreach($smilies as $smile) {

	$i++; $smile = trim($smile);

	$smiles .= "<td style=\"padding:2px;\" align=\"center\"><a href=\"#\" onClick=\"dle_smiley(':$smile:'); return false;\"><img style=\"border: none;\" alt=\"$smile\" src=\"".$config['http_home_url']."engine/data/emoticons/$smile.gif\" /></a></td>";

	if ($i%4 == 0) $smiles .= "</tr><tr>";

}

$smiles .= "</tr></table>";

if ($user_group[$member_id['user_group']]['allow_image_upload']) {

      $image_upload = "<div class=\"editor_button\" onclick=\"image_upload()\"><img title=\"$lang[bb_t_up]\" src=\"engine/skins/bbcodes/images/upload.gif\" width=\"23\" height=\"25\" border=\"0\"></div>";

} else $image_upload = "";

if ($mod != "editnews") {
	$row['autor'] = $member_id['name'];
}

$p_name = urlencode($row['autor']);

$typograf = "<div id=\"b_typograf\" class=\"editor_button\" onclick=\"tag_typograf(); return false;\"><img title=\"$lang[bb_t_t]\" src=\"engine/skins/bbcodes/images/typograf.gif\" width=\"23\" height=\"25\" border=\"0\"></div>";

$image_align = array ();
$image_align[$config['image_align']] = "selected";

$bb_js = <<<HTML
<SCRIPT type=text/javascript>
<!--

var uagent    = navigator.userAgent.toLowerCase();
var is_safari = ( (uagent.indexOf('safari') != -1) || (navigator.vendor == "Apple Computer, Inc.") );
var is_opera  = (uagent.indexOf('opera') != -1);
var is_ie     = ( (uagent.indexOf('msie') != -1) && (!is_opera) && (!is_safari) );
var is_ie4    = ( (is_ie) && (uagent.indexOf("msie 4.") != -1) );

var is_win    =  ( (uagent.indexOf("win") != -1) || (uagent.indexOf("16bit") !=- 1) );
var ua_vers   = parseInt(navigator.appVersion);
	
var text_enter_url       = "$lang[bb_url]";
var text_enter_size       = "$lang[bb_flash]";
var text_enter_flash       = "$lang[bb_flash_url]";
var text_enter_page      = "$lang[bb_page]";
var text_enter_url_name  = "$lang[bb_url_name]";
var text_enter_page_name = "$lang[bb_page_name]";
var text_enter_image    = "$lang[bb_image]";
var text_enter_email    = "$lang[bb_email]";
var text_enter_list     = "$lang[bb_list_item]";
var text_code           = "$lang[bb_code]";
var text_quote          = "$lang[bb_quote]";
var text_alt_image      = "$lang[bb_alt_image]";
var error_no_url        = "$lang[bb_no_url]";
var error_no_title      = "$lang[bb_no_title]";
var error_no_email      = "$lang[bb_no_email]";
var prompt_start        = "$lang[bb_prompt_start]";
var img_title   		= "$lang[bb_img_title]";
var img_align  	        = "{$lang['images_align']}";
var img_align_sel  	    = "<select name='dleimagealign' id='dleimagealign' class='ui-widget-content ui-corner-all'><option value='' {$image_align[0]}>{$lang['opt_sys_no']}</option><option value='left' {$image_align['left']}>{$lang['images_left']}</option><option value='right' {$image_align['right']}>{$lang['images_right']}</option><option value='center' {$image_align['center']}>{$lang['images_center']}</option></select>";
var email_title  	    = "$lang[bb_email_title]";
var dle_prompt          = "$lang[p_prompt]";
var bb_t_emo  	        = "{$lang['bb_t_emo']}";
var bb_t_col  	        = "{$lang['bb_t_col']}";

var ie_range_cache = '';
var list_open_tag = '';
var list_close_tag = '';
var listitems = '';

var selField  = "short_story";

var bbtags   = new Array();

var fombj    = document.forms[0];

function setFieldName(which)
{

   if (which != selField)
   {
       selField = which;

   }
}

function emoticon(theSmilie)
{
	doInsert(" " + theSmilie + " ", "", false);
}

function pagebreak()
{
	doInsert("{PAGEBREAK}", "", false);
}

function simpletag(thetag)
{
	doInsert("[" + thetag + "]", "[/" + thetag + "]", true);
}

function pagelink()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel = '$lang[bb_bb_page]';
    }

	DLEprompt(text_enter_page, "1", dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_page_name, thesel, dle_prompt, function (r) {

			doInsert("[page="+enterURL+"]"+r+"[/page]", "", false);
			ie_range_cache = null;
	
		});

	});
}

function tag_url()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='My Webpage';
    }

	DLEprompt(text_enter_url, "http://", dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_url_name, thesel, dle_prompt, function (r) {

			doInsert("[url="+enterURL+"]"+r+"[/url]", "", false);
			ie_range_cache = null;
	
		});

	});
}


function tag_leech()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='My Webpage';
    }

	DLEprompt(text_enter_url, "http://", dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_url_name, thesel, dle_prompt, function (r) {

			doInsert("[leech="+enterURL+"]"+r+"[/leech]", "", false);
			ie_range_cache = null;
	
		});

	});
}

function tag_video()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[video="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_audio()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[audio="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_youtube()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_url, thesel, dle_prompt, function (r) {

		doInsert("[media="+r+"]", "", false);
		ie_range_cache = null;
	
	});
}

function tag_flash()
{
	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='http://';
    }

	DLEprompt(text_enter_flash, thesel, dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(text_enter_size, "425,264", dle_prompt, function (r) {

			doInsert("[flash="+r+"]"+enterURL+"[/flash]", "", false);
			ie_range_cache = null;
	
		});

	});

}

function tag_list(type)
{

	list_open_tag = type == 'ol' ? '[ol=1]\\n' : '[list]\\n';
	list_close_tag = type == 'ol' ? '[/ol]' : '[/list]';
	listitems = '';

	var thesel = get_sel(eval('fombj.'+ selField))

    if (!thesel) {
        thesel ='';
    }

	insert_list( thesel );

}

function insert_list( thesel )
{
	DLEprompt(text_enter_list, thesel, dle_prompt, function (r) {

		if (r != '') {

			listitems += '[*]' + r + '\\n';
			insert_list('');

		} else {

			if( listitems )
			{
				doInsert(list_open_tag + listitems + list_close_tag, "", false);
				ie_range_cache = null;
			}
		}

	}, true);

}

function tag_image()
{

	var thesel = get_sel(eval('fombj.'+ selField));

    if (!thesel) {
        thesel ='http://';
    }

	DLEimagePrompt(thesel, function (imageurl, imagealt, imagealign) {

		var imgoption = "";

		if (imagealt != "") { 

			imgoption = "|"+imagealt;

		}

		if (imagealign != "" && imagealign != "center") { 

			imgoption = imagealign+imgoption;

		}

		if (imgoption != "" ) {

			imgoption = "="+imgoption;

		}

		if (imagealign == "center") {
			doInsert("[center][img"+imgoption+"]"+imageurl+"[/img][/center]", "", false);
		}
		else {
			doInsert("[img"+imgoption+"]"+imageurl+"[/img]", "", false);
		}

		ie_range_cache = null;

	});
};

function DLEimagePrompt( d, callback ){

	var b = {};

	b[dle_act_lang[3]] = function() { 
					$(this).dialog("close");						
			    };

	b[dle_act_lang[2]] = function() { 
					if ( $("#dle-promt-text").val().length < 1) {
						 $("#dle-promt-text").addClass('ui-state-error');
					} else {
						var imageurl = $("#dle-promt-text").val();
						var imagealt = $("#dle-image-alt").val();
						var imagealign = $("#dleimagealign").val();
						$(this).dialog("close");
						$("#dlepopup").remove();
						if( callback ) callback( imageurl, imagealt, imagealign );	
					}				
				};

	$("#dlepopup").remove();

	$("body").append("<div id='dlepopup' title='" + dle_prompt + "' style='display:none'><br />"+ text_enter_image +"<br /><input type='text' name='dle-promt-text' id='dle-promt-text' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value='" + d + "'/><br /><br />"+ text_alt_image +"<br /><input type='text' name='dle-image-alt' id='dle-image-alt' class='ui-widget-content ui-corner-all' style='width:97%; padding: .4em;' value=''/><br /><br />"+img_align+"&nbsp;"+img_align_sel+"</div>");

	$('#dlepopup').dialog({
		autoOpen: true,
		width: 500,
		buttons: b
	});

	if (d.length > 0) {
		$("#dle-promt-text").select().focus();
	} else {
		$("#dle-promt-text").focus();
	}
};

function tag_email()
{
	var thesel = get_sel(eval('fombj.'+ selField))
		
	if (!thesel) {
		   thesel ='';
	}

	DLEprompt(text_enter_email, "", dle_prompt, function (r) {

		var enterURL = r;

		DLEprompt(email_title, thesel, dle_prompt, function (r) {

			doInsert("[email="+enterURL+"]"+r+"[/email]", "", false);
		    ie_range_cache = null;
	
		});

	});
}

function doInsert(ibTag, ibClsTag, isSingle)
{
	var isClose = false;
	var obj_ta = eval('fombj.'+ selField);

	if ( (ua_vers >= 4) && is_ie && is_win)
	{
		if (obj_ta.isTextEdit)
		{
			obj_ta.focus();
			var sel = document.selection;
			var rng = ie_range_cache ? ie_range_cache : sel.createRange();
			rng.colapse;
			if((sel.type == "Text" || sel.type == "None") && rng != null)
			{
				if(ibClsTag != "" && rng.text.length > 0)
					ibTag += rng.text + ibClsTag;
				else if(isSingle)
					ibTag += rng.text + ibClsTag;
	
				rng.text = ibTag;
			}
		}
		else
		{
				obj_ta.value += ibTag + ibClsTag;
			
		}
		rng.select();
		ie_range_cache = null;

	}
	else if ( obj_ta.selectionEnd != null)
	{ 
		var ss = obj_ta.selectionStart;
		var st = obj_ta.scrollTop;
		var es = obj_ta.selectionEnd;
		
		var start  = (obj_ta.value).substring(0, ss);
		var middle = (obj_ta.value).substring(ss, es);
		var end    = (obj_ta.value).substring(es, obj_ta.textLength);
		
		if(!isSingle) middle = "";
		
		if (obj_ta.selectionEnd - obj_ta.selectionStart > 0)
		{
			middle = ibTag + middle + ibClsTag;
		}
		else
		{
			middle = ibTag + middle + ibClsTag;
		}
		
		obj_ta.value = start + middle + end;
		
		var cpos = ss + (middle.length);
		
		obj_ta.selectionStart = cpos;
		obj_ta.selectionEnd   = cpos;
		obj_ta.scrollTop      = st;


	}
	else
	{
		obj_ta.value += ibTag + ibClsTag;
	}

	obj_ta.focus();
	return isClose;
}

function ins_color( buttonElement )
{

	document.getElementById(selField).focus();

	if ( is_ie )
	{
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
	}

	$("#cp").remove();

	$("body").append("<div id='cp' title='" + bb_t_col + "' style='display:none'><br /><iframe width=\"154\" height=\"104\" src=\"engine/skins/bbcodes/color.html\" frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"no\"></iframe></div>");

	$('#cp').dialog({
		autoOpen: true,
		width: 180
	});
}
function setColor(color)
{
	doInsert("[color=" +color+ "]", "[/color]", true );
	$('#cp').dialog("close");
}
function ins_emo( buttonElement )
{
		document.getElementById(selField).focus();

		if ( is_ie )
		{
			document.getElementById(selField).focus();
			ie_range_cache = document.selection.createRange();
		}

		$("#dle_emo").remove();

		$("body").append("<div id='dle_emo' title='" + bb_t_emo + "' style='display:none'>"+ document.getElementById('dle_emos').innerHTML +"</div>");

		var w = '300';
		var h = 'auto';

		if ( $('#dle_emos').width() >= 450 )  w = '505';
		if ( $('#dle_emos').height() > 300 )  h = '340';

		$('#dle_emo').dialog({
				autoOpen: true,
				width: w,
				height: h
			});

};

function dle_smiley ( text ){
	doInsert(' ' + text + ' ', '', false);

	$('#dle_emo').dialog("close");
	ie_range_cache = null;
};
function image_upload()
{
	if ( is_ie )
	{
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
	}

	media_upload ( selField, '{$p_name}', '{$id}', 'no');

}
function insert_font(value, tag)
{
    if (value == 0)
    {
    	return;
	} 

	doInsert("[" +tag+ "=" +value+ "]", "[/" +tag+ "]", true );
    fombj.bbfont.selectedIndex  = 0;
    fombj.bbsize.selectedIndex  = 0;
}

function tag_typograf()
	{

		ShowLoading('');

		$.post("engine/ajax/typograf.php", { txt: document.getElementById( selField ).value}, function(data){
	
			HideLoading('');
	
			$('#' + selField).val(data); 
	
		});

	}

function get_sel(obj)
{

 if (document.selection) 
 {

   if ( is_ie )
   {
		document.getElementById(selField).focus();
		ie_range_cache = document.selection.createRange();
   }

   var s = document.selection.createRange(); 
   if (s.text)
   {
	 return s.text;
   }
 }
 else if (typeof(obj.selectionStart)=="number")
 {
   if (obj.selectionStart!=obj.selectionEnd)
   {
     var start = obj.selectionStart;
     var end = obj.selectionEnd;
	 return (obj.value.substr(start,end-start));
   }
 }

 return false;

};
-->
</SCRIPT>
HTML;

$bb_panel = <<<HTML
<div style="width:98%; height:50px; border:1px solid #BBB; background-image:url('engine/skins/bbcodes/images/bg.gif');">
<div id="b_b" class="editor_button" onclick="simpletag('b')"><img title="$lang[bb_t_b]" src="engine/skins/bbcodes/images/b.gif" width="23" height="25" border="0"></div>
<div id="b_i" class="editor_button" onclick="simpletag('i')"><img title="$lang[bb_t_i]" src="engine/skins/bbcodes/images/i.gif" width="23" height="25" border="0"></div>
<div id="b_u" class="editor_button" onclick="simpletag('u')"><img title="$lang[bb_t_u]" src="engine/skins/bbcodes/images/u.gif" width="23" height="25" border="0"></div>
<div id="b_s" class="editor_button" onclick="simpletag('s')"><img title="$lang[bb_t_s]" src="engine/skins/bbcodes/images/s.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div class="editor_button" onclick=tag_image()><img title="$lang[bb_b_img]" src="engine/skins/bbcodes/images/image.gif" width="23" height="25" border="0"></div>
{$image_upload}
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_emo" class="editor_button"  onclick="ins_emo(this);" style="width:33px;" align="center"><img title="$lang[bb_t_emo]" src="engine/skins/bbcodes/images/emo.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div class="editor_button"  onclick="tag_url()"><img title="$lang[bb_t_url]" src="engine/skins/bbcodes/images/link.gif" width="23" height="25" border="0"></div>
<div class="editor_button"  onclick="tag_leech()"><img title="$lang[bb_t_leech]" src="engine/skins/bbcodes/images/leech.gif" width="23" height="25" border="0"></div>
<div class="editor_button"  onclick="tag_email()"><img title="$lang[bb_t_m]" src="engine/skins/bbcodes/images/email.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div class="editor_button" onclick="tag_video()"><img title="$lang[bb_t_video]" src="engine/skins/bbcodes/images/mp.gif" width="23" height="25" border="0"></div>
<div class="editor_button" onclick="tag_audio()"><img title="$lang[bb_t_audio]" src="engine/skins/bbcodes/images/mp3.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_hide" class="editor_button" onclick="simpletag('hide')"><img title="$lang[bb_t_hide]" src="engine/skins/bbcodes/images/hide.gif" width="23" height="25" border="0"></div>
<div id="b_quote" class="editor_button" onclick="simpletag('quote')"><img title="$lang[bb_t_quote]" src="engine/skins/bbcodes/images/quote.gif" width="23" height="25" border="0"></div>
<div id="b_code" class="editor_button" onclick="simpletag('code')"><img title="$lang[bb_t_code]" src="engine/skins/bbcodes/images/code.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div class="editor_button" onclick="pagebreak()"><img title="$lang[bb_t_br]" src="engine/skins/bbcodes/images/pbreak.gif" width="23" height="25" border="0"></div>
<div class="editor_button" onclick="pagelink()"><img title="$lang[bb_t_p]" src="engine/skins/bbcodes/images/page.gif" width="23" height="25" border="0"></div>
<div><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div class="editor_button" style="padding-top:3px;width:140px;"><select name="bbfont" onchange="insert_font(this.options[this.selectedIndex].value, 'font')"><option value='0'>{$lang['bb_t_font']}</option><option value='Arial'>Arial</option><option value='Arial Black'>Arial Black</option><option value='Century Gothic'>Century Gothic</option><option value='Courier New'>Courier New</option><option value='Georgia'>Georgia</option><option value='Impact'>Impact</option><option value='System'>System</option><option value='Tahoma'>Tahoma</option><option value='Times New Roman'>Times New Roman</option><option value='Verdana'>Verdana</option></select></div>
<div class="editor_button" style="padding-top:3px;width:70px;"><select name="bbsize" onchange="insert_font(this.options[this.selectedIndex].value, 'size')"><option value='0'>{$lang['bb_t_size']}</option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option></select></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_left" class="editor_button" onclick="simpletag('left')"><img title="$lang[bb_t_l]" src="engine/skins/bbcodes/images/l.gif" width="23" height="25" border="0"></div>
<div id="b_center" class="editor_button" onclick="simpletag('center')"><img title="$lang[bb_t_c]" src="engine/skins/bbcodes/images/c.gif" width="23" height="25" border="0"></div>
<div id="b_right"class="editor_button" onclick="simpletag('right')"><img title="$lang[bb_t_r]" src="engine/skins/bbcodes/images/r.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_color" class="editor_button" onclick="ins_color(this);"><img title="$lang[bb_t_color]" src="engine/skins/bbcodes/images/color.gif" width="23" height="25" border="0"></div>
<div id="b_spoiler" class="editor_button" onclick="simpletag('spoiler')"><img title="$lang[bb_t_spoiler]" src="engine/skins/bbcodes/images/spoiler.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_flash" class="editor_button" onclick="tag_flash()"><img title="$lang[bb_t_flash]" src="engine/skins/bbcodes/images/flash.gif" width="23" height="25" border="0"></div>
<div id="b_youtube" class="editor_button" onclick="tag_youtube()"><img title="$lang[bb_t_youtube]" src="engine/skins/bbcodes/images/youtube.gif" width="23" height="25" border="0"></div>
{$typograf}
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
<div id="b_list" class="editor_button" onclick="tag_list('list')"><img title="$lang[bb_t_list1]" src="engine/skins/bbcodes/images/list.gif" width="23" height="25" border="0"></div>
<div id="b_ol" class="editor_button" onclick="tag_list('ol')"><img title="$lang[bb_t_list2]" src="engine/skins/bbcodes/images/ol.gif" width="23" height="25" border="0"></div>
<div class="editor_button"><img src="engine/skins/bbcodes/images/brkspace.gif" width="5" height="25" border="0"></div>
</div>
<div id="dle_emos" style="display: none;" title="{$lang['bb_t_emo']}"><div style="overflow: auto;">{$smiles}</div></div>
HTML;

$bb_code = $bb_js.$bb_panel;
?>