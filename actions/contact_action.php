<?php
session_start();

require_once __DIR__ . '/../includes/otp_mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$_SESSION['contact_old'] = [
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
];

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    $_SESSION['contact_flash'] = [
        'type' => 'error',
        'message' => 'Please fill in all fields before sending your message.',
    ];
    header('Location: ../contact.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_flash'] = [
        'type' => 'error',
        'message' => 'Please enter a valid email address.',
    ];
    header('Location: ../contact.php');
    exit;
}

if (mb_strlen($name, 'UTF-8') > 120 || mb_strlen($email, 'UTF-8') > 160 || mb_strlen($subject, 'UTF-8') > 180) {
    $_SESSION['contact_flash'] = [
        'type' => 'error',
        'message' => 'Some fields are too long. Please shorten your message details.',
    ];
    header('Location: ../contact.php');
    exit;
}

try {
    sendContactMessageEmail($name, $email, $subject, $message);
    unset($_SESSION['contact_old']);
    $_SESSION['contact_flash'] = [
        'type' => 'success',
        'message' => 'Message sent. Our team will get back to you soon.',
    ];
} catch (Throwable $e) {
    error_log('ApeX contact email error: ' . $e->getMessage());
    $_SESSION['contact_flash'] = [
        'type' => 'error',
        'message' => 'We could not send your message right now. Please try again later.',
    ];
}

header('Location: ../contact.php');
exit;
