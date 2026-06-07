<?php

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/db_seed_export.php';

apexgear_export_seed_data($conn, __DIR__ . '/seed_data.sql');
echo "Exported shared seed data to database/seed_data.sql\n";

