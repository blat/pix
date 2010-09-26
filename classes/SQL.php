<?php

class SQL {

    private $_db;
    private $_result;

    public function __construct() {
        $this->_connect();
    }

    public function __destruct() {
        if ($this->_result) {
            $this->free();
        }
        $this->_disconnect();
    }

    public function execute($query) {
        $this->_result = mysql_query($query);
        return $this->_result;
    }

    public function next() {
        return mysql_fetch_array($this->_result);
    }

    public function count() {
        return mysql_num_rows($this->_result);
    }

    public function free() {
        if ($this->_result) {
            @mysql_free_result($this->_result);
        }
    }

    public function escape($string) {
        return mysql_escape_string($string);
    }

    private function _connect() {
        global $config;

        $this->_db = mysql_connect($config['sql_host'], $config['sql_user'], $config['sql_password']);
        mysql_select_db($config['sql_database']);
    }

    private function _disconnect() {
        mysql_close($this->_db);
    }

    public function begin_transaction() {
        return mysql_query('BEGIN', $this->_db);
    }

    public function commit() {
        return mysql_query('COMMIT', $this->_db);
    }

    public function rollback() {
        return mysql_query('ROLLBACK', $this->_db);
    }

}
