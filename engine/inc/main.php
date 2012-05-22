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
 Файл: main.php
-----------------------------------------------------
 Назначение: Статистика и автопроверка
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

$js_array[] = "engine/skins/tabset.js";

echoheader( "home", "" );

$config['max_users_day'] = intval( $config['max_users_day'] );

$maxmemory = (@ini_get( 'memory_limit' ) != '') ? @ini_get( 'memory_limit' ) : $lang['undefined'];
$disabledfunctions = (strlen( ini_get( 'disable_functions' ) ) > 1) ? @ini_get( 'disable_functions' ) : $lang['undefined'];
$disabledfunctions = str_replace( ",", ", ", $disabledfunctions );
$safemode = (@ini_get( 'safe_mode' ) == 1) ? $lang['safe_mode_on'] : $lang['safe_mode_off'];
$licence = "<a href=\"http://www.mid-team.ws\" target=\"_blank\">Nulled by <font color=\"red\">M.I.D-Team</font></a>";
$offline = ($config['site_offline'] == "no") ? $lang['safe_mode_on'] : "<font color=\"red\">" . $lang['safe_mode_off'] . "</font>";

if( function_exists( 'apache_get_modules' ) ) {
	if( array_search( 'mod_rewrite', apache_get_modules() ) ) {
		$mod_rewrite = $lang['safe_mode_on'];
	} else {
		$mod_rewrite = "<font color=\"red\">" . $lang['safe_mode_off'] . "</font>";
	}
} else {
	$mod_rewrite = $lang['undefined'];
}

$os_version = @php_uname( "s" ) . " " . @php_uname( "r" );
$phpv = phpversion();

if ( function_exists( 'gd_info' ) ) {

	$array=gd_info ();
	$gdversion = "";

	foreach ($array as $key=>$val) {
	  
	  if ($val===true) {
	    $val="Enabled";
	  }
	
	  if ($val===false) {
	    $val="Disabled";
	  }
	
	  $gdversion .= $key.":&nbsp;{$val}, ";
	
	}

} else $gdversion = $lang['undefined'];


$maxupload = str_replace( array ('M', 'm' ), '', @ini_get( 'upload_max_filesize' ) );
$maxupload = formatsize( $maxupload * 1024 * 1024 );

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post" );
$stats_news = $row['count'];

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_subscribe" );
$count_subscribe = $row['count'];

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments" );
$count_comments = $row['count'];

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_comments WHERE approve ='0'" );
$count_c_app = $row['count'];

if( $count_c_app ) {
	
	$count_c_app = $count_c_app . " [ <a href=\"?mod=cmoderation\">{$lang['stat_cmod_link']}</a> ]";

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users" );
$stats_users = $row['count'];

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . USERPREFIX . "_users where banned='yes'" );
$stats_banned = $row['count'];

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post where approve = '0'" );
$approve = $row['count'];

if( $approve and $user_group[$member_id['user_group']]['allow_all_edit'] ) {
	
	$approve = $approve . " [ <a href=\"?mod=editnews&action=list&news_status=2\">{$lang['stat_medit_link']}</a> ]";

}

$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_complaint" );
$c_complaint = $row['count'];
set_cookie ( "dle_compl", $row['count'], 365 );

if( $c_complaint AND $user_group[$member_id['user_group']]['admin_complaint'] ) {

	$c_complaint = $row['count'] . " [ <a href=\"?mod=complaint\">{$lang['stat_complaint_1']}</a> ]";

	if ($row['count'] > intval ( $_COOKIE['dle_compl'] )) {

		$c_complaint .= <<<HTML
<script language="javascript" type="text/javascript">
<!--

$(function(){
	DLEconfirm( '{$lang['opt_complaint_20']}', '{$lang['p_confirm']}', function () {

		document.location='?mod=complaint';

	} );
});

//-->
</script>
HTML;

	}


}

$db->query( "SHOW TABLE STATUS FROM `" . DBNAME . "`" );
$mysql_size = 0;
while ( $r = $db->get_array() ) {
	if( strpos( $r['Name'], PREFIX . "_" ) !== false ) $mysql_size += $r['Data_length'] + $r['Index_length'];
}
$db->free();

$mysql_size = formatsize( $mysql_size );

function dirsize($directory) {
	
	if( ! is_dir( $directory ) ) return - 1;
	
	$size = 0;
	
	if( $DIR = opendir( $directory ) ) {
		
		while ( ($dirfile = readdir( $DIR )) !== false ) {
			
			if( @is_link( $directory . '/' . $dirfile ) || $dirfile == '.' || $dirfile == '..' ) continue;
			
			if( @is_file( $directory . '/' . $dirfile ) ) $size += filesize( $directory . '/' . $dirfile );
			
			else if( @is_dir( $directory . '/' . $dirfile ) ) {
				
				$dirSize = dirsize( $directory . '/' . $dirfile );
				if( $dirSize >= 0 ) $size += $dirSize;
				else return - 1;
			
			}
		
		}
		
		closedir( $DIR );
	
	}
	
	return $size;

}

$cache_size = formatsize( dirsize( "engine/cache" ) );

$dfs = @disk_free_space( "." );
$freespace = formatsize( $dfs );

if( $member_id['user_group'] == 1 ) {
	
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['main_quick']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="50%">
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/uset.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=editusers&action=list"><h3>{$lang['opt_user']}</h3>{$lang['opt_userc']}</a></div></td>
    </tr>
</table>
</td>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/ads.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=banners"><h3>{$lang['opt_banner']}</h3>{$lang['opt_bannerc']}</a></div></td>
    </tr>
</table>
</td>
    </tr>
    <tr>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/tools.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=options&action=syscon"><h3>{$lang['opt_all']}</h3>{$lang['opt_allc']}</a></div></td>
    </tr>
</table>
</td>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/nset.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=newsletter"><h3>{$lang['main_newsl']}</h3>{$lang['main_newslc']}</a></div></td>
    </tr>
</table>
</td>
    </tr>
    <tr>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/spset.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=static"><h3>{$lang['opt_static']}</h3>{$lang['opt_staticd']}</a></div></td>
    </tr>
</table>
</td>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/clean.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=clean"><h3>{$lang['opt_clean']}</h3>{$lang['opt_cleanc']}</a></div></td>
    </tr>
</table>
</td>
    </tr>
    <tr>
        <td>
<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/shield.png" border="0"></td>
        <td valign="middle"><div class="quick"><a onclick="check_files('lokal'); return false;" href="#"><h3>{$lang['mod_anti']}</h3>{$lang['anti_descr']}</a></div></td>
    </tr>
</table>
        <td>

<table width="100%">
    <tr>
        <td width="70" height="70" valign="middle" align="center" style="padding-top:5px;padding-bottom:5px;"><img src="engine/skins/images/next.png" border="0"></td>
        <td valign="middle"><div class="quick"><a href="$PHP_SELF?mod=options&action=options"><h3>{$lang['opt_all_rublik']}</h3>{$lang['opt_all_rublikc']}</a></div></td>
    </tr>
</table>

</td>

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

} else {

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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$lang['main_quick']}</div></td>
    </tr>
</table>
<div class="unterline"></div>
<table width="100%">
    <tr>
        <td width="50%"><div class="quick"><a href="$PHP_SELF?mod=options&action=personal"><img src="engine/skins/images/pset.png" border="0" align="left"><br><h3>{$lang['opt_priv']}</h3>{$lang['opt_privc']}</a></div></td>
        <td><div class="quick"><a href="$PHP_SELF?mod=options&action=options"><img src="engine/skins/images/next.png" border="0" align="left"><br><h3>{$lang['opt_all_rublik']}</h3>{$lang['opt_all_rublikc']}</a></div></td>
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

}

if( $user_group[$member_id['user_group']]['admin_comments'] ) {
	$edit_comments = "&nbsp;[ <a href=\"?mod=comments&action=edit\">{$lang['edit_comm']}</a> ]";
} else $edit_comments = "";

if( $member_id['user_group'] == 1 ) {
	
	echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
function check_files ( folder ){


	if (folder == "snap") {

	    DLEconfirm( '{$lang['anti_snapalert']}', '{$lang['p_confirm']}', function () {
			document.getElementById( 'main_title' ).innerHTML = '{$lang['anti_title']}';
			document.getElementById( 'antivirus' ).innerHTML = '{$lang['anti_box']}';

			ShowLoading('');		
			$.post('engine/ajax/antivirus.php', { folder: folder, key: "{$config['key']}" }, function(data){
		
				HideLoading('');
		
				$('#antivirus').html(data);
		
			});

		} );

	} else {

		document.getElementById( 'main_title' ).innerHTML = '{$lang['anti_title']}';
		document.getElementById( 'antivirus' ).innerHTML = '{$lang['anti_box']}';
	
		ShowLoading('');		
		$.post('engine/ajax/antivirus.php', { folder: folder, key: "{$config['key']}" }, function(data){
		
			HideLoading('');
		
			$('#antivirus').html(data);
		
		});

	}

	return false;
}

//-->
</script>
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation" id="main_title">{$lang['extra_info']}</div></td>
    </tr>
</table>
<div class="unterline"></div>

<div id="antivirus">
	<script type="text/javascript">
		$(function(){
			$("#tabset").buildMbTabset({
				sortable:false,
				position:"left"
			});

			$.ajaxSetup({
				cache: false
			});

			$('#clearbutton').click(function() {

				$('#main_box').html('{$lang['dle_updatebox']}');

				$.get("engine/ajax/adminfunction.php?action=clearcache", function( data ){

					$('#cachesize').html('0 b');
					$('#main_box').html(data);

				});
				return false;
			});

			$('#clearsubscribe').click(function() {

			    DLEconfirm( '{$lang['confirm_action']}', '{$lang['p_confirm']}', function () {

					$('#main_box').html('{$lang['dle_updatebox']}');
					$.get("engine/ajax/adminfunction.php?action=clearsubscribe", function( data ){
						$('#main_box').html(data);
					});
				} );
				return false;
			});

			$('#check_updates').click(function() {

				$('#main_box').html('{$lang['dle_updatebox']}');
				$.get("engine/ajax/updates.php?versionid={$config['version_id']}", function( data ){
					$('#main_box').html(data);
				});
				return false;
			});

			$('#send_notice').click(function() {

				$('#send_result').html('{$lang['dle_updatebox']}');
				var notice = $('#notice').val();
				$.post("engine/ajax/adminfunction.php?action=sendnotice", { notice: notice } , function( data ){
					$('#send_result').append('&nbsp;' + data);
				});
				return false;
			});

		});
	</script>

		<div class="tabset" id="tabset">
			<a id="a" class="tab  {content:'cont_1'}">{$lang['stat_all']}</a><a id="b" class="tab  {content:'cont_2'}">{$lang['main_notice']}</a><a id="c" class="tab  {content:'cont_3'}" >{$lang['stat_auto']}</a>
		</div>

		<div id="cont_1" style="display:none;">
<table width="100%">
    <tr>
        <td width="265" style="padding:2px;">{$lang['stat_allnews']}</td>
        <td>{$stats_news}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_mod']}</td>
        <td>{$approve}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_complaint']}</td>
        <td>{$c_complaint}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_comments']}</td>
        <td>{$count_comments} [ <a href="{$config['http_home_url']}index.php?do=lastcomments" target="_blank">{$lang['last_comm']}</a> ]{$edit_comments}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_cmod']}</td>
        <td>{$count_c_app}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_users']}</td>
        <td>{$stats_users}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_banned']}</td>
        <td><font color="red">{$stats_banned}</font></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_bd']}</td>
        <td>{$mysql_size}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['cache_size']}</td>
        <td><span id="cachesize">{$cache_size}</span></td>
    </tr>
</table>
HTML;
	echo "<br /><input id=\"check_updates\" name=\"check_updates\" class=\"btn btn-inverse\" type=\"button\" value=\"{$lang['dle_udate']}\">&nbsp;<input id=\"clearbutton\" name=\"clearbutton\" class=\"btn btn-danger\" type=\"button\" value=\"{$lang['btn_clearcache']}\">";

	if ($count_subscribe) echo "&nbsp;<input id=\"clearsubscribe\" name=\"clearsubscribe\" class=\"btn btn-danger\" type=\"button\" value=\"{$lang['btn_clearsubscribe']}\">";

	echo "<br /><br /><div id=\"main_box\"></div></div>";

echo <<<HTML

		<div id="cont_2">
HTML;

$row = $db->super_query( "SELECT notice FROM " . PREFIX . "_notice WHERE user_id = '{$member_id['user_id']}'" );

if( $row['notice'] == "" ) {
	
	$row['notice'] = $lang['main_no_notice'];

} else {
	
	$row['notice'] = htmlspecialchars( stripslashes( $row['notice'] ) );

}

echo <<<HTML
<form method="post" action="">
<input type="hidden" name="action" value="send_notice">
<table width="100%">
    <tr>
        <td class="quick"><textarea id="notice" name="notice" style="width:100%;height:200px;background-color:lightyellow;">{$row['notice']}</textarea>
		<div><input id="send_notice" name="send_notice" type="submit" class="btn btn-success" value="{$lang['btn_send']}" style="width:100px;">&nbsp;&nbsp;<span id="send_result"></span></div>
		</td>
    </tr>
</table>
</form>
		</div>


		<div id="cont_3" style="display:none;">
<table width="100%">
    <tr>
        <td style="padding:2px;">{$lang['dle_version']}</td>
        <td>{$config['version_id']}</td>
    </tr>
    <tr>
        <td width="265" style="padding:2px;">{$lang['licence_info']}</td>
        <td>{$licence}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['site_status']}</td>
        <td>{$offline}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_os']}</td>
        <td>{$os_version}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_php']}</td>
        <td>{$phpv}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_mysql']}</td>
        <td>{$db->mysql_version} <b>{$db->mysql_extend}</b></td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_gd']}</td>
        <td>{$gdversion}</td>
    </tr>
    <tr>
        <td style="padding:2px;">Module mod_rewrite</td>
        <td>{$mod_rewrite}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_safemode']}</td>
        <td>{$safemode}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_maxmem']}</td>
        <td>{$maxmemory}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_func']}</td>
        <td>{$disabledfunctions}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_maxfile']}</td>
        <td>{$maxupload}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['free_size']}</td>
        <td>{$freespace}</td>
    </tr>
</table>		</div>

HTML;


	if( ! is_writable( ENGINE_DIR . "/cache/" ) or ! is_writable( ENGINE_DIR . "/cache/system/" ) ) {
		echo "<br><table width=\"100%\" align=\"center\"><tr><td style='padding:3px; border:1px dashed red; background-color:lightyellow;' class=main>
	       $lang[stat_cache]
	          </td></tr><tr><td>&nbsp;</td></tr></table>";
	
	}
	
	if( @file_exists( "install.php" ) ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">$lang[stat_install]</div>
          </td></tr><tr><td>&nbsp;</td></tr></table>";
	}
	
	if( $dfs and $dfs < 20240 ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">$lang[stat_nofree]</div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( !function_exists('iconv') ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_not_min']} <b>iconv</b></div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	$test = "TEST OK";
	
	$test = iconv( "UTF-8", "windows-1251//IGNORE", $test );

	if (!$test) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_iconv']}</div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( !@extension_loaded('xml') ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_not_min']} <b>XML</b></div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( !@extension_loaded('zlib') ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_not_min']} <b>ZLib</b></div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	$status = strtolower(ini_get('register_globals'));

	if( $status AND $status != "off" ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_secfault']}</div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	$status = strtolower(ini_get('allow_url_include'));

	if( $status AND $status != "off" ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
         <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_secfault_3']}</div>
         </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( version_compare($phpv, '5.1', '<') ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_phperror']}</div>
          </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( $config['cache_type'] && !$mcache ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_m_fail']}</div>
          </td></tr><tr><td>&nbsp;</td></tr></table>";
	}

	if( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() ) {
		echo "<br><table width=\"100%\" align=center><tr><td>
       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">{$lang['stat_magic']}</div>
          </td></tr><tr><td>&nbsp;</td></tr></table>";
	}	


	$check_files       = array(
		"/templates/.htaccess",
		"/uploads/.htaccess",
		"/uploads/files/.htaccess",
		"/engine/data/.htaccess",
		"/engine/cache/.htaccess",
		"/engine/cache/system/.htaccess",
	);

	foreach ($check_files as $file) {

		if( is_writable(ROOT_DIR . $file) ) {

			echo "<br><table width=\"100%\" align=center><tr><td>
	       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">".str_replace("{file}", $file, $lang['stat_secfault_4'])."</div>
	          </td></tr><tr><td>&nbsp;</td></tr></table>";

		}


		if( !file_exists( ROOT_DIR .$file ) ) {
			echo "<br><table width=\"100%\" align=center><tr><td>
	       <div class=\"ui-state-error ui-corner-all\" style=\"padding:10px;\">".str_replace("{folder}", $file, $lang['stat_secfault_2'])."</div>
	          </td></tr><tr><td>&nbsp;</td></tr></table>";
		}

	}

echo <<<HTML
</div>
</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
HTML;

} else {

$row = $db->super_query( "SELECT notice FROM " . PREFIX . "_notice WHERE user_id = '{$member_id['user_id']}'" );

if( $row['notice'] == "" ) {
	
	$row['notice'] = $lang['main_no_notice'];

} else {
	
	$row['notice'] = htmlspecialchars( stripslashes( $row['notice'] ) );

}

echo <<<HTML
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
        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation" id="main_title">{$lang['extra_info']}</div></td>
    </tr>
</table>
<div class="unterline"></div>

	<script type="text/javascript">
		$(function(){
			$("#tabset").buildMbTabset({
				sortable:false,
				position:"left"
			});

			$('#send_notice').click(function() {

				$('#send_result').html('{$lang['dle_updatebox']}');
				var notice = $('#notice').val();
				$.post("engine/ajax/adminfunction.php?action=sendnotice", { notice: notice } , function( data ){
					$('#send_result').append('&nbsp;' + data);
				});
				return false;
			});

		});
	</script>

		<div class="tabset" id="tabset">
			<a id="a" class="tab  {content:'cont_1'}">{$lang['stat_all']}</a><a id="b" class="tab  {content:'cont_2'}">{$lang['main_notice']}</a>
		</div>

		<div id="cont_1">
<table width="100%">
    <tr>
        <td width="265" style="padding:2px;">{$lang['stat_allnews']}</td>
        <td>{$stats_news}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_mod']}</td>
        <td>{$approve}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_complaint']}</td>
        <td>{$c_complaint}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_comments']}</td>
        <td>{$count_comments} [ <a href="{$config['http_home_url']}index.php?do=lastcomments" target="_blank">{$lang['last_comm']}</a> ]{$edit_comments}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_cmod']}</td>
        <td>{$count_c_app}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_users']}</td>
        <td>{$stats_users}</td>
    </tr>
    <tr>
        <td style="padding:2px;">{$lang['stat_banned']}</td>
        <td><font color="red">{$stats_banned}</font></td>
    </tr>
</table>
		</div>

		<div id="cont_2">
<form method="post" action="">
<input type="hidden" name="action" value="send_notice">
<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td class="quick"><textarea id="notice" name="notice" style="width:100%;height:200px;background-color:lightyellow;">{$row['notice']}</textarea>
		<div><input id="send_notice" name="send_notice" type="submit" class="buttons" value="{$lang['btn_send']}" style="width:100px;">&nbsp;&nbsp;<span id="send_result"></span></div>
		</td>
    </tr>
</table>
</div>
</form>
		</div>

</td>
        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
HTML;

}

echofooter();
?>