<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2008 SoftNews Media Group
=====================================================
 Данный код защищен авторскими правами
=====================================================
 Файл: mysql.php
-----------------------------------------------------
 Назначение: Класс для работы с базой данных
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

if ( extension_loaded('mysqli') AND version_compare("5.0.5", phpversion(), "!=") )
{
	include_once( "mysqli.class.php" );
}
else
{
	include_once( "mysql.class.php" );
}

?>