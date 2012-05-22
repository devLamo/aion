<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "6.5";
$config['key'] = "";
$config['short_title'] = "Главная страница";
$config['allow_rss'] = "1";
$config['rss_mtype'] = "0";
$config['rss_number'] = $config['news_number'];
$config['rss_format'] = "1";


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
$config_userprefix = USERPREFIX;

$dbconfig = <<<HTML
<?PHP

define ("DBHOST", "{$config_dbhost}"); 

define ("DBNAME", "{$config_dbname}");

define ("DBUSER", "{$config_dbuser}");

define ("DBPASS", "{$config_dbpasswd}");  

define ("PREFIX", "{$config_dbprefix}"); 

define ("USERPREFIX", "{$config_dbprefix}"); 

define ("COLLATE", "cp1251");

\$db = new db;

?>
HTML;

$con_file = fopen(ENGINE_DIR.'/data/dbconfig.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/dbconfig.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($con_file, $dbconfig);
fclose($con_file);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `flag` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `template_folder` VARCHAR( 50 ) NOT NULL DEFAULT ''";

  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>6.3</b> до версии <b>6.5</b> успешно завершено.<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"next\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>