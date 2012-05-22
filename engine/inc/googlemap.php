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
 Файл: googlemap.php
-----------------------------------------------------
 Назначение: Создание карты сайта sitemap
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
  die("Hacking attempt!");
}

if( !$user_group[$member_id['user_group']]['admin_googlemap'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

if ($_POST['action'] == "create") {

	include_once ENGINE_DIR.'/classes/google.class.php';
	$map = new googlemap($config);

	$config['charset'] = strtolower($config['charset']);

	$map->limit = intval($_POST['limit']);
	$map->news_priority = strip_tags(stripslashes($_POST['priority']));
	$map->stat_priority = strip_tags(stripslashes($_POST['stat_priority']));
	$map->cat_priority = strip_tags(stripslashes($_POST['cat_priority']));

	$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );
	if ( !$map->limit ) $map->limit = $row['count'];


	if ( $map->limit > 45000 ) {

		$pages_count = @ceil( $row['count'] / 40000 );

		$sitemap = $map->build_index( $pages_count );

		if ( $config['charset'] != "utf-8" ) $sitemap = iconv($config['charset'], "UTF-8//IGNORE", $sitemap);

	    $handler = fopen(ROOT_DIR. "/uploads/sitemap.xml", "wb+");
	    fwrite($handler, $sitemap);
	    fclose($handler);
	
		@chmod(ROOT_DIR. "/uploads/sitemap.xml", 0666);

		$sitemap = $map->build_stat();

		if ( $config['charset'] != "utf-8" ) $sitemap = iconv($config['charset'], "UTF-8//IGNORE", $sitemap);

	    $handler = fopen(ROOT_DIR. "/uploads/sitemap1.xml", "wb+");
	    fwrite($handler, $sitemap);
	    fclose($handler);
	
		@chmod(ROOT_DIR. "/uploads/sitemap1.xml", 0666);

		for ($i =0; $i < $pages_count; $i++) {

			$t = $i+2;
			$n = $n+1;

			$sitemap = $map->build_map_news( $n );
			if ( $config['charset'] != "utf-8" ) $sitemap = iconv($config['charset'], "UTF-8//IGNORE", $sitemap);

		    $handler = fopen(ROOT_DIR. "/uploads/sitemap{$t}.xml", "wb+");
		    fwrite($handler, $sitemap);
		    fclose($handler);
		
			@chmod(ROOT_DIR. "/uploads/sitemap{$t}.xml", 0666);

		}


	} else {

		$sitemap = $map->build_map();

		if ( $config['charset'] != "utf-8" ) $sitemap = iconv($config['charset'], "UTF-8//IGNORE", $sitemap);
	
	    $handler = fopen(ROOT_DIR. "/uploads/sitemap.xml", "wb+");
	    fwrite($handler, $sitemap);
	    fclose($handler);
	
		@chmod(ROOT_DIR. "/uploads/sitemap.xml", 0666);
	}

	if(defined('AUTOMODE')) die("done"); else $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '38', '')" );

}

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['google_map']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;" colspan="2">
HTML;

	if(!@file_exists(ROOT_DIR. "/uploads/sitemap.xml")){ 

		echo $lang['no_google_map'];

	} else {

		$file_date = date("d.m.Y H:i", filectime(ROOT_DIR. "/uploads/sitemap.xml") + ($config['date_adjust'] * 60) );

		echo "<b>".$file_date."</b> ".$lang['google_map_info'];

		if ($config['allow_alt_url'] == "yes") {

			$map_link = $config['http_home_url']."sitemap.xml";

			echo " <a class=\"list\" href=\"".$map_link."\" target=\"_blank\">".$config['http_home_url']."sitemap.xml</a>";

		} else {

			$map_link = $config['http_home_url']."uploads/sitemap.xml";

			echo " <a class=\"list\" href=\"".$map_link."\" target=\"_blank\">".$config['http_home_url']."uploads/sitemap.xml</a>";

		}

		$map_link = base64_encode(urlencode($map_link));

		echo "<br /><br /><input id=\"sendbutton\" name=\"sendbutton\" type=\"button\" class=\"btn btn-warning\" value=\"{$lang['google_map_send']}\" /><br /><br /><div id=\"send_result\"></div>";

	}


echo <<<HTML
<script type="text/javascript">
$(function(){
	$('#sendbutton').click(function() {
		$('#send_result').html('{$lang['dle_updatebox']}');
		$.post("engine/ajax/sitemap.php", { url: "{$map_link}" } , function( data ){
					$('#send_result').append('<br />' + data);
		});
	});
});
</script>
</td>
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;" nowrap>{$lang['google_nnum']}</td>
        <td style="padding:2px;" width="100%"><input class="edit bk" type="text" size="10" name="limit"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_g_num]}', this, event, '220px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;" nowrap>{$lang['google_stat_priority']}</td>
        <td style="padding:2px;" width="100%"><input class="edit bk" type="text" size="10" name="stat_priority" value="0.5"><a href="#" class="hintanchor" onMouseover="showhint('{$lang[hint_g_priority]}', this, event, '220px')">[?]</a></td>
    </tr>
    <tr>
        <td style="padding:2px;" nowrap>{$lang['google_priority']}</td>
        <td style="padding:2px;" width="100%"><input class="edit bk" type="text" size="10" name="priority" value="0.6"></td>
    </tr>
    <tr>
        <td style="padding:2px;" nowrap>{$lang['google_cat_priority']}</td>
        <td style="padding:2px;" width="100%"><input class="edit bk" type="text" size="10" name="cat_priority" value="0.7"></td>
    </tr>
    <tr>
        <td style="padding:2px;" colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td style="padding:2px;" colspan="2"><input type="submit" class="btn btn-success" value="{$lang['google_create']}"><input type="hidden" name="action" value="create"></td>
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
HTML;

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['google_main']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['google_info']}</td>
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
HTML;


echofooter();
?>