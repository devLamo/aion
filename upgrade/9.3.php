<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "9.4";
$config['auth_metod'] = "0";
$config['comments_ajax'] = "0";
$config['create_catalog'] = "0";
$config['mobile_news'] = "10";
$config['reg_question'] = "0";

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `allow_count` TINYINT(1) NOT NULL DEFAULT '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_complaint` CHANGE `to` `to` VARCHAR(255) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `news_question` TINYINT(1) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `comments_question` TINYINT(1) NOT NULL DEFAULT '0'";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_admin_logs";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_admin_logs (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL DEFAULT '',
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(16) NOT NULL DEFAULT '',
  `action` int(11) NOT NULL DEFAULT '0',
  `extras` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_question";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_question (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

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

$video_config['fullsizeview'] = "2";

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

if ($db->error_count) {

	$error_info = "Всего запланировано запросов: <b>".$db->query_num."</b> Неудалось выполнить запросов: <b>".$db->error_count."</b>. Возможно они уже выполнены ранее.<br /><br /><div class=\"quote\"><b>Список не выполненных запросов:</b><br /><br />"; 

	foreach ($db->query_list as $value) {

		$error_info .= $value['query']."<br /><br />";

	}

	$error_info .= "</div>";

} else $error_info = "";

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>9.3</b> до версии <b>9.4</b> успешно завершено.<br />{$error_info}<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"94\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>