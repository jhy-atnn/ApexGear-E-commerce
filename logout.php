<?php
require_once __DIR__ . '/includes/storage.php';
unset($_SESSION['user']);
header('Location: index.php');
exit;
