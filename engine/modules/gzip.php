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
 Файл: gzip.php
-----------------------------------------------------
 Назначение: Сжатие gzip
=====================================================
*/
if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

function CheckCanGzip(){

if (headers_sent() || connection_aborted() || !function_exists('ob_gzhandler') || ini_get('zlib.output_compression')) return 0; 

if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) return "x-gzip"; 
if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) return "gzip"; 

return 0; 
}


function GzipOut($debug=0){
	global $config, $Timer, $db, $tpl, $_DOCUMENT_DATE;

	if ($debug) $s = "
<!-- Время выполнения скрипта ".$Timer->stop()." секунд -->
<!-- Время затраченное на компиляцию шаблонов ".round($tpl->template_parse_time, 5)." секунд -->
<!-- Время затраченное на выполнение MySQL запросов: ".round($db->MySQL_time_taken, 5)." секунд -->
<!-- Общее количество MySQL запросов ".$db->query_num." -->";

	if( $debug AND function_exists( "memory_get_peak_usage" ) ) $s .="\n<!-- Затрачено оперативной памяти ".round(memory_get_peak_usage()/(1024*1024),2)." MB -->";

	if($_DOCUMENT_DATE)
	{
		@header ("Last-Modified: " . date('r', $_DOCUMENT_DATE) ." GMT");
	
	}

	if ($config['allow_gzip'] != "yes") {if ($debug) echo $s; ob_end_flush(); return;}

    $ENCODING = CheckCanGzip(); 

    if ($ENCODING){
        $s .= "\n<!-- Для вывода использовалось сжатие $ENCODING -->\n"; 
        $Contents = ob_get_contents(); 
        ob_end_clean(); 

        if ($debug){
            $s .= "<!-- Общий размер файла: ".strlen($Contents)." байт "; 
            $s .= "После сжатия: ".
                   strlen(gzencode($Contents, 1, FORCE_GZIP)).
                   " байт -->"; 
            $Contents .= $s; 
        }

        header("Content-Encoding: $ENCODING"); 

		$Contents = gzencode($Contents, 1, FORCE_GZIP);
		echo $Contents;
        exit; 

    }else{

        ob_end_flush(); 
        exit; 

    }
}
?>