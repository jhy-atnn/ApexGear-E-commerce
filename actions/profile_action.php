<?php
session_start();
// Point this back to your root directory db_connect.php
require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$userId = intval($_SESSION['user']['id']);

// Map POST data (from the frontend modal)
$firstName     = trim($_POST['first_name'] ?? '');
$lastName      = trim($_POST['last_name'] ?? '');
$bio           = trim($_POST['bio'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$gender        = trim($_POST['gender'] ?? '');
$streetAddress = trim($_POST['street_address'] ?? '');
$city          = trim($_POST['city'] ?? '');
$zipCode       = trim($_POST['postal_code'] ?? ''); // Maps to postal_code from HTML

// Handle Profile Picture Upload
$profilePicturePath = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);

    if (isset($allowedTypes[$fileType])) {
        $extension = $allowedTypes[$fileType];
        $targetDir = __DIR__ . '/../assets/images/profiles/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate a unique filename using their User ID
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

// Connect to OOP Database
$db = new Database();
$conn = $db->getConnection();

// 1. Update users_tbl (Base Information)
$stmt1 = $conn->prepare("UPDATE users_tbl SET first_name = ?, last_name = ?, gender = ? WHERE user_id = ?");
$stmt1->bind_param("sssi", $firstName, $lastName, $gender, $userId);
$stmt1->execute();

// 2. Update users_profiles_tbl (Dependent Information & Address)
if ($profilePicturePath) {
    // If a new picture was uploaded, update the image_path column
    $stmt2 = $conn->prepare("UPDATE users_profiles_tbl SET bio = ?, phone_number = ?, street_address = ?, city = ?, zip_code = ?, image_path = ? WHERE user_id = ?");
    $stmt2->bind_param("ssssssi", $bio, $phone, $streetAddress, $city, $zipCode, $profilePicturePath, $userId);
} else {
    // Otherwise, update everything except the image
    $stmt2 = $conn->prepare("UPDATE users_profiles_tbl SET bio = ?, phone_number = ?, street_address = ?, city = ?, zip_code = ? WHERE user_id = ?");
    $stmt2->bind_param("sssssi", $bio, $phone, $streetAddress, $city, $zipCode, $userId);
}
$stmt2->execute();

// 3. Update the Live Session Data
// This ensures the frontend modal populates the new data immediately without re-logging in.
$_SESSION['user']['first_name']     = $firstName;
$_SESSION['user']['last_name']      = $lastName;
$_SESSION['user']['gender']         = $gender;
$_SESSION['user']['bio']            = $bio;
$_SESSION['user']['phone']          = $phone;
$_SESSION['user']['street_address'] = $streetAddress;
$_SESSION['user']['city']           = $city;
$_SESSION['user']['postal_code']    = $zipCode;

if ($profilePicturePath) {
    $_SESSION['user']['profile_picture'] = $profilePicturePath;
}

echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
$db->closeConnection();
exit;
