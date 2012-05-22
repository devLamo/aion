<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "5.7";
$config['mail_news'] = "0";
$config['mail_comments'] = "0";
$config['key'] = "";

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

$config_dbhost = DBHOST;
$config_dbname = DBNAME;
$config_dbuser = DBUSER;
$config_dbpasswd = DBPASS;
$config_dbprefix = PREFIX;

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}"); 

define ("USERPREFIX", "{$config_dbprefix}"); 

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/dbconfig.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_vote` CHANGE `category` `category` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_pm` ADD `reply` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `metadescr` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `metakeys` TEXT NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `icon` VARCHAR( 200 ) NOT NULL DEFAULT ''";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (4, 'new_news', 'Уважаемый администратор,\r\n\r\nуведомляем вас о том, что на сайт  {$config['http_home_url']} была добавлена новость, которая в данный момент ожидает модерации.\r\n\r\n------------------------------------------------\r\nКраткая информация о новости\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nЗаголовок новости: {%title%}\r\nКатегория: {%category%}\r\nДата добавления: {%date%}\r\n\r\nС уважением,\r\n\r\nАдминистрация {$config['http_home_url']}')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (5, 'comments', 'Уважаемый администратор,\r\n\r\nуведомляем вас о том, что на сайт  {$config['http_home_url']} был добавлен комментарий.\r\n\r\n------------------------------------------------\r\nКраткая информация о комментарии\r\n------------------------------------------------\r\n\r\nАвтор: {%username%}\r\nДата добавления: {%date%}\r\nСсылка на новость: {%link%}\r\nIP адрес: {%ip%}\r\n\r\n------------------------------------------------\r\nТекст комментария\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nС уважением,\r\n\r\nАдминистрация {$config['http_home_url']}')";

  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');

clear_cache();

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>5.5</b> до версии <b>5.7</b> успешно завершено.<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"6.0\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>