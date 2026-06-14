<?php
// Set the default PHP timezone to Manila
date_default_timezone_set('Asia/Manila');

class Database
{
    // Aiven Cloud Credentials
    private $host = 'apexgear-ph-apex26gear-21c2.e.aivencloud.com';
    private $username = 'avnadmin';
    private $password = 'AVNS_E-6phyJKuBkq_iaTkQ5';
    private $dbname = 'apexgear_db';
    private $port = 26058; // Specific port required by Aiven
    public $conn;

    // The constructor runs automatically when you create a new Database object
    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        // 1. Initialize the mysqli object
        $this->conn = mysqli_init();

        // 2. Define the path to your downloaded Aiven CA certificate
        // Make sure ca.pem is in the same folder as this db_connect.php file
        $ssl_ca = __DIR__ . '/ca.pem';

        // 3. Apply SSL configuration before connecting
        $this->conn->ssl_set(NULL, NULL, $ssl_ca, NULL, NULL);

        // 4. Connect using real_connect to support SSL and the custom port
        $connected = $this->conn->real_connect(
            $this->host,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port,
            NULL,
            MYSQLI_CLIENT_SSL
        );

        // Check for connection errors
        if (!$connected) {
            die("Database Connection Failed: " . mysqli_connect_error());
        }

        // Apply your specific charset and MySQL timezone settings
        // Enforcing +08:00 aligns the Singapore server with Philippine Time
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
