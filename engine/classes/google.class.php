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
 Файл: google.class.php
-----------------------------------------------------
 Назначение: Google Sitemap
=====================================================
*/

class googlemap {
	
	var $allow_url = "";
	var $home = "";
	var $limit = 0;
	var $news_priority = "0.5";
	var $stat_priority = "0.5";
	var $priority = "0.6";
	var $cat_priority = "0.7";
	
	function googlemap($config) {
		
		$this->allow_url = $config['allow_alt_url'];
		$this->home = $config['http_home_url'];
	
	}
	
	function build_map() {
		
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_static();
		$map .= $this->get_categories();
		$map .= $this->get_news();
		$map .= "</urlset>";
		
		return $map;
	
	}

	function build_index( $count ) {
		
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

		$lastmod = date( "Y-m-d" );		

		$map .= "<sitemap>\n<loc>{$this->home}uploads/sitemap1.xml</loc>\n<lastmod>{$lastmod}</lastmod>\n</sitemap>\n";

		for ($i =0; $i < $count; $i++) {
			$t = $i+2;
			$map .= "<sitemap>\n<loc>{$this->home}uploads/sitemap{$t}.xml</loc>\n<lastmod>{$lastmod}</lastmod>\n</sitemap>\n";
		}

		$map .= "</sitemapindex>";
		
		return $map;
	
	}

	function build_stat() {
		
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_static();
		$map .= $this->get_categories();
		$map .= "</urlset>";
		
		return $map;
	
	}

	function build_map_news( $n ) {
		
		$map = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$map .= $this->get_news( $n );
		$map .= "</urlset>";
		
		return $map;
	
	}
	
	function get_categories() {
		
		global $db;
		
		$cat_info = get_vars( "category" );
		$this->priority = $this->cat_priority;
		
		if( ! is_array( $cat_info ) ) {
			$cat_info = array ();
			
			$db->query( "SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC" );
			
			while ( $row = $db->get_row() ) {
				
				$cat_info[$row['id']] = array ();
				
				foreach ( $row as $key => $value ) {
					$cat_info[$row['id']][$key] = $value;
				}
			
			}
			
			set_vars( "category", $cat_info );
			$db->free();
		}
		
		$xml = "";
		$lastmod = date( "Y-m-d" );
		
		foreach ( $cat_info as $cats ) {
			if( $this->allow_url == "yes" ) $loc = $this->home . $this->get_url( $cats[id], $cat_info ) . "/";
			else $loc = $this->home . "index.php?do=cat&category=" . $cats['alt_name'];
			
			$xml .= $this->get_xml( $loc, $lastmod );
		}
		
		return $xml;
	}
	
	function get_news( $page = false ) {
		
		global $db, $config;
		
		$xml = "";
		$this->priority = $this->news_priority;
		
		if ( $page ) {

			$page = $page - 1;
			$page = $page * 40000;
			$this->limit = " LIMIT {$page},40000";

		} else {

			if( $this->limit < 1 ) $this->limit = false;
			
			if( $this->limit ) {
				
				$this->limit = " LIMIT 0," . $this->limit;
			
			} else {
				
				$this->limit = "";
			
			}
		}
		
		$thisdate = date( "Y-m-d H:i:s", (time() + ($config['date_adjust'] * 60)) );
		if( $config['no_date'] AND !$config['news_future'] ) $where_date = " AND date < '" . $thisdate . "'";
		else $where_date = "";
		
		$result = $db->query( "SELECT p.id, p.date, p.alt_name, p.category, e.editdate FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE approve=1" . $where_date . " ORDER BY date DESC" . $this->limit );
		
		while ( $row = $db->get_row( $result ) ) {

			$row['date'] = strtotime($row['date']);
			
			$row['category'] = intval( $row['category'] );
			
			if( $this->allow_url == "yes" ) {
				
				if( $config['seo_type'] == 1 OR  $config['seo_type'] == 2 ) {
					
					if( $row['category'] and $config['seo_type'] == 2 ) {
						
						$loc = $this->home . get_url( $row['category'] ) . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
					
					} else {
						
						$loc = $this->home . $row['id'] . "-" . $row['alt_name'] . ".html";
					
					}
				
				} else {
					
					$loc = $this->home . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
				}
			
			} else {
				
				$loc = $this->home . "index.php?newsid=" . $row['id'];
			
			}

			if ( $row['editdate'] ){
			
				$row['date'] =  $row['editdate'];
			
			}
			
			$xml .= $this->get_xml( $loc, date( "Y-m-d", $row['date'] ) );
		}
		
		return $xml;
	}
	
	function get_static() {
		
		global $db;
		
		$xml = "";
		$lastmod = date( "Y-m-d" );
		
		$this->priority = $this->stat_priority;
		
		$result = $db->query( "SELECT name, sitemap FROM " . PREFIX . "_static" );
		
		while ( $row = $db->get_row( $result ) ) {
			
			if( $row['name'] == "dle-rules-page" ) continue;
			if( !$row['sitemap'] ) continue;
			
			if( $this->allow_url == "yes" ) $loc = $this->home . $row['name'] . ".html";
			else $loc = $this->home . "index.php?do=static&page=" . $row[name];
			
			$xml .= $this->get_xml( $loc, $lastmod );
		}
		
		return $xml;
	}
	
	function get_url($id, $cat_info) {
		
		if( ! $id ) return;
		
		$parent_id = $cat_info[$id]['parentid'];
		
		$url = $cat_info[$id]['alt_name'];
		
		while ( $parent_id ) {
			
			$url = $cat_info[$parent_id]['alt_name'] . "/" . $url;
			
			$parent_id = $cat_info[$parent_id]['parentid'];
			
			if( $cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id'] ) break;
		
		}
		
		return $url;
	}
	
	function get_xml($loc, $lastmod) {
		
		$loc = htmlspecialchars( $loc );
		
		$xml = "\t<url>\n";
		$xml .= "\t\t<loc>$loc</loc>\n";
		$xml .= "\t\t<lastmod>$lastmod</lastmod>\n";
		$xml .= "\t\t<priority>" . $this->priority . "</priority>\n";
		$xml .= "\t</url>\n";
		
		return $xml;
	}

}

?>