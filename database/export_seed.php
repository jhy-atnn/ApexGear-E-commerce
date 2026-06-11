<?php

// Export script disabled — application now uses a session-backed fake DB.
// The legacy export required a mysqli connection from db_connect.php.
// To avoid IDE/runtime warnings, this script no longer attempts to call
// `apexgear_export_seed_data()` or use a missing `$conn` variable.

echo "Database export disabled. No database connection available.\n";
