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
 Файл: mysql.class.php
-----------------------------------------------------
 Назначение: Класс для работы с базой данных
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

class db
{
	var $db_id = false;
	var $connected = false;
	var $query_num = 0;
	var $error_count = 0;
	var $query_list = array();
	var $mysql_error = '';
	var $mysql_version = '';
	var $mysql_error_num = 0;
	var $mysql_extend = "MySQL";
	var $MySQL_time_taken = 0;
	var $query_id = false;
	
	function connect($db_user, $db_pass, $db_name, $db_location = 'localhost', $show_error=1)
	{
		if(!$this->db_id = @mysql_connect($db_location, $db_user, $db_pass)) {
			if($show_error == 1) {
				$this->display_error(mysql_error(), mysql_errno());
			} else {
				return false;
			}
		} 

		if(!@mysql_select_db($db_name, $this->db_id)) {
			if($show_error == 1) {
				$this->display_error(mysql_error(), mysql_errno());
			} else {
				return false;
			}
		}

		$this->mysql_version = mysql_get_server_info();

		if(!defined('COLLATE'))
		{ 
			define ("COLLATE", "cp1251");
		}

		if (version_compare($this->mysql_version, '4.1', ">=")) mysql_query("/*!40101 SET NAMES '" . COLLATE . "' */");

		$this->connected = true;

		return true;
	}
	
	function query($query, $show_error=true)
	{
		$time_before = $this->get_real_time();

		if(!$this->connected) $this->connect(DBUSER, DBPASS, DBNAME, DBHOST);
		
		if(!($this->query_id = mysql_query($query, $this->db_id) )) {

			$this->mysql_error = mysql_error();
			$this->mysql_error_num = mysql_errno();

			if($show_error) {
				$this->display_error($this->mysql_error, $this->mysql_error_num, $query);
			}
		}
			
		$this->MySQL_time_taken += $this->get_real_time() - $time_before;
		

//			$this->query_list[] = array( 'time'  => ($this->get_real_time() - $time_before), 
//										 'query' => $query,
//										 'num'   => (count($this->query_list) + 1));

		$this->query_num ++;

		return $this->query_id;
	}
	
	function get_row($query_id = '')
	{
		if ($query_id == '') $query_id = $this->query_id;

		return mysql_fetch_assoc($query_id);
	}

	function get_array($query_id = '')
	{
		if ($query_id == '') $query_id = $this->query_id;

		return mysql_fetch_array($query_id);
	}
	
	
	function super_query($query, $multi = false)
	{

		if(!$multi) {

			$this->query($query);
			$data = $this->get_row();
			$this->free();			
			return $data;

		} else {
			$this->query($query);
			
			$rows = array();
			while($row = $this->get_row()) {
				$rows[] = $row;
			}

			$this->free();			

			return $rows;
		}
	}
	
	function num_rows($query_id = '')
	{

		if ($query_id == '') $query_id = $this->query_id;

		return mysql_num_rows($query_id);
	}
	
	function insert_id()
	{
		return mysql_insert_id($this->db_id);
	}

	function get_result_fields($query_id = '') {

		if ($query_id == '') $query_id = $this->query_id;

		while ($field = mysql_fetch_field($query_id))
		{
            $fields[] = $field;
		}
		
		return $fields;
   	}

	function safesql( $source )
	{
		if ($this->db_id) return mysql_real_escape_string ($source, $this->db_id);
		else return mysql_escape_string($source);
	}

	function free( $query_id = '' )
	{

		if ($query_id == '') $query_id = $this->query_id;

		@mysql_free_result($query_id);
	}

	function close()
	{
		@mysql_close($this->db_id);
	}

	function get_real_time()
	{
		list($seconds, $microSeconds) = explode(' ', microtime());
		return ((float)$seconds + (float)$microSeconds);
	}	

	function display_error($error, $error_num, $query = '')
	{
		$this->error_count ++;

		$this->query_list[] = array( 'time'  => ($this->get_real_time() - $time_before), 
									 'query' => $query,
									 'num'   => (count($this->query_list) + 1));
	}

}


?>