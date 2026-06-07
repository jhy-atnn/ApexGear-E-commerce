<?php

function apexgear_run_sql_script(mysqli $conn, string $sql): void
{
    if (trim($sql) === '') {
        return;
    }

    if (!$conn->multi_query($sql)) {
        throw new RuntimeException($conn->error);
    }

    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
        throw new RuntimeException($conn->error);
    }
}

function apexgear_import_dump(mysqli $conn, string $dumpPath, string $database): void
{
    if (!is_file($dumpPath)) {
        throw new RuntimeException("Database dump not found: {$dumpPath}");
    }

    $sql = file_get_contents($dumpPath);
    if ($sql === false) {
        throw new RuntimeException("Unable to read database dump: {$dumpPath}");
    }

    $sql = str_replace('`db_apexgear`', '`' . str_replace('`', '``', $database) . '`', $sql);
    apexgear_run_sql_script($conn, $sql);
}

function apexgear_table_count(mysqli $conn, string $database): int
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS table_count
         FROM information_schema.tables
         WHERE table_schema = ?"
    );
    $stmt->bind_param('s', $database);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int)($row['table_count'] ?? 0);
}

function apexgear_run_migrations(mysqli $conn, string $migrationsDir): void
{
    if (!is_dir($migrationsDir)) {
        return;
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS schema_migrations (
            migration varchar(255) NOT NULL PRIMARY KEY,
            applied_at timestamp NOT NULL DEFAULT current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $files = glob($migrationsDir . '/*.sql') ?: [];
    sort($files, SORT_STRING);

    foreach ($files as $file) {
        $migration = basename($file);

        $stmt = $conn->prepare("SELECT 1 FROM schema_migrations WHERE migration = ? LIMIT 1");
        $stmt->bind_param('s', $migration);
        $stmt->execute();
        $alreadyApplied = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($alreadyApplied) {
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException("Unable to read migration: {$migration}");
        }

        apexgear_run_sql_script($conn, $sql);

        $stmt = $conn->prepare("INSERT INTO schema_migrations (migration) VALUES (?)");
        $stmt->bind_param('s', $migration);
        $stmt->execute();
        $stmt->close();
    }
}

function apexgear_prepare_database(string $host, string $username, string $password, string $database): void
{
    $serverConn = new mysqli($host, $username, $password);
    if ($serverConn->connect_error) {
        die("Database Server Connection Failed: " . $serverConn->connect_error);
    }

    $serverConn->set_charset('utf8mb4');
    $escapedDatabase = '`' . str_replace('`', '``', $database) . '`';
    $serverConn->query("CREATE DATABASE IF NOT EXISTS {$escapedDatabase} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    if (apexgear_table_count($serverConn, $database) === 0) {
        apexgear_import_dump($serverConn, __DIR__ . '/db_apexgear.sql', $database);
    }

    $serverConn->close();

    $dbConn = new mysqli($host, $username, $password, $database);
    if ($dbConn->connect_error) {
        die("Database Connection Failed: " . $dbConn->connect_error);
    }

    $dbConn->set_charset('utf8mb4');

    try {
        apexgear_run_migrations($dbConn, __DIR__ . '/migrations');
    } catch (RuntimeException $e) {
        die("Database Migration Failed: " . $e->getMessage());
    } finally {
        $dbConn->close();
    }
}
