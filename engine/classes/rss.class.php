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
 Файл: rss.class.php
-----------------------------------------------------
 Назначение: XML Парсер
=====================================================
*/

class xmlParser {
	
	var $att;
	var $id;
	var $title;
	var $content = array ();
	var $index = 0;
	var $xml_parser;
	var $tagname;
	var $max_news = 0;
	var $tag_open = false;
	var $rss_charset = '';
	var $rss_option = '';
	var $lastdate = '';
	var $pre_lastdate = '';
	
	function xmlParser($file, $max) {
		
		$this->max_news = $max;
		
		$this->xml_parser = xml_parser_create();
		xml_set_object( $this->xml_parser, $this );
		xml_set_element_handler( $this->xml_parser, "startElement", "endElement" );
		xml_set_character_data_handler( $this->xml_parser, 'elementContent' );
		$this->rss_option = xml_parser_get_option( $this->xml_parser, XML_OPTION_TARGET_ENCODING );
		
		if( ! ($data = $this->_get_contents( $file )) ) {
			$this->content[0]['title'] = "Fatal Error";
			$this->content[0]['description'] = "Fatal Error: could not open XML input (" . $file . ")";
			$this->content[0]['link'] = "#";
			$this->content[0]['date'] = time();
		}
		
		preg_replace( "#encoding=\"(.+?)\"#ie", "\$this->get_charset('\\1')", $data );
		
		if( ! xml_parse( $this->xml_parser, $data ) ) {
			
			$error_code = xml_get_error_code( $this->xml_parser );
			$error_line = xml_get_current_line_number( $this->xml_parser );
			
			if( $error_code == 4 ) {
				
				$this->content = array ();
				$this->index = 0;
				$this->tag_open = false;
				$this->tagname = "";
				
				$this->xml_parser = xml_parser_create();
				xml_set_object( $this->xml_parser, $this );
				xml_set_element_handler( $this->xml_parser, "startElement", "endElement" );
				xml_set_character_data_handler( $this->xml_parser, 'elementContent' );
				$this->rss_option = xml_parser_get_option( $this->xml_parser, XML_OPTION_TARGET_ENCODING );
				
				$data = iconv( $this->rss_charset, "utf-8", $data );
				
				if( ! xml_parse( $this->xml_parser, $data ) ) {
					
					$this->content[0]['title'] = "XML error in File: " . $file;
					$this->content[0]['description'] = sprintf( "XML error: %s at line %d", xml_error_string( xml_get_error_code( $this->xml_parser ) ), xml_get_current_line_number( $this->xml_parser ) );
					$this->content[0]['link'] = "#";
					$this->content[0]['date'] = time();
				
				}
			
			} else {
				
				$this->content[0]['title'] = "XML error in File: " . $file;
				$this->content[0]['description'] = sprintf( "XML error: %s at line %d", xml_error_string( $error_code ), $error_line );
				$this->content[0]['link'] = "#";
				$this->content[0]['date'] = time();
			
			}
		
		}
		
		xml_parser_free( $this->xml_parser );
	}
	
	function _get_contents($file) {
		
		$data = false;
		
		if( function_exists( 'curl_init' ) ) {
			
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $file );
			curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
			@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			
			$data = curl_exec( $ch );
			curl_close( $ch );

			if( $data ) return $data;
			else return false;
		
		} else {

			$data = @file_get_contents( $file );
			
			if( $data ) return $data;
			else return false;

		}
	
	}
	
	function pre_parse($date) {
		
		global $config;
		
		$i = 0;
		
		foreach ( $this->content as $content ) {
			
			$content_date = strtotime( $content['date'] );
			
			if( $date ) {
				$this->content[$i]['date'] = time() + ($config['date_adjust'] * 60);
			} else {
				$this->content[$i]['date'] = $content_date;
			}
			
			if( ! $i ) $this->lastdate = $content_date;
			
			if( $i and $content_date > $this->lastdate ) $this->lastdate = $content_date;
			
			if( $this->pre_lastdate != "" and $this->pre_lastdate >= $content_date ) {
				unset( $this->content[$i] );
				$i ++;
				continue;
			}
			
			$this->content[$i]['description'] = rtrim( $this->content[$i]['description'] );
			$this->content[$i]['content'] = rtrim( $this->content[$i]['content'] );
			
			if( $this->content[$i]['content'] != '' ) {
				$this->content[$i]['description'] = $this->content[$i]['content'];
			}
			unset( $this->content[$i]['content'] );
			
			if( preg_match_all( "#<div id=\'news-id-(.+?)\'>#si", $this->content[$i]['description'], $out ) ) {
				
				$this->content[$i]['description'] = preg_replace( "#<div id=\'news-id-(.+?)\'>#si", "", $this->content[$i]['description'] );
				$this->content[$i]['description'] = dle_substr( $this->content[$i]['description'], 0, - 6, $config['charset'] );
			
			}
			
			$i ++;
		}
	
	}
	
	function startElement($parser, $name, $attrs) {
		
		if( $name == "ITEM" ) {
			$this->tag_open = true;
		}
		
		$this->tagname = $name;
	}
	
	function endElement($parser, $name) {
		
		if( $name == "ITEM" ) {
			$this->index ++;
			$this->tag_open = false;
		}
	}
	
	function elementContent($parser, $data) {
		
		if( $this->tag_open and $this->index < $this->max_news ) {
			
			switch ($this->tagname) {
				case 'TITLE' :
					$this->content[$this->index]['title'] .= $data;
					break;
				case 'DESCRIPTION' :
					$this->content[$this->index]['description'] .= $data;
					break;
				case 'CONTENT:ENCODED' :
					$this->content[$this->index]['content'] .= $data;
					break;
				case 'LINK' :
					$this->content[$this->index]['link'] .= $data;
					break;
				case 'PUBDATE' :
					$this->content[$this->index]['date'] .= $data;
					break;
				case 'CATEGORY' :
					$this->content[$this->index]['category'] .= $data;
					break;
				case 'DC:CREATOR' :
					$this->content[$this->index]['author'] .= $data;
					break;
			
			}
		}
	
	}
	
	function get_charset($charset) {
		
		if( $this->rss_charset == '' ) $this->rss_charset = strtolower( $charset );
	
	}
	
	function convert($from, $to) {
		
		if( $from == '' ) return;
		
		if( function_exists( 'iconv' ) ) {
			$i = 0;
			
			foreach ( $this->content as $content ) {
				
				if( @iconv( $from, $to . "//IGNORE", $this->content[$i]['title'] ) ) $this->content[$i]['title'] = @iconv( $from, $to . "//IGNORE", $this->content[$i]['title'] );
				
				if( @iconv( $from, $to . "//IGNORE", $this->content[$i]['description'] ) ) $this->content[$i]['description'] = @iconv( $from, $to . "//IGNORE", $this->content[$i]['description'] );
				
				if( $this->content[$i]['content'] and @iconv( $from, $to . "//IGNORE", $this->content[$i]['content'] ) ) $this->content[$i]['content'] = @iconv( $from, $to . "//IGNORE", $this->content[$i]['content'] );
				
				if( $this->content[$i]['category'] and @iconv( $from, $to . "//IGNORE", $this->content[$i]['category'] ) ) $this->content[$i]['category'] = @iconv( $from, $to . "//IGNORE", $this->content[$i]['category'] );
				
				if( $this->content[$i]['author'] and @iconv( $from, $to . "//IGNORE", $this->content[$i]['author'] ) ) $this->content[$i]['author'] = @iconv( $from, $to . "//IGNORE", $this->content[$i]['author'] );
				
				$i ++;
			
			}
		}
	}
}

?>