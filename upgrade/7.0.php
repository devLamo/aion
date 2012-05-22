<?php

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$config['version_id'] = "7.2";
$config['allow_quick_wysiwyg'] = "0";
$config['sec_addnews'] = "1";
$config['mail_pm'] = "0";
$config['allow_change_sort'] = "0";
$config['registration_rules'] = "0";
$config['allow_tags'] = "0";


$tableSchema = array();

$tableSchema[] = "DROP TABLE IF EXISTS " . PREFIX . "_tags";
$tableSchema[] = "CREATE TABLE " . PREFIX . "_tags (
  `id` INT(11) NOT NULL auto_increment,
  `news_id` INT(11) NOT NULL default '0',
  `tag` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `news_id` (`news_id`),
  KEY `tag` (`tag`)
) TYPE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_rssinform` ADD `rss_date_format` VARCHAR( 20 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_files` CHANGE `date` `date` VARCHAR( 15 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_images` CHANGE `date` `date` VARCHAR( 15 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banned` CHANGE `date` `date` VARCHAR( 15 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `editdate` VARCHAR( 15 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `editor` VARCHAR( 40 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `reason` VARCHAR( 255 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `view_edit` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD `tags` VARCHAR( 255 ) NOT NULL DEFAULT ''";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_banned` CHANGE `ip` `ip` VARCHAR( 50 ) NOT NULL";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `allow_image` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_usergroups` ADD `news_sec_code` TINYINT( 1 ) NOT NULL DEFAULT '1'";

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `restricted` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `restricted_days` SMALLINT( 4 ) NOT NULL DEFAULT '0'";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_users` ADD `restricted_date` VARCHAR( 20 ) NOT NULL DEFAULT ''";

$tableSchema[] = "INSERT INTO " . PREFIX . "_email values (6, 'pm', 'Уважаемый {%username%},\r\n\r\nуведомляем вас о том, что на сайте  $url вам было отправлено персональное сообщение.\r\n\r\n------------------------------------------------\r\nКраткая информация о сообщении\r\n------------------------------------------------\r\n\r\nОтправитель: {%fromusername%}\r\nДата  получения: {%date%}\r\nЗаголовок: {%title%}\r\n\r\n------------------------------------------------\r\nТекст сообщения\r\n------------------------------------------------\r\n\r\n{%text%}\r\n\r\nС уважением,\r\n\r\nАдминистрация $url')";
$tableSchema[] = "INSERT INTO " . PREFIX . "_static (`name`, `descr`, `template`, `allow_br`, `allow_template`, `grouplevel`, `tpl`, `metadescr`, `metakeys`, `views`, `template_folder`) VALUES ('dle-rules-page', 'Общие правила на сайте', '<b>Общие правила поведения на сайте:</b><br /><br />Начнем с того, что на сайте общаются сотни людей, разных религих и взглядов, и все они являются полноправными посетителями нашего сайта, поэтому если мы хотим чтобы это сообщество людей функционировало нам и необходимы правила. Мы настоятельно рекомендуем прочитать настоящие правила, это займет у вас всего минут пять, но сбережет нам и вам время и поможет сделать сайт более интересным и организованным.<br /><br />Начнем с того, что на нашем сайте нужно вести себя уважительно ко всем посетителям сайта. Не надо оскорблений по отношению к участникам, это всегда лишнее. Если есть претензии - обращайтесь к Админам или Модераторам (воспользуйтесь личными сообщениями). Оскорбление других посетителей считается у нас одним из самых тяжких нарушений и строго наказывается администрацией. <b>У нас строго запрещен расизм, религиозные и политические высказывания.</b> Заранее благодарим вас за понимание и за желание сделать наш сайт более вежливым и дружелюбным.<br /><br /><b>На сайте строго запрещено:</b> <br /><br />- сообщения, не относящиеся к содержанию статьи или к контексту обсуждения<br />- оскорбление и угрозы в адрес посетителей сайта<br />- в комментариях запрещаются выражения, содержащие ненормативную лексику, унижающие человеческое достоинство, разжигающие межнациональную рознь<br />- спам, а также реклама любых товаров и услуг, иных ресурсов, СМИ или событий, не относящихся к контексту обсуждения статьи<br /><br />Давайте будем уважать друг друга и сайт, на который Вы и другие читатели приходят пообщаться и высказать свои мысли. Администрация сайта оставляет за собой право удалять комментарии или часть комментариев, если они не соответствуют данным требованиям.<br /><br />При нарушении правил вам может быть дано <b>предупреждение</b>. В некоторых случаях может быть дан бан <b>без предупреждений</b>. По вопросам снятия бана писать администратору.<br /><br /><b>Оскорбление</b> администраторов или модераторов также караются <b>баном</b> - уважайте чужой труд.<br /><br /><div align=\"center\">{ACCEPT-DECLINE}</div>', 1, 1, 'all', '', 'Общие правила', 'Общие правила', 0, '')";
$tableSchema[] = "UPDATE " . PREFIX . "_rssinform SET rss_date_format='j F Y H:i'";

if (!$config['news_captcha']) {

	$tableSchema[] = "UPDATE " . PREFIX . "_usergroups SET news_sec_code='0'";

}

$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post` ADD INDEX ( `comm_num` )";
$tableSchema[] = "ALTER TABLE `" . PREFIX . "_post`  ADD INDEX ( `tags` )";


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

@unlink(ENGINE_DIR.'/cache/system/usergroup.php');
@unlink(ENGINE_DIR.'/cache/system/vote.php');
@unlink(ENGINE_DIR.'/cache/system/banners.php');
@unlink(ENGINE_DIR.'/cache/system/category.php');
@unlink(ENGINE_DIR.'/cache/system/banned.php');
@unlink(ENGINE_DIR.'/cache/system/cron.php');
@unlink(ENGINE_DIR.'/data/snap.db');

clear_cache();

if ($db->error_count) $error_info = "Всего запланировано запросов: <b>".$db->query_num."</b> Неудалось выполнить запросов: <b>".$db->error_count."</b>. Возможно они уже выполнены ранее."; else $error_info = "";

msgbox("info","Информация", "<form action=\"index.php\" method=\"GET\">Обновление базы данных с версии <b>7.0</b> до версии <b>7.2</b> успешно завершено.<br />{$error_info}<br />Нажмите далее для продолжения процессa обновления скрипта<br /><br /><input type=\"hidden\" name=\"next\" value=\"7.3\"><input class=\"edit\" type=\"submit\" value=\"Далее ...\"></form>");
?>