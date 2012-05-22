<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "7.3";
$config['flv_watermark'] = "0";
$config['allow_add_tags'] = "0";
$config['allow_fixed'] = "1";
$config['max_file_size'] = "4096";
$config['max_file_count'] = "0";


$handler = fopen(ENGINE_DIR.'/data/config.php', "w") or die("Извините, но невозможно записать информацию в файл <b>.engine/data/config.php</b>.<br />Проверьте правильность проставленного CHMOD!");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value)
{
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);


@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) $error_info = "Всего запланировано запросов: <b>".$db->query_num."</b> Неудалось выполнить запросов: <b>".$db->error_count."</b>. Возможно они уже выполнены ранее."; else $error_info = "";

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>7.2</b> до версии <b>7.3</b> успешно завершено.<br />{$error_info}<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"7.5\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>