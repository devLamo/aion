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
 Файл: files.php
-----------------------------------------------------
 Назначение: управление загруженными картинками
=====================================================
*/
if( ! defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$allowed_extensions = array ("gif", "jpg", "png", "jpe", "jpeg" );

if( $_GET['userdir'] ) $userdir = totranslit( $_GET['userdir'], true, false ) . "/"; else $userdir = "";
if( $_GET['sub_dir'] ) $sub_dir = totranslit( $_GET['sub_dir'], true, false ) . "/"; else $sub_dir = "";

if ( $userdir == "files/" ) msg( "error", $lang['addnews_denied'], $lang['index_denied'] );

$config_path_image_upload = ROOT_DIR . "/uploads/" . $userdir . $sub_dir;

if( ! @is_dir( $config_path_image_upload ) ) msg( "error", $lang['addnews_denied'], "Directory {$userdir} not found" );

if( $action == "doimagedelete" ) {
	
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt! User not found" );
	
	}
	
	if( ! isset( $_POST['images'] ) ) {
		msg( "info", $lang['images_delerr'], $lang['images_delerr_1'], "$PHP_SELF?mod=files" );
	}

	foreach ( $_POST['images'] as $image ) {

		$image = totranslit($image);

		if( stripos ( $image, ".htaccess" ) !== false ) die("Hacking attempt!");

		$img_name_arr = explode( ".", $image );
		$type = totranslit( end( $img_name_arr ) );

		if( !in_array( $type, $allowed_extensions ) ) die("Hacking attempt!");

		@unlink( $config_path_image_upload . $image );
		@unlink( $config_path_image_upload . "thumbs/" . $image );

		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '37', '{$image}')" );

	}
}

	echoheader( '', '' );

	echo <<<HTML
<div style="padding-top:5px;padding-bottom:2px;">
<table width="100%">
    <tr>
        <td width="4"><img src="{$config['http_home_url']}engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
        <td background="{$config['http_home_url']}engine/skins/images/tl_oo.gif"><img src="{$config['http_home_url']}engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
        <td width="6"><img src="{$config['http_home_url']}engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
    </tr>
    <tr>
        <td background="{$config['http_home_url']}engine/skins/images/tl_lb.gif"><img src="{$config['http_home_url']}engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
        <td style="padding:5px;" bgcolor="#FFFFFF">
HTML;

echo "<table width=\"100%\">
    <tr>
        <td bgcolor=\"#EFEFEF\" height=\"29\" style=\"padding-left:10px;\"><div class=\"navigation\">{$lang['uploaded_file_list']}</div></td>
    </tr>
</table>
<div class=\"unterline\"></div>
<table width=100%>
    <form action=\"\" method=\"POST\">";

$img_dir = opendir( $config_path_image_upload );
$i = 0;
$total_size = 0;
$this_size_2 = 0;

while ( $file = readdir( $img_dir ) ) {
	$images_in_dir[] = $file;
}

natcasesort( $images_in_dir );
reset( $images_in_dir );

foreach ( $images_in_dir as $file ) {
	
	$img_type = explode( ".", $file );
	$img_type = totranslit( end( $img_type ) );
	
	if( in_array( $img_type, $allowed_extensions ) AND is_file( $config_path_image_upload . $file ) ) {
		
		$i ++;
		$this_size = @filesize( $config_path_image_upload . $file );
		$img_info = @getimagesize( $config_path_image_upload . $file );
		$total_size += $this_size + $this_size_2;
		
			echo "
	  <tr>
	  <td style=\"padding:2px;\">&nbsp;<a class=maintitle target=_blank href=\"" . $config['http_home_url'] . "uploads/" . $userdir . $sub_dir . "$file\">$file</a></td>
	  <td align=\"center\" width=\"180\">$preview&nbsp;</td>
	  <td align=\"right\" width=\"60\">$img_info[0]x$img_info[1]</td>
	  <td align=\"right\" width=\"60\"><nobr>" . formatsize( $this_size ) . "</nobr></td>
	  <td align=\"center\" width=\"10\">
          <input type=\"checkbox\" name=\"images[{$file}]\" value=\"$file\" style=\"border: 0; background: transparent;\">
	  </tr><tr><td background=\"{$config['http_home_url']}engine/skins/images/mline.gif\" height=\"1\" colspan=\"5\"></td></tr>";

	}
}

if( $i > 0 ) {
	
	echo "<tr><td height=\"16\"><td colspan=\"5\" align=\"right\"><br><input class=\"btn btn-danger\" type=\"submit\" value=\" {$lang['images_del']} \"></tr>";
	echo "<tr height=\"1\"><td colspan=\"5\"><b>$lang[images_size]</b> " . formatsize( $total_size ) . '</tr>';

}


	echo "<input type=\"hidden\" name=\"action\" value=\"doimagedelete\"><input type=\"hidden\" name=\"user_hash\" value=\"$dle_login_hash\" />
</form>
<tr height=\"1\">
<td colspan=\"5\"><b>{$lang['images_listdir']}</b> 
<select onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\"><option value=$PHP_SELF?mod=files>--</option>";
	
	$current_dir = opendir( ROOT_DIR . "/uploads" );
	
	while ( $entryname = readdir( $current_dir ) ) {
		
		if( is_dir( ROOT_DIR . "/uploads/$entryname" ) AND ($entryname != "." and $entryname != ".." and $entryname != "files") ) {
			
			if( $userdir == $entryname . "/" ) $sel_dir = "selected";
			else $sel_dir = "";
			
			if( $entryname == "fotos" ) $listname = $lang['images_foto'];
			elseif( $entryname == "thumbs" ) $listname = $lang['images_thumb'];
			elseif( $entryname == "posts" ) $listname = $lang['images_news'];
			else $listname = $entryname;
			
			echo "<option value=\"$PHP_SELF?mod=files&userdir=" . str_replace( ' ', '%20', $entryname ) . "\" $sel_dir>$listname";
			echo "</option>";
		}
	}

	$current_dir = opendir( ROOT_DIR . "/uploads/posts" );
	
	while ( $entryname = readdir( $current_dir ) ) {
		
		if( is_dir( ROOT_DIR . "/uploads/posts/$entryname" ) and ($entryname != "." and $entryname != ".." and $entryname != "thumbs") ) {
			
			if( $sub_dir == $entryname . "/" ) $sel_dir = "selected";
			else $sel_dir = "";
			
			echo "<option value=\"$PHP_SELF?mod=files&userdir=posts&sub_dir=" . str_replace( ' ', '%20', $entryname ) . "\" $sel_dir>{$lang['images_news']} / $entryname";
			echo "</option>";
		}
	}

	echo "</select></tr></table>";

	echo <<<HTML
</td>
        <td background="{$config['http_home_url']}engine/skins/images/tl_rb.gif"><img src="{$config['http_home_url']}engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
    </tr>
    <tr>
        <td><img src="{$config['http_home_url']}engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
        <td background="{$config['http_home_url']}engine/skins/images/tl_ub.gif"><img src="{$config['http_home_url']}engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
        <td><img src="{$config['http_home_url']}engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
    </tr>
</table>
</div>
HTML;

	echofooter();

?>