<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "9.0";
$config['fast_search'] = "0";
$config['login_log'] = "0";
unset($config['ajax']);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `flood_news` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `max_day_news` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `force_leech` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `edit_limit` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `captcha_pm` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `max_pm_day` SMALLINT( 6 ) NOT NULL DEFAULT '0', ADD `max_mail_day` SMALLINT( 6 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_flood` CHANGE `ip` `ip` VARCHAR( 40 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_flood` ADD `flag` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD INDEX ( `flag` )";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_sendlog";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_sendlog (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(40) NOT NULL DEFAULT '',
  `date` varchar(20) NOT NULL DEFAULT '',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `date` (`date`),
  KEY `flag` (`flag`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_login_log";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_login_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) NOT NULL DEFAULT '',
  `count` smallint(6) NOT NULL DEFAULT '0',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `date` (`date`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

foreach($tableSchema as $table) {
	$db->query ($table);
}


$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

require_once(ENGINE_DIR.'/data/videoconfig.php');

$video_config['tube_related'] = "1";
$video_config['tube_dle'] = "0";

$con_file = fopen(ENGINE_DIR.'/data/videoconfig.php', "w+") or die("Извините, но невозможно создать файл <b>.engine/data/videoconfig.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite( $con_file, "<?PHP \n\n//Videoplayers Configurations\n\n\$video_config = array (\n\n" );
foreach ( $video_config as $name => $value ) {
		
	fwrite( $con_file, "'{$name}' => \"{$value}\",\n\n" );
	
}
fwrite( $con_file, ");\n\n?>" );
fclose($con_file);

$fdir = opendir( ENGINE_DIR . '/cache/system/' );
while ( $file = readdir( $fdir ) ) {
	if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
		@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
	}
}

@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) $error_info = "Всего запланировано запросов: <b>".$db->query_num."</b> Неудалось выполнить запросов: <b>".$db->error_count."</b>. Возможно они уже выполнены ранее."; else $error_info = "";

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>8.5</b> до версии <b>9.0</b> успешно завершено.<br />{$error_info}<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"9.2\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>