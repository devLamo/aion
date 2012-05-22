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
 Файл: question.php
-----------------------------------------------------
 Назначение: Настройка вопросов и ответов
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}

if ($_POST['action'] == "addquestion") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$question = $db->safesql( strip_tags($_POST['question']) );
	$answer = $db->safesql( strip_tags(str_replace( "\r", "", $_POST['answer'] )) );

	$db->query( "INSERT INTO " . PREFIX . "_question (question, answer) VALUES ('{$question}', '{$answer}')" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '84', '".htmlspecialchars($question, ENT_QUOTES)."')" );

	header( "Location: ?mod=question" ); die();
}

if ($_POST['action'] == "editquestion") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$id = intval($_POST['id']);
	$question = $db->safesql( strip_tags($_POST['question']) );
	$answer = $db->safesql( strip_tags(str_replace( "\r", "", $_POST['answer'] )) );

	$db->query( "UPDATE " . PREFIX . "_question SET question='{$question}', answer='{$answer}' WHERE id='{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '83', '".htmlspecialchars($question, ENT_QUOTES)."')" );


	header( "Location: ?mod=question" ); die();
}


if ($_GET['action'] == "delete") {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}

	$id = intval($_GET['id']);

	$db->query( "DELETE FROM " . PREFIX . "_question WHERE id = '{$id}'" );
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '85', '{$id}')" );

	header( "Location: ?mod=question" ); die();
}

echoheader("", "");

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_question']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
HTML;

$db->query("SELECT SQL_CALC_FOUND_ROWS * FROM " . PREFIX . "_question ORDER BY id DESC");

$entries = "";

while($row = $db->get_row()){

		$row['question'] = htmlspecialchars( stripslashes($row['question']), ENT_QUOTES );
		$row['answer'] = htmlspecialchars( stripslashes($row['answer']), ENT_QUOTES );

		$entries .= "<tr>
        <td align=\"center\" style=\"width:100px;\"><a uid=\"{$row['id']}\" class=\"editlink\" href=\"?mod=question\"><img style=\"vertical-align: middle;border:none;\" alt=\"{$lang['word_ledit']}\" title=\"{$lang['word_ledit']}\" src=\"engine/skins/images/notepad.png\" /></a>&nbsp;&nbsp;<a uid=\"{$row['id']}\" class=\"dellink\" href=\"?mod=question\"><img style=\"vertical-align: middle;border:none;\" alt=\"{$lang['word_ldel']}\" title=\"{$lang['word_ldel']}\" src=\"engine/skins/images/delete.png\" /></a></td>
        <td style=\"padding:4px;\" nowrap><div id=\"question_{$row['id']}\">{$row['question']}</div><div id=\"answer_{$row['id']}\" style=\"display:none\">{$row['answer']}</div></td>
        </tr>
        <tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=4></td></tr>";

}

$result_count = $db->super_query("SELECT FOUND_ROWS() as count");

if ($result_count['count']) {

echo <<<HTML
<table width="100%" id="qa-list">
	{$entries}
</table>

<script type="text/javascript">
$(function(){

		$("#qa-list").delegate("tr", "hover", function(){
		  $(this).toggleClass("hoverRow");
		});

		$('.editlink').click(function(){

			var id = $(this).attr('uid');
			var qa = $('#question_'+id).html();
			var ans = $('#answer_'+id).html();

			var b = {};
			b[dle_act_lang[3]] = function() { 
				$(this).dialog('close');						
			};
	
			b['{$lang['user_save']}'] = function() { 
				if ( $('#question').val().length < 1 || $('#answer').val().length < 1) {
					if ( $('#question').val().length < 1) { $('#question').addClass('ui-state-error'); }
					if ( $('#answer').val().length < 1) { $('#answer').addClass('ui-state-error'); }
				} else {
					document.saveform.submit();
		
				}				
			};
	
			$('#dlepopup').remove();
							
			$('body').append("<div id='dlepopup' title='{$lang['opt_question_2']}' style='display:none'><form action=\"?mod=question\" method=\"POST\" name=\"saveform\" id=\"saveform\">{$lang['opt_question_3']}&nbsp;<input type='text' name='question' id='question' class='ui-widget-content ui-corner-all' style='width:420px; padding: .4em;' value=\""+qa+"\"/><br /><br />{$lang['opt_question_4']}<br /><textarea name='answer' id='answer' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'>"+ans+"</textarea><input type=\"hidden\" name=\"mod\" value=\"question\"><input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\"><input type=\"hidden\" name=\"action\" value=\"editquestion\"><input type=\"hidden\" name=\"id\" value=\""+id+"\"></form></div>");
							
			$('#dlepopup').dialog({
				autoOpen: true,
				width: 500,
				buttons: b
			});

			return false;
		});

		$('.dellink').click(function(){

			var id = $(this).attr('uid');
			var qa = $('#question_'+id).html();

		    DLEconfirm( '{$lang['opt_question_5']} <b>&laquo;'+qa+'&raquo;</b>', '{$lang['p_confirm']}', function () {

				document.location='?mod=question&user_hash={$dle_login_hash}&action=delete&id=' + id + '';

			} );

			return false;
		});

});
</script>
HTML;


} else {

echo <<<HTML
<table width="100%">
    <tr>
        <td style="padding:2px;height:50px;"><div align="center">{$lang['opt_question_1']}<br /><br> <a class="main" href="javascript:history.go(-1)">{$lang['func_msg']}</a></div></td>
    </tr>
</table>
HTML;

}

echo <<<HTML
<div class="unterline"></div>
<input type="button" class="btn btn-primary" value="{$lang['btn_question']}" name="btn-new" id="btn-new" style="width:180px;">
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
<script language="javascript" type="text/javascript">  
<!--
$(function(){

	$('#btn-new').click(function(){

		var b = {};
		b[dle_act_lang[3]] = function() { 
			$(this).dialog('close');						
		};

		b['{$lang['user_save']}'] = function() { 
			if ( $('#question').val().length < 1 || $('#answer').val().length < 1) {
				if ( $('#question').val().length < 1) { $('#question').addClass('ui-state-error'); }
				if ( $('#answer').val().length < 1) { $('#answer').addClass('ui-state-error'); }
			} else {
				document.saveform.submit();
	
			}				
		};

		$('#dlepopup').remove();
						
		$('body').append("<div id='dlepopup' title='{$lang['opt_question_2']}' style='display:none'><form action=\"?mod=question\" method=\"POST\" name=\"saveform\" id=\"saveform\">{$lang['opt_question_3']}&nbsp;<input type='text' name='question' id='question' class='ui-widget-content ui-corner-all' style='width:420px; padding: .4em;' value=''/><br /><br />{$lang['opt_question_4']}<br /><textarea name='answer' id='answer' class='ui-widget-content ui-corner-all' style='width:97%;height:100px; padding: .4em;'></textarea><input type=\"hidden\" name=\"mod\" value=\"question\"><input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\"><input type=\"hidden\" name=\"action\" value=\"addquestion\"></form></div>");
						
		$('#dlepopup').dialog({
			autoOpen: true,
			width: 500,
			buttons: b
		});

	});
});
//-->
</script>
HTML;
echofooter();
?>