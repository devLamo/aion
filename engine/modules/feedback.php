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
 Файл: feedback.php
-----------------------------------------------------
 Назначение: обратная связь
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

	if( isset( $_POST['send'] ) ) {
		$stop = "";
		
		if( $is_logged ) {

			$name = $member_id['name'];
			$email = $member_id['email'];

		} else {
			
			$name = $db->safesql( strip_tags( $_POST['name'] ) );
			$not_allow_symbol = array ("\x22", "\x60", "\t", '\n', '\r', "\n", "\r", '\\', ",", "/", "¬", "#", ";", ":", "~", "[", "]", "{", "}", ")", "(", "*", "^", "%", "$", "<", ">", "?", "!", '"', "'" );
			$email = $db->safesql(trim( str_replace( $not_allow_symbol, '', strip_tags( stripslashes( $_POST['email'] ) ) ) ) );

			
			$db->query( "SELECT name from " . USERPREFIX . "_users where LOWER(name) = '" . strtolower( $name ) . "' OR LOWER(email) = '" . strtolower( $email ) . "'" );
			
			if( $db->num_rows() > 0 ) {
				$stop = $lang['news_err_7'];
			}
			
			$name = strip_tags( stripslashes( $_POST['name'] ) );
		
		}
		
		$subject = strip_tags( stripslashes( $_POST['subject'] ) );
		$message = stripslashes( $_POST['message'] );
		$recip = intval( $_POST['recip'] );

		if( !$user_group[$member_id['user_group']]['allow_feed'] )	{

			$recipient = $db->super_query( "SELECT name, email, fullname FROM " . USERPREFIX . "_users WHERE user_id='" . $recip . "' AND user_group = '1'" );

		} else {

			$recipient = $db->super_query( "SELECT name, email, fullname FROM " . USERPREFIX . "_users WHERE user_id='" . $recip . "' AND allow_mail = '1'" );

		}			


		if( empty( $recipient['fullname'] ) ) $recipient['fullname'] = $recipient['name'];

		if (!$recipient['name']) $stop .= $lang['feed_err_8'];

		if( $user_group[$member_id['user_group']]['max_mail_day'] ) {
		
			$this_time = time() + ($config['date_adjust'] * 60) - 86400;
			$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='2'" );

			if ( !$is_logged ) $check_user = $_IP; else $check_user = $member_id['name'];
	
			$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_sendlog WHERE user = '{$check_user}' AND flag='2'");
		
			if( $row['count'] >=  $user_group[$member_id['user_group']]['max_mail_day'] ) {
		
				$stop .= str_replace('{max}', $user_group[$member_id['user_group']]['max_mail_day'], $lang['feed_err_9']);
			}
		}
		
		if( empty( $name ) OR dle_strlen($name, $config['charset']) > 100 ) {
			$stop .= $lang['feed_err_1'];
		}
		
		if( empty( $email ) OR dle_strlen($email, $config['charset']) > 50 OR @count(explode("@", $email)) != 2) {
			$stop .= $lang['feed_err_2'];
		} 

		if( empty( $subject ) OR dle_strlen($subject, $config['charset']) > 200 ) {
			$stop .= $lang['feed_err_4'];
		}
		
		if( empty( $message ) OR dle_strlen($message, $config['charset']) > 20000 ) {
			$stop .= $lang['feed_err_5'];
		}

		if ($config['allow_recaptcha']) {

			if ($_POST['recaptcha_response_field'] AND $_POST['recaptcha_challenge_field']) {

				require_once ENGINE_DIR . '/classes/recaptcha.php';			
				$resp = recaptcha_check_answer ($config['recaptcha_private_key'],
			                                     $_SERVER['REMOTE_ADDR'],
			                                     $_POST['recaptcha_challenge_field'],
			                                     $_POST['recaptcha_response_field']);
			
			        if ($resp->is_valid) {

						$_POST['sec_code'] = 1;
						$_SESSION['sec_code_session'] = 1;

			        } else $_SESSION['sec_code_session'] = false;
			} else $_SESSION['sec_code_session'] = false;

		}
		
		if( $_POST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) {
			$stop .= $lang['reg_err_19'];
		}
		$_SESSION['sec_code_session'] = false;
		
		if( $stop ) {
			
			msgbox( $lang['all_err_1'], "<ul>{$stop}</ul><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
		
		} else {
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
			$mail = new dle_mail( $config );
			
			$row = $db->super_query( "SELECT template FROM " . PREFIX . "_email WHERE name='feed_mail' LIMIT 0,1" );
			
			$row['template'] = stripslashes( $row['template'] );
			$row['template'] = str_replace( "{%username_to%}", $recipient['fullname'], $row['template'] );
			$row['template'] = str_replace( "{%username_from%}", $name, $row['template'] );
			$row['template'] = str_replace( "{%text%}", $message, $row['template'] );
			$row['template'] = str_replace( "{%ip%}", $_SERVER['REMOTE_ADDR'], $row['template'] );
			$row['template'] = str_replace( "{%group%}", $user_group[$member_id['user_group']]['group_name'], $row['template'] );
			
			$mail->from = $email;
			
			$mail->send( $recipient['email'], $subject, $row['template'] );
			
			if( $mail->send_error ) msgbox( $lang['all_info'], $mail->smtp_msg );
			else {

				if( $user_group[$member_id['user_group']]['max_mail_day'] ) { 
					if ( !$is_logged ) $check_user = $_IP; else $check_user = $member_id['name'];		
					$db->query( "INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$check_user}', '{$_TIME}', '2')" );
				}

				msgbox( $lang['feed_ok_1'], "{$lang['feed_ok_2']} <a href=\"{$config['http_home_url']}\">{$lang['feed_ok_4']}</a>" );
			}
		
		}
	
	} else {


		if( ! $user_group[$member_id['user_group']]['allow_feed'] )	{

			$group = 2;
			$user = false;

			if ($_GET['user']) {

				$lang['feed_error'] = str_replace( '{group}', $user_group[$member_id['user_group']]['group_name'], $lang['feed_error'] );
				msgbox( $lang['all_info'], $lang['feed_error'] );

			}

		} else { 

			$user = intval( $_GET['user'] );
			$group = 3;

		}
		
		if( ! $user ) $db->query( "SELECT name, user_group, user_id FROM " . USERPREFIX . "_users WHERE user_group < '$group' AND allow_mail = '1' ORDER BY user_group" );
		else $db->query( "SELECT name, user_group, user_id FROM " . USERPREFIX . "_users WHERE user_id = '$user' AND allow_mail = '1'" );
		
		if( $db->num_rows() ) {
			$empf = "<select name=\"recip\">";
			$i = 1;
			while ( $row = $db->get_array() ) {
				$str = $row['name'] . " (" . stripslashes( $user_group[$row['user_group']]['group_name'] ) . ")";
				
				if( $i == 1 ) {
					$empf .= "<option selected=\"selected\" value=\"" . $row["user_id"] . "\">" . $str . "</option>\n";
				} else {
					$empf .= "<option value=\"" . $row["user_id"] . "\">" . $str . "</option>\n";
				}
				$i ++;
			}
			$empf .= "</select>";
			
			$db->free();
			
			$tpl->load_template( 'feedback.tpl' );
			
			$path = parse_url( $config['http_home_url'] );
			$tpl->set( '{recipient}', $empf );

			if ( $config['allow_recaptcha'] ) {
		
				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );
		
			$tpl->set( '{recaptcha}', '
<script language="javascript" type="text/javascript">
<!--
	var RecaptchaOptions = {
        theme: \''.$config['recaptcha_theme'].'\',
        lang: \''.$lang['wysiwyg_language'].'\'
	};

//-->
</script>
<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k='.$config['recaptcha_public_key'].'"></script>' );

				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{code}', "" );
		
			} else {
		
				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );	
				$tpl->set( '{code}', "<span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot.php\" alt=\"{$lang['sec_image']}\" /><br /><a onclick=\"reload(); return false;\" href=\"#\">{$lang['reload_code']}</a></span>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
		
			}
		
			if( ! $is_logged ) {
				$tpl->set( '[not-logged]', "" );
				$tpl->set( '[/not-logged]', "" );
			} else
				$tpl->set_block( "'\\[not-logged\\](.*?)\\[/not-logged\\]'si", "" );
			
			$tpl->copy_template = "<form  method=\"post\" id=\"sendmail\" name=\"sendmail\" action=\"\">\n" . $tpl->copy_template . "
<input name=\"send\" type=\"hidden\" value=\"send\" />
</form>";
			
			$tpl->copy_template .= <<<HTML
<script language="javascript" type="text/javascript">
<!--
$(function(){

	$('#sendmail').submit(function() {

		if(document.sendmail.subject.value == '' || document.sendmail.message.value == '') { 

			DLEalert('{$lang['comm_req_f']}', dle_info);
			return false;

		}

		var params = {};
		$.each($('#sendmail').serializeArray(), function(index,value) {
			params[value.name] = value.value;
		});

		params['skin'] = dle_skin;

		ShowLoading('');

		$.post(dle_root + "engine/ajax/feedback.php", params, function(data){
			HideLoading('');
			if (data) {
	
				if (data.status == "ok") {

					scroll( 0, $("#dle-content").offset().top - 70 );
					$('#dle-content').html(data.text);	
	
				} else {

					if ( document.sendmail.sec_code ) {
			           document.sendmail.sec_code.value = '';
			           reload();
				    } else {
						Recaptcha.reload();
					}

					DLEalert(data.text, dle_info);

				}
	
			}
		}, "json");

	  return false;
	});

});

function reload () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha').innerHTML = '<img src="{$path['path']}engine/modules/antibot.php?rndval=' + rndval + '" width="120" height="50" alt="" /><br /><a onclick="reload(); return false;" href="#">{$lang['reload_code']}</a>';

};
//-->
</script>
HTML;
			
			$tpl->compile( 'content' );
			$tpl->clear();
		
		} else {
			msgbox( $lang['all_err_1'], $lang['feed_err_7'] );
		}
	}

?>