<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "9.2";
$config['allow_recaptcha'] = "0";
$config['recaptcha_public_key'] = "6LfoOroSAAAAAEg7PViyas0nRqCN9nIztKxWcDp_";
$config['recaptcha_private_key'] = "6LfoOroSAAAAAMgMr_BTRMZy20PFir0iGT2OQYZJ";
$config['recaptcha_theme'] = "clean";
unset($config['allow_upload']);
unset($config['news_captcha']);

$tableSchema = array();

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `admin_tagscloud` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET `admin_tagscloud` = '1' WHERE id = '1'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_comments` ADD INDEX `post_id` ( `post_id` ), ADD INDEX `approve` ( `approve` )";

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

$fdir = opendir( ENGINE_DIR . '/cache/system/' );
while ( $file = readdir( $fdir ) ) {
	if( $file != '.' and $file != '..' and $file != '.htaccess' ) {
		@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
	}
}

@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) $error_info = "Всего запланировано запросов: <b>".$db->query_num."</b> Неудалось выполнить запросов: <b>".$db->error_count."</b>. Возможно они уже выполнены ранее."; else $error_info = "";

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>9.0</b> до версии <b>9.2</b> успешно завершено.<br />{$error_info}<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"92\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>