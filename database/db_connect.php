<?php
class db_connect
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "db_apexgear";

    public $conn;
    public function getConnection()
    {
        $this->conn = null;

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}