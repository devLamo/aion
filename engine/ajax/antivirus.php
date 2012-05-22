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
 Файл: antivirus.php
-----------------------------------------------------
 Назначение: Проверка на подозрительные файлы
=====================================================
*/
@session_start();
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define('DATALIFEENGINE', true);
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -12 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR.'/data/config.php';

if ($config['http_home_url'] == "") {

	$config['http_home_url'] = explode("engine/ajax/antivirus.php", $_SERVER['PHP_SELF']);
	$config['http_home_url'] = reset($config['http_home_url']);
	$config['http_home_url'] = "http://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

}

require_once ENGINE_DIR.'/classes/mysql.php';
require_once ENGINE_DIR.'/data/dbconfig.php';
require_once ENGINE_DIR.'/inc/include/functions.inc.php';

$selected_language = $config['langs'];

if (isset( $_COOKIE['selected_language'] )) { 

	$_COOKIE['selected_language'] = trim(totranslit( $_COOKIE['selected_language'], false, false ));

	if ($_COOKIE['selected_language'] != "" AND @is_dir ( ROOT_DIR . '/language/' . $_COOKIE['selected_language'] )) {
		$selected_language = $_COOKIE['selected_language'];
	}

}
if ( file_exists( ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng' ) ) {
	require_once ROOT_DIR.'/language/'.$selected_language.'/adminpanel.lng';
} else die("Language file not found");

$config['charset'] = ($lang['charset'] != '') ? $lang['charset'] : $config['charset'];

require_once ENGINE_DIR.'/modules/sitelogin.php';

if(($member_id['user_group'] != 1)) {die ("error");}

require_once ENGINE_DIR.'/classes/antivirus.class.php';

$antivirus = new antivirus();

if ($_REQUEST['folder'] == "lokal"){

	$antivirus->scan_files( ROOT_DIR."/backup", false, true );
	$antivirus->scan_files( ROOT_DIR."/engine", false, true );
	$antivirus->scan_files( ROOT_DIR."/language", false, true );
	$antivirus->scan_files( ROOT_DIR."/templates", false, false );
	$antivirus->scan_files( ROOT_DIR."/uploads", false, true );
	$antivirus->scan_files( ROOT_DIR."/upgrade", false, true );
	$antivirus->scan_files( ROOT_DIR, false, true );

} elseif ($_REQUEST['folder'] == "snap") {

	$antivirus->scan_files( ROOT_DIR."/backup", true );
	$antivirus->scan_files( ROOT_DIR."/engine", true );
	$antivirus->scan_files( ROOT_DIR."/language", true );
	$antivirus->scan_files( ROOT_DIR."/templates", true );
	$antivirus->scan_files( ROOT_DIR."/uploads", true );
	$antivirus->scan_files( ROOT_DIR."/upgrade", true );
	$antivirus->scan_files( ROOT_DIR, true );

	$filecontents = "";

    foreach( $antivirus->snap_files as $idx => $data )
    {
		$filecontents .= $data['file_path']."|".$data['file_crc']."\r\n";
    }

    $filehandle = fopen(ENGINE_DIR.'/data/snap.db', "w+");
    fwrite($filehandle, $filecontents);
    fclose($filehandle);
	@chmod(ENGINE_DIR.'/data/snap.db', 0666);

} else {

	$antivirus->snap = false;
	$antivirus->scan_files( ROOT_DIR."/backup", false, true );
	$antivirus->scan_files( ROOT_DIR."/engine", false, true );
	$antivirus->scan_files( ROOT_DIR."/language", false, true );
	$antivirus->scan_files( ROOT_DIR."/templates", false, false );
	$antivirus->scan_files( ROOT_DIR."/uploads", false, true );
	$antivirus->scan_files( ROOT_DIR."/upgrade", false, true );
	$antivirus->scan_files( ROOT_DIR, false, true );

}

@header("Content-type: text/html; charset=".$config['charset']);

if (count($antivirus->bad_files)) {

echo <<<HTML
<table width="100%">
    <tr>
        <td colspan="2" style="padding:2px;">{$lang['anti_result']}</td>
    </tr>
    <tr>
        <td width="350" style="padding:2px;">{$lang['anti_file']}</td>
        <td width="100">{$lang['anti_size']}</td>
        <td width="150">{$lang['addnews_date']}</td>
        <td>&nbsp;</td>
    </tr>
HTML;

  foreach( $antivirus->bad_files as $idx => $data )
  { 

	if ($data['file_size'] < 50000) $color = "<font color=\"green\">";
	elseif ($data['file_size'] < 100000) $color = "<font color=\"blue\">";
	else $color = "<font color=\"red\">";

	$data['file_size'] = formatsize ($data['file_size']);
	if ($data['type']) $type = $lang['anti_modified']; else $type = $lang['anti_not'];

	$data['file_path'] = preg_replace("/([0-9]){10}_/", "*****_", $data['file_path']);

echo <<<HTML
    <tr>
        <td style="padding:2px;">{$color}{$data['file_path']}</font></td>
        <td>{$color}{$data['file_size']}</font></td>
        <td>{$color}{$data['file_date']}</font></td>
        <td>{$color}{$type}</font></td>
    </tr>
	<tr><td background="engine/skins/images/mline.gif" height=1 colspan=4></td></tr>
HTML;
  }
}
elseif ($_REQUEST['folder'] == "snap") {

echo <<<HTML
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="3">{$lang['anti_creates']}</td>
    </tr>
HTML;

}
else {

echo <<<HTML
<table width="100%">
    <tr>
        <td style="padding:2px;" colspan="3">{$lang['anti_notfound']}</td>
    </tr>
HTML;

}

echo <<<HTML
    <tr>
        <td style="padding:2px;" colspan=3><input onclick="check_files('global'); return false;" type="button" class="btn btn-primary" style="width:250px;" value="{$lang['anti_global']}"> <input onclick="check_files('snap'); return false;" type="button" class="btn btn-warning" style="width:150px;" value="{$lang['anti_snap']}"></td>
    </tr></table>
HTML;
?>