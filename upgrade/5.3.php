<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


$config['version_id'] = "5.5";
$config['no_date'] = "1";
$config['related_news'] = "0";
$config['key'] = "";

unset ($config['cron']);

$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_pm` CHANGE `user` `user` MEDIUMINT( 8 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_static` ADD `tpl` VARCHAR( 40 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `short_tpl` VARCHAR( 40 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_category` ADD `full_tpl` VARCHAR( 40 ) NOT NULL DEFAULT ''";

  foreach($tableSchema as $table) {
     $db->query ($table);
   }

@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');

clear_cache();

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>5.3</b> до версии <b>5.5</b> успешно завершено.<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"5.5\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>