<?php
session_start();
// Adjust this path if your db_connect is located elsewhere
require_once __DIR__ . '/../database/db_connect.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$userId = intval($_SESSION['user']['id']);

// 1. Map POST data from the frontend modal
$firstName     = trim($_POST['first_name'] ?? '');
$lastName      = trim($_POST['last_name'] ?? '');
$gender        = trim($_POST['gender'] ?? '');
$bio           = trim($_POST['bio'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$streetAddress = trim($_POST['street_address'] ?? '');
$city          = trim($_POST['city'] ?? '');
$zipCode       = trim($_POST['postal_code'] ?? ''); // Maps from HTML name="postal_code"

// 2. Connect to Database
$db = new Database();
$conn = $db->getConnection();

// 3. Handle Profile Picture Upload
$profilePicturePath = $_SESSION['user']['profile_picture'] ?? null; // Keep existing by default
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $fileType = mime_content_type($_FILES['profile_picture']['tmp_name']);
    
    if (isset($allowedTypes[$fileType])) {
        $extension = $allowedTypes[$fileType];
        $targetDir = __DIR__ . '/../assets/images/profiles/';
        
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        
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

// 4. Update core user details in `users_tbl`
$stmt1 = $conn->prepare("UPDATE users_tbl SET first_name = ?, last_name = ?, gender = ? WHERE user_id = ?");
$stmt1->bind_param("sssi", $firstName, $lastName, $gender, $userId);
$stmt1->execute();
$stmt1->close();

// 5. UPSERT (Update or Insert) dependent profile details in `users_profiles_tbl`
// FIXED: Using store_result() instead of get_result() to prevent the undefined method error
$checkStmt = $conn->prepare("SELECT profile_id FROM users_profiles_tbl WHERE user_id = ?");
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$checkStmt->store_result(); // Buffers the result safely

if ($checkStmt->num_rows > 0) {
    // Row exists -> UPDATE
    $stmt2 = $conn->prepare("UPDATE users_profiles_tbl SET bio = ?, phone_number = ?, street_address = ?, city = ?, zip_code = ?, image_path = ? WHERE user_id = ?");
    $stmt2->bind_param("ssssssi", $bio, $phone, $streetAddress, $city, $zipCode, $profilePicturePath, $userId);
    $stmt2->execute();
    $stmt2->close();
} else {
    // Row does not exist -> INSERT
    $stmt2 = $conn->prepare("INSERT INTO users_profiles_tbl (user_id, bio, phone_number, street_address, city, zip_code, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("issssss", $userId, $bio, $phone, $streetAddress, $city, $zipCode, $profilePicturePath);
    $stmt2->execute();
    $stmt2->close();
}
$checkStmt->close();

// 6. Update the Live Session Data so the frontend modal updates instantly
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

echo json_encode(['success' => true, 'message' => 'Profile successfully saved to the database!']);
$db->closeConnection();
exit;
?>