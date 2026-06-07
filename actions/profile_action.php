<?php
session_start();
require_once '../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    
    // Get POST data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');

    // Allow empty string for null fields
    $firstName = $firstName !== '' ? $firstName : null;
    $lastName = $lastName !== '' ? $lastName : null;
    $bio = $bio !== '' ? $bio : null;
    $phone = $phone !== '' ? $phone : null;
    $gender = $gender !== '' ? $gender : null;
    $birthday = $birthday !== '' ? $birthday : null;

    global $conn;

    $profilePicturePath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
        if (isset($allowedTypes[$fileType])) {
            $extension = $allowedTypes[$fileType];
            $targetDir = '../assets/images/profiles/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileName = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicturePath = 'assets/images/profiles/' . $fileName;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WEBP images are allowed.']);
            exit;
        }
    }

    if ($profilePicturePath !== null) {
        $stmt = $conn->prepare("UPDATE users_tbl SET first_name = ?, last_name = ?, bio = ?, phone = ?, gender = ?, birthday = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("sssssssi", $firstName, $lastName, $bio, $phone, $gender, $birthday, $profilePicturePath, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users_tbl SET first_name = ?, last_name = ?, bio = ?, phone = ?, gender = ?, birthday = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $firstName, $lastName, $bio, $phone, $gender, $birthday, $userId);
    }

    if ($stmt->execute()) {
        // Update session
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $_SESSION['user']['bio'] = $bio;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['gender'] = $gender;
        $_SESSION['user']['birthday'] = $birthday;
        if ($profilePicturePath !== null) {
            $_SESSION['user']['profile_picture'] = $profilePicturePath;
        }

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit;
