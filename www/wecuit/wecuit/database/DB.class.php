<?php

/**
 *================================================================
 *framework/database/DB.class.php
 *Database operation class
 *================================================================
 */

class DB
{

    protected $conn = false;  //DB connection resources
    protected $sql;           //sql statement

    /**
     * Constructor, to connect to database, select database and set charset
     * @param $config string configuration array
     */
    public function __construct($config = array())
    {
        $host = isset($config['host']) ? $config['host'] : 'localhost';
        $user = isset($config['user']) ? $config['user'] : 'root';
        $password = isset($config['password']) ? $config['password'] : '';
        $dbname = isset($config['dbname']) ? $config['dbname'] : '';
        $port = isset($config['port']) ? $config['port'] : '3306';
        $charset = isset($config['charset']) ? $config['charset'] : 'utf8';

        $this->conn = mysqli_connect($host, $user, $password, $dbname, $port) or die('Database connection error');

        $this->setChar($charset);

        // $this->conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        // if (!$this->conn) throw new cuitException('Code: 00001  Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());

        // //mysqli_select_db($this->conn, $db_name) or die(mysqli_error($this->conn));

        // mysqli_query($this->conn, "set sql_mode = ''");
        // //字符转换，读库
        // mysqli_query($this->conn, "set character set 'utf8'");
        // //写库
        // mysqli_query($this->conn, "set names 'utf8'");
    }

    /**
     * Set charset
     * @access private
     * @param $charset string charset
     */
    private function setChar($charset)
    {
        //字符转换，读库
        mysqli_query($this->conn, "set character set '{$charset}'");
        //写库
        mysqli_query($this->conn, "set names '{$charset}'");
    }

    /**
     * Execute SQL statement
     * @access public
     * @param $sql string SQL query statement
     * @return $result，if succeed, return resrouces; if fail return error message and exit
     */
    public function query($sql)
    {
        $this->sql = $sql;
        // Write SQL statement into log
        // $str = $sql . "  [" . date("Y-m-d H:i:s") . "]" . PHP_EOL;
        // file_put_contents("log.txt", $str, FILE_APPEND);
        $result = mysqli_query($this->conn, $this->sql);

        if (!$result) {
            die($this->errno() . ':' . $this->error() . '<br />Error SQL statement is ' . $this->sql . '<br />');
        }
        return $result;
    }

    /**
     * Get the first column of the first record
     * @access public
     * @param $sql string SQL query statement
     * @return return the value of this column
     */
    public function getOne($sql)
    {
        $result = $this->query($sql);
        $row = mysqli_fetch_row($result);
        if ($row) {
            return $row[0];
        } else {
            return false;
        }
    }

    /**
     * Get one record
     * @access public
     * @param $sql SQL query statement
     * @return array associative array
     */
    public function getRow($sql)
    {
        if ($result = $this->query($sql)) {
            $row = mysqli_fetch_assoc($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Get all records
     * @access public
     * @param $sql SQL query statement
     * @return $list an 2D array containing all result records
     */
    public function getAll($sql)
    {
        $result = $this->query($sql);
        $list = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }
        return $list;
    }

    /**
     * Get the value of a column
     * @access public
     * @param $sql string SQL query statement
     * @return $list array an array of the value of this column
     */
    public function getCol($sql)
    {
        $result = $this->query($sql);
        $list = array();
        while ($row = mysqli_fetch_row($result)) {
            $list[] = $row[0];
        }
        return $list;
    }

    /**
     * Get last insert id
     */
    public function getInsertId()
    {

        return mysqli_insert_id($this->conn);
    }

    public function fetch($q)
    {
        return mysqli_fetch_assoc($q);
    }

    public function insert($q)
    {
        if (mysqli_query($this->conn, $q))
            return mysqli_insert_id($this->conn);
        return false;
    }

    public function update($q)
    {
        if (mysqli_query($this->conn, $q))
            return mysqli_affected_rows($this->conn);
        return false;
    }

    public function delete($q)
    {
        if (mysqli_query($this->conn, $q))
            return mysqli_affected_rows($this->conn);
        return false;
    }
    
    public function count($q)
    {
        $result = mysqli_query($this->conn, $q);
        if(!$result)
            return false;
        $count = mysqli_fetch_array($result);
        return $count[0];
    }

    /**
     * 对字符串进行转义，防止sql注入
     * 
     * @param &$data 引用类型(string|array)
     * 
     * @return void
     */
    public function escape(&$data)
    {
        if(is_array($data))
            $this->escape_arr($data);
        else
            $this->escape_str($data);
    }
    private function escape_arr(&$arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$key] = mysqli_real_escape_string($this->conn, $value);
        }
    }
    private function escape_str(&$str)
    {
        $str = mysqli_real_escape_string($this->conn, $str);
    }

    public function affected()
    {
        return mysqli_affected_rows($this->conn);
    }

    public function insert_array($table, $array)
    {
        $q = "INSERT INTO `$table`";
        $q .= " (`" . implode("`,`", array_keys($array)) . "`) ";
        $q .= " VALUES ('" . implode("','", array_values($array)) . "') ";
        //exit($q);
        if (mysqli_query($this->conn, $q))
            return mysqli_insert_id($this->conn);
        return false;
    }

    /**
     * Get error number
     * @access private
     * @return error number
     */
    public function errno()
    {

        return mysqli_errno($this->conn);
    }

    /**
     * Get error message
     * @access private
     * @return error message
     */
    public function error()
    {
        return mysqli_error($this->conn);
    }

    public function close()
    {
        return mysqli_close($this->conn);
    }
}
