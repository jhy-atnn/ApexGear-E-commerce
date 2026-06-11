<?php

function apexgear_sql_value(mysqli $conn, $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    return "'" . $conn->real_escape_string((string)$value) . "'";
}

function apexgear_export_table_rows(mysqli $conn, string $table, array $columns, string $orderBy): array
{
    $quotedColumns = array_map(fn($column) => "`{$column}`", $columns);
    $sql = "SELECT " . implode(', ', $quotedColumns) . " FROM `{$table}` ORDER BY {$orderBy}";
    $result = $conn->query($sql);

    if (!$result) {
        throw new RuntimeException($conn->error);
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $values = [];
        foreach ($columns as $column) {
            $values[] = apexgear_sql_value($conn, $row[$column]);
        }
        $rows[] = '(' . implode(', ', $values) . ')';
    }

    return $rows;
}

function apexgear_auto_increment_sql(mysqli $conn, string $table, string $idColumn): string
{
    $result = $conn->query("SELECT COALESCE(MAX(`{$idColumn}`), 0) + 1 AS next_id FROM `{$table}`");
    if (!$result) {
        throw new RuntimeException($conn->error);
    }

    $row = $result->fetch_assoc();
    $nextId = max(1, (int)($row['next_id'] ?? 1));

    return "ALTER TABLE `{$table}` AUTO_INCREMENT = {$nextId};";
}

function apexgear_insert_statement(string $table, array $columns, array $rows, string $suffix = ''): string
{
    if (empty($rows)) {
        return '';
    }

    $quotedColumns = array_map(fn($column) => "`{$column}`", $columns);
    return "INSERT INTO `{$table}` (" . implode(', ', $quotedColumns) . ") VALUES\n" .
        implode(",\n", $rows) .
        $suffix . ";\n";
}

function apexgear_duplicate_update_suffix(array $columns, array $skipColumns): string
{
    $updates = [];
    foreach ($columns as $column) {
        if (in_array($column, $skipColumns, true)) {
            continue;
        }
        $updates[] = "`{$column}` = VALUES(`{$column}`)";
    }

    return "\nON DUPLICATE KEY UPDATE " . implode(', ', $updates);
}

function apexgear_export_seed_data(mysqli $conn, string $outputPath): void
{
    $sections = [];
    $sections[] = "-- ApeX Gear shared seed data";
    $sections[] = "-- Auto-generated from the local database. Do not edit by hand.";
    $sections[] = "SET FOREIGN_KEY_CHECKS = 0;\n";

    $brandColumns = ['brand_id', 'brand_name'];
    $brandRows = apexgear_export_table_rows($conn, 'brands_tbl', $brandColumns, 'brand_id');
    $sections[] = apexgear_insert_statement(
        'brands_tbl',
        $brandColumns,
        $brandRows,
        apexgear_duplicate_update_suffix($brandColumns, ['brand_id'])
    );
    $sections[] = apexgear_auto_increment_sql($conn, 'brands_tbl', 'brand_id') . "\n";

    $categoryColumns = ['category_id', 'category_name'];
    $categoryRows = apexgear_export_table_rows($conn, 'categories_tbl', $categoryColumns, 'category_id');
    $sections[] = apexgear_insert_statement(
        'categories_tbl',
        $categoryColumns,
        $categoryRows,
        apexgear_duplicate_update_suffix($categoryColumns, ['category_id'])
    );
    $sections[] = apexgear_auto_increment_sql($conn, 'categories_tbl', 'category_id') . "\n";

    $productColumns = ['product_id', 'name', 'brand_id', 'category_id', 'price', 'old_price', 'stock', 'rating', 'badge', 'badge_type', 'image', 'desc', 'is_archived'];
    $productRows = apexgear_export_table_rows($conn, 'products_tbl', $productColumns, 'product_id');
    $sections[] = apexgear_insert_statement(
        'products_tbl',
        $productColumns,
        $productRows,
        apexgear_duplicate_update_suffix($productColumns, ['product_id'])
    );
    $sections[] = apexgear_auto_increment_sql($conn, 'products_tbl', 'product_id') . "\n";

    // NOTE: users_tbl is intentionally excluded from seed data.
    // User accounts are created at runtime and must not be overwritten by seeds.

    $sections[] = "SET FOREIGN_KEY_CHECKS = 1;\n";

    if (file_put_contents($outputPath, implode("\n", array_filter($sections))) === false) {
        throw new RuntimeException("Unable to write seed file: {$outputPath}");
    }
}

