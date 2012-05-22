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
 Файл: pm.php
-----------------------------------------------------
 Назначение: персональные сообщения
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . '/classes/parse.class.php';

$parse = new ParseFilter( );
$parse->safe_mode = true;
$parse->allow_url = $user_group[$member_id['user_group']]['allow_url'];
$parse->allow_image = $user_group[$member_id['user_group']]['allow_image'];

$stop_pm = FALSE;
if( isset( $_REQUEST['doaction'] ) ) $doaction = $_REQUEST['doaction'];
else $doaction = "";

if( !$is_logged OR !$user_group[$member_id['user_group']]['allow_pm'] ) {
	msgbox( $lang['all_err_1'], $lang['pm_err_1'] );
	$stop_pm = TRUE;
}

if( $user_group[$member_id['user_group']]['max_pm'] AND $member_id['pm_all'] >= $user_group[$member_id['user_group']]['max_pm'] AND ! $stop_pm ) {
	msgbox( $lang['all_info'], $lang['pm_err_9'] );
}


if( $user_group[$member_id['user_group']]['max_pm_day'] AND ( isset( $_POST['send'] ) OR $doaction == "newpm" ) ) {

	$this_time = time() + ($config['date_adjust'] * 60) - 86400;
	$db->query( "DELETE FROM " . PREFIX . "_sendlog WHERE date < '$this_time' AND flag='1'" );

	$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_sendlog WHERE user = '{$member_id['name']}' AND flag='1'");

	if( $row['count'] >=  $user_group[$member_id['user_group']]['max_pm_day'] ) {

		msgbox( $lang['all_err_1'], str_replace('{max}', $user_group[$member_id['user_group']]['max_pm_day'], $lang['pm_err_10']) );
		$stop_pm = TRUE;
	}
}


if( $doaction == "del" AND !$stop_pm AND count($_POST['selected_pm']) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	$delete_count = 0;

	foreach ( $_POST['selected_pm'] as $pmid ) {
			
		$pmid = intval( $pmid );
		$row = $db->super_query( "SELECT id, user, user_from, pm_read, folder FROM " . USERPREFIX . "_pm where id= '{$pmid}'" );
			
		if( ($row['user'] == $member_id['user_id'] AND $row['folder'] == "inbox") OR ($row['user_from'] == $member_id['name'] AND $row['folder'] == "outbox") ) {
			$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE id='{$row['id']}'" );
			$delete_count ++;
				
			if( !$row['pm_read'] AND $row['folder'] == "inbox" ) {
				$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread=pm_unread-1 where user_id='{$member_id['user_id']}'" );
			}
				
			$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all-1 where user_id='{$member_id['user_id']}'" );
			
		}
		
	}

	$member_id['pm_all'] = $member_id['pm_all'] - $delete_count;
	if( !$delete_count ) msgbox( $lang['all_err_1'], $lang['pm_err_5'] );

}

if( $doaction == "setunread" AND !$stop_pm AND count($_POST['selected_pm']) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	foreach ( $_POST['selected_pm'] as $pmid ) {

		$pmid = intval( $pmid );
		$row = $db->super_query( "SELECT id, user, user_from, pm_read, folder FROM " . USERPREFIX . "_pm where id= '{$pmid}'" );

		if( ($row['user'] == $member_id['user_id'] AND $row['folder'] == "inbox") ) {

			if( $row['pm_read'] ) {
				
				$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread=pm_unread+1  WHERE user_id='{$member_id['user_id']}'" );
				
				$db->query( "UPDATE " . USERPREFIX . "_pm SET pm_read='0'  WHERE id='{$row['id']}'" );
			
			}

		}

	}

}


if( $doaction == "setread" AND !$stop_pm AND count($_POST['selected_pm']) ) {

	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}

	foreach ( $_POST['selected_pm'] as $pmid ) {

		$pmid = intval( $pmid );
		$row = $db->super_query( "SELECT id, user, user_from, pm_read, folder FROM " . USERPREFIX . "_pm where id= '{$pmid}'" );

		if( ($row['user'] == $member_id['user_id'] AND $row['folder'] == "inbox") ) {

			if( !$row['pm_read'] ) {
				
				$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread=pm_unread-1  WHERE user_id='{$member_id['user_id']}'" );
				
				$db->query( "UPDATE " . USERPREFIX . "_pm SET pm_read='1'  WHERE id='{$row['id']}'" );
			
			}

		}

	}

}

$tpl->load_template( 'pm.tpl' );

$tpl->set( '[inbox]', "<a href=\"$PHP_SELF?do=pm&amp;folder=inbox\">" );
$tpl->set( '[/inbox]', "</a>" );
$tpl->set( '[outbox]', "<a href=\"$PHP_SELF?do=pm&amp;folder=outbox\">" );
$tpl->set( '[/outbox]', "</a>" );
$tpl->set( '[new_pm]', "<a href=\"$PHP_SELF?do=pm&amp;doaction=newpm\">" );
$tpl->set( '[/new_pm]', "</a>" );

if ( $user_group[$member_id['user_group']]['max_pm'] ) {

	$prlim = intval( ($member_id['pm_all'] / $user_group[$member_id['user_group']]['max_pm']) * 100 );

	if ($prlim > 100) $prlim = 100;

	$tpl->set( '{proc-pm-limit}', $prlim );
	$tpl->set( '{pm-limit}', $user_group[$member_id['user_group']]['max_pm'] );

} else {
	$prlim = 0;
	$tpl->set( '{proc-pm-limit}', $prlim );
	$tpl->set( '{pm-limit}', $lang['no_pm_limit'] );
}

$tpl->set( '{pm-progress-bar}', "<div class=\"pm_progress_bar\" title=\"{$lang['pm_progress_bar']} {$prlim}%\"><span style=\"width: {$prlim}%\">{$prlim}%</span></div>" );

$tpl->copy_template = "
    <script language=\"javascript\" type=\"text/javascript\">
    function confirmDelete(url){
	    DLEconfirm( '{$lang['pm_confirm']}', dle_confirm, function () {
			document.location=url;
		} );
    }
    </script>" . $tpl->copy_template;

if( isset( $_POST['send'] ) and !$stop_pm ) {
	
	$name = $db->safesql( $parse->process( trim( $_POST['name'] ) ) );
	$subj = $db->safesql( $parse->process( trim( $_POST['subj'] ) ) );
	if( dle_strlen( $_POST['comments'], $config['charset'] ) > 65000 ) $_POST['comments'] = "";
	
	$stop = "";

	if( $config['sec_addnews'] ) {
		$id_key = $_POST[$_SESSION['id_key']];			
		if( $id_key == "" or $id_key != $dle_login_hash ) $stop .= "<li>ANTISPAM: User ID not valid</li>";
		if (clean_url($_SERVER['HTTP_REFERER']) != clean_url($_SERVER['HTTP_HOST'])) $stop .= "<li>ANTISPAM: User ID not valid</li>";
	}
	
	if( $config['allow_comments_wysiwyg'] != "yes" ) $comments = $db->safesql( $parse->BB_Parse( $parse->process( trim( $_POST['comments'] ) ), false ) );
	else {
		$parse->wysiwyg = true;
		
		if( strlen( $_POST['comments'] ) < 8 ) $_POST['comments'] = "";
		
		if( $user_group[$member_id['user_group']]['allow_url'] ) $parse->ParseFilter( Array ('div', 'a', 'span', 'p', 'br', 'strong', 'em', 'ul', 'li', 'ol' ), Array (), 0, 1 );
		else $parse->ParseFilter( Array ('div', 'span', 'p', 'br', 'strong', 'em', 'ul', 'li', 'ol' ), Array (), 0, 1 );
		
		$comments = $db->safesql( $parse->BB_Parse( $parse->process( trim( $_POST['comments'] ) ) ) );
	}
	
	if( empty( $name ) or empty( $subj ) or $comments == "" ) $stop .= $lang['pm_err_2'];
	
	if( dle_strlen( $subj, $config['charset'] ) > 250 ) {
		$stop .= $lang['pm_err_3'];
	}

	if( $parse->not_allowed_tags ) {
		
		$stop .= "<li>" .$lang['news_err_33']. "</li>";
	}

	if( $parse->not_allowed_text ) {
		
		$stop .= "<li>" . $lang['news_err_37']. "</li>";
	}
	
	if( $user_group[$member_id['user_group']]['captcha_pm'] ) {

		if ($config['allow_recaptcha']) {

			require_once ENGINE_DIR . '/classes/recaptcha.php';
			$sec_code = 1;
			$sec_code_session = false;

			if ($_POST['recaptcha_response_field'] AND $_POST['recaptcha_challenge_field']) {
			
				$resp = recaptcha_check_answer ($config['recaptcha_private_key'],
			                                     $_SERVER['REMOTE_ADDR'],
			                                     $_POST['recaptcha_challenge_field'],
			                                     $_POST['recaptcha_response_field']);
			
			        if (!$resp->is_valid) {

						$stop .= "<li>" . $lang['news_err_30'] . "</li>";

			        }

			} else $stop .= "<li>" . $lang['news_err_30'] . "</li>";

		} elseif( $_REQUEST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) $stop .= "<li>" . $lang['news_err_30'] . "</li>";
	
	}
	
	$db->query( "SELECT email, name, user_id, pm_all, user_group FROM " . USERPREFIX . "_users WHERE name = '$name'" );
	
	if( !$db->num_rows() ) $stop .= $lang['pm_err_4'];
	
	$row = $db->get_row();
	$db->free();

	if( !$stop ) {

		$db->query( "SELECT id FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' AND user_from='{$member_id['name']}'" );
		if( $db->num_rows() ) $stop .= $lang['pm_ignored'];
		$db->free();

	}
	
	if( !$stop AND ($user_group[$row['user_group']]['max_pm'] AND $row['pm_all'] >= $user_group[$row['user_group']]['max_pm']) and $member_id['user_group'] != 1 ) {
		$stop .= $lang['pm_err_8'];
	}
	
	if( !$stop ) {
		
		$_SESSION['sec_code_session'] = 0;
		
		$time = time() + ($config['date_adjust'] * 60);
		$member_id['name'] = $db->safesql($member_id['name']);

		if( intval( $_REQUEST['outboxcopy'] ) ) {
			
			$db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder) values ('$subj', '$comments', '{$row['user_id']}', '{$member_id['name']}', '{$time}', '0', 'outbox')" );
			$send_id = $db->insert_id();

			$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1 WHERE user_id='{$member_id['user_id']}'" );
		
		} else $send_id = 0;
		
		$db->query( "INSERT INTO " . USERPREFIX . "_pm (subj, text, user, user_from, date, pm_read, folder, sendid) values ('$subj', '$comments', '{$row['user_id']}', '{$member_id['name']}', '{$time}', '0', 'inbox', '{$send_id}')" );
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all+1, pm_unread=pm_unread+1  WHERE user_id='{$row['user_id']}'" );
		
		$replyid = intval( $_GET['replyid'] );
		
		if( $replyid ) {
			
			$db->query( "UPDATE " . USERPREFIX . "_pm SET reply=1 WHERE id= '{$replyid}'" );
		
		}

		if( $user_group[$member_id['user_group']]['max_pm_day'] ) { 

			$db->query( "INSERT INTO " . PREFIX . "_sendlog (user, date, flag) values ('{$member_id['name']}', '{$time}', '1')" );

		}
		
		if( $config['mail_pm'] ) {
			
			include_once ENGINE_DIR . '/classes/mail.class.php';
			$mail = new dle_mail( $config );
			
			$mail_template = $db->super_query( "SELECT template FROM " . PREFIX . "_email WHERE name='pm' LIMIT 0,1" );
			
			$mail_template['template'] = stripslashes( $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%username%}", $row['name'], $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $_TIME ), $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%fromusername%}", $member_id['name'], $mail_template['template'] );
			$mail_template['template'] = str_replace( "{%title%}", strip_tags( stripslashes( $subj ) ), $mail_template['template'] );
			
			$body = str_replace( '\n', "", $comments );
			$body = str_replace( '\r', "", $body );
			
			$body = stripslashes( stripslashes( $body ) );
			$body = str_replace( "<br />", "\n", $body );
			$body = strip_tags( $body );
			
			$mail_template['template'] = str_replace( "{%text%}", $body, $mail_template['template'] );
			
			$mail->send( $row['email'], $lang['mail_pm'], $mail_template['template'] );
		
		}
		
		msgbox( $lang['all_info'], $lang['pm_sendok'] . " <a href=\"$PHP_SELF?do=pm&amp;doaction=newpm\">" . $lang['pm_noch'] . "</a> " . $lang['pm_or'] . " <a href=\"$PHP_SELF\">" . $lang['pm_main'] . "</a>" );
		$stop_pm = TRUE;
	
	} else
		msgbox( $lang['all_err_1'], "<ul>".$stop."</ul>" );

}

if( $doaction == "del" AND !$stop_pm AND $_GET['pmid'] ) {
	
	if( $_REQUEST['dle_allow_hash'] == "" or $_REQUEST['dle_allow_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User ID not valid" );
	
	}
	
	$pmid = intval( $_GET['pmid'] );
	$row = $db->super_query( "SELECT id, user, user_from, pm_read, folder FROM " . USERPREFIX . "_pm where id= '{$pmid}'" );
		
	if( ($row['user'] == $member_id['user_id'] AND $row['folder'] == "inbox") OR ($row['user_from'] == $member_id['name'] AND $row['folder'] == "outbox") ) {
		$db->query( "DELETE FROM " . USERPREFIX . "_pm WHERE id='{$row['id']}'" );
			
		if( !$row['pm_read'] AND $row['folder'] == "inbox" ) {
			$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread=pm_unread-1 WHERE user_id='{$member_id['user_id']}'" );
		}
			
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_all=pm_all-1 WHERE user_id='{$member_id['user_id']}'" );

		msgbox( $lang['all_info'], $lang['pm_delok'] . " <a href=\"$PHP_SELF?do=pm\">" . $lang['all_prev'] . "</a>." );
		
	} else msgbox( $lang['all_err_1'], $lang['pm_err_5'] );


} elseif( $doaction == "readpm" AND !$stop_pm ) {
	
	$pmid = intval( $_GET['pmid'] );
	
	$tpl->set( '[readpm]', "" );
	$tpl->set( '[/readpm]', "" );
	$tpl->set_block( "'\\[pmlist\\].*?\\[/pmlist\\]'si", "" );
	$tpl->set_block( "'\\[newpm\\].*?\\[/newpm\\]'si", "" );
	
	$db->query( "SELECT id, subj, text, user, user_from, date, pm_read, folder, sendid, news_num, comm_num, user_group, reg_date, signature, foto, fullname, land, icq, xfields FROM " . USERPREFIX . "_pm LEFT JOIN " . USERPREFIX . "_users ON " . USERPREFIX . "_pm.user_from=" . USERPREFIX . "_users.name WHERE " . USERPREFIX . "_pm.id= '$pmid'" );
	$row = $db->get_row();
	
	if( $db->num_rows() < 1 ) {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_6'] );
		$stop_pm = TRUE;
	
	} elseif( $row['user'] != $member_id['user_id'] and $row['user_from'] != $member_id['name'] ) {
		
		msgbox( $lang['all_err_1'], $lang['pm_err_7'] );
		$stop_pm = TRUE;
	
	} else {
		
		if( $row['user'] == $member_id['user_id'] AND !$row['pm_read'] AND $row['folder'] == "inbox" ) {
			
			$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread=pm_unread-1  WHERE user_id='{$member_id['user_id']}'" );

			if ( $row['sendid'] ) $addwhere =" OR id='{$row['sendid']}'"; else $addwhere ="";

			$db->query( "UPDATE " . USERPREFIX . "_pm SET pm_read='1'  WHERE id='{$row['id']}'{$addwhere}" );
		
		}

		if( strpos( $tpl->copy_template, "[xfvalue_" ) !== false ) $xfound = true;
		else $xfound = false;
		
		if( $xfound ) { 

			$xfields = xfieldsload( true );

			$xfieldsdata = xfieldsdataload( $row['xfields'] );
				
			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );
					
				if( $value[5] != 1 OR $member_id['user_group'] == 1 OR ($is_logged AND $member_id['name'] == $row['user_from']) ) {
					if( empty( $xfieldsdata[$value[0]] ) ) {
						$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					} else {
						$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "\\1", $tpl->copy_template );
					}
					$tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", stripslashes( $xfieldsdata[$value[0]] ), $tpl->copy_template );
				} else {
					$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
					$tpl->copy_template = preg_replace( "'\\[xfvalue_{$preg_safe_name}\\]'i", "", $tpl->copy_template );
				}
			}
		}
		
		$tpl->set( '{subj}', stripslashes( $row['subj'] ) );
		$tpl->set( '{text}', stripslashes( $row['text'] ) );

		if( $row['signature'] and $user_group[$row['user_group']]['allow_signature'] ) {
				
			$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "\\1" );
			$tpl->set( '{signature}', stripslashes( $row['signature'] ) );
			
		} else {
			$tpl->set_block( "'\\[signature\\](.*?)\\[/signature\\]'si", "" );
		}

		if( $row['icq'] ) $tpl->set( '{icq}', stripslashes( $row['icq'] ) );
		else $tpl->set( '{icq}', '--' );

		if( $user_group[$row['user_group']]['icon'] ) $tpl->set( '{group-icon}', "<img src=\"" . $user_group[$row['user_group']]['icon'] . "\" border=\"0\" alt=\"\" />" );
		else $tpl->set( '{group-icon}', "" );

		$tpl->set( '{group-name}', $user_group[$row['user_group']]['group_prefix'].$user_group[$row['user_group']]['group_name'].$user_group[$row['user_group']]['group_suffix'] );

		$tpl->set( '{news-num}', intval( $row['news_num'] ) );
		$tpl->set( '{comm-num}', intval( $row['comm_num'] ) );

		if( $row['foto'] ) $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row['foto'] );
		else $tpl->set( '{foto}', "{THEME}/images/noavatar.png" );

		if( date( Ymd, $row['date'] ) == date( Ymd, $_TIME ) ) {
				
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
			
		} elseif( date( Ymd, $row['date'] ) == date( Ymd, ($_TIME - 86400) ) ) {
				
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
			
		} else {
				
			$tpl->set( '{date}', langdate( $config['timestamp_comment'], $row['date'] ) );
			
		}

		if($row['reg_date'] ) $tpl->set( '{registration}', langdate( "j.m.Y", $row['reg_date'] ) );
		else $tpl->set( '{registration}', '--' );

		if( $config['allow_alt_url'] == "yes" ) {
			
			$user_from = $config['http_home_url'] . "user/" . urlencode( $row['user_from'] ) . "/";
			$user_from = "onclick=\"ShowProfile('" . urlencode( $row['user_from'] ) . "', '" . htmlspecialchars( $user_from ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$tpl->set( '{author}', "<a {$user_from} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['user_from'] ) . "/\">" . $row['user_from'] . "</a>");
		
		} else {
			
			$user_from = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['user_from'] );
			$user_from = "onclick=\"ShowProfile('" . urlencode( $row['user_from'] ) . "', '" . htmlspecialchars( $user_from ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$tpl->set( '{author}', "<a {$user_from} class=\"pm_list\" href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['user_from'] ) . "\">" . $row['user_from'] . "</a>");

		}
		
		$tpl->set( '[reply]', "<a href=\"" . $config['http_home_url'] . "index.php?do=pm&amp;doaction=newpm&amp;replyid=" . $row['id'] . "\">" );
		$tpl->set( '[/reply]', "</a>" );
		
		$tpl->set( '[del]', "<a href=\"javascript:confirmDelete('" . $config['http_home_url'] . "index.php?do=pm&amp;doaction=del&amp;pmid=" . $row['id'] . "&amp;dle_allow_hash=" . $dle_login_hash . "')\">" );
		$tpl->set( '[/del]', "</a>" );

		$tpl->set( '[ignore]', "<a href=\"javascript:AddIgnorePM('" . $row['id'] . "', '" . $lang['add_to_ignore'] . "')\">" );
		$tpl->set( '[/ignore]', "</a>" );

		$tpl->set( '[complaint]', "<a href=\"javascript:AddComplaint('" . $row['id'] . "', 'pm')\">" );
		$tpl->set( '[/complaint]', "</a>" );
		
		$tpl->compile( 'content' );
		$tpl->clear();
	}

} elseif( $doaction == "newpm" and ! $stop_pm ) {
	
	$ajax_form = <<<HTML
<span id="dle-pm-preview"></span>
<script language="javascript" type="text/javascript">
<!--
function dlePMPreview( ){ 

	if (dle_wysiwyg == "yes") {
		submit_all_data(); 
	}

	var pm_text = document.getElementById('dle-comments-form').comments.value;

	if(document.getElementById('dle-comments-form').name.value == '' || document.getElementById('dle-comments-form').subj.value == '' || pm_text == '')
	{
		DLEalert('{$lang['comm_req_f']}', dle_info);return false;

	}

	var name = document.getElementById('dle-comments-form').name.value;
	var subj = document.getElementById('dle-comments-form').subj.value;

	ShowLoading('');

	$.post(dle_root + "engine/ajax/pm.php", { text: pm_text, name: name, subj: subj, skin: dle_skin }, function(data){

		HideLoading('');

		$("#dle-pm-preview").html(data);

		$("html"+( ! $.browser.opera ? ",body" : "")).animate({scrollTop: $("#dle-pm-preview").position().top - 70}, 1100);

		setTimeout(function() { $("#blind-animation").show('blind',{},1500)}, 1100);


	});

};

function reload () {

	var rndval = new Date().getTime(); 

	document.getElementById('dle-captcha').innerHTML = '<img src="{$path['path']}engine/modules/antibot.php?rndval=' + rndval + '" border="0" width="120" height="50" alt="" /><br /><a onclick="reload(); return false;" href="#">{$lang['reload_code']}</a>';

};
//-->
</script>
HTML;
	
	$tpl->set( '[newpm]', $ajax_form );
	$tpl->set( '[/newpm]', "" );
	$tpl->set_block( "'\\[pmlist\\].*?\\[/pmlist\\]'si", "" );
	$tpl->set_block( "'\\[readpm\\].*?\\[/readpm\\]'si", "" );
	
	if( $user_group[$member_id['user_group']]['captcha_pm'] ) {

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
				$tpl->set( '{reg_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );
				$path = parse_url( $config['http_home_url'] );
				$tpl->set( '{sec_code}', "<span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot.php\" alt=\"${lang['sec_image']}\" border=\"0\" /><br /><a onclick=\"reload(); return false;\" href=\"#\">{$lang['reload_code']}</a></span>" );
				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

	} else {

		$tpl->set( '{sec_code}', "" );
		$tpl->set( '{recaptcha}', "" );
		$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
		$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );

	}
	
	$replyid = intval( $_GET['replyid'] );
	$user = intval( $_GET['user'] );
	if( isset( $_REQUEST['username'] ) ) $username = $db->safesql( strip_tags( urldecode( $_GET['username'] ) ) );
	else $username = '';

	$text = "";

	if( $replyid ) {
		$row = $db->super_query( "SELECT * FROM " . USERPREFIX . "_pm WHERE id= '$replyid'" );
		
		if( ($row['user'] != $member_id['user_id']) and ($row['user_from'] != $member_id['name']) ) {
			
			msgbox( $lang['all_err_1'], $lang['pm_err_7'] );
			$stop_pm = TRUE;
		
		}
		
		if( $config['allow_comments_wysiwyg'] != "yes" ) {
			
			$text = $parse->decodeBBCodes( $row['text'], false );
			$text = "[quote]" . $text . "[/quote]\n";
		
		} else {
			
			$text = $parse->decodeBBCodes( $row['text'], TRUE, $config['allow_comments_wysiwyg'] );
			$text = "[quote]" . $text . "[/quote]<br />";
		}
		
		$tpl->set( '{author}', $row['user_from'] );

		if (strpos ( $row['subj'], "RE:" ) === false)
			$tpl->set( '{subj}', "RE: " . stripslashes( $row['subj'] ) );
		else
			$tpl->set( '{subj}', stripslashes( $row['subj'] ) );

		$row = $db->super_query( "SELECT user_id, pm_all, user_group FROM " . USERPREFIX . "_users WHERE name = '" . $db->safesql( $row['user_from'] ) . "'" );
		
		if( $user_group[$row['user_group']]['max_pm'] AND $row['pm_all'] >= $user_group[$row['user_group']]['max_pm'] AND $member_id['user_group'] != 1 ) {
			$stop_pm = true;
		}
	
	} elseif( $user OR $username != "" ) {
		
		if( $user ) $row = $db->super_query( "SELECT user_id, name, pm_all, user_group FROM " . USERPREFIX . "_users WHERE user_id = '$user'" );
		elseif( $username != "" ) $row = $db->super_query( "SELECT user_id, name, pm_all, user_group FROM " . USERPREFIX . "_users where name='$username'" );
		
		if( $user_group[$row['user_group']]['max_pm'] AND $row['pm_all'] >= $user_group[$row['user_group']]['max_pm'] and $member_id['user_group'] != 1 ) {
			$stop_pm = true;
		}
		
		$tpl->set( '{author}', $row['name'] );
		$tpl->set( '{subj}', "" );
	
	} else {
		$tpl->set( '{author}', "" );
		$tpl->set( '{subj}', "" );
	
	}

	if( $config['allow_comments_wysiwyg'] == "yes" ) {
		
		include_once ENGINE_DIR . '/editor/comments.php';
		$bb_code = "";
		$allow_comments_ajax = true;
	} else
		include_once ENGINE_DIR . '/modules/bbcode.php';

	if( $config['allow_comments_wysiwyg'] == "yes" ) {
		
		$tpl->set( '{editor}', $wysiwyg );
	
	} else {
		$tpl->set( '{editor}', $bb_code );
	}

	$tpl->set( '{text}', $text );		

	if( $config['sec_addnews'] ) {

		$salt = "abchefghjkmnpqrstuvwxyz";
		$random_key = "";
				
		for($i = 0; $i < 8; $i ++) {
			$random_key .= $salt{rand( 0, 23 )};
		}
	
		$_SESSION['id_key'] = $random_key;
				
		$random_key = "<input name=\"{$random_key}\" type=\"hidden\" value=\"{$dle_login_hash}\" />";

	} else
		$random_key = "";
	
	if( $config['allow_comments_wysiwyg'] == "yes" ) $tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" onsubmit=\"if(document.getElementById('dle-comments-form').name.value == '' || document.getElementById('dle-comments-form').subj.value == '' || document.getElementById('comments').value == ''){DLEalert('{$lang['comm_req_f']}', dle_info);return false}\" action=\"\">\n" . $tpl->copy_template . "{$random_key}<input name=\"send\" type=\"hidden\" value=\"send\" /></form>";
	else $tpl->copy_template = "<form  method=\"post\" name=\"dle-comments-form\" id=\"dle-comments-form\" onsubmit=\"if(document.getElementById('dle-comments-form').name.value == '' || document.getElementById('dle-comments-form').subj.value == '' || document.getElementById('dle-comments-form').comments.value == ''){DLEalert('{$lang['comm_req_f']}', dle_info);return false}\" action=\"\">\n" . $tpl->copy_template . "{$random_key}<input name=\"send\" type=\"hidden\" value=\"send\" /></form>";

	if ($row['user_id']) {

		$db->query( "SELECT id FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' AND user_from='{$member_id['name']}'" );
		if( $db->num_rows() ) { $stop_pm = true; $lang['pm_err_8'] = $lang['pm_ignored'];}
		$db->free();

	}

	if( !$stop_pm ) {
		$tpl->compile( 'content' );
		$tpl->clear();
	} else {
		$tpl->clear();
		if( ! $tpl->result['info'] ) msgbox( $lang['all_info'], $lang['pm_err_8'] );
	}

} elseif( ! $stop_pm ) {
	
	$tpl->set( '[pmlist]', "" );
	$tpl->set( '[/pmlist]', "" );
	$tpl->set_block( "'\\[newpm\\].*?\\[/newpm\\]'si", "" );
	$tpl->set_block( "'\\[readpm\\].*?\\[/readpm\\]'si", "" );

	$pm_per_page = 20;
	if (isset ( $_GET['cstart'] )) $cstart = intval ( $_GET['cstart'] ); else $cstart = 0;

	if ($cstart) {
		$cstart = $cstart - 1;
		$cstart = $cstart * $pm_per_page;
	}

	if ($cstart < 0) $cstart = 0;
	
	if( $member_id['pm_unread'] < 0 ) {
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET pm_unread='0' WHERE user_id='{$member_id['user_id']}'" );
	
	}
	
	$pmlist = <<<HTML
<form action="" method="post" name="pmlist">
<input type="hidden" name="dle_allow_hash" value="{$dle_login_hash}" />
HTML;
	
	if( $_GET['folder'] == "outbox" ) {

		$lang['pm_from'] = $lang['pm_to'];
		$sql = "SELECT SQL_CALC_FOUND_ROWS id, subj, name as user_from, date, pm_read FROM " . USERPREFIX . "_pm LEFT JOIN " . USERPREFIX . "_users ON " . USERPREFIX . "_pm.user=" . USERPREFIX . "_users.user_id WHERE user_from = '{$member_id['name']}' AND folder = 'outbox' ORDER BY date DESC LIMIT " . $cstart . "," . $pm_per_page;
		$user_query = "do=pm&amp;folder=outbox";

	} else {

		$sql = "SELECT SQL_CALC_FOUND_ROWS id, subj, user_from, date, pm_read, reply FROM " . USERPREFIX . "_pm where user = '{$member_id['user_id']}' AND folder = 'inbox' ORDER BY pm_read ASC, date DESC LIMIT " . $cstart . "," . $pm_per_page;
		$user_query = "do=pm";
	}
	
	$pmlist .= "<table class=\"pm\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr><td width=\"20\">&nbsp;</td><td class=\"pm_head\">" . $lang['pm_subj'] . "</td><td width=\"130\" class=\"pm_head\">" . $lang['pm_from'] . "</td><td width=\"130\" class=\"pm_head\" align=\"center\">" . $lang['pm_date'] . "</td><td width=\"50\" class=\"pm_head\" align=\"center\"><input type=\"checkbox\" name=\"master_box\" title=\"{$lang['pm_selall']}\" onclick=\"javascript:ckeck_uncheck_all()\" /></td>";
	
	$db->query( $sql );
	$i = 0;
	$cc = $cstart;
	
	while ( $row = $db->get_row() ) {
		
		$i ++;
		$cc ++;
		
		if( $config['allow_alt_url'] == "yes" ) {
			
			$user_from = $config['http_home_url'] . "user/" . urlencode( $row['user_from'] ) . "/";
			$user_from = "onclick=\"ShowProfile('" . urlencode( $row['user_from'] ) . "', '" . htmlspecialchars( $user_from ) . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_from = "<a {$user_from} class=\"pm_list\" href=\"" . $config['http_home_url'] . "user/" . urlencode( $row['user_from'] ) . "/\">" . $row['user_from'] . "</a>";
		
		} else {
			
			$user_from = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['user_from'] );
			$user_from = "onclick=\"ShowProfile('" . urlencode( $row['user_from'] ) . "', '" . $user_from . "', '" . $user_group[$member_id['user_group']]['admin_editusers'] . "'); return false;\"";
			$user_from = "<a {$user_from} class=\"pm_list\" href=\"$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['user_from'] ) . "\">" . $row['user_from'] . "</a>";

		}
		
		if( $row['pm_read'] ) {
			
			$subj = "<a class=\"pm_list\" href=\"$PHP_SELF?do=pm&amp;doaction=readpm&amp;pmid=" . $row['id'] . "\">" . stripslashes( $row['subj'] ) . "</a>";
			$icon = "{THEME}/dleimages/read.gif";
		
		} else {
			
			$subj = "<a class=\"pm_list\" href=\"$PHP_SELF?do=pm&amp;doaction=readpm&amp;pmid=" . $row['id'] . "\"><b>" . stripslashes( $row['subj'] ) . "</b></a>";
			$icon = "{THEME}/dleimages/unread.gif";
		
		}
		
		if( $row['reply'] ) $icon = "{THEME}/dleimages/send.gif";
		
		$pmlist .= "<tr><td><img src=\"{$icon}\" border=\"0\" alt=\"\" /></td><td class=\"pm_list\">{$subj}</td><td class=\"pm_list\">{$user_from}</td><td class=\"pm_list\" align=\"center\">" . langdate( "j.m.Y H:i", $row['date'] ) . "</td><td class=\"pm_list\" align=\"center\"><input name=\"selected_pm[]\" value=\"{$row['id']}\" type=\"checkbox\" /></td></tr>";
	
	}
	
	$db->free();

	$count_all = $db->super_query( "SELECT FOUND_ROWS() as count" );
	$count_all = $count_all['count'];
	$pages = "";

	if( $count_all AND $count_all > $pm_per_page) {

		if( isset( $cstart ) and $cstart > 0 ) {
			$prev = $cstart / $pm_per_page;

				if ($prev == 1)
					$pages .= "<a href=\"$PHP_SELF?{$user_query}\"> << </a> ";
				else
					$pages .= "<a href=\"$PHP_SELF?cstart=$prev&amp;$user_query\"> << </a> ";
		
		}
				
		$enpages_count = @ceil( $count_all / $pm_per_page );
				
		$cstart = ($cstart / $pm_per_page) + 1;
				
		if( $enpages_count <= 10 ) {
					
			for($j = 1; $j <= $enpages_count; $j ++) {
						
				if( $j != $cstart ) {
							
					if ($j == 1)
						$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
					else
						$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
						
				} else {
					
					$pages .= "<span>$j</span> ";
				}
			}
				
		} else {
					
			$start = 1;
			$end = 10;
			$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
			
			if( $cstart > 0 ) {
						
				if( $cstart > 6 ) {
							
					$start = $cstart - 4;
					$end = $start + 8;
							
					if( $end >= $enpages_count ) {
						$start = $enpages_count - 9;
						$end = $enpages_count - 1;
						$nav_prefix = "";
				} else
						$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
				}
					
			}
					
			if( $start >= 2 ) {
				
				$pages .= "<a href=\"$PHP_SELF?{$user_query}\">1</a> <span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
			
			}
					
			for($j = $start; $j <= $end; $j ++) {
						
				if( $j != $cstart ) {
					if ($j == 1)
						$pages .= "<a href=\"$PHP_SELF?{$user_query}\">$j</a> ";
					else
						$pages .= "<a href=\"$PHP_SELF?cstart=$j&amp;$user_query\">$j</a> ";
						
				} else {
							
					$pages .= "<span>$j</span> ";
				}
					
			}
					
			if( $cstart != $enpages_count ) {
						
				$pages .= $nav_prefix . "<a href=\"$PHP_SELF?cstart={$enpages_count}&amp;$user_query\">{$enpages_count}</a>";
					
			} else
				$pages .= "<span>{$enpages_count}</span> ";
		
		}

		if( $pm_per_page < $count_all AND $cc < $count_all ) {
			$next_page = $cc / $pm_per_page + 1;
			$pages .= "<a href=\"$PHP_SELF?cstart=$next_page&amp;$user_query\"> >> </a>";			
		
		}	
	}

	$pmlist .= "<tr><td colspan=\"5\">&nbsp;</td></tr><tr><td colspan=\"2\"><div class=\"navigation\">{$pages}</div></td><td colspan=\"3\" align=\"right\"><select name=\"doaction\"><optgroup label=\"{$lang['edit_selact']}\"><option value=\"del\">{$lang['edit_seldel']}</option><option value=\"setread\">{$lang['pm_set_read']}</option><option value=\"setunread\">{$lang['pm_set_unread']}</option></optgroup></select>&nbsp;&nbsp;<input class=\"bbcodes\" type=\"submit\" value=\"{$lang['b_start']}\" /></td></tr></table></form>";
	
	if( $i ) $tpl->set( '{pmlist}', $pmlist );
	else $tpl->set( '{pmlist}', $lang['no_message'] );
	
	$tpl->compile( 'content' );
	$tpl->clear();
}
?>