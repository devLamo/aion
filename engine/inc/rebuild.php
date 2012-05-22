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
 Файл: rebuild.php
-----------------------------------------------------
 Назначение: Перестроение новостей
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if($member_id['user_group'] != 1){ msg("error", $lang['addnews_denied'], $lang['db_denied']); }

$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '49', '')" );

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );

echoheader("", "");

echo <<<HTML
<form action="" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_srebuild']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">{$lang['rebuild_info']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:2px;height:50px;"><div id="progressbar"></div>{$lang['stat_allnews']}&nbsp;{$row['count']},&nbsp;{$lang['rebuild_count']}&nbsp;<font color="red"><span id="newscount">0</span></font>&nbsp;<span id="progress"></span></td>
    </tr>

	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;"><input type="submit" id="button" class="btn btn-primary" value="{$lang['rebuild_start']}" style="width:190px;"><input type="hidden" id="rebuild_ok" name="rebuild_ok" value="0"></td>
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
<script language="javascript" type="text/javascript">

  var total = {$row['count']};

	$(function() {

		$( "#progressbar" ).progressbar({
			value: 0
		});

		$('#button').click(function() {

			$("#progress").ajaxError(function(event, request, settings){
			   $(this).html('{$lang['nl_error']}');
				$('#button').attr("disabled", false);
			 });

			$('#progress').html('{$lang['rebuild_status']}');
			$('#button').attr("disabled", "disabled");
			$('#button').val("{$lang['rebuild_forw']}");
			senden( $('#rebuild_ok').val() );
			return false;
		});

	});

function senden( startfrom ){

	$.post("engine/ajax/rebuild.php?user_hash={$dle_login_hash}", { startfrom: startfrom },
		function(data){

			if (data) {

				if (data.status == "ok") {

					$('#newscount').html(data.rebuildcount);
					$('#rebuild_ok').val(data.rebuildcount);

					var proc = Math.round( (100 * data.rebuildcount) / total );

					if ( proc > 100 ) proc = 100;

					$('#progressbar').progressbar( "option", "value", proc );

			         if (data.rebuildcount >= total) 
			         {
			              $('#progress').html('{$lang['rebuild_status_ok']}');
			         }
			         else 
			         { 
			              setTimeout("senden(" + data.rebuildcount + ")", 5000 );
			         }


				}

			}
		}, "json");

	return false;
}
</script>
HTML;


$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_static WHERE allow_br !='2'" );

echo <<<HTML
<form action="" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_statrebuild']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">{$lang['rebuild_stat_info']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>
    <tr>
        <td style="padding:2px;height:50px;"><div id="progressbar2"></div>{$lang['stat_allstaic']}&nbsp;{$row['count']},&nbsp;{$lang['rebuild_count']}&nbsp;<font color="red"><span id="statcount">0</span></font>&nbsp;<span id="statprogress"></span></td>
    </tr>

	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=2></td></tr>
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;"><input type="submit" id="button2" class="btn btn-primary" value="{$lang['rebuild_start']}" style="width:190px;"><input type="hidden" id="rebuild_ok2" name="rebuild_ok2" value="0"></td>
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
<script language="javascript" type="text/javascript">

  var total2 = {$row['count']};

	$(function() {

		$( "#progressbar2" ).progressbar({
			value: 0
		});

		$('#button2').click(function() {

			$("#statprogress").ajaxError(function(event, request, settings){
			   $(this).html('{$lang['nl_error']}');
				$('#button2').attr("disabled", false);
			 });


			$('#statprogress').html('{$lang['rebuild_status']}');
			$('#button2').attr("disabled", "disabled");
			$('#button2').val("{$lang['rebuild_forw']}");
			senden_stat( $('#rebuild_ok2').val() );
			return false;
		});

	});

function senden_stat( startfrom ){

	$.post("engine/ajax/rebuild.php?user_hash={$dle_login_hash}", { startfrom: startfrom, area: 'static' },
		function(data){

			if (data) {

				if (data.status == "ok") {

					$('#statcount').html(data.rebuildcount);
					$('#rebuild_ok2').val(data.rebuildcount);

					var proc = Math.round( (100 * data.rebuildcount) / total2 );

					if ( proc > 100 ) proc = 100;

					$('#progressbar2').progressbar( "option", "value", proc );

			         if (data.rebuildcount >= total2) 
			         {
			              $('#statprogress').html('{$lang['rebuild_status_ok']}');
			         }
			         else 
			         { 
			              setTimeout("senden_stat(" + data.rebuildcount + ")", 5000 );
			         }


				}

			}
		}, "json");

	return false;
}
</script>
HTML;


echo <<<HTML
<form action="" method="post">
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['opt_relrebuild']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">{$lang['rebuild_rel_info']}</td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr_line"></div></td>
    </tr>

    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;"><input type="submit" id="button3" class="btn btn-primary" value="{$lang['rebuild_start']}" style="width:190px;">&nbsp;<span id="relprogress"></span></td>
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
<script language="javascript" type="text/javascript">

	$(function() {

		$('#button3').click(function() {

			$('#relprogress').html('{$lang['rebuild_status']}');
			$('#button3').attr("disabled", "disabled");

			$.post("engine/ajax/rebuild.php?user_hash={$dle_login_hash}", { area: 'related' },
				function(data){
		
					if (data) {
		
						if (data.status == "ok") {
		
							$('#relprogress').html('{$lang['rebuild_status_ok']}');
		
						}
		
					}
				}, "json");

			return false;
		});

	});
</script>
HTML;

echofooter();
?>