<?php
require_once(dirname(__FILE__) . "/../config/general.php");

class DB {
    private $connection;
    private $host;
    private $user;
    private $password;
    private $database;
    private static $instance;
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DB();
        }
        return self::$instance;
    } 
    
    private function __construct() {
        global $config;
        $this->host = $config['mysql_host'];
        $this->user = $config['mysql_user'];
        $this->password = $config['mysql_password'];
        $this->database = $config['mysql_database'];
    }
    
    public function __destruct() {
        $this->disconnect();
    }
    
    public function connect() { 
    	global $con;
    	
    if (!isset(static::$con)) {
    		$con = mysqli_connect($this->host, $this->user, $this->password, $this->database) or die(mysqli_error($con));
    		
    		
       // 	if (static::$connection = mysqli_connect($this->host, $this->user, $this->password, $this->database)) {
       //     	throw new Exception("MySQL Connect: " . mysqli_connect_error());
       // 	}
        //	if (!mysql_select_db($this->database, $this->connection)) {
         //   	throw new Exception("MySQL Select DB: " . mysql_error());
        //	}
    	}
        //mysql_set_charset('utf8', $this->connection); 
    }
    
    public function disconnect() {
    global $con;
        if ($con) {
            mysqli_close($con);
        }
    }
    
    public function query($query) {
    global $con;     
    	
        if (!$con) {
            $this->connect();
        }
        if (!$result = mysqli_query($con, $query)) {
            throw new Exception("MySQL Query: " . mysqli_error($con));
        }  
        return $result;
    }
    
    public function multi_query($query) {
    	global $con;
    	 
    	if (!$con) {
    		$this->connect();
    	}
    	if (!$result = mysqli_multi_query($con, $query)) {
    		throw new Exception("MySQL Query: " . mysqli_error($con));
    	}
    	return $result;
    }
    
    public function insert($table, $fields, $nulls = true) {
        if (!$table) {
            throw new InvalidArgumentException('The first argument ($table) must not be empty');
        }
        if (!is_array($fields)) {
            throw new InvalidArgumentException('The second argument ($fields) must be an array (dictionary)');
        }
        if (!is_resource($this->connection)) {
            $this->connect();
        }
        if (!$nulls) {
            $fields = array_filter($fields, 'is_not_empty'); 
        }
        if (!$fields) {
            return true;
        }
        $query = "INSERT INTO $table (" . implode(',', array_keys($fields)) . ") VALUES(";
        foreach ($fields as $field) {
            if (get_magic_quotes_gpc()) {
                $field = stripslashes($field);
            }
            if (is_empty($field)) {
                $query.= "NULL,"; 
            }
            else {
                $query.= "'".mysqlx_real_escape_string($field)."',";  
            }
        }
        $query = substr($query, 0, -1) . ')';
        return $this->query($query);
    }
    
    public function update($table, $fields, $where, $nulls = true) {
        if (!$table) {
            throw new InvalidArgumentException('The first argument ($table) must not be empty');
        }
        if (!is_array($fields)) {
            throw new InvalidArgumentException('The second argument ($fields) must be an array (dictionary)');
        }
        if (!is_resource($this->connection)) {
            $this->connect();
        }
        if (!$nulls) {
            $fields = array_filter($fields, 'is_not_empty'); 
        }
        if (!$fields) {
            return true;
        }
        $query = "UPDATE $table SET ";
        foreach ($fields as $column=>$value) {
            if (get_magic_quotes_gpc()) {
                $value = stripslashes($value);
            }
            if (is_empty($value)) {
                $query.= "$column = NULL,"; 
            }
            else {
                $query.= "$column = '".mysqlx_real_escape_string($value)."',";   
            }
        }
        $query = substr($query, 0, -1);
        if ($where) {
            $query.= " WHERE $where";
        }
        return $this->query($query);
    }
    
 	public function get_last_id() {
    	global $con;
        return mysqli_insert_id($con);
    }
    
    public function parse_result($result_sql) {
        $result = array();
        $index = 0;
        //Percorre todos os resultados da consulta
        while ($row = mysqli_fetch_array($result_sql)) {
            //Obtém os índices do registro da tabela (nomes das colunas)
            $keys = array_keys($row);
            foreach ($keys as $key) {
                //Ignora os índices numéricos para evitar duplicação (obtém apenas os nomes das colunas)
                if (!is_numeric($key)) {
                    //Armazena o valor no array do resultado
                    $result[$index][$key] = replace_quotes($row[$key]);
                }
            }
            $index++;
        }
        //Retorna o resultado em forma de matriz
        return $result;
    }

    public function import_db($file) {
        $filename = $file; //How to Create SQL File Step : url:http://localhost/phpmyadmin->detabase select->table select->Export(In Upper Toolbar)->Go:DOWNLOAD .SQL FILE
        $op_data = '';
        $lines = file($filename);
        foreach ($lines as $line)
        {
            if (substr($line, 0, 2) == '--' || $line == '')//This IF Remove Comment Inside SQL FILE
            {
                continue;
            }
            $op_data .= $line;
            if (substr(trim($line), -1, 1) == ';')//Breack Line Upto ';' NEW QUERY
            {
                $this->multi_query($op_data);
                $op_data = '';
            }
        }
    }
}
