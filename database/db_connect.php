<?php
// Set the default PHP timezone
date_default_timezone_set('Asia/Manila');

class Database
{
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $dbname = 'apexgear_db';
    public $conn;

    // The constructor runs automatically when you create a new Database object
    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        // Instantiate the mysqli object
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        // Check for connection errors
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }

        // Apply your specific charset and MySQL timezone settings
        $this->conn->set_charset("utf8mb4");
        $this->conn->query("SET time_zone = '+08:00'");
    }

    // Method for other files to access the active connection
    public function getConnection()
    {
        return $this->conn;
    }

    // Best practice: A method to close the connection when a script finishes
    public function closeConnection()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
