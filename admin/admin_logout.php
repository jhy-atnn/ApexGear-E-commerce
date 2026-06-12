<?php
session_start();

// Unset the specific admin session
unset($_SESSION['admin']);

header("Location: ../admingear.php");
exit;
?>