<?php
session_start(); // 1. Manually start the session so we can modify it
require_once __DIR__ . '/includes/auth_timeout.php';

unset($_SESSION['user']);

// 3. Remove their personal favorites so the next person on the computer doesn't see them
unset($_SESSION['favorites']); 
apex_clear_login_timeout();

// (Note: We are NOT unsetting the 'cart', which allows them to keep their shopping cart even if they log out!)

// 4. Redirect to home
header('Location: index.php');
exit;
?>
