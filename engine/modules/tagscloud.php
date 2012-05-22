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
 Файл: tagscloud.php
-----------------------------------------------------
 Назначение: Формирование облака тегов
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

$is_change = false;

if ($config['allow_cache'] != "yes") { $config['allow_cache'] = "yes"; $is_change = true;}

$tpl->result['tags_cloud'] = dle_cache("tagscloud", $config['skin']);

if ($tpl->result['tags_cloud'] === false) {

	$counts = array();
	$tags = array();
	$list = array();
	$sizes = array( "clouds_xsmall", "clouds_small", "clouds_medium", "clouds_large", "clouds_xlarge" );
	$min   = 1;
	$max   = 1;
	$range = 1;

	$db->query("SELECT SQL_CALC_FOUND_ROWS tag, COUNT(*) AS count FROM " . PREFIX . "_tags GROUP BY tag ORDER BY count DESC LIMIT 0,40");

	while($row = $db->get_row()){

		$tags[$row['tag']] = $row['count'];
		$counts[] = $row['count'];

	}
	$db->free();

	if (count($counts)) {
		$min   = min($counts);
		$max   = max($counts);
		$range = ($max-$min);
	}

	if (!$range) $range = 1;

	foreach ($tags as $tag => $value) {

		$list[$tag]['tag']   = $tag;
		$list[$tag]['size']  = $sizes[sprintf("%d", ($value-$min)/$range*4 )];
		$list[$tag]['count']  = $value;
	}

	usort ($list, "compare_tags");
	$tags = array();	

	foreach ($list as $value) {

		if (trim($value['tag']) != "" ) {

			if ($config['allow_alt_url'] == "yes")
	        	$tags[] = "<a href=\"".$config['http_home_url']."tags/".urlencode($value['tag'])."/\" class=\"{$value['size']}\" title=\"".$lang['tags_count']." ".$value['count']."\">".$value['tag']."</a>";
			else
				$tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=".urlencode($value['tag'])."\" class=\"{$value['size']}\" title=\"".$lang['tags_count']." ".$value['count']."\">".$value['tag']."</a>";

		}

	}

	$tpl->result['tags_cloud'] = implode(", ", $tags);

	$row = $db->super_query("SELECT FOUND_ROWS() as count");

	if ($row['count'] >= 40) {

		if ($config['allow_alt_url'] == "yes")
        	$tpl->result['tags_cloud'] .= "<br /><br /><a href=\"".$config['http_home_url']."tags/\">".$lang['all_tags']."</a>";
		else
			$tpl->result['tags_cloud'] .= "<br /><br /><a href=\"$PHP_SELF?do=tags\">".$lang['all_tags']."</a>";


	}

	create_cache ("tagscloud", $tpl->result['tags_cloud'], $config['skin']);
}


if ($do == "alltags") {

	$tpl->result['content'] = dle_cache("alltagscloud", $config['skin']);

	if (!$tpl->result['content']) {

		$tpl->load_template('tagscloud.tpl');

		$counts = array();
		$tags = array();
		$list = array();
		$sizes = array( "clouds_xsmall", "clouds_small", "clouds_medium", "clouds_large", "clouds_xlarge" );
		$min   = 1;
		$max   = 1;
		$range = 1;
		$limit = false;

		if ( preg_match( "#\\{tags limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$limit= true;
			$sql_select = "SELECT tag, COUNT(*) AS count FROM " . PREFIX . "_tags GROUP BY tag ORDER BY count DESC LIMIT 0,".intval($matches[1]);

		} else $sql_select = "SELECT tag, COUNT(*) AS count FROM " . PREFIX . "_tags GROUP BY tag";

		$db->query($sql_select);

		while($row = $db->get_row()){

			$tags[$row['tag']] = $row['count'];
			$counts[] = $row['count'];

		}
		$db->free();

		if (count($counts)) {
			$min   = min($counts);
			$max   = max($counts);
			$range = ($max-$min);
		}

		if (!$range) $range = 1;

		foreach ($tags as $tag => $value) {

			$list[$tag]['tag']   = $tag;
			$list[$tag]['size']  = $sizes[sprintf("%d", ($value-$min)/$range*4 )];
			$list[$tag]['count']  = $value;

		}

		usort ($list, "compare_tags");
		$tags = array();	

		foreach ($list as $value) {

			if (trim($value['tag']) != "" ) {

				if ($config['allow_alt_url'] == "yes")
	        		$tags[] = "<a href=\"".$config['http_home_url']."tags/".urlencode($value['tag'])."/\" class=\"{$value['size']}\" title=\"".$lang['tags_count']." ".$value['count']."\">".$value['tag']."</a>";
				else
					$tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=".urlencode($value['tag'])."\" class=\"{$value['size']}\" title=\"".$lang['tags_count']." ".$value['count']."\">".$value['tag']."</a>";
			}

		}

		$tags = implode(", ", $tags);

		if ( $limit ) $tpl->set( $matches[0], $tags);
		else $tpl->set('{tags}', $tags);

		$tpl->compile('content');
		$tpl->clear();

		create_cache ("alltagscloud", $tpl->result['content'], $config['skin']);

	}

}

if ($is_change) $config['allow_cache'] = false;

?>